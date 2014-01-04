/****f* languageExtentions/HTMLElement.addClass
 * NAME
 *  HTMLElement.addClass - Add the addClass() method to HTMLelement objects
 *
 * DESCRIPTION
 *  If the HTML element has no class yet, it will be set to the given class.
 *  Otherwise the given classname will be added to the list.
 *
 * INPUTS
 *  * className -- Name of the CSS Class
 *  * posFirst -- When true, the class will be first in the list. By default the classname is added to the end
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * COPYRIGHT
 *  (c)2002-2011 -- Oscar van Eijk, Oveas Functionality Provider
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
 * SYNOPSIS
 */
if (!HTMLElement.prototype.addClass) {
	HTMLElement.prototype.addClass = function(className, posFirst)
/****/
	{
		"use strict";

		if (this === void 0 || this === null)
			throw new TypeError();

		if (posFirst === undefined)
			posFirst = false;

		var c = this.getAttribute("class");
		if (c === null) {
			this.setAttribute("class", className);
			return;
		}
		if (posFirst === true) {
			this.setAttribute("class", className + ' ' + c);
			return;
		}
		this.setAttribute("class", c + ' ' + className);
	};
}
