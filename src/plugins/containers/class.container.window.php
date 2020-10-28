<?php
/**
 * \file
 * This file defines the window plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2020} Oscar van Eijk, Oveas Functionality Provider
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

/**
 * \defgroup WindowVisibility Constants for window visibility
 * These constants are used by the ContainerWindowPlugin. They can be passed to the setVisibility() method
 * *after* the container is initialised with
 * \code{.php}
 * $_window = new Container('window', array(), array('visible' => WINDOW_VISIBILITY_SHADED));
 * \endcode
 * It should *not* used during instantation of the window: if it's the first, the constants have not been defined yet.
 * @{
 */
//! Window is hidden (Closed)
define ('WINDOW_VISIBILITY_HIDDEN', 0);
//! Window is shaded: only the top bar is visible
define ('WINDOW_VISIBILITY_SHADED', 1);
//! Window is visible
define ('WINDOW_VISIBILITY_VISIBLE', 2);
//! Window is maximized
define ('WINDOW_VISIBILITY_MAXIMIZED', 3);
//! @}

/**
 * \ingroup TT_UI_PLUGINS
 * Class defining Window container plugin. It is a reimplentation of the WorkArea from
 * the original Terra-Terra (2001 - 2006) and defines creates several div objects to
 * define a window with top and bottom borders
 * \brief AreaContainer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 12, 2020 -- O van Eijk -- initial version
 */
class ContainerWindowPlugin extends ContainerPlugin
{
	private $hposition		= 0;		//!< Position; distance from left or right windowborder (in pixes)
	private $vposition		= 0;		//!< Position; distance from the top or bottom
	private $halignment		= 'left';	//!< Measure 'hposition' from left or right
	private $valignment		= 'top';	//!< Measure 'vposition' from top or bottom
	private $width;						//!< Area width in pixels
	private $height;					//!< Area height in pixels
	private $border			= 0;		//!< Border width in pixels (left and right side of the contentarea only)
	private $visibility;				//!< Initial status: 0: hidden, 1: shaded, 2: visible, 6: maximized
	private $z_index		= 25;		//!< Display order (z-index)
	private $title;						//!< Title as displayed in the titlebalr
	private $display_bars	= true;		//!< Should the Title- and Bottom bars be displayed?
	private $shadable		= true;		//!< Can the user shade the workarea?
	private $maximizable	= true;		//!< Can the user maximize the workarea?
	private $closable		= true;		//!< Can the user close the workarea?
	private $movable		= true;		//!< Can the user move the workarea?
	private $reloadable		= true;		//!< Can the user reload the workarea?
	private $resizable		= true;		//!< Can the user resize the workarea?

	private $barTop;					//!< Top bar object
	private $contentArea;				//!< Main content object
	private $barBottom;					//!< Bottom bar object

	private $wid;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();

		$_stack =& TTCache::getRef(TTCACHE_REGISTER, 'stack');
		if (!array_key_exists ('window-id', $_stack)) {
			$_stack['window-id'] = 0;
		}
		$this->wid = $_stack['window-id']++;

		$this->type = 'div';

		$this->setClass('workarea');
		$this->setId('TT_wa' . $this->wid . '_top');
		$this->contentArea = new Container('div', array('class' => 'embedded', 'id' => 'TT_wa' . $this->wid . '_body'));

