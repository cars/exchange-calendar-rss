# Introduction #

I've installed Ubuntu using the _LAMP server_ option. One then has to make sure that the libcurl3 and php5-curl packages are installed.


# Details #


In the directory where the extracted files are (if you downloaded the zip) you’ll find **cfg\_options.php**. This contains almost all the configurable values. The following values need to be defined:

  * $cfg\_option&#91;'user'&#93; – Login Id of the non-privileged account that will used to read all the calendars
  * $cfg\_option[''] -
  * $cfg\_option['authmethod'] – the scripts support both Basic and NTLM authentication when talking to EWS. NTLM required installation of curl
  * $cfg\_option['wsdl'] – path to the appropriate Exchange Web Services WSDL file.
  * $cfg\_option['installpath'] – full path to the scripts
  * $cfg\_option['urlpath'] – URL for the scripts. If the script URL is http://host/ews-cal-rss/getfeed.php this would be “/ews-cal-rss”

You also need to populate the variable $PFIDs by using the output of the list\_calendars.html and also modify the included WSDL files per [WSDLSetup](WSDLSetup.md).


Add your content here.  Format your content with:
  * Text in **bold** or _italic_
  * Headings, paragraphs, and lists
  * Automatic links to other wiki pages