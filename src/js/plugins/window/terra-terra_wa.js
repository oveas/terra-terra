/****m*
 * NAME
 *  terra-terra_wa.js --- JavaScript Functions to change (layer-)values of the TT workareas being displayed.
 *
 * DESCRIPTION
 *  
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * COPYRIGHT
 *  (c) 2003-2007 by Oscar van Eijk/Oveas Functionality Provider 
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
 */

/*
 * This arrays contains the orininal values for a workarea that
 * has been maximized:
 *   0:	The Area ID
 *   1:	Original top
 *   2:	Original left
 *   3:	Original width
 *   4:	Original height
 */
var waOrigDimensions = new Array (0, 0, 0, 0, 0);
var nowOnTop = -1;		// Which area is now in the foreground?
var nowActive = -1;		// Which area is now active.
				// This is not the same as OnTop, to prevent
				// confusion with AlwaysOnTop areas

function PopUp (areaName) {
/*
 * Popup a hidden work area and bring it to the front.
 * This function is called by the user by clicking an HTML link.
 * The function's argument is the area's name, not the number!
 */
	var areaNr;
	areaNr = GetWAIDByName (areaName);
	WAVisibility (areaNr, 'd');
	PlaceOnTop (areaNr);
}

function OpenPage (areaName, pageURL, areaData) {
	areaNr = GetWAIDByName (areaName);
	myTarget = GetObjectByID ('TT_wa' + areaNr + '_body');

//	areaData = unescape(areaData.replace(/\+/g, " "));

	newURL = pageURL;
	if (newURL.search(/\?/) > 0) {
		newURL = newURL + '&' + areaData;
	} else {
		newURL = newURL + '?' + areaData;
	}
	myTarget.src = newURL;
}


function RewriteAreaContents (areaName, newContent) {
	areaObj = GetObjectByID (areaName);
	if (typeof(areaObj.innerHTML) != 'undefined') {
//	if (true) {
		// Used by the IE series, Konqueror, Opera 7+ and Gecko browsers
		areaObj.innerHTML = newContent;
	} else {
		if (areaObj.document && areaObj.document != window.document ) {
			// Used by layers browsers
			areaObj.document.open();
			areaObj.document.write(newContent);
			areaObj.document.close();
		} else {
			alert ('You are using a browser that is currently unsupported by Terra-Terra. We are very sorry for this --- we try to find a solution asap.');
/*
			if( window.frames && window.frames.length && window.frames['areaName'] ) {
				//used by browsers like Opera 6-
				//if we attempt to rewrite the iframe content before
				//it has loaded we will only produce errors
				areaObj = window.frames['areaName'].window;
				areaObj.document.open();
				areaObj.document.write(newContent);
				areaObj.document.close();
			}
*/
		}
	}
}

function Interface (areaName, msgText) {

/*
 * ##### TODO #####
 * - Create 2 DIVs; new messages appear in the upper DIV, older ones are moved down.
 *   The upper DIV has a different fontsize
 * - Add a time value (in PHP's TTsignal() already?)
 * - Create a (configurable) timer that can make the console auto disappear.
 */
	areaNr = GetWAIDByName (areaName);
	myTarget = GetObjectByID ('TT_wa' + areaNr + '_body');

	if (areaName == 'TTconsole') {
		myTarget.innerHTML = unescape(msgText.replace(/\+/g, " ")) + '<br />' + myTarget.innerHTML;
	} else {
//		myTarget.innerHTML = myTarget.innerHTML + msgText + '<br />';
		myTarget.src = myTarget.src + '&interface_msg=' + msgText;
	}
//	myTarget.document.scrollBy(0,999);
	PopUp(areaName);
}

function GetWAIDByName (areaName) {
/*
 * Get the unique WorkAreaID for a given name. The name is known in the database
 * and used by the software. PHP passes the name to client via the Config layer,
 * translating it to the ID that's used by JavaScript.
 */
	var waCnfName;

	WorkAreaCnfName = 'waname_' + areaName;
	return (GetConfig (WorkAreaCnfName));
}

function GetApplicationsList () {
/*
 * Get a list of all active applications. That's a comma-seperated
 * list, written by PHP to the Config layer.
 */
	var applicList;
	var applicArray;

	applicList = GetConfig ('applications_list');
	applicArray = applicList.split(',');
	return (applicArray);
}

