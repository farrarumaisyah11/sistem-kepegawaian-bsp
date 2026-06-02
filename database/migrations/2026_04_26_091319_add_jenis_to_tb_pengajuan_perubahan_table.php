<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('tb_pengajuan_perubahan', function (Blueprint $table) {
        // Menambahkan kolom jenis setelah kolom nip
        $table->string('jenis')->after('nip')->comment('Contoh: ubah, tambah, hapus');
    });
}

public function down()
{
    Schema::table('tb_pengajuan_perubahan', function (Blueprint $table) {
        $table->dropColumn('jenis');
    });
}

};
