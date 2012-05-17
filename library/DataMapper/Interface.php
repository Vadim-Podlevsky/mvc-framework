<?php

interface DataMapper_Interface {
	
    public function findById($id);
    
    public function findAll();
    
    public function find($where);
    
    public function insert(Entity_Abstract $entity);
    
    public function update(Entity_Abstract $entity);
    
    public function delete($id);
        
}