<?php

namespace App\Services;

class QueryClassifier
{
    /** @var array<string, string> */
    private array $sectorMap = [
        'عقاري' => 'realstate',
        'عقارية' => 'realstate',
        'عقارات' => 'realstate',
        'اثاث' => 'furniture',
        'أثاث' => 'furniture',
        'مطاعم' => 'restaurants',
        'مطعم' => 'restaurants',
        'كافيه' => 'cafes',
        'مقاهي' => 'cafes',
        'صيدليات' => 'pharmacies',
        'صيدلية' => 'pharmacies',
        'عيادات' => 'clinics',
        'عيادة' => 'clinics',
        'اسنان' => 'dental',
        'أسنان' => 'dental',
        'مدارس' => 'schools',
        'مدرسة' => 'schools',
        'فنادق' => 'hotels',
        'فندق' => 'hotels',
        'محام' => 'lawyers',
        'محاماة' => 'lawyers',
        'نظافة' => 'cleaning',
        'سيارات' => 'cars',
        'ورش' => 'workshops',
        'ملابس' => 'clothes',
        'سوبرماركت' => 'supermarkets',
        'بقالة' => 'groceries',
        'مستشفيات' => 'hospitals',
        'مستشفى' => 'hospitals',
        'صالون' => 'salons',
        'تجميل' => 'beauty',
        'سباكة' => 'plumbing',
        'كهرباء' => 'electrical',
        'تكييف' => 'ac_services',
        'مقاولات' => 'contracting',
        'سياحة' => 'travel',
        'سفر' => 'travel',
        'تأمين' => 'insurance',
        'بنوك' => 'banks',
        'بنك' => 'banks',
        'محلات' => 'shops',
        'شركات' => 'companies',
    ];

    /** @var array<string, string> */
    private array $governorateMap = [
        'الرياض' => 'Riyadh',
        'رياض' => 'Riyadh',
        'جدة' => 'Jeddah',
        'جده' => 'Jeddah',
        'مكة' => 'Makkah',
        'مكه' => 'Makkah',
        'المدينة' => 'Madinah',
        'المدينه' => 'Madinah',
        'الدمام' => 'Dammam',
        'الخبر' => 'Khobar',
        'الظهران' => 'Dhahran',
        'الطائف' => 'Taif',
        'تبوك' => 'Tabuk',
        'أبها' => 'Abha',
        'ابها' => 'Abha',
        'حائل' => 'Hail',
        'جازان' => 'Jazan',
        'نجران' => 'Najran',
        'الجبيل' => 'Jubail',
        'ينبع' => 'Yanbu',
        'القصيم' => 'Qassim',
        'بريدة' => 'Buraydah',
        'الأحساء' => 'AlAhsa',
        'الاحساء' => 'AlAhsa',
    ];

    /**
     * @return array{sector: string, sector_ar: string, governorate: string, governorate_ar: string|null}
     */
    public function classify(string $query): array
    {
        $query = trim($query);

        $governorate = 'Unknown';
        $governorateAr = null;
        foreach ($this->governorateMap as $ar => $en) {
            if (str_contains($query, $ar)) {
                $governorate = $en;
                $governorateAr = $ar;
                break;
            }
        }

        $sector = 'general';
        $sectorAr = $query;
        foreach ($this->sectorMap as $ar => $en) {
            if (str_contains($query, $ar)) {
                $sector = $en;
                $sectorAr = $ar;
                break;
            }
        }

        // If only generic words matched, try a better slug from remaining keywords.
        if (in_array($sector, ['shops', 'companies', 'general'], true)) {
            $cleaned = $query;
            foreach (array_keys($this->governorateMap) as $city) {
                $cleaned = str_replace($city, ' ', $cleaned);
            }
            $cleaned = preg_replace('/\b(في|محلات|شركات|near|in)\b/u', ' ', $cleaned) ?? $cleaned;
            $cleaned = trim(preg_replace('/\s+/u', ' ', $cleaned) ?? $cleaned);

            if ($cleaned !== '') {
                foreach ($this->sectorMap as $ar => $en) {
                    if (str_contains($cleaned, $ar)) {
                        $sector = $en;
                        $sectorAr = $ar;
                        break;
                    }
                }

                if (in_array($sector, ['shops', 'companies', 'general'], true)) {
                    $sector = $this->slugify($cleaned) ?: 'general';
                    $sectorAr = $cleaned;
                }
            }
        }

        return [
            'sector' => $sector,
            'sector_ar' => $sectorAr,
            'governorate' => $governorate,
            'governorate_ar' => $governorateAr,
        ];
    }

    private function slugify(string $text): string
    {
        $map = [
            'ا' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'a', 'ب' => 'b', 'ت' => 't', 'ث' => 'th',
            'ج' => 'j', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'dh', 'ر' => 'r', 'ز' => 'z',
            'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'd', 'ط' => 't', 'ظ' => 'z', 'ع' => 'a',
            'غ' => 'gh', 'ف' => 'f', 'ق' => 'q', 'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'ه' => 'h', 'و' => 'w', 'ي' => 'y', 'ى' => 'a', 'ة' => 'a', 'ء' => 'a', 'ؤ' => 'w',
            'ئ' => 'y', ' ' => '_',
        ];

        $out = '';
        $chars = preg_split('//u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($chars as $char) {
            if (isset($map[$char])) {
                $out .= $map[$char];
            } elseif (preg_match('/[a-z0-9]/', $char)) {
                $out .= $char;
            } elseif ($char === '_' || $char === '-') {
                $out .= '_';
            }
        }

        $out = trim(preg_replace('/_+/', '_', $out) ?? $out, '_');

        return substr($out, 0, 40);
    }
}
