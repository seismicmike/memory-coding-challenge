# Memory Game

This application was developed as a coding challenge.

* [Requirements](#requirements)
* [Solution](#solution)

## Requirements

The following were the requirements for this challenge:
* Use Drupal 8 or 9
* Set up a local environment for development and testing
* Submit code as either a git repository or a zip folder
* Generate PHPUnit tests
* Generate a swagger file
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
  meta: {
    success: true,
    cardCount: 4,
    uniqueCardCount: 2,
    uniqueCards: ["D", "G"]
  },
  data: {
    cards: [
      ["G", "D"],
      ["D", "G"]
    ]
  }
}
```

GET /code-challenge/card-grid?rows=4&columns=6
```json
{
  meta: {
    success: true,
    cardCount: 24,
    uniqueCardCount: 12,
    uniqueCards: ["S","B","R","A","N","W","Q","F","V","L","J","X"]
  },
  data: {
    cards: [
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
  meta: {
    success: false,
    message: "Either `rows` or `columns` needs to be an even number."
  },
  data: {}
}
```

GET /code-challenge/card-grid?rows=10&columns=6
```json
{
  meta: {
    success: false,
    message: "Row count is greater than 6"
  },
  data: {}
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

### The API Endpoint

The endpoint is provided by a custom module called Memory Game (machine name: memory_game). The module contains:
* A controller that provides the route /code-challenge/card-grid and returns the API response
* A service class that provides validation checking of the input
* A service class that handles the logic of generating the grid

API responses go through the following 