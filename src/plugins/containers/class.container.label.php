<?php
/**
 * \file
 * This file defines the label plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.label.php,v 1.3 2011-05-02 12:56:14 oscar Exp $
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
	 */
	private $for;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setFor($_for)
	{
		$this->for = $_for;
	}

	/**
	 * Show the LABEL specific arguments. 
	 * \return HTML code for use in the LABEL tag
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
