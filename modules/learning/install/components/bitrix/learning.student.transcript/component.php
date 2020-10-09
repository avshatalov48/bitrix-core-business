<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}

$arParams["TRANSCRIPT_ID"] = (isset($arParams["TRANSCRIPT_ID"]) ? $arParams["TRANSCRIPT_ID"] : $_REQUEST["TRANSCRIPT_ID"]);

if (!preg_match("~^([0-9]+)\-([0-9]+)$~", $arParams["TRANSCRIPT_ID"], $match))
{
	ShowError(GetMessage("LEARNING_TRANSCRIPT_NOT_FOUND"));
	return;
}

$TRANSCRIPT = $match[1];
$USER_ID = $match[2];

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat() : $arParams["NAME_TEMPLATE"];

//Student exists?
$res = CStudent::GetList(Array(), Array("USER_ID" => $USER_ID, "TRANSCRIPT" => $TRANSCRIPT));
if (!$arStudent = $res->GetNext())
{
	ShowError(GetMessage("LEARNING_TRANSCRIPT_NOT_FOUND"));
	return;
}
//Can view transcript?
if($arStudent["PUBLIC_PROFILE"]=="N" && !($USER->GetID() == $arStudent["USER_ID"] || $USER->IsAdmin()) )
{
	ShowError(GetMessage("LEARNING_TRANSCRIPT_PERMISSION_DENIED"));
	return;
}
//User exists?
$res = CUser::GetByID($arStudent["USER_ID"]);
if (!$arUser = $res->GetNext())
{
	ShowError(GetMessage("LEARNING_TRANSCRIPT_ERROR"));
	return;
}

//Images
$arUser["PERSONAL_PHOTO_ARRAY"] = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
$arUser["WORK_LOGO_ARRAY"] = CFile::GetFileArray($arUser["WORK_LOGO"]);

//Country
$arUser["PERSONAL_COUNTRY_NAME"] = GetCountryByID($arUser["PERSONAL_COUNTRY"]);

$arResult = Array(
	"STUDENT" => $arStudent,
	"USER" => $arUser,
	"CERTIFICATES" => Array(),
);

$res = CCertification::GetList(
	Array(
		"SORT" => "ASC", 
		"DATE_CREATE" => "ASC"
	), 
	Array(
		"STUDENT_ID" => $arStudent["USER_ID"], 
		"ACTIVE" => "Y", 
		"PUBLIC" => "Y",
		"CHECK_PERMISSIONS" => "N"
	)
);

while ($arCertification = $res->GetNext())
{
	$arCertification["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arCertification["PREVIEW_PICTURE"]);
	$arResult["CERTIFICATES"][] = $arCertification;
}

unset($res);
unset($arStudent);
unset($arUser);

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
{
	if($arResult["USER"]["LAST_NAME"] <> '' || $arResult["USER"]["NAME"] <> '')
		$APPLICATION->SetTitle(CUser::FormatName($arParams["NAME_TEMPLATE"], $arResult["USER"]));
	else
		$APPLICATION->SetTitle($arResult["USER"]["LOGIN"]);
}

$this->IncludeComponentTemplate();
?>