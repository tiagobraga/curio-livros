<?php

namespace App\Services;

use App\Models\Library;
use App\Models\CentralItem;
use App\Models\LocalCopy;
use Illuminate\Support\Str;

class CallNumberService
{
    /**
     * Standard book fields available for call number generation.
     */
    public static function getStandardFields(): array
    {
        return [
            ['key' => 'title', 'name' => 'Título da obra', 'source' => 'standard', 'sample' => 'My Book Title'],
            ['key' => 'author', 'name' => 'Autor', 'source' => 'standard', 'sample' => 'Aronofsky'],
            ['key' => 'publisher', 'name' => 'Editora', 'source' => 'standard', 'sample' => 'Companhia'],
            ['key' => 'isbn', 'name' => 'ISBN', 'source' => 'standard', 'sample' => '9788535902778'],
            ['key' => 'publication_year', 'name' => 'Ano de publicação', 'source' => 'standard', 'sample' => '2007'],
            ['key' => 'pages', 'name' => 'Número de páginas', 'source' => 'standard', 'sample' => '320'],
            ['key' => 'subjects', 'name' => 'Assuntos', 'source' => 'standard', 'sample' => 'Che Guevara, Biografia'],
            ['key' => 'description', 'name' => 'Sinopse', 'source' => 'standard', 'sample' => 'Grande biografia histórica.'],
        ];
    }

    /**
     * Generate call number based on library format, book data, and custom field values.
     */
    public function generate(
        Library $library,
        $bookOrData,
        array $customValues = [],
        bool $isPermanentStore = false
    ): string {
        $format = $library->getCallNumberFormat();
        if (empty($format)) {
            return '';
        }

        $bookData = $this->extractBookData($bookOrData);
        $parts = [];

        foreach ($format as $element) {
            $type = $element['type'] ?? 'field';

            if ($type === 'static') {
                $val = (string)($element['value'] ?? '');
                if ($val !== '') {
                    $parts[] = ['is_static' => true, 'val' => $val];
                }
                continue;
            }

            if ($type === 'field') {
                $source = $element['source'] ?? 'standard';
                $key = $element['key'] ?? '';
                $length = !empty($element['length']) && is_numeric($element['length']) ? (int)$element['length'] : null;
                $casing = $element['casing'] ?? 'lower'; // default lower as requested

                $rawValue = '';
                if ($source === 'standard') {
                    $rawValue = $bookData[$key] ?? '';
                } elseif ($source === 'custom') {
                    // Check if field definition has auto_increment
                    $customFieldDef = $this->findCustomFieldDef($library, $key);
                    $isAutoIncrement = $customFieldDef && !empty($customFieldDef['auto_increment']);

                    if (isset($customValues[$key]) && $customValues[$key] !== '') {
                        $rawValue = (string)$customValues[$key];
                        if ($isAutoIncrement && is_numeric($rawValue) && $isPermanentStore) {
                            $library->advanceAutoIncrementCounter($key, (int)$rawValue);
                        }
                    } elseif ($isAutoIncrement) {
                        $nextVal = $library->getNextAutoIncrementValue($key);
                        $rawValue = (string)$nextVal;
                        if ($isPermanentStore) {
                            $library->advanceAutoIncrementCounter($key, $nextVal);
                        }
                    }
                }

                $processed = $this->processFieldValue($rawValue, $length, $casing);
                if ($processed !== '') {
                    $parts[] = ['is_static' => false, 'val' => $processed];
                }
            }
        }

        return $this->joinParts($parts);
    }

    /**
     * Preview generation with sample data (in-memory, no counter advancing).
     */
    public function preview(array $format, array $sampleData = [], array $customFields = []): string
    {
        if (empty($format)) {
            return '';
        }

        $parts = [];
        $sampleBook = array_merge([
            'title' => 'My Book Title',
            'author' => 'Aronofsky',
            'publisher' => 'Companhia',
            'isbn' => '9788535902778',
            'publication_year' => '2007',
            'pages' => '320',
            'subjects' => 'Che Guevara, Biografia',
            'description' => 'Biografia de liderança histórica.',
        ], $sampleData);

        foreach ($format as $element) {
            $type = $element['type'] ?? 'field';

            if ($type === 'static') {
                $val = (string)($element['value'] ?? '');
                if ($val !== '') {
                    $parts[] = ['is_static' => true, 'val' => $val];
                }
                continue;
            }

            if ($type === 'field') {
                $source = $element['source'] ?? 'standard';
                $key = $element['key'] ?? '';
                $length = !empty($element['length']) && is_numeric($element['length']) ? (int)$element['length'] : null;
                $casing = $element['casing'] ?? 'lower';

                $rawValue = '';
                if ($source === 'standard') {
                    $rawValue = $sampleBook[$key] ?? '';
                } elseif ($source === 'custom') {
                    $rawValue = (string)($sampleData[$key] ?? '001');
                }

                $processed = $this->processFieldValue($rawValue, $length, $casing);
                if ($processed !== '') {
                    $parts[] = ['is_static' => false, 'val' => $processed];
                }
            }
        }

        return $this->joinParts($parts);
    }

