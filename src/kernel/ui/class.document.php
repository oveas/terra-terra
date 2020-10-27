<?php
/**
 * \file
 * This file defines an HTML document
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
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
 * \ingroup TT_UI_LAYER
 * Class for Document singletons.
 * This class can be extended defining other document types
 * \brief Document
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 9, 2011 -- O van Eijk -- initial version
 */
class Document extends BaseElement
{
	/**
	* Base URL for the application, defaults to the server top
	*/
	private $base;

	/**
	* Base URL for the TT installation, defaults to the server top
	*/
	private $ttBase;

	/**
	 * Array for on-the-fly styles
	 */
	private	$styles;

	/**
	 * Array for stylesheet sources
	 */
	private	$css;

	/**
	 * Array for on-the-fly scripts
	 */
	private	$scripts;

	/**
	 * Array for javascript sources;
	 */
	private	$js;

	/**
	 * String for the document title
	 */
	private $title;

	/**
	 * Array for meta tags
	 */
	private $meta;

	/**
	 * Array for HTML headers
	 */
	private $headers;

	/**
	 * String for the Favicon URL;
	 */
	private $favicon;

	/**
	 * String for the content type
	 */
	private $contentType;

	/**
	 * integer - self reference
	 */
	protected static $instance;

	/**
	 * List of errors, warnings and other requested messages
	 */
	private $messages;

	/**
	 * Container for messages; if null, messages will be added to the front of the document
	 */
	private $msgContainer;

	/**
	 * Switch that will be set to True when TT-JS is enabled
	 */
	private $tt_jsEnabled;

	/**
	 * Boolean indicating if the document has been retrieved for display
	 */
	private $open;

	/**
	 * Reference to the Theme object
	 */
	private $theme;

