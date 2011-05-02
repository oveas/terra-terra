<?php
/**
 * \file
 * This file defines a  selectlist formfield element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.formfield.select.php,v 1.3 2011-05-02 12:56:13 oscar Exp $
 */

/**
 * \ingroup OWL_PLUGINS
 * Formfield selectlist elements
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldSelectPlugin extends FormFieldPlugin
{

	/**
	 * Number of visible options
	 */
	private $size;

	/**
	 * Boolean indicating a multiple select
	 */
	private $multiple;

	/**
	 * Array with options for the select list
	 */
	private $options;

	const DefaultOptionGroup = '__OWL_OptGroup__';
	/**
	 * Class constructor; 
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->type = 'select';
		$this->options = array(self::DefaultOptionGroup => array());
	}

	/**
	 * Define the options
	 * \param[in] $_value 2-Dimensional array with option data. The first level contains indexed arrays
	 * with data for each option in the format:
	 * \code
	 * array (
	 *     'value'    => string  // Required, value that will be submitted
	 *    ,'text'     => string  // Optional, text that will be displayed, defaults to 'value'
	 *    ,'selected' => boolean // Optional, true when this option is selected, defauls to false
	 *    ,'class'    => string  // Optional, class name
	 *    ,'group'    => string  // Optional, label of the optgroup the option belongs to
	 * )
	 * \endcode
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setValue($_value)
	{
		if (!is_array($_value)) {
			$this->setStatus(FORMFIELD_IVVALFORMAT, array($this->name));
			return;
		}

		foreach ($_value as $_option) {
			if (!is_array($_option)) {
				$this->setStatus(FORMFIELD_IVVALFORMAT, array($this->name));
				return;
			}
			if (!array_key_exists('value', $_option)) {
				$this->setStatus(FORMFIELD_NOVAL, array($this->name));
				return;
			}

			if (array_key_exists('group', $_option)) {
				$_valueArray = $this->options[$_option['group']];
			} else {
				$_valueArray = $this->options[self::DefaultOptionGroup];
			}
			if (in_array($_option['value'], $_valueArray)) { // TODO This will only check the current optgroup
				$this->setStatus (FORMFIELD_VALEXISTS, $_option['value'], $this->name);
				return;
			}
			$_valueArray['value'] = $_option['value'];
			$_valueArray['text'] = (array_key_exists('text', $_option) ? $_option['text'] : $_option['value']);
			if (array_key_exists('selected', $_option)) {
				$_valueArray['selected'] = toStrictBoolean($_option['selected'],array('yes', 'y', 'true', '1', 'checked', 'selected'));
			} else {
				$_valueArray['selected'] = false;
			}
			$_valueArray['class'] = (array_key_exists('class', $_option) ? $_option['class'] : '');
		}
	}
	
	/**
	 * Set the Size attribute
	 * \param[in] $size integer
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setSize ($size)
	{
		if (is_int($size)) {
			$this->size = $size;
		} else {
			$this->setStatus(FORMFIELD_IVVAL, array($size, 'size'));
		}
	}

	/**
	 * Set the Multiple boolean
	 * \param[in] $_value Value indicating true (default) or false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setMultiple($_value = true)
	{
		$this->multiple = toStrictBoolean($_value);
	}

	/**
	 * Return the HTML code to display the form elements
	 * \return String with the element formcode
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement ()
	{
		$_htmlCode = '<select ' . $this->getGenericFieldAttributes(array('value'));
		if (!empty($this->size)) {
			$_htmlCode .= " size='$this->size'";
		}
		if ($this->multiple) {
			$_htmlCode .= " multiple='multiple'";
		}
		$_htmlCode .= '/>';

		foreach ($this->options as $_group => $_options) {
			if ($_group != self::DefaultOptionGroup) {
				$_htmlCode .= "<optgroup label='$_group'>";
			}
			foreach ($_options as $_opt) {
				$_htmlCode .= "<option value='" . $_opt['value'] . "'";
				if (!empty($_opt['class'])) {
					$_htmlCode .= " class='" . $_opt['class'] . "'";
				}
				if ($_opt['selected'] === true) {
					$_htmlCode .= " selected='selected'";
				}
				$_htmlCode .= ">" . $_opt['text'] . '</select>';
			}
			if ($_group != self::DefaultOptionGroup) {
				$_htmlCode .= "</optgroup>";
			}
		}
		$_htmlCode .= '</select>';
		return $_htmlCode;
	}
}


//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

//Register::setSeverity (OWL_WARNING);

//Register::setSeverity (OWL_BUG);

//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
