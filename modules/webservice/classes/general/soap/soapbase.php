<?php

define("BX_SOAP_ENV", "http://schemas.xmlsoap.org/soap/envelope/");
define("BX_SOAP_ENC", "http://schemas.xmlsoap.org/soap/encoding/");
define("BX_SOAP_SCHEMA_INSTANCE", "http://www.w3.org/2001/XMLSchema-instance");
define("BX_SOAP_SCHEMA_DATA", "http://www.w3.org/2001/XMLSchema");

define("BX_SOAP_ENV_PREFIX", "SOAP-ENV");
define("BX_SOAP_ENC_PREFIX", "SOAP-ENC");
define("BX_SOAP_XSI_PREFIX", "xsi");
define("BX_SOAP_XSD_PREFIX", "xsd");

define("BX_SOAP_INT", 1);
define("BX_SOAP_STRING", 2);

class CSOAPHeader 
{
	var $Headers = array ();

	function CSOAPHeader() 
	{

	}

	function addHeader() 
	{

	}
}

class CSOAPBody 
{
	function CSOAPBody() 
	{

	}
}

class CSOAPEnvelope 
{
	var $Header;
	var $Body;

	function CSOAPEnvelope() 
	{
		$this->Header = new CSOAPHeader();
		$this->Body = new CSOAPBody();
	}
}

class CSOAPParameter
{
    var $Name;
    var $Value;
    
    function CSOAPParameter( $name, $value)
    {
        $this->Name = $name;
        $this->Value = $value;
    }

    function setName( $name )
    {
        $this->Name = $name;
    }

    function name()
    {
        return $this->Name;
    }

    function setValue( $value )
    {

    }
    function value()
    {
        return $this->Value;
    }
}

class CSOAPFault 
{
	var $FaultCode;
	var $FaultString;
	var $detail;
	
	function CSOAPFault($faultCode = "", $faultString = "", $detail = '') {
		$this->FaultCode = $faultCode;
		$this->FaultString = $faultString;
		$this->detail = $detail;
	}

	function faultCode() {
		return $this->FaultCode;
	}

	function faultString() {
		return $this->FaultString;
	}

	function detail() {
		return $this->detail;
	}
}

?>
