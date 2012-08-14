<?php
class InMemoryStorage implements IEntityGateway
{
    protected $entities = array();
       
    public function __construct()
    {
    }
    
    public function save($entity_type, $entity)
    {
        if (!empty($entity->id)) {
            $this->entities[$entity->id] = json_encode($entity);
        }
    }
    
    public function load($entity_type, $id)
    {
        if (!isset($this->entities[$id])) {
            return null;
        }
        return json_decode($this->entities[$key]);
    }
    
    public function find($options, $take, $skip) 
    {
        
    }
    
    public function delete($key)
    {
        if (isset($this->entities[$key])) {
            unset($this->entities[$key]);
        }
    }
    
    public function dump()
    {
        echo "DATABASE DUMP";
        var_dump($this->entities);
        die();
    }
}