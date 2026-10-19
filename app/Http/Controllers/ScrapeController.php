<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ExcelExporter;
use App\Services\GoogleMapsScraper;
use App\Services\QueryClassifier;
use App\Services\SectorCompanyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ScrapeController extends Controller
{
    public function __invoke(
        Request $request,
        GoogleMapsScraper $scraper,
        ExcelExporter $exporter,
        QueryClassifier $classifier,
        SectorCompanyRepository $companies,
    ): StreamedResponse {
        $data = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:200'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->ensureUsagePeriod();

        @set_time_limit(7200);
        ini_set('max_execution_time', '7200');
        ignore_user_abort(true);

        $runId = str_replace('.', '', uniqid('scrape_', true));

        return response()->stream(function () use ($data, $scraper, $exporter, $classifier, $companies, $runId, $user) {
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');

            $send = function (array $payload) use ($runId): void {
                $payload['run_id'] = $payload['run_id'] ?? $runId;
                echo json_encode($payload, JSON_UNESCAPED_UNICODE)."\n";
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                flush();
            };

            $user->refresh();
            $user->ensureUsagePeriod();

            if (! $user->canUseSearch()) {
                $send([
                    'type' => 'error',
                    'message' => 'لا توجد باقة مرتبطة بحسابك. تواصل مع الأدمن لتفعيل باقة.',
                    'usage' => $user->usageSummary(),
                ]);

                return;
            }

            if ($user->hasReachedLimit()) {
                $limit = number_format((int) $user->monthlyLimit());
                $send([
                    'type' => 'error',
                    'message' => "وصلت للحد الشهري لباقتك ({$limit} سجل). رقِّ الباقة أو انتظر بداية الشهر القادم.",
                    'usage' => $user->usageSummary(),
                ]);

                return;
            }

            $meta = $classifier->classify($data['query']);
            $sector = $companies->ensureSector($meta['sector'], $meta['sector_ar']);
            $storedAtStart = $companies->countInGovernorate($sector['table'], $meta['governorate']);

            $send([
                'type' => 'progress',
                'phase' => 'classify',
                'message' => "القطاع: {$sector['slug']} | المحافظة: {$meta['governorate']} | في القاعدة الآن: {$storedAtStart}",
                'found' => 0,
                'listed' => 0,
                'total' => 0,
                'percent' => 1,
                'inserted' => 0,
                'existing' => 0,
                'stored' => $storedAtStart,
                'sector' => $sector['slug'],
                'governorate' => $meta['governorate'],
                'can_stop' => true,
                'usage' => $user->usageSummary(),
            ]);

            $savedRows = [];
            $inserted = 0;
            $existing = 0;
            $stopped = false;
            $limitHit = false;

            try {
                $scraper->search(
                    $data['query'],
                    (int) config('services.maps.max_results', 0),
                    function (array $event) use (
                        $send,
                        $companies,
                        $sector,
                        $meta,
                        $storedAtStart,
                        $user,
                        $runId,
                        &$savedRows,
                        &$inserted,
                        &$existing,
                        &$stopped,
                        &$limitHit
                    ) {
                        if (($event['phase'] ?? null) === 'stopping') {
                            $stopped = true;
                        }

                        if (($event['type'] ?? null) === 'place' && is_array($event['place'] ?? null)) {
                            if ($user->hasReachedLimit()) {
                                $limitHit = true;
                                $stopped = true;
                                GoogleMapsScraper::requestStop($runId);

                                return;
                            }

                            $row = $companies->upsertOne(
                                $sector['table'],
                                $meta['governorate'],
                                $event['place']
                            );

                            if ($row) {
                                $row['sector'] = $sector['slug'];
                                $savedRows[] = $row;

                                if ($row['status'] === 'new') {
                                    $inserted++;
                                    $user->recordUsage(1);

                                    if ($user->hasReachedLimit()) {
                                        $limitHit = true;
                                        $stopped = true;
                                        GoogleMapsScraper::requestStop($runId);
                                    }
                                } else {
                                    $existing++;
                                }

                                $send([
                                    'type' => 'progress',
                                    'phase' => 'database',
                                    'message' => ($row['status'] === 'new' ? 'تم تسجيل: ' : 'موجودة مسبقًا: ').$row['name'],
                                    'found' => count($savedRows),
                                    'inserted' => $inserted,
                                    'existing' => $existing,
                                    'stored' => $storedAtStart + $inserted,
                                    'current' => $row['name'],
                                    'percent' => isset($event['percent']) ? $event['percent'] : null,
                                    'sector' => $sector['slug'],
                                    'governorate' => $meta['governorate'],
                                    'can_stop' => true,
                                    'usage' => $user->usageSummary(),
                                ]);
                            }

                            return;
                        }

                        if (($event['type'] ?? null) === 'progress') {
                            $event['inserted'] = $inserted;
                            $event['existing'] = $existing;
                            $event['saved'] = count($savedRows);
                            $event['stored'] = $storedAtStart + $inserted;
                            $event['can_stop'] = ! $stopped;
                            $event['usage'] = $user->usageSummary();
                            $send($event);
                        }
                    },
                    $runId
                );
            } catch (Throwable $e) {
                report($e);

                if ($savedRows === []) {
                    $send([
                        'type' => 'error',
                        'message' => $e->getMessage() ?: 'حدث خطأ أثناء البحث.',
                        'usage' => $user->usageSummary(),
                    ]);

                    return;
                }
            }

            if ($savedRows === []) {
                $send([
                    'type' => 'error',
                    'message' => $stopped
                        ? ($limitHit
                            ? 'تم إيقاف البحث لأنك وصلت لحد الباقة الشهري.'
                            : 'تم إيقاف البحث قبل العثور على شركات.')
                        : 'لم يتم العثور على نتائج لهذا البحث.',
                    'usage' => $user->usageSummary(),
                ]);

                return;
            }

            try {
                $send([
                    'type' => 'progress',
                    'phase' => 'excel',
                    'message' => $stopped
                        ? ($limitHit
                            ? 'وصلت لحد الباقة. جاري إنشاء Excel بما تم جمعه...'
                            : 'تم الإيقاف. جاري إنشاء ملف Excel بما تم جمعه...')
                        : 'جاري إنشاء ملف Excel...',
                    'found' => count($savedRows),
                    'inserted' => $inserted,
                    'existing' => $existing,
                    'stored' => $storedAtStart + $inserted,
                    'percent' => 99,
                    'can_stop' => false,
                    'usage' => $user->usageSummary(),
                ]);

                $file = $exporter->export(
                    $savedRows,
                    $data['query'],
                    $sector['slug'],
                    $meta['governorate']
                );

                $send([
                    'type' => 'result',
                    'message' => $limitHit
                        ? 'تم إيقاف الجمع عند حد الباقة، وحُفظت البيانات التي جُمعت.'
                        : ($stopped
                            ? 'تم إيقاف البحث، وتم حفظ البيانات التي جُمعت.'
                            : 'تم استخراج البيانات وحفظها بنجاح.'),
                    'stopped' => $stopped,
                    'limit_hit' => $limitHit,
                    'count' => count($savedRows),
                    'inserted' => $inserted,
                    'existing' => $existing,
                    'sector' => $sector['slug'],
                    'governorate' => $meta['governorate'],
                    'table' => $sector['table'],
                    'query' => $data['query'],
                    'filename' => $file['filename'],
                    'download_url' => '/storage/'.$file['relative'],
                    'preview' => array_slice($savedRows, 0, 5),
                    'percent' => 100,
                    'can_stop' => false,
                    'usage' => $user->usageSummary(),
                ]);
            } catch (Throwable $e) {
                report($e);
                $send([
                    'type' => 'error',
                    'message' => 'تم حفظ '.count($savedRows).' نتيجة في قاعدة البيانات لكن فشل Excel: '.$e->getMessage(),
                    'usage' => $user->usageSummary(),
                ]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function stop(Request $request): JsonResponse
    {
        $data = $request->validate([
            'run_id' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9_]+$/'],
        ]);

        GoogleMapsScraper::requestStop($data['run_id']);

        return response()->json(['ok' => true]);
    }
}
