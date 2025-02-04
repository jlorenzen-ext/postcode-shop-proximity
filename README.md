## postcode-shop-proximity

Coding task for snappyshopper.

Technology used: Laravel 11, MySQL, Docker for local setup and containerization, PHPUnit for tests.

## Setup

Requirements:
- Docker (alternatively Rancher) installed on machine that is to run the containers

Create copy of .env.example and name it .env

Start by using Sail or Docker to spin up the containers:
``
docker-compose up -d
`` or ``./vendor/bin/sail up -d``.

After the containers have been built, you need to run migrations:

``docker exec proximity-main php artisan migrate``

And finally, run the command to fill up the postcode table with up-to-date postcodes for a specified region:

``docker exec proximity-main php artisan app:update-postcodes {region}``

Some valid regions within Scotland include AB, EH, G and FK. 

## Example Requests

### Create a new location

**POST** /api/location

````json
{
  "name": "Janniks Burger Joint",
  "longitude": -3.154815,
  "latitude": 55.882411,
  "status": "open",
  "type": "restaurant",
  "delivery_distance": 2000
}
````

Creates a new location with provided parameters (example is a location at EH209EG).

````http request
curl --location 'http://localhost/api/location' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
  "name": "Janniks Burger Joint",
  "longitude": -3.154815,
  "latitude": 55.882411,
  "status": "open",
  "type": "restaurant",
  "delivery_distance": 2000
}'
````

### Search locations by radius within postcode

**GET** /api/location/search/radius?radius={radiusInMetres}&postcode={postcode}

````http request
curl --location 'http://localhost/api/location/search/radius?radius=1000&postcode=EH209EG' \
--header 'Accept: application/json'
````

Will return all locations within a given radius of postcode (radius must be provided in meters).

### Search locations by delivery eligible postcode

**GET** /api/location/search/delivery-distance?postcode={postcode}

````http request
curl --location 'http://localhost/api/location/search/delivery-distance?postcode=EH209EG' \
--header 'Accept: application/json'
````

Will return all locations that deliver to the given postcode (where the postcode is within their defined delivery distance).

## Supplementary Notes

Stuff that I would have done if this was a full application:
- Fix command to insert postcodes with proper escaping/via Eloquent or prepared statement (see notes in command itself)
- Extend command to be more robust: add an option to load all postcodes, check if a postcode already exists, allow for updating existing entries, etc.
- API token authentication (to avoid unauthorised access to endpoints)
- Rate limiting
- Current application is calculating distances based in metres (since this is what MySQL supports out of the box for spatial data) - but for a UK app it might be worth considering some conversions to miles
