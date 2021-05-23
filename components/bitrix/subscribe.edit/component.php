<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);

if(!CModule::IncludeModule("subscribe"))
{
	ShowError(GetMessage("SUBSCR_MODULE_NOT_INSTALLED"));
	return;
}

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

if($arParams["ALLOW_ANONYMOUS"]!="N")
	$arParams["ALLOW_ANONYMOUS"] = COption::GetOptionString("subscribe", "allow_anonymous", "Y");
if($arParams["ALLOW_ANONYMOUS"]!="N")
	$arParams["ALLOW_ANONYMOUS"] = "Y";
if($arParams["SHOW_AUTH_LINKS"]!="N")
	$arParams["SHOW_AUTH_LINKS"] = COption::GetOptionString("subscribe", "show_auth_links", "Y");
if($arParams["SHOW_AUTH_LINKS"]!="N")
	$arParams["SHOW_AUTH_LINKS"] = "Y";
if($arParams["SHOW_HIDDEN"]!="Y")
	$arParams["SHOW_HIDDEN"] = "N";
if($arParams["SET_TITLE"]!="N")
	$arParams["SET_TITLE"] = "Y";
$_REQUEST["CONFIRM_CODE"] = trim($_REQUEST["CONFIRM_CODE"]);

//options
$bAllowRegister = (COption::GetOptionString("main", "new_user_registration") == "Y");
$sLastLogin = ${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"};

$ID = intval($_REQUEST["ID"]); // Id of the subscription
//onscreen messages about actions
$aMsg = array(
	"UPD"=>GetMessage("adm_upd_mess"),
	"SENT"=>GetMessage("adm_sent_mess"),
	"SENTPASS"=>GetMessage("subscr_pass_mess"),
	"CONF"=>GetMessage("adm_conf_mess"),
	"UNSUBSCR"=>GetMessage("adm_unsubscr_mess"),
	"ACTIVE"=>GetMessage("subscr_active_mess")
);
if(array_key_exists($_REQUEST["mess_code"], $aMsg))
	$iMsg = $_REQUEST["mess_code"];
else
	$iMsg = "";

$obSubscription = new CSubscription;

//*************************
//settings form processing
//*************************
$arWarning = array();
$bVarsFromForm = false;
if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["PostAction"]) && check_bitrix_sessid())
{
	$bDoSubscribe = true;
	$bVarsFromForm = true;

	if(!empty($_REQUEST["LOGIN"]))
	{
		//authorize the user
		$res = $USER->Login($_REQUEST["LOGIN"], $_REQUEST["PASSWORD"]);
		if($res["TYPE"] == "ERROR")
			$arWarning[] = $res["MESSAGE"];
		else
			$bDoSubscribe = false;
	}
	elseif($bAllowRegister && !empty($_REQUEST["NEW_LOGIN"]))
	{
		//new user
		$res = $USER->Register($_REQUEST["NEW_LOGIN"], "", "", $_REQUEST["NEW_PASSWORD"], $_REQUEST["CONFIRM_PASSWORD"], $_REQUEST["EMAIL"], false, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
		if($res["TYPE"] == "ERROR")
			$arWarning[] = $res["MESSAGE"];
		else
			$bDoSubscribe = false;
	}

	//if anonymous users are not permitted then the user must be authorized
	if($arParams["ALLOW_ANONYMOUS"]=="N" && !$USER->IsAuthorized())
		$arWarning[] = GetMessage("adm_auth_err");

	//there must be at least one newsletter category
	if(!is_array($_REQUEST["RUB_ID"]) || count($_REQUEST["RUB_ID"]) == 0)
		$arWarning[] = GetMessage("adm_auth_err_rub");
	elseif($arParams["SHOW_HIDDEN"]=="N") //check for hidden categories
	{
		$bAllowSubscription=true;
		foreach($_REQUEST["RUB_ID"] as $rub_id)
		{
			$rsRubric = CRubric::GetByID($rub_id);
			if($arRubric = $rsRubric->Fetch())
				if($arRubric["VISIBLE"]=="N")
					$bAllowSubscription=false;
		}
		if($bAllowSubscription===false)
			$arWarning[] = GetMessage("subscr_wrong_rubric");
	}

	if(count($arWarning)<=0 && $bDoSubscribe)
	{
		//Check if subscription already have hidden rubrics and they was not displayed.
		//In this case we will add those categories to the list in order not to lost.
		if(($arParams["SHOW_HIDDEN"] == "N") && ($ID > 0))
		{
			$arNewRubrics = $_REQUEST["RUB_ID"];
			$rsRubric = CSubscription::GetRubricList($ID);
			while($ar = $rsRubric->Fetch())
			{
				if($ar["VISIBLE"] == "N")
					$arNewRubrics[] = $ar["ID"];
			}
		}
		else
		{
			$arNewRubrics = $_REQUEST["RUB_ID"];
		}

		$arFields = Array(
			"USER_ID" => ($USER->IsAuthorized()? $USER->GetID():false),
			"FORMAT" => ($_REQUEST["FORMAT"] <> "html"? "text":"html"),
			"EMAIL" => $_REQUEST["EMAIL"],
			"RUB_ID" => $arNewRubrics,
		);

		if($_REQUEST["CONFIRM_CODE"] <> "" && $ID > 0)
			$arFields["CONFIRM_CODE"] = $_REQUEST["CONFIRM_CODE"];

		$res = false;
		if($ID>0)
		{
			//allow edit only after authorization
			if(CSubscription::IsAuthorized($ID))
			{
				$res = $obSubscription->Update($ID, $arFields);
				if($res)
					$iMsg = ($obSubscription->LAST_MESSAGE<>""? $obSubscription->LAST_MESSAGE:"UPD");
			}
		}
		else
		{
			//can add without authorization
			$arFields["ACTIVE"] = "Y";
			$ID = $obSubscription->Add($arFields);
			$res = ($ID>0);
			if($res)
			{
				$iMsg = "SENT";
				CSubscription::Authorize($ID);
			}
		}

		if($res)
		{
			//remember e-mail in cookies
			$bVarsFromForm = false;
			$APPLICATION->set_cookie("SUBSCR_EMAIL", $_REQUEST["EMAIL"], mktime(0,0,0,12,31,2030));
			LocalRedirect($APPLICATION->GetCurPage()."?ID=".$ID.($iMsg <> ""? "&mess_code=".urlencode($iMsg):""));
		}
		else
			$arWarning[] = $obSubscription->LAST_ERROR;
	}//$arWarning
}//POST

//new or existing subscription?
//ID==0 indicates new subscription
if($_REQUEST["sf_EMAIL"] <> '' || $ID > 0 || $USER->IsAuthorized())
{
	if($ID > 0)
		$rsSubscription = CSubscription::GetByID($ID);
	elseif($_REQUEST["sf_EMAIL"] <> '')
		$rsSubscription = CSubscription::GetByEmail($_REQUEST["sf_EMAIL"], intval($USER->GetID()));
	else
		$rsSubscription = CSubscription::GetList(array(), array("USER_ID" => $USER->GetID()));

	if($arSubscription = $rsSubscription->GetNext())
		$ID = intval($arSubscription["ID"]);
	else
		$ID = 0;
}
else
	$ID = 0;

//try to authorize subscription by CONFIRM_CODE or user password AUTH_PASS
if($ID > 0 && !CSubscription::IsAuthorized($ID))
{
	if($arSubscription["USER_ID"] > 0 && !empty($_REQUEST["AUTH_PASS"]))
	{
		//trying to login user
		$rsUser = CUser::GetByID($arSubscription["USER_ID"]);
		if(($arUser = $rsUser->Fetch()))
		{
			$res = $USER->Login($arUser["LOGIN"], $_REQUEST["AUTH_PASS"]);
			if($res["TYPE"] == "ERROR")
				$arWarning[] = $res["MESSAGE"];
		}
	}
	CSubscription::Authorize($ID, (empty($_REQUEST["AUTH_PASS"])? $_REQUEST["CONFIRM_CODE"]:$_REQUEST["AUTH_PASS"]));
}

//confirmation code from letter or confirmation form
if($_REQUEST["CONFIRM_CODE"] <> "" && $ID > 0 && empty($_REQUEST["action"]))
{
	if($arSubscription["CONFIRMED"] <> "Y" && count($arWarning)==0)
	{
		//subscribtion confirmation
		if($obSubscription->Update($ID, array("CONFIRM_CODE"=>$_REQUEST["CONFIRM_CODE"])))
			$arSubscription["CONFIRMED"] = "Y";
		if($obSubscription->LAST_ERROR<>"")
			$arWarning[] = $obSubscription->LAST_ERROR;
		$iMsg = $obSubscription->LAST_MESSAGE;
	}
}

//*************************
//form actions processing
//*************************
if($ID > 0 && (($_REQUEST["action"] == "unsubscribe") || check_bitrix_sessid()))
{
	//confirmation code request
	switch($_REQUEST["action"])
	{
	case "sendcode":
		if(CSubscription::ConfirmEvent($ID))
			$iMsg = "SENT";
		break;
	case "sendpassword":
		if(intval($arSubscription["USER_ID"]) == 0)
		{
			//anonymous subscription
			if(CSubscription::ConfirmEvent($ID))
				$iMsg = "SENT";
		}
		else
		{
			//user account subscription
			CUser::SendUserInfo($arSubscription["USER_ID"], LANG, GetMessage("subscr_send_pass_mess"), true);
			$iMsg = "SENTPASS";
			LocalRedirect($APPLICATION->GetCurPage()."?sf_EMAIL=".urlencode($_REQUEST["sf_EMAIL"])."&change_password=yes&mess_code=".urlencode($iMsg));
		}
		break;
	case "unsubscribe":
		if(CSubscription::IsAuthorized($ID))
		{
			//unsubscription
			if($obSubscription->Update($ID, array("ACTIVE"=>"N")))
			{
				$arSubscription["ACTIVE"] = "N";
				$iMsg = "UNSUBSCR";
			}
		}
		break;
	case "activate":
		if(CSubscription::IsAuthorized($ID))
		{
			//activation
			if($obSubscription->Update($ID, array("ACTIVE"=>"Y")))
			{
				$arSubscription["ACTIVE"] = "Y";
				$iMsg = "ACTIVE";
			}
		}
		break;
	}
}

if($ID == 0 && !empty($_REQUEST["action"]))
	$arWarning[] = GetMessage("subscr_email_not_found");

//initialize variables from POST on error
if($bVarsFromForm)
{
	$arSubscription["FORMAT"] = $_REQUEST["FORMAT"]=="html"?"html":"text";
	$arSubscription["EMAIL"] = htmlspecialcharsbx($_REQUEST["EMAIL"]);
}

//page title
if($arParams["SET_TITLE"]=="Y")
{
	if($ID>0)
		$APPLICATION->SetTitle(GetMessage("subscr_title_edit"), array('COMPONENT_NAME' => $this->GetName()));
	else
		$APPLICATION->SetTitle(GetMessage("subscr_title_add"), array('COMPONENT_NAME' => $this->GetName()));
}

//if the subscription belongs to USER_ID then authorization is required
if($ID > 0 && intval($arSubscription["USER_ID"]) > 0 && !CSubscription::IsAuthorized($ID))
{
	unset($_GET["mess_code"]);
	$APPLICATION->AuthForm("", false);
}

//get site's newsletter categories
$obCache = new CPHPCache;
$strCacheID = LANG.$arParams["SHOW_HIDDEN"].$this->GetRelativePath();
if($obCache->StartDataCache($arParams["CACHE_TIME"], $strCacheID, "/".SITE_ID.$this->GetRelativePath()))
{
	$arFilter = array("ACTIVE"=>"Y", "LID"=>LANG);
	if($arParams["SHOW_HIDDEN"]<>"Y")
		$arFilter["VISIBLE"]="Y";
	$rsRubric = CRubric::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	$arRubrics = array();
	while($arRubric = $rsRubric->GetNext())
	{
		$arRubrics[]=$arRubric;
	}
	$obCache->EndDataCache($arRubrics);
}
else
{
	$arRubrics = $obCache->GetVars();
}

if(!array_key_exists($iMsg, $aMsg))
	$iMsg = "";
if($iMsg!="")
	$arResult["MESSAGE"] = array($iMsg=>$aMsg[$iMsg]);
else
	$arResult["MESSAGE"] = array();

$arResult["ERROR"] = $arWarning;
$arResult["ID"] = $ID;
$arResult["SUBSCRIPTION"] = $arSubscription;
$arResult["ALLOW_ANONYMOUS"] = $arParams["ALLOW_ANONYMOUS"];
$arResult["SHOW_AUTH_LINKS"] = $arParams["SHOW_AUTH_LINKS"];
$arResult["FORM_ACTION"] = $APPLICATION->GetCurPage();
$arResult["ALLOW_REGISTER"] = $bAllowRegister?"Y":"N";

$arSubscriptionRubrics = CSubscription::GetRubricArray($ID);
$arResult["RUBRICS"] = array();

//Let's find out where selected rubrics come from
$arInput = array();
if(!array_key_exists("sf_RUB_ID", $_REQUEST) || !is_array($_REQUEST["sf_RUB_ID"]))
{
	if($bVarsFromForm)
	{
		if(array_key_exists("RUB_ID", $_REQUEST) && is_array($_REQUEST["RUB_ID"]))
			$arInput = $_REQUEST["RUB_ID"];
	}
	else
	{
		$arInput = $arSubscriptionRubrics;
	}
}
else
{
	$arInput = $_REQUEST["sf_RUB_ID"];
}

foreach($arRubrics as $arRubric)
{
	$bChecked = in_array($arRubric["ID"], $arInput);

	$arResult["RUBRICS"][]=array(
		"ID"=>$arRubric["ID"],
		"NAME"=>$arRubric["NAME"],
		"DESCRIPTION"=>$arRubric["DESCRIPTION"],
		"CHECKED"=>$bChecked,
	);
}

$sRub = "";
if(is_array($_REQUEST["sf_RUB_ID"]))
	foreach($_REQUEST["sf_RUB_ID"] as $strRub)
		$sRub .= "&sf_RUB_ID[]=".urlencode($strRub);
$arResult["REQUEST"]["RUBRICS_PARAM"] = htmlspecialcharsbx($sRub);
$arResult["REQUEST"]["CONFIRM_CODE"] = htmlspecialcharsbx($_REQUEST["CONFIRM_CODE"]);
$arResult["REQUEST"]["EMAIL"] = htmlspecialcharsbx($_REQUEST["sf_EMAIL"]);
if($arResult["REQUEST"]["EMAIL"] == '' && $USER->IsAuthorized())
	$arResult["REQUEST"]["EMAIL"] = htmlspecialcharsbx($USER->GetEmail());
$arResult["REQUEST"]["PASSWORD"] = htmlspecialcharsbx($_REQUEST["PASSWORD"]);
$arResult["REQUEST"]["LOGIN"] = htmlspecialcharsbx((isset($_REQUEST["LOGIN"])? $_REQUEST["LOGIN"]:$sLastLogin));
$arResult["REQUEST"]["NEW_LOGIN"] = htmlspecialcharsbx($_REQUEST["NEW_LOGIN"]);
$arResult["REQUEST"]["NEW_PASSWORD"] = htmlspecialcharsbx($_REQUEST["NEW_PASSWORD"]);
$arResult["REQUEST"]["CONFIRM_PASSWORD"] = htmlspecialcharsbx($_REQUEST["CONFIRM_PASSWORD"]);

$this->IncludeComponentTemplate();
?>
