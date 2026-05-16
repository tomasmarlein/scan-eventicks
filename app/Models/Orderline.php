<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Orderline extends Model
{
    use HasFactory;

    protected $connection = 'tickets_mysql';

    /**
     * @var list<string>
     */
    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class)->withDefault();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class)->withDefault();
    }

    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeMatchingQr(Builder $query, string $qr): Builder
    {
        $columns = self::existingColumns(config('scanner.orderline_qr_columns', ['uuid']));

        if ($columns === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $subQuery) use ($columns, $qr) {
            foreach ($columns as $column) {
                $subQuery->orWhere($column, $qr);
            }
        });
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        $exactColumns = self::existingColumns(config('scanner.orderline_qr_columns', ['uuid']));
        $textColumns = self::existingColumns(config('scanner.orderline_search_columns', []));
        $like = '%'.self::escapeLike($term).'%';

        if ($exactColumns === [] && $textColumns === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $subQuery) use ($term, $like, $exactColumns, $textColumns) {
            foreach ($exactColumns as $column) {
                $subQuery->orWhere($column, $term);
            }

            foreach ($textColumns as $column) {
                $subQuery->orWhere($column, 'like', $like);
            }
        });
    }

    /**
     * @param  array<int, string>  $candidates
     * @return list<string>
     */
    public static function existingColumns(array $candidates): array
    {
        $candidates = array_values(array_unique(array_filter($candidates)));

        if ($candidates === []) {
            return [];
        }

        $instance = new self;
        $connection = $instance->getConnectionName() ?: config('database.default');
        $table = $instance->getTable();
        $cacheKey = sprintf('scanner:%s:%s:columns:%s', $connection, $table, md5(implode('|', $candidates)));

        try {
            return Cache::remember($cacheKey, now()->addDay(), fn () => self::lookupExistingColumns($connection, $table, $candidates));
        } catch (Throwable) {
            return self::lookupExistingColumns($connection, $table, $candidates);
        }
    }

    /**
     * @return list<string>
     */
    public static function scannerSelectColumns(): array
    {
        return self::existingColumns([
            'id',
            'uuid',
            'orderline_uuid',
            'event_id',
            'ticket_id',
            'unique_qr_code',
            'unique_qr_id',
            'name',
            'email',
            'order_reference',
            'zone',
            'blocked',
            'scanned',
            'created_at',
        ]);
    }

    public function scannerIdentifier(): ?string
    {
        return $this->orderline_uuid
            ?? $this->uuid
            ?? $this->unique_qr_id
            ?? $this->unique_qr_code
            ?? null;
    }

    public function displayQr(): ?string
    {
        return $this->unique_qr_code
            ?? $this->unique_qr_id
            ?? $this->scannerIdentifier();
    }

    public function toScannerListItem(string $orgSlug, string $eventSlug): array
    {
        $identifier = $this->scannerIdentifier();

        return array_merge($this->toArray(), [
            'unique_qr_id' => $this->displayQr(),
            'scanner_identifier' => $identifier,
            'url_checkin' => $identifier
                ? route('manuel.checkin', ['org_slug' => $orgSlug, 'slug' => $eventSlug, 'orderline_uuid' => $identifier])
                : null,
            'url_checkout' => $identifier
                ? route('manuel.checkout', ['org_slug' => $orgSlug, 'slug' => $eventSlug, 'orderline_uuid' => $identifier])
                : null,
        ]);
    }

    public function blockOrderline(self $orderline): void
    {
        $orderline->forceFill(['blocked' => true])->save();
    }

    public function unblockOrderline(self $orderline): void
    {
        $orderline->forceFill(['blocked' => false])->save();
    }

    /**
     * @param  array<int, string>  $candidates
     * @return list<string>
     */
    private static function lookupExistingColumns(string $connection, string $table, array $candidates): array
    {
        return array_values(array_filter(
            $candidates,
            fn (string $column) => Schema::connection($connection)->hasColumn($table, $column),
        ));
    }

    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'blocked' => 'boolean',
            'scanned' => 'boolean',
        ];
    }
}
