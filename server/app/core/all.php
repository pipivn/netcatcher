<?php

define("CORE_DIR", dirname(__FILE__) . "/");

include CORE_DIR . "models/Exceptions.php";
include CORE_DIR . "models/Entities.php";

/* BOUNDARY */
include CORE_DIR . "models/Common.php";
include CORE_DIR . "boundary/DataFactory.php";

P('ioc')->map(array(
    '@IdGen' => 'id_gen/SimpleIdGen.php',
    
    '@Database' =>  'database/MysqlDatabase.php',
    
    '@Repo' =>  array(
        'source' => 'RepositoryService.php',
        'depend' => array (
            'database' => '@Database',
            'id_gen' => '@IdGen',
        )
    ),
    
    '@AgentService' => array(
        'source' =>  'AgentService.php',
        'depend' => array(
            'repo' => '@Repo'
        )
    )
));
