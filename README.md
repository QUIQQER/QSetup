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
