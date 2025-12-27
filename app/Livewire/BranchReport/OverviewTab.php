<?php

namespace App\Livewire\BranchReport;

use App\Models\Branch;
use Livewire\Component;

class OverviewTab extends Component
{
    public Branch $branch;
    public array $data = [];
    public array $sentiment = [];
    public array $operational = [];

    public function mount(Branch $branch, array $data, array $sentiment, array $operational): void
    {
        $this->branch = $branch;
        $this->data = $data;
        $this->sentiment = $sentiment;
        $this->operational = $operational;
    }

    public function render()
    {
        return view('livewire.branch-report.overview-tab');
    }
}
