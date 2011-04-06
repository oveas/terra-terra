<?php
/**
 * \file
 * This file defines the abstract ContentArea class
 * \version $Id: class.contentarea.php,v 1.3 2011-04-06 14:42:16 oscar Exp $
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
	 * \protected
	 */
	protected $contentObject;

	/**
	 * This function must be reimplemented by all derived classes.
	 * It creates an object of any container type(with as many nested objects as desired)
	 * which holds all content for this area.
	 * \return The container object
	 * \public
	 */
	abstract public function loadArea();
	
	/**
	 * Add the newly created container object to the given container document
	 * \param[in] $_contnr Reference to the container object, by default (when null) the content will
	 * be added to the main document.
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
	 * Translate a textstring using the labels array
	 * \param[in] $_string Text string to translate
	 * \return The translation, or the input if none was found.
	 */
	public function trn ($_string)
	{
		if (array_key_exists($_string, $GLOBALS['labels'])) {
			return $GLOBALS['labels'][$_string];
		} else {
			return ((ConfigHandler::get ('debug')?'(!)':'').$_string);
		}
	}

}

/*
 * Register this class and all status codes
 */
Register::register_class ('ContentArea');

//Register::set_severity (OWL_DEBUG);

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
//Register::register_code ('FORM_RETVALUE');
//Register::set_severity (OWL_WARNING);
//Register::set_severity (OWL_BUG);
//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
