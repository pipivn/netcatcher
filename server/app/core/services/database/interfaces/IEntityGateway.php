<?php
interface IEntityGateway
{
    public function save($entity_type, $entity);
    public function load($entity_type, $id);
    public function delete($entity_type, $id);
    public function find($entity_type, $spec, $take, $skip, $order);
}