<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('length', false, true);
            $table->date('release_date');
            $table->text('overview');
            $table->string('poster_url');
            $table->integer('tmdb_id')->unsigned();
            $table->decimal('tmdb_vote_average', 2, 1, true);
            $table->integer('tmdb_vote_count', false, true);
            $table->string('tmdb_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movies');
    }
}
