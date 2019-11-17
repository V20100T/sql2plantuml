<?php

require __DIR__.'/vendor/autoload.php';
// require 'src/sql.structure.php';
require 'config.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Cocur\Slugify\Slugify;
use v20100t\PlantumlGraph\Tools;

//https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md
/*
    DEBUG (100): Detailed debug information.
    INFO (200): Interesting events. Examples: User logs in, SQL logs.
    NOTICE (250): Normal but significant events.
    WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
    ERROR (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
    CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.
    ALERT (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
    EMERGENCY (600): Emergency: system is unusable.
**/

//ok avec composer dump-autoload -o
// et fichier dans : /var/www/html/sql2plantuml/vendor/v20100t/plantuml-graph/src/index.php"
//
// $test = new v20100t\PlantumlGraph\Builder($config['graphHeader'], $config['graphSkinParam'], $config['graphFooter']);

//prod
$logLevel = Logger::ERROR;
//dev
$logLevel = Logger::DEBUG;
$logLevel = Logger::INFO;

//load config
$config = Config::$config;

$slugify = new Slugify();

// create a Logger
$log = new Logger('sqlModel');
$formatter = new LineFormatter(
    null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
    null, // Datetime format
    true, // allowInlineLineBreaks option, default false
    true  // discard empty Square brackets in the end, default false
);
$debugHandler = new StreamHandler($config['logFilePath'], $logLevel);
$debugHandler->setFormatter($formatter);
$log->pushHandler($debugHandler);

try {
    $sqlStrcuture = new App\sqlStructure($config);
    // $sql = $sqlStrcuture->getStrcuture();
    // $tables = $sqlStrcuture->getTables();
    // // $log->info('sql  => ', $sql);
    // // Load classes
    // $graph = new v20100t\PlantumlGraph\Builder($config['graphHeader'], $config['graphSkinParam'], $config['graphFooter']);

    // //Headers
    // $graph->setHeader();
    // $graph->addTitle($config['graphTitle']);

    // if (!$sql) {
    //     die('SQL NULL ');
    // }

    // $flows = [];
    // //build
    // foreach ($sql as $tableName => $col) {
    //     echo "<h1>$tableName</h1>";
    //     $txt = '';
    //     //@TODO build link with detecting of MUL and tableName in field

    //     foreach ($col as $colName => $c) {
    //         //TODO use https://useiconic.com/open/
    //         // detect primary key, foreign key and autocrement and canbeNull
    //         // <&key> <&calculator>
    //         $txt .= '<b>'.$colName.'</b> '.implode(', ', $c).' \n';
    //         $graph->addMacro('FA_DATABASE', $slugify->slugify($tableName, '_'), $tableName.' \n '.$txt);

    //         //build flow
    //         if ($c['primary'] == 'MUL') {
    //             $log->info('link ?');
    //             $slugFrom = '';
    //             foreach ($tables as $t) {
    //                 if (stristr($colName, $t) || stristr($t, $colName)) {
    //                     $log->info("link YESSS  $t => $colName ");
    //                     $slugFrom = $slugify->slugify($t, '_');
    //                     $flows[] = [
    //                         $slugify->slugify($tableName, '_'),
    //                          $slugify->slugify($t, '_'),
    //                          "$tableName.$colName::$t.id",
    //                         ];
    //                 }
    //             }
    //         }
    //     }
    // }

    // // add flows after all items
    // foreach ($flows as $f) {
    //     $graph->addFlow($f[0], $f[1], $f[2], '-->');
    // }

    // $graph->build();
    // $graph->encode();

    $graph = $sqlStrcuture->modelingGraph();
    $urls = Tools::getUrls($config['plantumlBaseUrl'], $graph->encoded); //build final
    $log->info('Url debug : '.($urls['debug']));
    echo '<a href="'.$urls['debug'].'">url debug </a>';
    echo '<hr>';
    $svg = Tools::getCurlDatas($urls['svg']);
    $graphName = date('Y.m.d..H.i') . '.plantumlGraph - ' . $config['sql']['MYSQL_DATABASE_NAME'];
    $graphPath = $config['graphsPath'].$graphName;
    Tools::saveInFile($graphPath, $svg, 'svg');
    Tools::saveInFile($graphPath, Tools::getCurlDatas($urls['png']), 'png');
    // Tools::saveInFile($graphPath, Tools::getCurlDatas($urls['txt']), 'txt');

    echo $svg;

    echo '<pre>'.$graph->graph.'</pre>';
    echo '<pre>'.$graph->encoded.'</pre>';
    echo '<pre>'.$urls['debug'].'</pre>';
    echo '<pre>'.$urls['svg'].'</pre>';

    echo '<hr>';

    // Search ICO by name in JS
    echo 'Search an ico : <a href="http://localhost/php-crud-api/api.php/records/plantuml-tools/?filter=name,cs,php">php crud api php search </a>';
    echo '<input type="text" id="icoSearch"/> <button onclick="window.location.href=(`http://localhost/php-crud-api/api.php/records/plantuml-tools/?filter=name,cs,`+document.getElementById(`icoSearch`).value);"> search </button>';
} catch (\Exception $e) {
    //throw $th;
    echo '<br>ERROR exeption message  INDEX : '.$e->getMessage();
    echo '<br><a href="'.$urls['debug'].'">url debug </a>';
    echo '<pre><code>'.$graph->graph.'</code></pre>';

    echo '<link href="./assets/line.number.html.pre.css" type="text/css" rel="stylesheet" media="all" />';
    echo '<script type="text/javascript" src="./assets/line.number.html.pre.js"></script>';
    die('ERROR exeption message  INDEX : '.$e->getMessage());
}
