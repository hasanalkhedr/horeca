<div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
    {{ Illuminate\Support\Number::currency($contract->net_total, $contract->report->currency->CODE) }}
</div>
