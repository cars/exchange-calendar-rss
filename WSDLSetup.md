# Introduction #

Because the WSDL Microsoft provides doesn't work quite correctly on its own if we query the Exchange server at https://exchange.company.com/ews/Services.wsdl It will return the WSDL for EWS but the soapclient can't figure out where to connect to actually make the calls.  This page will provide the instructions on how to modify the WSDL file so it can be used.


# Details #

Copies of the WSDL files for both Exchange 2007 and 2010 are included in the source. But if you need/want to create your own version here's how.......

  1. Get the WSDL from your Exchange server. The URL will be something like https://exchange.company.com/ews/Services.wsdl. You will need to provide a valid Exchange/Active Directory user account & password to access/download the file.
  1. Save it to a directory near where the rest of the code is located.
  1. Open the file and go to the end of the file and before the **`</wsdl:definitions>`** you want to add an entry similar to: _substituting the appropriate URL for your environment._
```
<wsdl:service name="ExchangeServices">
   <wsdl:port name="ExchangeServicePort" binding="tns:ExchangeServiceBinding">
      <soap:address location="https://mail01.company.com/EWS/Exchange.asmx" />
   </wsdl:port>
 </wsdl:service>
```

  * I also add the two additional files the WSDL reference to the location where the modified WSDL file is stored just for the sake of completeness. These files are: `types.xsd` and `messages.xsd`