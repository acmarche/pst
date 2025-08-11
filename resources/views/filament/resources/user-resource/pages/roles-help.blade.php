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
<p>
    Un seul rôle est suffisant,
</p>
