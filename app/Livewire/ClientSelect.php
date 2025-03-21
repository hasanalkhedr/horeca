<?php

namespace App\Livewire;

use Livewire\Component;

class ClientSelect extends Component
{
    public $model;          // Parent model (e.g., Company)
    public $dependentModel; // Dependent model (e.g., Client)
    public $foreignKey;     // Foreign key in the dependent model
    public $primaryKey;     // Primary key of the parent model
    public $parentField;    // Selected parent field value
    public $options = [];   // Options for the dependent select
    public $placeholder;    // Placeholder for the dependent select
    public $parentLabel;
    public $childLabel;
    public $child2Label;
    public $coordinatorId;  // Existing coordinator_id
    public $contactPerson;  // Existing contact_person

    public function mount(
        $model,
        $parentLabel,
        $childLabel,
        $child2Label,
        $dependentModel,
        $foreignKey,
        $primaryKey = 'id',
        $placeholder = 'Select an option',
        $parentField = null, // Existing company_id
        $coordinatorId = null, // Existing coordinator_id
        $contactPerson = null // Existing contact_person
    ) {
        $this->model = $model;
        $this->dependentModel = $dependentModel;
        $this->foreignKey = $foreignKey;
        $this->primaryKey = $primaryKey;
        $this->placeholder = $placeholder;
        $this->parentLabel = $parentLabel;
        $this->childLabel = $childLabel;
        $this->child2Label = $child2Label;
        $this->parentField = $parentField;
        $this->coordinatorId = $coordinatorId;
        $this->contactPerson = $contactPerson;

        // Initialize options if parentField is set
        if ($this->parentField) {
            $this->myupdatedParentField();
        }
    }

    public function myupdatedParentField()
    {
        if ($this->parentField) {
            $this->options = app($this->dependentModel)
                ->where($this->foreignKey, $this->parentField)
                ->get([$this->primaryKey, 'name']); // Adjust 'name' field as needed
        } else {
            $this->options = [];
        }
    }

    public function render()
    {
        return view('livewire.client-select');
    }
}
