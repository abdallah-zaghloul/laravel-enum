<?php

namespace App\Traits;

use BackedEnum;
use UnitEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use function Illuminate\Support\enum_value;

trait IterableEnum
{
    /**
     * @return array
     */
    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_map(fn($case) => enum_value($case), static::cases());
    }

    /**
     * @return array
     */
    public static function assocArray(): array
    {
        return array_combine(static::names(), static::values());
    }

    /**
     * @return Collection
     */
    public static function toAssocCollection(): Collection
    {
        return collect(static::assocArray());
    }

    /**
     * @return Collection
     */
    public static function toCasesCollection(): Collection
    {
        return collect(static::cases());
    }


    /**
     * @return Collection
     */
    public static function toNamesCollection(): Collection
    {
        return collect(static::names());
    }

    /**
     * @return Collection
     */
    public static function toValuesCollection(): Collection
    {
        return collect(static::values());
    }

    public static function get($column)
    {
        return match ($column) {
            'names' => static::toNamesCollection(),
            'values' => static::toValuesCollection(),
            'cases' => static::toCasesCollection(),
            'assoc' => static::toAssocCollection(),
            'array' => static::assocArray(),
            'db' => static::toDBValues(),
            default => throw new InvalidArgumentException("Invalid column: $column")
        };
    }


    /**
     * @return Collection
     */
    public static function toTransCollection(string|null $locale = null): Collection
    {
        return static::toCasesCollection()->flatMap(fn(self $case) => [
            $case->value() => $case->trans($locale)
        ]);
    }
    /**
     * @return string
     */
    public static function toDBValues(): string
    {
        return static::toCasesCollection()->map(fn($case) => self::quote($case))->implode(',');
    }


    /**
     * @return string
     */
    public static function toDBExcept(...$cases): string
    {
        $casesCollection = collect($cases);
        $originalCasesCollection = static::toCasesCollection();

        return $originalCasesCollection->when(
            $casesCollection->every(fn($case) => $case instanceof static),
            fn($originalCasesCollection) => $originalCasesCollection->reject(
                fn($originalCase) => $casesCollection->containsStrict($originalCase)
            ),
            fn() => static::invalidException()
        )
            ->map(fn($case) => self::quote($case))
            ->implode(',');
    }



    /**
     * @return string
     */
    public static function toDBOnly(...$cases): string
    {
        $casesCollection = collect($cases);
        $originalCasesCollection = static::toCasesCollection();

        return $originalCasesCollection->when(
            $casesCollection->every(fn($case) => $case instanceof static),
            fn($originalCasesCollection) => $originalCasesCollection->filter(
                fn($originalCase) => $casesCollection->containsStrict($originalCase)
            ),
            fn() => static::invalidException()
        )
            ->map(fn($case) => self::quote($case))
            ->implode(',');
    }

    /**
     * @param mixed $value
     * @param bool $rescue
     * @return ?static
     */
    public static function find(mixed $value, bool $rescue = true): ?static
    {
        $key = is_subclass_of(static::class, BackedEnum::class) ? 'value' : 'name';
        return static::toCasesCollection()->first(
            fn($case) => $case->{$key} === enum_value($value),
            $rescue ? null : static::invalidException()
        );
    }



    /**
     * @return string
     */
    private static function quote(self $case): string
    {
        return DB::getPdo()->quote($case->toString());
    }



    private static function invalidException(): never
    {
        throw new InvalidArgumentException('All cases must be instances of the enum.');
    }


    /**
     * @return string
     */
    public function toDBValue(): string
    {
        return self::quote($this);
    }

    /**
     * @return Stringable
     */
    public function toString(): Stringable
    {
        return str($this->value());
    }


    /**
     * @return string
     */
    public function value(): string
    {
        return enum_value($this);
    }


    /**
     * @return string
     */
    public function isEqual(mixed $value): bool
    {
        return $this === static::find($value);
    }


    public function trans(string|null $locale = null): Stringable
    {
        return $this->toString()->pipe(fn($transKey) => trans(
            key: "enums.$transKey",
            locale: $locale
        ));
    }
}
