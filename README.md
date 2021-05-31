# weather_forecast
## WEATHER FORECAST SERVICE

Made with Php 7.2
Used composer for codesniffer and a custom ruleset.xml.


File Structure
 - /app
   - BaseService.php
   - ForecastService.php
   - NotificationService.php
   - ServiceWrapper.php
 - /public
   - forecast.php

 - /vendor - contains codesniffer packages


run `composer install` to add the codesniffer packages
or

`composer require squizlabs/php_codesniffer ~3.0`

`composer require slevomat/coding-standard ~4.0`

## Classes
 - App\BaseService is parent to the other two Service Component classes and contains common functionality.
 - App\ForecastService contains the logic for getting the results from Open Weather API
 - App\NotificationService contains the logic for making requests to Rootee API, (authorizationRequest and messageRequest)
 - App\ServiceWrapper is used to elegantly combine the two Service Components (ForecastService,NotificationService)


 - public/index.php handles the requests up to 10 times with header refresh using global $_GET from the browser and displays the response messages.
