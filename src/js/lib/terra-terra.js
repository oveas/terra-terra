/****h* Terra-Terra/library
 * NAME
 *  library - General routines
 *
 * DESCRIPTION
 *  This file contains generally used library functions. When using Terra-Terra/JS, this script must
 *  always be included in the document by the application.
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * COPYRIGHT
 *  (c)2002-2014 -- Oscar van Eijk, Oveas Functionality Provider
 *
 * LICENSE
 *  This file is part of Terra-Terra.
 *
 *  Terra-Terra is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  any later version.
 *
 *  Terra-Terra is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 *
 * HISTORY
 *  * Nov 16, 2002 -- O van Eijk -- initial version for Terra-Terra 
 *  * Jun 8, 2011 -- O van Eijk -- initial version for OWL
 *  * Jan 4, 2014 -- O van Eijk -- Ported back to the new Terra-Terra
 ****/

/****iv* library/TTscripts
 * NAME
 *  TTscripts - General routines
 *
 * DESCRIPTION
 *  Global object that keeps track of all scripts that have been loaded
 *
 * SOURCE
 */
if (typeof(TTscripts) == 'undefined')
	var TTscripts = new Array();
/****/

/****iv* library/TTinitF
 * NAME
 *  TTinitF - List of initialisation functions
 *
 * DESCRIPTION
 *  Global array that holds all initialisation functions which must be called when
 *  the document has loaded.
 *
 * SOURCE
 */
if (typeof(TTinitF) == 'undefined')
	var TTinitF = new Array();
/****/

/****f* library/importScript
 * NAME
 *  importScript - Load a new JavaScript source
 *
 * DESCRIPTION
 *  This function adds a new JavaScript sourcefile to the document
 *
 * INPUTS
 *  * src -- Location of the source file. The path must be fully qualified, either absolute
 *  or relative.
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * SYNOPSIS
 */
function importScript(src, initFunction)
 /****/
{
	if (TTscripts.indexOf(src) >= 0)
		return;

	var sE = document.createElement('script');
	sE.type = 'text/javascript';
	sE.src = src;
	
	document.getElementsByTagName('head')[0].appendChild(sE);
	TTscripts.push(src);
	// TODO Make sure the script is loaded completely and optionally call an
	// init function (new argument).
	// Should work with onReadyStateChange||onLoad, but I can't get it to work :-(
}

/****f* library/languageExtentions
 * NAME
 *  languageExtentions - Load language extentions that are not supported by this browser
 *
 * DESCRIPTION
 *  This function, which is called when the script is loaded, checks for functions and methods
 *  that are used by Terra-Terra scripts but not supported by all browsers. Missing features will be
 *  loaded from the lib/lext directory.
 *
 *  All new additions that are placed in lib/lext must be added to this function with the
 *  appropriate check, the scripts are not autoloaded!
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * TODO
 *  This function is not used since it doesn't seem to work... The code in the script
 *  is not executed when loaded and somehow I can't get the onLoad and onReadyStateChange properties
 *  to work in importScript().
 *  
 *  Insteas, all language extentions are now loaded in the PHP code from Document::enableTT_JS()
 *  and the checks are made in the loaded scripts themselve.
 *
 * SOURCE
 */
function languageExtentions ()
{
	if (!Array.prototype.indexOf) importScript(TT_JS_LIB + '/lext/array_indexof.js');
	if (!HTMLElement.prototype.addClass) importScript(TT_JS_LIB + '/lext/htmlelement_addclass.js');
}
/****/

//Load language extentions - disabled...
//languageExtentions();

importScript(TT_JS_LIB + '/lext/htmlelement_addclass.js');
/****f* library/initFunctions
 * NAME
 *  initFunctions - Execute all initialisation scripts
 *
 * DESCRIPTION
 *  All plugins that are loaded and need initialisation, must call addInitFunction() to
 *  define the initialisation function.
 *  This function will be called after the document is loaded
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * SYNOPSIS
 */
function initFunctions()
 /****/
{
	for (var i = 0; i < TTinitF.length; i++) {
//		alert ("Executing " + TTinitF[i]);
		eval(TTinitF[i]);
	}
}

function addInitFunction(func/*, arguments, ... */)
/****/
{
	func += '(';
	for (var i = 1; i < arguments.length; i++)
		func += (i === 1 ? '' : ',') + arguments[i];
	func += ')';
	
	if (TTinitF.indexOf(func) === -1)
		TTinitF.push(func);
}

/****f* library/getElementsByClass
 * NAME
 *  getElementsByClass - Retrieve all elements that have the given class in the class-attribute
 *
 * DESCRIPTION
 *  This function searches for all elements, or elements of a specified type, either in the
 *  document of in the given rootnode, that contain the given classname in the class attribute.
 *
 * INPUTS
 *  * className -- Name of the class to look for
 *  * tagName -- Only return elements of this type. Defaults to '*'
 *  * rootNode -- Toplevel for the search. Defaults to 'document'
 *
 * RESULT
 *  * Array with all matching HTML elements
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * SYNOPSIS
 */
function getElementsByClass (className, tagName, rootNode)
/****/
{
	var elementList;
	if (!rootNode) {
		rootNode = document;
	}
	if (!tagName) {
		elementList = rootNode.getElementsByTagName("*");
	} else {
		elementList = rootNode.getElementsByTagName(tagName);
	}
	
	var matchingElements = new Array();
	
	className = className.replace(/\-/g, "\\-");
	var regExp = new RegExp("(^|\\s)" + className + "(\\s|$)");

	var elm;
	for (var idx = 0; idx < elementList.length; idx++) {
		elm = elementList[idx];
		if (regExp.test(elm.className)) {
			matchingElements.push(elm);
		}
	}
	return (matchingElements);
}

// FIXME
// The one below is moved to PHP's Document::_getScript() method since after introduction of
// the window container init is called before initFunctions are added.
// This is a workaround, but since JavaScript needs a complete redesign anyway, it's ok for now. 

// Execute all initialisation functions
//window.onload = initFunctions();
