<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueSlugService
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function generate(string $modelClass, string $value, string $column = 'slug', int|string|null $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::random(8));
        $counter = 2;

        while ($this->exists($modelClass, $column, $slug, $ignoreId)) {
            $slug = sprintf('%s-%d', $baseSlug !== '' ? $baseSlug : 'item', $counter);
            $counter++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function exists(string $modelClass, string $column, string $slug, int|string|null $ignoreId = null): bool
    {
        return $modelClass::query()
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where($column, $slug)
            ->exists();
    }
}
