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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('email')->index();
            $table->string('pin_code')->nullable()->after('password'); // Code PIN haché pour caissiers (F-AUTH-04)
            $table->string('status', 20)->default('active')->after('pin_code'); // active, inactive
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_active_at')->nullable()->after('updated_at');
            $table->json('settings')->nullable()->after('last_active_at');

            // L'email peut être optionnel si le commerçant s'inscrit ou se connecte uniquement par téléphone
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn([
                'tenant_id',
                'phone',
                'pin_code',
                'status',
                'phone_verified_at',
                'last_active_at',
                'settings',
            ]);
            $table->string('email')->nullable(false)->change();
        });
    }
};
