<?php

require CORE_DIR . "services/database/interfaces/IEntityGateway.php";

class MysqlDatabase implements IEntityGateway
{
    public function __construct()
    {
    }
    
    public function save($entity_type, $entity)
    {
        
    }
    
    public function load($entity_type, $id)
    {
    }
    
    public function find($entity_type, $spec, $take, $skip, $order) 
    {
    }
    
    public function delete($entity_type, $id)
    {
    }
}