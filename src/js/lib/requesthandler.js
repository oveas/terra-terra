/****c* library/requestHandler
 * NAME
 *  requestHandler - XMLHttp Request Handler
 *
 * EXAMPLE
 *	var reqHandler;
 *	
 *	function myFunction()
 *	{
 *		serverResponse = reqHandler.getResponseText();
 *		document.write(serverResponse);
 *	}
 *	
 *	reqHandler = new requestHandler();
 *	reqHandler.whenComplete(myFunction);
 *	reqHandler.sendRequest("serverPage.php", "variable=value");
 *
 * DESCRIPTION
 *  Define the class for XMLHttp Request handling. When constructed, the browser dependent
 *  object is instantiated
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
 * HISTORY
 *  * Jun 8, 2011 -- O van Eijk -- initial version
 ****/
function requestHandler()
{
	var rH;

	var uM = null;
	var lM = null;
	var fM = null;
	var iM = null;
	var cM = null;

	// Establish the request handler object
	try {
		// Mozilla, Safari
		rH = new XMLHttpRequest();
	} catch (e) {
		try {
			// MSIE
			rH = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				// Older MSIE
				rH = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				// Outdated browser
				rH = null;
			}
		}
	}

	/****m* requestHandler/sendRequest
	 * NAME
	 *  sendRequest - Send a request to the server
	 *
	 * INPUTS
	 *  * reqUrl -- URL to open for the request
	 *  * reqParams -- Parameter list
	 *  * reqMethod -- Request method, defaults to 'POST'
	 *  * reqContentType -- Content type, defaults to 'application/x-www-form-urlencoded'
	 *
	 * AUTHOR
	 *  Oscar van Eijk, Oveas Functionality Provider
	 *
	 * SYNOPSIS
	 */
	this.sendRequest = function(reqUrl, reqParams, reqMethod, reqContentType)
	/****/
	{
		this.setCallback();
		if (typeof(reqMethod) == 'undefined')
			reqMethod = "POST";

		if (typeof(reqContentType) == 'undefined')
			reqContentType = "application/x-www-form-urlencoded";

		rH.open(reqMethod, reqUrl, true);
		rH.setRequestHeader("Content-Type", reqContentType);
		rH.send(reqParams);
	};

	/****m* requestHandler/whenUninitialized
	 * NAME
	 *  whenUninitialized - Set a callback function for the 'uninitialized' state
	 *
	 * DESCRIPTION
	 *  This method registers a callback function that will be called when the state of
	 *  the requestHandler object changes to 'uninitialized' (0).
	 *
	 *  If no callback function for this state is set, nothing will be done.
	 *
	 * INPUTS
	 *  * userFunction -- Function to call when the requestHandler goes to the 'uninitialized' state
	 *
	 * AUTHOR
	 *  Oscar van Eijk, Oveas Functionality Provider
	 *
	 * SYNOPSIS
	 */
	this.whenUninitialized = function(userFunction)
	/****/
	{
		uM = userFunction;
	};

	/****m* requestHandler/whenLoading
	 * NAME
	 *  whenLoading - Set a callback function for the 'uninitialized' state
	 *
	 * DESCRIPTION
	 *  This method registers a callback function that will be called when the state of
	 *  the requestHandler object changes to 'loading' (1).
	 *
	 *  If no callback function for this state is set, nothing will be done.
	 *
	 * INPUTS
	 *  * userFunction -- Function to call when the requestHandler goes to the 'loading' state
	 *
	 * AUTHOR
	 *  Oscar van Eijk, Oveas Functionality Provider
	 *
	 * SYNOPSIS
	 */
	this.whenLoading = function(userFunction)
	/****/
	{
		lM = userFunction;
	};

	/****m* requestHandler/whenLoaded
	 * NAME
	 *  whenLoaded - Set a callback function for the 'uninitialized' state
	 *
	 * DESCRIPTION
	 *  This method registers a callback function that will be called when the state of
	 *  the requestHandler object changes to 'loaded' (2).
	 *
	 *  If no callback function for this state is set, nothing will be done.
	 *
	 * INPUTS
	 *  * userFunction -- Function to call when the requestHandler goes to the 'loaded' state
	 *
	 * AUTHOR
	 *  Oscar van Eijk, Oveas Functionality Provider
	 * 
	 * SYNOPSIS
	 */
	this.whenLoaded = function(userFunction)
	/****/
	{
		fM = userFunction;
	};

	/****m* requestHandler/whenInteractive
	 * NAME
	 *  whenInteractive - Set a callback function for the 'uninitialized' state
	 *
	 * DESCRIPTION
	 *  This method registers a callback function that will be called when the state of
	 *  the requestHandler object changes to 'interactive' (3).
	 *
	 *  If no callback function for this state is set, nothing will be done.
	 *
	 * INPUTS
	 *  * userFunction -- Function to call when the requestHandler goes to the 'interactive' state
	 *
	 * AUTHOR
	 *  Oscar van Eijk, Oveas Functionality Provider
	 *
	 * SYNOPSIS
	 */
	this.whenInteractive = function(method)
	/****/
	{
		iM = userFunction;
	};

	/****m* requestHandler/whenComplete
	 * NAME
	 *  whenComplete - Set a callback function for the 'complete' state
	 *
	 * DESCRIPTION
	 *  This method registers a callback function that will be called when the state of
	 *  the requestHandler object changes to 'complete' (4).
	 *
	 *  If no callback function for this state is set, nothing will be done.
	 *
	 * INPUTS
	 *  * userFunction -- Function to call when the requestHandler goes to the 'complete' state
	 *
	 * AUTHOR
	 *  Oscar van Eijk, Oveas Functionality Provider
	 *
	 * SYNOPSIS
	 */
	this.whenComplete = function(userFunction)
	/****/
	{
		cM = userFunction;
	};

	/****m* requestHandler/getResponseText
	 * NAME
	 *  getResponseText - Get the response from the server
	 *
	 * DESCRIPTION
	 *  Get the complete server response without any formatting
	 *
	 * RESULT
	 *  * Complete response as received from the server
	 *
	 * AUTHOR
	 *  Oscar van Eijk, Oveas Functionality Provider
	 *
	 * SYNOPSIS
	 */
	this.getResponseText = function()
	/****/
	{
		return rH.responseText;
	};

	/****im* requestHandler/setCallback
	 * NAME
	 *  setCallback - Define the callback function
	 *
	 * DESCRIPTION
	 *  Define the callback function that will check for which statechange userFunctions have
	 *  been defined and call them accordingly.
	 *
	 * AUTHOR
	 *  Oscar van Eijk, Oveas Functionality Provider
	 *
	 * SYNOPSIS
	 */
	this.setCallback = function()
	/****/
	{
		rH.onreadystatechange = function (){
			switch (rH.readyState) {
				case 0:
					m = uM;
					break;
				case 1:
					m = lM;
					break;
				case 2:
					m = fM;
					break;
				case 3:
					m = iM;
					break;
				case 4:
					m = cM;
					break;
				default:
					m = null;
			}
			if (m !== null) m();
		};
	};
}
