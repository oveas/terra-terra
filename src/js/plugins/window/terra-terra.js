/****m*
 * NAME
 *  terra-terra.js --- Collection of generally useful JavaScript routines
 *
 * DESCRIPTION
 *  
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * COPYRIGHT
 *  (c) 2002-2007 by Oscar van Eijk/Oveas Functionality Provider 
 ***/
/*
 * This module is part of Terra-Terra, the Virtual Operating System
 * http://terra-terra.com
 * ------------------------------------------------------------------------
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2.1 of the License,
 * or any later version.
 * This library is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library; if not, write to the Free Software Foundation,
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * ------------------------------------------------------------------------
 * $Id$ 
 */

/****************************************************************************** 
 * List of global variables                                                   * 
 ******************************************************************************/
imgShade   = new Image ();
imgRestore = new Image ();

knownObjects = new Array ();
var modSizes = false;	// Let dynamic code only set sizes on Init and Resize
var curDataDest;	// Where to copy SC interface data to

/****************************************************************************** 
 * Form Functions                                                             * 
 ******************************************************************************/

function GetFormByName(formName) {
/*
 * Given a Formname, return it's object.
 */
	var formObj;
	// Netscape6
	if (nsVersion >= 60) {
		formObj = document.getElementById(formName)
	} else if (ieVersion >= 40 || opVersion >= 60){
		formObj = document.forms[formName];
	} else { // if (nsVersion >= 40) {
		formObj = document.formName;
	}
	return formObj;
}

function EmptyInput (fieldObj, defValue) {
/*
 * This function is called with a text input field and its default
 * value as arguments when the field if focussed upon.
 * If the current value eqs the default value, the value is removed.
 */
	if (fieldObj.value == defValue) {
		fieldObj.value = '';
	}
}

function SubmitForm (formObj, areaName) {
	var newHTML;
	var elmIdx;
	var optIdx;
	var elmObj;
	var optObj;

	curDataDest = formObj.elements['TTdestination_area'].value;
	if (true) {
		return true;
	} else {

		/* **********
		 * I think the rest of this function is obsolete (already :-/),
		 * since I can put the 'target' attribute in the original form
		 * as wel; it has to be written by the library function anyway.
		 * This function can stay here a little longer for reference...
		 * OvE, 20040816
		 ************ */

		newHTML = '<form method="POST" id="TT_InterfaceForm" '
			+ 'action="' + GetConfig ('tttopurl') + '/kernel/TTinterface_sc.php" ';
		if (formObj.encoding) {
			newHTML = newHTML + 'enctype="' + formObj.encoding + ' ' ;
		}
		newHTML = newHTML + 'target="TT_sc_interface">' + "\n";

		for (elmIdx = 0; elmIdx < formObj.elements.length; elmIdx++) {
			elmObj = formObj.elements[elmIdx];
			switch (elmObj.type.toLowerCase()) {
				case 'button' :
				case 'fileupload' :
				case 'hidden':
				case 'password' :
				case 'reset' :
				case 'submit' :
				case 'text':
				default:
					newHTML = newHTML
					+	  '<input'
					+	  ' name="' + elmObj.name + '"'
					+	  ' type="' + elmObj.type + '"'
					+	  ' value="' + elmObj.value + '"'
					+	  ">\n";
					break;
				case 'radio' :
				case 'checkbox' :
					newHTML = newHTML
					+	  '<input'
					+	  ' name="' + elmObj.name + '"'
					+	  ' type="' + elmObj.type + '"'
					+	  ' value="' + elmObj.value + '"';
					if (elmObj.type) {
						newHTML = newHTML + ' checked';
					}
					newHTML = newHTML + ">\n";
					break;
				case 'select' :
					newHTML = newHTML
					+	  '<select'
					+	  ' name="' + elmObj.name + '">'
					for (optIdx = 0; optIdx < elmObj.length; optIdx++) {
						optObj = elmObj.options[optIdx];
						newHTML = newHTML +
						+	  '<option'
						+	  ' value="' + optObj.value + '"';
						if (optObj.selected) {
							newHTML = newHTML + ' selected'
						}
						newHTML = newHTML + '>'
						+	  optObj.text
						+	  '</option>';
					}
					newHTML = newHTML + "</select>\n";
					break;
				case 'textarea' :
					newHTML = newHTML
					+	  '<textarea'
					+	  ' name="' + elmObj.name + '">'
					+	  elmObj.value;
					newHTML = newHTML + "</textarea>\n";
					break;

			}
		}
		newHTML = newHTML + '<input type="hidden" '
		+		      'name="TT_original_form_action" '
		+		      'value="' + formObj.action + '">'
		+		      '<input type="hidden" '
		+		      'name="TT_original_workarea" '
		+		      'value="' + areaName + '">'
		+		    '</form>';
		RewriteAreaContents ('TT_cs_interface', newHTML);
		formObj = GetObjectByID ('TT_InterfaceForm');
		formObj.submit();

		return false;
	}
}

