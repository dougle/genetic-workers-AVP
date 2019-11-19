<?php

namespace App\Console\Commands;

use App\ParentSolution;
use App\Solution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EvolveAVP extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'avp:evolve {--generations= : The number of generations to evaluate} {--mutation= : The percentage chance of mutation} {--acceptable_fitness= : The maximum fitness value acceptable}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Evolve a population of avp solutions';

	protected $previous_fittest;

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		// wait for all jobs to finish initially
		$this->wait_for_jobs();

		$number_of_generations = $this->option('generations') ?? env('GENERATIONS', 100);
		$mutation_rate = $this->option('mutation') ?? env('MUTATION', 1);
		Log::info('Evolving '. $number_of_generations .' Generations with a '. $mutation_rate .'% chance of mutation');

		for ($i = $number_of_generations; $i; $i--) {
			// find the fittest solutions as a breeding pool
			$parents = ParentSolution::with(['packages'])->whereNotNull('fitness')->orderBy('fitness')->take(10)->get();
			$fittest = $parents->first();

			// only output if there is a change in fitness
			if($fittest->fitness != object_get($this->previous_fittest, 'fitness')){
				Log::info("Generation $i", $fittest->toArray());
			}


			if (!is_null($fittest->fitness) && $fittest->fitness <= $this->option('acceptable_fitness') ?? env('ACC_FITNESS', 10)) {
				Log::info("Solution found", $fittest->toArray());
				exit(0);
			}

			$children = $parents->shuffle()->take(2)->reproduce();

			// 0.1% chance of mutation
			if (mt_rand(0, 100) <= $mutation_rate) {
				$mutant = $children->shuffle()->first();
				$mutant->mutate();

				if((bool)env('SHOW_MUTATIONS', false)){
					Log::info('Mutating Solution', $mutant->toArray());
				}
			}

			// evaluate the children
			$children->each(function ($child) {
				$child->evaluate();
			});

			$this->wait_for_jobs();

			// kill weakest to keep population size constant
			Solution::query()->orderByDesc('fitness')->take($children->count())->delete();

			$this->previous_fittest = $fittest;
		}

		Log::info("Evolution finished", $fittest->toArray());
	}

	protected function wait_for_jobs() {
		// any solutions that have null fitness have not been evaluated
		while (Solution::whereNull('fitness')->count() > 0) {
			sleep(1);
		}
	}
}
