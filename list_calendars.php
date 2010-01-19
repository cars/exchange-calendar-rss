<?php
/*
    List the Calendars and corresponding Id and Changekey for an Exchange user.
    To be used to generate an RSS feed from the calendar.
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

#figure out whether we're in CLI or Web mode....
$asCGI = (isset($_SERVER['SERVER_NAME']));

#Let's get args... Error checking is currently non-existent
if ($asCGI){
	#if called from web we do it one way
	if (isset($_POST['login']) && isset($_POST['secret'])){
		$userlogin= $_POST['login'];
		$userpassword= $_POST['secret'];
	} else {
		header("HTTP/1.1 404");
		print "<html><head><title>404: Error Invalid Parameters</title></head>" .
		       "<body><p>An Error occurred. I can't determine what username and" .
		       "password I should use to list your calendars. </p></body></html>";
		exit;	
	}
} else {
	#if called from CLI do it differently
	if (count($argv) == 3) {
		$userlogin = $argv[1];
		$userpassword = $argv[2];
	} else {
		print "Usage:  php list_calendars.php <login> </password>\n";
		exit; 
	}
}




#Get our configuration options...
include ("cfg_options.php"); 

#Do SoapClient Setup. How we set up the SOAP Client will differ depending on whether we use
# Basic or NTLM/Windows authentication as defined by $cfg_option['auth'] in the config file. 
# NTLM Auth should work by default. Basic authentication requires a change on the EWS virtdir
# in IIS on the Exchange server(s)
if ($cfg_option['auth'] == 'basic') {
	// Create SOAP Client using "basic" authentication
	//$client = new SoapClient('https://e2kx-cas-02.e2kx.mentorg.com/EWS/Services.wsdl',
	$client = new SoapClient($cfg_option['wsdl'],
				 array('exceptions' => 0,
				'trace' => 1,
				'login'    => $userlogin,
				'password' => $userpassword
				));
} else {
	# Use NTLM auth (the default) with CURL
	#Load the NTLMStream and SoapClient extensions
	include ("NTLMSoapClient/NTLMSoapClient.php");
	include ("NTLMStream/NTLMStream.php");
	include ("NTLMSoapClient/Exchange.php");
	include ("NTLMStream/Exchange.php");
	#unregister the original https stream handler
	stream_wrapper_unregister('https');
	#tell PHP/Curl to use the new handler
	stream_wrapper_register('https', 'NTLMStream_Exchange') or die("Failed to register protocol");
	#$client = new NTLMSoapClient_Exchange($cfg_option['wsdl'],$options);
	$client = new NTLMSoapClient_Exchange($cfg_option['wsdl'],
					      array("user"=>$userlogin,
						    "password"=>$userpassword
						    )
					      );
};



#start the output
if ($asCGI){
	print "<html><head><title>Calendar Listing for $userlogin</title></head>";
	print "<body bgcolor='#c0c0c0'><br><br><div style='padding:15'><p> Below is a listing of all the Calendars I found in $userlogin's mailbox</p></div>";
	print "<br><br><pre>";
};

#Generate Request to list all the Folders in the mailbox
$FindFolderRequest = null;
$FindFolderRequest->Traversal = 'Deep';
$FindFolderRequest->FolderShape->BaseShape = 'AllProperties';
$FindFolderRequest->ParentFolderIds->DistinguishedFolderId->Id = 'msgfolderroot';

#Make the call to EWS
$result = $client->FindFolder($FindFolderRequest);
#check out the # of CalendarFolder entries. If less than two need to forcibly treat as an array otherwise
# it's already an array.
if (is_array($result->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder)){
	$folders = $result->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder;
}else{
	$folders = array($result->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder);
};
foreach ($folders as $folder){
	#set up some vars to help format the output....
	$nl = "\n";$prefix="";$postfix="";
	if ($asCGI){
		#change output format stuff if we're running as CGI
		$prefix= '<tr><td>';
		$postfix = '</tr></td>';
		$nl = "<br>";
		print '<div><table border="1px" cellpadding="15">';
	}
	
	print  $prefix . "Calendar name is <b><font color='#0000ff'>" . $folder->DisplayName . "</font></b>$nl";
	print "\tFolder ID is [" . $folder->ParentFolderId->Id . "]$nl";
	print "\tChangeKey is [" . $folder->ParentFolderId->ChangeKey . "]$nl" . $postfix;
	if ($asCGI){
		print "</table></div><br>";
	}
	
	
}

print "</pre></body></html>";
?>

