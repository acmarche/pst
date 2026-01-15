<?php

namespace App\Filament\Resources\Service\Pages;

use App\Filament\Resources\Service\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected static ?string $title = 'Ajout d\'un service';
}
