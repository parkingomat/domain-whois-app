<?php

use Monolog\Utils;
use Psr\Log\LogLevel;

require("../load_func.php");
require("../libs.php");

try {
    # Function Async
    //let_func("https://letjson.github.io/php/let_json.php", "let_json", "../../plesk.json", function ($objs) {
    load_func(['https://letjson.github.io/php/let_json.php', 'https://php.defjson.com/def_json.php'], function () {
        $objs = let_json("../../plesk.json");

        $domains_per_host = [];
        foreach ($objs as $obj) {
            $client = new \PleskX\Api\Client($obj->ip);
            $client->setCredentials($obj->login, $obj->password);
            //    $domains = getDomains($client, $host);
            $list = $client->webspace()->getAll();
            foreach ($list as $item) {
                $domains_per_host[$obj->host][] = $item->name;
            }
        }

        header('Content-Type: application/json');
        def_json('host-domains.json', $domains_per_host, function ($data) {
            echo $data;
            exit();
        });

    });

} catch (exception $e) {
// Set HTTP response status code to: 500 - Internal Server Error
    http_response_code(500);
}

$level = LogLevel::ERROR;
foreach ($this->uncaughtExceptionLevelMap as $class => $candidate) {
    if ($e instanceof $class) {
        $level = $candidate;
        break;
    }
}

$this->logger->log(
    $level,
    sprintf('Uncaught Exception %s: "%s" at %s line %s', Utils::getClass($e), $e->getMessage(), $e->getFile(), $e->getLine()),
    ['exception' => $e]
);

if ($this->previousExceptionHandler) {
    ($this->previousExceptionHandler)($e);
}

if (!headers_sent() && !ini_get('display_errors')) {
    http_response_code(500);
}

exit(255);


function getClass(object $object): string
{
    $class = \get_class($object);

    return 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class).'@anonymous' : $class;
}
