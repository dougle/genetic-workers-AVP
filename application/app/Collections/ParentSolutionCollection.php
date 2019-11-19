<?php


namespace App\Collections;


use App\Package;
use App\Solution;
use App\Van;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ParentSolutionCollection extends Collection
{

	/**
	 * @param int $number_of_offspring
	 * @return Collection
	 */
	public function reproduce($number_of_offspring = 2) {
		if ($this->count() != 2) {
			Log::warning('More than two parents supplied for reproduction');
		}

		$offspring = new Collection();
		for ($i = $number_of_offspring; $i; $i--) {
			// clone parent and package pivot
			$child = Solution::create();
			$child->packages()->attach($this->first()->packages->keyBy('id')->transform(function($packages) {
				return ['van_id' => $packages->pivot->van_id];
			})->toArray());

			$father_packages = $this->last()->packages;

			// move some packages (from mother) to another van (father)
			foreach ($father_packages->chunk(ceil($father_packages->count() / $number_of_offspring))[$i-1] as $father_package) {
				// find the existing package in child
				$child_package = $child->packages->where('id', $father_package->id)->first();

				if(is_null($child_package)){
					Log::error('The package:'. $father_package->id . ' was not found in the child chromosome', [$child->toArray(), $father->toArray()]);
					continue;
				}

				// switch vans
				$target_van = Van::find($father_package->pivot->van_id);
				if(is_null($target_van)){
					Log::error('The van:'. $father_package->pivot->van_id . ' was not found in the child chromosome', [$child->toArray(), $father->toArray()]);
					continue;
				}

				$child_package->switch_van($child, $target_van);
			}

			// mutate until we have multiple vans
			if ($child->vans()->count() < 2) {
				$child->mutate();
			}

			$child->unsetRelation('packages');
			$child->unsetRelation('vans');

			$offspring->add($child);
		}

		return $offspring;
	}
}
