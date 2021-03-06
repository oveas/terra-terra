<?php
/**
 * \file
 * This file defines a container element
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

TTloader::getClass('container', TT_PLUGINS . '/containers');

/**
 * \ingroup TT_UI_LAYER
 * Class for standard containers. It supports several container type, for each of them the methods
 * 'show&lt;Type&gt;Type()' must exist.
 * \brief Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 11, 2011 -- O van Eijk -- initial version
 */
class Container extends BaseElement
{
	/**
	 * Type specific container object (plugin)
	 */
	private $containerObject;

	/**
	 * Container type
	 */
	private $containerType;

	/**
	 * Class constructor;
	 * \param[in] $_type The container type. Supported containertypes are located in plugins/containers
	 * \param[in] $_attribs Indexed array with the HTML attributes
	 * \param[in] $_type_attribs (deprecated) Indexed array with the type specific attributes.
	 * \note The use of a seperated array with $_type_attribs is now deprecated: all attributes can be
	 * given in one single array $_attribs
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($_type, array $_attribs = array(), array $_type_attribs = array())
	{
		_TT::init(__FILE__, __LINE__);

		if (!TTloader::getClass('container.'.$_type, TT_PLUGINS . '/containers')) {
			$this->setStatus(__FILE__, __LINE__, CONTAINER_IVTYPE, array($_type));
			return null;
		}
		$_className = 'Container' . ucfirst($_type) . 'Plugin';

		if (!($this->containerObject = new $_className)) {
			$this->setStatus (__FILE__, __LINE__, CONTAINER_IVCLASSNAME, array($_type, $_className));
			return ($this->severity);
		}

		if (!empty($_attribs)) {
			$this->containerObject->setAttributes($_attribs);
		}
		if (!empty($_type_attribs)) {
			// Support for the deprecated $_type_attribs argument
			$this->containerObject->setAttributes($_type_attribs);
		}
		$this->containerType = $_type;
	}

	/**
	 * Redirect the call to the containerObject setContent() method
	 * This one must be added explicetly here since addToContent() wants a reference which won't
	 * work with the magig __cal() method
	 * \param[in] $_content The content
	 */
	public function setContent(&$_content)
	{
		$this->containerObject->setContent($_content);
	}

	/**
	 * Redirect the call to the containerObject addToContent() method
	 * This one must be added explicetly here since addToContent() wants a reference which won't
	 * work with the magig __cal() method
	 * \param[in] $_content The content
	 * \param[in] $_front Position
	 */
	public function addToContent(&$_content, $_front = false)
	{
		$this->containerObject->addToContent($_content, $_front);
	}

	/**
	 * Magic method to call container specific methods
	 * \param[in] $method Method name that should be called
	 * \param[in] $arguments Arguments for the method
	 * \return Return value of the method called, or the severity level on an error
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __call ($method, $arguments = null)
	{
		if (!method_exists($this->containerObject, $method)) {
			$this->setStatus(__FILE__, __LINE__, CONTAINER_IVMETHOD, array($method, $this->containerType));
			return $this->severity;
		}
		return call_user_func_array(array($this->containerObject, $method), $arguments);
	}

	/**
	 * Call the containers setAttributes() method
	 * \param[in] $_attribs Array with HTML attributes
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setAttributes(array $_attribs)
	{
		$this->containerObject->setAttributes($_attribs);
	}

	/**
	 * Set container specific attributes
	 * \param[in] $_attribs Indexed array with the type specific attributes.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \deprecated Use setAttributes() instead
	 */
	public function setContainer(array $_attribs = array())
	{
		$this->containerObject->setAttributes($_attribs);
	}

	/**
	 * Add a new subcontainer to this container.
	 * \param[in] $type Supported container thpe. The method <em>add&lt;Type&gt;()</em> must exist in this container.
	 * \param[in] $_content Optional HTML content
	 * \param[in] $_attribs General HTML attributes
	 * \param[in] $_type_attribs Type specific attributes
	 * \return Severity level in case of errors or the return value of the containers <em>add&lt;Type&gt;()</em>
	 * method, which is usually a reference to the new container object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addContainer ($type, $_content = '', array $_attribs = array(), array $_type_attribs = array())
	{
		$addContainer = 'add' . ucfirst($type);
		if (!method_exists($this->containerObject, $addContainer)) {
			$this->setStatus (__FILE__, __LINE__, CONTAINER_IVSUBCONTNR, array($type, get_class($this->containerObject)));
			return ($this->severity);
		}
		return $this->containerObject->$addContainer ($_content, $_attribs, $_type_attribs);
	}

	/**
	 * Get the HTML code to display the container
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$_htmlCode = '<' . $this->containerObject->getType();
		$_htmlCode .= $this->containerObject->showElement(); // Must be called before getStyleElement()!
		$_ignoreAttribs = array();
		$_css = $this->containerObject->getStyleElement();
		if ($_css != '') {
			$_ignoreAttribs = array('style'); // Style as a direct attribute is now deprecated
		}
		$_htmlCode .= $this->containerObject->getAttributes($_ignoreAttribs) . $this->getAttributes($_ignoreAttribs);
		$_htmlCode .= $_css;
		if ($this->containerObject->isSelfClosing()) {
			$_htmlCode .= '/>';
		} else {
			$_htmlCode .= '>' . $this->containerObject->getNestedType() . "\n";
			if (method_exists($this->containerObject, 'getContent')) {
				$_htmlCode .= $this->containerObject->getContent();
			}
			$_htmlCode .= $this->containerObject->getNestedType(true);
			$_htmlCode .= '</' . $this->containerObject->getType() . ">\n";
		}
		return $_htmlCode;
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('Container', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);

//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);
//Register::setSeverity (TT_WARNING);
Register::setSeverity (TT_BUG);
Register::registerCode ('CONTAINER_IVCLASSNAME');
Register::registerCode ('CONTAINER_IVSUBCONTNR');
Register::registerCode ('CONTAINER_IVMETHOD');

Register::setSeverity (TT_ERROR);
Register::registerCode ('CONTAINER_IVTYPE');

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
