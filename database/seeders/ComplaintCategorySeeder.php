<?php

namespace Database\Seeders;

use App\Models\ComplaintCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComplaintCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach ([
            ['name' => 'Service', 'type' => 'service'],
            ['name' => 'Product', 'type' => 'product'],
            ['name' => 'Feedback', 'type' => 'feedback'],
            ['name' => 'Other', 'type' => 'other'],
        ] as $index => $category) {
            ComplaintCategory::query()->updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'type' => $category['type'],
                    'description' => $category['name'] . ' related complaints.',
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
