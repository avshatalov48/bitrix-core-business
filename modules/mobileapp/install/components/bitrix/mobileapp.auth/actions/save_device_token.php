<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$data = Array(
	"status" => "failed",
);

$data = $_POST;
if ($USER->IsAuthorized() && $_REQUEST["device_token"])
{
	$token = $_REQUEST["device_token"];
	$uuid = $_REQUEST["uuid"];
	$data = array(
		"register_token" => "fail",
		"token" => $token,
		"user_id" => $USER->GetID()
	);

	if(CModule::IncludeModule("pull"))
	{
		$dbres = CPullPush::GetList(Array(),Array("DEVICE_ID" => $uuid));
		$arToken = $dbres->Fetch();

		$arFields = Array(
			"USER_ID" => $USER->GetID(),
			"DEVICE_NAME" => $_REQUEST["device_name"],
			"DEVICE_TYPE" =>  $_REQUEST["device_type"],
			"DEVICE_ID" => $_REQUEST["uuid"],
			"DEVICE_TOKEN" => $token
		);

		if($arToken["ID"])
		{
			$res = CPullPush::Update($arToken["ID"],$arFields);
			$data["register_token"] = "updated";
		}
		else
		{
			$res = CPullPush::Add($arFields);
			if($res)
				$data["register_token"] = "created";
		}
	}
}
?>