function SC_Interface (myData) {
	var destArea = 'TT_wa' + curDataDest + '_body';
	if (ttDebug) {
		alert ('SC_Interface: moving data to ' + destArea );
	}
//	scInterface = GetObjectByID ('TT_sc_interface');
//	RewriteAreaContents (destArea, scInterface.innerHTML);
	RewriteAreaContents (destArea, myData);
	curDataDest = undefined;
}

/****************************************************************************** 
 * Checking Functions                                                         * 
 ******************************************************************************/

function ValidateFileName (fileName) {
/*
 * Check if a given filename is valid
 */
	if (fileName == '') { return false; }
	ivChars = /[\s!\$\\\/]/;
	if (ivChars.exec(fileName)) {  return false; }
	return true;
}

function ValidateDate (myDate) {
/*
 * Validate a date given in the format [d]d-[m]m-[yy]yy
 * and return in the format dd-mm-yyyy if it's a valid date.
 * If not, return an empty string.
 * ##### TODO #####
 * Make the format configurable
 */
	myDateArray = myDate.split('-');
	if (myDateArray.length != 3) {
		return ('');
	}

	// Check the year
	// If it's given with 2 digits; years below 50 are
	// considered 20xx, all others in the 1900's.
	// Wouldn't it be better to force 4 digits??
	//
	myYear  = myDateArray[2];
	if (myYear.length == 2) {
		if (parseInt(myYear) < 50) {
			myYear = '20' + myYear;
		} else {
			myYear = '19' + myYear;
		}
	} else if (myYear.length != 4) {
		return ('');
	}
	myYear = parseInt(myYear);

	// Check the month
	//
	myMonth = parseInt(myDateArray[1]);
	if (myMonth < 0 || myMonth > 12) {
		return ('');
	}

	// Check the day, including leap-years.
	//
	myDay   = parseInt(myDateArray[0]);

	if (myDay < 0) {
		return ('');
	}
	
	if (myMonth == 1 || myMonth == 3 || myMonth == 5 || myMonth == 7 ||
		myMonth == 8 || myMonth == 10 || myMonth == 12) {
		if (myDay > 31) {
			return ('');
		}
	} else if (myMonth == 4 || myMonth == 6 || myMonth == 9 || myMonth == 11) {
		if (myDay > 30) {
			return ('');
		}
	} else {	// Month is 2...
		if (((myYear % 4) == 0 && (myYear % 100) != 0) || (myYear % 400) == 0) {
			Highest = 29;
		} else {
			Highest = 28;
		}
		if (myDay > Highest) {
			return ('');
		}
	}
	if (myDay < 10) {
		myDay = '0' + myDay;
	}
	if (myMonth < 10) {
		myMonth = '0' + myMonth;
	}
	return(myDay + '-' + myMonth + '-' + myYear);
}

/****************************************************************************** 
 * Cursor and Positioning Functions                                           * 
 ******************************************************************************/
/*
 * ##### TODO #####
 * Make them work :-/
 */
function GetCursorPos (myObj) {
/*
 * Get the cursor position in a text field
 * Taken from : http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
 */
	if (myObj.createTextRange) {
		myObj.caretPos = document.selection.createRange().duplicate();
	}
}
function InsertAtCursorPos (fieldObj, newText) {
/*
 * Insert text in the text field at the cursor position
 */
	if (fieldObj.createTextRange && fieldObj.caretPos) {
		var caretPos = fieldObj.caretPos;
		caretPos.text = caretPos.text.charAt (caretPos.text.length - 1) == ' ' ?
			newText + ' ' : newText;
	} else {
		fieldObj.value += newText;
	}
}


/****************************************************************************** 
 * Object Functions                                                           * 
 ******************************************************************************/

