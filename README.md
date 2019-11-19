# Genetic Workers

## Overview

This Laravel project uses workers to calculate Genetic Alogrithm fitness, the GA problem implemented is currently the Van Problem whereby X delivery vans are travelling from A to B and they must in total transport Y packages each with a weight, one van cannot carry all packages but not all vans must make the trip but ideally the load would be spread equally across the vans.

The fitness function run by the workers is the difference between the heaviest and lightest vans per solution, the workers pull jobs off of a standard laravel worker job queue.

SQLite is used to keep track of the population:

```
   ___________        __________
   |Solutions|--------|Packages|
   -----------   |    ----------
				 |
			  ___|__
			  |vans|
			  ------
```
Solutions have many packages each assigned to a van, a package will appear in every solution and can also be assigned to many vans across solutions.

The chromosome of a solution is the configuration of the packages across vans, during reproduction a simple crossover is used, Eloquent is used to select the fittest group, one of which is cloned, another in the Collection of fittest solutions has a section of it's chromosome copied over to the new child solution, overwriting the original. Reproduction is done to create two different children that are inserted into the population and then evaluated (fitness function in worker job).

At the end of each generation the two weakest solutions are deleted to keep the population consistent.

There is a slight (configurable) chance of mutation which will move a random package to another van, this is to allow the GA to escape local minima. 


## Build
A copy exists on registry.internal.xq
You can build the container with:
`$ docker build -t avp .`

## Run
You can pass in some env variables e.g.
POPULATION                  int     The number of solutions to initialise and keep each generation - Default 100
GENERATIONS                 int     The number of times to reproduce, evaluate, and kill - Default 100
MUTATION                    int     The percentage chance of random mutation - Default 1%
SHOW_MUTATIONS              boolean Whether to show when mutations happen - Default false
EVALUATORS_PROCESSES_NUM    int     The number of evaluation workers to spawn - Default =POPULATION
ACC_FITNESS                 float   The maximum fitness value acceptable as a solution - Default 10
`$ docker run --rm -e GENERATIONS=999 -e MUTATION=2 avp`


## Develop
Mount the code into the container via:
`$ docker run --rm --name avp -v $PWD/application:/app/application avp`