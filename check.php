#!/usr/bin/env php
<?php
define('USER_AGENT', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36');
define('API_PREFIX', 'https://api.github.com');
define('IP', join('.', array(220, mt_rand(50, 250), mt_rand(50, 250), mt_rand(50, 250))));

if (!is_cli())
{
    exit('Please run script in CLI mode.');
}

$args = getopt('u:');

if (!isset($args['u']))
{
    exit('Please type username.');
}
$username = $args['u'];

$my_following = get_following($username);

$result = array();

foreach ($my_following as $value)
{
    if (!get_check_status($value, $username))
    {
        $result[] = $value;
    }
}

print_r($result);

function get_following($username)
{
    $array  = array();
    $object = request(API_PREFIX . '/users/' . $username . '/following');
    foreach ($object as $value)
    {
        if (!isset($value->login))
        {
            exit('API rate limit exceeded.Please use another ip.');
        }
        $array[] = $value->login;
    }
    return $array;
}

function get_check_status($username, $target)
{
    return (204 == get_status_code(API_PREFIX . '/users/' . $username . '/following/' . $target));
}

function is_cli()
{
    return (PHP_SAPI === 'cli');
}

function request($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-FORWARDED-FOR:' . IP, 'CLIENT-IP:' . IP,
    ));
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
}

function get_status_code($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 200);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-FORWARDED-FOR:' . IP, 'CLIENT-IP:' . IP,
    ));
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return (int) $httpCode;
}
