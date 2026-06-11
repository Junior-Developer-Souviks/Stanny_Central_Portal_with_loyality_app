<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('is_super_admin')->nullable();
            $table->integer('admin_id')->nullable();
            $table->unsignedBigInteger('business_type')->nullable()->index('users_business_type_foreign');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->tinyInteger('user_type')->default(1)->comment('0:staff, 1:customer');
            $table->enum('customer_badge', ['general', 'premium'])->nullable();
            $table->string('employee_id')->nullable();
            $table->string('designation')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index('parent_id');
            $table->string('prefix')->nullable();
            $table->string('name')->nullable();
            $table->string('emp_code')->nullable();
            $table->string('surname')->nullable();
            $table->string('prof_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->tinyInteger('otp_verification')->default(0)->comment('0:otp not sent, 1:otp not veridied,2:otp verified');
            $table->string('mpin')->nullable();
            $table->string('ip_address')->nullable();
            $table->date('dob')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('verified_video')->nullable();
            $table->string('country_code_phone', 10)->nullable();
            $table->string('phone')->nullable();
            $table->integer('is_phone_whatsapp')->nullable();
            $table->string('country_code_alt_1')->nullable();
            $table->string('alternative_phone_number_1')->nullable();
            $table->integer('is_alternate_no_whatsapp')->nullable();
            $table->string('country_code_alt_2')->nullable();
            $table->string('alternative_phone_number_2')->nullable();
            $table->integer('is_alternate_no2_whatsapp')->nullable();
            $table->string('location')->nullable();
            $table->text('about')->nullable();
            $table->string('password')->nullable();
            $table->string('country_code_whatsapp')->nullable();
            $table->string('whatsapp_no')->nullable();
            $table->string('image')->nullable();
            $table->string('passport_id_back')->nullable();
            $table->date('passport_issued_date')->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->string('passport_id_front')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('visa_no')->nullable();
            $table->string('emergency_contact_person')->nullable();
            $table->string('country_code_emergency_mobile')->nullable();
            $table->string('emergency_mobile')->nullable();
            $table->string('country_code_emergency_whatsapp')->nullable();
            $table->string('emergency_whatsapp')->nullable();
            $table->text('emergency_address')->nullable();
            $table->string('aadhar_name')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('gst_certificate_image')->nullable();
            $table->decimal('credit_limit', 15)->nullable();
            $table->integer('credit_days')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->boolean('status')->default(true)->comment('1: Active, 0: Inactive');
            $table->unsignedBigInteger('created_by')->nullable()->index('created_by_user_id_k1');
            $table->timestamps();
            $table->string('employee_rank')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
