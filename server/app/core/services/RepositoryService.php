<?php

class RepositoryService
{  
    /**
     *
     * @var IEntityGateway 
     */
    public $database;
    
    /**
     *
     * @var IIdGen 
     */
    public $id_generator;
    
    public function gen_id()
    {
        return $this->id_generator->gen_id();
    }
    
    public function audit($entity)
    {
        if ($entity instanceof AuditedEntity) {
            if (!isset($entity->created_time)) {
                $entity->created_time = time();
            } else {
                $entity->updated_time = time();
            }
        }
    }
    
    public function save($entity_type, $entity)
    {
        if (empty($entity->id))
        {
            $entity->id = gen_id();
        }
        $this->audit($entity);
        $this->database->save($entity_type, $entity);
    }
    
    public function load($entity_type, $id)
    {
        
    }
    
    public function delete($entity_type, $id)
    {        
    }
    
    public function find($entity_type, $specs, $take, $skip, $orders)
    {
        return array();
    }
    
}