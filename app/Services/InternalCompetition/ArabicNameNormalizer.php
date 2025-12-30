<?php

namespace App\Services\InternalCompetition;

use Illuminate\Support\Str;

class ArabicNameNormalizer
{
    /**
     * Arabic character normalizations
     */
    protected array $arabicNormalizations = [
        // Alef variations → Alef
        'أ' => 'ا',
        'إ' => 'ا',
        'آ' => 'ا',
        'ٱ' => 'ا',

        // Taa Marbuta → Haa
        'ة' => 'ه',

        // Alef Maqsura → Yaa
        'ى' => 'ي',

        // Waw/Yaa with Hamza
        'ؤ' => 'و',
        'ئ' => 'ي',

        // Alef with Hamza below
        'ٳ' => 'ا',
    ];

    /**
     * Arabic diacritics (tashkeel) to remove
     */
    protected array $diacritics = [
        'ً', // Fathatan
        'ٌ', // Dammatan
        'ٍ', // Kasratan
        'َ', // Fatha
        'ُ', // Damma
        'ِ', // Kasra
        'ّ', // Shadda
        'ْ', // Sukun
        'ٰ', // Superscript Alef
        'ٓ', // Madda
        'ٔ', // Hamza Above
        'ٕ', // Hamza Below
    ];

    /**
     * Common Arabic prefixes to handle
     */
    protected array $prefixes = [
        'ال',   // Al (the)
        'أبو',  // Abu
        'أبي',  // Abi
        'ابو',  // Abu (without hamza)
        'ابن',  // Ibn
        'بن',   // Bin
        'عبد',  // Abd
    ];

    /**
     * Common Arabic-English name mappings
     */
    protected array $transliterations = [
        // محمد variations
        'mohammed' => 'محمد',
        'muhammad' => 'محمد',
        'mohamed' => 'محمد',
        'mohamad' => 'محمد',
        'mohd' => 'محمد',

        // أحمد variations
        'ahmed' => 'احمد',
        'ahmad' => 'احمد',

        // علي variations
        'ali' => 'علي',
        'aly' => 'علي',

        // عمر variations
        'omar' => 'عمر',
        'omer' => 'عمر',

        // خالد variations
        'khalid' => 'خالد',
        'khaled' => 'خالد',

        // عبدالله variations
        'abdullah' => 'عبدالله',
        'abdallah' => 'عبدالله',
        'abd allah' => 'عبدالله',

        // عبدالرحمن variations
        'abdulrahman' => 'عبدالرحمن',
        'abdul rahman' => 'عبدالرحمن',
        'abdelrahman' => 'عبدالرحمن',

        // فاطمة variations
        'fatima' => 'فاطمه',
        'fatma' => 'فاطمه',

        // سارة variations
        'sara' => 'ساره',
        'sarah' => 'ساره',

        // نورة variations
        'noura' => 'نوره',
        'nora' => 'نوره',

        // ريم variations
        'reem' => 'ريم',
        'rima' => 'ريم',

        // يوسف variations
        'youssef' => 'يوسف',
        'yousef' => 'يوسف',
        'yusuf' => 'يوسف',
        'josef' => 'يوسف',

        // حسن variations
        'hassan' => 'حسن',
        'hasan' => 'حسن',

        // حسين variations
        'hussein' => 'حسين',
        'hussain' => 'حسين',
        'hosein' => 'حسين',

        // إبراهيم variations
        'ibrahim' => 'ابراهيم',
        'ebrahim' => 'ابراهيم',
        'abraham' => 'ابراهيم',

        // سعود variations
        'saud' => 'سعود',
        'saoud' => 'سعود',

        // فيصل variations
        'faisal' => 'فيصل',
        'faysal' => 'فيصل',

        // سلمان variations
        'salman' => 'سلمان',
        'selman' => 'سلمان',

        // ناصر variations
        'nasser' => 'ناصر',
        'nasir' => 'ناصر',

        // منصور variations
        'mansour' => 'منصور',
        'mansoor' => 'منصور',
    ];

    /**
     * Normalize an Arabic/English name for comparison
     */
    public function normalize(string $name): string
    {
        $normalized = trim($name);

        // Convert to lowercase for English names
        $normalized = mb_strtolower($normalized);

        // Check if it's an English transliteration and convert to Arabic
        if ($this->isLatinScript($normalized)) {
            $normalized = $this->transliterateToArabic($normalized);
        }

        // Remove diacritics (tashkeel)
        $normalized = $this->removeDiacritics($normalized);

        // Normalize Arabic character variations
        $normalized = $this->normalizeArabicChars($normalized);

        // Handle common prefixes
        $normalized = $this->handlePrefixes($normalized);

        // Remove extra whitespace
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);

