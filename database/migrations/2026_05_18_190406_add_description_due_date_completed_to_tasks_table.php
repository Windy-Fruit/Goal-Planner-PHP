<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->date('due_date')->nullable()->after('description');
            $table->renameColumn('is_done', 'is_completed');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('is_completed', 'is_done');
            $table->dropColumn(['description', 'due_date']);
        });
    }
};
