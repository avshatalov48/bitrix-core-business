<?php

$wsdescs = array();
$wswraps = array();

$componentContext = array();

class CWebServiceDesc
{
	var $wsname;		// webservice name
	var $wsclassname;	// webservice class wrapper (i-face implementor) name
	var $wsdlauto;		// boolean, automatic generating wsdl

	var $wstargetns;	// target namespace
	var $wsendpoint;

	/*
	 * Return info about $reflect method, class, para,
	 * For method:
	 * 	classname=>method
	 *
	 * 	must contain array(
	 * 		"name"	=> string,
	 * 		"documentation" => "Description"
	 * 		"input" => array(..),
	 * 		"output" => array(..)
	 * 		)
	 * 	Input in method: list of para's.
	 * 		array(
	 * 			"name" => "type",
	 * 			...
	 * 		)
	 * 	Output in method:
	 * 		1) "simpleType" => "type"
	 * 		2) "complexType" => array[] of structModuleSoapRetData declared by output array.
	 * */
	var $classes;		// class methods for soap

	/*
	 * On next two, syntax same as for $classes, but:
	 *
	 */
	var $structTypes;	// complex assoc array data types.
	var $classTypes;	// complex class data struct. when soaped. must be unserialized to class.

	////////////////////// registrating
	var $_wsdlci;		// wsdlcreator instance class
	var $_soapsi;		// soap server instance class
}

class IWebService
{
	// May be called by Event to collect CWebServiceDesc on configuring WS.Server
	public static function GetWebServiceDesc() {}

	//function TestComponent() {}

	/*
	 * Web Service methods must have ws prefix in there names and
	 * they have to be serviced by ReflectService function to generate
	 * valid wsdl code, binding and other.
	 * Example:
	 * 		wsGetUserInfo();
	 * */
}

class CWebService
{
	public static function SetComponentContext($arParams)
	{
		if (is_array($arParams))
			$GLOBALS["componentContext"] = $arParams;
	}

	public static function GetComponentContext($arParams)
	{
		if (is_array($GLOBALS["componentContext"]))
			return $GLOBALS["componentContext"];

		return false;
	}

	public static function SOAPServerProcessRequest($wsname)
	{
		if (!isset($GLOBALS["wsdescs"][$wsname]) or
			!$GLOBALS["wsdescs"][$wsname] or
			!$GLOBALS["wsdescs"][$wsname]->_soapsi)
			return false;

		return $GLOBALS["wsdescs"][$wsname]->_soapsi->ProcessRequest();
	}

	public static function RegisterWebService($className /*IWebService implementor*/)
	{
		$ifce =& CWebService::GetInterface($className);
		if (!is_object($ifce)) return false;

		$wsHandler = $ifce->GetWebServiceDesc();
		if (!$wsHandler or
			isset($GLOBALS["wsdescs"][$wsHandler->wsname])
			or !$wsHandler->wsname
			or !$wsHandler->wsclassname
			or !$wsHandler->wstargetns
			or !$wsHandler->wsendpoint
			or !is_array($wsHandler->classes)
			or !is_array($wsHandler->structTypes)
			or !is_array($wsHandler->classTypes))
			return false;

		if (isset($GLOBALS["wsdescs"][$wsHandler->wsname]))
			return false;

		$wsHandler->_wsdlci = new CWSDLCreator(
			$wsHandler->wsname,
			$wsHandler->wsendpoint,
			$wsHandler->wstargetns);

		$wsHandler->_wsdlci->setClasses($wsHandler->classes);
		if (count($wsHandler->structTypes))
			foreach ($wsHandler->structTypes as $pname => $vars)
				$wsHandler->_wsdlci->AddComplexDataType($pname, $vars);
		if (count($wsHandler->classTypes))
			foreach ($wsHandler->classTypes as $pname => $vars)
				$wsHandler->_wsdlci->AddComplexDataType($pname, $vars);
		$wsHandler->_wsdlci->createWSDL();

		$wsHandler->_soapsi = new CSOAPServer();

		$soapr = new CWSSOAPResponser();

		foreach ($wsHandler->structTypes as $cTypeN => $desc)
		{
			$tdesc = $desc;
			$tdesc["serialize"] = "assoc";
			$soapr->RegisterComplexType(
				array($cTypeN => $tdesc)
			);
		}

		foreach ($wsHandler->classTypes as $cTypeN => $desc)
		{
			$tdesc = $desc;
			$tdesc["serialize"] = "class";
			$soapr->RegisterComplexType(
				array($cTypeN => $tdesc)
			);
		}

		foreach ($wsHandler->classes as $classws => $methods)
		foreach ($methods as $method => $param)
		{
			if (isset($param["httpauth"]))
				$httprequired = $param["httpauth"];
			if ($httprequired!="Y")
				$httprequired = "N";

			$input = array(); if (is_array($param["input"]))
				$input = $param["input"];
			$output = array(); if (is_array($param["output"]))
				$output = $param["output"];

			$soapr->RegisterFunction(
					$method,
					array(
						"input" => $input,
						"output" => $output,
						"myclassname" => $classws,
						"request" => $method,
						"response" => $method."Response",
						"httpauth" => $httprequired
					)
				);


		}

		$wsHandler->_soapsi->AddServerResponser($soapr);

		$GLOBALS["wsdescs"][$wsHandler->wsname] = &$wsHandler;
		$GLOBALS["wswraps"][$wsHandler->wsname] = &$ifce;

		return true;
	}

