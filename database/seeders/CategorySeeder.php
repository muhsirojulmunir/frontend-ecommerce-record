<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sepatu Sport',
                'slug' => 'sepatu-sport',
                'description' => 'Koleksi sepatu sport untuk aktivitas olahraga dan gaya hidup aktif.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Sepatu Anak',
                'slug' => 'sepatu-anak',
                'description' => 'Sepatu nyaman dan tahan lama untuk anak-anak.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Sepatu Sekolah',
                'slug' => 'sepatu-sekolah',
                'description' => 'Sepatu sekolah berkualitas tinggi untuk setiap langkah.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Sepatu Lifestyle',
                'slug' => 'sepatu-lifestyle',
                'description' => 'Sepatu lifestyle trendy untuk gaya sehari-hari.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Sepatu Cewek',
                'slug' => 'sepatu-cewek',
                'description' => 'Koleksi sepatu khusus wanita dengan desain modern.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Sepatu Cowok',
                'slug' => 'sepatu-cowok',
                'description' => 'Koleksi sepatu pria dengan kualitas premium.',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
