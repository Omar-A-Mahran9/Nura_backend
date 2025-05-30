<?php

use App\Enums\VendorStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('another_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default(VendorStatus::pending->value)->comment('App\Enums\VendorStatus');
            // $table->enum('type', ['author','user']);
            // $table->foreignId('city_id')->nullable()->references('id')->on('cities');
            $table->string('identity_no')->nullable();
            // $table->string('commercial_registration_no')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->string('password');
            $table->string('rejection_reason')->nullable();
            $table->foreignId('created_by')->nullable()->references('id')->on('employees');
            $table->boolean('Terms_and_conditions')->default(false);
            $table->boolean('created_by_social')->default(0);

            $table->string('verification_code')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendors');
    }
}