		$document = TT::factory('Document', 'ui');
		$document->addJSPlugin('window', 'terra-terra');
		$document->addJSPlugin('window', 'terra-terra_wa');
	}

	/**
	 * Overrule the BaseElement's setContent() to ensure the content of the correct container is set
	 * \param[in] $_content Reference to the content, which can be HTML code or an object,
	 * of which the showElement() method will be called to retrieve the HTML.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setContent(&$_content)
	{
		$this->contentArea->setContent($_content);
	}

	/**
	 * Overrule the BaseElement's addToContent() to ensure the content is added to the correct container
	 * \param[in] $_content Reference to the content, which can be HTML code or an object,
	 * of which the showElement() method will be called to retrieve the HTML.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addToContent(&$_content, $_front = false)
	{
		$this->contentArea->addToContent($_content, $_front);
	}

	/**
	 * Create the topbar of the window which is a table with a single row.
	 * It contains the window title and several icons:
	 *   * Mode (left side)
	 *   * Shade (right side, left)
	 *   * Maximize/Restore (right side, middle)
	 *   * Close (right side, right)
	 */
	private function createTopBar()
	{
		$_theme = TT::factory('Theme', 'ui');

		// Create the table
		$this->barTop = new Container('table');
		$this->barTop->addStyleAttributes(
			array(
				 'width'			=> '100%'
				,'border-spacing'	=> '0px'
				,'vertical-align'	=> 'middle'
				,'border-width'		=> '0px'
			)
		);

		// Create the row
		$_r = $this->barTop->addContainer('row');
		$_r->addStyleAttributes(array('height' => ConfigHandler::get('theme-backgrounds', 'top-bar-height') . 'px'));

		// Left part which holds the Move icon
		$_c = $_r->addContainer('cell', '', array('id' => 'MoveMe'));
		$_c->addStyleAttributes(
			array(
				 'width'				=> '29%'
				,'background-image'		=> 'url('. $_theme->getImage('bar.png', 'backgrounds') . ')'
				,'background-repeat'	=> 'repeat-x'
				,'margin'				=> 0
				,'text-align'			=> 'left'
				,'vertical-align'		=> 'top'
			)
		);

		// Left border
		$_i = new Container('img', array(), array('src' => $_theme->getImage('bar.left.png', 'backgrounds')));
		$_i->addStyleAttributes(
			array(
				 'width'			=> ConfigHandler::get('theme-backgrounds', 'top-bar-border-width') . 'px'
				,'height'			=> ConfigHandler::get('theme-backgrounds', 'top-bar-height') . 'px'
				,'border'			=> '0px'
				,'margin'			=> '0px'
				,'vertical-align'	=> 'top'
			)
		);
		$_c->addToContent($_i);

		// The Move icon
		$_i = new Container('img', array(), array('src' => $_theme->getImage('move.png', 'icons'), 'alt' => 'Move Workarea'));
		$_i->addStyleAttributes(
			array(
				 'width'			=> ConfigHandler::get('theme-icons', 'top-width') . 'px'
				,'height'			=> ConfigHandler::get('theme-icons', 'top-height') . 'px'
				,'border'			=> '0px'
				,'margin'			=> '0px 2px'
				,'vertical-align'	=> 'middle'
				,'cursor'			=> 'move'
			)
		);
		$_i->setEvent('mousedown', 'startAction (event, "' . $this->wid . '", "d")');
		$_c->addToContent($_i);

		//  Left filler
		$_c = $_r->addContainer('cell');
		$_c->addStyleAttributes(
			array(
				 'background-image'		=> 'url('. $_theme->getImage('bar.png', 'backgrounds') . ')'
				,'background-repeat'	=> 'repeat-x'
				,'margin'				=> 0
			)
		);

		// Left side of the Title
		$_c = $_r->addContainer('cell');
		$_c->addStyleAttributes(
			array(
				 'background-image'		=> 'url('. $_theme->getImage('bar.text.left.png', 'backgrounds') . ')'
				,'background-repeat'	=> 'no-repeat'
				,'margin'				=> 0
				,'text-align'			=> 'right'
				,'width'				=> ConfigHandler::get('theme-backgrounds', 'top-bar-border-width') . 'px'
			)
		);

		// Title
		$_c = $_r->addContainer('cell', $this->title, array('id' => 'TT_wa_ttfld_' . $this->wid, 'class' => 'wa_titlebar'));
		$_c->addStyleAttributes(
			array(
				 'background-image'		=> 'url('. $_theme->getImage('bar.text.png', 'backgrounds') . ')'
				,'background-repeat'	=> 'repeat-x'
				,'margin'				=> 0
				,'text-align'			=> 'center'
				,'width'				=> '40%'
				,'height'				=> ConfigHandler::get('theme-backgrounds', 'top-bar-height') . 'px'
				,'vertical-align'		=> 'middle'
			)
		);

		// Right side of the title
		$_c = $_r->addContainer('cell');
		$_c->addStyleAttributes(
			array(
				 'background-image'		=> 'url('. $_theme->getImage('bar.text.right.png', 'backgrounds') . ')'
				,'background-repeat'	=> 'no-repeat'
				,'margin'				=> 0
				,'text-align'			=> 'right'
				,'width'				=> ConfigHandler::get('theme-backgrounds', 'top-bar-border-width') . 'px'
			)
		);

		// Right filler
		$_c = $_r->addContainer('cell');
		$_c->addStyleAttributes(
			array(
				 'background-image'		=> 'url('. $_theme->getImage('bar.png', 'backgrounds') . ')'
				,'background-repeat'	=> 'repeat-x'
				,'margin'				=> 0
			)
		);

		//  Area for the right-side icons
		$_c = $_r->addContainer('cell');
		$_c->addStyleAttributes(
			array(
				 'background-image'		=> 'url('. $_theme->getImage('bar.png', 'backgrounds') . ')'
				,'background-repeat'	=> 'repeat-x'
				,'margin'				=> 0
				,'text-align'			=> 'right'
				,'width'				=> '29%'
			)
		);

		// Shade icon
		$_i = new Container('img', array('id' => 'ShadeIcon_' . $this->wid), array('src' => $_theme->getImage('shade.png', 'icons'), 'alt' => 'Shade Workarea'));
		$_i->addStyleAttributes(
			array(
				 'width'			=> ConfigHandler::get('theme-icons', 'top-width') . 'px'
				,'height'			=> ConfigHandler::get('theme-icons', 'top-height') . 'px'
				,'border'			=> '0px'
				,'margin'			=> '0px 2px'
				,'vertical-align'	=> 'middle'
				,'cursor'			=> 'default'
			)
		);
		$_l = new Container('link', array('class' => 'icons', 'id' => 'ShadeLink_' . $this->wid), array('href' => "javascript:WAVisibility('$this->wid', 's')"));
		$_l->setContent($_i);
		$_c->addToContent($_l);

		// Maximize/Restore icon
		$_i = new Container('img', array('id' => 'MaximizeIcon_' . $this->wid), array('src' => $_theme->getImage('maximize.png', 'icons'), 'alt' => 'Maximize Workarea'));
		$_i->addStyleAttributes(
			array(
				 'width'			=> ConfigHandler::get('theme-icons', 'top-width') . 'px'
				,'height'			=> ConfigHandler::get('theme-icons', 'top-height') . 'px'
				,'border'			=> '0px'
				,'margin'			=> '0px 2px'
				,'vertical-align'	=> 'middle'
				,'cursor'			=> 'default'
			)
		);
		$_l = new Container('link', array('class' => 'icons', 'id' => 'MiniMaxLink_' . $this->wid), array('href' => "javascript:WAVisibility('$this->wid', 'm')"));
		$_l->setContent($_i);
		$_c->addToContent($_l);

		// Close icon
		$_i = new Container('img', array('id' => 'Close_' . $this->wid), array('src' => $_theme->getImage('close.png', 'icons'), 'alt' => 'Close Workarea'));
		$_i->addStyleAttributes(
			array(
				 'width'			=> ConfigHandler::get('theme-icons', 'top-width') . 'px'
				,'height'			=> ConfigHandler::get('theme-icons', 'top-height') . 'px'
				,'border'			=> '0px'
				,'margin'			=> '0px 2px'
				,'vertical-align'	=> 'middle'
				,'cursor'			=> 'default'
			)
		);

		$_l = new Container('link', array('class' => 'icons', 'id' => 'CloseLink_' . $this->wid), array('href' => "javascript:WAVisibility('$this->wid', 'c')"));
		$_l->setContent($_i);
		$_c->addToContent($_l);

		// Right border
		$_i = new Container('img', array(), array('src' => $_theme->getImage('bar.right.png', 'backgrounds')));
		$_i->addStyleAttributes(
			array(
				 'width'			=> ConfigHandler::get('theme-backgrounds', 'top-bar-border-width') . 'px'
				,'height'			=> ConfigHandler::get('theme-backgrounds', 'top-bar-height') . 'px'
				,'border'			=> '0px'
				,'margin'			=> '0px'
				,'vertical-align'	=> 'top'
			)
		);
		$_c->addToContent($_i);

	}

	/**
	 * Create the actual container that will hold all content
	 */
	private function createContentArea()
	{
		$this->contentArea->addStyleAttributes(
			array(
				 'position'		=> 'absolute'
				,'overflow'		=> 'auto'
				,'left'			=> '0px'
				,'top'			=> ConfigHandler::get('theme-backgrounds', 'top-bar-height') . 'px'
				,'width'		=> ($this->width - (2*$this->border)) . 'px'
				,'height'		=> ($this->height
						- ConfigHandler::get('theme-backgrounds', 'top-bar-height')
						- ConfigHandler::get('theme-backgrounds', 'bottom-bar-height')) . 'px'
				,'border-style'	=> 'solid'
				,'border-width'	=> '0px ' . $this->border . 'px'
				,'z-index'		=> $this->z_index
			)
		);
		$this->setEvent('click', 'PlaceOnTop ("' . $this->wid . '")');
	}

	/**
	 * Create the bottombar oif the window, a single table in a div, with a single row/cell and a resize icon at the right
	 */
	private function createBottomBar()
	{
		$_theme = TT::factory('Theme', 'ui');

		// Create the table
		$_t = new Container('table');
		$_t->addStyleAttributes(
			array(
				' width'			=> '100%'
				,'height'			=> ConfigHandler::get('theme-backgrounds', 'bottom-bar-height') . 'px'
				,'border-spacing'	=> '0px'
				,'vertical-align'	=> 'top'
				,'border-width'		=> '0px'
			)
		);

		// Add the table row
		$_r = $_t->addContainer('row');

		// Add the table cell
		$_c = $_r->addContainer('cell', '', array('id' => 'ResizeMe'));
		$_c->addStyleAttributes(
			array(
				 'width'				=> '100%'
				,'background-image'		=> 'url('. $_theme->getImage('bar.bottom.png', 'backgrounds') . ')'
				,'background-repeat'	=> 'repeat-x'
				,'margin'				=> 0
				,'text-align'			=> 'right'
				,'vertical-align'		=> 'top'
			)
		);

		// Filler
		$_i = new Container('img', array(), array('src' => $_theme->getImage('bar.bottom.left.png', 'backgrounds')));
		$_i->addStyleAttributes(
			array(
				 'width'	=> ConfigHandler::get('theme-backgrounds', 'bottom-bar-border-width') . 'px'
				,'height'	=> ConfigHandler::get('theme-backgrounds', 'bottom-bar-height') . 'px'
				,'border'	=> '0px'
				,'float'	=> 'left'
				,'margin'	=> '0px'
			)
		);
		$_c->addToContent($_i);

		// Right border
		$_i = new Container('img', array(), array('src' => $_theme->getImage('bar.bottom.right.png', 'backgrounds')));
		$_i->addStyleAttributes(
			array(
				 'width'			=> ConfigHandler::get('theme-backgrounds', 'bottom-bar-border-width') . 'px'
				,'height'			=> ConfigHandler::get('theme-backgrounds', 'bottom-bar-height') . 'px'
				,'border'			=> '0px'
				,'float'			=> 'right'
				,'vertical-align'	=> 'top'
				,'margin'			=> '0px'
			)
		);
		$_c->addToContent($_i);

		// Resize icon
		$_i = new Container('img', array(), array('src' => $_theme->getImage('resize.png', 'icons'), 'alt' => 'Resize Workarea'));
		$_i->addStyleAttributes(
			array(
				 'width'			=> ConfigHandler::get('theme-icons', 'bottom-width') . 'px'
				,'height'			=> ConfigHandler::get('theme-icons', 'bottom-height') . 'px'
				,'border'			=> '0px'
				,'margin'			=> '0px 1px'
				,'vertical-align'	=> 'top'
				,'cursor'			=> 'se-resize'
				,'float'			=> 'right'
			));
		$_i->setEvent('mousedown', 'startAction (event, "' . $this->wid . '", "r")');
		$_c->addToContent($_i);

		// Create the div for this table
		$this->barBottom = new Container('div', array('id' => 'TT_wa' . $this->wid . '_bottom'));
		$this->barBottom->setContent($_t);
		$this->barBottom->addStyleAttributes(
			array(
				 'position'		=> 'absolute'
				,'left'			=>'0px'
				,'top'			=> ($this->height - ConfigHandler::get('theme-backgrounds', 'bottom-bar-height')) . 'px'
				,'width'		=> $this->width . 'px'
				,'height'		=> ConfigHandler::get('theme-backgrounds', 'bottom-bar-height') . 'px'
				,'z-index'		=> $this->z_index
			)
		);
	}

	/**
	 * Set the horizontal position
	 * \param[in] $hposition distance from left or right windowborder (in pixels)
	 */
	public function setHposition ($hposition)
	{
		$this->hposition = $hposition;
	}

	/**
	 * Set the vertical position
	 * \param[in] $vposition distance from the top or bottom
	 */
	public function setVposition ($vposition)
	{
		$this->vposition = $vposition;
	}

	/**
	 * Set the horizontal floating element
	 * \param[in] $halignment 'left'or 'right'
	 */
	public function setHalignment ($halignment)
	{
		if ($halignment != 'left' && $halignment != 'right') {
			$this->setStatus(__FILE__, __LINE__, DOM_IVVALUE, array($halignment, 'Horizontal alignment'));
		} else {
			$this->halignment = $halignment;
		}
	}

	/**
	 * Set the vertical floating element
	 * \param[in] $valignment 'top' or 'bottom'
	 */
	public function setValignment ($valignment)
	{
		if ($valignment != 'top' && $valignment != 'bottom') {
			$this->setStatus(__FILE__, __LINE__, DOM_IVVALUE, array($valignment, 'Vertical alignment'));
		} else {
			$this->valignment = $valignment;
		}
	}

	/**
	 * Set the Area width
	 * \param[in] $width width in pixels
	 */
	public function setWidth ($width)
	{
		$this->width = $width;
	}

	/**
	 * Set the Area height
	 * \param[in] $height height in pixels
	 */
	public function setHeight ($height)
	{
		$this->height = $height;
	}

	/**
	 * Set the nitial visibility status
	 * \param[in] $visibility Visibility as an integer or as a constant:
	 *   * 0: WINDOW_VISIBILITY_HIDDEN
	 *   * 1: WINDOW_VISIBILITY_SHADED
	 *   * 2: WINDOW_VISIBILITY_VISIBLE
	 *   * 3: WINDOW_VISIBILITY_MAXIMIZED
	 * \note When passing the parameter as a constant, check the note in the \ref WindowVisibility group description!
	 */
	public function setVisibility ($visibility)
	{
		$this->visibility = $visibility;
	}

	/**
	 * Set the Display order (z-index)
	 * \param[in] $z_index Ordering
	 */
	public function setZ_index ($z_index)
	{
		$this->z_index = $z_index;
	}

	/**
	 * Set the Title as displayed in the titlebalr
	 * \param[in] $title Title text
	 */
	public function setTitle ($title)
	{
		$this->title = $title;
	}

	/**
	 * Set the Border width
	 * \param[in] $border width in pixels
	 */
	public function setBorder ($border)
	{
		$this->border = $border;
	}

	/**
	 * Set the Should the Title- and Bottom bars be displayed?
	 * \param[in] $display_bars True or false
	 */
	public function setDisplay_bars (bool $display_bars)
	{
		$this->display_bars = $display_bars;
	}

	/**
	 * Set the Can the user shade the workarea?
	 * \param[in] $shadable
	 */
	public function setShadable (bool $shadable)
	{
		$this->shadable = $shadable;
	}

	/**
	 * Set the Can the user maximize the workarea?
	 * \param[in] $maximizable  True or false
	 */
	public function setMaximizable (bool $maximizable)
	{
		$this->maximizable = $maximizable;
	}

	/**
	 * Set the Can the user close the workarea?
	 * \param[in] $closable True or false
	 */
	public function setClosable (bool $closable)
	{
		$this->closable = $closable;
	}

	/**
	 * Set the Can the user move the workarea?
	 * \param[in] $movable True or false
	 */
	public function setMovable (bool $movable)
	{
		$this->movable = $movable;
	}

	/**
	 * Set the Can the user reload the workarea?
	 * \param[in] $reloadable True or false
	 */
	public function setReloadable (bool $reloadable)
	{
		$this->reloadable = $reloadable;
	}

	/**
	 * Set the Can the user resize the workarea?
	 * \param[in] $resizable True or false
	 */
	public function setResizable (bool $resizable)
	{
		$this->resizable = $resizable;
	}

	/**
	 * This container has no specific arguments, but it's the last local method called during rendering of the HTML
	 * tag, so we use it here to finalise this windows visibility.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \return Empty string
	 * \todo This method must add Javascript code to reposition and/or maximize the window
	 */
	public function showElement()
	{
		$this->addStyleAttributes(
			array(
				 'position'		=> 'absolute'
				,'left'			=> $this->hposition . 'px'
				,'top'			=> $this->vposition . 'px'
				,'width'		=> $this->width . 'px'
				,'height'		=> $this->height . 'px'
				,'z-index'		=> $this->z_index
			)
		);

		$this->createTopBar();
		$this->createContentArea();
		$this->createBottomBar();

		// Add the containers to my own content. Use parent for this, since addToContent is reimplemented!
		parent::addToContent($this->barTop);
		parent::addToContent($this->contentArea);
		parent::addToContent($this->barBottom);

		switch ($this->visibility) {
			case WINDOW_VISIBILITY_HIDDEN:
				$this->addStyleAttributes(array('visibility' => 'hidden'));
				$this->contentArea->addStyleAttributes(array('visibility' => 'hidden'));
				$this->barBottom->addStyleAttributes(array('visibility' => 'hidden'));
				break;
			case WINDOW_VISIBILITY_SHADED:
				$this->addStyleAttributes(array('visibility' => 'visible'));
				$this->contentArea->addStyleAttributes(array('visibility' => 'hidden'));
				$this->barBottom->addStyleAttributes(array('visibility' => 'hidden'));
				break;
			case WINDOW_VISIBILITY_VISIBLE:
				$this->addStyleAttributes(array('visibility' => 'visible'));
				$this->contentArea->addStyleAttributes(array('visibility' => 'visible'));
				$this->barBottom->addStyleAttributes(array('visibility' => 'visible'));
				break;
			case WINDOW_VISIBILITY_MAXIMIZED:
				$this->addStyleAttributes(array('visibility' => 'visible'));
				$this->contentArea->addStyleAttributes(array('visibility' => 'visible'));
				$this->barBottom->addStyleAttributes(array('visibility' => 'visible'));
				break;
		}
		return '';
	}
}
