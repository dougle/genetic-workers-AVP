<?php


namespace App\Collections;


use App\Package;
use App\Solution;
use App\Van;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class VanCollection extends Collection
{
	public function distribute($count){
		while($this->count() < $count){
			foreach($this->take($count - $this->count()) as $item){
				$this->add($item);
			}
		}

		return $this->take($count);
	}
}
