<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Event;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Builder;

trait ResolvesScannerContext
{
    protected function resolveOrganisation(string $slug): Organisation
    {
        $organisation = Organisation::query()
            ->select(['id', 'name', 'slug'])
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless(auth()->user()?->canAccessOrganisation((int) $organisation->id), 403);

        return $organisation;
    }

    /**
     * @param  array<int, string>  $with
     * @param  array<int, string>|null  $columns
     */
    protected function resolveEventForOrganisation(
        Organisation $organisation,
        string $slug,
        array $with = [],
        ?array $columns = null,
    ): Event {
        $query = Event::query()
            ->when($columns !== null, fn (Builder $query) => $query->select($columns))
            ->where('organisation_id', $organisation->id)
            ->where('slug', $slug);

        foreach ($with as $relation) {
            $query->with($relation);
        }

        return $query->firstOrFail();
    }
}
