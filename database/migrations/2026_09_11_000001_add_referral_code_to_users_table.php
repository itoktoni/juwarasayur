<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->unique()->after('reference_id');
        });

        // Generate code untuk affiliator/reseller yang sudah ada tapi belum punya code
        $users = DB::table('users')->whereNull('referral_code')->whereIn('type', ['affiliator', 'reseller'])->get(['id']);
        foreach ($users as $user) {
            $code = $this->uniqueCode();
            DB::table('users')->where('id', $user->id)->update(['referral_code' => $code]);
        }

        // Index tambahan untuk referral_hits (opsional, buat attribution tracking)
        if (! Schema::hasTable('referral_hits')) {
            Schema::create('referral_hits', function (Blueprint $table) {
                $table->id();
                $table->string('referral_code', 20)->index();
                $table->foreignId('affiliator_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->string('landing_url', 1000)->nullable();
                $table->timestamps();
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('referral_hits')) {
            Schema::dropIfExists('referral_hits');
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['referral_code']);
            $table->dropColumn('referral_code');
        });
    }

    private function uniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (DB::table('users')->where('referral_code', $code)->exists());

        return $code;
    }
};
