# DrellerPayPal
>This MD file doesn't contains all instructions for the Class, please, see the [Wiki](https://github.com/Dreller/dreller-php-paypal/wiki) for all documentation!!

This is a PHP Class to makes calls to PayPal API a little more easier, using cURL functions.
All you need to use it is:
 - Your Client ID,
 - Your Client Secret.

 Declare the Class and you are good to go!

 ```php
 $myClass = new DrellerPayPal($myClientID, $myClientSecret, true);
 ```
 Note: The optional last parameter is to tell `true` or `false` to connect to Sandbox API.  `false` by default.

 Feel free to submit issues to add new functions/API Calls, I have added those that are usefull for my current project.
