<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ExpenseImport implements ToArray
{

    protected array $processedData = [];

    public static function fields(): array
    {
        return array(
            array('id' => 'item_name', 'name' => __('modules.expenses.itemName'), 'required' => 'Yes',),
            array('id' => 'price', 'name' => __('app.price'), 'required' => 'Yes',),
            array('id' => 'purchase_date', 'name' => __('modules.expenses.purchaseDate'), 'required' => 'Yes',),
            array('id' => 'email', 'name' => __('modules.employees.employeeEmail'), 'required' => 'No',),
            array('id' => 'purchase_from', 'name' => __('modules.expenses.purchaseFrom'), 'required' => 'No',),
            array('id' => 'description', 'name' => __('app.description'), 'required' => 'No'),
            array('id' => 'bank_account', 'name' => __('app.menu.bankaccount'), 'required' => 'No'),
            array('id' => 'category', 'name' => __('modules.expenses.expenseCategory'), 'required' => 'No',),
        );
    }

    public function array(array $array): array
    {
        $header = $array[0] ?? [];
        $dataRows = array_slice($array, 1);

        $purchaseDateIndex = $this->findColumnIndex($header, [
            'Purchase Date',
            'purchase_date',
            'purchase date',
        ]);

        foreach ($dataRows as &$row) {
            if ($purchaseDateIndex !== false && isset($row[$purchaseDateIndex])) {
                $row[$purchaseDateIndex] = $this->convertExcelDateToString($row[$purchaseDateIndex]);
            }
        }
        unset($row);

        $this->processedData = [$header, ...$dataRows];

        return $array;
    }

    public function getProcessedData(): array
    {
        return $this->processedData;
    }

    private function findColumnIndex(array $header, array $names)
    {
        $normalizedNames = array_map(fn ($name) => strtolower(trim($name)), $names);

        foreach ($header as $index => $column) {
            if (in_array(strtolower(trim((string) $column)), $normalizedNames, true)) {
                return $index;
            }
        }

        return false;
    }

    private function convertExcelDateToString($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

}
