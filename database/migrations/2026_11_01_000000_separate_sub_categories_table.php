<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['company_id', 'category_id', 'name']);
            $table->index(['company_id', 'category_id', 'status']);
        });

        DB::statement("INSERT INTO sub_categories (id, company_id, category_id, name, description, status, created_at, updated_at) SELECT id, company_id, parent_id, name, description, status, created_at, updated_at FROM categories WHERE parent_id IS NOT NULL");

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained('sub_categories')->nullOnDelete();
        });
        foreach (DB::table('sub_categories')->select('id', 'category_id')->get() as $subCategory) {
            DB::table('products')->where('category_id', $subCategory->id)->update([
                'category_id' => $subCategory->category_id,
                'sub_category_id' => $subCategory->id,
            ]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_company_id_parent_id_name_unique');
            $table->dropIndex('categories_company_id_parent_id_status_index');
            $table->dropConstrainedForeignId('parent_id');
            $table->index(['company_id', 'status']);
        });

        DB::table('categories')->whereIn('id', DB::table('sub_categories')->select('id'))->delete();
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('company_id')->constrained('categories')->cascadeOnDelete();
            $table->unique(['company_id', 'parent_id', 'name']);
            $table->index(['company_id', 'parent_id', 'status']);
        });

        DB::statement("INSERT INTO categories (id, company_id, parent_id, name, description, status, created_at, updated_at) SELECT id, company_id, category_id, name, description, status, created_at, updated_at FROM sub_categories");

        foreach (DB::table('sub_categories')->select('id')->get() as $subCategory) {
            DB::table('products')->where('sub_category_id', $subCategory->id)->update([
                'category_id' => $subCategory->id,
                'sub_category_id' => null,
            ]);
        }
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_category_id');
        });
        Schema::dropIfExists('sub_categories');
    }
};
