<?php
/**
 * \file
 * This file defines a container element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.php,v 1.5 2011-05-02 12:56:14 oscar Exp $
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
	 * \param[in] $_type The container type. Currently supported are:
	 * 	- div
	 * 	- label
	 * 	- frameset
	 * \param[in] $_content HTML that will be placed in the table cell
	 * \param[in] $_attribs Indexed array with the HTML attributes 
	 * \param[in] $_type_attribs Indexed array with the type specific attributes.
	 * Refer to the 'show&lt;Type&gt;Type()' method for details
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($_type, $_content = '&nbsp;', array $_attribs = array(), array $_type_attribs = array())
	{
		_OWL::init();
		$this->showMethod = 'show' . ucfirst($_type) . 'Type';

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
	 * Set container specific attributes
	 * \param[in] $_attribs Indexed array with the type specific attributes.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setContainer(array $_attribs = array())
	{
		$this->containerObject->setAttributes($_attribs);
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
		$_htmlCode .= $this->containerObject->getSubTags();
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

Register::setSeverity (OWL_ERROR);
Register::registerCode ('CONTAINER_IVTYPE');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
