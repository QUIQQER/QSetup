# Quiqqer Setup

This package contains the Quiqqer setup routine.

## Components

This package contains three main components :

QUI\Setup\Setup              - Main controller for setupviews. Contains all setuplogic  
QUI\Setup\Utils\Validator    - Provides static functions to verify and validate inputs
QUI\ConsoleSetup\Installer   - ConsoleSetup


## Usage

### Method 1 (Console Setup)

Primarily aimed at __console applications__, where the setup object can be stored into memory
1) Instantiate a new Setupobject (QUI\Setup\Setup)
2) Call its setters to provide neccessary data for each step
3) Execute Setup::runSetup()



### Method 2 (Web Setup)

Primarily aimed at __websetups__ , where the setup has to be executed in one request  (no consistent setupobject)
1) Instantiate a new setup object
2) call the Setup::setData(array $data) function and provide a valid data array with all parameters
3) Execute Setup::runSetup()


## Folderstructure

- ajax - Provides ajax functions for the web setup
- bin - Contains web assets for the web setup 
- lib - Contains the composer.phar (needed for shared hosting, as they usually do not have shell_exec and curl)  
- src - Contains the setup packagaes  
   - src/Setup - Contains the main interface for the setup  
   - src/ConsoleSetup - Contains the console setup which uses the src/Setup  
   - src/WebSetup - Contains the WebSetup, which uses src/Setup  
- templates - Provides file templates. i.E. the composer.json tpl
- tests - Contains unit tests
- xml - Contains xml files needed for the setup

## Setup Guide
- [Installation](https://dev.quiqqer.com/quiqqer/quiqqer/wikis/setup/installation)