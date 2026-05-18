<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('agent_posts') && Schema::hasColumn('agent_posts', 'cover_image')) {
            DB::statement('ALTER TABLE `agent_posts` MODIFY `cover_image` MEDIUMBLOB NULL');
        }

        if (Schema::hasTable('post_products') && Schema::hasColumn('post_products', 'image_path')) {
            DB::statement('ALTER TABLE `post_products` MODIFY `image_path` MEDIUMBLOB NULL');
        }

        if (Schema::hasTable('request_items') && Schema::hasColumn('request_items', 'reference_image')) {
            DB::statement('ALTER TABLE `request_items` MODIFY `reference_image` MEDIUMBLOB NULL');
        }

        if (Schema::hasTable('agent_applications')) {
            if (Schema::hasColumn('agent_applications', 'id_image_front')) {
                DB::statement('ALTER TABLE `agent_applications` MODIFY `id_image_front` MEDIUMBLOB NULL');
            }
            if (Schema::hasColumn('agent_applications', 'id_image_back')) {
                DB::statement('ALTER TABLE `agent_applications` MODIFY `id_image_back` MEDIUMBLOB NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('agent_posts') && Schema::hasColumn('agent_posts', 'cover_image')) {
            DB::statement('ALTER TABLE `agent_posts` MODIFY `cover_image` BLOB NULL');
        }

        if (Schema::hasTable('post_products') && Schema::hasColumn('post_products', 'image_path')) {
            DB::statement('ALTER TABLE `post_products` MODIFY `image_path` BLOB NULL');
        }

        if (Schema::hasTable('request_items') && Schema::hasColumn('request_items', 'reference_image')) {
            DB::statement('ALTER TABLE `request_items` MODIFY `reference_image` BLOB NULL');
        }

        if (Schema::hasTable('agent_applications')) {
            if (Schema::hasColumn('agent_applications', 'id_image_front')) {
                DB::statement('ALTER TABLE `agent_applications` MODIFY `id_image_front` BLOB NULL');
            }
            if (Schema::hasColumn('agent_applications', 'id_image_back')) {
                DB::statement('ALTER TABLE `agent_applications` MODIFY `id_image_back` BLOB NULL');
            }
        }
    }
};