        return $normalized;
    }

    /**
     * Check if string contains Latin script
     */
    public function isLatinScript(string $text): bool
    {
        return preg_match('/[a-zA-Z]/', $text) === 1;
    }

    /**
     * Check if string contains Arabic script
     */
    public function isArabicScript(string $text): bool
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text) === 1;
    }

    /**
     * Remove Arabic diacritics (tashkeel)
     */
    public function removeDiacritics(string $text): string
    {
        // Remove using regex for Unicode range
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

        // Also remove from array (backup)
        foreach ($this->diacritics as $diacritic) {
            $text = str_replace($diacritic, '', $text);
        }

        return $text;
    }

    /**
     * Normalize Arabic character variations
     */
    public function normalizeArabicChars(string $text): string
    {
        return str_replace(
            array_keys($this->arabicNormalizations),
            array_values($this->arabicNormalizations),
            $text
        );
    }

    /**
     * Handle common Arabic prefixes
     */
    public function handlePrefixes(string $text): string
    {
        // Remove leading "ال" (the) if followed by more characters
        if (Str::startsWith($text, 'ال') && mb_strlen($text) > 3) {
            $text = mb_substr($text, 2);
        }

        // Normalize "عبد ال" to "عبدال" (remove space)
        $text = str_replace('عبد ال', 'عبدال', $text);
        $text = str_replace('عبد الله', 'عبدالله', $text);
        $text = str_replace('عبد الرحمن', 'عبدالرحمن', $text);

        return $text;
    }

    /**
     * Transliterate English name to Arabic
     */
    public function transliterateToArabic(string $name): string
    {
        $lowerName = mb_strtolower(trim($name));

        // Direct mapping
        if (isset($this->transliterations[$lowerName])) {
            return $this->transliterations[$lowerName];
        }

        // Try partial matches for compound names
        foreach ($this->transliterations as $english => $arabic) {
            if (Str::contains($lowerName, $english)) {
                $lowerName = str_replace($english, $arabic, $lowerName);
            }
        }

        return $lowerName;
    }

    /**
     * Calculate similarity between two names (0-100)
     */
    public function calculateSimilarity(string $name1, string $name2): float
    {
        $normalized1 = $this->normalize($name1);
        $normalized2 = $this->normalize($name2);

        // Exact match after normalization
        if ($normalized1 === $normalized2) {
            return 100.0;
        }

        // Levenshtein distance based similarity
        $maxLen = max(mb_strlen($normalized1), mb_strlen($normalized2));
        if ($maxLen === 0) {
            return 0.0;
        }

        $distance = levenshtein($normalized1, $normalized2);
        $similarity = (1 - ($distance / $maxLen)) * 100;

        return round(max(0, $similarity), 2);
    }

    /**
     * Check if two names are likely the same person
     */
    public function areSamePerson(string $name1, string $name2, float $threshold = 80.0): bool
    {
        return $this->calculateSimilarity($name1, $name2) >= $threshold;
    }

    /**
     * Find matching name from a list
     */
    public function findMatch(string $name, array $existingNames, float $threshold = 80.0): ?string
    {
        $normalizedName = $this->normalize($name);

        $bestMatch = null;
        $bestSimilarity = 0;

        foreach ($existingNames as $existingName) {
            $similarity = $this->calculateSimilarity($name, $existingName);

            if ($similarity >= $threshold && $similarity > $bestSimilarity) {
                $bestMatch = $existingName;
                $bestSimilarity = $similarity;
            }
        }

        return $bestMatch;
    }

    /**
     * Group similar names together
     */
    public function groupSimilarNames(array $names, float $threshold = 80.0): array
    {
        $groups = [];
        $processed = [];

        foreach ($names as $name) {
            if (in_array($name, $processed)) {
                continue;
            }

            $group = [$name];
            $processed[] = $name;

            foreach ($names as $otherName) {
                if ($name === $otherName || in_array($otherName, $processed)) {
                    continue;
                }

                if ($this->areSamePerson($name, $otherName, $threshold)) {
                    $group[] = $otherName;
                    $processed[] = $otherName;
                }
            }

            $groups[] = [
                'canonical' => $this->selectCanonicalName($group),
                'variations' => $group,
            ];
        }

        return $groups;
    }

    /**
     * Select the best canonical name from variations
     */
    public function selectCanonicalName(array $variations): string
    {
        if (count($variations) === 1) {
            return $variations[0];
        }

        // Prefer Arabic over transliterated names
        $arabicNames = array_filter($variations, fn ($n) => $this->isArabicScript($n));
        if (!empty($arabicNames)) {
            // Return the longest Arabic name (likely most complete)
            usort($arabicNames, fn ($a, $b) => mb_strlen($b) - mb_strlen($a));
            return $arabicNames[0];
        }

        // Return the most common/longest variation
        usort($variations, fn ($a, $b) => mb_strlen($b) - mb_strlen($a));
        return $variations[0];
    }

    /**
     * Add custom transliteration mapping
     */
    public function addTransliteration(string $english, string $arabic): self
    {
        $this->transliterations[mb_strtolower($english)] = $arabic;
        return $this;
    }

    /**
     * Get all transliterations
     */
    public function getTransliterations(): array
    {
        return $this->transliterations;
    }
}
