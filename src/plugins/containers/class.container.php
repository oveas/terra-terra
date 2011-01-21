<?php
/**
 * \file
 * This file defines the standard container plugin
 * \version $Id: class.container.php,v 1.1 2011-01-13 11:05:35 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Abstract class defining container plugins
 * \brief ContainerPlugins
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

abstract class ContainerPlugin extends BaseElement
{

	/**
	 * Type; the container type, which must match the HTML tag.
	 * \protected
	 */
	protected $type;

	/**
	 * For containerst that have subtags i.s.o. arguments  this array will hold
	 * all subtags as complete HTML code composed by the type specific plugin.
	 */
	private $subtags;

	/**
	 * Class constructor; 
	 * \public
	 */
	protected function __construct ()
	{
		_OWL::init();
		$this->subtags = array();
	}

	/**
	 * Add an complete tag to the subtags array
	 * \param[in] $_tag HTML code
	 */
	protected function addSubTag($_tag)
	{
		$this->subtags[] = $_tag;
	}

	/**
	 * Retrieve all subtags for this container type
	 * \return HTML code or an empty string if no subtags are set
	 */
	public function getSubTags()
	{
		if (count($this->subtags) == 0) {
			return '';
		} else {
			return implode("\n", $this->subtags);
		}
	}

	/**
	 * Return the container type, which is equal to the HTML tag name
	 * \return container type
	 */
	public function getType()
	{
		return $this->type;
	}
}