<?php

class CSOAPRequest extends CSOAPEnvelope
{
    /// The request name
    var $Name;

    /// The request target namespace
    var $Namespace;

	/// Headers
	var $Headers = array();

    /// Additional body element attributes.
    var $BodyAttributes = array();

    /// Contains the request parameters
    var $Parameters = array(); 
	
    function CSOAPRequest( $name="", $namespace="", $parameters = array() )
    {
        $this->Name = $name;
        $this->Namespace = $namespace;

        // call the parents constructor
        $this->CSOAPEnvelope();

        foreach( $parameters as $name => $value )
        {
            $this->addParameter( $name, $value );
        }
    }

    function name()
    {
        return $this->Name;
    }

	function get_namespace()
    {
        return $this->Namespace;
    }
	
	function GetSOAPAction($separator = '/')
	{			
		if ($this->Namespace[strlen($this->Namespace)-1] != $separator)
		{
			return $this->Namespace . $separator . $this->Name;
		}
		return $this->Namespace . $this->Name;
	}
    
    function addSOAPHeader( $name, $value )
    {
    	$this->Headers[] = CXMLCreator::encodeValueLight($name, $value);
    }

	//     Adds a new attribute to the body element.
    function addBodyAttribute( $name, $value )
    {
        $this->BodyAttributes[$name] = $value;
    }
	
	//      Adds a new parameter to the request. You have to provide a prameter name
	//      and value.
    function addParameter( $name, $value )
    {
        $this->Parameters[$name] = $value;        
    }
    
	//      Returns the request payload
    function payload()
    {
        $root = new CXMLCreator( "soap:Envelope" );
        $root->setAttribute("xmlns:soap", BX_SOAP_ENV);

        $root->setAttribute( BX_SOAP_XSI_PREFIX, BX_SOAP_SCHEMA_INSTANCE );
        $root->setAttribute( BX_SOAP_XSD_PREFIX, BX_SOAP_SCHEMA_DATA );
        $root->setAttribute( BX_SOAP_ENC_PREFIX, BX_SOAP_ENC );

		$header = new CXMLCreator( "soap:Header" );
		$root->addChild( $header );
		
		foreach ($this->Headers as $hx)
			$header->addChild($hx);

        // add the body
        $body = new CXMLCreator( "soap:Body" );
        
        foreach( $this->BodyAttributes as $attribute => $value)
        {
            $body->setAttribute( $attribute, $value );
        }

        // add the request
        $request = new CXMLCreator( $this->Name );
        $request->setAttribute("xmlns", $this->Namespace);

        // add the request parameters
        $param = null;
        foreach ( $this->Parameters as $parameter => $value )
        {
            unset( $param );
            $param = CXMLCreator::encodeValueLight( $parameter, $value );

            if ( $param == false )
                ShowError( "Error enconding data for payload" );
            $request->addChild( $param );
        }

        $body->addChild( $request );
        $root->addChild( $body );
        return CXMLCreator::getXMLHeader().$root->getXML();
    }
}

?>