function GetMouseButton (e) {
/*
 * Given an event, return the mousebutton, of 0 if it was not a mouse event
 * Mouse button can be 1 (left), 2 (middle) or 3 (right)
 * On unsupported browserd, -1 is returned.
 */
	var Btn;

	if (typeof (e.which) != 'undefined') {
		// 'which' is always numbered 1, 2, 3
		Btn = e.which;
	} else if (typeof (e.button) != 'undefined') {
		if (ieVersion > 0) {
			// IE numbers the buttons 1, 4, 2
			if (e.button == 4) { // Middle
				Btn = 2;
			} else if (e.button == 2) { // Right
				Btn = 3;
			} else { // Left is ok
				Btn = e.button;
			}
		} else {
			// Standard numbering: 0, 1, 2
			Btn = (e.button + 1);
		}
	} else {
		Btn = -1;
	}
	if (Btn > 3) { // Not a mouse button
		Btn = 0;
	}
	return (Btn);
}

function GetKnownObject (objName) {
/*
 * See if a requested object has been located before.
 */
	var arrayPointer;

	for (arrayPointer = 0; arrayPointer < knownObjects.length; arrayPointer++) {
		if (knownObjects[arrayPointer][0] == objName) {
			return knownObjects[arrayPointer][1];
		}
	}
	return null;
}

function GetObjectByID(objName, currDocument) {
/*
 * Given an Objectname, return it's object description.
 */
	var objPointer, layerIndex;

	if (!currDocument) {
		knownObject = GetKnownObject (objName);
		if (knownObject != null) {
			if (ttDebug) {
				alert ('Object ['+objName+'] was already known');
			}
			return knownObject;
		}
		if (ttDebug) {
			alert ('Object ['+objName+'] was not known yet');
		}
		currDocument = document;
	}

	if ((objPointer = objName.indexOf('?')) > 0 && parent.frames.length) {
		currDocument = parent.frames[currDocument.substring(objPointer+1)].document;
		objName = objName.substring(0, objPointer);
	}

	if (ieVersion > 0) {
		htmlObj = currDocument.all[objName];
	} else if (nsVersion > 40) {
		htmlObj = currDocument.getElementById(objName)
	} else {
		for (layerIndex = 0; !htmlObj && currDocument.layers && layerIndex < currDocument.layers.length; layerIndex++) {
			htmlObj = currDocument.layers[layerIndex][objName];
		}
	}

	// Nested?
	for (layerIndex = 0; !htmlObj && currDocument.layers  && layerIndex < currDocument.layers.length; layerIndex++) {
		htmlObj = GetObjectByID (objName, currDocument.layers[layerIndex].document);
	}
	if (ttDebug) {
		alert ('Object ['+objName+'] = ['+htmlObj+']');
	}

	knownObjects.push(new Array(objName, htmlObj));
	return htmlObj;
}

/****************************************************************************** 
 * Layer Functions                                                            * 
 ******************************************************************************/

function ShowHideLayer (layerName, visibleState) {
/*
 * Given a layerName and a requested state ('show' or 'hidden'), show
 * or hide a layer.
 * In stead of 'show' and 'hide', '1' and '0' are also accepted.
 */
	var layerState, layerObj;

//	if ((opVersion > 0 && opVersion <= 60) ||
//	    (nsVersion > 0 && nsVersion <= 40)) {
		if ((layerObj = GetObjectByID (layerName)) != null) {
			if (layerObj.style) {
				layerObj.style.visibility = (visibleState == 'show' || visibleState == '1')
						? 'visible'
						: (visibleState == 'hide' || visibleState == '0')
							? 'hidden' : visibleState;
			} else {
				layerObj.visibility = (visibleState == 'show' || visibleState == '1')
						? 'show'
						: (visibleState == 'hide' || visibleState == '0')
							? 'hide' : visibleState;
			}
		}
/*	} else {
		if ((layerObj = GetObjectByID (layerName)) != null) {
			if (layerObj.style) {
				layerObj.style.display = (visibleState == 'show' || visibleState == '1')
						? ''
						: (visibleState == 'hide' || visibleState == '0')
							? 'none' : visibleState;
			} else {
				layerObj.display = (visibleState == 'show' || visibleState == '1')
						? ''
						: (visibleState == 'hide' || visibleState == '0')
							? 'none' : visibleState;
			}
		}
	}
*/
}

function ToggleLayers () {
/*
 * This function toggles the visibility of a set of layers. It takes a list of
 * of arguments, all in the format 'Layername:state'; the name of the layer,
 * followed by a colon and the requested visibility state, which can be
 * 'show (or '1') or 'hide' (or '0').
 *  e.g.: ToggleLayers ('Layer1:hide', 'Layer2:show', 'Layer3:hide', ...)
 */
	var argIndex, myArgs = ToggleLayers.arguments;

	for (argIndex = 0; argIndex < (myArgs.length); argIndex++) {
		Argument = myArgs[argIndex].split(':');
		ShowHideLayer (Argument[0], Argument[1]);
	}
}

