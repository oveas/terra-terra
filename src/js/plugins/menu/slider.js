/****p* MenuPlugins/SliderMenu
 * NAME
 *  Slider - Menu that slides out submenus
 *
 * DESCRIPTION
 *  Create a menu that slides out the submenus
 *
 * AUTHOR
 *  Alf Magne Kalleland, DHTMLgoodies.com
 *
 * COPYRIGHT
 *  Copyright (C) 2007  Dean Parkinson
 *
 * LICENSE
 *  This library is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Lesser General Public
 *  License as published by the Free Software Foundation; either
 *  version 2.1 of the License, or (at your option) any later version.
 *
 *  This library is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public
 *  License along with this library; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * HISTORY
 *  * 2007 - DHTMLgoodies.com - Created as slide-out-menu.js
 *  * Jun 10, 2011 -- O van Eijk -- Implemented in OWL-JS as slider.js, allow the use of mulitple menus
 ****/

var SUBMENU_CLASS = 'sliderSubMenu';
var menuItems;
var slideSpeed_out = 10;	// Steps to move sub menu at a time ( higher = faster)
var slideSpeed_in = 10;
var delayMenuClose = 150;	// Microseconds from mouseout to close of menu
var slideTimeout_out = 25;	// Microseconds between slide steps ( lower = faster)
var slideTimeout_in = 10;	// Microseconds between slide steps ( lower = faster)
var xOffsetSubMenu = 0; 	// Offset x-position of sub menu items - use negative value if you want the sub menu to overlap main menu

/* Don't change anything below here */

var indeces = new Array();
indeces[0] = 0;
var isMSIE = navigator.userAgent.indexOf('MSIE')>=0?true:false;
var browserVersion = parseInt(navigator.userAgent.replace(/.*?MSIE ([0-9]+?)[^0-9].*/g,'$1'));
if(!browserVersion)browserVersion=1;

function mouseOn(obj) {
	var mi = findNode(getSearchIdFromObj(obj));
	if (mi) mi.mouseOn();
}

function mouseOff(obj) {
	var mi = findNode(getSearchIdFromObj(obj));
	if (mi) mi.mouseOff();
}

function getSearchIdFromObj(obj) {
	// pull the postfix off the A link or LI tag id and return the menu item ID
	var objId = obj.id;
	var idx = objId.indexOf('_');
	if (idx>=0) {
		return "MenuItem" + objId.substring(idx);
	}
	return null;
}

function slideChildMenu(aId) {
	var mi = findNode(aId);
	if (mi) mi.slideChildMenu();
}

function findNode(searchId) {
	var result;
	for (var no=0;no<menuItems.length;no++) {
		result = menuItems[no].findNode(searchId);
		if (result) return result;
	}
	return null;
}

function getNextIndex(lvl) {
	var result = 0;
	if (indeces.length<=lvl) {
		indeces[lvl] = 1;
	} else {
		result = indeces[lvl];
		indeces[lvl]++;
	}
	return result;
}

