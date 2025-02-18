<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Report;
use App\Models\Event;

class ReportBuilder extends Component
{
    public $report;
    //public $selectedComponents = []; // Stores selected components
    public $reportName = ''; // Optional: Report name
    public $event_id;

   // public string $bankAccount;
    public function mount($report = null)
    {
        if ($report) {
            $report = new Report([
                'name' => 'Event Name',
                'components' => $this->selectedComponents
            ]);
        } else {
            $this->report = $report;
        }
        $this->selectedComponents = [];
    }
    // Save the report template
    public $message = '';
    public $messageType = ''; // 'success' or 'error'

    public function saveReport()
    {
        $this->validate([
            'reportName' => 'required|string|min:3',
            'event_id' => 'required|exists:events,id'
        ]);
        if (empty($this->selectedComponents)) {
            $this->message = 'Please select at least one component.';
            $this->messageType = 'error';
            return;
        }

        // Save the selected components and their order
        $this->report = Report::create([
            'name' => $this->reportName,
            'components' => $this->selectedComponents,
            'event_id' =>$this->event_id,
            'bank_account' => $this->bankAccount
        ]);
        $this->message = 'Report template saved successfully! ID: ' . $this->report->id;
        $this->messageType = 'success';
        return redirect()->route('reports.index');
    }

    public function render()
    {
        $events = Event::all();
        return view('livewire.report-builder', compact('events'))
            ->layout('components.layouts.builder'); // Use your custom layout
    }
    public $selectedComponents = [

    ];

    public function updateSort($order)
    {
        $this->selectedComponents = array_values($order);
        //        $order;
    }

    public function removeComponent($component)
    {
        $this->selectedComponents = array_values(array_diff($this->selectedComponents, [$component]));
    }

    public function addComponent($component)
{
    if (!in_array($component, $this->selectedComponents)) {
        $this->selectedComponents[] = $component;
    }
}
    public string $paymentMethod = '';
    public string $bankAccount = '';
    public string $bankNameAddress = '';
    public string $swiftCode = '';
    public string $iban = '';
}






