<?php
/**
 * \file
 * This file defines a container element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.php,v 1.7 2011-05-30 17:00:19 oscar Exp $
 */

OWLloader::getClass('container', OWL_PLUGINS . '/containers');

/**
 * \ingroup OWL_UI_LAYER
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
	 * Class constructor;
	 * \param[in] $_type The container type. Supported containertypes are located in plugins/containers
	 * \param[in] $_content HTML that will be placed in the container
	 * \param[in] $_attribs Indexed array with the HTML attributes
	 * \param[in] $_type_attribs Indexed array with the type specific attributes.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($_type, $_content = '', array $_attribs = array(), array $_type_attribs = array())
	{
		_OWL::init();

		if (!OWLloader::getClass('container.'.$_type, OWL_PLUGINS . '/containers')) {
			$this->setStatus(CONTAINER_IVTYPE, array($_type));
			return null;
		}
		$_className = 'Container' . ucfirst($_type) . 'Plugin';
		if (!($this->containerObject = new $_className)) {
			$this->setStatus (CONTAINER_IVCLASSNAME, array($_type, $_className));
			return ($this->severity);
		}
		if (count($_attribs) > 0) {
			parent::setAttributes($_attribs);
		}
		$this->containerObject->setAttributes($_type_attribs);
		$this->setContent($_content);
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
			$this->setStatus (CONTAINER_IVSUBCONTNR, array($_type, getclass($this->containerObject)));
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
		$_htmlCode .= $this->getAttributes();
		$_htmlCode .= $this->containerObject->showElement();
		$_htmlCode .= ">\n";
		if (method_exists($this->containerObject, 'getContent')) {
			$_htmlCode .= $this->containerObject->getContent();
		}
		$_htmlCode .= $this->getContent();
		$_htmlCode .= '</' . $this->containerObject->getType() . ">\n";
		return $_htmlCode;
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('Container');

//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::setSeverity (OWL_WARNING);
Register::setSeverity (OWL_BUG);
Register::registerCode ('CONTAINER_IVCLASSNAME');
Register::registerCode ('CONTAINER_IVSUBCONTNR');

Register::setSeverity (OWL_ERROR);
Register::registerCode ('CONTAINER_IVTYPE');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
