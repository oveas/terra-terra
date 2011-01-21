<?php
/**
 * \file
 * This file defines the label plugin for containers
 * \version $Id: class.container.label.php,v 1.2 2011-01-21 10:18:27 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Class defining Label container plugin
 * \brief LabelContainer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

class ContainerLabelPlugin extends ContainerPlugin
{

	/**
	 * Reference to the formfield object
	 * \private
	 */
	private $for;

	/**
	 * Container constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'label';
		$this->for = null;
	}

	/**
	 * Set the for attribute, which identifies the formfield for which this is a label
	 * \param[in] $_for Reference to the formfield object or a string with the ID
	 */
	public function setFor($_for)
	{
		$this->for = $_for;
	}

	/**
	 * Show the LABEL specific arguments. 
	 * \return HTML code for use in the LABEL tag
	 */
	public function showElement()
	{
		$_htmlCode = '';
		if ($this->for !== null) {
			$_htmlCode .= ' for="'
				. ((is_object($this->for)) ? $this->for->getId() : $this->for)
				. '"';
		}
		return $_htmlCode;
	}
	
}
