<?php

use App\Van;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('vans')->truncate();

        for ($i=mt_rand(2, 5);$i;$i--) {
            Van::create();
        }
    }
}
