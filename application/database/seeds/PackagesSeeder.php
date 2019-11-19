<?php

use App\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('packages')->truncate();

        for ($i=mt_rand(5, 20);$i;$i--) {
            Package::create([
                'weight' => mt_rand(0, 1000) / 10
            ]);
        }
    }
}
