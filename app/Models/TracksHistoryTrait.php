<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

//https://medium.com/sammich-shop/simple-record-history-tracking-with-laravel-observers-48a2e3c5698b
//https://laravel.com/docs/12.x/eloquent#examining-attribute-changes
trait TracksHistoryTrait
{
    protected function track(Model $model, ?callable $func = null, $table = null, $id = null)
    {
        $id = $id ?: $model->id;
        // Allow for customization of the history record if needed
        $func = $func ?: [$this, 'getHistoryBody'];

        // Get the dirty fields and run them through the custom function, then insert them into the history table
        $this->getUpdated($model)
            ->map(function ($value, $field) use ($func) {
                return call_user_func_array($func, [$value, $field]);
            })
            ->each(function ($fields) use ($table, $id) {
                History::create(
                    [
                        'action_id' => $id,
                        'user_add' => Auth::user()->username,
                    ] + $fields
                );
            });
    }

    protected function getHistoryBody($value, $field): array
    {
        return [
            'body' => "Updated {$field} to {$value}",
            'property' => $field,
            'new_value' => $value,
        ];
    }

    protected function getUpdated($model): Collection
    {
        return collect($model->getDirty())->filter(function ($value, $key) {
            // We don't care if timestamps are dirty, we're not tracking those
            return !in_array($key, ['created_at', 'updated_at']);
        })->mapWithKeys(function ($value, $key) {
            // Take the field names and convert them into human readable strings for the description of the action
            // e.g. first_name -> first name
            return [str_replace('_', ' ', $key) => $value];
        });
    }
}
