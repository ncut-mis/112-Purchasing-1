<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agent_applications')) {
            return;
        }

        Schema::table('agent_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_applications', 'name')) {
                $table->string('name')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('agent_applications', 'country')) {
                $table->string('country')->nullable()->after('name');
            }

            if (! Schema::hasColumn('agent_applications', 'id_number')) {
                $table->string('id_number')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('agent_applications', 'id_image_front')) {
                $table->string('id_image_front')->nullable()->after('id_number');
            }

            if (! Schema::hasColumn('agent_applications', 'id_image_back')) {
                $table->string('id_image_back')->nullable()->after('id_image_front');
            }

            if (! Schema::hasColumn('agent_applications', 'admin_remark')) {
                $table->text('admin_remark')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_applications')) {
            return;
        }

        Schema::table('agent_applications', function (Blueprint $table) {
            foreach (['name', 'country', 'id_number', 'id_image_front', 'id_image_back', 'admin_remark'] as $column) {
                if (Schema::hasColumn('agent_applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
