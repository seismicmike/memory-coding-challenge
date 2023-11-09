# Memory Game

This application was developed as a coding challenge.

* [Requirements](#requirements)
  * [Specifications](#specifications)
  * [Sample requests and responses](#sample-requests-and-responses)
  * [Sample validation errors](#sample-validation-errors)
* [Solution](#solution)
  * [Local environment](#local-environment)
  * [Drupal](#drupal)
  * [The API Endpoint](#the-api-endpoint)
  * [Unit Testing](#unit-testing)
  * [Code Quality](#code-quality)

## Requirements

The following were the requirements for this challenge:
* Use Drupal 8 or 9
* Set up a local environment for development and testing
* Submit code as either a git repository or a zip folder
* Generate PHPUnit tests
* Generate a swagger file with OpenAPI Spec 3.0
* Create an API end point that produces a magic memory game according to the specifications

### Specifications
The specifications for the endpoint are as follows:
* The endpoint takes 2 query parameters: rows and columns.
* Both parameters are required, should be greater than zero but no greater than 6, and at
least one of them needs to be an even number.
* To build the response you’ll need to come up with a list of at least 18 items of your
choice, such as numbers, letters, fruit names, etc (the sample responses below use
letters).
* The response should contain meta data and a 2-dimensional array with as many rows
and columns as the query parameter values.
* The array will have a total of ((rows * columns) / 2) unique items repeated twice.
* Each item will be placed in a random location every time a request is made.

### Sample requests and responses:

GET /code-challenge/card-grid?rows=2&columns=2
```json
{
  "meta": {
    "success": true,
    "cardCount": 4,
    "uniqueCardCount": 2,
    "uniqueCards": ["D", "G"]
  },
  "data": {
    "cards": [
      ["G", "D"],
      ["D", "G"]
    ]
  }
}
```

GET /code-challenge/card-grid?rows=4&columns=6
```json
{
  "meta": {
    "success": true,
    "cardCount": 24,
    "uniqueCardCount": 12,
    "uniqueCards": ["S","B","R","A","N","W","Q","F","V","L","J","X"]
  },
  "data": {
    "cards": [
      ["R","R","Q","Q","L","S"],
      ["X","N","A","V","J","A"],
      ["B","W","X","L","W","N"],
      ["V","F","S","J","B","F"]
    ]
  }
}
```

### Sample Validation Errors

GET /code-challenge/card-grid?rows=3&columns=5
```json
{
  "meta": {
    "success": false,
    "message": "Either `rows` or `columns` needs to be an even number."
  },
  "data": {}
}
```

GET /code-challenge/card-grid?rows=10&columns=6
```json
{
  "meta": {
    "success": false,
    "message": "Row count is greater than 6"
  },
  "data": {}
}
```

## Solution

### Local Environment

The local environment for this application was created using [Lando](https://devwithlando.io/download/). Follow the instructions to install Lando and then run ```lando start``` to start up the container. Once started, the application will become available at https://memory.lndo.site. To deploy the setup of the custom module and endpoint, import a database that should be provided externally. Download the database file, unzip it and place it in the database_backups folder. Then use lando db-import to import it.

Assuming that you are on a Mac and your code is in your ~/code directory under the folder "memory".

```shell
$ cd ~/code/memory/database_backups
$ mv ~/Downloads/setup.sql.gz .
$ gunzip setup.sql.gz
$ cd ..
$ lando db-import database_backups/setup.sql
```

### Drupal

This application was developed in Drupal. However since it was developed in November of 2023 when Drupal 9 has reached End of Life, I have chosen Drupal 10 instead of 8 or 9.

### The API Endpoint

The endpoint is provided by a custom module called Memory Game (machine name: memory_game). The module contains:
* A [controller](web/modules/custom/memory_game/src/Controller/MemoryGameController.php) that provides the route /code-challenge/card-grid and returns the API response
* A [validator service class](web/modules/custom/memory_game/src/RequestValidator.php) that provides validation checking of the input
* A [GridGenerator](web/modules/custom/memory_game/src/GridGenerator.php) class that handles the logic of generating the grid

API responses go through the following algorithm:
1. We initialize an error response code and response object so we have that to fall back on.
1. We pass the request to the validator service class to see if it is valid.
1. The validator checks all conditions and returns TRUE only if all conditions pass.
   * If it fails, it stores an error message we can use for our response message.
1. If validation fails, we update the message to reflect what the error was.
1. If validation succeeds, we generate a grid and update the response code and response object to output the data.
1. Then we return the response as a JsonResponse object with a status code of 200 or 400 depending on whether validation succeeded.

See the swagger file in [json](docs/openapi.json) or [yaml](docs/openapi.json) for more detail on the API.


### Unit Testing

The validation service class may be tested using the [RequestValidatorTest](web/modules/custom/memory_game/tests/src/Unit/RequestValidatorTest.php) class.

To execute only this test, run
```
php vendor/bin/php web/modules/custom/memory_game/tests/src/Unit/RequestValidatorTest.php
```

The grid generator class may be tested using the [GridGeneratorTest](web/modules/custom/memory_game/tests/src/Unit/GridGeneratorTest.php)

To execute only this test, run
```
php vendor/bin/php web/modules/custom/memory_game/tests/src/Unit/GridGeneratorTest.php
```

### Code Quality

I have enforced code quality using [grumphp](https://github.com/phpro/grumphp) to scan the project with PHPCS and PHPStan. PHPCS uses both the Drupal and DrupalPractice coding standards. Grumphp registers its checks as a git pre-commit hook to prevent committing bad code. I've also provided tooling to the lando container to enable easily running its checks.

To run grumphp directly, run:

```
lando grumphp
```

Grumphp can easily be set up in github using github actions to lint the code prior to merging pull requests if it is so desired.
