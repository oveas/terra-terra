/****f* languageExtentions/Array.indexOf
 * NAME
 *  Array.indexOf - Add the indexOf method to the Array object for browsers that don't support this (yet)
 *
 * DESCRIPTION
 *  indexOf compares searchElement to elements of the Array using strict equality (the same method used by the ===, or triple-equals, operator).
 *
 * INPUTS
 *  * searchElement -- Element to locate in the array.
 *  * fromIndex -- The index at which to begin the search. Defaults to 0, i.e. the whole array will be searched. If the index is greater than or equal to the length of the array, -1 is returned, i.e. the array will not be searched. If negative, it is taken as the offset from the end of the array. Note that even when the index is negative, the array is still searched from front to back. If the calculated index is less than 0, the whole array will be searched.
 *
 * RESULT
 *  * the first index at which a given element can be found in the array, or -1 if it is not present
 *
 * AUTHOR
 *  Mozilla, see href:https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Array/indexOf
 *
 * SYNOPSIS
 */
if (!Array.prototype.indexOf) {
	Array.prototype.indexOf = function(searchElement/*, fromIndex */)
/****/
	{
		"use strict";

		if (this === void 0 || this === null)
			throw new TypeError();

		var t = Object(this);
		var len = t.length >>> 0;
		if (len === 0)
			return -1;

		var n = 0;
		if (arguments.length > 0) {
			n = Number(arguments[1]);
			if (n !== n) // shortcut for verifying if it's NaN
				n = 0;
			else if (n !== 0 && n !== (1 / 0) && n !== -(1 / 0))
				n = (n > 0 || -1) * Math.floor(Math.abs(n));
		}

		if (n >= len)
			return -1;

		var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);

		for (; k < len; k++) {
			if (k in t && t[k] === searchElement)
				return k;
		}
		return -1;
	};
}