function GetWorkAreaList (applicName) {
/*
 * Get a list of all active workareas by application. That's a comma-seperated
 * list, written by PHP to the Config layer.
 */
	var waList;
	var waArray;

	waList = GetConfig ('workarealist_' + applicName);
	waArray = waList.split(',');
	return (waArray);
}

function PlaceOnTop (areaNr) {
/*
 * 
 */
	layerObj = GetObjectByID ('TT_wa' + areaNr + '_top');
	if (nowActive != 0) {
		if ((fieldTitleText = GetObjectByID ('TT_wa_ttfld_' + nowActive)) != null) {
			fieldTitleText.className = 'wa_titlebar';
		}
	}
	if ((fieldTitleText = GetObjectByID ('TT_wa_ttfld_' + areaNr)) != null) {
		fieldTitleText.className = 'wa_active_titlebar';
	}
	nowActive = areaNr;

	if (layerObj.style.zIndex == 99) {
		// Ignore 'Always on Top' areas
		return;
	}
	if (nowOnTop >= 0) {
		SetLayerValues ('TT_wa' + nowOnTop + '_top', 'z=22');
		SetLayerValues ('TT_wa' + nowOnTop + '_border', 'z=21');
	}
	SetLayerValues ('TT_wa' + areaNr + '_top', 'z=32');
	SetLayerValues ('TT_wa' + areaNr + '_border', 'z=31');
	nowOnTop = areaNr;
}

function ApplicAreaForeGround (applicArray) {
	var waList;
	var listIndex;
	var layerSet;
	var layerObj;
	var applicID;
	var isApplic;

	isApplic = true;

	applicID = applicArray.options[applicArray.selectedIndex].value;
	applicArray.selectedIndex = 0;
	applicArray.blur();

	if (applicID.substr(0,2) == "WA") {
		applicID = applicID.substr(2);
		isApplic = false;
	}

	if (isNaN(parseInt(applicID, 10) || parseInt(applicID, 10) < 0)) {
		return;
	}

	if (isApplic) {
		waList = GetWorkAreaList (applicID);
		for (listIndex = 0; listIndex < (waList.length); listIndex++) {
			layerSet = waList[listIndex];
			layerObj = GetObjectByID ('TT_wa' + layerSet + '_top');
			if (layerObj.style.visibility == 'hidden') {
				WAVisibility (layerSet, 'o');
			}
			PlaceOnTop (layerSet);
		}
	} else {
		layerObj = GetObjectByID ('TT_wa' + applicID + '_top');
		if (layerObj.style.visibility == 'hidden') {
			WAVisibility (applicID, 'o');
		}
		PlaceOnTop (applicID);
	}
}

function WAVisibility (layerSet, newState) {
/*
 * Change the WorkArea visibility
 * layerSet: The number that appears in each workarea after "wa";
 *	eg: TT_wa2_body, the layerSet here is 2
 * newState can be:
 *	'm'	(Maxi)	Maximize/Restore a layer
 *	'o'	(Open)	Open a layer
 *	'c'	(Close)	Close a layer; remove body and titlebar
 *	's'	(Shade)	Shade  or Unshade a layer; hide/show it's body but keep the titlebar
 *			The current state is taken dynamically
 *	'd'	(Display) Whatever the current status is, display the layer
 *			unshaded.
 */
	var layerState;
	var layerLevel;
	var layerObj;
		
	switch (newState) {
		case 'm' :
			WAMiniMaxi (layerSet);
			return;
		case 'o' :
			layerLevel = 'top'
			layerState = 'show';
			break;
		case 'c' :
			layerLevel = 'top'
			layerState = 'hide';
			break;
		case 's' :
			layerLevel = 'body';
			break;
		case 'd' :
			WAVisibility (layerSet, 'o');
			layerObj = GetObjectByID ('TT_wa' + (layerSet + '_body'));
			if (layerObj.style.visibility == 'hidden') {
				WAVisibility (layerSet, 's');
			}
			return;
			break;
	}
	layerObj = GetObjectByID ('TT_wa' + layerSet + '_' + layerLevel);

	if (layerLevel == 'body') {
		// Get current stats, change the icon and the visibility
		//
		shadeIcon = GetObjectByID ('ShadeIcon_' + layerSet);
		shadeLink = GetObjectByID ('ShadeLink_' + layerSet);
		layerTop = GetObjectByID ('TT_wa' + layerSet + '_top');
		if (ttDebug) { alert('Layerstate is now: ' + layerObj.style.visibility); }
		if (layerObj.style.visibility == 'hidden') {
			layerState = 'show';
			shadeLink.title = promptShadWA;
			shadeIcon.src = imgShade.src;
		} else {
			layerState = 'hide';
			shadeLink.title = promptReviWA;
			shadeIcon.src = imgRestore.src;
		}
		ShowHideLayer ('TT_wa' + layerSet + '_body', layerState);
		ShowHideLayer ('TT_wa' + layerSet + '_bottom', layerState);
	} else {
		ShowHideLayer ('TT_wa' + layerSet + '_top', layerState);
		// When a closed window is reopened, show the body and bottome
		// layers only when the previous stats was not Shaded
		// This is checked using the icon.
		//
		shadeIcon = GetObjectByID ('ShadeIcon_' + layerSet);
		if (shadeIcon == null || shadeIcon.src == imgShade.src) {
			// Okay, was not shaded
			ShowHideLayer ('TT_wa' + layerSet + '_body', layerState);
			ShowHideLayer ('TT_wa' + layerSet + '_bottom', layerState);
		}
	}
}