function SetLayerSource (LayerName, Source) {
/*
 * Load an external document in the specified layer
 */
	if ((layerObj = GetObjectByID (LayerName)) != null) {
		if (nsVersion == 40) {
			LayerWidth = window.innerWidth; // - 200;
			layerObj.load(Source, LayerWidth);
		} else {
			layerObj.src = Source;
		}
	}
}

function SetLayerValues () {
/*
 * Given layername and a(n) (list of) argument(s), change the positioning
 * values of the layer.
 * An artgument is in the format: 'characteristic=value', where the
 * characteristic nan be abbreviated to 1 character.
 * Example:
 *   SetLayerValues('Layer1', 'top=50', size=150x200')
 *  This positions the layer named 'Layer1' at 50px from the top, and makes is
 * 150 pixels wide.
 */
	var argIndex, layerObj, styleObj, dimArray, myArgs = SetLayerValues.arguments;
	var addPix = document.childNodes ? 'px' : '';

	if ((layerObj = GetObjectByID (myArgs[0])) == null) {
		return;
	}
	if (layerObj.style) {
		styleObj = layerObj.style
	} else {
		styleObj = layerObj;
	}
	for (argIndex = 1; argIndex < (myArgs.length); argIndex++) {
//alert('Setlayer '+myArgs[0]+ ': '+myArgs[argIndex]);
		Argument = myArgs[argIndex].split('=');
		switch (Argument[0].substring(0,1)) {
			case 't' : // Top
				styleObj.top = Argument[1] + addPix;
				break;
			case 'l' : // Left
				styleObj.left = Argument[1] + addPix;
				break;
			case 'r' : // Right
				styleObj.right = Argument[1] + addPix;
				break;
			case 'b' : // Bottom
				styleObj.bottom = Argument[1] + addPix;
				break;
			case 'h' : // Height
				styleObj.height = Argument[1] + addPix;
				break;
			case 'w' : // Width
				styleObj.width = Argument[1] + addPix;
				break;
			case 's' : // Sizes (WxH)
				dimArray = Argument[1].split('x');
				if (layerObj.resizeTo) {
					layerObj.resizeTo (dimArray[0], dimArray[1])
				} else {
					styleObj.width  = dimArray[0] + addPix;
					styleObj.height = dimArray[1] + addPix;
				}
				break;
			case 'z' : // Z-index
				styleObj.zIndex = Argument[1];
				break;
		}
	}
}


/****************************************************************************** 
 * Browser detection and initial configuration                                * 
 ******************************************************************************/

function GetBrowser () {
/*
 * Find out what browser and version is being used on which client
 */

	if (window.opera) {
		// Opera browsers
		//
		if (document.createComment) {
			// Opera v7+
			opVersion = 70;
		} else {
			// Opera -v6
			opVersion = 60;
		}
	} else if (document.getElementById) {
		// Internet Explorer 5+ or Netscape 6+
		//
		if (document.all) {
			// Internet Explorer 5+
			//
//			if (!document.mimeType) {
				// Internet Explorer on Macintosh
//				clientPlatform = 'MAC';
//			}
			if (document.createComment) {
				// Internet Explorer 6+
				 ieVersion = 60;
			} else if (document.fireEvent) {
				// Internet Explorer 5.5
				ieVersion = 55;
			} else {
				// Internet Explorer 5.0
				ieVersion = 50;
			}
		} else {
			// Netscape v6+
			nsVersion = 60;
		}
	} else if (document.all) {
		// Internet Explorer 4
		ieVersion = 40;
	} else if (document.layers) {
		// Netscape 4
		nsVersion = 40;
	}
}


function NS4_reload () {
/*
 * Reload the page after a resize when measurements are incorrect (NS4 bug)
 */
	if (savedIW != window.innerWidth || savedIH != window.innerHeight) {
		location.reload();
	}
}

function WorkSpaceDimensions () {
/*
 * Get the dimensions of the current browser window. This function
 * should be called when the TT workspace is loaded (done by the JavaScript
 * loader), and on a resize.
 */
	if (ieVersion == 40) {
		wsWidth  = window.width;
		wsHeight = window.height;
	} else if (ieVersion >= 60) {
		wsWidth  = document.documentElement.clientWidth;
		wsHeight = document.documentElement.clientHeight;
	} else {
		wsWidth = window.innerWidth;
		wsHeight = window.innerHeight;
	}
}

