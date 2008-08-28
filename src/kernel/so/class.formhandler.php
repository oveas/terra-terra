<?php
/**
 * \file
 * This file defines the Formhandler class
 * \version $Id: class.formhandler.php,v 1.1 2008-08-28 18:12:52 oscar Exp $
 */

/**
 * \ingroup OWL_SO_LAYER
 * Handler for all incoming formdata. It implements the datahandler class.
 * \brief Formhandler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 28, 2008 -- O van Eijk -- initial version
 */
class FormHandler extends DataHandler
{
	/**
	 * Create an array which will hold multi-select values; 
	 */
	private $owl_multivalues;

	/**
	 * Class constructor; Parse the incoming formdata.
	 * \public
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->set_tablename ('_FORMDATA_'); // Use a dummy tablename

		$this->owl_multivalues = array();
		$this->set_status (FORM_PARSE);
		$this->parse_formdata ($_GET);
		$this->parse_formdata ($_POST);
		$this->set_status (OWL_STATUS_OK);
	}

	/**
	 * Parse a given form ans store all data in the parent class, except values that
	 * come from a multiple select; they will be stored locally.
	 * \private
	 * \param[in] $data The formdata array. 
	 */
	private function parse_formdata ($data = null)
	{
		if ($data === null) {
			return;
		}
		foreach ($data as $_k => $_v) {
			if ($this->$_k === null) {
				// New form field
				$this->$_k = $_v;
			} else {
				// This field already exists (multi select); make sure it's
				// not overwritten, but written in an array
				if (!array_key_exists ($_k, $this->owl_multivalues)) {
					// This is the first one, copy the previously stored value to the
					// multivalue array (note it has the table#value format!) and
					// add the new value.
					$_p = explode ('#', $this->$_k, 2);
					$this->owl_multivalues[$_k] = array ($_p[1], $_v);
				} else {
					$this->owl_multivalues[$_k][] = $_v;
				}
			}
		}
		if (ConfigHandler::get ('debug') === true) {
			$this->set_status ('FORM_STORVALUE', array ($_k, $_v));
		}
	}

	/**
	 * Reimplement of the __get magic method; the parent's __get will only be called
	 * of the requested variable name is not in the 'local' array where multi-values
	 * are stored.
	 * \public
	 * \param[in] $variable The variable name who's value should be returned
	 * \return Value as taken form the form.
	 */
	public function __get ($variable)
	{
		if (array_key_exists ($variable, $this->owl_multivalues)) {
			$_val = $this->owl_multivalues[$variable];
		} else {
			$_val = parent::__get ($variable);
		}

		if (ConfigHandler::get ('debug') === true) {
			$this->set_status ('FORM_RETVALUE', array ($_val, $variable));
		}
		return ($_val);
	}

}

/*
 * Register this class and all status codes
 */
Register::register_class ('FormHandler');

Register::set_severity (OWL_DEBUG);
Register::register_code ('FORM_STORVALUE');
Register::register_code ('FORM_PARSE');

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
Register::set_severity (OWL_SUCCESS);
Register::register_code ('FORM_RETVALUE');

Register::set_severity (OWL_WARNING);
Register::register_code ('FORM_NOVALUE');

//Register::set_severity (OWL_BUG);

//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
