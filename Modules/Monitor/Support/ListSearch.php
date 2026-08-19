<?php

namespace Modules\Monitor\Support;

class ListSearch
{
    public static function normalize(?string $search): string
    {
        return trim($search ?? '');
    }

    /**
     * @param  array<int, string|null>  $fields
     */
    public static function matches(string $search, array $fields): bool
    {
        if ($search === '') {
            return true;
        }

        $needle = mb_strtolower($search);

        foreach ($fields as $field) {
            if ($field !== null && $field !== '' && str_contains(mb_strtolower((string) $field), $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $keys
     * @return array<int, array<string, mixed>>
     */
    public static function filterRows(array $rows, string $search, array $keys): array
    {
        if ($search === '') {
            return $rows;
        }

        return array_values(array_filter(
            $rows,
            fn (array $row) => self::matches($search, array_map(fn (string $key) => $row[$key] ?? '', $keys))
        ));
    }
}