    /**
     * Recalculate call numbers for all local copies registered in a specific library.
     */
    public function recalculateForLibrary(Library $library): int
    {
        if (!$library->hasCallNumberFormat()) {
            return 0;
        }

        $copies = LocalCopy::where('library_id', $library->id)
            ->with('centralItem')
            ->get();

        $count = 0;
        foreach ($copies as $copy) {
            if (!$copy->centralItem) {
                continue;
            }

            $customValues = is_array($copy->custom_fields_values) ? $copy->custom_fields_values : [];
            $newCallNumber = $this->generate($library, $copy->centralItem, $customValues, false);

            if ($newCallNumber !== '') {
                $copy->call_number = $newCallNumber;
                $copy->save();
                $count++;
            }
        }

        return $count;
    }

    /**
     * Extract normalized book array from CentralItem or raw array.
     */
    protected function extractBookData($bookOrData): array
    {
        if ($bookOrData instanceof CentralItem) {
            $meta = is_array($bookOrData->metadata) ? $bookOrData->metadata : [];
            $subjects = $meta['subjects'] ?? [];
            if (is_array($subjects)) {
                $subjectsStr = implode(', ', $subjects);
            } else {
                $subjectsStr = (string)$subjects;
            }

            return [
                'title' => $bookOrData->title,
                'author' => $meta['author'] ?? '',
                'publisher' => $meta['publisher'] ?? '',
                'isbn' => $bookOrData->isbn ?: ($meta['isbn'] ?? ''),
                'publication_year' => $meta['publication_year'] ?? '',
                'pages' => $meta['pages'] ?? '',
                'subjects' => $subjectsStr,
                'description' => $meta['description'] ?? '',
            ];
        }

        if (is_array($bookOrData)) {
            $subjects = $bookOrData['subjects'] ?? [];
            if (is_array($subjects)) {
                $subjectsStr = implode(', ', $subjects);
            } else {
                $subjectsStr = (string)$subjects;
            }

            return [
                'title' => $bookOrData['title'] ?? '',
                'author' => $bookOrData['author'] ?? '',
                'publisher' => $bookOrData['publisher'] ?? '',
                'isbn' => $bookOrData['isbn'] ?? '',
                'publication_year' => $bookOrData['publication_year'] ?? '',
                'pages' => $bookOrData['pages'] ?? '',
                'subjects' => $subjectsStr,
                'description' => $bookOrData['description'] ?? '',
            ];
        }

        return [];
    }

    /**
     * Process raw field text: unaccent, trim, slice, and casing.
     */
    protected function processFieldValue(string $val, ?int $length, string $casing): string
    {
        $val = trim($val);
        if ($val === '') {
            return '';
        }

        // Remove accents (transliterate to ascii)
        $clean = Str::ascii($val);

        // Remove special punctuation characters from word boundaries if needed, but keep alphanumeric
        // If length is set, take first N characters
        if ($length !== null && $length > 0) {
            $clean = mb_substr($clean, 0, $length, 'UTF-8');
        }

        if ($casing === 'lower') {
            $clean = mb_strtolower($clean, 'UTF-8');
        } elseif ($casing === 'upper') {
            $clean = mb_strtoupper($clean, 'UTF-8');
        }

        return trim($clean);
    }

    /**
     * Join parts cleanly, preventing leading/trailing or duplicated consecutive separators.
     */
    protected function joinParts(array $parts): string
    {
        $result = '';
        $lastWasStatic = false;

        foreach ($parts as $p) {
            if ($p['is_static']) {
                if ($result !== '' && !$lastWasStatic) {
                    $result .= $p['val'];
                    $lastWasStatic = true;
                }
            } else {
                $result .= $p['val'];
                $lastWasStatic = false;
            }
        }

        // Trim trailing static separators if any
        return preg_replace('/[\s\-\/\.]+$/u', '', $result);
    }

    /**
     * Helper to find a custom field definition by key in a library.
     */
    protected function findCustomFieldDef(Library $library, string $key): ?array
    {
        $fields = $library->getCustomFields();
        foreach ($fields as $field) {
            if (($field['key'] ?? '') === $key) {
                return $field;
            }
        }
        return null;
    }
}
