##TODO
  * Features
    * INSERT/UPDATE to multiple tables (so brand new item can be inserted with model at once)
      * Need to know autoincremented ID of last inserted row
  * DB
    * Reference integrity
    * Use INSTEAD OF triggers to maintain audit trails (currently not supported by MySQL)
  * UI
    * CSS
    * Templating system
    * Stateless barcode scanner interface (php-cli?)
  * Security
    * SQLi (some fixed, some not)
    * XSS (none fixed)
  * Code refactoring
    * Optimize magic quotes usage
    * Move classes to separate files
      * Make sure that every method is in the class that it belongs to
    * Move CSS and Javascript to separate files
    * Use GetText
      * English and Czech locales
  * Release engineering