<?php

class Form_ElementsList {
	
	/**
	 * @var array
	 */
	protected $_elements = array();
	
	/**
	 * @var string
	 */
	protected $_elementsNamePrefix = '';
	
	/**
	 * @param Form_Element string $element
	 * @param array $properties
	 */
	public function addElement($element, $properties = null){
		if (!$this->isElement($element)) {
			$element = new Form_Element($this->prefixElementName($element), $properties);
		}
		/* @var $element Form_Element */
		$this->_elements[$element->getName()] = $element;
	}
	
	/**
	 * @return array
 	 */
	public function getElements(){
		return $this->_elements;
	}
	
	/**
	 * @param string $name
	 * @return Form_Element
	 */
	public function getElement($name){
		return isset($this->_elements[$name]) && $this->isElement($this->_elements[$name]) ? $this->_elements[$name] : null;
	}
	
	/**
	 * @param mixed $element
	 * @return bool
	 */
	public function isElement($element){
		return $element instanceof Form_Element;
	}
	
	/**
	 * @return bool
	 */
	public function hasElements(){
		return is_array($this->_elements) && sizeof($this->_elements);
	}
	
	/**
	 * @param Form_ComplexElement $celement
	 */
	public function addComplexElement(Form_ComplexElement $celement){
		$this->_elements[$celement->getName()] = $celement;
	}
	
	/**
	 * @param string $name
	 * @return Form_ComplexElement
	 */
	public function getComplexElement($name){
		return isset($this->_elements[$name]) && $this->isComplexElement($this->_elements[$name]) ? $this->_elements[$name] : null;
	}
	
	/**
	 * @param mixed $element
	 * @return bool
	 */
	public function isComplexElement($element){
		return $element instanceof Form_ComplexElement;
	}
	
	/**
	 * @param string $prefix
	 */
	public function setElementsNamePrefix($prefix){
		$this->_elementsNamePrefix = $prefix;
	}
	
	/**
	 * @return string
	 */
	public function getElementsNamePrefix(){
		return $this->_elementsNamePrefix;
	}
	
	/**
	 * @param string $name
	 * @return string
	 */
	public function prefixElementName($name){
		return $this->getElementsNamePrefix().$name;
	}
}