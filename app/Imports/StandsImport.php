<?php

namespace App\Imports;

use App\Models\Stand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Models\Settings\Category;
use Illuminate\Validation\Rule;

class StandsImport implements ToModel, WithHeadingRow, WithValidation
{

    protected $event_id;

    public function __construct($event_id)
    {
        $this->event_id = $event_id;
    }
    public function rules(): array
    {
        return [
            '*.no' => ['required', 'string', Rule::unique('stands')->where('event_id', $this->event_id)],
            '*.space' => 'required|decimal:0,3',
            '*.deductable' => 'required|boolean',
            '*.category' => 'required|string|exists:categories,name',
        ];
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $categoryName = strtolower($row['category']);
        $category = Category::whereRaw('LOWER(name) = ?', [$categoryName])->first();

        return new Stand([
            'no' => $row['no'],
            'space' => $row['space'],
            'deductable' => $row['deductable'],
            'event_id' => $this->event_id,
            'category_id' => $category->id,
        ]);
    }
}
