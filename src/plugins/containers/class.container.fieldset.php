<?php
/**
 * \file
 * This file defines the label plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.fieldset.php,v 1.3 2011-05-02 12:56:14 oscar Exp $
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
	 * Label that will be displayed for this fieldset
	 */
	private $legend;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		$this->type = 'fieldset';
		$this->legend = '';
		parent::__construct();
	}

	/**
	 * Set the legend attribute, which will be a nested tag in the fieldset container
	 * \param[in] $_legend Textstring to use
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setLegend($_legend)
	{
		$this->legend = $_legend;
	}

	/**
	 * The fieldset specific legend will not be displayed as argument for the fieldset tag,
	 * but as a subtag.
	 * Hence, this method shows nothing, but adds the legend tag to the parent's array with
	 * subtags that will be retrieved later.
	 * \return empty string
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		if ($this->legend !== '') {
			parent::addSubTag('<legend>' . $this->legend . '</legend>');
		}
		return '';
	}
}
