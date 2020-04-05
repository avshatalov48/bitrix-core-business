<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!$USER->IsAuthorized())
{
	echo json_encode(Array("status"=>"failed"));
	die();
}

if($_REQUEST["api_version"])
{
	$APPLICATION->set_cookie("MOBILE_APP_VERSION", intval($_REQUEST["api_version"]), time()+60*60*24*30*12*2);
	$api_version = intval($_REQUEST["api_version"]);
}
else
{
	$api_version = $APPLICATION->get_cookie("MOBILE_APP_VERSION");
	if(!$api_version)
		$api_version = 1;
}

$APPLICATION->SetPageProperty("api_version", $api_version);

$mobileAction = isset($_REQUEST["mobile_action"]) ? $_REQUEST["mobile_action"] : '';

if($mobileAction != '')
{
	header("Content-Type: application/x-javascript");
	$data = Array("error"=>"unknow data request action");

	switch ($mobileAction)
	{
		case "checkout": //this is authorization checkout, !do not delete!
			include(dirname(__FILE__)."/actions/checkout.php");
			break;
		case "logout":
			include(dirname(__FILE__)."/actions/logout.php");
			break;
		case "save_device_token":
			include(dirname(__FILE__)."/actions/save_device_token.php");
			break;
	}

	echo json_encode($data);
	die();
}

$this->IncludeComponentTemplate();

?>