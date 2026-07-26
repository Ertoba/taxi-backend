<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('app_users')->cascadeOnDelete();
            $table->unsignedBigInteger('module')->nullable()->index();
            $table->uuid('thread_id')->unique();
            $table->string('app_role', 16)->default('rider');
            $table->string('title')->default('Live support');
            $table->text('description')->nullable();
            $table->boolean('thread_status')->default(true)->index();
            $table->boolean('ai_enabled')->default(true);
            $table->boolean('operator_active')->default(false);
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
            });
        } else {
            Schema::table('support_tickets', function (Blueprint $table) {
                if (! Schema::hasColumn('support_tickets', 'app_role')) {
                    $table->string('app_role', 16)->default('rider');
                }
                if (! Schema::hasColumn('support_tickets', 'ai_enabled')) {
                    $table->boolean('ai_enabled')->default(true);
                }
                if (! Schema::hasColumn('support_tickets', 'operator_active')) {
                    $table->boolean('operator_active')->default(false);
                }
                if (! Schema::hasColumn('support_tickets', 'last_message_at')) {
                    $table->timestamp('last_message_at')->nullable();
                }
            });
        }

        if (! Schema::hasTable('support_ticket_replies')) {
            Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_admin_reply')->default(false);
            $table->text('message');
            $table->boolean('reply_status')->default(true);
            $table->string('source', 16)->default('user');
            $table->timestamps();

            $table->index(['thread_id', 'id']);
            });
        } elseif (! Schema::hasColumn('support_ticket_replies', 'source')) {
            Schema::table('support_ticket_replies', function (Blueprint $table) {
                $table->string('source', 16)->default('user');
            });
        }
    }

    public function down(): void
    {
        // These table names are also used by older RideOn installer dumps that
        // were not tracked by Laravel migrations. A rollback must never drop
        // pre-existing customer support data.
        if (Schema::hasTable('support_ticket_replies')
            && Schema::hasColumn('support_ticket_replies', 'source')) {
            Schema::table('support_ticket_replies', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
        if (Schema::hasTable('support_tickets')) {
            $columns = array_values(array_filter([
                'app_role',
                'ai_enabled',
                'operator_active',
                'last_message_at',
            ], fn (string $column): bool => Schema::hasColumn('support_tickets', $column)));
            if ($columns !== []) {
                Schema::table('support_tickets', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
