<?php

namespace App\Livewire;

use Vildanbina\LivewireWizard\Components\Step;
use Illuminate\Validation\Rule;

class General extends Step
{
    protected string $view = 'livewire.general';

    public function mount()
    {
        $this->mergeState([
            'name' => $this->model->name,
            'CODE' => $this->model->CODE,
            'country' => $this->model->country,
            'city' => $this->model->city,
            'address' => $this->model->address,
            'description' => $this->model->description,
            'start_date' => $this->model->start_date? $this->model->start_date->format('Y-m-d') : '',
            'end_date' =>$this->model->end_date ? $this->model->end_date->format('Y-m-d') : '',
            'apply_start_date' =>$this->model->apply_start_date ? $this->model->apply_start_date->format('Y-m-d') : '',
            'apply_deadline_date' =>$this->model->apply_deadline_date ? $this->model->apply_deadline_date->format('Y-m-d') : '',
            'total_space' => $this->model->total_space,
            'space_to_sell' => $this->model->space_to_sell,
            // 'free_space' => $this->model->free_space,
            // 'remaining_space_to_sell' => $this->model->remaining_space_to_sell,
            // 'remaining_free_space' => $this->model->remaining_free_space,
        ]);
    }

    public function icon(): string
    {
        return 'check';
    }

    public function save($state)
    {
        $event = $this->model;

        $event->name = $state['name'];
        $event->CODE = $state['CODE'];
        $event->country = $state['country'];
        $event->city = $state['city'];
        $event->address = $state['address'];
        $event->description = $state['description'];
        $event->start_date = $state['start_date'];
        $event->end_date = $state['end_date'];
        $event->apply_start_date = $state['apply_start_date'];
        $event->apply_deadline_date = $state['apply_deadline_date'];
        $event->total_space = $state['total_space'];
        $event->space_to_sell = $state['space_to_sell'];
        // $event->free_space = $state['free_space'];
        // $event->remaining_space_to_sell = $state['remaining_space_to_sell'];
        // $event->remaining_free_space = $state['remaining_free_space'];
        $event->free_space = 0;
        $event->remaining_space_to_sell = 0;
        $event->remaining_free_space = 0;
        //$event->save();
    }

    public function validate()
    {
        return [
            [
                'state.name' => ['required', Rule::unique('events', 'name')->ignoreModel($this->model)],
                'state.CODE' => ['required', Rule::unique('events', 'CODE')->ignoreModel($this->model)],
                'state.description' => ['nullable', ],
                'state.start_date' => ['required', 'date', 'after_or_equal:today' ],
                'state.end_date' => ['required', 'date', 'after_or_equal:state.start_date'],
                'state.apply_start_date' => ['required','date', 'before_or_equal:state.start_date'],
                'state.apply_deadline_date' => ['required','date', 'after_or_equal:today', 'after_or_equal:state.apply_start_date'],
                'state.total_space' => ['required','decimal:0,3'],
                'state.space_to_sell' => ['required','decimal:0,3'],
                /* 'state.free_space' => ['required','decimal:0,3',function ($attribute, $value, $fail) {
                    if ($value !== $this->model->total_space - $this->model->space_to_sell) {
                        dd($this->model , $this->model->space_to_sell,$value);
                        $fail("Space Fields don't match");
                    }
                },],*/
                // 'state.remaining_space_to_sell' => ['required',],
                // 'state.remaining_free_space' => ['required',],
            ],
            [],
            [
                'state.name' => __('Name'),
                'state.CODE' => __('CODE'),
                'state.description' => __('description'),
                'state.start_date' => __('start_date'),
                'state.end_date' => __('end_date'),
                'state.apply_start_date' => __('apply_start_date'),
                'state.apply_deadline_date' => __('apply_deadline_date'),
                'state.total_space' => __('total_space'),
                'state.space_to_sell' => __('space_to_sell'),
                // 'state.free_space' => __('free_space'),
                // 'state.remaining_space_to_sell' => __('remaining_space_to_sell'),
                // 'state.remaining_free_space' => __('remaining_free_space'),
            ],
        ];
    }

    public function title(): string
    {
        return __('Basic Event Info');
    }
}
