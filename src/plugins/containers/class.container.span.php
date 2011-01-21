<?php
/**
 * \file
 * This file defines the span plugin for containers
 * \version $Id: class.container.span.php,v 1.2 2011-01-21 10:18:27 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Class defining Span container plugin
 * \brief SpanContainer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

class ContainerSpanPlugin extends ContainerPlugin
{

	/**
	 * Container constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'span';
	}

	/**
	 * The SPAN tag has no specific arguments, but this method is required by syntax
	 * \return Empty string
	 */
	public function showElement()
	{
		return '';
	}
	
}