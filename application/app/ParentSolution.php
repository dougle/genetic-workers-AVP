<?php

namespace App;

use App\Collections\ParentSolutionCollection;
use Illuminate\Support\Str;

class ParentSolution extends Solution
{
	protected $table = 'solutions';

    public function newCollection(array $models = [])
    {
        return new ParentSolutionCollection($models);
    }

	/**
	 * Get the parent model's half of the intermediate table name for belongsToMany relationships.
	 *
	 * @return string
	 */
	public function joiningTableSegment()
	{
		return Str::snake(class_basename(Solution::class));
	}

	/**
	 * Get the default foreign key name for the model.
	 *
	 * @return string
	 */
	public function getForeignKey()
	{
		return Str::snake(class_basename(Solution::class)).'_'.$this->getKeyName();
	}
}
