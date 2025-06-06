<?php

namespace App\Livewire;

use App\Models\Settings\Currency;
use Livewire\Component;
use App\Models\Report;
use App\Models\Event;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class ReportBuilder extends Component
{
    use WithFileUploads;
    public ?Report $report = null;
    public $selectedComponents = [];
    public $reportName = ''; // Optional: Report name
    public $event_id;
    public Event $event;
    public $message = '';
    public $messageType = ''; // 'success' or 'error'
    public string $paymentMethod = '';
    public string $bankAccount = '';
    public string $bankNameAddress = '';
    public string $swiftCode = '';
    public string $iban = '';
    public bool $with_logo = false;
    public bool $showCategories = false;
    public bool $special_price = false;
    public string $with_options = '';
    public string $logo_path = '';
    public $logo_image;
    public $currency_id;
    public Currency $currency;

    public function mount($report = null)
    {
        if ($report == null) {
            $report = new Report([
                'name' => 'Event Name',
                'components' => $this->selectedComponents
            ]);
        } else {
            $this->report = $report;
            $this->selectedComponents = $report->components;
            $this->reportName = $report->name;
            $this->event_id = $report->event_id;
            $this->event = $report->Event;
            $this->paymentMethod = $report->payment_method;
            $this->bankAccount = $report->bank_account;
            $this->bankNameAddress = $report->bank_name_address;
            $this->swiftCode = $report->swift_code;
            $this->iban = $report->iban;
            $this->currency_id = $report->currency_id;
            $this->showCategories = $report->show_categories;
            $this->special_price = $report->special_price;
            $this->with_options = $report->with_options;
            $this->with_logo = $report->with_logo;
            $this->logo_path = $report->logo_path;
        }
    }
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
        if ($this->report) {
            $this->report->update([
                'name' => $this->reportName,
                'components' => $this->selectedComponents,
                'event_id' => $this->event_id,
                'payment_method' => $this->paymentMethod,
                'bank_account' => $this->bankAccount,
                'bank_name_address' => $this->bankNameAddress,
                'swift_code' => $this->swiftCode,
                'iban' => $this->iban,
                'with_logo' => $this->with_logo,
                'logo_path' => $this->logo_path,
                'currency_id' => $this->currency_id,
                'show_categories' => $this->showCategories,
                'special_price'=> $this->special_price,
                'with_options' => $this->with_options,
            ]);
        } else {
            // Save the selected components and their order
            $this->report = Report::create([
                'name' => $this->reportName,
                'components' => $this->selectedComponents,
                'event_id' => $this->event_id,
                'payment_method' => $this->paymentMethod,
                'bank_account' => $this->bankAccount,
                'bank_name_address' => $this->bankNameAddress,
                'swift_code' => $this->swiftCode,
                'iban' => $this->iban,
                'with_logo' => $this->with_logo,
                'logo_path' => $this->logo_path,
                'currency_id' => $this->currency_id,
                'show_categories' => $this->showCategories,
                'special_price'=> $this->special_price,
                'with_options' => $this->with_options,
            ]);
        }
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
        $index = 0;
        if (in_array($component, $this->selectedComponents)) {
            $index = array_search($component, $this->selectedComponents, true);
            $this->selectedComponents = array_values(array_diff($this->selectedComponents, [$component]));
            array_splice($this->selectedComponents, $index, 0, $component);
            $this->selectedComponents = array_values($this->selectedComponents);
        } else {
            //if (!in_array($component, $this->selectedComponents)) {
            $this->selectedComponents[] = $component;
        }

        $this->render();
        //}
    }
    public function updatedLogoImage()
    {
        // Validate the uploaded file
        $this->validate([
            'logo_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Only allow image files
        ]);

        // Store the uploaded file in the 'public/logos' directory
        $this->logo_path = $this->logo_image->store('logos', 'public');

        // Optionally, save the logo path to the database
        // Example: auth()->user()->update(['logo_path' => $this->logoPath]);
    }
    public function updatedEventId()
    {
        $this->event = Event::where('id', $this->event_id)->first();
    }
    public function updatedCurrencyId()
    {
        $this->currency = Currency::where('id', $this->currency_id)->first();
    }
}
