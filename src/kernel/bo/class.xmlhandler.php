<?php
/**
 * \file
 * This file defines the XML Handler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2014} Oscar van Eijk, Oveas Functionality Provider
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
 * \ingroup TT_SO_LAYER
 * Handle XML data
 * \brief XML handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 13, 2014 -- O van Eijk -- initial version for Terra-Terra
 */
class XmlHandler extends _TT
{

	private $fileObject;	//!< Filehandler object
	private $xmlData;		//!< SimpleXMLElement object containing  the XML data

	/**
	 * Object constructor; setup the file characteristics
	 * \param[in] $name Name of the XML file. If not null, the file must exist
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($name = null)
	{
		_TT::init(__FILE__, __LINE__);

		$this->fileObject = null;
		if ($name !== null) {
			$this->fileObject = new FileHandler($name);
		}
	}

	/**
	 * Set a filename fot the XML file. This file does not have to exist.
	 * \param[in] $name Name of the XML file
	 */
	public function setFile ($name)
	{
		$this->fileObject = new FileHandler($name, false);
	}


	/**
	 * Parse the XML file and store the data in a SimpleXMLElement object
	 * \return True on success, false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function parse ()
	{
		if ($this->fileObject === null) {
			$this->setStatus (__FILE__, __LINE__, XML_NOFILOBJ);
			return false;
		}
		$_fName = $this->fileObject->getFileName();
		if ($this->fileObject->exists() === false) {
			$this->setStatus (__FILE__, __LINE__, XML_NOSUCHFILE, array($_fName));
			return false;
		}
		if (($this->xmlData = simplexml_load_file($_fName, 'SimpleXMLElement', LIBXML_NOBLANKS)) === false) {
			$this->setStatus (__FILE__, __LINE__, XML_PARSERR, array($_fName));
			return false;
		}
		$this->setStatus (__FILE__, __LINE__, XML_FPARSED, array($_fName));
		return true;
	}

	/**
	 * Get the data from an XML element object and return as an array
	 * \param[in] $node Reference to an XML element object as returned by getNodeByPath()
	 * \return array with the nodedata with the following elements:
	 *    * _TTnodeName =&gt; Name of the element
	 *    * _TTnodeText =&gt; Nodetext formatted as a string
	 *    * [attributeName] =&gt; [attributeValue]
	 *    * ... (continued for all attributes)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getNodeData (SimpleXMLElement &$node)
	{
		$_data = array(
			 '_TTnodeName' => $node->getName()
			,'_TTnodeText' => (string)$node
		);
		foreach($node->attributes() as $_attrib => $_value) {
			$_data[$_attrib] = (string)$_value;
		}
		return $_data;
	}

	/**
	 * Get a reference to an XML element object, specified by path
	 * \param[in] $path Path from the first child level specified as a string seperated by slashes. Optional indexes can be given seperated by commas, e.g.:
	 *
	 *   XML file:
	 *   <pre>
	 *      &lt;person&gt;
	 *         &lt;sons&gt;
	 *           &lt;child name="Joe"&gt;
	 *             &lt;sons&gt;
	 *               &lt;child name="Mike"/&gt;
	 *             &lt;/sons&gt;
	 *           &lt;/child&gt;
	 *         &lt;/sons&gt;
	 *         &lt;daughters&gt;
	 *           &lt;child name="Mary"&gt;
	 *             &lt;sons&gt;
	 *               &lt;child name="Jack"/&gt;
	 *             &lt;/sons&gt;
	 *             &lt;daughters&gt;
	 *               &lt;child name="Jane"/&gt;
	 *             &lt;/daugters&gt;
	 *           &lt;/child&gt;
	 *           &lt;child name="Rose"&gt;
	 *             &lt;daughters&gt;
	 *               &lt;child name="Julie"/&gt;
	 *             &lt;/daugters&gt;
	 *           &lt;/child&gt;
	 *         &lt;/daughters&gt;
	 *      &lt;/person&gt;
	 *   </pre>
	 *   In this XML, the path 'daughters/child,2/daughters/child' will return a reference to Julie. When an index is out of range (e.g. 'sons/child,2'),
	 *   null will be returned.
	 *
	 * To get a reference to the 'person' rootnode, specify null (which is the default).
	 * \param[in] $checkFullPath Boolean that can be set to true to make sure the full path is checked. If this is not done,
	 * the eval() at the end of this method might throw an uncatchable fatal error exiting Terra-Terra immediatly if an elementname
	 * halfway the path does nog exist. Setting this to true might degrade performance significantly when large XML files are parsed.
	 * \return Object reference, or null when the path does not exist
	 * \todo Get gid of the eval here. Original idea was to use a floating pointer ($_xmlObject) used instead of the $_xmlPath, but that fails
	 * all checks.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getNodeByPath ($path = null, $checkFullPath = false)
	{
		$_xmlPath = '$this->xmlData';
		if ($path !== null) {
			$_path = explode ('/', $path);
			foreach ($_path as $_child) {
				$_childInfo = explode (',', $_child);
				$_index = 0;
				if (count($_childInfo) == 1) {
					$_node = $_childInfo[0];
				} else {
					$_node = $_childInfo[0];
					$_index = $_childInfo[1];
				}
				$_xmlPath .= "->children()->{$_node}[$_index]";
				if ($checkFullPath === true) {
					eval("\$xmlObject = $_xmlPath;");
					if (!is_object($xmlObject)) {
						$this->setStatus (__FILE__, __LINE__, XML_NOSUCHFILE, array($path));
						return null;
					}
				}
			}
		}
		eval ("\$_xmlObject = $_xmlPath;");
		return $_xmlObject;
	}

	/**
	 * Find out if a child with the given name exists in the given node
	 * \param[in] $child Name of the child
	 * \param[in] $node XML element object as returned by getNodeByPath()
	 * \return True if the child exists, false if not
	 */
	public function childExists ($child, SimpleXMLElement $node = null)
	{
		if ($node === null) {
			$node = $this->xmlData;
		}
		return (array_key_exists ($child, $node->children()));
	}
}

/*
 * Register this class and all status codes
*/
Register::registerClass('XmlHandler', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);
Register::setSeverity (TT_INFO);

//Register::setSeverity (TT_OK);

Register::setSeverity (TT_SUCCESS);
Register::registerCode ('XML_FPARSED');

//Register::setSeverity (TT_WARNING);

//Register::setSeverity (TT_BUG);

Register::setSeverity (TT_ERROR);
Register::registerCode ('XML_NOFILOBJ');
Register::registerCode ('XML_NOSUCHFILE');
Register::registerCode ('XML_PARSERR');

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
