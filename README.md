## postcode-shop-proximity

Coding task for snappyshopper.

Technology used: Laravel 11, MySQL, Docker for local setup and containerization, PHPUnit for tests.

## Setup

Requirements:
- Docker (alternatively Rancher) installed on machine that is to run the containers

Start by using Sail or Docker to spin up the containers:
``
docker-compose up -d
`` or ``./vendor/bin/sail up -d``.

After the containers have been built, you need to run migrations:
``docker exec php artisan migrate``

And finally, run a command to update the postcode table with up-to-date data:
``docker exec php artisan app:update-postcodes``

## Example Requests

### Create a new location

**POST** /api/location

````json
{
  "name": "Joe's Pizza",
  "longitude": -73.935242,
  "latitude": 40.730610,
  "status": "open",
  "type": "restaurant",
  "delivery_distance": 5000
}
````

### Search locations by postcode

**GET** 

````json
{
  "postcode": "EC1A 1BB",
  "radius": 5000
}
````

## Supplementary Notes

Stuff that I would have done if this was a full application:
- API token authentication (to avoid unauthorised access to endpoints)
- Rate limiting
