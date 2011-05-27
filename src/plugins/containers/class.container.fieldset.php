<?php
/**
 * \file
 * This file defines the label plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.fieldset.php,v 1.4 2011-05-27 12:42:20 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Class defining Fieldset container plugin
 * \brief FieldsetContainer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

class ContainerFieldsetPlugin extends ContainerPlugin
{

	/**
	 * Reference to the Legend container object
	 */
	private $legend;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		$this->type = 'fieldset';
		$this->legend = null;
		parent::__construct();
	}

	/**
	 * Add a legend container to the fieldset
	 * \param[in] $_content Texstring
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \param[in] $_type_attribs Array with container type specific arguments; not used here
	 * \return Reference to the legend object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addLegend($_content = '&nbsp;', array $_attribs = array(), array $_type_attribs = array())
	{
		$this->legend = new Container('legend', $_content, $_attribs, $_type_attribs);
		return $this->legend;
	}

	/**
	 * The FIELDSET tag has no specific arguments, but this method is required by syntax
	 * \return Empty string
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		return '';
	}

	/**
	 * If a legend is set, return the content
	 * \return HTML code or an empty string
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getContent()
	{
		if ($this->legend !== null) {
			return $this->legend->showElement();
		} else {
			return '';
		}
	}

}
