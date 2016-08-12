# Quiqqer Setup

This package contains the Quiqqer setup routine.

## Components

This package contains two main components :

QUI\Setup\Setup             - Main controller for setupviews. Contains all setuplogic  
QUI\Setup\Utils\Validator   - Provides static functions to verify and validate inputs


## Usage

### Method 1

Primarily aimed at console applications where the setup object can be stored into memory
1) Instantiate a new Setupobject
2) call its setters to provide neccery data for each step
3) Execute Setup::runSetup()

### Method 2

Primarily aimed at websetups, where the setup has to be executed in one request  (no consistent setupobject)
1) Instantiate a new setup object
2) call the Setup::setData(array $data) function and provide a valid data array with all parameters
3) Execute Setup::runSetup()

## Folderstructure

- lib - Contains the composer.phar (needed for shared hosting, as they usually do not have shell_exec and curl)  
- src - Contains the setup packagaes  
   - src/Setup - Contains the main interface for the setup  
   - src/ConsoleSetup - Contains the console setup which uses the src/Setup  
   - src/WebSetup - Contains the WebSetup, which uses src/Setup  
- tests - Contains unit tests
- xml - Contains xml files needed for the setup
