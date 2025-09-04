@php use App\Constant\RoleEnum; @endphp
@foreach(RoleEnum::cases() as $role)

    <div class="flex flex-col gap-2 p-2 bg-white rounded-2xl shadow-md border border-gray-200 w-full max-w-md">
        <div class="text-xl font-semibold text-gray-800">
            {{$role->getLabel()}}
        </div>
        <div class="text-gray-600">
            {{$role->getDescription()}}
        </div>
    </div>

@endforeach

<div class="flex flex-col gap-2 p-2 bg-white rounded-2xl shadow-md border border-gray-200 w-full max-w-md">
    <div class="text-xl font-semibold text-gray-800">
        LES AGENTS
    </div>
    <div class="text-gray-600">
        <ul class="space-y-4">
            <li class="flex items-start">
                Ajouter des actions avec comme statut "A valider"
            </li>
            <li class="flex items-start">
                Droit d'écriture si repris comme agent pilote ou membre du service pilote
            </li>
            <li class="flex items-start">
                Droit de lecture sur toutes les actions
            </li>
        </ul>

    </div>
</div>



