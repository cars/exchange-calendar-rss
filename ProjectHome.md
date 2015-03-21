An examples of how to use PHP and the Exchange Web Services (EWS) interface to generate an RSS feed from a user's calendar similar to what can be done with Google Calendar.

Mainly for my own edification but can be used as a full working examples of using PHP and EWS together since I haven't been able to find any others.

This code can use the NTLMSoapClient Class by Thomas Rabaix. ( http://rabaix.net/en/articles/2008/03/13/using-soap-php-with-ntlm-authentication )
It also uses the PHP Universal Feed Generator from (http://www.ajaxray.com/blog/2008/03/08/php-universal-feed-generator-supports-rss-10-rss-20-and-atom/) to geneate the ATOM feed.

An overview of this project is available at: http://cars.home.lostroncos.org/2010/01/25/generating-an-atom-feed-from-an-exchange-calendar/ and I'll be working on migrating that info to the Wiki here as well as expanding on it.