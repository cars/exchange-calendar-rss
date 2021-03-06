<?php
/*
    Config options for the Exchange Calendar RSS project.
    Copyright (C) 2010  Carlos R. Tronco

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/* Jan 2010 - Carlos Tronco -  cars at LosTroncos dot org */

#Username and password of the user we use to connect to EWS. This account must
#have "Reviewer" permissions on the calendars it will be generating feeds for.
# used so we don't have to use/keep userids/password for individuals around somewhere.
 
# To use an account called RSSSvc with a password of FRED1234
#$cfg_option['user'] = "RSSSVC";
#$cfg_option['password'] = "FRED1234";
$cfg_option['user'] = "";
$cfg_option['password'] = "";


#auth method should be either 'basic' or 'NTLM'
$cfg_option['auth'] = 'NTLM';

#Path to the "ported" WSDL file for the Exchange server we're going to talk to. 
$cfg_option['wsdl'] = "./e2k7_wsdl/services.wsdl";

#Exchange WSDL Version. One of Exchange2007,Exchange2007_SP1,Exchange2010
$cfg_option['exchangever']='Exchange2010';

# Install Path is path to where files are installed
#$cfg_option['installpath']="/var/www/ewscalendarfeed";
$cfg_option['installpath']="/var/www/html";

#URL path - URL Path to the scripts. 
$cfg_option['urlpath'] = '/ewscalendarfeed';

# Hash of FolderIds and users   ex $PDID['user'] = array(FolderId,ChangeKey)
#keeping it here is a bad idea for the longterm. may want to move to mySql
#or SQLite or something in the future
$PFIDs=array();
#Testuser's primary/default calendar
#$PFIDs['testuser1']="AAAhAHRlc3R1c2VyMUBleGNoYW5nZS5sb3N0cm9uY29zLm9yZwAuAAAAAABw7Kb1U4UyTbdDaIdxHjy6AQByp0Ud9GkGSYSvTvtebwzmAACRXCcsAAA=";

#ctronco's default calendar
#$PFIDs['ctronco'] ="AAAfAGN0cm9uY29AZXhjaGFuZ2UubG9zdHJvbmNvcy5vcmcALgAAAAAATG7V4Of7LUuYb9Ie3YEiPwEAcqdFHfRpBkmEr077Xm8M5gAAkVwnHQAA";

# calendar in ctronco's mailbox called Project Stuff
#$PFIDs['ctronco2']="AQAfAGN0cm9uY29AZXhjaGFuZ2UubG9zdHJvbmNvcy5vcmcALgAAA0xu1eDn+y1LmG/SHt2BIj8BAHKnRR30aQZJhK9O+15vDOYAKcJJ1M0AAAA=";

# calendar in ctronco's mailbox called Personal Calendar
#$PFIDs['ctronco3']="AQAfAGN0cm9uY29AZXhjaGFuZ2UubG9zdHJvbmNvcy5vcmcALgAAA0xu1eDn+y1LmG/SHt2BIj8BACF2A4ice5NKqylmrwBU4csAKf+4AAoAAAA=";





?>
