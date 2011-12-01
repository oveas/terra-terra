<?php
/**
 * \file
 * This file defines a text-, password or hidden formfield element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup OWL_PLUGINS
 * Formfield text elements
 * \brief Formfield
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldTextPlugin extends FormFieldPlugin
{
	/**
	 * Field size
	 */
	private $size;

	/**
	 * Maximum field size
	 */
	private $maxsize;

	/**
	 * Class constructor;
	 * \param[in] $type Element type: text (default), password or hidden
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($type = 'text')
	{
		parent::__construct();
		$this->type = strtolower($type);
	}

	/**
	 * Set the Size attribute
	 * \param[in] $size integer
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setSize ($size)
	{
		if (is_int($size)) {
			$this->size = $size;
		} else {
			$this->setStatus(FORMFIELD_IVVAL, array($size, 'size'));
		}
	}

	/**
	 * Set the Maxsize attribute
	 * \param[in] $maxsize integer
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setMaxsize ($maxsize)
	{
		if (is_int($maxsize)) {
			$this->maxsize = $maxsize;
		} else {
			$this->setStatus(FORMFIELD_IVVAL, array($maxsize, 'maxsize'));
		}
	}

	/**
	 * Create the javascript code to replace the contents of this element with the result of
	 * a javascript request. Reimlemented from BaseElement to update the value i.s.o. innerHTML
	 * \return Name of the javascript function
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dynamicSetContent()
	{
		$_doc = OWL::factory('document', OWL_UI);
		$_fName = 'handleSetContent'.$this->id;
		$_doc->addScript("function $_fName() {\n"
				. "\tdocument.getElementById('$this->id').value = reqHandler.getResponseText();\n"
				. "}\n"
		);
		return $_fName;
	}

	/**
	 * Create the javascript code add the result of a javascript request to the contents
	 * of this element. Reimlemented from BaseElement to update the value i.s.o. innerHTML
	 * \return Name of the javascript function
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dynamicAddContent()
	{
		$_doc = OWL::factory('document', OWL_UI);
		$_fName = 'handleSetContent'.$this->id;
		$_doc->addScript("function $_fName() {\n"
				. "\tdocument.getElementById('$this->id').value += reqHandler.getResponseText();\n"
				. "}\n"
		);
		return $_fName;
	}

	/**
	 * Return the HTML code to display the form element
	 * \return Textstring with the complete code for the form element
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement ()
	{
		$_htmlCode = "<input type='$this->type'";
		if (!empty($this->size)) {
			$_htmlCode .= " size='$this->size'";
		}
		if (!empty($this->maxsize) && ($this->type != 'hidden')) {
			$_htmlCode .= " maxlength='$this->maxsize'";
		}
		$_htmlCode .= $this->getGenericFieldAttributes() . '/>';
		return $_htmlCode;
	}
}


//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

//Register::setSeverity (OWL_WARNING);

//Register::setSeverity (OWL_BUG);

//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