	public static function GetSOAPServerRequest($wsname)
	{
		if (isset($GLOBALS["wsdescs"][$wsname]) and
			$GLOBALS["wsdescs"][$wsname]->_soapsi)
		{
			return $GLOBALS["wsdescs"][$wsname]->_soapsi->GetRequestData();
		}
		return false;
	}

	public static function GetSOAPServerResponse($wsname)
	{
		if (isset($GLOBALS["wsdescs"][$wsname]))
		{
			return $GLOBALS["wsdescs"][$wsname]->_soapsi->GetResponseData();
		}
		return false;
	}

	public static function MethodRequireHTTPAuth($class, $method)
	{
		global $USER;

		if (!$USER->IsAuthorized())
		{
			\CHTTP::SetAuthHeader(true);
			return false;
		}

		return true;
	}

	public static function TestComponent($wsname)
	{
		if (isset($GLOBALS["wsdescs"][$wsname]))
		{
			$ifce =& CWebService::GetInterface($GLOBALS["wsdescs"][$wsname]->wsclassname);
			if (!is_object($ifce)) return false;
			$ifce->TestComponent();
		}
		return false;
	}

	public static function GetWSDL($wsname)
	{
		if (!isset($GLOBALS["wsdescs"][$wsname]) or
			!$GLOBALS["wsdescs"][$wsname] or
			!$GLOBALS["wsdescs"][$wsname]->_wsdlci)
			return false;
		return $GLOBALS["wsdescs"][$wsname]->_wsdlci->getWSDL();
	}

	public static function GetDefaultEndpoint()
	{
		global $APPLICATION;
		return ($APPLICATION->IsHTTPS() ? "https" : "http")."://".$_SERVER["HTTP_HOST"].
				$APPLICATION->GetCurPage();
	}

	public static function GetDefaultTargetNS()
	{
		global $APPLICATION;

		return ($APPLICATION->IsHTTPS() ? "https" : "http")."://".$_SERVER["HTTP_HOST"]."/";
	}

	public static function &GetWebServiceDeclaration($className)
	{
		if (isset($GLOBALS["wsdescs"][$className])) return $GLOBALS["wsdescs"][$className];
		$ifce =& CWebService::GetInterface($className);
		if (!is_object($ifce)) return false;
		return $ifce->GetWebServiceDesc();
	}

	public static function &GetInterface($className)
	{
		if (isset($GLOBALS["wswraps"][$className])) return $GLOBALS["wswraps"][$className];

		if (!class_exists($className)) return 0;
		//AddMessage2Log(mydump(class_exists($className, true)));
		$ifce = new $className;
		if (!is_subclass_of($ifce, "IWebService")) return 0;
		return $ifce;
	}
}
