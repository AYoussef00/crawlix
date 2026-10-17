<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SectorCompanyRepository
{
    /**
     * Ensure sector registry + dynamic companies table exist.
     *
     * @return array{slug: string, table: string}
     */
    public function ensureSector(string $slug, ?string $nameAr = null): array
    {
        $slug = $this->safeSlug($slug);
        $table = $slug; // e.g. realstate

        if (! Schema::hasTable('sectors')) {
            Schema::create('sectors', function (Blueprint $blueprint) {
                $blueprint->id();
                $blueprint->string('slug')->unique();
                $blueprint->string('name_ar')->nullable();
                $blueprint->string('table_name')->unique();
                $blueprint->timestamps();
            });
        }

        $existingSector = DB::table('sectors')->where('slug', $slug)->first();
        if ($existingSector) {
            DB::table('sectors')->where('slug', $slug)->update([
                'name_ar' => $nameAr ?: $existingSector->name_ar,
                'table_name' => $table,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('sectors')->insert([
                'slug' => $slug,
                'name_ar' => $nameAr,
                'table_name' => $table,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $blueprint) {
                $blueprint->id();
                $blueprint->string('name');
                $blueprint->string('email')->nullable();
                $blueprint->string('phone')->nullable();
                $blueprint->string('governorate')->index();
                $blueprint->string('phone_normalized')->nullable()->index();
                $blueprint->string('name_normalized')->index();
                $blueprint->timestamps();
            });
        }

        return ['slug' => $slug, 'table' => $table];
    }

    public function countInGovernorate(string $table, string $governorate): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) DB::table($table)->where('governorate', $governorate)->count();
    }

    /**
     * @return list<array{
     *     slug: string,
     *     name_ar: string|null,
     *     table: string,
     *     total: int,
     *     with_email: int,
     *     with_phone: int,
     *     governorates: list<array{name: string, total: int}>
     * }>
     */
    public function dashboardStats(): array
    {
        if (! Schema::hasTable('sectors')) {
            return [];
        }

        $sectors = DB::table('sectors')->orderBy('slug')->get();
        $stats = [];

        foreach ($sectors as $sector) {
            $table = (string) $sector->table_name;
            $total = 0;
            $withEmail = 0;
            $withPhone = 0;
            $governorates = [];

            if (Schema::hasTable($table)) {
                $total = (int) DB::table($table)->count();
                $withEmail = (int) DB::table($table)->whereNotNull('email')->where('email', '!=', '')->count();
                $withPhone = (int) DB::table($table)->whereNotNull('phone')->where('phone', '!=', '')->count();

                $governorates = DB::table($table)
                    ->select('governorate', DB::raw('COUNT(*) as total'))
                    ->groupBy('governorate')
                    ->orderByDesc('total')
                    ->get()
                    ->map(fn ($row) => [
                        'name' => (string) $row->governorate,
                        'total' => (int) $row->total,
                    ])
                    ->all();
            }

            $stats[] = [
                'slug' => (string) $sector->slug,
                'name_ar' => $sector->name_ar ? (string) $sector->name_ar : null,
                'table' => $table,
                'total' => $total,
                'with_email' => $withEmail,
                'with_phone' => $withPhone,
                'governorates' => $governorates,
            ];
        }

        return $stats;
    }

    /**
     * Save one company immediately. Returns null if invalid.
     *
     * @param  array{name?: string, email?: string|null, phone?: string|null}  $company
     * @return array{name: string, email: string|null, phone: string|null, governorate: string, status: string}|null
     */
    public function upsertOne(string $table, string $governorate, array $company): ?array
    {
        $name = trim((string) ($company['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        $email = trim((string) ($company['email'] ?? '')) ?: null;
        $phone = trim((string) ($company['phone'] ?? '')) ?: null;
        $phoneNormalized = $this->normalizePhone($phone);
        $nameNormalized = $this->normalizeName($name);

        $current = DB::table($table)
            ->where('governorate', $governorate)
            ->when(
                $phoneNormalized !== '',
                fn ($q) => $q->where('phone_normalized', $phoneNormalized),
                fn ($q) => $q->where('name_normalized', $nameNormalized)
            )
            ->first();

        if ($current) {
            $update = [];
            if (empty($current->email) && $email) {
                $update['email'] = $email;
            }
            if (empty($current->phone) && $phone) {
                $update['phone'] = $phone;
                $update['phone_normalized'] = $phoneNormalized;
            }
            if ($update !== []) {
                $update['updated_at'] = now();
                DB::table($table)->where('id', $current->id)->update($update);
            }

            return [
                'name' => $name,
                'email' => $email ?: ($current->email ?: null),
                'phone' => $phone ?: ($current->phone ?: null),
                'governorate' => $governorate,
                'status' => 'existing',
            ];
        }

        DB::table($table)->insert([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'governorate' => $governorate,
            'phone_normalized' => $phoneNormalized !== '' ? $phoneNormalized : null,
            'name_normalized' => $nameNormalized,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'governorate' => $governorate,
            'status' => 'new',
        ];
    }

    /**
     * @param  list<array{name: string, email: string|null, phone: string|null}>  $companies
     * @return array{
     *     inserted: list<array{name: string, email: string|null, phone: string|null, governorate: string, status: string}>,
     *     existing: list<array{name: string, email: string|null, phone: string|null, governorate: string, status: string}>,
     *     all: list<array{name: string, email: string|null, phone: string|null, governorate: string, status: string}>
     * }
     */
    public function syncCompanies(string $table, string $governorate, array $companies): array
    {
        $inserted = [];
        $existing = [];

        foreach ($companies as $company) {
            $row = $this->upsertOne($table, $governorate, $company);
            if (! $row) {
                continue;
            }

            if ($row['status'] === 'new') {
                $inserted[] = $row;
            } else {
                $existing[] = $row;
            }
        }

        return [
            'inserted' => $inserted,
            'existing' => $existing,
            'all' => array_merge($inserted, $existing),
        ];
    }

    private function safeSlug(string $slug): string
    {
        $slug = Str::lower($slug);
        $slug = preg_replace('/[^a-z0-9_]+/', '_', $slug) ?? 'general';
        $slug = trim($slug, '_');

        if ($slug === '' || preg_match('/^\d/', $slug)) {
            $slug = 'sector_'.$slug;
        }

        // Avoid colliding with core Laravel tables.
        $reserved = ['users', 'migrations', 'jobs', 'cache', 'sessions', 'sectors', 'password_reset_tokens'];
        if (in_array($slug, $reserved, true)) {
            $slug = 'biz_'.$slug;
        }

        return substr($slug, 0, 48) ?: 'general';
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '966')) {
            return substr($digits, -9);
        }

        if (str_starts_with($digits, '0') && strlen($digits) >= 9) {
            return substr($digits, -9);
        }

        return substr($digits, -9);
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return $name;
    }
}
