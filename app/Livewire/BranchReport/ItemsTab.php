<?php

namespace App\Livewire\BranchReport;

use App\Models\Branch;
use Livewire\Component;

class ItemsTab extends Component
{
    public Branch $branch;
    public array $data = [];

    public function mount(Branch $branch, array $data): void
    {
        $this->branch = $branch;
        $this->data = $data;
    }

    public function render()
    {
        return view('livewire.branch-report.items-tab');
    }
}
