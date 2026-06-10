## Setup

- clone the repo and run the following commands

```sh
git clone https://github.com/mahdirahmani1376/wisetrack-back-end-test.git
cd wisetrack-back-end-test
cp ./src/.env.example .env
cp ./src/.env.testing.example .env.testing
docker compose up -d --build
docker exec -it wt-php composer install
docker exec -it wt-php php artisan migrate --seed
docker exec -it wt-php php artisan key:generate --env testing
docker exec -it wt-php php artisan test
```