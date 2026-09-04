<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(file_get_contents(database_path('seeders/data/products.json')), true);

        $categoryIds = ProductCategory::pluck('id', 'name');

        foreach ($rows as $row) {
            Product::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'product_category_id' => $categoryIds[$row['category_name']] ?? null,
                    'sku' => $row['sku'],
                    'ebay_item_number' => $row['ebay_item_number'],
                    'name' => $row['name'],
                    'meta_description' => $row['meta_description'],
                    'price' => $row['price'],
                    'currency' => $row['currency'],
                    'quantity_available' => $row['quantity_available'],
                    'quantity_sold' => $row['quantity_sold'],
                    'image_path' => $row['image_path'],
                    'body' => $row['body'],
                    'is_active' => $row['is_active'],
                ]
            );
        }
    }
}
