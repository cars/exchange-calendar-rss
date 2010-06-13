<?php

/*
    Generates an RSS feed from a properly configured user calendar in Exchange.
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
/* Jun 2010 - Removed Changekey in  FindIte, request since we don't need it */
/* Jan 2010 - Carlos Tronco -  cars at LosTroncos dot org */
	
	#Do we want to log debug output? If so it's gonna be added as an additional item
	# in the feed itself. 
	if ($_REQUEST['debug'] == "true"){
		$scriptdebug=true;
		$debuglog = "Starting Script with Debugging info on\n";
	}
	
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
			                'login'    => $cfg_option['user'],
					'password' => $cfg_option['password']
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
						      array("user"=>$cfg_option['user'],
							    "password"=>$cfg_option['password']
							    )
						      );
	};
	
	# let's parse the path info to figure out the calendard we want to get
	# PathInfo should be something like: /testuser1-default
	$origpathinfo=$_SERVER["PATH_INFO"];
	$fields=split('/',$origpathinfo);
	$userfeedtogen=$fields[1];
	
	#
	#Let's see if we can find this calendar in the list of PFIDs from the config file. 
	# if not we need to bail....
	if (!isset($PFIDs[$userfeedtogen])) {
		header("HTTP/1.1 404 404 Unknown User and/or Calendar");
		print "<html><head><title>404: retrieving calendar[" . $userfeedtogen ."]</title></head><body>I don't seem to be aware of any calendars called [" . $userfeedtogen."]";
		print "<p>here are calendars I know about\n<ul>";
		foreach (array_keys($PFIDs) as $k) {
			print "<li> " . $k . "</li>";
		};
		print "</ul></body></html>";
		exit;		
	}	

	#Build the XML/object for the initial FindItem request to list the calendar items.
	$FindItemsRequest = null;
        $FindItemsRequest->Traversal = "Shallow";
        $FindItemsRequest->ItemShape->BaseShape = "AllProperties";
	$FindItemsRequest->ParentFolderIds->FolderId->Id = $PFIDs[$userfeedtogen][0];
	# Change key isn't needed.
	#$FindItemsRequest->ParentFolderIds->FolderId->ChangeKey = $PFIDs[$userfeedtogen][1];
	
	#Let's try to get the list by making the SOAP call....
        $result=$client->FindItem($FindItemsRequest);

	#Let's see if there were any errors in the respose....
	$response_class = $result->ResponseMessages->FindItemResponseMessage->ResponseClass;
	#
	if ($response_class == "Error"){
		header("HTTP/1.1 404");
		print "<html><head><title>404: ". $response_class  ." retrieving calendar</title></head><body><p>Error trying to retrieve calendar " . $origpathinfo ."</p><pre>" . print_r($result->ResponseMessages->FindItemResponseMessage) . "</pre>";
		print "</body></html>";
		exit;		
	}
	#Get # of items in FindItem result
	$numitems= $result->ResponseMessages->FindItemResponseMessage->RootFolder->TotalItemsInView;
	$debuglog.="<pre> Total Items in View = [". $result->ResponseMessages->FindItemResponseMessage->RootFolder->TotalItemsInView ."]</pre>\n";
	if ($result->ResponseMessages->FindItemResponseMessage->RootFolder->TotalItemsInView == 1) {
		# IF there's only one item, $appts isn't read as an array... so we force it here. 		
		$appts=array($result->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem);
	}else {
		$appts=$result->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem;
	};
	
	 if ($scriptdebug){
		#If we're debugging add the apps the and result info to the debug log. 
		$debuglog .= '<div><hr><p>Result</p><pre>' . print_r($result,true) . "</pre></div>";
	}
	
	include("FeedWriter/FeedWriter.php");
	$CalFeed= new FeedWriter(ATOM);
	$CalFeed->setTitle('Exchange Calendar Feed');
	$CalFeed->setLink('./');
	$CalFeed->setChannelElement('author',array('name'=>'Carlos Tronco'));
	$CalFeed->setChannelElement('updated', date(DATE_ATOM , time()));
	
	#Let's iterate through the list of appts and get each appt and get more info than FindItem gave us.
        foreach ($appts as $appt) {
		#Build the XML/object for the GetItemRequest
                $GetItemRequest=null;
                $GetItemRequest->ItemShape->BaseShape = "AllProperties";
                $GetItemRequest->ItemIds->ItemId->Id = $appt->ItemId->Id;
                $GetItemRequest->ItemIds->ItemId->ChangeKey = $appt->ItemId->ChangeKey;
		
		if ($scriptdebug) {
			$debuglog .=  "<hr><p>Appt used to build GetItem Request</p><pre>" . print_r($appt,true);
			$debuglog .=  "</pre><hr><p>Appt Info</p><pre>";
			$debuglog .=  "ItemId - Id = [" . $appt->ItemId->Id . "]\n";
			$debuglog .=  "ItemId - ChangeKey = [" . $appt->ItemId->ChangeKey . "]\n";
			$debuglog .=  "</pre><hr><p></p>\n<p>GetItem pre-call</p><pre>";
			$debuglog .= print_r($GetItem,true);
			$debuglog .=  "</pre>";
		};
		
                $apptResult= $client->GetItem($GetItemRequest);
		
                $apptDetails=$apptResult->ResponseMessages->GetItemResponseMessage->Items->CalendarItem;
		$ApptItem=$CalFeed->createNewItem();
                
                $itemDetails = $apptDetails->ItemId;
		$ApptItem->setTitle($apptDetails->Subject);
	/*	print "<pre>";
		print_r($itemDetails);
		print "</pre>"; */

		$ApptItem->setLink($cfg_option['urlpath']. "/getappt/" . $userfeedtogen . "/".rawurlencode($apptDetails->ItemId->Id));
#		$ApptItem->setLink("/ewscalendarfeed/getappt/" . $userfeedtogen . "/".$apptDetails->ItemId->Id);

		$ApptItem->setDate( $apptDetails->Start);
		$start=rtrim($apptDetails->Start,'zZ');
		
		$ApptSummary = "<em>When:</em><b> " . date("D M j Y g:ia",strtotime($apptDetails->Start))          . " to " . date("D M j Y g:ia",strtotime($apptDetails->End)) . "(" . date_default_timezone_get() .")</b>\n<br>";

		# There can be different kinds of entries on the calendar. If it's a meeting we want to display
		# 
		if ($apptDetails->IsMeeting == 1){ 
			$ApptSummary.= "<br><em>Who:</em><b> ";
			if (strlen($apptDetails->DisplayTo) > 3){
				$ApptSummary.= $apptDetails->DisplayTo . " ";
			};
			if (strlen($apptDetails->DisplayCC) > 3){
				$ApptSummary.= ";" . $apptDetails->DisplayCC;
			};
			$ApptSummary .= "</b>\n";
		}
		if ($apptDetails->Location) {
			$ApptSummary .= "<br><em>Where:</em><b> " . $apptDetails->Location . "</b>\n";
		}
		#apptBodyFull is the full HTML document version of the appt description
		$apptBodyFull = $apptDetails->Body->_;
		#Let's strip all the html header stuff off of it. 
		$bodystart=stripos($apptBodyFull,"<body>")+6;
		$bodyend=strripos($apptBodyFull,"</body>");
		$apptBody=substr($apptBodyFull,$bodystart,$bodyend-$bodystart);
		$ApptItem->addElement('summary',$ApptSummary);
		$ApptItem->addElement('content',$ApptSummary . "\n" . $apptBody, array('type'=>'html'));
		$CalFeed->addItem($ApptItem);
        }
        # If we're debugging let's add an item to the feed for the debug info
	if ($scriptdebug){
		$debugdescription= "<h3>SERVER VARS</h3>\n<hr><pre>";
		$debugdescription.= print_r($_SERVER,true);
		$pathinfo=$_SERVER["PATH_INFO"];
		$fields=split('/',$pathinfo);
		$debugdescription.="parsed fields\n";
		$debugdescription.="Fields[0] =[". $fields[0] . "]\n";
		$debugdescription.="Fields[1] =[". $fields[1] . "]\n";
		$debugdescription.="UserFeedtoGen is " . $userfeedtogen . "\n";
		$debugdescription.="debug" . $_SERVER[argv][0] . "\n";
		$debugdescription.="</PRE>";
		
		$DebugItem=$CalFeed->createNewItem();
		$DebugItem->setTitle('DebugInfo');
		$DebugItem->setLink("http://192.168.1.112/" . $_SERVER["REQUEST_URI"] ."/debug");
		$DebugItem->setDate( $apptDetails->Start);
		$DebugItem->addElement('summary',$debugdescription);
		$DebugItem->addElement('content',$debugdescription . "\n<hr>" .$debuglog, array('type'=>'html'));
		$CalFeed->addItem($DebugItem);
	};
	

	$CalFeed->genarateFeed();
        
        /* Do something with the web service connection */
        stream_wrapper_restore('https');
