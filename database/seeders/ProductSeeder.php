<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $cat = Category::pluck('id', 'name');
        $sup = Supplier::pluck('id', 'name');

        $products = [
            // ===== Makanan Ringan (Snacks) =====
            ['name' => 'Indomie Goreng',            'barcode' => '089686012345', 'cost_price' => 2500, 'selling_price' => 3500, 'stock' => 120, 'category' => 'Makanan Ringan', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Indomie Kuah Soto',         'barcode' => '089686012346', 'cost_price' => 2500, 'selling_price' => 3500, 'stock' => 100, 'category' => 'Makanan Ringan', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Chitato Sapi Panggang',     'barcode' => '089686012347', 'cost_price' => 8000, 'selling_price' => 11000, 'stock' => 50, 'category' => 'Makanan Ringan', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Qtela Singkong Pedas',      'barcode' => '089686012348', 'cost_price' => 6000, 'selling_price' => 8500, 'stock' => 40, 'category' => 'Makanan Ringan', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Taro Net 68gr',             'barcode' => '089686012349', 'cost_price' => 7000, 'selling_price' => 9500, 'stock' => 35, 'category' => 'Makanan Ringan', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Oreo Original 137gr',       'barcode' => '089686012350', 'cost_price' => 9000, 'selling_price' => 12000, 'stock' => 45, 'category' => 'Makanan Ringan', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Roma Malkist Crackers',     'barcode' => '089686012351', 'cost_price' => 5000, 'selling_price' => 7000, 'stock' => 60, 'category' => 'Makanan Ringan', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Beng-Beng',                 'barcode' => '089686012352', 'cost_price' => 2000, 'selling_price' => 3000, 'stock' => 80, 'category' => 'Makanan Ringan', 'supplier' => 'PT Sinar Jaya Abadi'],

            // ===== Minuman (Beverages) =====
            ['name' => 'Teh Botol Sosro 500ml',     'barcode' => '089686012353', 'cost_price' => 4000, 'selling_price' => 5500, 'stock' => 90, 'category' => 'Minuman', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Coca-Cola 390ml',           'barcode' => '089686012354', 'cost_price' => 5000, 'selling_price' => 7000, 'stock' => 70, 'category' => 'Minuman', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Aqua 600ml',                'barcode' => '089686012355', 'cost_price' => 2000, 'selling_price' => 3500, 'stock' => 150, 'category' => 'Minuman', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Mizone Botol 500ml',        'barcode' => '089686012356', 'cost_price' => 4000, 'selling_price' => 6000, 'stock' => 55, 'category' => 'Minuman', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Kopiko 3in1 Sachet',        'barcode' => '089686012357', 'cost_price' => 1500, 'selling_price' => 2000, 'stock' => 200, 'category' => 'Minuman', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Nutrisari Jeruk Peras',     'barcode' => '089686012358', 'cost_price' => 1000, 'selling_price' => 1500, 'stock' => 180, 'category' => 'Minuman', 'supplier' => 'PT Sinar Jaya Abadi'],

            // ===== Sembako (Staples) =====
            ['name' => 'Beras Maknyus 1kg',         'barcode' => '089686012359', 'cost_price' => 12000, 'selling_price' => 15000, 'stock' => 40, 'category' => 'Sembako', 'supplier' => 'CV Maju Makmur'],
            ['name' => 'Minyak Goreng Bimoli 1L',   'barcode' => '089686012360', 'cost_price' => 16000, 'selling_price' => 20000, 'stock' => 30, 'category' => 'Sembako', 'supplier' => 'CV Maju Makmur'],
            ['name' => 'Gula Pasir Gulaku 1kg',     'barcode' => '089686012361', 'cost_price' => 14000, 'selling_price' => 17000, 'stock' => 25, 'category' => 'Sembako', 'supplier' => 'CV Maju Makmur'],
            ['name' => 'Telur Ayam 1kg',            'barcode' => '089686012362', 'cost_price' => 22000, 'selling_price' => 28000, 'stock' => 20, 'category' => 'Sembako', 'supplier' => 'CV Maju Makmur'],
            ['name' => 'Tepung Segitiga Biru 1kg',  'barcode' => '089686012363', 'cost_price' => 10000, 'selling_price' => 13000, 'stock' => 15, 'category' => 'Sembako', 'supplier' => 'CV Maju Makmur'],
            ['name' => 'Kecap Bango 550ml',         'barcode' => '089686012364', 'cost_price' => 10000, 'selling_price' => 14000, 'stock' => 25, 'category' => 'Sembako', 'supplier' => 'CV Maju Makmur'],
            ['name' => 'Sambal ABC 340gr',          'barcode' => '089686012365', 'cost_price' => 7000, 'selling_price' => 9500, 'stock' => 30, 'category' => 'Sembako', 'supplier' => 'CV Maju Makmur'],

            // ===== Perlengkapan Rumah & Mandi =====
            ['name' => 'Sabun Lifebuoy 85gr',       'barcode' => '089686012366', 'cost_price' => 3000, 'selling_price' => 4500, 'stock' => 60, 'category' => 'Peralatan Mandi', 'supplier' => 'UD Berkah Jaya'],
            ['name' => 'Pepsodent 120gr',           'barcode' => '089686012367', 'cost_price' => 7000, 'selling_price' => 10000, 'stock' => 45, 'category' => 'Peralatan Mandi', 'supplier' => 'UD Berkah Jaya'],
            ['name' => 'Shampoo Clear 70ml',        'barcode' => '089686012368', 'cost_price' => 4000, 'selling_price' => 6000, 'stock' => 50, 'category' => 'Peralatan Mandi', 'supplier' => 'UD Berkah Jaya'],
            ['name' => 'Sabun Cuci Piring Sunlight', 'barcode' => '089686012369', 'cost_price' => 5000, 'selling_price' => 7500, 'stock' => 35, 'category' => 'Perlengkapan Rumah', 'supplier' => 'CV Maju Makmur'],
            ['name' => 'Sapu Lidi',                 'barcode' => '089686012370', 'cost_price' => 8000, 'selling_price' => 12000, 'stock' => 15, 'category' => 'Perlengkapan Rumah', 'supplier' => 'CV Maju Makmur'],

            // ===== Rokok & Linting =====
            ['name' => 'Sampoerna Mild 12',         'barcode' => '089686012371', 'cost_price' => 24000, 'selling_price' => 30000, 'stock' => 30, 'category' => 'Rokok & Linting', 'supplier' => 'UD Berkah Jaya'],
            ['name' => 'Dji Sam Soe Magnum',        'barcode' => '089686012372', 'cost_price' => 18000, 'selling_price' => 23000, 'stock' => 25, 'category' => 'Rokok & Linting', 'supplier' => 'UD Berkah Jaya'],
            ['name' => 'Surya Gudang Garam',        'barcode' => '089686012373', 'cost_price' => 20000, 'selling_price' => 26000, 'stock' => 20, 'category' => 'Rokok & Linting', 'supplier' => 'UD Berkah Jaya'],

            // ===== Makanan Berat =====
            ['name' => 'Nasi Padang Ayam',          'barcode' => '089686012374', 'cost_price' => 10000, 'selling_price' => 15000, 'stock' => 10, 'category' => 'Makanan Berat', 'supplier' => 'PT Sinar Jaya Abadi'],
            ['name' => 'Pecel Lele + Nasi',         'barcode' => '089686012375', 'cost_price' => 8000, 'selling_price' => 12000, 'stock' => 8, 'category' => 'Makanan Berat', 'supplier' => 'PT Sinar Jaya Abadi'],
        ];

        foreach ($products as $data) {
            $product = Product::create([
                'name' => $data['name'],
                'barcode' => $data['barcode'],
                'cost_price' => $data['cost_price'],
                'selling_price' => $data['selling_price'],
                'category_id' => $cat[$data['category']] ?? null,
                'supplier_id' => $sup[$data['supplier']] ?? null,
            ]);

            // Record initial stock
            if ($data['stock'] > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $data['stock'],
                    'note' => 'Stock awal',
                    'user_id' => 1,
                ]);
            }
        }

        $this->command->info('30 produk berhasil dibuat!');
    }
}
