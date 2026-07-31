<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('support_ticket_replies')) {
            return;
        }

        Schema::table('support_ticket_replies', function (Blueprint $table) {
            if (! Schema::hasColumn('support_ticket_replies', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('message');
            }
            if (! Schema::hasColumn('support_ticket_replies', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attachment_path');
            }
            if (! Schema::hasColumn('support_ticket_replies', 'attachment_mime')) {
                $table->string('attachment_mime', 100)->nullable()->after('attachment_name');
            }
            if (! Schema::hasColumn('support_ticket_replies', 'attachment_size')) {
                $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('support_ticket_replies')) {
            return;
        }

        $columns = array_values(array_filter([
            'attachment_path',
            'attachment_name',
            'attachment_mime',
            'attachment_size',
        ], fn (string $column): bool => Schema::hasColumn('support_ticket_replies', $column)));

        if ($columns !== []) {
            Schema::table('support_ticket_replies', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
