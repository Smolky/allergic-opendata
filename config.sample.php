<?php

/**
 * Config file
 *
 * @package AllergyLESS
 */
 
/** $production boolean  Defines if the application is 
                         in production */
$production = false;


/** $base_url String Defines the base_path of the application */
$base_url = '/economical/';


/** $dsn String Database connection string */
$dsn = 'mysql:host=localhost;dbname=envopendataextractor;charset=utf8mb4';


/** $user String Database user */
$user='root';


/** $password String Database password */
$password='root';


/** $email_server String The email server */
$email_server = '';


/** $email_port String The port of the email server */
$email_port = '';


/** $email_protocol String The protocol of the mail server*/
$email_protocol = 'tls';


/** $email_username String The user of the email account*/
$email_username = '';


/** $email_password String The password of the email account*/
$email_password = '';


/** $open_weather_api_key String OpenWeahter API Key */
$open_weather_api_key = '';


/** $aemed_api Array Aemed API Key */
$aemed_api = [
    'base_url' => 'https://opendata.aemet.es/opendata/',
    'key' => ''
];


/** $apixu_api Array Aemed API Key */
$apixu_api = [
    'base_url' => 'http://api.apixu.com/v1/',
    'key' => ''
];