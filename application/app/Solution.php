<?php

namespace App;

use App\Jobs\EvaluateAVPSolution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Solution extends Model
{
    use DispatchesJobs;
    protected $fillable = ['fitness'];
	protected $casts = [
		'fitness' => 'float'
	];

    public function evaluate(){
		if (is_null($this->fitness) && $this->packages()->count() > 0) {
			$this->dispatch(new EvaluateAVPSolution($this));
		}
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function packages() {
		return $this->belongsToMany(Package::class, $this->joiningTable(Package::class))->withPivot('van_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function vans() {
		return $this->belongsToMany(Van::class, 'package_solution', 'solution_id', 'van_id');
	}

	public function mutate() {
		$package = $this->packages()->inRandomOrder()->first();
		$van = Van::where('id', '!=', $package->pivot->van_id)->inRandomOrder()->first();

		// mutate until we have multiple vans
		$this->packages()->updateExistingPivot($package, ['van_id' => $van->id], true);
		$this->unsetRelation('vans');
		$this->unsetRelation('packages');

		return $this;
	}

	public function toArray() {
    	return [
    		'id' => $this->id,
			'fitness' => $this->fitness,
			'packages' => $this->packages->groupBy('pivot.van_id')->sortKeys()->transform(function ($van) {
				return $van->transform(function($package){
					return $package->id .':'. $package->weight;
				})->toArray();
			})->toArray()
		];
	}
}
