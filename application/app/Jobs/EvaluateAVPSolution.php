<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EvaluateAVPSolution implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var string
	 */


	protected $solution;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($solution) {
		$this->onQueue('avp-evaluate');
		$this->solution = $solution;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		$solution = $this->solution;
		$vans = $this->solution->vans()->with(['packages' => function($q) use ($solution) {
			$q->where('solution_id', $solution->id);
		}])->get()->sortBy('total_weight');

		$delta = $vans->last()->total_weight - $vans->first()->total_weight;

		$this->solution->update(['fitness' => $delta]);

		Log::debug('Updating solution fitness', $this->solution->toArray());
	}
}
