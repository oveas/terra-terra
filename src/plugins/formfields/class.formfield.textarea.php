<?php
/**
 * \file
 * This file defines a textarea formfield element
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
 * \ingroup TT_PLUGINS
 * Formfield textarea elements
 * \brief Formfield
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldTextareaPlugin extends FormFieldPlugin
{
	/**
	 * Number of columns in the textarea
	 */
	public $rows;

	/**
	 * Number of rows in the textarea
	 */
	public $cols;


	/**
	 * Class constructor;
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->type = 'textarea';
	}

	/**
	 * Create the javascript code to replace the contents of this element with the result of
	 * a javascript request. Reimlemented from BaseElement to update the value i.s.o. innerHTML
	 * \return Name of the javascript function
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dynamicSetContent()
	{
		$_doc = TT::factory('document', 'ui');
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
		$_doc = TT::factory('document', 'ui');
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
		$_htmlCode = "<textarea";
		if (!empty($this->rows)) {
			$_htmlCode .= " rows='$this->rows'";
		}
		if (!empty($this->cols)) {
			$_htmlCode .= " cols='$this->cols'";
		}
		$_htmlCode .= $this->getGenericFieldAttributes() . '>' . $this->value . '</textarea>';
		return $_htmlCode;
	}
}


//Register::setSeverity (TT_DEBUG);

//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

//Register::setSeverity (TT_WARNING);

//Register::setSeverity (TT_BUG);

//Register::setSeverity (TT_ERROR);
//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
