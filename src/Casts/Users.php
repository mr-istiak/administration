<?php

namespace Administration\Casts;

use Administration\Role;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Users implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        if(($attributes['id'] ?? false) === 2) {
            $notMember = [];
            Role::query()
                ->whereNot('id', 2)
                ->get(['users'])
                ->each(function($role) use(&$notMember) {
                    $notMember = array_merge($notMember, json_decode($role->getAttributes()['users'], true));
                });
            $members = User::query()->whereNot('id', $notMember)->get();
            return $members;
        }
        return User::whereIn('id', (array) json_decode($value, true))->get();
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if((int) $attributes['id'] === 1) {
            $newOwner = collect($value)->last();
            $newOwner = (string) ((is_object($newOwner) || is_array($newOwner)) ? (is_object($newOwner) ? $newOwner->id : $newOwner['id']) : $newOwner);
            return json_encode([$newOwner]);
        }
        return collect($value)
            ->map(fn($value) => (string) ((is_object($value) || is_array($value)) ? (is_object($value) ? $value->id : $value['id']) : $value))
            ->unique()
            ->toJson();
    }
}
