<?php
/**
 * \file
 * This file defines a textarea formfield element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.formfield.textarea.php,v 1.3 2011-05-02 12:56:13 oscar Exp $
 */

/**
 * \ingroup OWL_PLUGINS
 * Formfield textarea elements
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldTextareaPlugin extends FormFieldPlugin
{
	/**
	 * Field type; this class is used for 'text' and 'password' types
	 */
	private $type;

	/**
	 * Number of columns in the textarea
	 */
	public $rows;

	/**
	 * Number of rows in the textarea
	 */
	public $cols;
	
	
	/**
	 * Class constructor; 
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->type = 'textarea';
	}

	/**
	 * Return the HTML code to display the form element
	 * \return Textstring with the complete code for the form element
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement ()
	{
		$_htmlCode = "<textarea";
		if (!empty($this->rows)) {
			$_htmlCode .= " rows='$this->rows'";
		}
		if (!empty($this->cols)) {
			$_htmlCode .= " cols='$this->cols'";
		}
		$_htmlCode .= $this->getGenericFieldAttributes() . '>' . $this->value . '</textarea>';
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
