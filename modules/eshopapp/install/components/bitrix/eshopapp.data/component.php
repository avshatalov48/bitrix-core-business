<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActions = Array(
	"barcode",
);

if ($_REQUEST["eshopapp_action"])
{
	if ($_REQUEST["eshopapp_action"] == "login")
	{
		include(dirname(__FILE__)."/actions/login.php");
	}
	else
	{
		header("Content-Type: application/x-javascript");
		$data = Array("error"=>"unknow data request action");
		$action = $_REQUEST["eshopapp_action"];
		if (in_array($action, $arActions))
		{
			switch ($action)
			{
				case "barcode":
					require(dirname(__FILE__)."/actions/barcode.php");
					break;
			}
		}

		$APPLICATION->RestartBuffer();
		echo json_encode($data);
		die();
	}
}

//$this->IncludeComponentTemplate();
?>