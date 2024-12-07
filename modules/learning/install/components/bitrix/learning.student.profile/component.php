<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Authorized?
if (!$USER->IsAuthorized())
{
	$APPLICATION->AuthForm(GetMessage("LEARNING_NO_AUTHORIZE"), false, false, "N", false);
	return;
}

//Module
if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}

//Params
$arParams["TRANSCRIPT_DETAIL_TEMPLATE"] = (
	$arParams["TRANSCRIPT_DETAIL_TEMPLATE"] <> '' ?
	htmlspecialcharsbx($arParams["TRANSCRIPT_DETAIL_TEMPLATE"]) :
	"certification/?TRANSCRIPT_ID=#TRANSCRIPT_ID#"
);

//User
$USER_ID = intval($USER->GetID());
$rsUser = CUser::GetByID($USER_ID);
if (!$arUser = $rsUser->GetNext())
{
	ShowError(GetMessage("LEARNING_NO_AUTHORIZE"));
	return;
}

//Post form
$strError = "";
if ($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["ACTION"]=="EDIT" && $USER_ID > 0 && check_bitrix_sessid())
{
	if ($_POST["EMAIL"] == '')
		$strError .= GetMessage("LEARNING_NO_MAIL")."<br />";
	elseif (!check_email($_POST["EMAIL"]))
		$strError .= GetMessage("LEARNING_BAD_MAIL")."<br />";

	if ($strError == '')
	{
		$rsPhoto = $DB->Query("SELECT PERSONAL_PHOTO FROM b_user WHERE ID='$USER_ID'");
		$arPhoto = $rsPhoto->Fetch();
		$arPersonPhoto = $_FILES["PERSONAL_PHOTO"] ?? [];
		$arPersonPhoto["old_file"] = $arPhoto["PERSONAL_PHOTO"] ?? '';
		$arPersonPhoto["del"] = $_POST["PERSONAL_PHOTO_del"] ?? '';

		$arFields = Array(
			"NAME"					=> $_POST["NAME"] ?? '',
			"LAST_NAME"				=> $_POST["LAST_NAME"] ?? '',
			"EMAIL"					=> $_POST["EMAIL"] ?? '',
			"PERSONAL_WWW"			=> $_POST["PERSONAL_WWW"] ?? '',
			"PERSONAL_ICQ"			=> $_POST["PERSONAL_ICQ"] ?? '',
			"PERSONAL_STREET"			=> $_POST["PERSONAL_STREET"] ?? '',
			"PERSONAL_CITY"		=> $_POST["PERSONAL_CITY"] ?? '',
			"PERSONAL_ZIP"		=> $_POST["PERSONAL_ZIP"] ?? '',
			"PERSONAL_STATE"		=> $_POST["PERSONAL_STATE"] ?? '',
			"PERSONAL_COUNTRY"	=> $_POST["PERSONAL_COUNTRY"] ?? '',
			"PERSONAL_PHOTO"		=> $arPersonPhoto,
		);

		$success = $USER->Update($USER_ID, $arFields);
		if (!$success)
			$strError .= $USER->LAST_ERROR."<br />";
	}

	if ($strError == '')
	{
		$arStudentFields = Array(
			"RESUME" => $_POST["RESUME"] ?? '',
			"PUBLIC_PROFILE" => (isset($_POST["PUBLIC_PROFILE"]) && $_POST["PUBLIC_PROFILE"]=="Y" ? "Y" : "N")
		);

		$rsStudent = CStudent::GetList(Array(), Array("USER_ID" => $USER_ID));
		if ($arStudent = $rsStudent->Fetch())
			$success = CStudent::Update($USER_ID, $arStudentFields);
		else
		{
			$arStudentFields["USER_ID"] = $USER_ID;
			$STUDENT_USER_ID = CStudent::Add($arStudentFields);
			$success = (intval($STUDENT_USER_ID)>0);
		}

		if($success)
			LocalRedirect($APPLICATION->GetCurPage());
		else
		{
			if ($e = $APPLICATION->GetException())
				$strError = $e->GetString();
		}

	}
}

//Images
$arUser["PERSONAL_PHOTO_ARRAY"] = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
$arUser["WORK_LOGO_ARRAY"] = CFile::GetFileArray($arUser["WORK_LOGO"]);

//Countries
$arUser["PERSONAL_COUNTRY_ARRAY"] = GetCountryArray();

//arResult
$arResult = Array(
	"USER" => $arUser,
	"STUDENT" => Array(),
	"TRANSCRIPT_DETAIL_URL" => "",
	"CURRENT_PAGE" => $APPLICATION->GetCurPage(),
	"ERROR_MESSAGE" => $strError
);

//Student
$rsStudent = CStudent::GetList(array(), array("USER_ID" => $USER_ID));
if ($arStudent = $rsStudent->GetNext())
{
	$arResult["STUDENT"] = $arStudent;
	$arResult["TRANSCRIPT_DETAIL_URL"] = CComponentEngine::MakePathFromTemplate(
		$arParams["TRANSCRIPT_DETAIL_TEMPLATE"],
		Array("TRANSCRIPT_ID" => $arStudent["TRANSCRIPT"]."-".$arStudent["USER_ID"])
	);
}


//If post and error occured
if ($_SERVER["REQUEST_METHOD"]=="POST" && $arResult["ERROR_MESSAGE"] <> '')
{
	$arUserFields = $DB->GetTableFieldsList("b_user");
	foreach ($arUserFields as $field)
		if (array_key_exists($field, $_REQUEST))
			$arResult["USER"][$field] = htmlspecialcharsbx($_REQUEST[$field]);

	$arUserFields = $DB->GetTableFieldsList("b_learn_student");
	foreach ($arUserFields as $field)
		if (array_key_exists($field, $_REQUEST))
			$arResult["STUDENT"][$field] = htmlspecialcharsbx($_REQUEST[$field]);
}

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("LEARNING_PROFILE_TITLE"));

$this->IncludeComponentTemplate();
?>
