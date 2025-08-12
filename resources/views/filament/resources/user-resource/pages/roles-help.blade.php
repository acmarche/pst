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
<p class="text-base block border-l-red-300 border-l-2 pl-3">
    Les agents encodés comme "Agents pilotes" sur une action, dispose de droits d'écriture sur l'action
</p>
<p class="text-base block border-l-red-300 border-l-2 pl-3">
    Tout le monde a accès en lecture à toutes les actions
</p>
<p class="text-base block border-l-red-300 border-l-2 pl-3">
    Le rôle administrateur dispose du rôle responsable
</p>
