<?php

namespace App;

use PDO;
use v20100t\PlantumlGraph\Builder;
use v20100t\PlantumlGraph\Tools;
use Cocur\Slugify\Slugify;

class sqlStructure
{
    private $pdo;
    private $tables;
    public $config;

    public function __construct($config)
    {
        $this->setPDO( $config['sql']['MYSQL_SERVER'],
        $config['sql']['MYSQL_DATABASE_NAME'],
        $config['sql']['MYSQL_USERNAME'],
        $config['sql']['MYSQL_PASSWORD']);
        
        $this->config = $config;
    }

    public function setPDO($server, $bdd, $user, $pwd)
    {
   //Instantiate the PDO object and connect to MySQL.
   $this->pdo = new \PDO(
    'mysql:host='.$server.';dbname='.$bdd,
    $user,
    $pwd
);
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function getStrcuture()
    {
        $statement = $this->pdo->query('SHOW TABLES ');
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        // echo '<pre>'.print_r($result).'</pre>';
        $this->tables = [];
        $sql = [];

        print_r($result);

        foreach ($result as $tablesRez) {
            $table = reset($tablesRez);
            $this->tables[] = $table;
            // echo "\n Tables <b></b>>".reset($tablesRez)."<</b>  \n ";
            // die();
            // $statement3 = $this->pdo->query('DESCRIBE `'.$table.'`');

            $statement = $this->pdo->query('SHOW FULL COLUMNS FROM `'.$table.'`');

            $sql[$table] = [];
            //Fetch our result.
            if ($statement) {
                // $columns = $statement3->fetchAll(PDO::FETCH_ASSOC);
                $columns = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($columns as $col) {
                    $sql[$table][$col['Field']] = [
                'type' => $col['Type'],
                'primary' => $col['Key'],
                'autoincrement' => $col['Extra'],
                'canBeNull' => $col['Null'],
                'comment' => $col['Comment'],
                'encodage' => $col['Collation'],
            ];
                }

                // $sql[$table][] = $columns;
        // echo '<pre> columns '.print_r($columns).'</pre>';
        // echo '<pre>columns : '.print_r($columns).'</pre>';
    // echo '<pre>SQL building superm : '.print_r($sql).'</pre>';
            } else {
                echo '<hr><hr> ERROR <hr> <hr>';
                print_r($table);
                echo '<hr><hr> ERROR <hr> <hr>';
            }

            // die();
        }
        // echo '<pre>'.print_r($tables).'</pre>';
        // echo '<hr><hr><pre>SQL : '.print_r($sql).'</pre>';
        //sql ok on fait le graph
        return $sql;
    }

    public function modelingGraph()
    {
        $slugify = new Slugify();

        $config = $this->config;
        
        

        $sql = $this->getStrcuture();
    $tables = $this->getTables();
    // $log->info('sql  => ', $sql);
    // Load classes
    $graph = new \v20100t\PlantumlGraph\Builder($config['graphHeader'], $config['graphSkinParam'], $config['graphFooter'], $config['graphIconsBaseUrl']);

    //Headers
    $graph->setHeader();
    $graph->addTitle($config['graphTitle']);

    if (!$sql) {
        die('SQL NULL ');
    }

    $flows = [];
    //build
    foreach ($sql as $tableName => $col) {
        echo "<h1>$tableName</h1>";
        $txt = '';
        //@TODO build link with detecting of MUL and tableName in field

        foreach ($col as $colName => $c) {
            //TODO use https://useiconic.com/open/
            // detect primary key, foreign key and autocrement and canbeNull
            // <&key> <&calculator>
            $txt .= '<b>'.$colName.'</b> '.implode(', ', $c).' \n';
            $graph->addMacro('FA_DATABASE', $slugify->slugify($tableName, '_'), $tableName.' \n '.$txt);

            //build flow
            if ($c['primary'] == 'MUL') {
                // $log->info('link ?');
                $slugFrom = '';
                foreach ($tables as $t) {
                    if (stristr($colName, $t) || stristr($t, $colName)) {
                        // $log->info("link YESSS  $t => $colName ");
                        $slugFrom = $slugify->slugify($t, '_');
                        $flows[] = [
                            $slugify->slugify($tableName, '_'),
                             $slugify->slugify($t, '_'),
                             "$tableName.$colName::$t.id",
                            ];
                    }
                }
            }
        }
    }

    // add flows after all items
    foreach ($flows as $f) {
        $graph->addFlow($f[0], $f[1], $f[2], '-->');
    }

    $graph->build();
    $graph->encode();
    return $graph;
    }
}
