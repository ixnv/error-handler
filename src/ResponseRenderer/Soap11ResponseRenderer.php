<?php

namespace eLama\ErrorHandler\ResponseRenderer;

class Soap11ResponseRenderer extends WebResponseRenderer
{

    /**
     * @return string
     */
    protected function getResponseBody()
    {
        return <<<'SOAP_FAULT'
<?xml version='1.0' encoding='UTF-8'?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Header/>
    <SOAP-ENV:Body>
        <SOAP-ENV:Fault>
            <faultcode>SOAP-ENV:Server</faultcode>
            <faultstring>Unknown error</faultstring>
            <detail>
                Something unexpected happened, we`ll fix it as soon as possible. Sorry for inconvenience.
            </detail>
        </SOAP-ENV:Fault>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
SOAP_FAULT;
    }
}
