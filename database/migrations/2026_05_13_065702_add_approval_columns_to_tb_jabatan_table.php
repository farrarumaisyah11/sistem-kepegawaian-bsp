<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_jabatan', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_jabatan', 'approval_status')) {
                $table->string('approval_status', 30)->default('pending')->after('struktur_file');
            }

            if (!Schema::hasColumn('tb_jabatan', 'approval_token')) {
                $table->string('approval_token', 100)->nullable()->unique()->after('approval_status');
            }

            if (!Schema::hasColumn('tb_jabatan', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_token');
            }

            if (!Schema::hasColumn('tb_jabatan', 'approved_by_name')) {
                $table->string('approved_by_name', 100)->nullable()->after('approved_by');
            }

            if (!Schema::hasColumn('tb_jabatan', 'approved_by_role')) {
                $table->string('approved_by_role', 50)->nullable()->after('approved_by_name');
            }

            if (!Schema::hasColumn('tb_jabatan', 'approved_by_jabatan')) {
                $table->string('approved_by_jabatan', 100)->nullable()->after('approved_by_role');
            }

            if (!Schema::hasColumn('tb_jabatan', 'approved_by_departemen')) {
                $table->string('approved_by_departemen', 100)->nullable()->after('approved_by_jabatan');
            }

            if (!Schema::hasColumn('tb_jabatan', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by_departemen');
            }

            if (!Schema::hasColumn('tb_jabatan', 'approval_catatan')) {
                $table->text('approval_catatan')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tb_jabatan', function (Blueprint $table) {
            $columns = [
                'approval_catatan',
                'approved_at',
                'approved_by_departemen',
                'approved_by_jabatan',
                'approved_by_role',
                'approved_by_name',
                'approved_by',
                'approval_token',
                'approval_status',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tb_jabatan', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};