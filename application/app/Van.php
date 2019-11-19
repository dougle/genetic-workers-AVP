<?php

namespace App;

use App\Collections\VanCollection;
use Illuminate\Database\Eloquent\Model;

class Van extends Model
{
    public function packages() {
        return $this->belongsToMany(Package::class, 'package_solution', 'van_id', 'package_id');
    }

    public function solutions() {
        return $this->belongsToMany(Solution::class, 'package_solution', 'solution_id', 'van_id');
    }

    public function getTotalWeightAttribute() {
    	return $this->packages->sum(function ($package){
			return $package->weight;
		});
	}

	public function newCollection(array $models = [])
	{
		return new VanCollection($models);
	}

	public function toArray() {
		return [
			'id' => $this->id,
			'packages' => $this->packages->groupBy('pivot.van_id')->transform(function ($van) {
				return $van->transform(function($package){
					return $package->id .':'. $package->weight;
				})->toArray();
			})->toArray()
		];
	}
}
