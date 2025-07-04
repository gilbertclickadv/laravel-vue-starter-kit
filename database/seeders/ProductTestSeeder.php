<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;

class ProductTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $user1 = User::firstOrCreate(
            ['email' => 'vendor1@example.com'],
            [
                'name' => 'Test Vendor 1',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'vendor2@example.com'],
            [
                'name' => 'Test Vendor 2',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Create test vendors
        $vendor1 = Vendor::firstOrCreate(
            ['user_id' => $user1->id],
            [
                'company_name' => 'Tech Solutions Inc.',
                'store_name' => 'Tech Store',
                'status' => 'active',
            ]
        );

        $vendor2 = Vendor::firstOrCreate(
            ['user_id' => $user2->id],
            [
                'company_name' => 'Fashion World Ltd.',
                'store_name' => 'Fashion Boutique',
                'status' => 'active',
            ]
        );

        // Create test categories
        $electronicsCategory = Category::firstOrCreate(
            ['name' => 'Electronics'],
            [
                'description' => 'Electronic gadgets and devices',
                'status' => 'active',
                'parent_id' => null,
            ]
        );

        $fashionCategory = Category::firstOrCreate(
            ['name' => 'Fashion'],
            [
                'description' => 'Clothing and accessories',
                'status' => 'active',
                'parent_id' => null,
            ]
        );

        $phonesCategory = Category::firstOrCreate(
            ['name' => 'Smartphones'],
            [
                'description' => 'Mobile phones and accessories',
                'status' => 'active',
                'parent_id' => $electronicsCategory->id,
            ]
        );

        // Create test attributes
        $colorAttribute = Attribute::firstOrCreate(
            ['slug' => 'color'],
            [
                'name' => 'Color',
                'description' => 'Product color',
                'type' => 'color',
                'is_required' => true,
                'sort_order' => 1,
            ]
        );

        $sizeAttribute = Attribute::firstOrCreate(
            ['slug' => 'size'],
            [
                'name' => 'Size',
                'description' => 'Product size',
                'type' => 'dropdown',
                'is_required' => true,
                'sort_order' => 2,
            ]
        );

        $storageAttribute = Attribute::firstOrCreate(
            ['slug' => 'storage'],
            [
                'name' => 'Storage',
                'description' => 'Device storage capacity',
                'type' => 'dropdown',
                'is_required' => false,
                'sort_order' => 3,
            ]
        );

        // Create attribute values for Color
        $colorValues = [
            ['value' => 'Red', 'hex' => '#FF0000'],
            ['value' => 'Blue', 'hex' => '#0000FF'],
            ['value' => 'Green', 'hex' => '#00FF00'],
            ['value' => 'Black', 'hex' => '#000000'],
            ['value' => 'White', 'hex' => '#FFFFFF'],
        ];

        foreach ($colorValues as $index => $colorValue) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $colorAttribute->id,
                    'value' => $colorValue['value'],
                ],
                [
                    'hex' => $colorValue['hex'],
                    'sort_order' => $index,
                ]
            );
        }

        // Create attribute values for Size
        $sizeValues = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizeValues as $index => $sizeValue) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $sizeAttribute->id,
                    'value' => $sizeValue,
                ],
                [
                    'sort_order' => $index,
                ]
            );
        }

        // Create attribute values for Storage
        $storageValues = ['64GB', '128GB', '256GB', '512GB', '1TB'];
        foreach ($storageValues as $index => $storageValue) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $storageAttribute->id,
                    'value' => $storageValue,
                ],
                [
                    'sort_order' => $index,
                ]
            );
        }

        $this->command->info('Sample data created successfully!');
        $this->command->info('Test Users:');
        $this->command->info('- vendor1@example.com / password');
        $this->command->info('- vendor2@example.com / password');
    }
}
