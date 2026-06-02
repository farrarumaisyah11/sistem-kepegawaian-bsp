<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_jabatan', function (Blueprint $table) {
            $table->string('approval_status', 30)->default('pending')->after('struktur_file');
            $table->uuid('approval_token')->nullable()->unique()->after('approval_status');

            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_token');
            $table->string('approved_by_name', 150)->nullable()->after('approved_by');
            $table->string('approved_by_role', 50)->nullable()->after('approved_by_name');
            $table->string('approved_by_jabatan', 150)->nullable()->after('approved_by_role');
            $table->string('approved_by_departemen', 150)->nullable()->after('approved_by_jabatan');

            $table->timestamp('approved_at')->nullable()->after('approved_by_departemen');
            $table->text('approval_catatan')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('tb_jabatan', function (Blueprint $table) {
            $table->dropColumn([
                'approval_status',
                'approval_token',
                'approved_by',
                'approved_by_name',
                'approved_by_role',
                'approved_by_jabatan',
                'approved_by_departemen',
                'approved_at',
                'approval_catatan',
            ]);
        });
    }
};