<?php

namespace App\Livewire\BranchReport;

use App\Models\Branch;
use Livewire\Component;

class DemographicsTab extends Component
{
    public Branch $branch;
    public array $data = [];
    public ?string $expandedCategory = null;

    public function mount(Branch $branch, array $data): void
    {
        $this->branch = $branch;
        $this->data = $data;
    }

    public function toggleCategory(string $category): void
    {
        $this->expandedCategory = $this->expandedCategory === $category ? null : $category;
    }

    public function render()
    {
        return view('livewire.branch-report.demographics-tab');
    }
}
