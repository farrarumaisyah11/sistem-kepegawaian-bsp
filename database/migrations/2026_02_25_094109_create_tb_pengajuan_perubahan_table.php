<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_pengajuan_perubahan', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->index();

            // "buat baru" atau "ubah"
            $table->enum('jenis', ['buat_baru', 'ubah'])->default('ubah');

            // isi semua data form disimpan di sini
            $table->json('payload');

            $table->enum('status', ['belum_diolah','diproses','diterima','ditolak'])
                ->default('belum_diolah')
                ->index();

            $table->text('catatan_pegawai')->nullable();
            $table->text('catatan_reviewer')->nullable();

            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_pengajuan_perubahan');
    }
};