function WAMiniMaxi (layerSet) {
/*
 * Maximize a workarea, or restore a maximized area to it's original values.
 * Only 1 workarea can be maximized simultaneously, so if a second is
 * max'd, the currently max'd workarea is restored first.
 */
	if (waOrigDimensions[0] != 0) {
		SetLayerValues ('TT_wa' + waOrigDimensions[0] + '_top',
			'top=' + waOrigDimensions[1],
			'left=' + waOrigDimensions[2],
			'width=' + waOrigDimensions[3],
			'height=' + waOrigDimensions[4]
		);
		SetLayerValues ('TT_wa' + waOrigDimensions[0] + '_body',
			'width=' + waOrigDimensions[3],
			'height=' + (waOrigDimensions[4] - bbHeight - tbHeight)
		);
		SetLayerValues ('TT_wa' + waOrigDimensions[0] + '_bottom',
			'top=' + (waOrigDimensions[4] - bbHeight),
			'width=' + waOrigDimensions[3]
		);

		linkObj = GetObjectByID ('MiniMaxiLink_' + waOrigDimensions[0]);
		linkObj.title = promptMaxiWA;

		// This layer is maximized already. Now it's
		// retured, just return.
		//
		if (waOrigDimensions[0] == layerSet) {
			waOrigDimensions[0] = 0;
			return;
		}

		waOrigDimensions[0] = 0;
	}

	// Store the current values
	//
	maxiObj = GetObjectByID ('TT_wa' + layerSet + '_top');
	waOrigDimensions[0] = layerSet;
	waOrigDimensions[1] = parseInt(maxiObj.style.top, 10);
	waOrigDimensions[2] = parseInt(maxiObj.style.left, 10);
	waOrigDimensions[3] = parseInt(maxiObj.style.width, 10);
	waOrigDimensions[4] = parseInt(maxiObj.style.height, 10);

	if (ttDebug) {
		alert ('waOrigDimensions() now set to ['+waOrigDimensions[0]+'] '+
			'['+waOrigDimensions[1]+'] '+
			'['+waOrigDimensions[2]+'] '+
			'['+waOrigDimensions[3]+'] '+
			'['+waOrigDimensions[4]+']');
	}
	SetLayerValues ('TT_wa' + layerSet + '_top',
		'top=' + wsTop,
		'left=' + wsLeft,
		'width=' + (wsWidth - wsLeft),
		'height=' + (wsHeight - wsTop)
	);
	SetLayerValues ('TT_wa' + layerSet + '_body',
		'width=' + wsWidth,
		'height=' + (wsHeight - wsTop - bbHeight - tbHeight)
	);
	SetLayerValues ('TT_wa' + layerSet + '_bottom',
		'top=' + (wsHeight - wsTop - bbHeight),
		'width=' + wsWidth
	);
	mmLink = GetObjectByID ('MiniMaxiLink_' + layerSet);
	mmLink.title = promptResiWA;
}

