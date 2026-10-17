<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class GoogleMapsScraper
{
    public function __construct(
        private readonly EmailExtractor $emailExtractor,
    ) {}

    public static function stopFlagPath(string $runId): string
    {
        return storage_path('app/temp/stop-'.$runId);
    }

    public static function pidPath(string $runId): string
    {
        return storage_path('app/temp/pid-'.$runId);
    }

    public static function requestStop(string $runId): void
    {
        $dir = storage_path('app/temp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Soft stop only: the Node scraper watches this flag, finishes the
        // current item, emits `done` with partial results, then exits.
        // Hard-killing here used to cut the stream before Excel could be built.
        file_put_contents(self::stopFlagPath($runId), (string) time());
    }

    /**
     * @param  callable(array<string, mixed>): void  $onEvent
     * @return list<array{name: string, email: string|null, phone: string|null}>
     */
    public function search(string $query, int $maxResults = 0, ?callable $onEvent = null, ?string $runId = null): array
    {
        $query = trim($query);
        if ($query === '') {
            throw new RuntimeException('ابحث بجملة صحيحة أولاً.');
        }

        $script = base_path('scripts/google-maps-scraper.cjs');
        if (! is_file($script)) {
            throw new RuntimeException('سكربت البحث غير موجود.');
        }

        // 0 = unlimited: keep collecting until sources are exhausted.
        $maxResults = max(0, $maxResults);
        $runId = $runId ?: uniqid('scrape_', true);
        $stopped = false;

        $process = new Process(
            [$this->nodeBinary(), $script, $query, (string) $maxResults],
            base_path(),
            array_merge($this->processEnvironment(), [
                'SCRAPE_STOP_FILE' => self::stopFlagPath($runId),
                'SCRAPE_RUN_ID' => $runId,
            ]),
            null,
            7200
        );

        if ($onEvent) {
            $onEvent([
                'type' => 'progress',
                'phase' => 'starting',
                'message' => $maxResults > 0
                    ? "بدء البحث بأقصى تغطية (حد اختياري: {$maxResults}) مع منع التكرار..."
                    : 'بدء البحث بأقصى تغطية بدون حد ثابت (Google Maps + مناطق + Google Search) مع منع التكرار...',
                'found' => 0,
                'listed' => 0,
                'total' => $maxResults,
                'unlimited' => $maxResults <= 0,
                'run_id' => $runId,
                'percent' => 1,
            ]);
        }

        $onEvent = $onEvent ?: static function (array $event): void {};

        $process->start();

        $dir = storage_path('app/temp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::pidPath($runId), (string) $process->getPid());

        $buffer = '';
        $places = null;
        $accumulated = [];
        $resultsFile = null;
        $errorMessage = null;

        $handleLine = function (string $line) use (&$places, &$accumulated, &$resultsFile, &$errorMessage, $onEvent): void {
            $line = trim($line);
            if ($line === '') {
                return;
            }

            $event = json_decode($line, true);
            if (! is_array($event) || ! isset($event['type'])) {
                return;
            }

            if ($event['type'] === 'progress') {
                $onEvent($event);
            } elseif ($event['type'] === 'place' && is_array($event['place'] ?? null)) {
                $accumulated[] = $event['place'];
                // Forward live place events so controller can save immediately.
                $onEvent($event);
            } elseif ($event['type'] === 'done') {
                if (! empty($event['file']) && is_string($event['file']) && is_file($event['file'])) {
                    $resultsFile = $event['file'];
                    $decoded = json_decode((string) file_get_contents($event['file']), true);
                    $places = is_array($decoded) ? $decoded : [];
                } elseif (is_array($event['places'] ?? null)) {
                    $places = $event['places'];
                } else {
                    $places = $accumulated;
                }

                $onEvent([
                    'type' => 'progress',
                    'phase' => 'done_collect',
                    'message' => 'اكتمل جمع الشركات، جاري تجهيز ملف Excel...',
                    'found' => count($places),
                    'listed' => count($places),
                    'total' => count($places),
                    'percent' => 99,
                ]);
            } elseif ($event['type'] === 'error') {
                $errorMessage = (string) ($event['message'] ?? 'فشل البحث');
            }
        };

        while (true) {
            $buffer .= $process->getIncrementalOutput();
            $process->getIncrementalErrorOutput();

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);
                $handleLine($line);
            }

            if (! $process->isRunning()) {
                $buffer .= $process->getIncrementalOutput();
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    $handleLine($line);
                }
                if (trim($buffer) !== '') {
                    $handleLine($buffer);
                    $buffer = '';
                }
                break;
            }

            if ($this->shouldStop($runId)) {
                if (! $stopped) {
                    $stopped = true;
                    $onEvent([
                        'type' => 'progress',
                        'phase' => 'stopping',
                        'message' => 'تم طلب الإيقاف... جاري تجهيز Excel بما تم جمعه.',
                        'found' => count($accumulated),
                        'listed' => count($accumulated),
                        'percent' => 95,
                        'run_id' => $runId,
                        'can_stop' => false,
                    ]);
                }

                // Give Node time to notice the flag and emit `done` with partial data.
                $deadline = microtime(true) + 12;
                while ($process->isRunning() && microtime(true) < $deadline) {
                    $buffer .= $process->getIncrementalOutput();
                    $process->getIncrementalErrorOutput();

                    while (($pos = strpos($buffer, "\n")) !== false) {
                        $line = substr($buffer, 0, $pos);
                        $buffer = substr($buffer, $pos + 1);
                        $handleLine($line);
                    }

                    if ($places !== null) {
                        break;
                    }

                    usleep(100000);
                }

                if ($process->isRunning()) {
                    $this->stopProcess($process, $runId);
                }
                break;
            }

            usleep(80000);
        }

        if ($process->isRunning()) {
            $process->wait();
        }

        if (! $stopped && is_file(self::stopFlagPath($runId))) {
            $stopped = true;
        }

        if ($places === null && $accumulated !== []) {
            $places = $accumulated;
        }

        if ($places === null && is_string($resultsFile) && is_file($resultsFile)) {
            $decoded = json_decode((string) file_get_contents($resultsFile), true);
            $places = is_array($decoded) ? $decoded : null;
        }

        if (($places === null || $places === []) && $errorMessage && ! $stopped) {
            throw new RuntimeException('فشل البحث في خرائط جوجل: '.$errorMessage);
        }

        if ($places === null || $places === []) {
            if ($stopped) {
                $places = $accumulated;
            } else {
                $stderr = trim($process->getErrorOutput());
                $short = $this->shortError($stderr);
                throw new RuntimeException(
                    'فشل البحث في خرائط جوجل'.($short ? ': '.$short : '.')
                );
            }
        }

        // Email is optional. Do not slow the pipeline with website crawling here.
        $rows = [];
        foreach ($places as $place) {
            if (! is_array($place)) {
                continue;
            }

            $name = trim((string) ($place['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $rows[] = [
                'name' => $name,
                'email' => trim((string) ($place['email'] ?? '')) ?: null,
                'phone' => trim((string) ($place['phone'] ?? '')) ?: null,
            ];
        }

        if (is_string($resultsFile) && is_file($resultsFile)) {
            @unlink($resultsFile);
        }

        @unlink(self::pidPath($runId));
        @unlink(self::stopFlagPath($runId));

        return $this->dedupe($rows);
    }

    private function shouldStop(string $runId): bool
    {
        return is_file(self::stopFlagPath($runId)) || connection_aborted();
    }

    private function stopProcess(Process $process, string $runId): void
    {
        try {
            $process->stop(1, defined('SIGTERM') ? SIGTERM : 15);
        } catch (\Throwable) {
            // ignore
        }

        self::requestStop($runId);
    }

    /**
     * @param  list<array{name: string, email: string|null, phone: string|null}>  $rows
     * @return list<array{name: string, email: string|null, phone: string|null}>
     */
    private function dedupe(array $rows): array
    {
        $unique = [];
        $seen = [];

        foreach ($rows as $row) {
            $key = $this->dedupeKey($row);
            if (isset($seen[$key])) {
                $index = $seen[$key];
                if (empty($unique[$index]['email']) && ! empty($row['email'])) {
                    $unique[$index]['email'] = $row['email'];
                }
                if (empty($unique[$index]['phone']) && ! empty($row['phone'])) {
                    $unique[$index]['phone'] = $row['phone'];
                }
                continue;
            }

            $seen[$key] = count($unique);
            $unique[] = $row;
        }

        return $unique;
    }

    /**
     * @param  array{name: string, email: string|null, phone: string|null}  $row
     */
    private function dedupeKey(array $row): string
    {
        $phone = preg_replace('/\D+/', '', (string) ($row['phone'] ?? '')) ?: '';
        if ($phone !== '') {
            if (str_starts_with($phone, '966')) {
                $phone = substr($phone, -9);
            } elseif (str_starts_with($phone, '0') && strlen($phone) >= 9) {
                $phone = substr($phone, -9);
            }

            return 'p:'.$phone;
        }

        $name = mb_strtolower(trim((string) $row['name']));
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return 'n:'.$name;
    }

    /**
     * @return array<string, string>
     */
    private function processEnvironment(): array
    {
        $env = [];

        foreach ([$_SERVER, $_ENV] as $source) {
            foreach ($source as $key => $value) {
                if (is_string($key) && is_string($value) && $key !== '' && ! str_starts_with($key, 'HTTP_')) {
                    $env[$key] = $value;
                }
            }
        }

        $home = $env['HOME'] ?? null;
        if (! is_string($home) || $home === '' || $home === '/') {
            if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
                $home = posix_getpwuid(posix_geteuid())['dir'] ?? null;
            }
            $home = is_string($home) && $home !== '' ? $home : '/Users/macbookpro';
        }

        $path = $env['PATH'] ?? '/usr/bin:/bin';
        $extraPath = [
            '/Users/macbookpro/.local/bin',
            '/usr/local/bin',
            '/opt/homebrew/bin',
            '/Applications/Google Chrome.app/Contents/MacOS',
        ];
        $path = implode(':', array_unique(array_filter(array_merge($extraPath, explode(':', $path)))));

        $env['HOME'] = $home;
        $env['USER'] = $env['USER'] ?? get_current_user();
        $env['TMPDIR'] = $env['TMPDIR'] ?? sys_get_temp_dir();
        $env['TMP'] = $env['TMP'] ?? sys_get_temp_dir();
        $env['TEMP'] = $env['TEMP'] ?? sys_get_temp_dir();
        $env['PATH'] = $path;
        $env['LANG'] = $env['LANG'] ?? 'en_US.UTF-8';
        $env['PLAYWRIGHT_BROWSERS_PATH'] = storage_path('app/playwright');
        $env['NODE_ENV'] = 'production';

        return $env;
    }

    private function shortError(string $stderr): string
    {
        if ($stderr === '') {
            return '';
        }

        if (preg_match('/browserType\.launch:([^\n]+)/', $stderr, $matches)) {
            return trim($matches[1]);
        }

        $lines = preg_split('/\R/', $stderr) ?: [];
        $first = trim((string) ($lines[0] ?? ''));

        return mb_substr($first, 0, 240);
    }

    private function nodeBinary(): string
    {
        foreach ([
            env('NODE_BINARY'),
            '/Users/macbookpro/.local/bin/node',
            '/usr/local/bin/node',
            '/opt/homebrew/bin/node',
            'node',
        ] as $candidate) {
            if (! $candidate) {
                continue;
            }

            if ($candidate === 'node' || is_executable($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('Node.js غير متوفر لتشغيل سكربت البحث.');
    }
}
