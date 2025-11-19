<?php

namespace App\Imports;

use App\Models\Stand;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use App\Models\Settings\Category;
use Illuminate\Validation\Rule;

class StandsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $event_id;

    public function __construct($event_id)
    {
        $this->event_id = $event_id;
    }

    public function rules(): array
    {
        return [
            '*.no' => ['required', 'string'],
            '*.space' => 'required|decimal:0,3',
            '*.deductable' => 'required|boolean',
            '*.category' => 'required|string|exists:categories,name',
        ];
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $categoryName = strtolower($row['category']);
            $category = Category::whereRaw('LOWER(name) = ?', [$categoryName])->first();

            // Convert boolean values properly
            $deductable = filter_var($row['deductable'], FILTER_VALIDATE_BOOLEAN);

            Stand::updateOrCreate(
                [
                    'no' => $row['no'],
                    'event_id' => $this->event_id,
                ],
                [
                    'space' => $row['space'],
                    'deductable' => $deductable,
                    'category_id' => $category->id,
                ]
            );
        }
    }
}
