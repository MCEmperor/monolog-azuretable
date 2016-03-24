monolog-azuretable
=============

Azure Table Storage Handler for Monolog, which allows to store log messages in a Azure Table Storage.

# Installation
monolog-azuretable is available via composer. Just add the following line to your required section in composer.json and do a `php composer.phar update`.

```
"huhushow/monolog-azuretable": ">1.0.0"
```

# Usage
Just use it as any other Monolog Handler, push it to the stack of your Monolog Logger instance. The Handler however needs some parameters:

- **$client** TableRestProxy Instance of your Azure Storage account.
- **$table** The table name where the logs should be stored
- **$level** can be any of the standard Monolog logging levels. Use Monologs statically defined contexts. _Defaults to Logger::DEBUG_
- **$bubble** _Defaults to true_

# Examples
Given that $client is your TableRestProxy instance, you could use the class as follows:

```php
//Import class
use AzureTableStorageHandler\AzureTableStorageHandler;

//Create AzureTableStorageHandler
$AzureTableStorageHandler = new AzureTableStorageHandler($client, "log", \Monolog\Logger::DEBUG);

//Create logger
$logger = new \Monolog\Logger($context);
$logger->pushHandler($AzureTableStorageHandler);

//Now you can use the logger, and further attach additional information
```

# License
This tool is free software and is distributed under the MIT license. Please have a look at the LICENSE file for further information.