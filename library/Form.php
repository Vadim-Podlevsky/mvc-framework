<?php
FileLoader::loadClass('Filter');
FileLoader::loadClass('Validator');
FileLoader::loadClass('Form_Element');
FileLoader::loadClass('Form_Exception');
FileLoader::loadClass('Form_ElementsList');
FileLoader::loadClass('Form_ComplexElement');

class Form {
	
	const DEFAULT_REQUIRED_VALIDATOR = 'Required';

	/**
	 * @var Form_ElementsList
	 */
	protected $_elements_list;
	
	/**
	 * @var array
	 */
	protected $_errors = array();
	
	
	public function __construct(){
		$this->setElementsList(new Form_ElementsList());
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
	 * @param Form_Element $element
	 * @param array $properties
	 * @return Form
	 */
	public function addElement($element, $properties = null){
		$this->getElementsList()->addElement($element, $properties);
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
	
	/**
	 * @param Form_ComplexElement $celement
	 * @return Form
	 */
	public function addComplexElement(Form_ComplexElement $celement){
		$this->getElementsList()->addComplexElement($celement);
		return $this;
	}
	
	/**
	 * @param array $form_data
	 * @return bool
	 */
	public function isValid($form_data){
		$elements = $this->getElements();
		foreach ($elements as $element) {
			if ($this->getElementsList()->isComplexElement($element)) {
				/* @var $element Form_ComplexElement */
				$complex_elements = $element->getElements();
				foreach ($complex_elements as $form_element) {
					/* @var $form_element Form_Element */
					$this->_validateElement($form_element, $form_data);
				}
				continue;
			}
			/* @var $element Form_Element */
			$this->_validateElement($element, $form_data);
		}
		$this->resetElements();
		return !$this->hasErrors();
	}

	/**
	 * @param array $form_data
	 * @return void
	 */
    public function applyInputFilters($form_data){
        $this->applyFilters($form_data, 'input');
    }

	/**
	 * @param array $form_data
	 * @return void
	 */
    public function applyOutputFilters($form_data){
        $this->applyFilters($form_data, 'output');
    }

    /**
     * @param  array $form_data
     * @param  string $type
     * @return void
     */
    public function applyFilters($form_data, $type){
        $elements = $this->getElements();
		foreach ($elements as $element) {
			if ($this->getElementsList()->isComplexElement($element)) {
				/* @var $element Form_ComplexElement */
				$complex_elements = $element->getElements();
				foreach ($complex_elements as $form_element) {
					/* @var $form_element Form_Element */
					$this->_filterElement($form_element, $form_data, $type);
				}
				continue;
			}
			/* @var $element Form_Element */
			$this->_filterElement($element, $form_data, $type);
		}
    }
	
	/**
	 * @param Form_Element $element
	 * @param array $form_data
	 * @param string $type
	 * @return void
	 */
	private function _filterElement(Form_Element $element, &$form_data, $type){
		if ($element->hasFilters($type)) {
			$filters = $element->getFilters($type);
			foreach ($filters as $options) {
				if (is_array($options)) {
					$filterName = array_shift($options);
					$element->setValue(Filter::filterFormData($filterName, $form_data, $element->getName(), $options));
				} else {
					$filterName = $options;
					$element->setValue(Filter::filterFormData($filterName, $form_data, $element->getName()));
				}
				$form_data[$element->getName()] = $element->getValue();
			}
		} elseif (isset($form_data[$element->getName()])) {
			$element->setValue($form_data[$element->getName()]);
		}
	}
	
	/**
	 * @param Form_Element $element
	 * @param array $form_data
	 */
	private function _validateElement(Form_Element $element, $form_data){
		/* If element is required add default required validator */
		if ($element->isRequired()) {
			$element->addRequiredValidator(self::DEFAULT_REQUIRED_VALIDATOR);
		}
		/* Check if parameter is required and if its value is not empty */
		if ($element->hasRequiredValidators()) {
			$required_validators = $element->getRequiredValidators();
			foreach ($required_validators as $options) {
				$this->_processValidator($element, $options, $form_data);
			}
		}
		if ($element->hasErrors()) {
			/* Required fields are empty */
			return;
		} else if(empty($form_data[$element->getName()])) {
			/* Empty and not required fields are not validated */
			return;
		}
		/* Element is required and not empty, check for validators */
		if ($element->hasValidators()) {
			$validators = $element->getValidators();
			foreach ($validators as $options) {
				$this->_processValidator($element, $options, $form_data);
				$form_data[$element->getName()] = $element->getValue();
			}
		}
	}
	
	/**
	 * @param Form_Element $element
	 * @param mixed $options
	 * @param array $form_data
	 */
	private function _processValidator(Form_Element $element, $options, $form_data){
		list($validatorName, $validatorMessage, $validatorArguments) = Form_Element::parseValidatorOptions($options);
		if (!empty($validatorMessage)) {
			Validator::setMessage($validatorName, $validatorMessage);
		}
		if ($element_label = $element->getLabel()){
			Validator::setLabel($validatorName, $element_label);
		}
		if (is_array($validatorArguments)) {
			$is_valid = Validator::isValidFormData($validatorName, $form_data, $element->getName(), $validatorArguments);
		} else {
			$is_valid = Validator::isValidFormData($validatorName, $form_data, $element->getName());
		}
		if (!$is_valid) {
			$element->hasErrors(true);
			$this->addError($element->getErrorName(), Validator::getError($validatorName), Validator::getMessage($validatorName));
		}
	}
	
	/**
	 * @return array
	 */
	public function getValues(){
		$return_array = array();
		$elements = $this->getElements();
		foreach ($elements as $element) {
			if ($this->getElementsList()->isComplexElement($element)) {
				/* @var $element Form_ComplexElement */
				$complex_elements = $element->getElements();
				foreach ($complex_elements as $name => $form_element) {
					/* @var $form_element Form_Element */
					$return_array[$form_element->getName()] = $form_element->getValue();
				}
				continue;
			}
			/* @var $element Form_Element */
			$return_array[$element->getName()] = $element->getValue();
		}
		return $return_array;
	}
	
	/**
	 * @param string $element_name
	 * @param string $error_code
	 * @param string $message
	 * @return Form
	 */
	public function addError($element_name, $error_code, $message){
		if (!isset($this->_errors[$element_name])) {
			$this->_errors[$element_name] = array();
		}
		$this->_errors[$element_name][$error_code] = $message;
		return $this;
	}
	
	/**
	 * @param array $errors
	 */
	public function setErrors($errors){
		$this->_errors = $errors;
	}
	
	/**
	 * @return array
	 */
	public function getErrors() {
		return $this->_errors;
	}
	
	/**
	 * @return bool
	 */
	public function hasErrors(){
		return is_array($this->_errors) && sizeof($this->_errors) > 0;
	}
	
	/**
	 * @return array
	 */
	public function getErrorElementNames(){
		return array_keys($this->_errors);
	}
	
	/**
	 * @return array
	 */
	public function getErrorMessages(){
		$messages = array();
		foreach ($this->_errors as $element_errors) {
			foreach ($element_errors as $message) {
				$messages[] = Translation::translate($message);
			}
		}
		return array_unique($messages);
	}

	/**
	 * Returns errors as pair element_name => error_message
	 * @return array
	 */
	public function getErrorMap(){
		$map = array();
		foreach ($this->_errors as $element_name => $element_errors) {
			foreach ($element_errors as $message) {
				$map[$element_name] = Translation::translate($message);
			}
		}
		return $map;
	}
	
	/**
	 * @return void
	 */
	public function reset(){
		$this->_errors = array();
		$this->resetElements();
	}

	/**
	 * @return void
	 */
	public function resetElements(){
		$elements = $this->getElements();
		foreach ($elements as $element) {
			/* @var $element Form_Element */
			$element->reset();
		}
	}
	
	public function isValidPartial(){
		/* TODO: to be implemented */
	}
	
	public function getDecoratedValues(){
		/* TODO: to be implemented */
	}
	
}