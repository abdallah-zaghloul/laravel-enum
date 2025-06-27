<?php

use App\Enums\WorkUnitEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            query: Str::swap(
                [
                    ":table" => "users",
                    ":values" => WorkUnitEnum::toDBExcept(WorkUnitEnum::Remote),
                    ":default" => WorkUnitEnum::Hybrid->toDBValue(),
                ],
                "ALTER TABLE :table MODIFY COLUMN work ENUM(:values) DEFAULT :default",
            )
        );

        DB::statement(
            query: str("ALTER TABLE :table MODIFY COLUMN work ENUM(:values) DEFAULT :default")
                ->swap([
                    ":table" => "users",
                    ":values" => WorkUnitEnum::toDBExcept(WorkUnitEnum::Remote),
                    ":default" => WorkUnitEnum::Hybrid->toDBValue(),
                ])
        );
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            query: strtr(
                "ALTER TABLE :table MODIFY COLUMN work ENUM(:values) DEFAULT :default",
                [
                    ":table" => "users",
                    ":values" => WorkUnitEnum::toDBValues(),
                    ":default" => WorkUnitEnum::Onsite->toDBValue(),
                ]
            )
        );
    }
};