	/**
	 * Class constructor;
	 * \param[in] $_attribs Indexed array with the HTML attributes
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function __construct (array $_attribs = array())
	{
		_TT::init(__FILE__, __LINE__);
		$_proto = explode('/', $_SERVER['SERVER_PROTOCOL']);
		$this->ttBase = $this->base = strtolower($_proto[0]) . '://' . $_SERVER['HTTP_HOST'];
		if (defined('TT_USER_LOCATION')) {
			$this->base .= '/' . TT_USER_LOCATION;
		}
		$this->styles = array();
		$this->css = array('unconditional' => array());
		$this->scripts = array();
		$this->js = array();
		$this->title = 'Terra-Terra Generated document';
		$this->meta = array(
			  'robots'	=> 'index, follow'
			, 'keywords'	=> array(TT_APPNAME, 'Oveas', TT_CODE)
			, 'description'	=> TT_APPNAME . ' - Oveas Web 2.2 Platform'
			, 'generator'	=> TT_APPNAME . ' v' . TTCache::getApplic(TT_CODE, TT_APPITM_VERSION)
				. ' - Oveas Web 2.2 Platform, (c)2001-'.substr(TTCache::getApplic(TT_CODE, TT_APPITM_RELEASED), 0, 4)
				. ' Oveas Functionality Provider'
		);
		$this->header = array();
		$this->messages = array();
		$this->favicon = '';
		$this->contentType = 'text/html; charset=utf-8';
		$this->tt_jsEnabled = false;
		$this->open = false;
		$this->theme = null;
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInstance()
	{
		if (!Document::$instance instanceof self) {
			Document::$instance = new self();
		}
		return Document::$instance;
	}

	/**
	 * This method can be called at any time to enable TT-JS. It writes some global variables
	 * to the document for use by JavaScript, and loads the library file with the core functionality.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function enableTT_JS()
	{
		if ($this->tt_jsEnabled === true) {
			return;
		}
		$this->tt_jsEnabled = true;
		$this->addScript("// These variables are written here by Terra-Terra. If any of them need to change,\n"
						. "// you should change them in TTloader.php, section TT_Globals.\n"
						. "// The defines in Terra-Terra have the same name as the variables here.");
		$this->addScript("\n// Top URL of Terra-Terra/JS\n" . 'var TT_JS_TOP = "' . TT_JS_TOP . '";');
		$this->addScript("\n// Location of the Terra-Terra/JS standard libfiles\n" . 'var TT_JS_LIB = "' . TT_JS_LIB . '";');
		$this->addScript("\n// Top location of the Terra-Terra/JS plugins\n" . 'var TT_JS_PLUGINS = "' . TT_JS_PLUGINS . '";');
		$this->addScript("\n// Name of the Terra-Terra dispatcher as it should appear in requests\n" . 'var TT_DISPATCHER_NAME = "' . TT_DISPATCHER_NAME . '";');
		$this->addScript("\n// Callback URL for requests (AJAX, Form, links etc).\n" . 'var TT_CALLBACK_URL = "' . TT_CALLBACK_URL . '";');
		$this->loadScript(TT_JS_LIB . '/terra-terra.js');

		// Now load the language extentions.
		// TODO This can be removed when I got languageExtentions() in tt.js working...
		$lextDir = TT_SERVER_TOP . TT_JS_LIB . '/lext/';
		if ($dH = opendir($lextDir)) {
			while (($fName = readdir($dH)) !== false) {
				if (is_file($lextDir . $fName)) {
					$fElements = explode('.', $fName);
					if (array_pop($fElements) == 'js') {
						$this->loadScript(TT_JS_LIB . '/lext/' . $fName);
					}
				}
			}
		}
		closedir($dH);
	}

	/**
	 * Add an TT-JD plugin to the document. If necessary, TT-JS is enabled first.
	 * Also, an attempt will be made to load a style sheet in case the plugin requires one.
	 * \param[in] $type The plugin type, must be a directory name in the TT-JS plugin directory
	 * \param[in] $name Name of the plugin which must match (in lowercase) the filename of the plugin
	 * without '.js'. If the plugin requires a special style sheet, it must be in the same location with
	 * the same name and extension '.css'.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addJSPlugin($type, $name)
	{
		$this->enableTT_JS();
		$this->loadScript(TT_JS_PLUGINS . '/' . $type  . '/' . strtolower($name) . '.js');
		$this->loadStyle(TT_JS_PLUGINS . '/' . $type  . '/' . strtolower($name) . '.css', '', true);
	}

	/**
	 * Add a new message to the proper message stack. For each severity level a seperate
	 * stack will be created. When the document is displayed, these messages will be formatted
	 * and added the the documents output
	 * \param[in] $stack Severity level, indicating the stack
	 * \param[in] $message The complete message as composed by signal()
	 * \deprecated Console should be used instead
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addMessage($stack, $message)
	{
		if (!array_key_exists('s'.$stack, $this->messages)) {
			$this->messages['s'.$stack] = array(
				 'stack' => $stack
				,'messages' => array()
			);
		}
		$this->messages['s'.$stack]['messages'][] = $message;
	}

	/**
	 * Add an on-the-fly style tag to the document
	 * \param $_style HTML code to define the style, without the &lt;(/)style&gt; tags
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addStyle($_style)
	{
		$this->styles[] = $_style;
	}

	/**
	 * Add a CSS stylesheet to the document
	 * \param[in] $_style URL of the stylesheet
	 * \param[in] $_condition Condition to specify the browser(s), e.g. "lte IE 6" means the stylesheet
	 * will be loaded only for Internet Explorer up and including version 6. See
	 * http://www.thesitewizard.com/css/excludecss.shtml for the full syntax of conditions
	 * \param[in] $_try When true, just try to load the stylesheet, ignoring (not logging) any errors. This is used for
	 * loading TT-JS plugins and defaults to false.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function loadStyle($_style, $_condition = '', $_try = false)
	{
		$_path = urlToPath($_style);
		// If not null the file is on the local host; check if it's there
		if ($_path !== null) {
			if (file_exists(TT_SITE_TOP . $_style)) {
				$_style = TT_SITE_TOP . $_style;
			} elseif (TT_SERVER_TOP != TT_SITE_TOP && file_exists(TT_SERVER_TOP . $_style)) {
				$_style = TT_SERVER_TOP . $_style;
			} elseif (!file_exists($_style)) {
				if ($_try !== true) {
					// TODO This one signals as "(WARNING:DOC_NOSUCHFILE) No section given for configuration item stylesheet (File: /var/www/terra-terra/kernel/ui/class.document.php, Line: 267)"
					$this->setStatus(__FILE__, __LINE__, DOC_NOSUCHFILE, array('stylesheet', $_style));
				}
				return;
			}
		}
		if (($_styleUrl = urlExpand($_style)) === null) {
			if ($_try !== true) {
				$this->setStatus(__FILE__, __LINE__, DOC_IVFILESPEC, array('stylesheet', $_style));
			}
			return;
		}

		if ($_condition != '') {
			if (!array_key_exists($_condition, $this->css)) {
				$this->css[$_condition] = array();
			}
			$_ptr =& $this->css[$_condition];
		} else {
			$_ptr =& $this->css['unconditional'];
		}
		if (!in_array($_styleUrl, $_ptr)) {
			$_ptr[] = $_styleUrl;
		}
	}

	/**
	 * Add an on-the-fly javascript to the document
	 * \param $_script Script code without the &lt;(/)script&gt; tags
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addScript($_script)
	{
		$this->scripts[] = $_script;
	}

	/**
	 * Add a Javascript sourcefile to the document
	 * \param $_script URL of the scriptsource
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function loadScript($_script)
	{
		$_path = urlToPath($_script);

		// If not null the file is on the local host; check if it's there
		if ($_path !== null) {
			if (file_exists(TT_SITE_TOP . $_script)) {
				$_script = TT_SITE_TOP . $_script;
			} elseif (TT_SERVER_TOP != TT_SITE_TOP && file_exists(TT_SERVER_TOP . $_script)) {
				$_script = TT_SERVER_TOP . $_script;
			} elseif (!file_exists($_script)) {
				$this->setStatus(__FILE__, __LINE__, DOC_NOSUCHFILE, array('javascript', $_script));
				return;
			}
		}
		if (($_scriptUrl = urlExpand($_script)) === null) {
			$this->setStatus(__FILE__, __LINE__, DOC_IVFILESPEC, array('javascript', $_script));
			return;
		}
		if (!in_array($_scriptUrl, $this->js)) {
			$this->js[] = $_scriptUrl;
		}
	}

	/**
	 * Set the page title
	 * \param[in] $_value Title value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setTitle($_value)
	{
		$this->title = $_value;
	}

	/**
	 * Set the Favorites Icon
	 * \param[in] $_icon URL to the icon. It must be on the local site
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setFavicon($_icon)
	{
		$_path = urlToPath($_icon);
		if ($_path === null || !file_exists($_path)) {
			$this->setStatus(__FILE__, __LINE__, DOC_NOSUCHFILE, array('favicon', $_icon));
			return;
		}
		if (($_iconURL = urlExpand($_icon)) === null) {
			$this->setStatus(__FILE__, __LINE__, DOC_IVFILESPEC, array('favicon', $_icon));
			return;
		}
		$this->favicon = $_iconURL;
	}

	/**
	 * Set the base URL for the site
	 * \param[in] $_url Fully qualified URL
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setBase($_url)
	{
		// TODO Validate the given URL
		$this->base = $_url;
	}

	/**
	 * Set, update or overwrite metatags. Only the 'generator' metatag cannot be overwritten
	 * \param[in] $_tags Array with meta tags in the format 'name' => 'content'
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setMeta(array $_tags)
	{
		foreach ($_tags as $_name => $_content) {
			if ($_name == 'keywords') {
				$this->meta[$_name][] = $_content;
			} elseif ($_name == 'generator') {
				// Not allowed to overwrite this meta tag
				$this->setStatus(__FILE__, __LINE__, DOC_PROTTAG, arra('meta', 'generator'));
			} else {
				$this->meta[$_name] = $_content;
			}
		}
	}

	/**
	 * Set, update or overwrite HTML headers.
	 * \param[in] $_hdrs Array with headers in the format 'header' => 'value'
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setHeader (array $_hdrs)
	{
		foreach ($_hdrs as $_hdr => $_val) {
			$this->headers[$_hdr] = $_val;
		}
	}

	/**
	 * Set the page's content type
	 * \param[in] $_value Title value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setContenttype($_value)
	{
		$this->contentType = $_value;
	}

	/**
	 * Get the Base href
	 * \param[in] $ttBase True when the serverwide TT base should be returned
	 * \return URL
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getBase($ttBase = false)
	{
		return ($ttBase === true) ? $this->ttBase : $this->base;
	}

	/**
	 * Load the stylesheets from the active theme
	 * \param[in] $theme An optional theme. If omitted, the theme will be taken from the configuration
	 * \param[in] $variant An optional theme variane. If omitted, the variant will be taken from the configuration
	 */
	public function setTheme($theme = null, $variant = null)
	{
		$_theme = TT::factory('Theme', 'ui');
		if ($theme !== null) {
			$_theme->setTheme($theme);
		}
		if ($variant !== null) {
			$_theme->setVariane($variant);
		}
		$_styles = $_theme->getStyleSheets();
		foreach ($_styles as $_style) {
			$this->loadStyle($_style);
		}
	}

