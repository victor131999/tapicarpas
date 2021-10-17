<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManoObraHasProductoFsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mano_obra_has_producto_fs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();


            //relaciones
            $table->unsignedBigInteger('mano_obra_id')->nullable();
            $table->unsignedBigInteger('producto_finalizado_id')->nullable();

            $table->foreign('mano_obra_id')->references('id')->on('mano_de_obras')->onDelete('set null');
            $table->foreign('producto_finalizado_id')->references('id')->on('producto_finalizados')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mano_obra_has_producto_fs');
    }
}
