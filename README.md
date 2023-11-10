# Memory Game

This application was developed as a coding challenge.

- [Requirements](#requirements)
  - [Specifications](#specifications)
  - [Sample requests and responses](#sample-requests-and-responses)
  - [Sample validation errors](#sample-validation-errors)
- [Solution](#solution)
  - [Local environment](#local-environment)
  - [Drupal](#drupal)
  - [The API Endpoint](#the-api-endpoint)
  - [Unit Testing](#unit-testing)
  - [Code Quality](#code-quality)

## Requirements

The following were the requirements for this challenge:

- Use Drupal 8 or 9
- Set up a local environment for development and testing
- Submit code as either a git repository or a zip folder
- Generate PHPUnit tests
- Generate a swagger file with OpenAPI Spec 3.0
- Create an API end point that produces a magic memory game according to the specifications

### Specifications

The specifications for the endpoint are as follows:

- The endpoint takes 2 query parameters: rows and columns.
- Both parameters are required, should be greater than zero but no greater than 6, and at
  least one of them needs to be an even number.
- To build the response you’ll need to come up with a list of at least 18 items of your
  choice, such as numbers, letters, fruit names, etc (the sample responses below use
  letters).
- The response should contain meta data and a 2-dimensional array with as many rows
  and columns as the query parameter values.
- The array will have a total of ((rows \* columns) / 2) unique items repeated twice.
- Each item will be placed in a random location every time a request is made.

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
    "uniqueCards": ["S", "B", "R", "A", "N", "W", "Q", "F", "V", "L", "J", "X"]
  },
  "data": {
    "cards": [
      ["R", "R", "Q", "Q", "L", "S"],
      ["X", "N", "A", "V", "J", "A"],
      ["B", "W", "X", "L", "W", "N"],
      ["V", "F", "S", "J", "B", "F"]
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

The local environment for this application was created using [Lando](https://devwithlando.io/download/). Follow the instructions to install Lando and then run `lando start` to start up the container. Once started, the application will become available at https://memory.lndo.site. To deploy the setup of the custom module and endpoint, import a database that should be provided externally. Download the database file, unzip it and place it in the database_backups folder. Then use lando db-import to import it. 

Assuming that you are on a Mac and your code is in your ~/code directory under the folder "memory".

```shell
$ cd ~/code/memory/database_backups
$ mv ~/Downloads/setup.sql.gz .
$ gunzip setup.sql.gz
$ cd ..
$ lando db-import database_backups/setup.sql
```

Importing a database isn't strictly necessary. If you load the site without one, it will take you through the installation process. Choose the installation profile of your choice. I recommend Minimal for this use case. Once the installation is complete, enable the memory_game module using ```lando drush en memory_game -y```

### Drupal

This application was developed in Drupal. However since it was developed in November of 2023 when Drupal 9 has reached End of Life, I have chosen Drupal 10 instead of 8 or 9.

### The API Endpoint

The endpoint is provided by a custom module called Memory Game (machine name: memory_game). The module contains:

- A [controller](#the-controller) that serves the /code-challenge/card-grid route.
- A [validator service class](#request-validator)
- A [Grid Generator service class](#grid-generator)
- A [Card Grid Class](#card-grid) to represent card grid objects.

#### The Controller

The controller is loaded by Drupal when the path /code-challenge/card-grid is requested from the router. Drupal calls the create method and provides the service container. The Controller pulls the GridGenerator service from the service container and holds onto it for use. Then Drupal calls the build() method to get the response object to serve to the user. Drupal provides the HTTP request to build() as an argument by default. The build method passes the request to the grid generator through the generateGridFromRequest method. Per the GridGeneratorInterface, the controller knows the response will be structured for the API, so the controller checks the metadata success message to determine whether to use HTTP response code 200 or 400 and then returns the data with the appropriate response code as a JsonResponse.

See [the code](web/modules/custom/memory_game/src/Controller/MemoryGameController.php)

#### Request Validator

The request validator service checks the request inputs to make sure they match the requirements and provides a message for why the request was denied if invalid. This service is not run as an access check by Drupal's routing system. I could have done that, but I chose the route I did because I wanted to have more control over how the response was provided to the user. Rather than letting Drupal control the handling of 403 errors, I wanted to return a 400 with the body structured according to the specifications.

Request validator provides the following public methods:

| Function               | Description                                                              | Dependencies                                                                                                                                                                                                       | Return Value                                                                                                                                                                                                                                                                                          |
| ---------------------- | ------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| validateRequest        | Validates a request to make sure it meets requirements                   | A Request object. The request can come from the request stack, which Drupal provides to the service class's constructor when returning it from the service container, or it can be passed as an optional argument. | void                                                                                                                                                                                                                                                                                                  |
| isValid                | Returns whether the most recent validation attempt was successful or not | None                                                                                                                                                                                                               | boolean. Successful validations return true. All other cases return false.                                                                                                                                                                                                                            |
| getValidationResult    | Returns the most recent validation result.                               | None                                                                                                                                                                                                               | AccessResultInterface. Will return the result of the most recent validation request. The validation request is initialized to AccessResultForbidden in the constructor so the default case is failure. A valid request must be provided to validateRequest for an AccessResultNeutral to be returned. |
| getAccessResultMessage | Returns the message associated with the most recent validation attempt.  | None                                                                                                                                                                                                               | If the access result was denied, this will return the string describing the reason. If it was successful, this will be an empty string.                                                                                                                                                                |

See [the code](web/modules/custom/memory_game/src/RequestValidator.php) and [the interface](web/modules/custom/memory_game/src/RequestValidatorInterface.php)

#### Grid Generator

Provides logic to generate the grid from a request. This takes a Request Validator as a dependency that Drupal injects when serving it from the service container. GridGenerator exposes a single public method, generateGridFromRequest that takes a Request object as a dependency. It will validate this request using the request validator and then return the appropriate grid output depending on the validation request and the parameters in the request input.

See [the code](web/modules/custom/memory_game/src/GridGenerator.php) and [the interface](web/modules/custom/memory_game/src/GridGeneratorInterface.php)

#### Card Grid

The Card Grid class is used by the Grid Generator class to generate the actual grid. It takes two dependencies, the number of rows and columns as integers. It performs the logic to randomly select the correct number of unique cards, shuffle a deck with 2 copies of each unique card and array them in a grid with the specified number of rows and columns.

See [the code](web/modules/custom/memory_game/src/CardGrid.php) and [the interface](web/modules/custom/memory_game/src/CardGrid.php)

See the swagger file in [json](docs/openapi.json) or [yaml](docs/openapi.json) for more detail on the API.

### Unit Testing

The validation service class may be tested using the [RequestValidatorTest](web/modules/custom/memory_game/tests/src/Unit/RequestValidatorTest.php) class.

To execute only this test, run

```
php vendor/bin/php web/modules/custom/memory_game/tests/src/Unit/RequestValidatorTest.php
```

The grid generator service may be tested using the [GridGeneratorTest](web/modules/custom/memory_game/tests/src/Unit/GridGeneratorTest.php)

To execute only this test, run

```
php vendor/bin/php web/modules/custom/memory_game/tests/src/Unit/GridGeneratorTest.php
```

The card grid class may be tested using the [CardGridTest](web/modules/custom/memory_game/tests/src/Unit/CardGridTest.php)

To execute only this test, run

```
php vendor/bin/php web/modules/custom/memory_game/tests/src/Unit/CardGridTest.php
```

### Code Quality

I have enforced code quality using [grumphp](https://github.com/phpro/grumphp) to scan the project with PHPCS and PHPStan. PHPCS uses both the Drupal and DrupalPractice coding standards. Grumphp registers its checks as a git pre-commit hook to prevent committing bad code. I've also provided tooling to the lando container to enable easily running its checks.

To run grumphp directly, run:

```
lando grumphp
```

Grumphp can easily be set up in github using github actions to lint the code prior to merging pull requests if it is so desired.
