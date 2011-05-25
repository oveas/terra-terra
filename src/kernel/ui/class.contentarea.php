<?php
/**
 * \file
 * This file defines the abstract ContentArea class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.contentarea.php,v 1.9 2011-05-25 12:04:30 oscar Exp $
 */

/**
 * \ingroup OWL_UI_LAYER
 * Abstract base class for all content areas
 * \brief Content Area base class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 29, 2008 -- O van Eijk -- initial version
 */
abstract class ContentArea extends _OWL
{
	/**
	 * The contentobject that must be filled by the derived class
	 */
	protected $contentObject;

	/**
	 * This function must be reimplemented by all derived classes.
	 * It creates an object of any container type(with as many nested objects as desired)
	 * which holds all content for this area.
	 * To check of a user has access to the current content area, the following check
	 * should be used:
	 * \code
	 * 	if ($this->hasRight('(right)', OWL_ID) === false) {
	 * 		return false;
	 * 	}
	 * \endcode
	 * Where 'right' is the rightsbit to check (e.g. 'readregistered') and the second parameter
	 * identifies the application (either OWL_ID or APP_ID)
	 * \param[in] $arg An optional argument (see OWLloader::getArea())
	 * \return The container object, or false when the user has no access
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	abstract public function loadArea($arg = null);
	
	/**
	 * Add the newly created container object to the given container document
	 * \param[in] $_contnr Reference to the container object, by default (when null) the content will
	 * be added to the main document.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addToDocument(Container $_contnr = null)
	{
		if ($_contnr === null) {
			$_document = OWL::factory('Document', 'ui');
			$_document->addToContent($this->contentObject);
		} else {
			$_contnr->addToContent($this->contentObject);
		}
	}

	/**
	 * Retrieve the content of this area
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getArea ()
	{
		return $this->contentObject->showElement();
	}

	/**
	 * Translate a textstring using the labels array. This method just calls the static
	 * translate method.
	 * \param[in] $_string Text string to translate
	 * \param[in] $_params An optional parameter or array with paramets that will by substituted in
	 * the translated text.
	 * \return The translation, or the input if none was found.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function trn ($_string, $_params = array())
	{
		return (self::translate($_string, $_params));
	}

	/**
	 * Translate a textstring using the labels array
	 * \param[in] $_string Text string to translate
	 * \param[in] $_params An optional parameter or array with paramets that will by substituted in
	 * the translated text.
	 * \return The translation, or the input if none was found.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function translate ($_string, $_params = array())
	{
		if (array_key_exists($_string, $GLOBALS['labels'])) {
			$translation = $GLOBALS['labels'][$_string];
		} else {
			$translation = ((ConfigHandler::get ('debug') > 0?'(!)':'').$_string);
		}
		if ($_params === array()) {
			return ($translation);
		}
		if (is_string($_params)) {
			$_params = array($_params);
		}
		for ($_i = 0; $_i < count ($_params); $_i++) {
			$_search[] = '$p' . ($_i + 1) . '$';
		}
		if ($_i > 0) {
			$translation = str_replace ($_search, $_params, $translation);
		}
		return ($translation);
	}

	/**
	 * Check if the current user has the right to see this container
	 * \param[in] $bit Rightsbit to check
	 * \param[in] $appl ID of the application the bit belongs to
	 * \return Boolean; true when the user has the right
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function hasRight ($bit, $appl)
	{
		$_u = OWLCache::get(OWLCACHE_OBJECTS, 'user');
		return ($_u->hasRight($bit, $appl));
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('ContentArea');

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
