@php
    $buttonLabel = $label ?? (
        ($this->getOperation() ?? 'create') === 'create'
            ? "Ajouter l'action"
            : "Enregistrer l'action"
    );
@endphp
<x-filament::button
    type="submit"
    icon="tabler-plus">
    {{ $buttonLabel }}
</x-filament::button>
