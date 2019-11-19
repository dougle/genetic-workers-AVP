#!/bin/sh

chown -R nobody:nobody \
	/app/application/storage/framework \
	/app/application/storage/logs \
	/app/application/storage/app \
	/app/application/bootstrap/cache

[ ! -s /app/application/.env ] && cp -a /app/application/.env.example  /app/application/.env
touch /app/application/database/database.sqlite

# default the number of workers to the population size
POPULATION=${POPULATION:=100}
EVALUATORS_PROCESSES_NUM=${EVALUATORS_PROCESSES_NUM:=$POPULATION}

# supervisor abandoned here as it will hold the container open

# spin up the workers
for i in $(seq 0 $EVALUATORS_PROCESSES_NUM);do
  php /app/application/artisan queue:listen --queue=avp-evaluate --tries=3 --sleep=3 --timeout=30 &
done

# boot and start GA
cd /app/application
php artisan migrate
php artisan db:seed
php artisan avp:initialise
php artisan avp:evolve

# forget about the workers and just exit

exit 0