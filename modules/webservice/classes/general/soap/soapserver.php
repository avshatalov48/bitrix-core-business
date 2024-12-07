<?php

class CSOAPServerResponser
{
	function OnBeforeRequest(&$cserver)
	{
	}

	/* $cserver->RawPostData */
	function OnAfterResponse(&$cserver)
	{
	}

	/*
	 * If function returns true, chain of ProcessRequest ends.
	 * If function returns true, this means function already passed It's value to ShowResponse handler
	 * If returns false, then next item in a chain will be called.
	 * Result Value must be set to $cserver->ResponseValue 
	 */
	function ProcessRequestHeader(&$cserver, $header)
	{
	}

	/* stub, never used */
	function ProcessRequestBody(&$cserver, $body)
	{
	}
}

class CWSSOAPResponser extends CSOAPServerResponser
{
	/*
	 * typename => name => array()
	 * funcname => parameters => array()
	 * Array can contain:
	 * 	serialize => class/assoc array.
	 * 	varType, arrType
	 * */
	var $TypensVars;

	/// message => function
	var $MessageTags;

	/// Contains a list over registered functions
	var $FunctionList;

	function RegisterFunction($name, $params = array())
	{
		$this->FunctionList[] = $name;
		$this->TypensVars[$name] = $params;

		if ($params["request"]) $this->MessageTags[$params["request"]] = $name;
		if ($params["response"]) $this->MessageTags[$params["response"]] = $name;
	}

	/*
	 * $complex = array( "typename" => array( paraname => array(type desc, valType)))
	 */
	function RegisterComplexType($complex)
	{
		foreach ($complex as $complexTypeName => $declaration)
		{
			$this->TypensVars[$complexTypeName] = $declaration;
		}
	}

	function ProcessRequestBody(&$cserver, $body)
	{
		$functionName = $body->name();
		$namespaceURI = $body->namespaceURI();
		$requestNode = $body;

		// If this is request name in functionName, get functionName.
		if (!in_array($functionName, $this->FunctionList)
			and isset($this->MessageTags[$functionName])
		)
		{
			$functionName = $this->MessageTags[$functionName];
		}

		if (!in_array($functionName, $this->FunctionList))
		{
			CSOAPServer::ShowSOAPFault("Trying to access unregistered function: ".$functionName);
			return true;
		}

		$objectName = "";
		$params = array();

		$paramsDecoder = new CSOAPResponse($functionName, $namespaceURI);
		$paramsDecoder->setTypensVars($this->TypensVars);

		if (!isset($this->TypensVars[$functionName]) or
			!isset($this->TypensVars[$functionName]["myclassname"]) or
			!isset($this->TypensVars[$functionName]["input"])
		)
		{
			CSOAPServer::ShowSOAPFault("Requested function has no type specified: ".$functionName);
			return true;
		}

		$objectName = $this->TypensVars[$functionName]["myclassname"];
		$inputParams = $this->TypensVars[$functionName]["input"];

		$httpAuth = "N";
		if (isset($this->TypensVars[$functionName]["httpauth"]))
		{
			$httpAuth = $this->TypensVars[$functionName]["httpauth"];
		}

		if ($httpAuth == "Y" and !CWebService::MethodRequireHTTPAuth($objectName, $functionName))
		{
			CSOAPServer::ShowSOAPFault("Requested function requires HTTP Basic Auth to be done before.");
			return true;
		}

		$requestParams = array(); // reorganize params
		foreach ($requestNode->children() as $parameterNode)
		{
			if (!$parameterNode->name())
				continue;
			$requestParams[$parameterNode->name()] = $parameterNode;
		}

		// check parameters/decode // check strict params
		foreach ($inputParams as $pname => $param)
		{
			$decoded = null;

			if (isset($requestParams[$pname]))
			{
				$decoded = $paramsDecoder->decodeDataTypes($requestParams[$pname]);
			}

			if (is_object($decoded) and (get_class($decoded) == "CSOAPFault" or get_class($decoded) == "csoapfault"))
			{
				CSOAPServer::ShowSOAPFault($decoded);
				return true;
			}

			if (
				!isset($decoded) and (!isset($param["strict"])
				or (isset($param["strict"]) and $param["strict"] == "strict"))
			)
			{
				CSOAPServer::ShowSOAPFault("Request has not enough params of strict type to be decoded. ");
				return true;
			}
			$params[] = $decoded;
		}

		unset($paramsDecoder);

		$object = null;

		if (class_exists($objectName))
			$object = new $objectName;

		if (is_object($object) && method_exists($object, $functionName))
		{
			$this->ShowResponse(
				$cserver,
				$functionName,
				$namespaceURI,
				call_user_func_array(
					array($object, $functionName),
					$params
				)
			);
		}
		else if (!class_exists($objectName))
		{
			$this->ShowResponse(
				$cserver,
				$functionName,
				$namespaceURI,
				new CSOAPFault('Server Error', 'Object not found')
			);
		}
		else
		{
			$this->ShowResponse(
				$cserver,
				$functionName,
				$namespaceURI,
				new CSOAPFault('Server Error', 'Method not found')
			);
		}

		return true;
	}

	function ShowResponse(&$cserver, $functionName, $namespaceURI, &$value)
	{
		global $APPLICATION;
		// Convert input data to XML

		$response = new CSOAPResponse($functionName, $namespaceURI);
		$response->setTypensVars($this->TypensVars);

		$response->setValue($value);

		$payload = $response->payload();

		header("SOAPServer: BITRIX SOAP");
		header("Content-Type: text/xml; charset=\"UTF-8\"");
		header("Content-Length: " . strlen($payload));

		$APPLICATION->RestartBuffer();
		$cserver->RawPayloadData = $payload;
		echo $payload;
	}
}

