<?php

class BaseService
{  
    
    
}

class HasRepoService
{
    /**
     * @var RepositoryService
     */
    public $repo;
    
    /**
     *
     * @var string 
     */
    public $entity_type;
    
    public function save($entity)
    {
        $this->repo->save($this->entity_type, $entity);
    }
    
    public function load($id)
    {
        return $this->repo->load($$this->entity_type, id);
    }
    
    public function find($specs, $take, $skip, $orders)
    {        
        return $this->repo->find($this->entity_type, $specs, $take, $skip, $orders);
    }
    
    public function can_delete($id)
    {
        return true;
    }
    
    public function delete($id)
    {
        //make sure it can delete
        if ($this->can_delete($id))
        {            
            $this->repo->delete($this->entity_type, $id);
        }
    }
}