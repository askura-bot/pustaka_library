<?php

namespace App\Livewire;

use App\Models\KtiType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Template KTI')]
class KtiTypeManager extends Component
{
    public $ktiTypes = [];

    public $name = '';

    public $columns = [];

    public $editingId = null;

    /**
     * Protected column names when editing the "Article" template.
     *
     * @var array<int, string>
     */
    public array $protectedColumns = [];

    public function mount(): void
    {
        $this->loadTypes();
        if (empty($this->columns)) {
            $this->columns = ['']; // initial empty row
        }
    }

    public function loadTypes(): void
    {
        $this->ktiTypes = Auth::user()->ktiTypes()->get();
    }

    public function addColumn(): void
    {
        $this->columns[] = '';
    }

    public function removeColumn(int $index): void
    {
        // Prevent removal of protected columns
        $columnName = trim($this->columns[$index] ?? '');
        if (in_array($columnName, $this->protectedColumns, true)) {
            return;
        }

        unset($this->columns[$index]);
        $this->columns = array_values($this->columns); // re-index
    }

    public function edit(int $id): void
    {
        $type = KtiType::findOrFail($id);
        if ($type->user_id !== Auth::id()) {
            abort(403);
        }
        $this->editingId = $type->id;
        $this->name = $type->name;
        $this->columns = $type->columns;

        // Set protected columns if editing "Article" template
        $this->protectedColumns = $type->isArticleTemplate()
            ? KtiType::ARTICLE_PROTECTED_COLUMNS
            : [];
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->columns = [''];
        $this->protectedColumns = [];
        $this->resetErrorBag();
    }

    public function delete(int $id): void
    {
        $type = KtiType::findOrFail($id);

        // Prevent deletion of the "Article" template
        if ($type->user_id === Auth::id() && ! $type->isArticleTemplate()) {
            $type->delete();
            $this->loadTypes();
        }
    }

    public function save(): void
    {
        // Filter out empty columns
        $this->columns = array_filter($this->columns, fn ($col) => trim($col) !== '');

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
                // Ensure protected columns are still present for Article template
                if ($type->isArticleTemplate()) {
                    $currentColumns = array_values($this->columns);
                    foreach (KtiType::ARTICLE_PROTECTED_COLUMNS as $required) {
                        if (! in_array($required, $currentColumns, true)) {
                            $currentColumns[] = $required;
                        }
                    }
                    $this->columns = $currentColumns;
                }

                $updateData = ['columns' => array_values($this->columns)];

                // Prevent renaming the "Article" template
                if (! $type->isArticleTemplate()) {
                    $updateData['name'] = $this->name;
                }

                $type->update($updateData);
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
