# Parakeet

Parakeet provides an asynchronous server implementation for multiplayer games via a HTTP based API. Built on Laravel 8 it is easily extended and modified to suit your games specific needs.

Out of the box, Parakeet can support user authentication, creating and joining games as well as a basic matchmaking system.

## Features

* User authentication and authorization supported by Laravel's Sanctum
* Game creation
* Basic matchmaking - join an available game or create one if none are available
* Player seat numbering (for example, seating players around a poker table)
* Game data synchronisation between clients

## Installation

1. Download this repository
2. Use composer to install the dependencies
```composer install```
3. Create a copy of ```env.example``` and save as ```.env```
4. Modify your ```.env``` file to suit your project
5. From the command line, run ```php artisan key:generate``` to generate a new application key
6. Serve up your project!

## Contributing

Please feel free to contribute to this project by creating a pull request with details of your changes or additions to the project.
