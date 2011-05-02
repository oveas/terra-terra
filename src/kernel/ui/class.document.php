<?php
/**
 * \file
 * This file defines an HTML document
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.document.php,v 1.6 2011-05-02 12:56:14 oscar Exp $
 */

/**
 * \ingroup OWL_UI_LAYER
 * Class for Document singletons
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
	private static $instance;

	/**
	 * Class constructor;
	 * \param[in] $_attribs Indexed array with the HTML attributes 
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function __construct (array $_attribs = array())
	{
		_OWL::init();
		$_proto = explode('/', $_SERVER['SERVER_PROTOCOL']);
		$this->base = strtolower($_proto[0]) . '://' . $_SERVER['HTTP_HOST'];
		$this->styles = array();
		$this->css = array('unconditional' => array());
		$this->scripts = array();
		$this->js = array();
		$this->title = 'OWL Generated document';
		$this->meta = array(
			  'robots'	=> 'index, follow'
			, 'keywords'	=> array('OWL-PHP', 'Oveas', 'OWL')
			, 'description'	=> 'OWL-PHP - Oveas Web Library for PHP'
			, 'generator'	=> 'OWL-PHP v'.OWL_VERSION.' - Oveas Web Library for PHP, (c)2006-2011 Oveas Functionality Provider'
		);
		$this->favicon = '';
		$this->contentType = 'text/html; charset=utf-8';
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
	 * Add an on-the-fly style tag to the document
	 * \param $_style HTML code to define the style, without the &lt;(/)style&gt; tags
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addStyle($_style = '')
	{
		$this->styles[] = $_style;
	}

	/**
	 * Add a CSS stylesheet to the document
	 * \param[in] $_style URL of the stylesheet
	 * \param[in] $_condition Condition to specify the browser(s), e.g. "lte IE 6" means the stylesheet
	 * will be loaded only for Internet Explorer up and including version 6.
	 * \see http://www.thesitewizard.com/css/excludecss.shtml for the syntax
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function loadStyle($_style = '', $_condition = '')
	{
		$_path = URL2Path($_style);
		// If not null the file is on the local host; check if it's there
		if ($_path !== null && !file_exists($_path)) {
			$this->setStatus(DOC_NOSUCHFILE, array('stylesheet', $_style));
			return;
		}
		if (($_styleUrl = ExpandURL($_style)) === null) {
			$this->setStatus(DOC_IVFILESPEC, array('stylesheet', $_script));
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
	public function addScript($_script = '')
	{
		$this->scripts[] = $_script;
	}

	/**
	 * Add a Javascript sourcefile to the document
	 * \param $_script URL of the scriptsource
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function loadScript($_script = '')
	{
		$_path = URL2Path($_script);
		// If not null the file is on the local host; check if it's there
		if ($_path !== null && !file_exists($_path)) {
			$this->setStatus(DOC_NOSUCHFILE, array('javascript', $_script));
			return;
		}
		if (($_scriptUrl = ExpandURL($_script)) === null) {
			$this->setStatus(DOC_IVFILESPEC, array('javascript', $_script));
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
		$_path = URL2Path($_icon);
		if ($_path === null || !file_exists($_path)) {
			$this->setStatus(DOC_NOSUCHFILE, array('favicon', $_icon));
			return;
		}
		if (($_iconURL = ExpandURL($_icon)) === null) {
			$this->setStatus(DOC_IVFILESPEC, array('favicon', $_icon));
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
	public function setMeta($_tags)
	{
		foreach ($_tags as $_name => $_content) {
			if ($_name == 'keywords') {
				$this->meta[$_name][] = $_content;
			} elseif ($_name == 'generator') {
				// Not allowed to overwrite this meta tag
				$this->setStatus(DOC_PROTTAG, arra('meta', 'generator'));
			} else {
				$this->meta[$_name] = $_content;
			}
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
	 * \return URL
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getBase()
	{
		return $this->base;
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
			$_htmlCode .= '<script type="text/javascript" src="'.$_src.'" />'."\n";
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
			$_htmlCode .= '<script type="text/javascript">//<![CDATA['."\n";
			$_htmlCode .= "<!--\n";
			$_htmlCode .= implode("\n", $this->scripts);
			$_htmlCode .= "// -->\n";
			$_htmlCode .= "//]]></script>\n";
		}
		return $_htmlCode;
	}

	/**
	 * Get the HTML code to display the document
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$_htmlCode  = '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
		$_htmlCode .= "<head>\n";
		$_htmlCode .= '<base href="'.$this->getBase().'" />'."\n";
		$_htmlCode .= $this->_getMeta();
		$_htmlCode .= "<title>$this->title</title>\n";
		if ($this->favicon != '') {
			$_htmlCode .= '<link href="'.$this->favicon.'" rel="shortcut icon" type="image/x-icon" />'."\n";
		}
		$_htmlCode .= $this->_loadStyles();
		$_htmlCode .= $this->_loadScripts();

		$_htmlCode .= "</head>\n";
		$_htmlCode .= '<body';
		$_htmlCode .= $this->getAttributes();
		$_htmlCode .= ">\n";
		$_htmlCode .= $this->_getStyles();
		$_htmlCode .= $this->_getScripts();
		$_htmlCode .= $this->getContent() . "\n";
		$_htmlCode .= "</body>\n";
		$_htmlCode .= "</html>\n";
		return $_htmlCode;
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('Document');

//Register::setSeverity (OWL_DEBUG);

Register::setSeverity (OWL_INFO);
Register::registerCode('DOC_PROTTAG');

//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
Register::setSeverity (OWL_WARNING);
Register::registerCode('DOC_NOSUCHFILE');
Register::registerCode('DOC_IVFILESPEC');


//Register::setSeverity (OWL_BUG);
//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
