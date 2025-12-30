<?php

namespace App\Livewire\BranchReport;

use App\Models\Branch;
use Livewire\Component;

class EmployeesTab extends Component
{
    public Branch $branch;
    public array $data = [];
    public string $activeView = 'overview';
    public ?string $expandedEmployee = null;

    public function mount(Branch $branch, array $data): void
    {
        $this->branch = $branch;
        $this->data = $data;
    }

    public function setActiveView(string $view): void
    {
        $this->activeView = $view;
    }

    public function toggleEmployee(string $employeeName): void
    {
        $this->expandedEmployee = $this->expandedEmployee === $employeeName ? null : $employeeName;
    }

    public function render()
    {
        return view('livewire.branch-report.employees-tab');
    }
}
