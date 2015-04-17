<?php

namespace eLama\ErrorHandler\ResponseRenderer;

class Soap12ResponseRenderer extends WebResponseRenderer
{

    /**
     * @return string
     */
    protected function getResponseBody()
    {
        return <<<'SOAP_FAULT'
<?xml version='1.0' encoding='UTF-8'?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
    <env:Header/>
    <env:Body>
        <env:Fault>
            <env:Code>
                <env:Value>env:Receiver</env:Value>
            </env:Code>
            <env:Reason>
                <env:Text xml:lang="en-US">
                    Unknown error
                </env:Text>
            </env:Reason>
            <env:Detail>
                Something unexpected happened, we`ll fix it as soon as possible. Sorry for inconvenience.
            </env:Detail>
        </env:Fault>
    </env:Body>
</env:Envelope>
SOAP_FAULT;
    }
}
