<?php

namespace App\Services;

use App\Models\Department;
use App\Models\ResearchSubject;
use Illuminate\Http\Request;

class SubjectCatalogue
{
    public function listing(Request $request): array
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'department_id' => ['nullable', 'integer'],
            'laboratory' => ['nullable', 'string', 'max:120'],
            'field' => ['nullable', 'string', 'max:120'],
        ]);

        $openSubjects = ResearchSubject::open();
        $catalogue = clone $openSubjects;
        $options = [
            'department_id' => Department::orderBy('name')->pluck('name', 'id'),
        ];
        foreach (['laboratory', 'field'] as $column) {
            $options[$column] = (clone $openSubjects)->whereNotNull($column)
                ->where($column, '!=', '')->distinct()->orderBy($column)->pluck($column, $column);
            if (filled($filters[$column] ?? null)) {
                $catalogue->where($column, $filters[$column]);
            }
        }

        $search = $filters['search'] ?? null;
        $subjects = $catalogue->with(['professor', 'department'])
            ->when(filled($filters['department_id'] ?? null), fn ($query) => $query->where('department_id', $filters['department_id']))
            ->when(filled($search), fn ($query) => $query->where(fn ($subject) => $subject
                ->whereLike('title', '%'.$search.'%')->orWhereLike('keywords', '%'.$search.'%')))
            ->latest()->orderByDesc('id')->paginate(9)->withQueryString();

        $labels = ['search' => 'Recherche', 'department_id' => 'Formation', 'laboratory' => 'Laboratoire', 'field' => 'Domaine'];
        $activeFilters = [];
        foreach ($labels as $key => $label) {
            if (filled($filters[$key] ?? null)) {
                $activeFilters[$label] = $options[$key][$filters[$key]] ?? $filters[$key];
            }
        }

        return compact('subjects', 'options', 'filters', 'activeFilters');
    }
}
