<?php

FileLoader::loadClass('Form_ElementsList');

class Form_ComplexElement {
	
	/**
	 * @var string
	 */
	protected $_name;
	
	/**
	 * @var string
	 */
	private $_label;
	
	/**
	 * @var Form_ElementsList
	 */
	protected $_elements_list;
	
	/**
	 * @param string $name
	 */
	public function __construct($name){
		$this->setName($name);
		$this->setElementsList(new Form_ElementsList());
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name){
		$this->_name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getName(){
		return $this->_name;
	}
	
	/**
	 * @return string
	 */
	public function getLabel(){
		return $this->_label;
	}
	
	/**
	 * @param string $label
	 */
	public function setLabel($label){
		$this->_label = $label;
	}
	
	/**
	 * @param Form_ElementsList $elements_list
	 */
	public function setElementsList(Form_ElementsList $elements_list) {
		$this->_elements_list = $elements_list;
	}
	
	/**
	 * @return Form_ElementsList
	 */
	public function getElementsList(){
		return $this->_elements_list;
	}
	
	/**
	 * @param Form_Element string $element
	 * @param array $properties
	 * @return Form_ComplexElement
	 */
	public function addElement($element, $properties = null){
		$this->getElementsList()->addElement($element, $properties);
		if (!$this->getElementsList()->isElement($element)) {
			$element = $this->getElementsList()->getElement($element);
		}
		$element->setErrorName($this->getName());
		$element->setLabel($this->getLabel());
		return $this;
	}
	
	/**
	 * @return array
 	 */
	public function getElements(){
		return $this->getElementsList()->getElements();
	}
	
	/**
	 * @return bool
	 */
	public function hasElements(){
		return $this->getElementsList()->hasElements();
	}
	
}