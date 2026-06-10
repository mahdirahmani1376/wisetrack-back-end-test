## Setup

- clone the repo and run the following commands

```sh
cp /src/.env.example .env
cp /src/.env.testing.example .env.testing
docker compose up -d --build
docker exec -it wt-php composer install
docker exec -it wt-php php artisan migrate --seed
docker exec -it wt-php php artisan key:generate --env testing
docker exec -it wt-php php artisan test
```