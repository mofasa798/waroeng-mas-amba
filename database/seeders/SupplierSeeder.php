<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Sinar Jaya Abadi',
                'phone' => '021-5551234',
                'address' => 'Jl. Industri Raya No. 10, Jakarta',
                'notes' => 'Supplier utama snack & minuman',
            ],
            [
                'name' => 'CV Maju Makmur',
                'phone' => '021-5555678',
                'address' => 'Jl. Niaga No. 25, Bekasi',
                'notes' => 'Supplier sembako & kebutuhan rumah',
            ],
            [
                'name' => 'UD Berkah Jaya',
                'phone' => '0251-123456',
                'address' => 'Jl. Raya Bogor Km 15, Bogor',
                'notes' => 'Supplier rokok & peralatan mandi',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::create($data);
        }
    }
}
