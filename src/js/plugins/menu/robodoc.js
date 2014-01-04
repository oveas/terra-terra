/****h* Plugins/MenuPlugins
 * NAME
 *  MenuPlugins - Terra-Terra/JS plugins for menus
 *
 * DESCRIPTION
 *  Menu plugins are set by the Terra-Terra Menu container plugin, using the menuType() method.
 *  All files in the Terra-Terra/JS menu plugin directory must have lowercase names that match the
 *  given menu type. Optionally, a style sheet with the same name can be located here as well;
 *  that file will also be loaded by menuType().
 *
 *  When the menutype is set, Terra-Terra will create an array with the name "<menutype>MenuList"
 *  that contains all menus with that type, e.g. if a document is created with 2 menus of type
 *  <menutype> and the ID's 'mainMenu' and 'userMenu', the following code is written to
 *  the document:
 *  
 *    if (typeof(<menutype>MenuList) == 'undefined') <menutype>MenuList = new Array();
 *    <menutype>MenuList.push('mainMenu')
 *    if (typeof(<menutype>MenuList) == 'undefined') <menutype>MenuList = new Array();
 *    <menutype>MenuList.push('userMenu')
 *
 *  This array can be used to initialise menus when the document is loaded. If the plugin used
 *  the function 'initMyMenuType(ID)', this code should be added to the the plugin:
 *
 *    if (typeof(<menutype>MenuList) != 'undefined')
 *        for (var i = 0; i < <menutype>MenuList.length; i++)
 *            addInitFunction('initMyMenuType', "'" + <menutype>MenuList[i] + "'");
 *
 *  TT menus are created as unsorted lists (<ul>...</ul>) where every menu option is a list item
 *  (<li>...</li>).
 *  The complete menu is nested in a div whose ID is in the <menutype>MenuList array.
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * HISTORY
 *  * Jun 12, 2011 -- O van Eijk -- initial version for OWL
 ****/
// This is a dummy file which is never loaded, just to provide ROBODoc documentation.
