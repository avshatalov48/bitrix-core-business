<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$data = array(
	"status" => "failed",
);

if ($USER->IsAuthorized())
	$data = array(
		"status" => "success",
		"target" => md5($USER->GetID() . CMain::GetServerUniqID()),
		"sessid_md5" => bitrix_sessid(),
		"appmap" => Array(
			"main" => Array("url" => $arParams["START_PAGE"]),
			"menu" => Array("url" => $arParams["MENU_PAGE"])
		)
	);
?>