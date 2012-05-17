<?php

require_once('DataMapper.php');
require_once('Entity/Collection.php');

class DataMapper_Factory {

	/**
	 * @var array
	 */
	private static $_mappers;

	/**
	 * @static
	 * @param string $mapper_name
	 * @param null $entity_name
	 * @param null $table_name
	 * @return DataMapper_Abstract
	 */
	public static function factory($mapper_name, $entity_name = null, $table_name = null){
		if (!isset($entity_name)) {
			$entity_name = $mapper_name;
		}
		if (!isset(self::$_mappers[$mapper_name])) {
			$collection_class_name = $entity_name.'_Collection';
			FileLoader::throwExceptions(false);
			if (!FileLoader::loadClass($collection_class_name)) {
				$collection_class_name = 'Entity_Collection';
				FileLoader::loadClass($collection_class_name);
			}
			$collection = new $collection_class_name;
			$mapper_class_name = $mapper_name.'_DataMapper';
			FileLoader::loadClass($mapper_class_name);
			self::$_mappers[$mapper_name] = new $mapper_class_name($collection, $entity_name, $table_name);
		} else {
			if (isset($entity_name)) {
				self::$_mappers[$mapper_name]->setEntityClass($entity_name);
			}
			if (isset($table_name)) {
				self::$_mappers[$mapper_name]->initTable($table_name);
			}
		}
		return self::$_mappers[$mapper_name];
	}
	
	/**
	 * @static
	 * @throws DataMapper_FactoryException
	 * @param string $entity_name
	 * @param string $default_class_name
	 * @param string $class_type
	 * @return string
	 */
/*	private static function getClassByEntity($entity_name, $default_class_name, $class_type) {
		$entity_path = str_replace('_', '/', $entity_name);
		$class = $entity_name.'_'.$class_type;
		$file = $entity_path.'/'.$class_type.'.php';

		if (!file_exists_include_path($file)) {
			$class = $default_class_name;
		} else {
			require_once($file);
			if (!class_exists($class)) {
				throw new DataMapper_FactoryException($class.' not found!');
			}
		}
		return $class;
	}*/
	
}

class DataMapper_FactoryException extends FrameworkException{}