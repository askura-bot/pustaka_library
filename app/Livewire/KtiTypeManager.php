<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\KtiType;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
#[Title('Template KTI')]
class KtiTypeManager extends Component
{
    public $ktiTypes = [];
    public $name = '';
    public $columns = [];
    public $editingId = null;

    public function mount()
    {
        $this->loadTypes();
        if (empty($this->columns)) {
            $this->columns = ['']; // initial empty row
        }
    }

    public function loadTypes()
    {
        $this->ktiTypes = Auth::user()->ktiTypes()->get();
    }

    public function addColumn()
    {
        $this->columns[] = '';
    }

    public function removeColumn($index)
    {
        unset($this->columns[$index]);
        $this->columns = array_values($this->columns); // re-index
    }

    public function edit($id)
    {
        $type = KtiType::findOrFail($id);
        if ($type->user_id !== Auth::id()) abort(403);
        $this->editingId = $type->id;
        $this->name = $type->name;
        $this->columns = $type->columns;
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->name = '';
        $this->columns = [''];
        $this->resetErrorBag();
    }

    public function delete($id)
    {
        $type = KtiType::findOrFail($id);
        if ($type->user_id === Auth::id()) {
            $type->delete();
            $this->loadTypes();
        }
    }

    public function save()
    {
        // Filter out empty columns
        $this->columns = array_filter($this->columns, fn($col) => trim($col) !== '');
        
        $this->validate([
            'name' => 'required|string|max:255',
            'columns' => 'required|array|min:1',
            'columns.*' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama Kategori KTI tidak boleh kosong.',
            'columns.required' => 'Minimal harus ada 1 kolom.',
            'columns.min' => 'Minimal harus ada 1 kolom.',
            'columns.*.required' => 'Nama kolom tidak boleh kosong.',
        ]);

        if ($this->editingId) {
            $type = KtiType::findOrFail($this->editingId);
            if ($type->user_id === Auth::id()) {
                $type->update([
                    'name' => $this->name,
                    'columns' => array_values($this->columns),
                ]);
            }
        } else {
            Auth::user()->ktiTypes()->create([
                'name' => $this->name,
                'columns' => array_values($this->columns),
            ]);
        }

        $this->cancelEdit();
        $this->loadTypes();
    }

    public function render()
    {
        return view('livewire.kti-type-manager');
    }
}
