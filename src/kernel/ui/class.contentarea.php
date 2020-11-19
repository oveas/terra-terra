<?php
/**
 * \file
 * This file defines the abstract ContentArea class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup TT_UI_LAYER
 * Abstract base class for all content areas
 * \brief Content Area base class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 29, 2008 -- O van Eijk -- initial version
 */
abstract class ContentArea extends _TT
{
	/**
	 * The contentobject that must be filled by the derived class
	 */
	protected $contentObject = null;

	/**
	 * This function must be reimplemented by all derived classes.
	 * It creates an object of any container type(with as many nested objects as desired)
	 * which holds all content for this area.
	 * To check of a user has access to the current content area, the following check
	 * should be used:
	 * \code
	 * 	if ($this->hasRight('(right)', TT_ID) === false) {
	 * 		return false;
	 * 	}
	 * \endcode
	 * Where 'right' is the rightsbit to check (e.g. 'readregistered') and the second parameter
	 * identifies the application (either TT_ID or APP_ID)
	 * \param[in] $arg An optional argument (see TTloader::getArea())
	 * \return The container object, or false when the user has no access
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	abstract public function loadArea($arg = null);

	/**
	 * Add the newly created container object to the given container document
	 * \param[in] $_contnr Either the reference to the container object, or a conatiner name (see \ref LayoutContainers)
	 * that refers to a cached container added to the main document. By default, the content will
	 * be added to CONTAINER_CONTENT in the main document.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addToDocument($_contnr = CONTAINER_CONTENT)
	{
		if (is_object($_contnr)) {
			$_contnr->addToContent($this->contentObject);
		} else {
			if (($_c = TTCache::get(TTCACHE_OBJECTS, $_contnr)) === null) {
				// ERROR
			}
			$_c->addToContent($this->contentObject, $_contnr);
		}
	}

	/**
	 * Retrieve the content of this area
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getArea ()
	{
		if ($this->contentObject === null) {
			$this->setStatus(__FILE__, __LINE__, AREANOCONTENTOBJ, array(get_class($this)));
		} else {
			return $this->contentObject->showElement();
		}
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
		return (parent::translate($_string, $_params));
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
		$_u = TTCache::get(TTCACHE_OBJECTS, 'user');
		return ($_u->hasRight($bit, $appl));
	}

	/**
	 * Add output to the existing contentObject
	 * \param[in] $_html HTML code to add
	 */
	public function addContent($_html)
	{
		$this->contentObject->addToContent($_content);
	}

}

/*
 * Register this class and all status codes
 */
Register::registerClass ('ContentArea', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);

//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');
//Register::setSeverity (TT_WARNING);
//Register::setSeverity (TT_BUG);
Register::setSeverity (TT_ERROR);
Register::registerCode('AREA_NOCONTENTOBJ');

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
