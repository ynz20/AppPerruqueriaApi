<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Executar les migracions.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // ID únic per a cada producte
            $table->string('name'); // Nom del producte
            $table->text('description')->nullable(); // Descripció del producte (opcional)
            $table->decimal('price', 10, 2); // Preu del producte (fins a 10 dígits, 2 decimals)
            $table->integer('stock')->default(0); // Quantitat en estoc (per defecte 0)
            $table->timestamps(); // created_at i updated_at
        });
    }

    /**
     * Revertir les migracions.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