function MenuItem(divref, ulref, liref, lvlnum, parentref) {
	this.parent = parentref;
	this.div = divref;
	this.ul = ulref;
	this.width = this.ul.offsetWidth;
	// this.left = div.style.left.replace(/[^0-9]/g,'');
	this.li = liref;
	this.alink = this.li.getElementsByTagName('A')[0];
	this.lvl = lvlnum;
	this.idx = getNextIndex(this.lvl);
	this.children;
	this.subUL = this.li.getElementsByTagName('UL')[0];
	this.children;
	this.isMouseOnMe = false;
	// note: if !isOpen && !isClosed then I am animating a slide
	this.isChildMenuOpen = false;
	this.isChildMenuClosed = true;

	// Constructor
	// if a node does not have an A tag but it's children do then we need
	// null out this node's alink field...
	if (this.alink) {
		if (this.alink.parentNode!=this.li) this.alink = null;
	}
	if (this.subUL) {
		this.children = new Array();
		var subLI = this.subUL.getElementsByTagName('LI')[0];
		while(subLI) {
			if(subLI.tagName && subLI.tagName.toLowerCase()=='li') {
				this.children[this.children.length] = new MenuItem(null, this.subUL, subLI, this.lvl + 1, this);
			}
			subLI = subLI.nextSibling;
		}
	}

	this.getPostfix = function() {
		return '_' + this.idx + '_' + this.lvl;
	};
	
	this.getId = function() {
		return "MenuItem" + this.getPostfix();
	};

	this.hasChildren = function() {
		return (this.children!=null);
	};

	this.getTopPos = function() {
		var origDisp = this.div.style.display;
		this.div.style.display = "";
		var obj = this.li;
		var result = obj.offsetTop;
		while((obj = obj.offsetParent) != null) result += obj.offsetTop;
		this.div.style.display = origDisp;
		return result;
	};

	this.getLeftPos = function() {
		var origDisp = this.div.style.display;
		this.div.style.display = "";
		var obj = this.li;
		var result = obj.offsetLeft;
		while((obj = obj.offsetParent) != null) result += obj.offsetLeft;
		this.div.style.display = origDisp;
		return result;
	};

	this.renderNode = function() {
		// set node properties
		this.li.id = "menuItemLI" + this.getPostfix();
		this.ul.style.position = "relative";
		if (this.alink) {
			this.alink.id = "menuItemA" + this.getPostfix();
			this.alink.onmouseover = function() {mouseOn(this);};
			this.alink.onmouseout = function() {mouseOff(this);};
		} else {
			this.li.onmouseover = function() {mouseOn(this);};
			this.li.onmouseout = function() {mouseOff(this);};
		}

		// set sub-menu nodes
		if (this.hasChildren()) {
			var mi = this.children[0];
			var subdiv = document.createElement('DIV');
			// Below, the originating class is saved. Modification by OvE
			subdiv.className=SUBMENU_CLASS+' '+this.div.className;
			document.body.appendChild(subdiv);
			subdiv.id = "menuItemDIV" + mi.getPostfix();
			this.subUL.id = "menuItemUL" + mi.getPostfix();
			subdiv.appendChild(this.subUL);
			subdiv.style.left = this.getLeftPos() + this.width + xOffsetSubMenu + 'px';
			subdiv.style.top = this.getTopPos() + 'px';
			subdiv.style.visibility = "hidden";
			subdiv.style.display = "none";
			subdiv.style.zindex = "1000";
			for (var no=0;no<this.children.length;no++) {
				var mi = this.children[no];
				mi.div = subdiv;
				mi.renderNode();
			}
		}
		return this.li;
	};

	this.findNode = function(searchId) {
		var result;
		if (this.getId() == searchId) {
			result = this;
		} else {
			if (this.hasChildren()) {
				for (var no=0;no<this.children.length;no++) {
					var mi = this.children[no];
					result = mi.findNode(searchId);
					if (result!=null) break;
				}
			}
		}
		return result;
	};

	this.mouseOn = function() {
		this.isMouseOnMe = true;
		if (this.hasChildren() && this.isChildMenuClosed) {
			this.initiateChildMenuOpen();
		}
	};

	this.mouseOff = function() {
		this.isMouseOnMe = false;
		if (this.hasChildren() && !this.isChildMenuClosed) {
			this.initiateChildMenuClose();
		} else if (this.parent) {
			this.parent.mouseOff();
		}
	};

	this.isMouseOnChild = function() {
		if (this.isMouseOnMe) return true;
		if (this.hasChildren()) {
			for (var no=0;no<this.children.length;no++) {
				if (this.children[no].isMouseOnChild()) return true;
			}
		}
		return false;
	};

	this.initiateChildMenuOpen = function() {
		this.isChildMenuClosed = false;
		var childDiv = this.children[0].div;
		childDiv.style.width = "0px";
		childDiv.style.visibility = "visible";
		childDiv.style.display = "";
		this.slideChildMenu();
	};

	this.initiateChildMenuClose = function() {
		this.isChildMenuOpen = false;
		// we have to wait to close the menu
		// allow the mouse to navigate over the child menu
		setTimeout("slideChildMenu('" + this.getId() + "')", delayMenuClose);
	};

	this.slideChildMenu = function() {
		var divref = this.children[0].div;
		var ulref = this.children[0].ul;
		var maxwidth = this.children[0].width;
		var nextWidth;
		if (this.isMouseOnMe  || this.isMouseOnChild()) {
			nextWidth = divref.offsetWidth + slideSpeed_out;
			if (nextWidth >= maxwidth) {
				this.finishOpeningChild(divref, ulref, maxwidth);
			} else {
				ulref.style.left = nextWidth - maxwidth + "px";
				divref.style.width = nextWidth + "px";
				setTimeout("slideChildMenu('" + this.getId() + "')", slideTimeout_out);
			}
		} else {
			nextWidth = divref.offsetWidth - slideSpeed_in;
			if (nextWidth <= 0) {
				this.finishClosingChild(divref, ulref, maxwidth);
			} else {
				ulref.style.left = nextWidth - maxwidth + "px";
				divref.style.width = nextWidth + "px";
				setTimeout("slideChildMenu('" + this.getId() + "')", slideTimeout_out);
			}
		}
	};

	this.finishOpeningChild = function(divref, ulref, maxwidth) {
		this.isChildMenuOpen = true;
		this.isChildMenuClosed = false;
		ulref.style.left = "0px";
		divref.style.width = maxwidth + "px";
	};

	this.finishClosingChild = function(divref, ulref, maxwidth) {
		this.isChildMenuOpen = false;
		this.isChildMenuClosed = true;
		divref.style.visibility = "hidden";
		divref.style.display = "none";
		divref.style.width = maxwidth + "px";
		if (this.parent) this.parent.mouseOff();
	};

}

function collectMenuNodes(menuObj)
{
	if (!menuObj) return null;

	var res = new Array();
	var mL = menuObj.getElementsByTagName('UL')[0];
	var mI = mL.getElementsByTagName('LI')[0];
	while(mI) {
		if(mI.tagName && mI.tagName.toLowerCase()=='li')
			res[res.length] = new MenuItem(menuObj, mL, mI, 0, null);
		mI = mI.nextSibling;
	}
	return res;
}

function collectMenuNodes(menuObj)
{
	if (!menuObj) return null;

	var results = new Array();
	var menuUL = menuObj.getElementsByTagName('UL')[0];
	var menuLI = menuUL.getElementsByTagName('LI')[0];
	while(menuLI) {
		if(menuLI.tagName && menuLI.tagName.toLowerCase()=='li') {
			results[results.length] = new MenuItem(menuObj, menuUL, menuLI, 0, null);
		}
		menuLI = menuLI.nextSibling;
	}
	return results;
}

function initSliderMenu(menuID)
{
	var mnu = document.getElementById(menuID);
	mnu.addClass('sliderMenu');
	menuItems = collectMenuNodes(mnu);
	if (menuItems) {
		for (var i=0; i < menuItems.length; i++) {
			var mi = menuItems[i];
			mi.renderNode();
		}
		mnu.style.visibility = 'visible';
	}
	// window.onresize = resetPosition;
}

if (typeof(SliderMenuList) != 'undefined')
	for (var i = 0; i < SliderMenuList.length; i++)
		addInitFunction('initSliderMenu', "'" + SliderMenuList[i] + "'");
