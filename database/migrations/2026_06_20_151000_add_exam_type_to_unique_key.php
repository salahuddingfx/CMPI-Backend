<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $oldIndex = 'bteb_results_roll_semester_regulation_unique';
        $newIndex = 'bteb_results_roll_sem_reg_type_unique';

        if ($this->indexExists($oldIndex)) {
            Schema::table('bteb_results', fn($t) => $t->dropUnique($oldIndex));
        }

        if (!$this->indexExists($newIndex)) {
            Schema::table('bteb_results', function ($t) {
                $t->string('roll', 20)->change();
                $t->string('semester', 10)->change();
                $t->string('regulation', 10)->change();
                $t->string('exam_type', 30)->change();
            });
            Schema::table('bteb_results', fn($t) => $t->unique(['roll', 'semester', 'regulation', 'exam_type'], $newIndex));
        }
    }

    public function down(): void
    {
        $oldIndex = 'bteb_results_roll_semester_regulation_unique';
        $newIndex = 'bteb_results_roll_sem_reg_type_unique';

        if ($this->indexExists($newIndex)) {
            Schema::table('bteb_results', fn($t) => $t->dropUnique($newIndex));
        }

        if (!$this->indexExists($oldIndex)) {
            Schema::table('bteb_results', fn($t) => $t->unique(['roll', 'semester', 'regulation'], $oldIndex));
        }
    }

    private function indexExists(string $name): bool
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            try {
                $indexes = DB::select("PRAGMA index_list('bteb_results')");
                return collect($indexes)->contains(fn($i) => $i->name === $name);
            } catch (\Exception $e) {
                return false;
            }
        }
        return DB::select("
            SELECT COUNT(1) AS cnt FROM information_schema.STATISTICS
            WHERE table_schema = DATABASE() AND table_name = 'bteb_results' AND index_name = ?
        ", [$name])[0]->cnt > 0;
    }
};