	/**
	 * Get the meta tags
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function _getMeta()
	{
		$_htmlCode = '';
		if ($this->contentType != '') {
			$_htmlCode .= '<meta http-equiv="content-type" content="'.$this->contentType.'" />'."\n";
		}
		foreach ($this->meta as $_n => $_c) {
			if ($_n == 'keywords') {
				$_c = implode(',', $_c);
			}
			$_htmlCode .= '<meta name="'.$_n.'" content="'.$_c.'" />'."\n";
		}
		return $_htmlCode;
	}

	/**
	 * Get the stylesheets to load
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function _loadStyles()
	{
		$_htmlCode = '';
		foreach ($this->css as $_condition => $_styles) {
			if ($_condition != 'unconditional') {
				$_htmlCode .= "<!--[if $_condition]>\n";
			}
			foreach ($_styles as $_css) {
				$_htmlCode .= '<link rel="stylesheet" href="'.$_css.'" type="text/css" />'."\n";
			}
			if ($_condition != 'unconditional') {
				$_htmlCode .= "<![endif]-->\n";
			}
		}
		return $_htmlCode;
	}

	/**
	 * Get the javascripts to load
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function _loadScripts()
	{
		$_htmlCode = '';
		foreach ($this->js as $_src) {
			$_htmlCode .= '<script language="javascript" type="text/javascript" src="'.$_src.'" ></script>'."\n";
		}
		return $_htmlCode;
	}

	/**
	 * Get the on-the-fly styles
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function _getStyles()
	{
		$_htmlCode = '';
		if (count($this->styles) > 0) {
			$_htmlCode .= '<style type="text/css">'."\n";
			$_htmlCode .= implode("\n", $this->styles);
			$_htmlCode .= "</style>\n";
		}
		return $_htmlCode;
	}

	/**
	 * Get the on-the-fly javascript
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function _getScripts()
	{
		$_htmlCode = '';
		if (count($this->scripts) > 0) {
			$_htmlCode .= '<script language="javascript" type="text/javascript">//<![CDATA['."\n";
			$_htmlCode .= "<!--\n";
			$_htmlCode .= implode("\n", $this->scripts);
			$_htmlCode .= "\n// -->\n";
			$_htmlCode .= "//]]></script>\n";
		}
		return $_htmlCode;
	}

	/**
	 * If messages have been generated during this run that have been added to the messages
	 * stacks, generate the containers now and add them to the front of the content objectlist
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \deprecated Console should be used instead via the LogHandler
	 */
	private function addMessagesToContent()
	{
		foreach ($this->messages as $stack) {
			switch ($stack['stack']) {
				case TT_DEBUG :
					$class = 'debugMessages';
					break;
				case TT_INFO :
				case TT_OK :
					$class = 'infoMessages';
					break;
				case TT_SUCCESS :
					$class = 'successMessages';
					break;
				case TT_WARNING :
					$class = 'warningMessages';
					break;
				case TT_BUG :
				case TT_ERROR :
				case TT_FATAL :
				case TT_CRITICAL :
					$class = 'errorMessages';
					break;
			}
			$_msgList = implode('<br />', $stack['messages']);
			$_stackContainer = new Container('div', array('class' => $class));
			$_stackContainer->setContent($_msgList);
			if ($this->msgContainer instanceof Container) {
				$this->msgContainer->addToContent($_stackContainer);
			} else {
				$this->addToContent($_stackContainer, true);
			}
		}
	}

