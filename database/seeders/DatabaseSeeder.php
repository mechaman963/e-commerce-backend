<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(1)->withToken()->create();
        
        // Clear existing data to avoid conflicts
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProductImage::truncate();
        Product::truncate();
        Category::truncate();
        User::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Create categories first
        Category::factory(50)->create();
        
        // Create products that reference existing categories
        Product::factory(100)->create();
        
        // Create product images
        ProductImage::factory(400)->create();
    
        // Create admin user (role: 1995)
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => '1995',
        ]);

        // Create product manager user (role: 1999)
        User::create([
            'name' => 'Product Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => '1999',
        ]);

        // Create regular user (role: 2001)
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => '2001',
        ]);

        // Create manager user (role: 3276)
        User::create([
            'name' => 'Manager User',
            'email' => 'manager2@example.com',
            'password' => Hash::make('password'),
            'role' => '3276',
        ]);
    }
}