<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Package extends Model
{
	public function solutions() {
		return $this->belongsToMany(Solution::class);
	}

	public function switch_van(Solution $solution, Van $van) {
		$original_van_id = $this->pivot->van_id;
		$switch_packages = $solution->packages->where('pivot.van_id', $van->id);

		$this->solutions()->updateExistingPivot($solution, ['van_id' => $van->id], true);

		// switch a package onto the original van to maintain a spread
		if($switch_packages->count() > 0){
			$switch_packages->random()->solutions()->updateExistingPivot($solution, ['van_id' => $original_van_id], true);
		}
	}
}
