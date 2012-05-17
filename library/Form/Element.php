<?php
class Form_Element {
	
	/**
	 * @var string
	 */
	private $_name;
	
	/**
	 * Name of element that is used in form errors array
	 * 
	 * @var string
	 */
	private $_error_name;
	
	/**
	 * @var string
	 */
	private $_label;
	
	/**
	 * @var bool
	 */
	private $_required;
	
	/**
	 * @var array
	 */
	private $_required_validators;
	
	/**
	 * @var array
	 */
	private $_validators;
	
	/**
	 * @var array
	 */
	private $_filters;

	/**
	 * @var bool
	 */
	private $_unique;
	
	/**
	 * @var mixed
	 */
	private $_value;
	
	/**
	 * @var bool
	 */
	private $_filtered;
	
	/**
	 * @var bool
	 */
	private $_has_errors = false;

	/**
	 * @var bool
	 */
	private $is_file = false;

	/**
	 * @static
	 * @param string $name
	 * @param array|null $properties
	 * @return Form_Element
	 */
	public static function factory($name, $properties = null){
		return new self($name, $properties);
	}

	/**
	 * @param string $name
	 * @param array|null $properties
	 */
	public function __construct($name, $properties = null){
		$this->setName($name);
		$this->setErrorName($name);
		$this->init($properties);
	}

	/**
	 * @param array|null $properties
	 * @return
	 */
	public function init($properties = array()) {
		if (!isset($properties) || !sizeof($properties)) {
			return;
		}
		if (isset($properties['required'])) {
			$this->_required = $properties['required'];
		}
		if (isset($properties['required_validators'])) {
			$this->setRequiredValidators($properties['required_validators']);
		}
		if (isset($properties['validators'])) {
			$this->setValidators($properties['validators']);
		}
		if (isset($properties['unique'])) {
			$this->_unique = $properties['unique'];
		}
		if (isset($properties['input_filters'])) {
			$this->setFilters($properties['input_filters'], 'input');
		}
		if (isset($properties['output_filters'])) {
			$this->setFilters($properties['output_filters'], 'output');
		}
		if (isset($properties['label'])) {
			$this->setLabel($properties['label']);
		}
		if (isset($properties['is_file'])) {
			$this->setIsFile($properties['label']);
		}
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->_name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * @param string $name
	 */
	public function setErrorName($name){
		$this->_error_name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getErrorName(){
		return $this->_error_name;
	}
	
	/**
	 * @return string
	 */
	public function getLabel(){
		return $this->_label;
	}
	
	/**
	 * @param string $label
	 * @return Form_Element
	 */
	public function setLabel($label){
		$this->_label = $label;
		return $this;
	}
	
	/**
	 * @param bool $required
	 * @return Form_Element
	 */
	public function setRequired($required){
		$this->_required = $required;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isRequired() {
		return $this->_required;
	}
	
	/**
	 * @return array
	 */
	public function getRequiredValidators(){
		return $this->_required_validators;
	}
	
	/**
	 * @return bool
	 */
	public function hasRequiredValidators(){
		return is_array($this->_required_validators) && sizeof($this->_required_validators) > 0;
	}
	
	/**
	 * @param string $validator_name
	 * @param mixed $validator_options
	 * @return Form_Element
	 */
	public function addRequiredValidator($validator_name, $validator_options = array()){
		$this->_required_validators[] = array_merge(array($validator_name), $validator_options);
		return $this;
	}
	
	/**
	 * @param array $required_validators
	 */
	public function setRequiredValidators(array $required_validators){
		$this->_required_validators = $required_validators;
	}
	
	/**
	 * @param mixed $value
	 */
	public function setValue($value){
		$this->_value = $value;
	}
	
	/**
	 * @return mixed
	 */
	public function getValue(){
		return $this->_value;
	}
	
	/**
	 * @return bool
	 */
	public function isUnique() {
		return $this->_unique;
	}
	
	/**
	 * @param bool $unique
	 * @return Form_Element
	 */
	public function setUnique($unique){
		$this->_unique = $unique;
		return $this;
	}
	
	/**
	 * @param array $validators
	 */
	public function setValidators(array $validators) {
		$this->_validators = $validators;
	}
	
	/**
	 * @return array
	 */
	public function getValidators(){
		return $this->_validators;
	}
	
	/**
	 * @return bool
	 */
	public function hasValidators(){
		return is_array($this->_validators) && sizeof($this->_validators);
	}
	
	/**
	 * @param string $validator_name
	 * @param array $validator_options
	 * @return Form_Element
	 */
	public function addValidator($validator_name, $validator_options = array()) {
		$this->_validators[] = array_merge(array($validator_name), $validator_options);
		return $this;
	}

	/**
	 * @param string $filter_name
	 * @param array $properties
	 * @return Form_Element
	 */
	public function addInputFilter($filter_name, $properties = array()){
		$this->setFilters(array_merge(array($filter_name), $properties), 'input');
		return $this;
	}

	/**
	 * @param string $filter_name
	 * @param array $properties
	 * @return Form_Element
	 */
	public function addOutputFilter($filter_name, $properties = array()){
		$this->setFilters(array_merge(array($filter_name), $properties), 'output');
		return $this;
	}

	/**
	 * @param string $filter_name
	 * @param array $properties
	 * @return Form_Element
	 */
	public function addValidationFilter($filter_name, $properties = array()){
		$this->setFilters(array_merge(array($filter_name), $properties), 'validation');
		return $this;
	}
	
    /**
     * @param array $filters
     * @param  $type
     * @return void
     */
	public function setFilters(array $filters, $type) {
		$this->_filters[$type] = $filters;
	}
	
    /**
     * @param string $type
     * @return array
     */
	public function getFilters($type = 'validation') {
		return $this->hasFilters($type) ? $this->_filters[$type] : array();
	}

	/**
	 * @param  $type
	 * @return bool
	 */
	public function hasFilters($type){
		return isset($this->_filters[$type]);
	}
	
	/**
	 * @param bool $filtered
	 */
	public function setFiltered($filtered) {
		$this->_filtered = $filtered;
	}
	
	/**
	 * @return bool
	 */
	public function isFiltered() {
		return $this->_filtered;
	}

	/**
	 * @param bool $is_file
	 * @return void
	 */
	public function setIsFile($is_file){
		$this->is_file = $is_file;
	}

	/**
	 * @return bool
	 */
	public function isFile(){
		return $this->is_file;
	}
	
	/**
	 * @param bool $has_errors
	 * @return bool
	 */
	public function hasErrors($has_errors = null){
		if ($has_errors !== null) {
			$this->_has_errors = (bool) $has_errors;
		} else {
			return $this->_has_errors;
		}
	}
	
	public function reset(){
		$this->setValue(null);
		$this->hasErrors(false);
	}
	
	/**
	 * @param mixed $validator_options
	 * @return array
	 */
	public function parseValidatorOptions($validator_options){
		$validatorName = null;
		$validatorMessage = null;
		$validatorArguments = null;
		if (is_array($validator_options)) {
			if (isset($validator_options[0])) {
				$validatorName = $validator_options[0];
			}
			if (isset($validator_options[1])) {
				$validatorMessage = $validator_options[1];
			}
			if (isset($validator_options[2])) {
				$validatorArguments = $validator_options[2];
			}
		} else {
			$validatorName =  $validator_options;
		}
		return array($validatorName, $validatorMessage, $validatorArguments);
	}
}