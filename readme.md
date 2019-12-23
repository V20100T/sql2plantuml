# SQL InnoDB draw with Plantuml

> __Générer le MCD d'une BDD InnoDB.__  

> Fast modeling Structure of your DB in InnoDB.  

## @TODO

* Clean Code
* Add filters
* Add usage in command line.
* Handle Errors and Exeptions (proxy, folders existing with access rights)


## Requierements

* SQL database InnoDB
* php7+
* composer : 
    * [plantuml-graph](https://github.com/V20100T/plantuml-graph) branch : addHandleForLocalIcone

* OPTIONAL
  * [Plantuml server](https://hub.docker.com/r/plantuml/plantuml-server/)
  * icons : https://github.com/tupadr3/plantuml-icon-font-sprites

## Installation

### clone repo & install composer dependencies

`git clone https://github.com/V20100T/sql2plantuml.git`

`composer install `


### Your configs

Copy `config.php.dist` in `config.php`  
Set your configs in `config.php`  

### App configs

Create folder **log**, and log file with rights.

```bash
mkdir log
touch log/log.log
chmod 777 log/log.log
```

Create folder **graphs** with rights

```bash
mkdir graphs/
chmod 777 graphs/
```

## Usage

Go to the index url,  
the model of your database is modelized with **plantuml**.


## Debug

Plantuml errors are catched. See the link ``url_debug`` for details when exception is show.

## Aim of this project

Proof of Concept of my repo:  [plantuml-graph](https://github.com/V20100T/plantuml-graph)   
Fast modeling Structure of your DB in InnoDB.

__Générer le MCD d'une BDD InnoDB.__

