# Chaining Console Command functionality

The project purpose is to show example of how to implement chaining of the console commands in Symfony.

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up -d` to start the project
4. Run `docker exec -it chain_php sh` to enter container shell
5. Run `composer install` inside container to install dependencies

## Features

* Easy and simple usage - just
  apply `#[ChainChildren(parentCommand:'parent:command', sortIndex: 10)]
  ` attribute to the
  needed command

## Examples

For demonstration purposes `FooBundle` and `BarBundle` included, exposing commands `foo:hello` and `bar:hi` accordingly.

`bar:hi` command is configured to be in chain after the `foo:hello` command, that means:

* You can't run `bar:hi` command on its own
* This command will be executed automatically after the `foo:hello` command will finish execution

## Useful commands

* Inside the container shell execute `./bin/phpunit` to run tests
