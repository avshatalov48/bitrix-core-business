<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$data = Array(
	"logout_status" => "success",
);

if ($_REQUEST["uuid"])
{
	if(CModule::IncludeModule("pull"))
	{
		$dbres = CPullPush::GetList(array(), array("=DEVICE_ID" => $_REQUEST["uuid"]));
		while($arToken = $dbres->Fetch())
		{
			CPullPush::Delete($arToken["ID"]);
			$data["token_status"] = "deleted";
		}
	}
}
$USER->Logout();
?>