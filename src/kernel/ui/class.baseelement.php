<?php
/**
 * \file
 * This file defines the top-level BaseElement class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.baseelement.php,v 1.13 2011-10-16 11:11:45 oscar Exp $
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup OWL_UI_LAYER
 * Abstract base class for all DOM elements
 * \brief DOM Element base class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 29, 2008 -- O van Eijk -- initial version
 */
abstract class BaseElement extends _OWL
{
	/**
	 * Class specification
	 */
	protected $class = '';

	/**
	 * Element style
	 */
	protected $style = '';

	/**
	 * Array with javascript events
	 */
	protected $events = array();

	/**
	 * Name of the element
	 */
	protected $name = '';

	/**
	 * Content for a container
	 */
	private $content;

	/**
	 * Element ID
	 */
	protected $id = '';

	/**
	 * Boolean for loop detection
	 */
	private $shown = false;

	/**
	 * Set the element ID
	 * \param[in] $_value Identification
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setId($_value)
	{
		$this->id = $_value;
	}

	/**
	 * Get the element's HTML ID
	 * \return The ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set the element name
	 * \param[in] $_value Element name
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setName($_value)
	{
		$this->name = $_value;

		// Default the id to the name
		if (empty($this->id)) {
			$this->setId($_value);
		}

	}

	/**
	 * Set the element style
	 * \param[in] $_value Element style
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setStyle($_value)
	{
		$this->style = $_value;
	}

	/**
	 * Set the element Class
	 * \param[in] $_value Class name
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setClass($_value)
	{
		$this->class = $_value;
	}

	/**
	 * Add a new value to the class element. This can either be used for chained CSS classes,
	 * of as a selector by the OWL-JS
	 * <a href="../../owl-js/html/lib/owl_js.html#library2fgetElementsByClass">getElementByClass()</a> function.
	 * \param[in] $_value Class name
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addClass($_value)
	{
		if ($this->class === '') {
			$this->class = $_value;
		} else {
			$this->class .= " $_value";
		}
	}

	/**
	 * Fill the content for a container. Existing content (e.g. set during instantiation)
	 * will be overwritten. If that is not desired, the addToContent method should be used instead.
	 * \param[in] $_content Reference to the content, which can be HTML code or an object,
	 * of which the showElement() method will be called to retrieve the HTML.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setContent(&$_content)
	{
		if (is_object($_content) && ($_content === $this)) {
			$this->setStatus (DOM_SELFREF, $this->name);
			return '&nbsp;'; // Probably fatal, but for completeness...
		}
		$this->content = $_content;
	}

	/**
	 * Add a content to the container. If the container is not an array yet, it will be
	 * converted to one.
	 * \param[in] $_content Reference to the content, which can be HTML code or an object,
	 * of which the showElement() method will be called to retrieve the HTML.
	 * \param[in] $_front When true, the content item will be the first item in the list with
	 * content items. By default, it is added to the end of the list.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addToContent(&$_content, $_front = false)
	{
		if (is_object($_content) && ($_content === $this)) {
			$this->setStatus (DOM_SELFREF, get_class($this));
			return '&nbsp;'; // Probably fatal, but for completeness...
		}
		if (!is_array($this->content)) {
			$_existingContent = $this->content;
			$this->content = array($_existingContent);
		}
		if ($_front === true) {
			array_unshift($this->content, $_content);
		} else {
			$this->content[] = $_content;
		}
	}

	/**
	 * Get the content of the current container, which can be plain HTML, an object,
	 * or an array which can mix both types.
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getContent()
	{
		$_htmlCode = '';
		if (is_array($this->content)) {
			foreach ($this->content as $_item) {
				$_htmlCode .= $this->_getContent($_item);
			}
		} else {
			$_htmlCode .= $this->_getContent($this->content);
		}
		$this->shown = true;
		return $_htmlCode;
	}

	/**
	 * Get the HTML for a content item, which can be plain HTML or an object,
	 * in which case the HTML will be retrieved from the object here.
	 * \param[in] $_contentItem The (next) contentitem
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function _getContent($_contentItem)
	{
		if (is_object($_contentItem)) {
			if ($this->shown === true) {
				OWLdbg_add(OWLDEBUG_OWL_OBJ, $this, 'ContentElement');
				OWLdbg_add(OWLDEBUG_OWL_VAR, $_contentItem, 'ContentItem');
				$this->setStatus (DOM_LOOPDETECT, array(
					  get_class($_contentItem) . ' (' . $_contentItem->getId() . ')'
					, get_class($this) . ' (' . $this->getId() . ')'
					)
				);
				return '&nbsp;'; // Probably fatal, but for completeness...
			}
			return $_contentItem->showElement();
		} else {
			return $_contentItem;
		}
	}

	/**
	 * Add an event to the events array
	 * \param[in] $_event Javascript event name (onXxx)
	 * \param[in] $_action Javascript function or code
	 * \param[in] $_add Boolean; when true, the action will be added if the event name already exists.
	 * Default is false; overwrite the action for this event.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setEvent($_event, $_action, $_add = false)
	{
		// TODO Validate the event and action
		if (!preg_match('/;$/', $_action)) {
			$_action .= ';';
		}

		if (array_key_exists($_event, $this->events) && $_add === true) {
			$this->events[$_event] .= $_action;
		} else {
			$this->events[$_event] = $_action;
		}
	}

	/**
	 * Return the HTML attribute list for events that where set for this element.
	 * \return string, HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getEvents()
	{
		$_htmlCode = '';
		foreach ($this->events as $_e => $_a) {
			$_htmlCode .= " $_e='$_a'";
		}
		return $_htmlCode;
	}

	/**
	 * Set the attributes of the DOM element by calling the 'set&lt;Attrib&gt;' method for this class.
	 * \param[in] $_attribs Array with attributes in the format attrib=>value
	 * \return Severity level. The status increased when a set&lt;Attrib&gt; method does not exist.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setAttributes(array $_attribs)
	{
		foreach ($_attribs as $_k => $_v) {
			$_method = 'set' . ucfirst($_k);
			if (method_exists($this, $_method)) {
				$this->$_method($_v);
			} else {
				$this->setStatus (DOM_IVATTRIB, array($_k));
				return $this->severity;
			}
		}
		return $this->severity;
	}

	/**
	 * Return the general element attributes that are set
	 * \param[in] $_ignore Array with attributes names that should be ignored
	 * \return string attributes in HTML format (' fld="value"...)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function getAttributes(array $_ignore = array())
	{
		$_htmlCode = '';
		if (!in_array('id', $_ignore) && !empty($this->id)) {
			$_htmlCode .= " id='$this->id'";
		}
		if (!in_array('class', $_ignore) && !empty($this->class)) {
			$_htmlCode .= " class='$this->class'";
		}
		if (!in_array('style', $_ignore) && !empty($this->style)) {
			$_htmlCode .= " style='$this->style'";
		}
		if (!in_array('name', $_ignore) && !empty($this->name)) {
			$_htmlCode .= " name='$this->name'";
		}
		if (!in_array('events', $_ignore)) {
			$_htmlCode .= self::getEvents();
		}
		return $_htmlCode;
	}

	/**
	 * This function must be implemented by all elements.
	 * \return The implementation must return a textstring with the complete
	 * HTML code to display the element
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	abstract public function showElement();
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('DOMElement');

//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
Register::setSeverity (OWL_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

Register::setSeverity (OWL_WARNING);
Register::registerCode('DOM_IVATTRIB');

//Register::setSeverity (OWL_BUG);

Register::setSeverity (OWL_ERROR);
Register::registerCode('DOM_SELFREF');
Register::registerCode('DOM_LOOPDETECT');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
