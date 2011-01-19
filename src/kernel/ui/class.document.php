<?php
/**
 * \file
 * This file defines an HTML document
 * \version $Id: class.document.php,v 1.2 2011-01-19 17:04:01 oscar Exp $
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
	 * Base URL for the application, defaults to Document_Root
	 * \private
	 */
	private $base;

	/**
	 * Array for on-the-fly styles
	 * \private
	 */
	private	$styles;

	/**
	 * Array for stylesheet sources
	 * \private
	 */
	private	$css;

	/**
	 * Array for on-the-fly scripts
	 * \private
	 */
	private	$scripts;

	/**
	 * Array for javascript sources;
	 * \private
	 */
	private	$js;

	/**
	 * String for the document title
	 * \private
	 */
	private $title;

	/**
	 * Array for meta tags
	 * \private
	 */
	private $meta;

	/**
	 * String for the Favicon URL;
	 * \private
	 */
	private $favicon;

	/**
	 * String for the content type
	 * \private
	 */
	private $contentType;

	/**
	 * integer - self reference
	 * \private
	 * \static
	 */
	private static $instance;

	/**
	 * Class constructor;
	 * \param[in] $_attribs Indexed array with the HTML attributes 
	 * \private
	 */
	private function __construct ($_attribs = array())
	{
		_OWL::init();
		$this->base = $_SERVER['DOCUMENT_ROOT'];
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
	 * \public
	 * \return Severity level
	 */
	public static function get_instance()
	{
		if (!Document::$instance instanceof self) {
			Document::$instance = new self();
		}
		return Document::$instance;
	}

	/**
	 * Call to the parent's (protected) setAttributes()
	 * \param[in] $_attribs Indexed array with the HTML attributes 
	 * \public
	 */
	public function setAttribs($_attribs = array())
	{
		if (count($_attribs) > 0) {
			parent::setAttributes($_attribs);
		}
	}

	/**
	 * Add an on-the-fly style tag to the document
	 * \param $_style HTML code to define the style, without the &lt;(/)style&gt; tags
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
	 */
	public function loadStyle($_style = '', $_condition = '')
	{
		$_path = URL2Path($_style);
		// If not null the file is on the local host; check if it's there
		if ($_path !== null && !file_exists($_path)) {
			$this->set_status(DOC_NOSUCHFILE, array('stylesheet', $_style));
			return;
		}
		if (($_styleUrl = ExpandURL($_style)) === null) {
			$this->set_status(DOC_IVFILESPEC, array('stylesheet', $_script));
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
		if (!in_array($_scriptUrl, $_prt)) {
			$_prt[] = $_scriptUrl;
		}
	}

	/**
	 * Add an on-the-fly javascript to the document
	 * \param $_script Script code without the &lt;(/)script&gt; tags
	 */
	public function addScript($_script = '')
	{
		$this->scripts[] = $_script;
	}

	/**
	 * Add a Javascript sourcefile to the document
	 * \param $_script URL of the scriptsource
	 */
	public function loadScript($_script = '')
	{
		$_path = URL2Path($_script);
		// If not null the file is on the local host; check if it's there
		if ($_path !== null && !file_exists($_path)) {
			$this->set_status(DOC_NOSUCHFILE, array('javascript', $_script));
			return;
		}
		if (($_scriptUrl = ExpandURL($_script)) === null) {
			$this->set_status(DOC_IVFILESPEC, array('javascript', $_script));
			return;
		}
		if (!in_array($_scriptUrl, $this->js)) {
			$this->js[] = $_scriptUrl;
		}
	}

	/**
	 * Set the page title
	 * \param[in] $_value Title value
	 * \public
	 */
	public function setTitle($_value)
	{
		$this->title = $_value;
	}

	/**
	 * Set the Favorites Icon
	 * \param[in] $_icon URL to the icon. It must be on the local site
	 */
	public function setFavicon($_icon)
	{
		$_path = URL2Path($_icon);
		if ($_path === null || !file_exists($_path)) {
			$this->set_status(DOC_NOSUCHFILE, array('favicon', $_icon));
			return;
		}
		if (($_iconURL = ExpandURL($_icon)) === null) {
			$this->set_status(DOC_IVFILESPEC, array('favicon', $_icon));
			return;
		}
		$this->favicon = $_iconURL;
	}

	/**
	 * Set the base URL for the site
	 * \param[in] $_url Fully qualified URL
	 */
	public function setBase($_url)
	{
		// TODO Validate the given URL
		$this->base = $_url;
	}

	/**
	 * Set, update or overwrite metatags. Only the 'generator' metatag cannot be overwritten
	 * \param[in] $_tags Array with meta tags in the format 'name' => 'content'
	 */
	public function setMeta($_tags)
	{
		foreach ($_tags as $_name => $_content) {
			if ($_name == 'keywords') {
				$this->meta[$_name][] = $_content;
			} elseif ($_name == 'generator') {
				// Not allowed to overwrite this meta tag
				$this->set_status(DOC_PROTTAG, arra('meta', 'generator'));
			} else {
				$this->meta[$_name] = $_content;
			}
		}
	}

	/**
	 * Set the page's content type
	 * \param[in] $_value Title value
	 * \public
	 */
	public function setContenttype($_value)
	{
		$this->contentType = $_value;
	}

	/**
	 * Get the Base href
	 * \return URL
	 * \public
	 */
	public function getBase()
	{
		return $this->base;
	}

	/**
	 * Get the meta tags
	 * \return HTML code
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
	 * \public
	 * \return string with the HTML code
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
Register::register_class ('Document');

//Register::set_severity (OWL_DEBUG);

Register::set_severity (OWL_INFO);
Register::register_code('DOC_PROTTAG');

//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
Register::set_severity (OWL_WARNING);
Register::register_code('DOC_NOSUCHFILE');
Register::register_code('DOC_IVFILESPEC');


//Register::set_severity (OWL_BUG);
//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
