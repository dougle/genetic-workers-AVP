<?php

namespace App\Console\Commands;

use App\Package;
use App\Solution;
use App\Van;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InitialiseAVP extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'avp:initialise {--population= : The population size}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Initialise a population of avp solutions';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$population_size = $this->option('population') ?? env('POPULATION', 100);
		Log::info('Initialising '. $population_size .' Solutions');

		$vans = Van::inRandomOrder()->get();
		$packages = Package::all();

		if ($vans->count() >= $packages->count()) {
			Log::error('Not enough packages for vans');
			exit;
		}

		// distribute all of the vans to a package, this guarantees that all vans will be used
		$van_ids = $vans->distribute($packages->count())->transform(function ($van) {
			return ['van_id' => $van->id];
		});

		for ($i = $population_size; $i; $i--) {
			$solution = Solution::create();
			$solution->packages()->attach($packages->shuffle(mt_rand())->pluck('id')->combine($van_ids->shuffle(mt_rand()))->toArray());

			$solution->evaluate();

			Log::debug('Created Solution ' . $i);
		}
	}
}