	/**
	 * Set the container used to display messages. If not set, or set to null, messages will be
	 * added to the front of the document.
	 * \param[in] $_container Message container
	 * \author Daan Schulpen
	 * \deprecated Console should be used instead
	 */
	public function setMessageContainer($_container)
	{
		if ($_container !== null && !($_container instanceof Container)) {
			$this->setStatus(__FILE__, __LINE__, DOC_IVMSGCONTAINER, array($_container));
		}
		$this->msgContainer = $_container;
	}

	/**
	 * Get the HTML code to display the document
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$this->addMessagesToContent();

		if (count($this->headers) > 0) {
			foreach ($this->headers as $_hdr => $_val) {
				header("$_hdr: $_val");
			}
		}
		$_htmlCode  = "<!DOCTYPE html>\n";
		$_htmlCode .= '<html lang="'.ConfigHandler::get('locale', 'lang', 'en-US').'">' . "\n";
		$_htmlCode .= "<head>\n";
		$_htmlCode .= '<base href="'.$this->getBase().'" />'."\n";
		$_htmlCode .= $this->_getMeta();
		$_htmlCode .= "<title>$this->title</title>\n";
		if ($this->favicon != '') {
			$_htmlCode .= '<link href="'.$this->favicon.'" rel="shortcut icon" type="image/x-icon" />'."\n";
		}
		$_htmlCode .= $this->_loadStyles();
		$_htmlCode .= $this->_getScripts();
		$_htmlCode .= $this->_loadScripts();

		$_htmlCode .= "</head>\n";
		$_htmlCode .= '<body';
		$_htmlCode .= $this->getAttributes();
		$_htmlCode .= ">\n";
		$_htmlCode .= $this->_getStyles();
		$_htmlCode .= $this->getContent() . "\n";
		$this->open = true;
		return $_htmlCode;
	}

	/**
	 * Send the code that closes the document
	 * \return string with the HTML code, or null when the document was not yet opened
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function close()
	{
		if ($this->open) {
			$this->open = false;
			return "</body>\n</html>\n";
		} else {
			$this->setStatus(__FILE__, __LINE__, DOC_NOTOPENED);
			return null;
		}
	}
}
/**
 * \example exa.document.php
 * This example shows how to create an HTML document, add some content to it and display it.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

/*
 * Register this class and all status codes
 */
Register::registerClass ('Document', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);

Register::setSeverity (TT_INFO);
Register::registerCode('DOC_PROTTAG');

//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);
Register::setSeverity (TT_WARNING);
Register::registerCode('DOC_NOTOPENED');
Register::registerCode('DOC_NOSUCHFILE');
Register::registerCode('DOC_IVFILESPEC');

Register::setSeverity (TT_BUG);
Register::registerCode('DOC_IVMSGCONTAINER');

//Register::setSeverity (TT_ERROR);
//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