function GetWorkAreaAttributes (cfgForm) {
/*
 * Get the Work Area settings as they where changed by the user,
 * so they can be sent back to the server.
 */
	var waList, appList;
	var thisAreaID, thisArea, thisBody;
	var thisXpos, thisYpos, thisWidth, thisHeight, thisVisibility;
	var hCenter, vCenter;

	if (cfgForm.TTsave_area.checked == false) {
		return;
	}

	anyChanges = false;
	appList = GetApplicationsList ();
	waList = new Array();

	hCenter = wsWidth / 2;
	vCenter = wsHeight / 2;

	while (thisApplicID = appList.shift()) {
		// Get all applications
		//
		waList = waList.concat(GetWorkAreaList (thisApplicID));
	}

	while (thisAreaID = waList.shift()) {
		// Get all workareas per application and their dimensions
		//
		thisArea = GetObjectByID ('TT_wa' + thisAreaID + '_top');
		thisBody = GetObjectByID ('TT_wa' + thisAreaID + '_body');
		thisXpos   = parseInt(thisArea.style.left, 10);
		thisYpos   = parseInt(thisArea.style.top, 10);
		thisWidth  = parseInt(thisBody.style.width, 10);
		thisHeight = parseInt(thisBody.style.height, 10);

		thishCenter = thisXpos + (thisWidth / 2);
		thisvCenter = thisYpos + (thisHeight / 2);

		if (thishCenter > (hCenter + dockGrid)) {
			thishAlign = 'r';
			thisXpos = wsWidth - (thisXpos + thisWidth);
		} else if (thishCenter < (hCenter - dockGrid)) {
			thishAlign = 'l';
		} else {
			thishAlign = 'c';
		}

		if (thisvCenter > (vCenter + dockGrid)) {
			thisvAlign = 'b';
			thisYpos = wsHeight - (thisYpos + thisHeight);
		} else if (thisvCenter < (vCenter - dockGrid)) {
			thisvAlign = 't';
		} else {
			thisvAlign = 'c';
		}

		thisVisibility = ((thisArea.style.visibility == 'hidden') ? 0 : 2);
		if (waOrigDimensions[0] == thisAreaID) {
			thisVisibility = 6;		// Maximized
		}
		if (thisVisibility > 0) {
			if (thisBody.style.visibility == 'hidden') {
				thisVisibility = 1;	// shaded
			}
		}
		cfgForm.TTarea_data.value += "#" + parseInt(thisAreaID, 10) + ":"
			+ thisXpos + ":"
			+ thisYpos + ":"
			+ thishAlign + ":"
			+ thisvAlign + ":"
			+ thisWidth + ":"
			+ thisHeight + ":"
			+ thisVisibility;
	}
}

function ReloadArea (layerSet, newMode) {
/*
 * Reload a given workaarea, or the whole workspace if the workarea
 * was -1.
 * Application managers can reload certain workareas in 'Write' mode;
 * that is requested with a 'w' as second parameter. By default it's 'r'.
 */
	var reloadMode = '';
	if (layerSet == -1) {
		// Reload the whole workspace
		// The workspace (including embedded areas)
		// can *never* be loaded in write mode.
		//
		document.location.replace(document.location.href);
	} else {
		layerObj = GetObjectByID ('TT_wa' + layerSet + '_body');
//		layerObj.innerHTML = layerObj.innerHTML;
//alert ('Reloading [' + layerObj + ']');
//alert ('Source [' + layerObj.src + ']');
//alert ('HTML [' + layerObj.innerHTML + ']');
		if (newMode == 'w') {
			reloadMode = '&TTreload_mode=w';
		}
		if (layerObj.src.indexOf('TTreload_mode') != -1) {
			newSrc = layerObj.src.replace(/&TTreload_mode=./g, '');
		} else {
			newSrc = layerObj.src;
		}
		layerObj.src = newSrc + reloadMode;
	}
}

/****************************************************************************** 
 * All functions below are related to mouse- movents:
 *  the drag- and resize functions
 ******************************************************************************/


var changeWA      = null;
var changeXoffset = -1;
var changeYoffset = -1;
var changeXpos    = -1;
var changeYpos    = -1;
var changeXbody   = -1;
var changeYbody   = -1;
var changeYbottom = -1;
var changeBwid    = -1;

var anyChanges    = false;
var currentActionType = null;

