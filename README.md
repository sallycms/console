SallyCMS Console
================

This package provides a command line application to manage a SallyCMS project. It's capable of performing tasks like clearing the cache and perform commands provided by addOns, which makes it a powerful extension to SallyCMS. Technically it's based on the Symfony Console Component and therefore requires *PHP 5.3+* to run, but this does not change the fact that Sally itself needs PHP 5.2.

Requirements
------------

* SallyCMS 0.8+
* Composer

Installation
------------

The console is not yet part of the standard distribution for SallyCMS projects, so you have to require it manually and perform the installation with Composer. Add the following lines to your `composer.json`:

    :::json
    {
        "require": {
            "sallycms/console": "0.8.*@dev"
        }
    }

After installing (via `composer install`), you can access the console on your shell via

    :::text
    $ sally/vendor/bin/console ...

Usage
-----

The console is divided into *commands*, which can themselves have arguments and options. Technically, the first argument for the console determines the command. Command names are divided into namespaces with `:`; it's recommended that all commands are prefixed with a sensible namespace, in most cases this is `sly:` for Sally core commands and the addOn name for an addOn's command, like `imageresize:`.

Calling the console with no further arguments lists all available commands and global options (options you can give to any command, even though the command may not take it into account):

    :::text
    $ sally/vendor/bin/console
    Sally Console version 0.8.0

    Usage:
      [options] command [arguments]

    Options:
      --help           -h Display this help message.
      --quiet          -q Do not output any message.
      --verbose        -v Increase verbosity of messages.
      --version        -V Display this application version.
      --ansi              Force ANSI output.
      --no-ansi           Disable ANSI output.
      --no-interaction -n Do not ask any interactive question.

    Available commands:
      help          Displays help for a command
      list          Lists commands
    sly
      sly:install   Perform initial system installation

To see how a command should be used, use either the `help` command or call the command itself with the `--help` option:

    :::text
    $ sally/vendor/bin/console sly:install --help
    Usage:
     sly:install [--timezone="..."] [--name="..."] [--db-host="..."] [--db-driver="..."] [--db-prefix="..."] [--no-db-init] [--create-db] [--no-user] db-name db-user db-pass [password] [username]

    Arguments:
     db-name               The name of the database to use
     db-user               The database username
     db-pass               The database password
     password              The password for the new admin account
     username              The username for the new admin account

    Options:
     --timezone            The project timezone (default: "UTC")
     --name                The project name (default: "SallyCMS-Projekt")
     --db-host             The database host (default: "localhost")
     --db-driver           The database driver to use (default: "mysql")
     --db-prefix           The database table prefix (default: "sly_")
     --no-db-init          To perform no changes to the database.
     --create-db           To create the database if it does not yet exist.
     --no-user             To not create/update the admin account.

Commands
--------

Commands are implemented in PHP classes that need to extend `Symfony\Component\Console\Command\Command`. In this regard, Sally commands are identical to Symfony commands, so any documentation on how to configure them also applies here, with the exception that the command is not deducted from the command class name, but rather from the Sally configuration (see below).

The major difference here is the command class's constructor, which does not only get the command name passed (as the first argument), but also the Sally Dependency Injection Container.

    :::php
    <?php
    
    class MyCommand extends Symfony\Component\Console\Command\Command {
       public function __construct($name, sly_Container $container) {
          parent::__construct($name);
          $this->container = $container;
       }
       
       protected function configure() {
          $this
             ->setName('prefix:command')
             ->setDescription('This is my first command.')
             ->setDefinition(array(
                new InputArgument('argx', InputArgument::REQUIRED, 'My only required argument.')
             ));
       }

       protected function execute(InputInterface $input, OutputInterface $output) {
          $config = $this->container->getConfig();
          // ...
       }
    }

Configuration
-------------

To make a new command available, it needs to be registered inside the Sally configuration. This can be done via an addOn or any configuration file in `develop/config/`. Commands must be added to `console/commands`, each one having a unique key (which is **not** the command name, but rather an identifier):

    :::yaml
    console:
       commands:
          my_command: 'Fully\Qualified\Class\Name'
          my_other_command: 'AddOn\Command\Other'
