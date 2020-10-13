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

// Doxygen setup
/**
 * \defgroup TT_UI_LAYER Presentation Layer modules
 * \defgroup TT_BO_LAYER Business Layer modules
 * \defgroup TT_SO_LAYER Storage Layer modules
 * \defgroup TT_LIBRARY Library (codes, messages files etc.)
 * \defgroup TT_CONTRIB Contributed helper functions
 * \defgroup TT_UI_PLUGINS Plugins for the presentation modules
 * \defgroup TT_DRIVERS Drivers
 * \defgroup TT_TTADMIN TT administration site
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
 * The entry point of every application is the application loader. To create a menu from which the application can be called,
 * the applic hook must be created.
 * 
 * \todo this section must be rewritten since the 2020 changes
 */
