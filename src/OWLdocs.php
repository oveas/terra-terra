<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This is a dummy PHP file that is never loaded. It's just here for the longer documentation blocks read
 * by Doxygen.
 * \copyright{2007-2013} Oscar van Eijk, Oveas Functionality Provider
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
 * \mainpage
 * Oveas Web Library for PHP is a development framework for webbased applications.
 *
 * The aim is an environment that combines the best of several worlds; ease of use from Windows,
 * flexibility from Linux, robustness from OpenVMS and of course internet's platform and location independency.
 * The design principles of OWL-PHP ensure a 100% safe web development platform; since the library itself
 * is unhackable, so are the applications built with it!
 * OWL comes with a testapplication (<a href="http://owl.oveas.com/docs/otk/index.html">OTK</a>) for automated
 * testing.
 *
 * Together with the planned <a href="http://owl.oveas.com/docs/owl-js/index.html">OWL-JS</a>, you might consider the OWL family as the basis of what Web2.2 will look like ;)
 *
 * Much of this code started as the project Terra-Terra in 2001 (http://terra-terra.org), a project that
 * was abandoned when AJAX became popular from 2005 onwards.
 *
 * OWL-PHP can be downloaded from <a href="https://github.com/oveas/owl-php">GitHub</a>
 *
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2013} Oscar van Eijk, Oveas Functionality Provider
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
 *
 * * \subpage intro
 * * \subpage internals
 */

/**
 * \page intro Introduction
 * 
 * \section into What is OWL-PHP?
 *
 * OWL-PHP is a library for web based application development. It is not created because the world is desperately waiting for yet
 * another PHP library, but it is just a  continuation of the work I started back in 2001, a project that started as ORO (Oveas Roaming Office)
 * but was soon released in open source as Terra-Terra (probably still online at http://terra-terra.org).
 * 
 * \section history A short history
 *
 * At that time, Ajax did not exist yet, and most libraries used today where not existing or in a very early stage... just like Terra-Terra.
 * When Ajax became popular after 2005, I abandoned my project and focussed on other projects, both open source (like eGroupWare and Virtuemart; I've been core developer
 * for both) and commercial. In evey project I kept on using and improving my old Terra-Terra-code. In 2007 I decided to create a library
 * for personal use and renamed it to OWL. In 2011 this project was released in open source.
 * 
 * By now (2013) it's a hobby project, used for some private sites and commercial projects, but if this code is helpful tro anyone out there: feel free.
 *
 * \section help Wanna help?
 * 
 * As with any open source project, help is always appreciated, especially for OWL-JS. On paper, OWL-PHP works together with OWL-JS, it's JavaScript
 * partner which is virtually non-existing at the moment.
 */

/**
 * \page internals Understanding OWL-PHP
 * \tableofcontents
 * 
 * This page will give a brief overview of what happens when an application using OWL-PHP is started.
 * 
 * \section startup Starting an application.
 * 
 * The entry point of every application is an own index.php from where OWL-PHP is loaded. Here is een example of an index file.
 * 
 * \code
 *  1. define ('OWL_ROOT', '/var/www/owl-php');
 *  2. define ('APPL_CODE', 'ABC');
 *  3. define ('APP_CONFIG_FILE', '/var/www/owladmin/abc.cfg');
 *  4. 
 *  5. require (OWL_ROOT . '/OWLloader.php');
 *  6. 
 *  7. define ('ABC_SO', APPL_SITE_TOP . '/so');
 *  8. define ('ABC_BO', APPL_SITE_TOP . '/bo');
 *  9. define ('ABC_UI', APPL_SITE_TOP . '/ui');
 * 10. Register::registerApp(APPL_NAME, 0x02000001);
 * 11. Register::registerLabels();
 * 12.
 * 13. if (!OWLloader::getClass('abcuser', ABC_BO)) {
 * 14.     trigger_error('Error loading classfile ABCUser from ' . ABC_BO, E_USER_ERROR);
 * 15. }
 * 16. ABCUser::getReference();
 * 17. 
 * 18. require (ABC_UI . '/mainpage.php');
 * 19. 
 * 20. OWLloader::getClass('OWLrundown.php', OWL_ROOT);
 * \endcode
 * 
 * \subsection defines Required defines
 * 
 * First, your application must define itself and the environment. Therefore, 2 constants are required:
 *   * OWL_ROOT: (line 1) Full path at the server to the OWL-PHP installation directory
 *   * APPL_CODE: (line 2) This is the code by which OWL-PHP identifies the application. The applicatino must be installed using this code (installation will
 *   be described elsewhere)
 * 
 * Optionally, a configuration file can be defined (line 3). If set, the configfile can override most settings that are in the OWL-PHP default configuration.
 * Most of the settings can also be stored in the database and changed dynamically.
 * 
 * For more constants thet can be defined by the application, see \ref GlobalConstants.
 * 
 * \subsection owlloader The OWL-Loader
 * 
 * Next step is loading the OWL-PHP library (line 5).
 * 
 * \subsection structure Application structure
 * 
 * OWL-PHP is built using a 3-tier architecture, and although it is good practice to stick to the same architecture, you're free to use
 * your own favorite like MVC.
 * 
 * The defines at lines 7-9 use the constant APPL_SITE_TIO that is provided by OWL-PHP. The constants created here are used by the
 * applicatino only, so they can be freely choosen (or even completely omitted).
 * 
 * On line 10, a unique code is defined that is used by the message handling - to be explained later (but this will probably change).
 * 
 * Line 11 show the call to register all labels that are used for this application. It your application contains a /lib directory
 * and  that directory holds an &lt;app-code&gt;.labels[.lang-code].php file, the labels will be stored in the labels array.
 * 
 * \subsection userclass Load the user class.
 * 
 * Every application must reimplement the user class. In this example, it is called  abcuser and located in the applications
 * ABC_BO directory. Setting up a user class will be described elsewhere.
 * 
 * The class is loaded at line 13. The error check is optional. After loading, the user object must be instantiated; this will
 * start or restore the user's session.
 * OWL-PHP's User baseclass is a singleton, so a call to getReference() will instantiate the object and add it to OWLCache,
 * from where it can be retrieved at any time with OWLCache::get(OWLCACHE_OBJECTS, 'user').
 * 
 * \subsection load Load the application
 * 
 * Here, a seperate php file is created in the ABC_UI directory, called mainpage.php. This file is loaded
 * at line 18 and will design the page that's gonna be displayed.
 * 
 * \subsection rundown Close the session
 * 
 * The call at line 20 is important. The OWLRundown class closes the page and makes sure all information is stored if a next
 * page will be displayed in the same session.
 * 
 */
