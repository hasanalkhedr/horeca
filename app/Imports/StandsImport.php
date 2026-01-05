<?php

namespace App\Imports;

use App\Models\Stand;
use App\Models\Settings\Category;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class StandsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $event_id;
    protected $createdCount = 0;
    protected $updatedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function __construct($event_id)
    {
        $this->event_id = $event_id;
    }

    public function rules(): array
    {
        return [
            '*.no' => ['required', 'string'],
            '*.space' => 'required|numeric|min:0',
            '*.deductable' => 'required|boolean',
            '*.category' => 'required|string|exists:categories,name',
            '*.status' => 'nullable|in:Available,Sold,Reserved',
        ];
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $processedCount = 0;

        foreach ($rows as $row) {
            $processedCount++;

            try {
                $categoryName = strtolower($row['category'] ?? '');
                $category = Category::whereRaw('LOWER(name) = ?', [$categoryName])->first();

                if (!$category) {
                    $this->errors[] = "Row {$processedCount}: Category '{$row['category']}' not found";
                    continue;
                }

                // Convert boolean values properly
                $deductable = filter_var($row['deductable'] ?? false, FILTER_VALIDATE_BOOLEAN);

                // Set default status if not provided
                $status = $row['status'] ?? 'Available';

                // Check if stand already exists
                $existingStand = Stand::where('event_id', $this->event_id)
                    ->where('no', $row['no'])
                    ->first();

                if ($existingStand) {
                    // Update existing stand
                    $existingStand->update([
                        'space' => $row['space'],
                        'deductable' => $deductable,
                        'category_id' => $category->id,
                        'status' => $status,
                    ]);

                    $this->updatedCount++;
                } else {
                    // Create new stand
                    Stand::create([
                        'no' => $row['no'],
                        'space' => $row['space'],
                        'deductable' => $deductable,
                        'category_id' => $category->id,
                        'status' => $status,
                        'event_id' => $this->event_id,
                    ]);

                    $this->createdCount++;
                }

            } catch (\Exception $e) {
                $this->errors[] = "Row {$processedCount}: " . $e->getMessage();
            }
        }
    }

    // Getter methods for statistics
    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
