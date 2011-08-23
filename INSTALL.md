#SōkoMan Instalation Guide

* Install following software
  * DB (currently tested only on MySQL)
  * WebServer with PHP>=5 support
  * GNU Barcode
  * ImageMagick
* Download source (and unpack it) somewhere in webserver wwwroot
  * Make sure that PHP can write to images/ and barcodes/
* Create new DB
  * load install.sql dump into it
* Copy sklad.conf.php.example to sklad.conf.php
  * Set DB\_* constants to match your DB settings
  * Optionaly you can
     * Set LOCALE\_LANG (eg.: en, cs,...)
     * Set INSTANCE\_ID (this is how you call your instalation, hostname, organization, etc...)
     * Set BARCODE\_TYPE (that will be used for printing barcodes)
     * Set BARCODE\_PREFIX (this will be used for generating unique barcodes if you have more instalations)
     * Set $fake\_lms\_users (If you want to use login credentials different from your DB user/password)