function SnapToGrid () {
	var doSnap = false;
	var areaObj;

	if (ttDebug) {
		alert ("Grid size: ["+dockGrid+"]");
	}

	if (dockGrid <= 0) {
		return;
	}

	areaObj = GetObjectByID ('TT_wa' + changeWA + '_top');
	if (areaObj.style) {
		areaObj = areaObj.style;
	}

	thisWidth  = parseInt(areaObj.width, 10);
	thisHeight = parseInt(areaObj.height, 10);
	thisXpos   = parseInt(areaObj.left, 10);
	thisYpos   = parseInt(areaObj.top, 10);

	if (thisXpos < dockGrid) {
		thisXpos = 0;
		doSnap = true;
	} else {
		if ((thisXpos + thisWidth + dockGrid ) >= wsWidth ) {
			thisXpos = wsWidth - thisWidth;
			doSnap = true;
		}
	}
	if (thisYpos < dockGrid) { 
		thisYpos = 0;
		doSnap = true;
	} else {
		if ((thisYpos + thisHeight + dockGrid ) >= wsHeight ) {
			thisYpos = wsHeight - thisHeight;
			doSnap = true;
		}
	}
	if (doSnap) {
		SetLayerValues ('TT_wa' + changeWA + '_top', 'left=' + thisXpos, 'top=' + thisYpos);
	}
}

var dbgEventData = false; // Some extra debug info when set to true (only once)

function endAction () {
	if (changeWA == null) {
		return;
	}
	if (currentActionType == 'd') {
		SnapToGrid();
	}

	changeWA = null;
	changeXoffset = -1;
	changeYoffset = -1;
	changeXpos    = -1;
	changeYpos    = -1;
	if (currentActionType == 'r') {
		changeXbody   = -1;
		changeYbody   = -1;
		changeYbottom = -1;
		changeBwid    = -1;
	}

	currentActionType = null;

	if (document.removeEventListener) {
		document.removeEventListener('mousemove', doAction,      false);
		document.removeEventListener('mouseup',   endAction, false);
	} else if (document.detachEvent) {
		document.detachEvent('onmousemove', doAction);
		document.detachEvent('onmouseup',   endAction);
	} else if (eventObj.captureEvents) {
		document.onmousemove = null;
		document.onmouseup   = null;
	}
	return false;
}

function doAction (evt) {
	if (changeWA == null) {
		return;
	}

	if (ieVersion > 0) {
		mouseX = event.clientX; 
		mouseY = event.clientY;
	} else {
		mouseX = evt.clientX; 
		mouseY = evt.clientY;
	}


	if ((layerObj = GetObjectByID ('TT_wa' + changeWA + '_top')) == null) {
		return false;
	}
	if (layerObj.style) {
		layerObj = layerObj.style;
	}
	if (layerObj.right) {
		xposElement    = 'right';
	} else {
		xposElement    = 'left';
	}
	if (layerObj.bottom) {
		yposElement    = 'bottom';
	} else {
		yposElement    = 'top';
	}

	if (currentActionType == 'd') {
		if (layerObj.right) {
			newX = (changeXpos - (mouseX - changeXoffset));
		} else {
			newX = (changeXpos + (mouseX - changeXoffset));
		}
		if (layerObj.bottom) {
			newY = (changeYpos - (mouseY - changeYoffset));
		} else {
			newY = (changeYpos + (mouseY - changeYoffset));
		}
		SetLayerValues ('TT_wa' + changeWA + '_top', xposElement + '=' + newX, yposElement + '='+ newY);
	} else if (currentActionType == 'r') {
		SetLayerValues ('TT_wa' + changeWA + '_top', 'size=' + (changeXpos + (mouseX - changeXoffset)) + 'x' + (changeYpos + (mouseY - changeYoffset)));
		SetLayerValues ('TT_wa' + changeWA + '_body', 'size=' + (changeXbody + (mouseX - changeXoffset)) + 'x' + (changeYbody + (mouseY - changeYoffset)));
		SetLayerValues ('TT_wa' + changeWA + '_bottom', 'top=' + (changeYbottom + (mouseY - changeYoffset)), "width=" + (changeBwid + (mouseX - changeXoffset)));
	} else {
		alert ('-BUG- Noting to do; invalid action type specified!');
	}

//	if (ttDebug) {
//		alert ('Change To X:' + (changeXpos + (mouseX - changeXoffset)) + ' Y:' + (changeYpos + (mouseY - changeYoffset)));
//	}

	return false;  
}