function SetWorkSpaceProperties () {
/*
 * Set the background layer and -image to the correct size
 * Put all special layers in place
 */
	
	SetLayerValues ('TT_background', 'width='+wsWidth, 'height='+wsHeight);
	bgImage = GetObjectByID('TT_bgImage');
	bgImage.width = wsWidth;
	bgImage.height = wsHeight

	// The TT info- layer
	//
	if ((infoLayer = GetObjectByID('TT_wa999_top')) != null) {
		SetLayerValues ('TT_wa999_top', 'left='+(infoLayer.style.width - bgImage.width),
			'left='+(infoLayer.style.height - bgImage.height))
	}
}

function GetConfig (elmName) {
/*
 * This function scans the HTML page for an object with the given name
 * to return its value.
 * It is used to read variables from PHP that should be constants in PHP.
 * They are given as hidden input elements in an invisible Config layer.
 */
	if ((cfgObj = GetObjectByID (elmName)) == null) {
		return ('');
	} else {
		return (cfgObj.value);
	}
}

function PreloadImages () {
	imgShade.src   = GetConfig ('themeurl') + '/' + layoutScheme + '/icons/shade.png';
	imgRestore.src = GetConfig ('themeurl') + '/' + layoutScheme + '/icons/restore.png';
}

function InitWorkSpace () {
/*
 * This function is called using the BODY's onload() event when the
 * TT workspace is initially loaded.
 */
	modSizes = true;
	// Find the size of our current browsing window.
	WorkSpaceDimensions ();
	// and set the background accordingly
	SetWorkSpaceProperties ();
	ShowHideLayer('TT_background', 'show');
	ShowHideLayer('TT_wa999_top', 'show');

	// Constants
	tbHeight	= GetConfig ('titlebarheight');
	bbHeight	= GetConfig ('bottombarheight');
	wsTop		= GetConfig ('workspacetop');
	wsLeft		= GetConfig ('workspaceleft');
	waBorder	= GetConfig ('areaborderwidth');

	layoutScheme	= GetConfig('theme');

	dockGrid	= parseInt(GetConfig('docking_grid'), 10);

	// Prompts; languange specific
	promptMaxiWA	= GetConfig ('prompt_maxiwa');
	promptResiWA	= GetConfig ('prompt_resiwa');
	promptShadWA	= GetConfig ('prompt_shadwa');
	promptReviWA	= GetConfig ('prompt_reviwa');
	
	PreloadImages ();
	if (execDynCode) {
		DynamicJavaScriptTTCode();
	}
	modSizes = false;
	ShowHideLayer('TT_curtain', 'hide');
}

function DestroyWorkSpace () {
/*
 * This function is called using the BODY's onunload() event when the
 * TT workspace is unloaded.
 */
	if (anyChanges && ((cfgForm = GetObjectByID('TTConfigForm')) != null)) {
		if (confirm (GetConfig ('promptSaveChWa'))) {
			cfgForm.TTsave_area.checked = true;
			cfgForm.submit();
			return false;
		}
	}
}

function ResizeWorkSpace () {
/*
 * After a resize, set the new values of the workspace and make sure the 
 * background layer matches the new size
 */
	modSizes = true;
	WorkSpaceDimensions ();
	SetWorkSpaceProperties ();
	if (execDynCode) {
		DynamicJavaScriptTTCode();
	}
	modSizes = false;
}

/*
 * Coded below should always execute.
 */
var ttDebug = false; // For the patient and the really bored....

/*
 * Browser version (2 digits; v4+ = '40').
 * Version nr '0' means 'Not this browser'
 */
var nsVersion = 0; // Netscape
var ieVersion = 0; // Internet Explorer
var opVersion = 0; // Opera
var KQVersion = 0; // Konqueror; not yet implemented

if (navigator.platform.indexOf('Win') != -1) clientPlatform = 'W32';
if (navigator.platform.indexOf('Mac') != -1) clientPlatform = 'MAC';
if (navigator.platform.indexOf('Lin') != -1) clientPlatform = 'LNX';

GetBrowser();

if (ttDebug) {
	alert ('NS: ['+nsVersion+'], IE: ['+ieVersion+'], OP: ['+opVersion+']');
	alert ('OS: ['+navigator.platform+']');
}

if (nsVersion == 40) {
	// Workaround NS4's resize bug
	savedIW = window.innerWidth;
	savedIH = window.innerHeight;
	window.onresize = NS4_reload;
}

