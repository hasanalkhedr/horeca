<?php

namespace App\Imports;

use App\Models\Stand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StandsImport implements ToModel, WithHeadingRow, WithValidation
{

    protected $event_id;

    public function __construct($event_id){
        $this->event_id = $event_id;
    }
    public function rules(): array
    {
        return [
            '*.no' => 'required|string',
            '*.space' => 'required|decimal:0,3',
            '*.deductable' => 'required|boolean',
        ];
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Stand([
            'no' => $row['no'],
            'space' => $row['space'],
            'deductable' => $row['deductable'],
            'event_id' => $this->event_id,
        ]);
    }
}
