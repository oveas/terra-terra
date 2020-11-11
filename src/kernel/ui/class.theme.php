<?php
/**
 * \file
 * This file defines Theme class
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
 * \ingroup TT_UI_LAYOUT
 * Class that defines the singleton theme.
 * \brief Theme class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 13, 2020 -- O van Eijk -- initial version
 */
class Theme extends _TT
{
	/**
	 * integer - self reference
	 */
	protected static $instance;

	/**
	 * Selected theme
	 */
	protected $theme;

	/**
	 * Selected theme variant
	 */
	protected $variant;

	/**
	 * Constructor. It reads the theme and optional theme variant from the configuration.
	 */
	protected function __construct()
	{
		$this->setTheme(ConfigHandler::get ('layout', 'theme'));
		$this->setVariant(ConfigHandler::get ('layout', 'variant', ''));
		ConfigHandler::readConfig(array('file' => TT_THEMES . '/' . $this->theme . '/tt_theme.cfg'));
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInstance()
	{
		if (!Theme::$instance instanceof self) {
			Theme::$instance = new self();
		}
		return Theme::$instance;
	}

	/**
	 * Load all existing stylesheets from a given location. The location can be the theme's location,
	 * of the location of a theme variant.
	 * \param[in] $location Location as a path relative from TT_THEMES
	 * \return An array with all stylesheets from the location with full URL.
	 */
	private function _getStyleSheets ($location)
	{
		$_styles = array();
		if ($_themesDir = opendir(TT_THEMES . '/' . $location)) {
			while (($_file = readdir($_themesDir)) !== false) {
				if (preg_match('/\.css/', $_file)) {
					$_styles[] = TT_THEMES_URL . '/' . $location . '/' . $_file;
				}
			}
		}
		closedir($_themesDir);
		return $_styles;
	}

	/**
	 * Set the theme. When the theme is not fount, the THEME_NOSUCHTHEME status is set.
	 * \param[in] $theme Name of the theme
	 */
	public function setTheme($theme)
	{
		if (is_dir(TT_THEMES . '/' . $theme)) {
			$this->theme = $theme;
		} else {
			$this->setStatus(__FILE__, __LINE__, THEME_NOSUCHTHEME, array($theme));
		}
	}

	/**
	 * Set the theme variant. When the theme is not fount, the THEME_NOSUCHVARIANT status is set.
	 * \param[in] $variant Name of the variant. It can be empty in which case nothing is done.
	 */
	public function setVariant($variant)
	{
		if ($variant == '') {
			return;
		}

		if (is_dir(TT_THEMES . '/' . $this->theme . '/variants/' . $variant)) {
			$this->variant = $variant;
		} else {
			$this->setStatus(__FILE__, __LINE__, THEME_NOSUCHVARIANT, array($this->theme, $variant));
		}
	}

	/**
	 * Get the full URL of an image. First, the variant is checked if this theme as a variant,
	 * if not found there, the same location at theme level is checked.
	 * \param $image Full name of the image.
	 * \param $location Location of the image, which is an optional subdirectory of the variant or theme directory
	 * \return Full URL of the image
	 */
	public function getImage($image, $location = '')
	{
		if ($location != '') {
			$location = '/' . $location;
		}
		if ($this->variant != '') {
			if (file_exists(TT_THEMES . '/' . $this->theme . '/variants/' . $this->variant . $location . '/' . $image)) {
				return TT_THEMES_URL . '/' . $this->theme . '/variants/' . $this->variant . $location . '/' . $image;
			}
		}
		if (file_exists(TT_THEMES . '/' . $this->theme . $location . '/' . $image)) {
			return TT_THEMES_URL . '/' . $this->theme . $location . '/' . $image;
		}
		$this->setStatus(__FILE__, __LINE__, THEME_NOSUCHIMAGE, array($image, $location, $this->theme, $this->variant));
	}

	/**
	 * Collect all stylesheets for this theme, including a selected variant if any.
	 * \return Array with all stylesheets including the full URL.
	 */
	public function getStyleSheets()
	{
		$_styles = $this->_getStyleSheets($this->theme);
		if ($this->variant != '') {
			$_styles = array_merge($_styles, $this->_getStyleSheets($this->theme . '/variants/' . $this->variant));
		}
		return $_styles;
	}

	/**
	 * Load the layout for this theme
	 */
	public function loadLayout()
	{
		if (!TTloader::getClass('layout', TT_THEMES . '/' . $this->theme)) {
			// We're too early in the process to signal() the error, fallback to PHP errorsignaling
			trigger_error('Error loading the Layout class from ' . TT_THEMES  . '/' . $this->theme, E_USER_ERROR);
		} else {
			Layout::createContainers();
		}
	}

	/**
	 * Get the full theme, including the variant
	 * \return Theme and variant as a path-segment
	 */
	public function getFullTheme()
	{
		return $this->theme . '/variants/' . $this->variant;
	}

	/**
	 * Get the theme name
	 * \return Theme
	 */
	public function getTheme()
	{
		return $this->theme;
	}

	/**
	 * Get the variant name
	 * \return Variant
	 */
	public function getVariant()
	{
		return $this->variant;
	}
}

Register::registerClass('Theme', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);
//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);

Register::setSeverity (TT_WARNING);
Register::registerCode('THEME_NOSUCHVARIANT');
Register::registerCode('THEME_NOSUCHIMAGE');

//Register::setSeverity (TT_BUG);

Register::setSeverity (TT_ERROR);
Register::registerCode('THEME_NOSUCHTHEME');

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
