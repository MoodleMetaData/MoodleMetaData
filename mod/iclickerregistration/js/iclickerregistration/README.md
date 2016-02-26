# mod-iclickerregistration Javascript Frontend

## Introduction
The purpose of this javascript module is to serve as the front end of the iclicker registration display.
It utilizes bootstrap, angular, and other modern javascript/css goodies.

## To compile
As you may have notice, the .gitignore contains the node_modules, this is because  we
compile all the needed stuff to a single file in ```public/js/main.js``` and it's
minified version ```public/js/main.min.js```.

To compile:

1. npm install
2. gulp

Congratulations, the module is now compiled.

_Note:_ ```gulp``` _command keeps executing and automatically compile when there are changes (changes and not file addition)._