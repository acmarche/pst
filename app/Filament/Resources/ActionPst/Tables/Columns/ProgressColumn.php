<?php

namespace App\Filament\Resources\ActionPst\Tables\Columns;

use Filament\Tables\Columns\Column;

/**
 * https://laraveldaily.com/post/filament-custom-table-column-progress-bar
 */
final class ProgressColumn extends Column
{
    protected string $view = 'tables.columns.progress-column';
}
