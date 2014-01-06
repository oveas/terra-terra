<?php
/**
 * \file
 * \ingroup TT_LIBRARY
 * This is a dummy PHP file that is never loaded. It's just here for the longer documentation blocks read
 * by Doxygen.
 * \copyright{2001-2014} Oscar van Eijk, Oveas Functionality Provider
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
 * \mainpage
 * Terra-Terra is a development framework for webbased applications.
 *
 * The aim is an environment that combines the best of several worlds; ease of use from Windows,
 * flexibility from Linux, robustness from OpenVMS and of course internet's platform and location independency.
 * The design principles of Terra-Terra ensure a 100% safe web development platform; since the library itself
 * is unhackable, so are the applications built with it!
 * Terra-Terra comes with a testapplication (<a href="http://docs.terra-terra.org/tt-testkit/index.html">TTK</a>) for automated
 * testing.
 *
 * Much of this code started in 2001, a project that was abandoned when AJAX became popular from 2005 onwards. Since 2007
 * the code was continued as OWL-PHP (Oveas Web Library for PHP), but renamed back to Terra-Terra in 2014.
 *
 * Terra-Terra can be downloaded from <a href="https://github.com/oveas/terra-terra">GitHub</a>
 *
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2001-2014} Oscar van Eijk, Oveas Functionality Provider
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
 *
 * * \subpage intro
 * * \subpage internals
 */

/**
 * \page intro Introduction
 * 
 * \section into What is Terra-Terra?
 *
 * Terra-Terra is a library for web based application development. It is not created because the world is desperately waiting for yet
 * another PHP library, but it is just a  continuation of the work I started back in 2001, a project that started as ORO (Oveas Roaming Office)
 * but was soon released in open source as Terra-Terra.
 * 
 * \section history A short history
 *
 * At that time, Ajax did not exist yet, and most libraries used today where not existing or in a very early stage... just like Terra-Terra.
 * When Ajax became popular after 2005, I abandoned my project and focussed on other projects, both open source (like eGroupWare and Virtuemart; I've been core developer
 * for both) and commercial. In evey project I kept on using and improving my old Terra-Terra-code. In 2007 I decided to create a library
 * for personal use and renamed it to OWL-PHP. In 2011 this project was released in open source.
 * 
 * Early 2014 the project was renamed back to Terra-Terra and used for some private sites and commercial projects, but if this code is helpful tro anyone out there: feel free.
 *
 * \section help Wanna help?
 * 
 * As with any open source project, help is always appreciated, especially for the JavaScript part.
 */

/**
 * \page internals Understanding Terra-Terra
 * \tableofcontents
 * 
 * This page will give a brief overview of what happens when an application using Terra-Terra is started.
 * 
 * \section startup Starting an application.
 * 
 * The entry point of every application is an own index.php from where Terra-Terra is loaded. Here is een example of an index file.
 * 
 * \code
 *  1. define ('TT_ROOT', '/var/www/terra-terra');
 *  2. define ('APP_CONFIG_FILE', '/var/www/ttadmin/abc.cfg');
 *  3. 
 *  4. require (TT_ROOT . '/TTloader.php');
 *  5. TTloader::loadApplication('ABC');
 *  6.
 *  7. define ('ABC_SO', TTloader::getCurrentAppUrl() . '/so');
 *  8. define ('ABC_BO', TTloader::getCurrentAppUrl() . '/bo');
 *  9. define ('ABC_UI', TTloader::getCurrentAppUrl() . '/ui');
 * 10. Register::registerApp(TTloader::getCurrentAppName(), 0x02000001);
 * 11. Register::registerLabels();
 * 12.
 * 13. if (!TTloader::getClass('abcuser', ABC_BO)) {
 * 14.     trigger_error('Error loading classfile ABCUser from ' . ABC_BO, E_USER_ERROR);
 * 15. }
 * 16. ABCUser::getReference();
 * 17. 
 * 18. require (ABC_UI . '/mainpage.php');
 * 19. 
 * 20. TTloader::getClass('TTrundown.php', TT_ROOT);
 * \endcode
 * 
 * \subsection defines Required defines
 * 
 * First, your application must define the environment:
 *   * TT_ROOT: (line 1) Full path at the server to the Terra-Terra installation directory
 * 
 * Optionally, a configuration file can be defined (line 2). If set, the configfile can override most settings that are in the Terra-Terra default configuration.
 * Most of the settings can also be stored in the database and changed dynamically.
 * 
 * For more constants that can be defined by the application, see \ref GlobalConstants.
 * 
 * \subsection ttloader The TT-Loader
 * 
 * Next step is loading the Terra-Terra library (line 4). After this call, the TT-Loader is used to load your own application (line 5).
 * The loadApplication() method accepts an optional second parameter the boolean $primary). This parameter is for internal use by Terra-Terra: the default here is 'true'
 * and when called from the entry point of your application is must always be true!
 * 
 * \subsection structure Application structure
 * 
 * Terra-Terra is built using a 3-tier architecture, and although it is good practice to stick to the same architecture, you're free to use
 * your own favorite like MVC.
 * 
 * The defines at lines 7-9 use the getter method TTloader::getCurrentAppUrl() to get application data of your own application that is
 * stored in cache by the TT-Loader.
 * The constants created here are used by the applicatino only, so they can be freely choosen (or even completely omitted).
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
 * Terra-Terra's User baseclass is a singleton, so a call to getReference() will instantiate the object and add it to TTCache,
 * from where it can be retrieved at any time with TTCache::get(TTCACHE_OBJECTS, 'user').
 * 
 * \subsection load Load the application
 * 
 * Here, a seperate php file is created in the ABC_UI directory, called mainpage.php. This file is loaded
 * at line 18 and will design the page that's gonna be displayed.
 * 
 * \subsection rundown Close the session
 * 
 * The call at line 20 is important. The TTRundown class closes the page and makes sure all information is stored if a next
 * page will be displayed in the same session.
 * 
 */
