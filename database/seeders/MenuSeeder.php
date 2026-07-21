<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Category;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $categoryNames = [
            'Espresso Based', 'Black Series', 'Signature', 'Blend Series', 
            'Tea Series', 'Matcha Series', 'Appetizer', 'Pasta', 'Main Course', 
            'Pizza', 'Dessert', 'Lainnya'
        ];
        $categories = [];
        
        foreach ($categoryNames as $i => $name) {
            $categories[$name] = Category::create([
                'name' => $name,
                'sort_order' => $i,
            ]);
        }

        $menus = [
            // ESPRESSO BASED
            ['name'=>'Espresso', 'category_name'=>'Espresso Based', 'description'=>'Espresso', 'price'=>15000, 'has_hot'=>true, 'price_hot'=>15000, 'desc_hot'=>'Espresso', 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>5.0, 'reviews'=>10, 'tag'=>null],
            ['name'=>'Americano', 'category_name'=>'Espresso Based', 'description'=>'Americano', 'price'=>25000, 'has_hot'=>true, 'price_hot'=>25000, 'desc_hot'=>'Espresso with hot water', 'has_ice'=>true, 'price_ice'=>31000, 'desc_ice'=>'Espresso, Water with ice', 'rating'=>4.8, 'reviews'=>15, 'tag'=>null],
            ['name'=>'Caffe Latte', 'category_name'=>'Espresso Based', 'description'=>'Caffe Latte', 'price'=>31000, 'has_hot'=>true, 'price_hot'=>31000, 'desc_hot'=>'Espresso, Fresh Milk Hot', 'has_ice'=>true, 'price_ice'=>32000, 'desc_ice'=>'Espresso, Fresh Milk with Ice', 'rating'=>4.9, 'reviews'=>20, 'tag'=>null],
            ['name'=>'Cappucino', 'category_name'=>'Espresso Based', 'description'=>'Cappucino', 'price'=>30000, 'has_hot'=>true, 'price_hot'=>30000, 'desc_hot'=>'Espresso, Fresh Milk Hot', 'has_ice'=>true, 'price_ice'=>32000, 'desc_ice'=>'Espresso, Fresh Milk with Ice', 'rating'=>4.7, 'reviews'=>18, 'tag'=>null],
            ['name'=>'Cream Vanilla', 'category_name'=>'Espresso Based', 'description'=>'Cream Vanilla', 'price'=>35000, 'has_hot'=>true, 'price_hot'=>35000, 'desc_hot'=>'Espresso, Vanilla Syrup, Creamer', 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.6, 'reviews'=>12, 'tag'=>null],

            // BLACK SERIES
            ['name'=>'Apple Black Sparkling', 'category_name'=>'Black Series', 'description'=>'Apple Black Sparkling', 'price'=>32000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>32000, 'desc_ice'=>'Espresso, Soda water, Juice Apple, Vanilla Syrup, with ice', 'rating'=>4.8, 'reviews'=>25, 'tag'=>null],
            ['name'=>'Americano Strawberry', 'category_name'=>'Black Series', 'description'=>'Americano Strawberry', 'price'=>33000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>33000, 'desc_ice'=>'Espresso, Strawberry pure, Apple Juice with ice', 'rating'=>4.7, 'reviews'=>19, 'tag'=>null],
            ['name'=>'Cranberry Black Sparkling', 'category_name'=>'Black Series', 'description'=>'Cranberry Black Sparkling', 'price'=>32000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>32000, 'desc_ice'=>'Espresso, Simple Syrup, Cranberry Juice, Soda Water with ice', 'rating'=>4.9, 'reviews'=>22, 'tag'=>null],
            ['name'=>'Guava Black Sparkling', 'category_name'=>'Black Series', 'description'=>'Guava Black Sparkling', 'price'=>32000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>32000, 'desc_ice'=>'Espresso, Soda Water, Juice Guava, Vanilla Syrup, with ice', 'rating'=>4.6, 'reviews'=>15, 'tag'=>null],

            // SIGNATURE
            ['name'=>'Coffee LDR', 'category_name'=>'Signature', 'description'=>'Coffee LDR', 'price'=>30000, 'has_hot'=>true, 'price_hot'=>30000, 'desc_hot'=>'Freshmilk Coconut, espresso, Brown Sugar', 'has_ice'=>true, 'price_ice'=>32000, 'desc_ice'=>'Freshmilk Coconut, Espresso, creamer, Brown Sugar with ice', 'rating'=>5.0, 'reviews'=>120, 'tag'=>'Terlaris'],
            ['name'=>'Caramel Machiato Slay', 'category_name'=>'Signature', 'description'=>'Caramel Machiato Slay', 'price'=>33000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>33000, 'desc_ice'=>'Freshmilk, Cimory, Syrup Butterscotch, Salted Caramel with caramel garnish and sauce', 'rating'=>4.8, 'reviews'=>85, 'tag'=>null],
            ['name'=>'Mocha Deep Talk', 'category_name'=>'Signature', 'description'=>'Mocha Deep Talk', 'price'=>34000, 'has_hot'=>true, 'price_hot'=>34000, 'desc_hot'=>'Espresso, Dark Choco, Creames and Freshmilk', 'has_ice'=>true, 'price_ice'=>41000, 'desc_ice'=>'Espresso, Syrup Vanilla, Dark choco, Sea Cream Whip with ice', 'rating'=>4.9, 'reviews'=>95, 'tag'=>'Premium'],
            ['name'=>'Butterscotch Cegil', 'category_name'=>'Signature', 'description'=>'Butterscotch Cegil', 'price'=>36000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>36000, 'desc_ice'=>'Freshmilk, Espresso, Syrup butterscotch, Sea Cream with ice', 'rating'=>4.7, 'reviews'=>70, 'tag'=>null],
            ['name'=>'Ice Coffee Kenapa Makassar', 'category_name'=>'Signature', 'description'=>'Ice Coffee Kenapa Makassar', 'price'=>34000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>34000, 'desc_ice'=>'Strawberry Milk, espresso, Syrup Vanilla, Butterscotch Powder, Creamer with ice', 'rating'=>4.8, 'reviews'=>65, 'tag'=>null],
            ['name'=>'Ice Coffee Bjir', 'category_name'=>'Signature', 'description'=>'Ice Coffee Bjir', 'price'=>35000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>35000, 'desc_ice'=>'Freshmilk, syrup Pandan, Creamer with ice', 'rating'=>4.6, 'reviews'=>50, 'tag'=>null],
            ['name'=>'Ice Coffee Baileys', 'category_name'=>'Signature', 'description'=>'Ice Coffee Baileys', 'price'=>39000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>39000, 'desc_ice'=>'Freshmilk, Espresso, Baileys Powder, Syrup Vanilla, Syrup Rum, Creamer with ice', 'rating'=>4.9, 'reviews'=>110, 'tag'=>'Hits'],
            ['name'=>'Ice Coffee Kalcer', 'category_name'=>'Signature', 'description'=>'Ice Coffee Kalcer', 'price'=>33000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>33000, 'desc_ice'=>'Espresso, Creamer, Freshmilk, Brown Sugar, Cinnamon Powder with ice', 'rating'=>4.7, 'reviews'=>80, 'tag'=>null],
            ['name'=>'Cream Bruelle Gwenchana', 'category_name'=>'Signature', 'description'=>'Cream Bruelle Gwenchana', 'price'=>35000, 'has_hot'=>true, 'price_hot'=>37000, 'desc_hot'=>'Espresso, Creamer, Freshmilk, Brown Sugar, Cinnamon Powder with ice', 'has_ice'=>true, 'price_ice'=>35000, 'desc_ice'=>'Espresso, Scrup Caramel, Freshmilk, Sea Cream Whip with ice', 'rating'=>4.8, 'reviews'=>90, 'tag'=>null],
            ['name'=>'Cream Manggo Coco Citrus', 'category_name'=>'Signature', 'description'=>'Cream Manggo Coco Citrus', 'price'=>34000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>34000, 'desc_ice'=>'Cream Manggo Coco Citrus', 'rating'=>4.6, 'reviews'=>45, 'tag'=>null],
            ['name'=>'Strawberry Snow Cream', 'category_name'=>'Signature', 'description'=>'Strawberry Snow Cream', 'price'=>34000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>34000, 'desc_ice'=>'Strawberry Snow Cream', 'rating'=>4.7, 'reviews'=>55, 'tag'=>null],
            ['name'=>'Minty Berry Bliss', 'category_name'=>'Signature', 'description'=>'Minty Berry Bliss', 'price'=>34000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>34000, 'desc_ice'=>'Minty Berry Bliss', 'rating'=>4.8, 'reviews'=>60, 'tag'=>null],

            // BLEND SERIES
            ['name'=>'Choco Butter Yolo', 'category_name'=>'Blend Series', 'description'=>'Choco Butter Yolo', 'price'=>39000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>39000, 'desc_ice'=>'Dark Choco, Butterscotch Syrup, Sea Cream Whip, Freshmilk with ice', 'rating'=>4.9, 'reviews'=>105, 'tag'=>'Terlaris'],
            ['name'=>'Salt Choco Sans', 'category_name'=>'Blend Series', 'description'=>'Salt Choco Sans', 'price'=>36000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>36000, 'desc_ice'=>'Dark choco, Syrup Caramel, Syrup Salted Caramel, Freshmilk, Sea Cream Whip with ice', 'rating'=>4.8, 'reviews'=>85, 'tag'=>null],
            ['name'=>'Ice Chocolate Pw', 'category_name'=>'Blend Series', 'description'=>'Ice Chocolate Pw', 'price'=>30000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>30000, 'desc_ice'=>'Dark Choco, Water, Simple Syrup, Creamer with ice', 'rating'=>4.7, 'reviews'=>75, 'tag'=>null],
            ['name'=>'Cheese YGY', 'category_name'=>'Blend Series', 'description'=>'Cheese YGY', 'price'=>37000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>37000, 'desc_ice'=>'Cheese Biscuit, Vanilla Syrup, Sea Cream Whip, Freshmilk, Topping Caramel with ice', 'rating'=>4.8, 'reviews'=>95, 'tag'=>'Hits'],
            ['name'=>'Cookies & Cream', 'category_name'=>'Blend Series', 'description'=>'Cookies & Cream', 'price'=>33000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>33000, 'desc_ice'=>'Oreo Biscuit, Syrup Vanilla, Sea Cream Whip, Fresh Milk with ice', 'rating'=>4.9, 'reviews'=>115, 'tag'=>null],
            ['name'=>'Lotus Gamon', 'category_name'=>'Blend Series', 'description'=>'Lotus Gamon', 'price'=>38000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>38000, 'desc_ice'=>'Lotus Biscuit, Syrup Butterscotch, sea Cream Whip, Freshmilk with ice', 'rating'=>4.8, 'reviews'=>88, 'tag'=>null],

            // TEA SERIES
            ['name'=>'Lychee Tea', 'category_name'=>'Tea Series', 'description'=>'Lychee Tea', 'price'=>29000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>29000, 'desc_ice'=>'Black Tea, Lychee powder, Lychee fruit, Simple Syrup with ice', 'rating'=>4.7, 'reviews'=>65, 'tag'=>null],
            ['name'=>'Lemon Tea', 'category_name'=>'Tea Series', 'description'=>'Lemon Tea', 'price'=>29000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>29000, 'desc_ice'=>'Black Tea, Lemon Powder, Simple Syrup with ice', 'rating'=>4.6, 'reviews'=>50, 'tag'=>null],
            ['name'=>'Manggo Tea', 'category_name'=>'Tea Series', 'description'=>'Manggo Tea', 'price'=>30000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>30000, 'desc_ice'=>'Black tea, Manggo Syrup, Simple Syrup with ice', 'rating'=>4.8, 'reviews'=>70, 'tag'=>null],
            ['name'=>'Lemonade Berry', 'category_name'=>'Tea Series', 'description'=>'Lemonade Berry', 'price'=>33000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>33000, 'desc_ice'=>'Lemon Powder, water, Strawberry Jam. Simple Syrup, with ice', 'rating'=>4.7, 'reviews'=>55, 'tag'=>null],
            ['name'=>'Passion Fruit Ucul', 'category_name'=>'Tea Series', 'description'=>'Passion Fruit Ucul', 'price'=>28000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>28000, 'desc_ice'=>'Lemon Powder, Water, Markisa Syrup, Simple Syrup with ice', 'rating'=>4.6, 'reviews'=>40, 'tag'=>null],
            ['name'=>'Milk Tea', 'category_name'=>'Tea Series', 'description'=>'Milk Tea', 'price'=>29000, 'has_hot'=>true, 'price_hot'=>29000, 'desc_hot'=>'Tea, Condense Milk', 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.5, 'reviews'=>35, 'tag'=>null],

            // MATCHA SERIES
            ['name'=>'Matcha Flanky', 'category_name'=>'Matcha Series', 'description'=>'Matcha Flanky', 'price'=>38000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>38000, 'desc_ice'=>'Sea Salt, Whip Cream, Freshmilk, Condense Milk, Matcha Powder with ice', 'rating'=>4.9, 'reviews'=>90, 'tag'=>'Terlaris'],
            ['name'=>'Matcha Earl Grey', 'category_name'=>'Matcha Series', 'description'=>'Matcha Earl Grey', 'price'=>38000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>38000, 'desc_ice'=>'Matcha Powder, Freshmilk, Whip Cream, Condense Milk, earl Grey with ice', 'rating'=>4.8, 'reviews'=>85, 'tag'=>null],
            ['name'=>'Strawberry Matcha', 'category_name'=>'Matcha Series', 'description'=>'Strawberry Matcha', 'price'=>36000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>36000, 'desc_ice'=>'Strawberry Pure, Strawberry Milk, Whip Cream, Freshmilk, Condense Milk, Matcha powder with ice', 'rating'=>4.9, 'reviews'=>110, 'tag'=>'Hits'],
            ['name'=>'Matcha Latte', 'category_name'=>'Matcha Series', 'description'=>'Matcha Latte', 'price'=>31000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>31000, 'desc_ice'=>'Matcha Powder, Freshmilk, Condense Milk, Whip Cream with ice', 'rating'=>4.7, 'reviews'=>75, 'tag'=>null],
            ['name'=>'Matcha Sparkling', 'category_name'=>'Matcha Series', 'description'=>'Matcha Sparkling', 'price'=>35000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>true, 'price_ice'=>35000, 'desc_ice'=>'Matcha Powder, Freshmilk, Condense Milk, Whip Cream, Soda Water with ice', 'rating'=>4.8, 'reviews'=>80, 'tag'=>null],

            // APPETIZER
            ['name'=>'French Fries', 'category_name'=>'Appetizer', 'description'=>'French Fries', 'price'=>33000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.6, 'reviews'=>40, 'tag'=>null],
            ['name'=>'Cheese Fries', 'category_name'=>'Appetizer', 'description'=>'Cheese Fries', 'price'=>38000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>55, 'tag'=>'Hits'],
            ['name'=>'Miso Honey Chicken Wings', 'category_name'=>'Appetizer', 'description'=>'Miso Honey Chicken Wings', 'price'=>48000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.9, 'reviews'=>70, 'tag'=>'Premium'],
            ['name'=>'OG Chicken Wings', 'category_name'=>'Appetizer', 'description'=>'OG Chicken Wings', 'price'=>42000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.7, 'reviews'=>45, 'tag'=>null],
            ['name'=>'Popcorn Chicken', 'category_name'=>'Appetizer', 'description'=>'Popcorn Chicken', 'price'=>48000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>60, 'tag'=>null],
            ['name'=>'Skena Platter', 'category_name'=>'Appetizer', 'description'=>'Skena Platter', 'price'=>58000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.9, 'reviews'=>85, 'tag'=>'Terlaris'],
            ['name'=>'Italian Potato Nachos', 'category_name'=>'Appetizer', 'description'=>'Italian Potato Nachos', 'price'=>42000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.7, 'reviews'=>50, 'tag'=>null],
            ['name'=>'Beef Burger', 'category_name'=>'Appetizer', 'description'=>'Beef Burger', 'price'=>45000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>65, 'tag'=>null],
            ['name'=>'Spring Roll', 'category_name'=>'Appetizer', 'description'=>'Spring Roll', 'price'=>31000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.6, 'reviews'=>35, 'tag'=>null],
            ['name'=>'Classic Indonesian Plater', 'category_name'=>'Appetizer', 'description'=>'Classic Indonesian Plater', 'price'=>38000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.7, 'reviews'=>40, 'tag'=>null],

            // PASTA
            ['name'=>'Aglio e Olio Chicken', 'category_name'=>'Pasta', 'description'=>'Aglio e Olio Chicken', 'price'=>48000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>60, 'tag'=>null],
            ['name'=>'Cream Alfredo', 'category_name'=>'Pasta', 'description'=>'Cream Alfredo', 'price'=>58000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.9, 'reviews'=>75, 'tag'=>'Premium'],
            ['name'=>'Black Pasta with Seafood', 'category_name'=>'Pasta', 'description'=>'Black Pasta with Seafood', 'price'=>68000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.9, 'reviews'=>80, 'tag'=>'Terlaris'],

            // MAIN COURSE
            ['name'=>'Nasi Goreng', 'category_name'=>'Main Course', 'description'=>'Nasi Goreng', 'price'=>58000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.7, 'reviews'=>65, 'tag'=>null],
            ['name'=>'Chicken Nanban', 'category_name'=>'Main Course', 'description'=>'Chicken Nanban', 'price'=>58000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>70, 'tag'=>null],
            ['name'=>'Beef Bowl', 'category_name'=>'Main Course', 'description'=>'Beef Bowl', 'price'=>68000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.9, 'reviews'=>85, 'tag'=>'Terlaris'],
            ['name'=>'Soto Betawi', 'category_name'=>'Main Course', 'description'=>'Soto Betawi', 'price'=>72000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>60, 'tag'=>null],
            ['name'=>'Sate Ayam', 'category_name'=>'Main Course', 'description'=>'Sate Ayam', 'price'=>45000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.7, 'reviews'=>55, 'tag'=>null],
            ['name'=>'Lodeh Grill Ribs', 'category_name'=>'Main Course', 'description'=>'Lodeh Grill Ribs', 'price'=>70000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.9, 'reviews'=>75, 'tag'=>'Premium'],
            ['name'=>'Ayam Bakar Sambal Dadak', 'category_name'=>'Main Course', 'description'=>'Ayam Bakar Sambal Dadak', 'price'=>45000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>65, 'tag'=>null],

            // PIZZA
            ['name'=>'Holly Meat', 'category_name'=>'Pizza', 'description'=>'Holly Meat', 'price'=>85000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.9, 'reviews'=>80, 'tag'=>'Terlaris'],
            ['name'=>'Truffle Mushroom', 'category_name'=>'Pizza', 'description'=>'Truffle Mushroom', 'price'=>80000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>65, 'tag'=>'Premium'],
            ['name'=>'Margaritha', 'category_name'=>'Pizza', 'description'=>'Margaritha', 'price'=>68000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.7, 'reviews'=>50, 'tag'=>null],
            ['name'=>'Pepperoni', 'category_name'=>'Pizza', 'description'=>'Pepperoni', 'price'=>75000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>70, 'tag'=>'Hits'],

            // DESSERT
            ['name'=>'Chocolate Tiramisu', 'category_name'=>'Dessert', 'description'=>'Classic Tiramisu Cake with Glass', 'price'=>35000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>55, 'tag'=>null],
            ['name'=>'Cheese Cake', 'category_name'=>'Dessert', 'description'=>'Burn Cheese Cake with Ice Cream', 'price'=>45000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.9, 'reviews'=>80, 'tag'=>'Hits'],
            ['name'=>'Matcha Strawberry', 'category_name'=>'Dessert', 'description'=>'Matcha Strawberry Cake', 'price'=>45000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.7, 'reviews'=>60, 'tag'=>null],
            ['name'=>'Chocolate Cake', 'category_name'=>'Dessert', 'description'=>'Chocolate paste Tulip, Butter President/Anchor un', 'price'=>45000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>45, 'tag'=>null],
            ['name'=>'Matcha Cheese Cake', 'category_name'=>'Dessert', 'description'=>'Matcha Powder, Cream Cheese, Ice Cream', 'price'=>55000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.9, 'reviews'=>70, 'tag'=>'Premium'],
            ['name'=>'Pannaconta', 'category_name'=>'Dessert', 'description'=>'Fresh Milk, Milad Gold, Vanilla Essence, Ice Cream', 'price'=>35000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.6, 'reviews'=>35, 'tag'=>null],
            ['name'=>'Red Velvet Cake', 'category_name'=>'Dessert', 'description'=>'Spon Cake, Milac Gold, Cream Cheese, Icing Sugar, White Butter', 'price'=>45000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.8, 'reviews'=>50, 'tag'=>null],
            ['name'=>'Dadar Cheese Cream', 'category_name'=>'Dessert', 'description'=>'Dadar Cheese Cream', 'price'=>35000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.7, 'reviews'=>40, 'tag'=>null],

            // LAINNYA
            ['name'=>'Air Mineral', 'category_name'=>'Lainnya', 'description'=>'Air Mineral', 'price'=>18000, 'has_hot'=>false, 'price_hot'=>null, 'desc_hot'=>null, 'has_ice'=>false, 'price_ice'=>null, 'desc_ice'=>null, 'rating'=>4.5, 'reviews'=>20, 'tag'=>null],
        ];

        foreach ($menus as $i => $menuData) {
            $catName = $menuData['category_name'];
            unset($menuData['category_name']);
            
            $menuData['category_id'] = $categories[$catName]->id;
            $menuData['sort_order'] = $i;
            Menu::create($menuData);
        }
    }
}
