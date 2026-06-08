<?php
    $WsdlAdres 			= "http://efatura-test.uyumsoft.com.tr/Services/BasicIntegration?singleWsdl";
	$WsdlKullaniciAdi 	= "Uyumsoft";
	$Wsdlsifre			= "Uyumsoft";
	
	$WsdlXml			='	<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
								<s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
									<IsEInvoiceUser xmlns="http://tempuri.org/"> 
										<userInfo Username="'.$WsdlKullaniciAdi.'" Password="'.$Wsdlsifre.'"/> 
										<vknTckn>9000068418</vknTckn> 
										<alias></alias>
									</IsEInvoiceUser>
								</s:Body> 
							</s:Envelope>
							';

   $WsdlBaslik 			= array(	"Content-type: text/xml;charset=\"utf-8\"",
   									"Accept: text/xml",
   									"Cache-Control: no-cache",
   									"Pragma: no-cache",
				                    "Content-length: ".strlen($WsdlXml),
				                    "SOAPAction: http://tempuri.org/IBasicIntegration/IsEInvoiceUser" 
				                	);


    $WsdlBaglan = curl_init();
    curl_setopt($WsdlBaglan, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($WsdlBaglan, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($WsdlBaglan, CURLOPT_URL, $WsdlAdres);
    curl_setopt($WsdlBaglan, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($WsdlBaglan, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($WsdlBaglan, CURLOPT_AUTOREFERER, true);
    curl_setopt($WsdlBaglan, CURLOPT_USERPWD, $WsdlKullaniciAdi.":".$Wsdlsifre);
    curl_setopt($WsdlBaglan, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($WsdlBaglan, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($WsdlBaglan, CURLOPT_TIMEOUT, 10);
    curl_setopt($WsdlBaglan, CURLOPT_POST, true);
    curl_setopt($WsdlBaglan, CURLOPT_HTTPGET, false);
    curl_setopt($WsdlBaglan, CURLOPT_VERBOSE, true);
    curl_setopt($WsdlBaglan, CURLOPT_HEADER, false);
    curl_setopt($WsdlBaglan, CURLINFO_HEADER_OUT, true);
    curl_setopt($WsdlBaglan, CURLOPT_POSTFIELDS, $WsdlXml);
    curl_setopt($WsdlBaglan, CURLOPT_HTTPHEADER, $WsdlBaslik);

    $WsdlSonuc = curl_exec($WsdlBaglan); 
    curl_close($WsdlBaglan);

    $WsdlSonuc = str_replace("<s:Body","<body",$WsdlSonuc);
	$WsdlSonuc = str_replace("</s:Body","</body>",$WsdlSonuc);
    $WsdlSonuc = simplexml_load_string($WsdlSonuc);

	echo $WsdlSonuc->body->IsEInvoiceUserResponse->IsEInvoiceUserResult["Value"];

    ?>