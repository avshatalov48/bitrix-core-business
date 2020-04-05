<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("webservice"))
{
	return;
}

/*************************************************************************
	Processing of received parameters
*************************************************************************/

$bDesignMode = $GLOBALS["APPLICATION"]->GetShowIncludeAreas() && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();
$GLOBALS["APPLICATION"]->SetShowIncludeAreas($bDesignMode);

if (is_array($arParams["SOAPSERVER_RESPONSER"]))
{
	// Raw SOAP Server processing.
	if ($_SERVER["REQUEST_METHOD"] == "POST" and !isset($_REQUEST["directcall"]))
	{
		$server = new CSOAPServer();

		for ($i = 0; $i<count($arParams["SOAPSERVER_RESPONSER"]); $i++)
		{
			$server->AddServerResponser($arParams["SOAPSERVER_RESPONSER"][$i]);
		}

		$result = $server->ProcessRequest();
	}
	else
	{
		echo "<img src=\"/bitrix/components/bitrix/webservice.server/images/ws.server.gif\">";
	}

	if(!$bDesignMode)
		die();
}
else if($bDesignMode)
{
	if(!class_exists($arParams["WEBSERVICE_CLASS"]))
		CModule::IncludeModule($arParams["WEBSERVICE_MODULE"]);

	$arParams["WSDESCR"] = CWebService::GetWebServiceDeclaration($arParams["WEBSERVICE_CLASS"]);
	$this->IncludeComponentTemplate();
}
else
{
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");

	if(!class_exists($arParams["WEBSERVICE_CLASS"])
		&& (empty($arParams["WEBSERVICE_MODULE"]) || !CModule::IncludeModule($arParams["WEBSERVICE_MODULE"]))
	)
	{
		return;
	}

	CWebService::SetComponentContext($arParams);
	CWebService::RegisterWebService($arParams["WEBSERVICE_CLASS"]);

	if (isset($_GET["wsdl"]))
	{
		header("Content-Type: text/xml");
		echo CWebService::GetWSDL($arParams["WEBSERVICE_NAME"]);
		die();
	}
	else if (isset($_GET["test"]))
	{
		echo CWebService::TestComponent($arParams["WEBSERVICE_NAME"]);
		die();
	}
	else if ($_SERVER["REQUEST_METHOD"] == "POST" and !isset($_REQUEST["directcall"]))
	{
		CWebService::SOAPServerProcessRequest($arParams["WEBSERVICE_NAME"]);
	}
	else
	{
		$arParams["WSDESCR"] = CWebService::GetWebServiceDeclaration($arParams["WEBSERVICE_NAME"]);
		$this->IncludeComponentTemplate();
	}

	//die();
}
?>