function startAction (evt, targetArea, actionType) {
	var areaObj, bodyObj, bottomObj;


	if (dbgEventData && ttDebug) {
		dbgEventData = false;
		alert ('event.altKey                 : ' +    evt.altKey + '\n' +
			'event.bubbles                : ' +    evt.bubbles + '\n' +
			'event.button                 : ' +    evt.button + '\n' +
			'event.cancelBubble           : ' +    evt.cancelBubble + '\n' +
			'event.cancelable             : ' +    evt.cancelable + '\n' +
			'event.charCode               : ' +    evt.charCode + '\n' +
			'event.clientX                : ' +    evt.clientX + '\n' +
			'event.clientY                : ' +    evt.clientY + '\n' +
			'event.ctrlKey                : ' +    evt.ctrlKey + '\n' +
			'event.currentTarget          : ' +    evt.currentTarget + '\n' +
			'event.detail                 : ' +    evt.detail + '\n' +
			'event.eventPhase             : ' +    evt.eventPhase + '\n' +
			'event.explicitOriginalTarget : ' +   evt.explicitOriginalTarget + '\n' +
			'event.isChar                 : ' +    evt.isChar + '\n' +
			'event.keyCode                : ' +    evt.keyCode + '\n' +
			'event.layerX                 : ' +    evt.layerX + '\n' +
			'event.layerY                 : ' +    evt.layerY + '\n' +
			'event.metaKey                : ' +    evt.metaKey + '\n' +
			'event.originalTarget         : ' +    evt.originalTarget + '\n' +
			'event.pageX                  : ' +    evt.pageX + '\n' +
			'event.pageY                  : ' +    evt.pageY + '\n' +
			'event.relatedTarget          : ' +    evt.relatedTarget + '\n' +
			'event.screenX                : ' +    evt.screenX + '\n' +
			'event.screenY                : ' +    evt.screenY + '\n' +
			'event.shiftKey               : ' +    evt.shiftKey + '\n' +
			'event.target                 : ' +    evt.target + '\n' +
			'event.timeStamp              : ' +    evt.timeStamp + '\n' +
			'event.type                   : ' +    evt.type + '\n' +
			'event.view                   : ' +    evt.view + '\n' +
			'event.which                  : ' +    evt.which + '\n');
	}

	if (ieVersion > 0) {
		leftBtn = 1;
	} else {
		leftBtn = 0;
	}
	if (evt.button != leftBtn || evt.ctrlKey || evt.metaKey || evt.shiftKey || evt.altKey) {
		// Only do something when the left mousebutton is pressed without any other keys
		return;
	}
	changeWA = targetArea;
	PlaceOnTop (changeWA);
	currentActionType = actionType;

	if (ttDebug) {
		alert ('changeWA: [' + changeWA + '], type: [' + currentActionType + ']');
	}

	areaObj = GetObjectByID ('TT_wa' + changeWA + '_top');
	if (areaObj.style) {
		areaObj = areaObj.style;
	}

	if (currentActionType == 'r') {
		bodyObj =  GetObjectByID ('TT_wa' + changeWA + '_body');
		bottomObj =  GetObjectByID ('TT_wa' + changeWA + '_bottom');
		if (bodyObj.style) {
			bodyObj   = bodyObj.style;
			bottomObj = bottomObj.style;
		}
	}

	changeXoffset = evt.clientX;
	changeYoffset = evt.clientY;

	if (currentActionType == 'd') {
		if (areaObj.right) {
			changeXpos    = parseInt(areaObj.right, 10);
		} else {
			changeXpos    = parseInt(areaObj.left, 10);
		}
		if (areaObj.bottom) {
			changeYpos    = parseInt(areaObj.bottom, 10);
		} else {
			changeYpos    = parseInt(areaObj.top, 10);
		}
	} else if (currentActionType == 'r') {
		changeXpos    = parseInt(areaObj.width, 10);
		changeYpos    = parseInt(areaObj.height, 10);
		changeXbody   = parseInt(bodyObj.width, 10);
		changeYbody   = parseInt(bodyObj.height, 10);
		changeYbottom = parseInt(bottomObj.top, 10);
		changeBwid    = parseInt(bottomObj.width, 10);
	} else {
		alert ('-BUG- Noting to do; invalid action type specified!');
	}

	if (document.addEventListener) {
		document.addEventListener('mousemove', doAction, false);
		document.addEventListener('mouseup',   endAction, false);
		evt.preventDefault();
	} else if (document.attachEvent) {
		document.attachEvent('onmousemove', doAction);
		document.attachEvent('onmouseup',   endAction);
		window.event.cancelBubble = true;
		window.event.returnValue = false;
	} else if (eventObj.captureEvents) {
		document.onmousemove = doAction;
		document.onmouseup   = endAction;
	}

	return false;
}

