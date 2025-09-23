<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communication_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('birthday_template_id')->nullable()->after('whatsapp_config');
            $table->unsignedBigInteger('anniversary_template_id')->nullable()->after('birthday_template_id');
            $table->boolean('auto_send_birthdays')->default(false)->after('anniversary_template_id');
            $table->boolean('auto_send_anniversaries')->default(false)->after('auto_send_birthdays');

            $table->foreign('birthday_template_id')->references('id')->on('message_templates')->onDelete('set null');
            $table->foreign('anniversary_template_id')->references('id')->on('message_templates')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('communication_settings', function (Blueprint $table) {
            $table->dropForeign(['birthday_template_id']);
            $table->dropForeign(['anniversary_template_id']);
            $table->dropColumn([
                'birthday_template_id',
                'anniversary_template_id',
                'auto_send_birthdays',
                'auto_send_anniversaries',
            ]);
        });
    }
};
