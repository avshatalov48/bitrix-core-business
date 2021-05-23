<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("webservice"))
{
	return;
}

class CCheckAuthWS extends IWebService
{
	function CheckAuthorization($user, $password)
	{
		$UserAuthTry = new CUser();
		$authTry = $UserAuthTry->Login($user, $password);
		if ($authTry === true)
		{
			$unode = $UserAuthTry->GetByLogin($user);
			$uinfo = $unode->Fetch();
			return $uinfo;
		}

		return new CSOAPFault( 'Server Error', 'Unable to authorize user.' );
	}

	function GetHTTPUserInfo()
	{
		global $USER;
		if (!$USER->IsAdmin())
			$USER->RequiredHTTPAuthBasic();
		else
		{
			$authId = $USER->GetID();
			$unode = $USER->GetById($authId);
			$uinfo = $unode->Fetch();
			return $uinfo;
		}
		return new CSOAPFault( 'Server Error', 'Unable to authorize user.' );

	}

	function GetWebServiceDesc()
	{
		$wsdesc = new CWebServiceDesc();
		$wsdesc->wsname = "bitrix.webservice.checkauth";
		$wsdesc->wsclassname = "CCheckAuthWS";
		$wsdesc->wsdlauto = true;
		$wsdesc->wsendpoint = CWebService::GetDefaultEndpoint();
		$wsdesc->wstargetns = CWebService::GetDefaultTargetNS();

		$wsdesc->classTypes = array();
		$wsdesc->structTypes["CUser"] =
		array(
			"ID" => array("varType" => "integer"),
			"NAME" => array("varType" => "string"),
			"TIMESTAMP_X" => array("varType" => "string"),
			"LOGIN" => array("varType" => "string"),
			"PASSWORD" => array("varType" => "string"),
			"CHECKWORD" => array("varType" => "string"),
			"ACTIVE" => array("varType" => "string"),
			"LAST_NAME" => array("varType" => "string"),
			"EMAIL" => array("varType" => "string")
		);

		$wsdesc->classes = array(
			"CCheckAuthWS" => array(
				"CheckAuthorization" => array(
					"type"		=> "public",
					"name"		=> "CheckAuthorization",
					"input"		=> array(
						"user" =>array("varType" => "string"),
						"password" =>array("varType" => "string")),
					"output"	=> array(
						"user" => array("varType" => "CUser")
						)
				),
			"GetHTTPUserInfo" => array(
					"type"		=> "public",
					"name"		=> "GetHTTPUserInfo",
					"input"		=> array(),
					"output"	=> array(
						"user" => array("varType" => "CUser")
						),
					"httpauth" => "Y"
				)
			)
		);

		return $wsdesc;
	}

	function TestComponent()
	{
		global $APPLICATION;
		$client = new CSOAPClient( "bitrix.soap", $APPLICATION->GetCurPage() );
		$client->setLogin("admin");
		$client->setPassword("123456");
		$request = new CSOAPRequest( "GetHTTPUserInfo", CWebService::GetDefaultTargetNS() );
		//$request->addParameter("stub", 0);
		$response = $client->send( $request );
		if ($response->FaultString)
			echo $response->FaultString;
		else
			echo "Call GetHTTPUserInfo(): <br>".mydump($response->Value)."<br>";

	}
}

$arParams["WEBSERVICE_NAME"] = "bitrix.webservice.checkauth";
$arParams["WEBSERVICE_CLASS"] = "CCheckAuthWS";
$arParams["WEBSERVICE_MODULE"] = "";

//TestWSDocumentService();
/*
$research = new CMSSOAPResearch();

$research->provider_id = '{XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX}';
$research->service_id = '{XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX}';
$research->add_tittle = "";
$research->query_path = "http://{$_SERVER[HTTP_HOST]}/ws/wscauth.php";
$research->registration_path = "http://{$_SERVER[HTTP_HOST]}/ws/wscauth.php";

$arParams["SOAPSERVER_RESPONSER"] = array( &$research );
*/

$APPLICATION->IncludeComponent(
	"bitrix:webservice.server",
	"",
	$arParams
	);

die();

?>