class CSOAPServer
{
	/// Contains the RAW HTTP post data information
	var $RawPostData;
	var $RawPayloadData;

	/// Consists of instances of CSOAPServerResponser
	var $OnRequestEvent = array();

	public function __construct()
	{
		$this->RawPostData = file_get_contents("php://input");
	}

	function GetRequestData()
	{
		return $this->RawPostData;
	}

	function GetResponseData()
	{
		return $this->RawPayloadData;
	}

	function AddServerResponser(&$respobject)
	{
		if (is_subclass_of($respobject, "CSOAPServerResponser"))
		{
			$this->OnRequestEvent[count($this->OnRequestEvent)] =& $respobject;
			return true;
		}

		return false;
	}

	// $valueEncoded type of CXMLCreator
	function ShowRawResponse($valueEncoded, $wrapEnvelope = false)
	{
		global $APPLICATION;

		if ($wrapEnvelope)
		{
			// $valueEncoded class of CXMLCreator
			$root = new CXMLCreator("soap:Envelope");
			$root->setAttribute("xmlns:soap", BX_SOAP_ENV);

			// add the body
			$body = new CXMLCreator("soap:Body");

			$body->addChild($valueEncoded);

			$root->addChild($body);

			$valueEncoded = $root;
		}

		$payload = CXMLCreator::getXMLHeader().$valueEncoded->getXML();

		header("SOAPServer: BITRIX SOAP");
		header("Content-Type: text/xml; charset=\"UTF-8\"");
		header("Content-Length: " . strlen($payload));

		$APPLICATION->RestartBuffer();
		$this->RawPayloadData = $payload;

		echo $payload;
	}

	function ShowResponse($functionName, $namespaceURI, $valueName, &$value)
	{
		global $APPLICATION;
		// Convert input data to XML

		$response = new CSOAPResponse($functionName, $namespaceURI);
		$response->setValueName($valueName);
		$response->setValue($value);

		$payload = $response->payload();

		header("SOAPServer: BITRIX SOAP");
		header("Content-Type: text/xml; charset=\"UTF-8\"");
		header("Content-Length: " . strlen($payload));

		$APPLICATION->RestartBuffer();

		$this->RawPayloadData = $payload;
		echo $payload;
	}

	public static function ShowSOAPFault($errorString)
	{
		global $APPLICATION;
		$response = new CSOAPResponse('unknown_function_name', 'unknown_namespace_uri');
		if (is_object($errorString) and (get_class($errorString) == "CSOAPFault" or get_class($errorString) == "csoapfault"))
			$response->setValue($errorString /*CSOAPFault*/);
		else
			$response->setValue(new CSOAPFault('Server Error', $errorString));

		$payload = $response->payload();

		header("SOAPServer: BITRIX SOAP");
		header("Content-Type: text/xml; charset=\"UTF-8\"");
		header("Content-Length: " . strlen($payload));

		$APPLICATION->RestartBuffer();
		echo $payload;

		die();
	}

	/**
		Processes the SOAP request and prints out the
		propper response.
	*/
	function ProcessRequest()
	{
		if (
			$_SERVER["REQUEST_METHOD"] != "POST"
			||!class_exists("CDataXML")
		)
		{
			$this->ShowSOAPFault("Error: this web page does only understand POST methods. BitrixXMLParser. ");
		}

		for ($i = 0; $i < count($this->OnRequestEvent); $i++)
		{
			$this->OnRequestEvent[$i]->OnBeforeRequest($this);
		}

		//AddMessage2Log($this->RawPostData);
		$xmlData = $this->stripHTTPHeader($this->RawPostData);

		$xml = new CDataXML();

		//AddMessage2Log($xmlData);
		if (!$xml->LoadString($xmlData))
		{
			$this->ShowSOAPFault("Error: Can't parse request xml data. ");
		}

		$dom = $xml->GetTree();

		// Check for non-parsing XML, to avoid call to non-object error.
		if (!is_object($dom))
		{
			$this->ShowSOAPFault("Bad XML");
		}

		// add namespace fetching on body
		// get the SOAP body
		$body = $dom->elementsByName("Body");

		if(count($body) <= 0)
		{
			$this->ShowSOAPFault('No "Body" element in the request');
		}
		else
		{
			$children = $body[0]->children();

			if(count($children) == 1)
			{
				$requestNode = $children[0];
				$requestParsed = false;

				// get target namespace for request
				// it often function request message. in wsdl gen. = function+"request"
				$functionName = $requestNode->name();
				$namespaceURI = $requestNode->namespaceURI();

				for($i = 0; $i < count($this->OnRequestEvent); $i++)
				{
					if($this->OnRequestEvent[$i]->ProcessRequestBody($this, $requestNode))
					{
						$requestParsed = true;
						break;
					}
				}

				for($i = 0; $i < count($this->OnRequestEvent); $i++)
				{
					$this->OnRequestEvent[$i]->OnAfterResponse($this);
				}

				if(!$requestParsed)
				{
					$this->ShowSOAPFault('Unknown operation requested.');
				}

				return $requestParsed;
			}
			else
			{
				$this->ShowSOAPFault('"Body" element in the request has wrong number of children');
			}
		}

		return false;
	}

	function stripHTTPHeader($data)
	{
		//$start = strpos( $data, "<"."?xml" );
		$start = mb_strpos($data, "\r\n\r\n");
		return mb_substr($data, $start, mb_strlen($data) - $start);
	}
}
