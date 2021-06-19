<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage advertising
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

use Bitrix\Main;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/prolog.php");
Loader::includeModule('advertising');

ClearVars();

$isDemo = CAdvContract::IsDemo();
$isManager = CAdvContract::IsManager();
$isAdvertiser = CAdvContract::IsAdvertiser();
$isAdmin = CAdvContract::IsAdmin();

if (!$isAdmin && !$isDemo && !$isManager && !$isAdvertiser)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
$err_mess = "FILE: ".__FILE__."<br>LINE: ";

CModule::IncludeModule('fileman');
CJSCore::Init('file_input');
if (class_exists('\Bitrix\Main\UI\FileInput', true))
	CJSCore::Init('fileinput');

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("AD_TAB_BANNER"), "ICON"=>"ad_banner_edit", "TITLE"=> GetMessage("AD_TAB_TITLE_BANNER")),
	array("DIV" => "edit2", "TAB" => GetMessage("AD_TAB_LIMIT"), "ICON"=>"ad_banner_edit", "TITLE"=> GetMessage("AD_WHEN")),
	array("DIV" => "edit3", "TAB" => GetMessage("AD_TAB_TARGET"), "ICON"=>"ad_banner_edit", "TITLE"=> GetMessage("AD_WHERE")),
	array("DIV" => "edit5", "TAB" => GetMessage("AD_TAB_COMMENT"), "ICON"=>"ad_banner_edit", "TITLE"=> GetMessage("AD_COMMENTS")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$strError = '';
$ID = intval($_REQUEST["ID"]);
$action = $_REQUEST["action"];
$bCopy = ($action == "copy");
$CONTRACT_ID = intval($CONTRACT_ID);
$isEditMode = true;
$arPropsTemplate = array();
if ($ID>0 && $CONTRACT_ID<=0)
{
	$rsBanner = CAdvBanner::GetByID($ID);
	if ($arBanner = $rsBanner->Fetch())
		$CONTRACT_ID = $arBanner["CONTRACT_ID"];
}
if ($CONTRACT_ID<=0)
	$CONTRACT_ID=1;

$rsContract = CAdvContract::GetByID($CONTRACT_ID, "N");
if (!$rsContract || !$arContract = $rsContract->Fetch())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	CAdminMessage::ShowMessage(GetMessage("AD_ERROR_INCORRECT_CONTRACT_ID"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
else
{
	$arrPERM = CAdvContract::GetUserPermissions($CONTRACT_ID);
	$arrPERM = is_array($arrPERM[$CONTRACT_ID]) ? $arrPERM[$CONTRACT_ID] : array();
	if (!$isDemo)
	{
		if (count($arrPERM) <= 0)
			$APPLICATION->AuthForm(GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_CONTRACT"));
		if (!in_array("ADD", $arrPERM))
			$isEditMode = false;
	}
	if ($action == "view")
		$isEditMode = false;

	$arrCONTRACT_TYPE = CAdvContract::GetTypeArray($CONTRACT_ID);
	$isOwner = CAdvContract::IsOwner($CONTRACT_ID);
}

function pr_comp($a, $b)
{
	if ($a["SORT"] < $b["SORT"])
		return -1;
	elseif ($a["SORT"] > $b["SORT"])
		return 1;
	else
		return 0;
}

if (
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
	&& ($_POST["action"] == 'getTemplate' || $_POST["action"] == 'refreshTemplate' || $_POST["action"] == 'refreshAll')
	&& isset($_POST["name"])
)
{
	$GLOBALS['APPLICATION']->RestartBuffer();
	if ($_POST["name"] <> '')
	{
		$properties = is_array($_POST['properties']) ? $_POST['properties'] : array();
		$arCurVal = is_array($_POST['curValues'])
			? $_POST['curValues']
			: (isset($properties['parameters']['PROPS'])
				? $properties['parameters']['PROPS']
				: array());
		$bCopy = $_POST["bCopy"] == 'Y';
		if (!empty($properties['parameters']['PROPS']) && is_array($properties['parameters']['PROPS']))
		{
			foreach ($properties['parameters']['PROPS'] as $id => $prop)
			{
				$arCurVal[$id]['EXTENDED_MODE'] = $properties['parameters']['MODE'];
				$arPropsTemplate[$id] = CComponentUtil::GetTemplateProps('bitrix:advertising.banner.view', $_POST["name"], '', $arCurVal[$id]);
				uasort($arPropsTemplate[$id]["PARAMETERS"], 'pr_comp');
			}
		}
		else if ($_POST["action"] == 'refreshAll')
		{
			if (empty($arCurVal))
			{
				$arCurVal = array('EXTENDED_MODE' => $_POST["mode"]);
				$arPropsTemplate[0] = CComponentUtil::GetTemplateProps('bitrix:advertising.banner.view', $_POST["name"], '', $arCurVal);
				uasort($arPropsTemplate[0]["PARAMETERS"], 'pr_comp');
			}
			else
			{
				foreach ($arCurVal as $id => $curVal)
				{
					$arPropsTemplate[$id] = CComponentUtil::GetTemplateProps('bitrix:advertising.banner.view', $_POST["name"], '', $curVal);
					uasort($arPropsTemplate[$id]["PARAMETERS"], 'pr_comp');
				}
			}
		}
		else
		{
			if (empty($arCurVal))
				$arCurVal = array('EXTENDED_MODE' => $_POST["mode"]);
			$arPropsTemplate[0] = CComponentUtil::GetTemplateProps('bitrix:advertising.banner.view', $_POST["name"], '', $arCurVal);
			uasort($arPropsTemplate[0]["PARAMETERS"], 'pr_comp');
		}

		$defaultProps = array();
		foreach ($arPropsTemplate as $i => $k)
		{
			$ind = isset($_POST["index"]) && $_POST["index"] != '' ? intval($_POST["index"]) : $i;
			foreach ($k['PARAMETERS'] as $name => $prop)
			{
				$html = '';
				$defaultProps[$name] = $prop['DEFAULT'];
				if ($prop['TYPE'] == 'IMAGE')
				{
					$file_ID = (is_array($properties) && isset($properties['files'][$ind][$name]) && $properties['files'][$ind][$name] !== 'null') ? intval($properties['files'][$ind][$name]) : 0;
					if ($bCopy)
					{
						$html .= '<input type=\'hidden\' name=\'TEMPLATE_FILES_copy['.$ind.'_'.$name.']\' value=\''.$file_ID.'\'>';
					}
					ob_start();
					if (class_exists('\Bitrix\Main\UI\FileInput', true))
					{
						echo \Bitrix\Main\UI\FileInput::createInstance(array(
							"name" => "TEMPLATE_FILES[".$ind.'_'.$name."]",
							"description" => true,
							"upload" => true,
							"allowUpload" => "I",
							"medialib" => true,
							"fileDialog" => true,
							"cloud" => true,
							"delete" => true,
							"maxCount" => 1
						))->show($file_ID);
					}
					else
					{
						echo CFileInput::Show("TEMPLATE_FILES[".$ind.'_'.$name."]", $file_ID,
							array(
								"IMAGE" => "Y",
								"PATH" => "Y",
								"FILE_SIZE" => "Y",
								"DIMENSIONS" => "Y",
								"IMAGE_POPUP" => "Y",
								"MAX_SIZE" => array(
									"W" => 200,
									"H" => 200,
								),
							), array(
								'upload' => true,
								'medialib' => true,
								'file_dialog' => true,
								'cloud' => true,
								'del' => true,
								'description' => true,
							)
						);
					}
					$html .= ob_get_contents();
					ob_end_clean();
				}
				if ($prop['TYPE'] == 'HTML')
				{
					$strVal = isset($properties['parameters']['PROPS'][$i][$name]['CODE']) ? $properties['parameters']['PROPS'][$i][$name]['CODE'] : $defaultProps[$name];
					$codeType = isset($properties['parameters']['PROPS'][$i][$name]['TYPE']) ? $properties['parameters']['PROPS'][$i][$name]['TYPE'] : 'html';
					ob_start();
					if (COption::GetOptionString("advertising", "USE_HTML_EDIT", "Y")=="Y" && CModule::IncludeModule("fileman")):
						if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
							CFileMan::AddHTMLEditorFrame("TEMPLATE_EDITOR_".$ind.'_'.$name, $strVal, "TEMPLATE_EDITOR[".$ind.'_'.$name."_CODE_TYPE]", $codeType, array('height' => 200, 'width' => '100%'), "N", 0, "", "", false, true, false, array('setFocusAfterShow' => false, 'minHeight' => 200));
						else
							CFileMan::AddHTMLEditorFrame("TEMPLATE_EDITOR_".$ind.'_'.$name, $strVal, "TEMPLATE_EDITOR[".$ind.'_'.$name."_CODE_TYPE]", $codeType, array('height' => 200, 'width' => '100%'), "N", 0, "", "", false, true, false, array('setFocusAfterShow' => false, 'minHeight' => 200));
					else: ?>
						<div align="center" style="vertical-align:top;">
							<?=InputType("radio", "TEMPLATE_EDITOR[".$ind.'_'.$name."_CODE_TYPE]","text",$codeType,false)?>&nbsp;<?=GetMessage("AD_TEXT")?>&nbsp;<?=InputType("radio","TEMPLATE_EDITOR[".$ind.'_'.$name."_CODE_TYPE]","html",$codeType,false)?>&nbsp;HTML
							<textarea style="width:100%" rows="30" name="<?='TEMPLATE_EDITOR_'.$ind.'_'.$name?>"><?=$strVal?></textarea>
						</div>
					<? endif;
					$html = ob_get_contents();
					ob_end_clean();
				}
				if ($prop['TYPE'] == 'FILE')
				{
					$file_ID = (is_array($properties) && isset($properties['files'][$ind][$name]) && $properties['files'][$ind][$name] !== 'null') ? intval($properties['files'][$ind][$name]) : 0;
					if ($bCopy)
					{
						$html .= '<input type=\'hidden\' name=\'TEMPLATE_FILES_copy['.$ind.'_'.$name.']\' value=\''.$file_ID.'\'>';
					}
					ob_start();
					echo CFileInput::Show("TEMPLATE_FILES[".$ind.'_'.$name."]", $file_ID,
						array(
							"IMAGE" => "Y",
							"PATH" => "Y",
							"FILE_SIZE" => "Y",
							"DIMENSIONS" => "Y",
							"IMAGE_POPUP" => "Y",
							"MAX_SIZE" => array(
								"W" => 200,
								"H" => 200,
							),
						), array(
							'upload' => true,
							'medialib' => true,
							'file_dialog' => true,
							'cloud' => true,
							'del' => true,
							'description' => true,
						)
					);
					$html .= ob_get_contents();
					ob_end_clean();
				}
				$arPropsTemplate[$i]['PARAMETERS'][$name]['HTML'] = $html;
				$arPropsTemplate[$i]['BANNER_NAME'] = $properties['parameters']['PROPS'][$i]['BANNER_NAME'];
			}
		}
		echo CUtil::PhpToJsObject($arPropsTemplate);
	}
	die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == 'getCleanTemplate' && isset($_POST["name"]) && check_bitrix_sessid())
{
	$GLOBALS['APPLICATION']->RestartBuffer();
	if ($_POST["name"] <> '')
	{
		$i = intval($_POST["index"]);
		$arPropsTemplate = CComponentUtil::GetTemplateProps('bitrix:advertising.banner.view', $_POST["name"], '', array('EXTENDED_MODE' => $_POST["mode"]));
		uasort($arPropsTemplate["PARAMETERS"], 'pr_comp');

		$defaultProps = array();
		foreach ($arPropsTemplate["PARAMETERS"] as $name => $prop)
		{
			$html = '';
			$defaultProps[$name] = $prop['DEFAULT'];
			if ($prop['TYPE'] == 'IMAGE')
			{
				ob_start();
				if (class_exists('\Bitrix\Main\UI\FileInput', true))
				{
					echo \Bitrix\Main\UI\FileInput::createInstance(array(
						"name" => "TEMPLATE_FILES[".$i.'_'.$name."]",
						"description" => true,
						"upload" => true,
						"allowUpload" => "I",
						"medialib" => true,
						"fileDialog" => true,
						"cloud" => true,
						"delete" => true,
						"maxCount" => 1
					))->show(0);
				}
				else
				{
					echo CFileInput::Show("TEMPLATE_FILES[".$i.'_'.$name."]", 0,
						array(
							"IMAGE" => "Y",
							"PATH" => "Y",
							"FILE_SIZE" => "Y",
							"DIMENSIONS" => "Y",
							"IMAGE_POPUP" => "Y",
							"MAX_SIZE" => array(
								"W" => 200,
								"H" => 200,
							),
						), array(
							'upload' => true,
							'medialib' => true,
							'file_dialog' => true,
							'cloud' => true,
							'del' => true,
							'description' => true,
						)
					);
				}
				$html = ob_get_contents();
				ob_end_clean();
			}
			if ($prop['TYPE'] == 'HTML')
			{
				$strVal = $defaultProps[$name];
				$codeType = 'html';
				ob_start();
				if (COption::GetOptionString("advertising", "USE_HTML_EDIT", "Y")=="Y" && CModule::IncludeModule("fileman")):
					if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
						CFileMan::AddHTMLEditorFrame("TEMPLATE_EDITOR_".$i.'_'.$name, $strVal, "TEMPLATE_EDITOR[".$i.'_'.$name."_CODE_TYPE]", $codeType, array('height' => 200, 'width' => '100%'), "N", 0, "", "", false, true, false, array('setFocusAfterShow' => false, 'minHeight' => 200));
					else
						CFileMan::AddHTMLEditorFrame("TEMPLATE_EDITOR_".$i.'_'.$name, $strVal, "TEMPLATE_EDITOR[".$i.'_'.$name."_CODE_TYPE]", $codeType, array('height' => 200, 'width' => '100%'), "N", 0, "", "", false, true, false, array('setFocusAfterShow' => false, 'minHeight' => 200));
				else: ?>
					<div align="center" style="vertical-align:top;">
						<?=InputType("radio", "TEMPLATE_EDITOR[".$i.'_'.$name."_CODE_TYPE]","text",$codeType,false)?>&nbsp;<?=GetMessage("AD_TEXT")?>&nbsp;<?=InputType("radio","TEMPLATE_EDITOR[".$i.'_'.$name."_CODE_TYPE]","html",$codeType,false)?>&nbsp;HTML
						<textarea style="width:100%" rows="30" name="<?='TEMPLATE_EDITOR_'.$i.'_'.$name?>"><?=$strVal?></textarea>
					</div>
				<? endif;
				$html = ob_get_contents();
				ob_end_clean();
			}
			if ($prop['TYPE'] == 'FILE')
			{
				ob_start();
				echo CFileInput::Show("TEMPLATE_FILES[".$i.'_'.$name."]", 0,
					array(
						"IMAGE" => "Y",
						"PATH" => "Y",
						"FILE_SIZE" => "Y",
						"DIMENSIONS" => "Y",
						"IMAGE_POPUP" => "Y",
						"MAX_SIZE" => array(
							"W" => 200,
							"H" => 200,
						),
					), array(
						'upload' => true,
						'medialib' => true,
						'file_dialog' => true,
						'cloud' => true,
						'del' => true,
						'description' => true,
					)
				);
				$html = ob_get_contents();
				ob_end_clean();
			}
			$arPropsTemplate['PARAMETERS'][$name]['HTML'] = $html;
		}
		echo CUtil::PhpToJsObject(array(0 => $arPropsTemplate));
	}
	die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["save"] <> '' || $_POST["apply"] <> '') && check_bitrix_sessid())
{
	$SEND_EMAIL = $SEND_EMAIL == "Y" ? "Y" : "N";
	if (class_exists('\Bitrix\Main\UI\FileInput', true))
	{
		$img_id = $_REQUEST["IMAGE_ID"] ? $_REQUEST["IMAGE_ID"] : $_REQUEST["IMAGE_ID_copy"];
		$flash_image_id = $_REQUEST["FLASH_IMAGE"] ? $_REQUEST["FLASH_IMAGE"] : $_REQUEST["FLASH_IMAGE_copy"];
		$arrIMAGE_ID = CAdvBanner_all::makeFileArray($img_id, $_REQUEST["IMAGE_ID_del"] === "Y", '');
		$arrFlashIMAGE_ID = CAdvBanner_all::makeFileArray($flash_image_id,	$_REQUEST["FLASH_IMAGE_del"] === "Y", '');
	}
	else
	{
		if ($_REQUEST["IMAGE_ID"] <> '')
			$arrIMAGE_ID = CAdvBanner_all::makeFileArray($_REQUEST["IMAGE_ID"]);
		else
			$arrIMAGE_ID = $_FILES["IMAGE_ID"];

		if ($_REQUEST["FLASH_IMAGE"] <> '')
			$arrFlashIMAGE_ID = CAdvBanner_all::makeFileArray($_REQUEST["FLASH_IMAGE"]);
		else
			$arrFlashIMAGE_ID = $_FILES["FLASH_IMAGE"];

		if ($_REQUEST["IMAGE_ID_del"] === "Y")
			$arrIMAGE_ID['del'] = 'Y';
		if ($_REQUEST["FLASH_IMAGE_del"] === "Y")
			$arrFlashIMAGE_ID['del'] = 'Y';
	}
	if (!is_array($TEMPLATE_FILES_del))
		$TEMPLATE_FILES_del = array();
	if (!is_array($TEMPLATE_FILES))
	{
		if (!empty($request))
		{
			$TEMPLATE_FILES = $request->getFile('TEMPLATE_FILES');
		}
		else
		{
			$TEMPLATE_FILES = Bitrix\Main\Context::getCurrent()->getRequest()->getFile('TEMPLATE_FILES');
		}
	}

	//array for save if show error
	$templateFilesErr = array();
	if (is_array($TEMPLATE_FILES))
	{
		foreach ($TEMPLATE_FILES as $tfk => $tfv)
		{
			$before_ = mb_substr($tfk, 0, mb_strpos($tfk, '_'));
			$after_ = mb_substr($tfk, mb_strpos($tfk, '_') + 1);
			if (is_array($tfv))
				$tfv = '';
			$templateFilesErr[$before_][$after_] = $tfv;
		}
	}

	if (!is_array($TEMPLATE_FILES['name']))
	{
		foreach ($TEMPLATE_FILES_del as $k2 => &$v2)
		{
			if (isset($TEMPLATE_FILES[$k2]))
				$v2 = '';
			else
				$TEMPLATE_FILES[$k2]['del'] = 'Y';
		}
		if (is_array($TEMPLATE_FILES))
		{
			foreach ($TEMPLATE_FILES as $k => $v)
			{
				$template_file_id = $_REQUEST["TEMPLATE_FILES"][$k] ? $_REQUEST["TEMPLATE_FILES"][$k] : $_REQUEST["TEMPLATE_FILES_copy"][$k];
				$TEMPLATE_FILES[$k] = CAdvBanner_all::makeFileArray(
					$template_file_id,
					$TEMPLATE_FILES_del[$k] === "Y",
					$TEMPLATE_FILES_descr[$k]
				);
			}
		}
	}
	else
	{
		$arrTEMPLATE_FILES = array();
		if (is_array($TEMPLATE_FILES['name']))
		{
			foreach ($TEMPLATE_FILES['name'] as $k => $v)
			{
				foreach ($TEMPLATE_FILES as $key => $value)
					$arrTEMPLATE_FILES[$k][$key] = $TEMPLATE_FILES[$key][$k];
			}
		}
		$TEMPLATE_FILES = $arrTEMPLATE_FILES;
		foreach ($TEMPLATE_FILES_del as $k2 => &$v2)
		{
			if (isset($_REQUEST["TEMPLATE_FILES"][$k2]))
				$v2 = '';
			else
				$TEMPLATE_FILES[$k2]['del'] = 'Y';
		}
		if (is_array($_REQUEST["TEMPLATE_FILES"]))
		{
			foreach ($_REQUEST["TEMPLATE_FILES"] as $k1 => $v1)
			{
				$TEMPLATE_FILES[$k1] = CAdvBanner_all::makeFileArray(
					$v1,
					$TEMPLATE_FILES_del[$k1] === "Y",
					$TEMPLATE_FILES_descr[$k1]
				);
			}
		}
		if (is_array($_REQUEST["TEMPLATE_FILES_copy"]))
		{
			foreach ($_REQUEST["TEMPLATE_FILES_copy"] as $k1 => $v1)
			{
				if (isset($_REQUEST["TEMPLATE_FILES"][$k1]) && $_REQUEST["TEMPLATE_FILES"][$k1]['tmp_name'] <> '')
					continue;
				$TEMPLATE_FILES[$k1] = CAdvBanner_all::makeFileArray(
					$v1,
					$TEMPLATE_FILES_del[$k1] === "Y",
					$TEMPLATE_FILES_descr[$k1]
				);
			}
		}
		foreach ($TEMPLATE_FILES_descr as $k3 => $v3)
		{
			$TEMPLATE_FILES[$k3]['description'] = $v3;
		}
	}

	$arrWEEKDAY = array(
		"SUNDAY"	=> $arrSUNDAY,
		"MONDAY"	=> $arrMONDAY,
		"TUESDAY"	=> $arrTUESDAY,
		"WEDNESDAY"	=> $arrWEDNESDAY,
		"THURSDAY"	=> $arrTHURSDAY,
		"FRIDAY"	=> $arrFRIDAY,
		"SATURDAY"	=> $arrSATURDAY
	);
	if (!$isEditMode && ($isManager || $isAdmin))
	{
		$arFields = array(
			"STATUS_SID"		=> $STATUS_SID,
			"STATUS_COMMENTS"	=> $STATUS_COMMENTS
		);
	}
	else
	{
		$ACTIVE = $ACTIVE == "Y" ? "Y" : "N";
		$FIX_SHOW = $FIX_SHOW == "Y" ? "Y" : "N";
		if (!is_array($TEMPLATE_PROP))
			$TEMPLATE_PROP = array();
		if (!is_array($TEMPLATE_EDITOR))
			$TEMPLATE_EDITOR = array();
		foreach ($TEMPLATE_EDITOR as $name => $value)
		{
			$start = mb_strpos($name, '_') + 1;
			$num = mb_substr($name, 0, $start - 1);
			$name = mb_substr($name, $start, mb_strpos($name, '_CODE_TYPE') - $start);
			$TEMPLATE_PROP[$num][$name]['CODE'] = ${"TEMPLATE_EDITOR_".$num."_".$name};
			$TEMPLATE_PROP[$num][$name]['TYPE'] = $value;
		}

		$arrTemplateFiles = array();
		if (is_array($TEMPLATE_FILES)){
			foreach ($TEMPLATE_FILES as $tfk => $tfv)
			{
				$before_ = mb_substr($tfk, 0, mb_strpos($tfk, '_'));
				$after_ = mb_substr($tfk, mb_strpos($tfk, '_') + 1);
				$arrTemplateFiles[$before_][$after_] = $tfv;
				$arrTemplateFiles[$before_][$after_]['lastKey'] = $before_;
			}
		}

		$arTemplateProperties = array();
		$TEMPLATE_FILES = array();

		if ($AD_TYPE == 'template')
		{
			foreach ($TEMPLATE_PROP as $tpk => $tpv)
			{
				$arTemplateProperties[] = $tpv;
				$TEMPLATE_FILES[] = $arrTemplateFiles[$tpk];
			}
			$arTemplateProperties = serialize(array('NAME' => $TEMPLATE_NAME, 'MODE' => $EXTENDED_MODE, 'PROPS' => $arTemplateProperties));
		}
		else
			$arTemplateProperties = '';

		$arFields = array(
			"CONTRACT_ID"			=> $CONTRACT_ID,
			"TYPE_SID"			=> $TYPE_SID,
			"STATUS_SID"			=> $STATUS_SID,
			"STATUS_COMMENTS"		=> $STATUS_COMMENTS,
			"NAME"				=> $NAME,
			"GROUP_SID"			=> $GROUP_SID,
			"ACTIVE"				=> ($ACTIVE=="Y" ? "Y" : "N"),
			"WEIGHT"				=> $WEIGHT,
			"MAX_VISITOR_COUNT"		=> $MAX_VISITOR_COUNT,
			"RESET_VISITOR_COUNT"	=> $RESET_VISITOR_COUNT,
			"SHOWS_FOR_VISITOR"		=> $SHOWS_FOR_VISITOR,
			"MAX_SHOW_COUNT"		=> $MAX_SHOW_COUNT,
			"RESET_SHOW_COUNT"		=> $RESET_SHOW_COUNT,
			"FIX_SHOW"		=> $FIX_SHOW,
			"FLYUNIFORM"	=> ($FLYUNIFORM=="Y" ? "Y" : "N"),
			"MAX_CLICK_COUNT"		=> $MAX_CLICK_COUNT,
			"RESET_CLICK_COUNT"		=> $RESET_CLICK_COUNT,
			"DATE_SHOW_FROM"		=> $DATE_SHOW_FROM,
			"DATE_SHOW_TO"			=> $DATE_SHOW_TO,
			"arrIMAGE_ID"			=> $arrIMAGE_ID,
			"IMAGE_ALT"			=> $IMAGE_ALT,
			"URL"				=> $_POST["URL"],
			"URL_TARGET"			=> $URL_TARGET,
			"NO_URL_IN_FLASH"		=> ($NO_URL_IN_FLASH=="Y"? "Y" : "N"),
			"CODE"				=> $CODE,
			"CODE_TYPE"			=> $CODE_TYPE,
			"FOR_NEW_GUEST"		=> $FOR_NEW_GUEST,
			"COMMENTS"			=> $COMMENTS,
			"SHOW_USER_GROUP"		=> $SHOW_USER_GROUP,
			"arrSHOW_PAGE"			=> preg_split('/[\n\r]+/', $SHOW_PAGE),
			"arrNOT_SHOW_PAGE"		=> preg_split('/[\n\r]+/', $NOT_SHOW_PAGE),
			"arrSTAT_ADV"			=> $arrSTAT_ADV,
			"arrWEEKDAY"			=> $arrWEEKDAY,
			"arrSITE"				=> $arrSITE,
			"arrUSERGROUP"			=> $arrUSERGROUP,
			"KEYWORDS"			=> $KEYWORDS,
			"SEND_EMAIL"			=> $SEND_EMAIL,
			"AD_TYPE"				=> $AD_TYPE,
			"TEMPLATE"				=> $arTemplateProperties,
			"TEMPLATE_FILES"		=> $TEMPLATE_FILES,
			"FLASH_TRANSPARENT" => $FLASH_TRANSPARENT,
			"arrFlashIMAGE_ID" => $arrFlashIMAGE_ID,
			"FLASH_JS" => ($FLASH_JS=="Y" ? "Y" : "N"),
			"FLASH_VER" => $FLASH_VER,
		);

		$arFields["arrCOUNTRY"] = array();
		if ($_POST["STAT_TYPE"] === "CITY")
		{
			$arFields["STAT_TYPE"] = "CITY";
			$arrCITY = explode(",", $_POST["ALL_STAT_TYPE_VALUES"]);
			$arFilter = array();
			foreach ($arrCITY as $CITY_ID)
				$arFilter[] = intval($CITY_ID);
			if (count($arFilter) > 0)
			{
				$rs = CCity::GetList("CITY", array("=CITY_ID" => $arFilter));
				while ($ar = $rs->GetNext())
					$arFields["arrCOUNTRY"][] = array(
						"COUNTRY_ID" => $ar["COUNTRY_ID"],
						"REGION" => $ar["REGION_NAME"],
						"CITY_ID" => $ar["CITY_ID"],
					);
			}
		}
		elseif ($_POST["STAT_TYPE"] === "REGION")
		{
			$arFields["STAT_TYPE"] = "REGION";
			$arrREGION = explode(",", $_POST["ALL_STAT_TYPE_VALUES"]);
			foreach ($arrREGION as $reg)
			{
				$ar = explode("|", $reg, 2);
				$arFields["arrCOUNTRY"][] = array(
					"COUNTRY_ID" => $ar[0],
					"REGION" => $ar[1],
					"CITY_ID" => false,
				);
			}
		}
		else
		{
			$arFields["STAT_TYPE"] = "COUNTRY";
			$arFields["arrCOUNTRY"] = explode(",", $_POST["ALL_STAT_TYPE_VALUES"]);
		}

		if (!$arBanner and $ID>0)
		{
			$rsBanner = CAdvBanner::GetByID($ID);
			if ($arBanner = $rsBanner->Fetch())
			{
				if ($DATE_SHOW_FROM != $arBanner["DATE_SHOW_FROM"] or
					$DATE_SHOW_TO != $arBanner["DATE_SHOW_TO"] or
					$RESET_SHOW_COUNT == "Y")
				{
					$arFields["DATE_SHOW_FIRST"] = "null";
				}
			}
		}
	}

	if ($ID = CAdvBanner::Set($arFields, $ID))
	{
		// test if Set finished secsesfully.
		if ($strError == '')
		{
			if ($_POST["save"] <> '')
				LocalRedirect("/bitrix/admin/adv_banner_list.php?lang=".LANGUAGE_ID);
			else
				LocalRedirect("/bitrix/admin/adv_banner_edit.php?ID=".$ID."&CONTRACT_ID=".$CONTRACT_ID."&lang=".LANGUAGE_ID."&action=".$action."&".$tabControl->ActiveTabParam());
		}
	}
	$TEMPLATE_FILES = serialize($templateFilesErr);
	$DB->PrepareFields("b_adv_banner");
}

$arrSites = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
	$arrSites[$ar["ID"]] = $ar;

$rsBanner = CAdvBanner::GetByID($ID);

$arrKEYWORDS = array();
if (!$rsBanner || !$banner = $rsBanner->ExtractFields())
{
	if (!$isEditMode)
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		CAdminMessage::ShowMessage(GetMessage("AD_ERROR_INCORRECT_BANNER_ID"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
	$ID=0;
	$str_AD_TYPE = 'image';
	$str_WEIGHT = 100;
	$str_ACTIVE = "Y";
	$str_FIX_SHOW = "N";
	$str_FLYUNIFORM = "N";
	$str_DATE_SHOW_FROM = $arContract["DATE_SHOW_FROM"];
	$str_DATE_SHOW_TO = $arContract["DATE_SHOW_TO"];
	$str_CODE_TYPE = "html";
	//if ($isAdmin || $isManager) $str_STATUS_SID = "PUBLISHED";
	$str_MAX_SHOW_COUNT = $arContract["MAX_SHOW_COUNT"];
	$str_MAX_CLICK_COUNT = $arContract["MAX_CLICK_COUNT"];
	$arrSITE = array_keys($arrSites);
	$str_CONTRACT_ID = $CONTRACT_ID;
	$str_STAT_TYPE = "COUNTRY";

	$str_TYPE_SID = isset($TYPE_SID) && $TYPE_SID <> '' ? $TYPE_SID : "";
}
else
{
	if ($strError == '')
	{
		if ($str_KEYWORDS <> '')
		{
			$arrKEYWORDS = preg_split('/[\n\r]+/',$str_KEYWORDS);
			TrimArr($arrKEYWORDS);
		}
		$arrSITE = CAdvBanner::GetSiteArray($ID);
		$arrSHOW_PAGE = CAdvBanner::GetPageArray($ID, "SHOW");
		$str_SHOW_PAGE = implode("\n", $arrSHOW_PAGE);
		$arrNOT_SHOW_PAGE = CAdvBanner::GetPageArray($ID, "NOT_SHOW");
		$str_NOT_SHOW_PAGE = implode("\n", $arrNOT_SHOW_PAGE);
		if ($str_STAT_TYPE !== "CITY" && $str_STAT_TYPE != "REGION")
			$str_STAT_TYPE = "COUNTRY";
		$arrSTAT_TYPE_VALUES = CAdvBanner::GetCountryArray($ID, $str_STAT_TYPE);
		$arrWEEKDAY = CAdvBanner::GetWeekdayArray($ID);
		foreach ($arrWEEKDAY as $key => $value)
		{
			${"arr".$key} = $value;
		}
		$arrSTAT_ADV = CAdvBanner::GetStatAdvArray($ID);
		$arrUSERGROUP = CAdvBanner::GetGroupArray($ID);
	}
}
if ($strError <> '')
{
	$DB->InitTableVarsForEdit("b_adv_banner", "", "str_");
	$str_SHOW_PAGE = htmlspecialcharsbx($SHOW_PAGE);
	$str_NOT_SHOW_PAGE = htmlspecialcharsbx($NOT_SHOW_PAGE);
	$str_IMAGE_ID = 0;
	$str_FLASH_IMAGE = 0;
}
if ($SEND_EMAIL == '') $SEND_EMAIL = "Y";

if ($str_TEMPLATE && CheckSerializedData($str_TEMPLATE))
	$str_TEMPLATE = unserialize(htmlspecialchars_decode($str_TEMPLATE), ['allowed_classes' => false]);
else
	$str_TEMPLATE = array();

if ($str_TEMPLATE_FILES && CheckSerializedData($str_TEMPLATE_FILES))
	$str_TEMPLATE_FILES = unserialize(htmlspecialchars_decode($str_TEMPLATE_FILES), ['allowed_classes' => false]);
else
	$str_TEMPLATE_FILES = array();

if ($str_AD_TYPE == 'template')
{
	$arCurVal = isset($str_TEMPLATE['PROPS']) ? $str_TEMPLATE['PROPS'] : $_POST['TEMPLATE_PROP'];
	$templateName = $str_TEMPLATE["NAME"] ? $str_TEMPLATE["NAME"] : $_POST['TEMPLATE_NAME'];
	$templateMode = $str_TEMPLATE['MODE'] ? $str_TEMPLATE['MODE'] : $_POST['EXTENDED_MODE'];
	if (is_array($arCurVal) && !empty($arCurVal))
	{
		foreach ($arCurVal as $id => $prop)
		{
			$arCurVal[$id]['EXTENDED_MODE'] = $templateMode;
			$arPropsTemplate[$id] = CComponentUtil::GetTemplateProps('bitrix:advertising.banner.view', $templateName, '', $arCurVal[$id]);
			uasort($arPropsTemplate[$id]["PARAMETERS"], 'pr_comp');
		}
	}
	else
	{
		$arCurVal = array('EXTENDED_MODE' => $templateMode);
		$arPropsTemplate[0] = CComponentUtil::GetTemplateProps('bitrix:advertising.banner.view', $templateName, '', $arCurVal);
		if (is_array($arPropsTemplate[0]["PARAMETERS"]) && !empty($arPropsTemplate[0]["PARAMETERS"]))
			uasort($arPropsTemplate[0]["PARAMETERS"], 'pr_comp');
	}

	$defaultProps = array();
	foreach ($arPropsTemplate as $i => $k)
	{
		if (is_array($k["PARAMETERS"]) && !empty($k["PARAMETERS"]))
		{
			foreach ($k['PARAMETERS'] as $name => $prop)
			{
				$html = '';
				$defaultProps[$name] = $prop['DEFAULT'];
				if ($prop['TYPE'] == 'IMAGE')
				{
					$file_ID = (is_array($str_TEMPLATE_FILES) && isset($str_TEMPLATE_FILES[$i][$name]) && $str_TEMPLATE_FILES[$i][$name] !== 'null') ? intval($str_TEMPLATE_FILES[$i][$name]) : 0;
					if ($bCopy)
					{
						$html .= '<input type=\'hidden\' name=\'TEMPLATE_FILES_copy['.$i.'_'.$name.']\' value=\''.$file_ID.'\'>';
					}
					ob_start();
					if (class_exists('\Bitrix\Main\UI\FileInput', true))
					{
						echo \Bitrix\Main\UI\FileInput::createInstance(array(
								"name" => "TEMPLATE_FILES[".$i.'_'.$name."]",
								"description" => true,
								"allowUpload" => "I",
								"maxCount" => 1
							) + ($isEditMode ? array(
								"medialib" => true,
								"fileDialog" => true,
								"cloud" => true,
								"upload" => true
							) : array(
								"delete" => false,
								"edit" => false
							)
							))->show($file_ID);
					}
					else
					{
						echo CFileInput::Show("TEMPLATE_FILES[".$i.'_'.$name."]", $file_ID,
							array(
								"IMAGE" => "Y",
								"PATH" => "Y",
								"FILE_SIZE" => "Y",
								"DIMENSIONS" => "Y",
								"IMAGE_POPUP" => "Y",
								"MAX_SIZE" => array(
									"W" => 200,
									"H" => 200,
								)
							), array(
								'upload' => $isEditMode,
								'medialib' => $isEditMode,
								'file_dialog' => $isEditMode,
								'cloud' => $isEditMode,
								'del' => $isEditMode,
								'description' => $isEditMode
							)
						);
					}
					$html .= ob_get_contents();
					ob_end_clean();
				}
				if ($prop['TYPE'] == 'HTML')
				{
					$strVal = isset($str_TEMPLATE['PROPS'][$i][$name]['CODE']) ? $str_TEMPLATE['PROPS'][$i][$name]['CODE'] : $defaultProps[$name];
					if ($isEditMode)
					{
						$codeType = isset($str_TEMPLATE['PROPS'][$i][$name]['TYPE']) ? $str_TEMPLATE['PROPS'][$i][$name]['TYPE'] : 'html';
						ob_start();
						if (COption::GetOptionString("advertising", "USE_HTML_EDIT", "Y")=="Y" && CModule::IncludeModule("fileman")):
							if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
								CFileMan::AddHTMLEditorFrame("TEMPLATE_EDITOR_".$i.'_'.$name, $strVal, "TEMPLATE_EDITOR[".$i.'_'.$name."_CODE_TYPE]", $codeType, array('height' => 200, 'width' => '100%'), "N", 0, "", "", false, true, false, array('setFocusAfterShow' => false, 'minHeight' => 200));
							else
								CFileMan::AddHTMLEditorFrame("TEMPLATE_EDITOR_".$i.'_'.$name, $strVal, "TEMPLATE_EDITOR[".$i.'_'.$name."_CODE_TYPE]", $codeType, array('height' => 200, 'width' => '100%'), "N", 0, "", "", false, true, false, array('setFocusAfterShow' => false, 'minHeight' => 200));
						else: ?>
							<div align="center" style="vertical-align:top;">
								<?=InputType("radio", "TEMPLATE_EDITOR[".$i.'_'.$name."_CODE_TYPE]","text",$codeType,false)?>&nbsp;<?=GetMessage("AD_TEXT")?>&nbsp;<?=InputType("radio","TEMPLATE_EDITOR[".$i.'_'.$name."_CODE_TYPE]","html",$codeType,false)?>&nbsp;HTML
								<textarea style="width:100%" rows="30" name="<?='TEMPLATE_EDITOR_'.$i.'_'.$name?>"><?=$strVal?></textarea>
							</div>
						<? endif;
						$html = ob_get_contents();
						ob_end_clean();
					}
					else
						$html = $strVal;
				}
				if ($prop['TYPE'] == 'FILE')
				{
					$file_ID = (is_array($str_TEMPLATE_FILES) && isset($str_TEMPLATE_FILES[$i][$name]) && $str_TEMPLATE_FILES[$i][$name] !== 'null') ? intval($str_TEMPLATE_FILES[$i][$name]) : 0;
					if ($bCopy)
					{
						$html .= '<input type=\'hidden\' name=\'TEMPLATE_FILES_copy['.$i.'_'.$name.']\' value=\''.$file_ID.'\'>';
					}
					ob_start();
					echo CFileInput::Show("TEMPLATE_FILES[".$i.'_'.$name."]", $file_ID,
						array(
							"IMAGE" => "Y",
							"PATH" => "Y",
							"FILE_SIZE" => "Y",
							"DIMENSIONS" => "Y",
							"IMAGE_POPUP" => "Y",
							"MAX_SIZE" => array(
								"W" => 200,
								"H" => 200,
							)
						), array(
							'upload' => $isEditMode,
							'medialib' => $isEditMode,
							'file_dialog' => $isEditMode,
							'cloud' => $isEditMode,
							'del' => $isEditMode,
							'description' => $isEditMode
						)
					);
					$html .= ob_get_contents();
					ob_end_clean();
				}
				$arPropsTemplate[$i]['PARAMETERS'][$name]['HTML'] = $html;
				$arPropsTemplate[$i]['BANNER_NAME'] = $str_TEMPLATE['PROPS'][$i]['BANNER_NAME'];
			}
		}
	}
}

$sDocTitle = ($ID>0 && !$bCopy) ? GetMessage("AD_EDIT_RECORD", array("#ID#" => $ID)) : GetMessage("AD_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"	=> GetMessage("AD_BACK_TO_BANNER_LIST"),
		"TITLE"	=> GetMessage("AD_BACK_TO_BANNER_LIST_TITLE"),
		"LINK"	=> "adv_banner_list.php?lang=".LANGUAGE_ID,
		"ICON"	=> "btn_list"
	)
);

$arMenuActions = array();

if (intval($ID) > 0 && !$bCopy)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> GetMessage("AD_BANNER_STATISTICS"),
		"TITLE"	=> GetMessage("AD_BANNER_STATISTICS_TITLE"),
		"LINK"	=> "adv_banner_graph.php?find_banner_id[]=".$ID."&find_what_show[]=ctr&set_filter=Y&lang=".LANGUAGE_ID,
		"ICON"	=> "btn_adv_graph"
	);

	if ($isEditMode)
	{
		$arMenuActions[] = array(
			"TEXT"	=> GetMessage("AD_BANNER_VIEW_SETTINGS"),
			"TITLE"	=> GetMessage("AD_BANNER_VIEW_SETTINGS_TITLE"),
			"LINK"	=> "adv_banner_edit.php?ID=".$ID."&lang=".LANGUAGE_ID."&action=view&CONTRACT_ID=".$CONTRACT_ID,
			"ICON"	=> "btn_adv_view"
		);
	}
	elseif (in_array("ADD", $arrPERM))
	{
		$arMenuActions[] = array(
			"TEXT"	=> GetMessage("AD_BANNER_EDIT"),
			"TITLE"	=> GetMessage("AD_BANNER_EDIT_TITLE"),
			"LINK"	=> "adv_banner_edit.php?ID=".$ID."&lang=".LANGUAGE_ID."&CONTRACT_ID=".$CONTRACT_ID,
			"ICON"	=> "btn_adv_edit"
		);
	}

	if ($isAdmin || ($isDemo && !$isOwner) || $isManager || ($isAdvertiser && in_array("ADD", $arrPERM)))
	{
		$arMenuActions[] = array(
			"TEXT"	=> GetMessage("AD_ADD_NEW_BANNER"),
			"TITLE"	=> GetMessage("AD_ADD_NEW_BANNER_TITLE"),
			"LINK"	=> "adv_banner_edit.php?lang=".LANGUAGE_ID."&CONTRACT_ID=".$CONTRACT_ID,
			"ICON"	=> "btn_new"
		);
		$arMenuActions[] = array(
			"TEXT"	=> GetMessage("AD_BANNER_COPY"),
			"TITLE"	=> GetMessage("AD_BANNER_COPY_TITLE"),
			"LINK"	=> "adv_banner_edit.php?ID=".$ID."&lang=".LANGUAGE_ID."&action=copy",
			"ICON"	=> "btn_copy"
		);
		$arMenuActions[] = array(
			"TEXT"	=> GetMessage("AD_DELETE_BANNER"),
			"TITLE"	=> GetMessage("AD_DELETE_BANNER_TITLE"),
			"LINK"	=> "javascript:if (confirm('".GetMessage("AD_DELETE_BANNER_CONFIRM")."'))window.location='adv_banner_list.php?ID=".$ID."&lang=".LANGUAGE_ID."&sessid=".bitrix_sessid()."&action=delete';",
			"ICON"	=> "btn_delete"
		);
	}
}

if (count($arMenuActions) > 0)
{
	$aMenu[] = array(
		"TEXT"	=> GetMessage("AD_ACTIONS"),
		"TITLE"	=> GetMessage("AD_ACTIONS"),
		"MENU" => $arMenuActions
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<? if ($strError <> '')
	CAdminMessage::ShowMessage(Array("MESSAGE"=>$strError, "HTML" => true, "TYPE" => "ERROR"));?>
	<style>
		#bx-admin-prefix .list-table td, #bx-admin-prefix .internal td {
			background: #fafcfc;
			border-bottom: none!important;
			color: #3f4b54!important;
			font-size: 13px;
			height: 15px!important;
			text-shadow: 0 1px #fff;
			padding: 12px 10px 12px 10px!important;
		}
		#bx-admin-prefix #adv-banner-rename-dialog-container .popup-window-close-icon{
			background-color: #fff;
		}
	</style>
	<div id="ADV_RENAME_DIALOG" class="adv-rename-dialog">
		<b><?=GetMessage("AD_RENAME_DIALOG")?></b> <br><br>
		<input type="text" id="ADV_RENAME_DIALOG_VALUE" value="">
		<br><br>
		<input type="button" id="ADV_RENAME_DIALOG_BTN_SAVE" value="<?=GetMessage("AD_APPLY")?>" class="adm-btn">
		<input type="button" id="ADV_RENAME_DIALOG_BTN_CANCEL" value="<?=GetMessage("AD_CANCEL")?>" class="adm-btn">
	</div>
	<form name="bx_adv_edit_form" method="POST" action="<?=$APPLICATION->GetCurPage()?>" enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
		<input type="hidden" name="CONTRACT_ID" value="<?=htmlspecialcharsbx($arContract["ID"])?>">
		<? if (!$bCopy): ?>
			<input type="hidden" name="action" value="<?=htmlspecialcharsbx($action)?>">
			<input type="hidden" name="ID" value="<?=$ID?>">
		<? endif ?>

		<?
		$tabControl->Begin();

		$tabControl->BeginNextTab();
		?>

		<?
		if ($ID>0) :
			if ($str_LAMP=='green') $lamp_alt = GetMessage("AD_GREEN_ALT");
			if ($str_LAMP=='red') $lamp_alt = GetMessage("AD_RED_ALT");
			$lamp = '<div class="lamp-'.$str_LAMP.'" title="'.$lamp_alt.'" style="float:left;"></div>';
			?>
			<tr valign="top">
				<td width="40%"><?=GetMessage("AD_BANNER_STATUS")?>:</td>
				<td width="60%"><?=$lamp?><?=$lamp_alt?></td>
			</tr>
		<? endif ?>

		<? if ($ID>0): ?>
			<? if ($str_DATE_CREATE <> ''): ?>
				<tr valign="top">
					<td width="40%"><?=GetMessage("AD_CREATED")?></td>
					<td width="60%"><?=$str_DATE_CREATE?><?
						if (intval($str_CREATED_BY) > 0) :
							$rsUser = CUser::GetByID($str_CREATED_BY);
							$arUser = $rsUser->Fetch();
							echo "&nbsp;&nbsp;[<a href='/bitrix/admin/user_edit.php?ID=".$str_CREATED_BY."&lang=".LANGUAGE_ID."' title='".GetMessage("AD_USER_ALT")."'>".$str_CREATED_BY."</a>]&nbsp;(".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
						endif;
						?></td>
				</tr>
			<? endif ?>
			<? if ($str_DATE_MODIFY <> ''): ?>
				<tr valign="top">
					<td><?=GetMessage("AD_MODIFIED")?></td>
					<td><?=$str_DATE_MODIFY?><?
						if (intval($str_MODIFIED_BY) > 0) :
							$rsUser = CUser::GetByID($str_MODIFIED_BY);
							$arUser = $rsUser->Fetch();
							echo "&nbsp;&nbsp;[<a href='/bitrix/admin/user_edit.php?ID=".$str_MODIFIED_BY."&lang=".LANGUAGE_ID."' title='".GetMessage("AD_USER_ALT")."'>".$str_MODIFIED_BY."</a>]&nbsp;(".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
						endif;
						?></td>
				</tr>
			<? endif ?>
		<? endif ?>

		<tr valign="top">
			<td width="40%"><?=GetMessage("AD_CONTRACT")?></td>
			<td width="60%">[<a title="<?=GetMessage("AD_CONTRACT_SETTINGS")?>" href="adv_contract_edit.php?ID=<?=$arContract["ID"]?>&action=view&lang=<?=LANGUAGE_ID?>"><?=$arContract["ID"]?></a>] <?=htmlspecialcharsbx($arContract["NAME"])?></td>
		</tr>

		<tr>
			<td width="40%"><label for="ACTIVE" ><?=GetMessage("AD_ACTIVE")?></label></td>
			<td width="60%"><?
				if ($isEditMode) :
					echo InputType("checkbox", "ACTIVE", "Y", $str_ACTIVE, false, "", 'id="ACTIVE"');
				else:
					echo ($str_ACTIVE=="Y" ? GetMessage("AD_YES") : GetMessage("AD_NO"));
				endif;
				?></td>
		</tr>
		<tr>
			<td><?=GetMessage("AD_SHOW_INTERVAL").":"?></td>
			<td><?
				if ($isEditMode) :
					echo CalendarPeriod("DATE_SHOW_FROM", $str_DATE_SHOW_FROM, "DATE_SHOW_TO", $str_DATE_SHOW_TO, "bx_adv_edit_form", "N", "", "", 20);
				else:
					if ($str_DATE_SHOW_FROM <> '') :
						echo GetMessage("AD_FROM")?>&nbsp;<b><?=$str_DATE_SHOW_FROM?></b>&nbsp;<?
					endif;
					if ($str_DATE_SHOW_TO <> '') :
						echo GetMessage("AD_TILL")?>&nbsp;<b><?=$str_DATE_SHOW_TO?></b><?
					endif;
					if ($str_DATE_SHOW_TO == '' && $str_DATE_SHOW_FROM == '')
						echo GetMessage("ADV_NOT_SET");
				endif;
				?></td>
		</tr>
		<tr>
			<td><?=GetMessage("AD_NAME")?></td>
			<td><?
				if ($isEditMode) :
					?><input type="text" maxlength="255" name="NAME" size="50" value="<?=$str_NAME?>"><?
				else:
					?><?=$str_NAME?><?
				endif;
				?></td>
		</tr>

		<tr>
			<td><?=GetMessage("AD_GROUP")?></td>
			<td><?
				if ($isEditMode) :

					$ref = array();
					$ref_id = array();
					$rsBann = CAdvBanner::GetList("s_group_sid", "asc");
					while ($arBann = $rsBann->Fetch())
					{
						if (!in_array($arBann["GROUP_SID"], $ref_id) && $arBann["GROUP_SID"] <> '')
						{
							$ref[] = $arBann["GROUP_SID"];
							$ref_id[] = $arBann["GROUP_SID"];
						}
					}
					?>
					<input type="text" maxlength="255" name="GROUP_SID" size="30" value="<?=$str_GROUP_SID?>">&nbsp;<?
				if (count($ref_id) > 0) :
				?>
					<script>
						<!--
						function SelectGroup()
						{
							var obj;
							obj = document.bx_adv_edit_form.SELECT_GROUP;
							document.bx_adv_edit_form.GROUP_SID.value = obj[obj.selectedIndex].value;
						}
						//-->
					</script>
					<?
					echo SelectBoxFromArray("SELECT_GROUP", array("reference" => $ref, "reference_id" => $ref_id), "", " ", " OnChange = SelectGroup()");
				endif;

				else:
					if ($str_GROUP_SID <> '')
						echo $str_GROUP_SID;
					else
						echo GetMessage("ADV_NOT_SET");
				endif;
				?></td>
		</tr>

		<tr>
			<td><?=GetMessage("AD_TYPE")?><? if ($isEditMode): ?><span class="required"><sup>1</sup></span><? endif ?></td>
			<td><?
				if ($isEditMode) :

					$ref = array();
					$ref_id = array();
					$arFilter = array();
					$arrCONTRACT_TYPE_SID = array_keys($arrCONTRACT_TYPE);
					if (!in_array("ALL", $arrCONTRACT_TYPE_SID))
					{
						$arFilter = array(
							"SID"				=> implode(" | ", $arrCONTRACT_TYPE_SID),
							"SID_EXACT_MATCH"	=> "Y"
						);
					}
					$rsTypies = CAdvType::GetList('', '', $arFilter);
					while ($arType = $rsTypies->Fetch())
					{
						$ref[] = "[".$arType["SID"]."] ".htmlspecialcharsbx($arType["NAME"]);
						$ref_id[] = $arType["SID"];
					}

					echo SelectBoxFromArray("TYPE_SID", array("reference" => $ref, "reference_id" => $ref_id), $str_TYPE_SID, "");

				else:
					echo "[<a href='adv_type_edit.php?SID=".urlencode($str_TYPE_SID)."&lang=".LANGUAGE_ID."&action=view' title='".GetMessage("ADV_TYPE_VIEW")."'>".htmlspecialcharsbx($str_TYPE_SID)."</a>] ".$str_TYPE_NAME;
				endif;
				?></td>
		</tr>

		<tr>
			<td width="40%"><?=GetMessage("AD_WEIGHT")?></td>
			<td width="60%"><?
				if ($isEditMode):
					?><input type="text" name="WEIGHT" value="<?=$str_WEIGHT;?>" size="10"><?
				else:
					echo $str_WEIGHT;
				endif;
				?></td>
		</tr>

		<tr class="heading">
			<td colspan="2"><b><?=GetMessage("AD_WHAT")?></b></td>
		</tr>

		<tr<? if (!$isEditMode) echo ' style="display: none;"'; ?> id='banner_type' valign="top">
			<td>
				<?=GetMessage("AD_TYPE_TEMPLATE")?><span class="required"><sup>3</sup></span>
			</td>
			<td align="left">
				<select onchange='setType(this);' name="AD_TYPE">
					<option id="AD_TYPE_IMAGE" value="image"<? if (((($ID && $str_AD_TYPE=='image')|| !$ID) && !isset($AD_TYPE)) || (isset($AD_TYPE) && ($AD_TYPE == 'image'))): ?> selected<? endif; ?>><?=GetMessage("ADV_BANNER_IMAGE")?></option>
					<option id="AD_TYPE_FLASH" value="flash"<? if (($ID && $str_AD_TYPE=='flash') || (isset($AD_TYPE) && ($AD_TYPE == 'flash'))): ?> selected<? endif; ?>><?=GetMessage("ADV_BANNER_FLASH")?></option>
					<option id="AD_TYPE_HTML" value="html"<? if (($ID && $str_AD_TYPE=='html') || (isset($AD_TYPE) && ($AD_TYPE == 'html'))): ?> selected<? endif; ?>><?=GetMessage("ADV_BANNER_HTML")?></option>
					<?$arTemplates = CComponentUtil::GetTemplatesList('bitrix:advertising.banner.view');?>
					<?foreach ($arTemplates as $k => $template): ?>
						<option id="AD_TYPE_TEMPLATE[<?=$k?>]" value="template" data-name="<?=$template['NAME']?>" <? if ((($ID && $str_AD_TYPE=='template') || (isset($AD_TYPE) && ($AD_TYPE == 'template'))) && ($template['NAME'] == $templateName)): ?> selected<? endif; ?>><?=$template['DESCRIPTION']?></option>
					<?endforeach;?>
				</select>
				<input type="hidden" id="TEMPLATE_NAME" name="TEMPLATE_NAME" value="">
				<script>
					function SwitchRows(elements, on)
					{
						for (var i=0; i<elements.length; i++)
						{
							var el = document.getElementById(elements[i]);
							if (el)
								el.style.display = (on? '':'none');
						}
					}

					function changeTemplateNodes(action, ind)
					{
						var bannerProperties = BX('ADV_BANNER_PROPERTIES_CONTAINER');
						if (typeof ind !== 'undefined' && !bannerProperties.childNodes[ind])
							return;

						var node = BX('eTemplateComponent'),
							addTemplate = BX('eAddTemplateBanner'),
							clean, nextClean;

						if (typeof ind === 'undefined')
						{
							clean = bannerProperties.firstChild;
							while (!!clean)
							{
								nextClean = clean.nextSibling;
								if (action == 'clean')
									clean.parentNode.removeChild(clean);
								else if (action == 'fade')
									clean.style.display = 'none';
								else
								{
									clean.style.display = '';
									node.style.display = '';
									addTemplate.style.display = '';
								}
								clean = nextClean;
							}
						}
						else
						{
							clean = bannerProperties.childNodes[ind];
							if (action == 'clean')
								clean.parentNode.removeChild(clean);
							else if (action == 'fade')
								clean.style.display = 'none';
							else
							{
								clean.style.display = '';
								node.style.display = '';
								addTemplate.style.display = '';
							}
						}
					}

					function setType(node)
					{
						var selected;
						if (node)
						{
							for (var i in node)
							{
								if (node[i].selected)
								{
									selected = node[i].value;
									break;
								}
							}
						}
						changeType(selected);
					}

					var changeType = function(type, params)
					{
						if (!type)
							type = 'image';

						if (type == 'image')
						{
							SwitchRows(['eAltImage','eFileLoaded','eFlashUrl','eFlashTrans','eFlashJs','eFlashVer','eTemplatePropsHead','eTemplateComponent','eTemplateComponentHead','eAddTemplateBanner','eTemplateProperties','eExtMode'], false);
							SwitchRows(['eFile','eUrl','eImageAlt','eUrlTarget', 'eCodeHeader'], true);
							changeTemplateNodes('fade');
						}
						else if (type == 'flash')
						{
							SwitchRows(['eCodeHeader','eFile',<? if ($str_AD_TYPE=='flash')echo "'eFileLoaded',";?>'eFlashUrl','eFlashJs','eFlashTrans', 'eUrl', 'eUrlTarget', 'eImageAlt'], true);
							SwitchRows(['eAltImage', 'eFlashVer'], document.getElementById('FLASH_JS').checked);
							SwitchRows(['eTemplatePropsHead','eTemplateComponent','eTemplateComponentHead','eAddTemplateBanner','eTemplateProperties','eExtMode'], false);
							changeTemplateNodes('fade');
						}
						else if (type == 'html')
						{
							SwitchRows(['eFlashUrl','eFile','eFileLoaded','eFlashJs','eFlashTrans','eExtMode',
								'eAltImage','eImageAlt','eUrl','eUrlTarget','eCodeHeader','eFlashVer','eTemplatePropsHead','eTemplateComponent','eTemplateComponentHead','eAddTemplateBanner','eTemplateProperties'], false);
							SwitchRows(['eCode'], true);
							changeTemplateNodes('fade');
						}
						else if (type == 'template')
						{
							SwitchRows(['eFlashUrl','eFile','eFileLoaded','eFlashJs','eFlashTrans',
								'eAltImage','eImageAlt','eUrl','eUrlTarget','eCodeHeader','eFlashVer', 'eCode'], false);
							SwitchRows(['eExtMode', 'eTemplateProperties'], true);
							changeTemplateNodes();
							BX.ready(function() {
								BX('TEMPLATE_NAME').value = window.oBXBannerTemplate.getName();
								if (!!params)
									window.oBXBannerTemplate.createFromDB(params);
								else
									window.oBXBannerTemplate.select(params);

								<? if ($isEditMode): ?>
									window.oBXBannerTemplate.initDraggableItems();
								<? endif ?>
							});
						}
					}

					function iFrameAutoResize(id)
					{
						setTimeout(function(){
							var newheight, frame = document.getElementById(id);
							if (BX.browser.IsFirefox())
							{
								frame.style.display = '';
								newheight = Math.max(
									frame.contentWindow.document.body.offsetHeight, frame.contentWindow.document.documentElement.offsetHeight,
									frame.contentWindow.document.body.clientHeight, frame.contentWindow.document.documentElement.clientHeight
								);
							}
							else
							{
								newheight = Math.max(
									frame.contentWindow.document.body.scrollHeight, frame.contentWindow.document.documentElement.scrollHeight,
									frame.contentWindow.document.body.offsetHeight, frame.contentWindow.document.documentElement.offsetHeight,
									frame.contentWindow.document.body.clientHeight, frame.contentWindow.document.documentElement.clientHeight
								);
							}
							frame.height= (newheight) + "px";
							if (newheight > 20)
							{
								BX('eTemplateComponentHead').style.display = '';
								if (!BX.browser.IsFirefox())
								{
									var easing = new BX.easing({
										duration : 500,
										start : { height : 0, opacity : 0 },
										finish : { height : 100, opacity: 100 },
										transition : BX.easing.transitions.quart,
										step : function(state){
											frame.style.opacity = state.opacity/100;
											frame.style.display = '';
										},
										complete : function() {
										}
									});
									easing.animate();
								}
							}
						}, 150)
					}

					function changeUploaderInputs()
					{
						var arrInputs = document.body.querySelectorAll('form[name="bx_adv_edit_form"]>input'),
							pattern = new RegExp ("^TEMPLATE_FILES", "ig"),
							fileInput;

						for (var i in arrInputs)
						{
							if (arrInputs.hasOwnProperty(i))
							{
								fileInput = arrInputs[i].name.match(pattern);
								if (fileInput)
								{
									arrInputs[i].parentNode.removeChild(arrInputs[i]);
								}
							}
						}
					}

					BX.ready(
						function(){
							setTimeout(function(){
								var button;

								if (button = document.body.querySelector('input[name="savebtn"]'))
								{
									BX.bind(button, 'click', changeUploaderInputs);
								}

								if (button = document.body.querySelector('input[name="save"]'))
								{
									BX.bind(button, 'click', changeUploaderInputs);
								}

								if (button = document.body.querySelector('input[name="apply"]'))
								{
									BX.bind(button, 'click', changeUploaderInputs);
								}
							}, 100);
						}
					);
				</script>
			</td>
		</tr>

		<tr id='eExtMode' style="display:none;">
			<td><label for="EXTENDED_MODE"><?=GetMessage("AD_EXTENDED_MODE")?></label></td>
			<td>
				<input type="hidden" name="EXTENDED_MODE" value="N" />
				<?=InputType("checkbox", "EXTENDED_MODE", "Y", '', false, "", 'id="EXTENDED_MODE" onclick="window.oBXBannerTemplate.refreshAll()"');?>
			</td>
		</tr>

		<? if ($isEditMode || intval($str_IMAGE_ID) > 0): ?>
			<? if ($isEditMode): ?>
				<tr valign="top" id="eFile" style="display: none;">
					<td><?=GetMessage("ADV_BANNER_FILE")?><span class="required"><sup>1</sup></span></td>
					<td><? if ($bCopy): ?>
							<input type="hidden" name="IMAGE_ID_copy" value="<?=$str_IMAGE_ID?>">
						<? endif ?>
						<? if (class_exists('\Bitrix\Main\UI\FileInput', true))
						{
							echo \Bitrix\Main\UI\FileInput::createInstance(array(
								"name" => "IMAGE_ID",
								"description" => false,
								"upload" => true,
								"medialib" => true,
								"fileDialog" => true,
								"cloud" => true,
								"delete" => true,
								"maxCount" => 1
							))->show($str_IMAGE_ID);
						}
						else
						{
							echo CFileInput::Show("IMAGE_ID", $str_IMAGE_ID,
								array(
									"IMAGE" => $str_AD_TYPE == 'image' ? "Y" : "N",
									"PATH" => "Y",
									"FILE_SIZE" => "Y",
									"DIMENSIONS" => "Y",
									"IMAGE_POPUP" => "Y"
								), array(
									'upload' => true,
									'medialib' => true,
									'file_dialog' => true,
									'cloud' => true,
									'del' => true,
									'description' => false,
								)
							);
						}?>
					</td>
				</tr>
			<? endif ?>

			<?
			if (intval($str_IMAGE_ID) > 0) :
				?>
				<tr valign="top" id="eFileLoaded" style="display: none;">
					<td align="center" colspan="2"><?
						echo CAdvBanner_all::GetHTML(array(
							"IMAGE_ID" => $str_IMAGE_ID,
							"FLASH_JS" => $str_FLASH_JS,
							"FLASH_IMAGE" => $str_FLASH_IMAGE,
							"FLASH_TRANSPARENT" => $str_FLASH_TRANSPARENT,
							"FLASH_VER" => $str_FLASH_VER,
						));?></td>
				</tr>
			<? endif ?>

			<tr id="eFlashTrans" style="display: none;">
				<td><?=GetMessage('AD_FLASH_TRANSPARENT')?></td>
				<td>
					<select id="FLASH_TRANSPARENT" name="FLASH_TRANSPARENT">
						<option value="transparent"<? if ($str_FLASH_TRANSPARENT == 'transparent'): ?> selected="selected"<? endif; ?>>transparent</option>
						<option value="opaque"<? if ($str_FLASH_TRANSPARENT == 'opaque'): ?> selected="selected"<? endif; ?>>opaque</option>
						<option value="window"<? if ($str_FLASH_TRANSPARENT == 'window'): ?> selected="selected"<? endif; ?>>window</option>
					</select>
				</td>
			</tr>
			<tr id="eFlashJs" style="display: none;">
				<td><?=GetMessage('AD_FLASH_JS')?> <?=GetMessage('AD_FLASH_JS_DESCRIPTION')?></td>
				<td>
					<input type="checkbox" id="FLASH_JS" onclick="SwitchRows(['eAltImage', 'eFlashVer'], this.checked);" name="FLASH_JS" value="Y"<? if ($str_FLASH_JS == 'Y') echo ' checked="checked"'; ?> />
				</td>
			</tr>

			<tr id="eFlashVer" style="display: none;">
				<td><?=GetMessage('ADV_FLASH_VERSION')?></td>
				<td>
					<input type="text" name="FLASH_VER" maxlength="20" size="20"  value="<?=$str_FLASH_VER?>">
				</td>
			</tr>

			<tr valign="top" id="eAltImage" style="display: none;">
				<td><?=GetMessage("ADV_FLASH_IMAGE")?></td>
				<td><? if ($bCopy): ?>
						<input type="hidden" name="FLASH_IMAGE" value="<?=$str_FLASH_IMAGE?>">
					<? endif ?>
					<? if (class_exists('\Bitrix\Main\UI\FileInput', true))
					{
						echo \Bitrix\Main\UI\FileInput::createInstance(array(
							"name" => "FLASH_IMAGE",
							"description" => false,
							"allowUpload" => "I",
							"upload" => true,
							"medialib" => true,
							"fileDialog" => true,
							"cloud" => true,
							"delete" => true,
							"maxCount" => 1
						))->show($str_FLASH_IMAGE);
					}
					else
					{
						echo CFileInput::Show("FLASH_IMAGE", $str_FLASH_IMAGE,
							array(
								"IMAGE" => "Y",
								"PATH" => "Y",
								"FILE_SIZE" => "Y",
								"DIMENSIONS" => "Y",
								"IMAGE_POPUP" => "Y"
							), array(
								'upload' => true,
								'medialib' => true,
								'file_dialog' => true,
								'cloud' => true,
								'del' => true,
								'description' => false,
							)
						);
					}
					?>
				</td>
			</tr>
			<tr id="eFlashUrl" style="display: none;">
				<td><?=GetMessage("ADV_BANNER_NO_LINK")?>:<? if ($isEditMode): ?><span class="required"><sup>1</sup></span><? endif ?></td>
				<td><input type="checkbox" id="NO_URL_IN_FLASH" name="NO_URL_IN_FLASH" value="Y"<? if ($str_NO_URL_IN_FLASH=="Y") echo " checked";?><? if (!$isEditMode) echo ' disabled="true"';?> id="NO_URL_IN_FLASH"></td>
			</tr>
			<tr id="eUrl" style="display: none;">
				<td valign="top"><?=GetMessage("AD_URL");?><? if ($isEditMode): ?><span class="required"><sup>1</sup></span><? endif ?></td>
				<td><?
					if ($isEditMode) :
						?><input id="iUrl" type="text" size="50" name="URL" value="<?=$str_URL?>"><?
					else:
						if ($str_URL <> '')
							echo $str_URL;
						else
							echo GetMessage("ADV_NOT_SET");
					endif;

					if ($isEditMode): ?>
						<script>
							function PutEventGID(str)
							{
								document.bx_adv_edit_form.URL.value += str;
							}
						</script>
						<br /><?=str_replace("#EVENT_GID#", "<a href=\"javascript:PutEventGID('#EVENT_GID#')\"  title='".GetMessage("AD_INS_TEMPL")."'>#EVENT_GID#</a>", GetMessage("AD_CAN_USE_EVENT_GID"))?>
						<?
					endif;
					?></td>
			</tr>
			<tr valign="top" id="eUrlTarget" style="display: none;">
				<td><?=GetMessage("AD_URL_TARGET");?><? if ($isEditMode): ?><span class="required"><sup>1</sup></span><? endif ?></td>
				<td><?
					$ref = array(
						GetMessage("AD_SELF_WINDOW"),
						GetMessage("AD_BLANK_WINDOW"),
						GetMessage("AD_PARENT_WINDOW"),
						GetMessage("AD_TOP_WINDOW"),
					);
					$ref_id = array(
						"_self",
						"_blank",
						"_parent",
						"_top"
					);

					if ($isEditMode) :
						?>
						<script>
							<!--
							function SelectUrlTarget()
							{
								var obj;
								obj = document.bx_adv_edit_form.SELECT_URL_TARGET;
								document.bx_adv_edit_form.URL_TARGET.value = obj[obj.selectedIndex].value;
							}
							//-->
						</script>
						<input type="text" id="iURL_TARGET" maxlength="255" name="URL_TARGET" size="30" value="<?=$str_URL_TARGET?>"> <?
						echo SelectBoxFromArray("SELECT_URL_TARGET", array("reference" => $ref, "reference_id" => $ref_id), "", " ", " OnChange = SelectUrlTarget()");
					else:
						$key = array_search($str_URL_TARGET, $ref_id);
						if ($ref[$key] <> '') echo $ref[$key]; else echo $str_URL_TARGET;
					endif;
					?></td>
			</tr>
			<tr valign="top" id="eImageAlt" style="display: none;">
				<td><?=GetMessage("AD_IMAGE_ALT")?><? if ($isEditMode): ?><span class="required"><sup>1</sup></span><? endif ?></td>
				<td><?
					if ($isEditMode) :
						?><input type="text" name="IMAGE_ALT" maxlength="255" size="50" value="<?=$str_IMAGE_ALT?>"><?
					else:
						if ($str_IMAGE_ALT <> '')
							echo $str_IMAGE_ALT;
						else
							echo GetMessage("ADV_NOT_SET");
					endif;
					?></td>
			</tr>
			<? if ($isEditMode): ?>
				<tr valign="top" style="display: none;" id="eCodeHeader">
					<td colspan="2" align="center"><a href="javascript:void(0)" onclick="SwitchRows(['eCode'], document.getElementById('eCode').style.display == 'none')"><b><?=GetMessage("AD_OR");?></b></a></td>
				</tr>
			<? endif; ?>
		<? endif ?>
		<script>
			var t = null;
			function PutRandom(str)
			{
				document.bx_adv_edit_form.CODE.value += str;
				BX.fireEvent(document.bx_adv_edit_form.CODE, 'change');
			}
		</script>
		<tr valign="top" id="eCode" style="display:<? if ($str_AD_TYPE <> 'html'): ?>none<? endif?>;">
			<td align="center" colspan="2">
				<table width="95%" cellspacing="0" border="0" cellpadding="0">
					<? if ($isEditMode):
						if (COption::GetOptionString("advertising", "USE_HTML_EDIT", "Y")=="Y" && CModule::IncludeModule("fileman")): ?>
							<tr valign="top">
								<td align="center" colspan="2"><?
									if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
										CFileMan::AddHTMLEditorFrame("CODE", $str_CODE, "CODE_TYPE", $str_CODE_TYPE, array('height' => 450, 'width' => '100%'), "N", 0, "", "onfocus=\"t=this\"");
									else
										CFileMan::AddHTMLEditorFrame("CODE", $str_CODE, "CODE_TYPE", $str_CODE_TYPE, 300, "N", 0, "", "onfocus=\"t=this\"");
									?></td>
							</tr>
						<? else: ?>
							<tr valign="top">
								<td align="center" colspan="2"><? echo InputType("radio", "CODE_TYPE","text",$str_CODE_TYPE,false)?><?=GetMessage("AD_TEXT")?>/&nbsp;<? echo InputType("radio","CODE_TYPE","html",$str_CODE_TYPE,false)?>&nbsp;HTML&nbsp;</td>
							</tr>
							<tr>
								<td align="center"><textarea style="width:100%" rows="30" name="CODE" onfocus="t=this"><?=$str_CODE?></textarea></td>
							</tr>
						<? endif;
					else: ?>
						<? if ($str_CODE <> ''): ?>
							<tr valign="top">
								<td align="center" colspan="2"><? echo InputType("radio", "CODE_TYPE","text",$str_CODE_TYPE,false, "", " disabled")?><?=GetMessage("AD_TEXT")?>/&nbsp;<? echo InputType("radio","CODE_TYPE","html",$str_CODE_TYPE,false, "", " disabled")?>&nbsp;HTML&nbsp;</td>
							</tr>
							<tr>
								<td align="center"><?=($str_CODE_TYPE == "text")? $str_CODE : htmlspecialcharsback($str_CODE)?></td>
							</tr>
						<? endif ?>
					<? endif ?>
					<? if ($isEditMode): ?>
						<tr>
							<td><?=GetMessage("AD_HTML_ALT")?>&nbsp;<a href="javascript:PutRandom('#RANDOM1#')" title="<?=GetMessage("AD_INS_TEMPL")?>">#RANDOM1#</a>,
								<a href="javascript:PutRandom('#RANDOM2#')" title="<?=GetMessage("AD_INS_TEMPL")?>">#RANDOM2#</a>,
								<a href="javascript:PutRandom('#RANDOM3#')" title="<?=GetMessage("AD_INS_TEMPL")?>">#RANDOM3#</a>,
								<a href="javascript:PutRandom('#RANDOM4#')" title="<?=GetMessage("AD_INS_TEMPL")?>">#RANDOM4#</a>,
								<a href="javascript:PutRandom('#RANDOM5#')" title="<?=GetMessage("AD_INS_TEMPL")?>">#RANDOM5#</a>
							</td>
						</tr>
					<? endif ?>
				</table></td>
		</tr>
		<?
		$APPLICATION->IncludeComponent('bitrix:main.colorpicker', '', array('SHOW_BUTTON' => 'N'), false, array('HIDE_ICONS' => 'Y'));

		CJSCore::RegisterExt('adv_dragdrop', array('js' => '/bitrix/js/main/core/core_dragdrop.js'));
		\CJSCore::Init(array("adv_templates", "adv_dragdrop"));
		?>
		<script>
			BX.ready(function() {
				window.oBXBannerTemplate = new window.BXBannerTemplate({
					curPage: '<?=$APPLICATION->GetCurPage()?>',
					bCopy: !!<?=intval($bCopy)?>,
					adminMode: !!<?=(!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1) ? '1' : '0'?>,
					canEdit: !!<?=$isEditMode ? '1' : '0'?>,
					lang: {
						DELETE: '<?=CUtil::JSEscape(GetMessage("AD_DELETE"))?>',
						HIDE: '<?=CUtil::JSEscape(GetMessage("AD_HIDE"))?>',
						SHOW: '<?=CUtil::JSEscape(GetMessage("AD_SHOW"))?>',
						NAME: '<?=CUtil::JSEscape(GetMessage("AD_SLIDE_NAME"))?>',
						SLIDE: '<?=CUtil::JSEscape(GetMessage("AD_SLIDE"))?>',
						SELECT_COLOR: '<?=CUtil::JSEscape(GetMessage("AD_SELECT_COLOR"))?>',
						YES: '<?=CUtil::JSEscape(GetMessage("AD_YES"))?>',
						NO: '<?=CUtil::JSEscape(GetMessage("AD_NO"))?>'
					}
				});
			});
		</script>
		<tr id="eTemplateComponentHead" class="heading" style="display:none;">
			<? if ((!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1) && COption::GetOptionString('advertising', 'SHOW_COMPONENT_PREVIEW') == "Y"): ?>
				<td colspan="2"><b><?=GetMessage("AD_PREVIEW")?></b></td>
			<? endif ?>
		</tr>
		<tr id="eTemplateComponent" valign="top" style="display: none;">
			<? if ((!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1) && COption::GetOptionString('advertising', 'SHOW_COMPONENT_PREVIEW') == "Y"): ?>
				<td align="center" colspan="2">
					<script>window.cWidth = BX('eCode').parentNode.clientWidth;</script>
					<iframe src="adv_banner_preview.php?id=<?=intval($_GET['ID'])?>&name=<?=$str_TEMPLATE["NAME"]?>&bitrix_include_areas=N" id="componentIframe" style="width:100%;display:none;" scrolling="no" frameBorder="0" onLoad="iFrameAutoResize('componentIframe');"></iframe>
				</td>
			<? endif ?>
		</tr>
		<tr id="eTemplateProperties">
			<td colspan="2">
				<div id="ADV_BANNER_PROPERTIES_CONTAINER" style="margin-top:5px;"></div>
			</td>
		</tr>
		<tr id="eAddTemplateBanner" style="display:none;">
			<? if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1): ?>
				<td colspan="2" align="center"><a href="#" onclick='window.oBXBannerTemplate.addNewTBanner();return false;' class="adm-btn adm-btn-add adm-btn-save" title="<?=GetMessage("AD_ADD_SLIDE")?>"><?=GetMessage("AD_ADD_SLIDE")?></a></td>
			<? else: ?>
				<td colspan="2" style="text-align: center;"><input type="button" onclick='window.oBXBannerTemplate.addNewTBanner();return false;' class="adm-btn-save" value="<?=GetMessage("AD_ADD_SLIDE")?>" title="<?=GetMessage("AD_ADD_SLIDE")?>"></td>
			<? endif ?>
		</tr>
		<tr class="heading">
			<td colspan="2"><b><?=GetMessage("AD_BANNER_STATUS")?></b></td>
		</tr>
		<? if ($isAdmin || ($isDemo && !$isOwner) || $isManager) : ?>
			<tr>
				<td><?=GetMessage("AD_STATUS")?></td>
				<td><?
					$arrStatus = CAdvBanner::GetStatusList();
					if ($ID == 0)
					{
						$arrDefStatus = CAdvContract::GetByID($str_CONTRACT_ID);
						if ($defStatus = $arrDefStatus->Fetch())
						{
							$str_STATUS_SID = $defStatus['DEFAULT_STATUS_SID'];
						}

					}
					echo SelectBoxFromArray("STATUS_SID", $arrStatus, $str_STATUS_SID, " ");
					?></td>
			</tr>
		<? elseif ($ID>0): ?>
			<tr>
				<td><?=GetMessage("AD_STATUS")?></td>
				<td><?
					$arrStatus = CAdvBanner::GetStatusList();
					$key = array_search($str_STATUS_SID, $arrStatus["reference_id"]);
					if ($key!==false) echo $arrStatus["reference"][$key];
					?></td>
			</tr>
		<? endif ?>

		<? if ($isAdmin || ($isDemo && !$isOwner) || $isManager) : ?>
			<tr valign="top">
				<td><?=GetMessage("AD_STATUS_COMMENTS")?></td>
				<td><textarea cols="35" name="STATUS_COMMENTS" rows="3" wrap="VIRTUAL"><?=$str_STATUS_COMMENTS?></textarea></td>
			</tr>
		<? elseif ($str_STATUS_COMMENTS <> ''): ?>
			<tr valign="top">
				<td><?=GetMessage("AD_STATUS_COMMENTS")?></td>
				<td><?=TxtToHtml($str_STATUS_COMMENTS)?></td>
			</tr>
		<? endif ?>

		<? if ($isAdmin || ($isDemo && !$isOwner) || $isManager) : ?>
			<tr>
				<td><label for="SEND_EMAIL"><?=GetMessage("AD_SEND_EMAIL")?></label></td>
				<td><?=InputType("checkbox", "SEND_EMAIL", "Y", $SEND_EMAIL, false, "", 'id="SEND_EMAIL"');?></td>
			</tr>
		<? endif ?>
		<?
		$tabControl->BeginNextTab();
		?>

		<? if ($isAdmin || ($isDemo && !$isOwner) || $isManager): ?>
			<script>
				function DisableFixShow(check)
				{
					document.getElementById("MAX_VISITOR_COUNT").disabled
						= document.getElementById("SHOWS_FOR_VISITOR").disabled
						= document.getElementById("MAX_SHOW_COUNT").disabled
						= !check;

					if (document.getElementById("RESET_VISITOR_COUNT"))
						document.getElementById("RESET_VISITOR_COUNT").disabled = !check;

					if (document.getElementById("RESET_SHOW_COUNT"))
						document.getElementById("RESET_SHOW_COUNT").disabled = !check;
				}
			</script>
		<?$disableFixShow = ($str_FIX_SHOW != "Y" ? " disabled" : "");?>
			<tr valign="top">
				<td width="40%"><label for="FIX_SHOW"><?=GetMessage("AD_FIX_SHOW")?></label></td>
				<td width="60%"><?
					if ($isEditMode):
						echo InputType("checkbox", "FIX_SHOW", "Y", $str_FIX_SHOW, false, "", 'id="FIX_SHOW" OnClick="DisableFixShow(this.checked);"');
					else:
						?><?=($str_FIX_SHOW=="Y" ? GetMessage("AD_YES") : GetMessage("AD_NO"))?><?
					endif;
					?></td>
			</tr>
		<? if (COption::GetOptionString('advertising', 'DONT_FIX_BANNER_SHOWS') == "Y"): ?>
			<tr><td colspan="2"><?=BeginNote()?><?=GetMessage("AD_EDIT_NOT_FIX")?><?=EndNote()?></td></tr>
		<? endif ?>
		<? endif ?>

		<tr valign="top">
			<td width="40%"><label for="FLYUNIFORM"><?=GetMessage("AD_UNIFORM")?></label><span class="required"><sup>2</sup></span></td>
			<td width="60%"><?
				if ($isEditMode):
					echo InputType("checkbox", "FLYUNIFORM", "Y", $str_FLYUNIFORM, false, "", 'id="FLYUNIFORM"');
				else:
					?><?=($str_FLYUNIFORM=="Y" ? GetMessage("AD_YES") : GetMessage("AD_NO"))?><?
				endif;
				?></td>
		</tr>

		<? if (!$isEditMode): ?>
			<tr valign="top">
				<td><?=GetMessage("AD_VISITOR_COUNT_2")?></td>
				<td><b><?=intval($str_VISITOR_COUNT)?></b>&nbsp;/&nbsp;<?=$str_MAX_VISITOR_COUNT?></td>
			</tr>
		<? else: ?>
			<tr>
				<td><?=GetMessage("AD_VISITOR_COUNT")?></td>
				<td><input type="text" name="MAX_VISITOR_COUNT" id="MAX_VISITOR_COUNT" size="10" value = "<?=$str_MAX_VISITOR_COUNT?>"<?=$disableFixShow?>><?
					if ($ID>0) :
						?>&nbsp;<?=GetMessage("AD_VISITORS")?>&nbsp;<b><?=$str_VISITOR_COUNT?></b>&nbsp;&nbsp;<?
						if ($isAdmin || ($isDemo && !$isOwner) || $isManager)
						{
							echo '<label for="RESET_VISITOR_COUNT">'.GetMessage("AD_RESET_COUNTER")."</label>";
							?>&nbsp;<input type="checkbox" name="RESET_VISITOR_COUNT" value="Y" id="RESET_VISITOR_COUNT"<?=$disableFixShow?>><?
						}
					endif;
					?></td>
			</tr>
		<? endif ?>

		<tr>
			<td><?=GetMessage("AD_SHOWS_FOR_VISITOR")?></td>
			<td><?
				if ($isEditMode) :
					?><input type="text" name="SHOWS_FOR_VISITOR" id="SHOWS_FOR_VISITOR" value="<?=$str_SHOWS_FOR_VISITOR?>" size="10"<?=$disableFixShow?>><?
				else:
					if (intval($str_SHOWS_FOR_VISITOR) > 0)
						echo $str_SHOWS_FOR_VISITOR;
					else
						echo GetMessage("ADV_NO_LIMITS");
				endif;
				?></td>
		</tr>

		<? if (!$isEditMode): ?>
			<tr valign="top">
				<td><?=GetMessage("AD_SHOW_COUNT_2")?></td>
				<td><b><?=intval($str_SHOW_COUNT)?></b>&nbsp;/&nbsp;<?=intval($str_MAX_SHOW_COUNT)?></td>
			</tr>
		<? else: ?>
			<tr>
				<td><?=GetMessage("AD_SHOW_COUNT")?></td>
				<td><input type="text" name="MAX_SHOW_COUNT" id="MAX_SHOW_COUNT" size="10" value = "<?=$str_MAX_SHOW_COUNT?>"<?=$disableFixShow?>><?
					if ($ID>0) :
						?>&nbsp;<?=GetMessage("AD_SHOWN")?>&nbsp;<b><?=$str_SHOW_COUNT?></b>&nbsp;&nbsp;<?
						if ($isAdmin || ($isDemo && !$isOwner) || $isManager)
						{
							echo '<label for="RESET_SHOW_COUNT">'.GetMessage("AD_RESET_COUNTER").'</label>';
							?>&nbsp;<input type="checkbox" name="RESET_SHOW_COUNT" value="Y" id="RESET_SHOW_COUNT"<?=$disableFixShow?>><?
						}
					endif;
					?></td>
			</tr>
		<? endif ?>

		<? if (!$isEditMode): ?>
			<tr valign="top">
				<td><?=GetMessage("AD_CLICK_COUNT_2")?></td>
				<td><b><?=intval($str_CLICK_COUNT)?></b>&nbsp;/&nbsp;<?=$str_MAX_CLICK_COUNT?></td>
			</tr>
		<? else: ?>
			<tr>
				<td><?=GetMessage("AD_CLICK_COUNT")?></td>
				<td><input type="text" name="MAX_CLICK_COUNT" id="MAX_CLICK_COUNT" size="10" value = "<?=$str_MAX_CLICK_COUNT?>"><?
					if ($ID>0) :
						?>&nbsp;<?=GetMessage("AD_CLICKED")?>&nbsp;<b><?=$str_CLICK_COUNT?></b>&nbsp;&nbsp;&nbsp;<?
						if ($isAdmin || ($isDemo && !$isOwner) || $isManager)
						{
							echo '<label for="RESET_CLICK_COUNT">'.GetMessage("AD_RESET_COUNTER").'</label>';
							?>&nbsp;<input type="checkbox" name="RESET_CLICK_COUNT" value="Y" id="RESET_CLICK_COUNT"><?
						}
					endif;
					?></td>
			</tr>
		<? endif ?>

		<? if ($ID>0): ?>
			<tr valign="top">
				<td><?=GetMessage("AD_CTR")?></td>
				<td><b><?=$str_CTR?></b></td>
			</tr>
		<? endif ?>



		<?
		$tabControl->BeginNextTab();
		?>
		<tr valign="top">
			<td width="40%"><?=GetMessage("AD_SITE")?></td>
			<td width="60%"><?

				$arrContractSite =  CAdvContract::GetSiteArray($str_CONTRACT_ID);

				if (is_array($arrContractSite)):

					if ($isEditMode) : ?>
						<div class="adm-list">
							<?
							foreach ($arrSites as $sid => $arrS):
								if (in_array($sid, $arrContractSite)) :
									$checked = (in_array($sid, $arrSITE)) ? "checked" : "";
									/*<?=$disabled?>*/
									?>
									<div class="adm-list-item">
										<div class="adm-list-control"><input type="checkbox" name="arrSITE[]" value="<?=htmlspecialcharsbx($sid)?>" style="vertical-align:baseline; border-spacing: 0px; margin: 0px; padding: 0px;" id="site_<?=htmlspecialcharsbx($sid)?>" <?=$checked?>></div>
										<div class="adm-list-label"><?='[<a href="/bitrix/admin/site_edit.php?LID='.urlencode($sid).'&lang='.LANGUAGE_ID.'" title="'.GetMessage("AD_SITE_ALT").'">'.htmlspecialcharsex($sid).'</a>]&nbsp;<label for="site_'.htmlspecialcharsbx($sid).'">'.htmlspecialcharsex($arrS["NAME"])?></label></div>
									</div>
									<?
								endif;
							endforeach;?>
						</div>
					<? else:

						reset($arrSITE);
						if (is_array($arrSITE)):
							foreach ($arrSITE as $sid):
								if (in_array($sid, $arrContractSite)) :
									$arS = $arrSites[$sid];
									echo '[<a href="/bitrix/admin/site_edit.php?LID='.urlencode($arS["ID"]).'&lang='.LANGUAGE_ID.'" title="'.GetMessage("AD_SITE_ALT").'">'.htmlspecialcharsex($arS["ID"]).'</a>] '.htmlspecialcharsex($arS["NAME"]).'<br>';
								endif;
							endforeach;
						endif;

					endif;

				endif;
				?></td>
		</tr>

		<tr valign="top">
			<td><?=GetMessage("AD_SHOW_PAGES");?></td>
			<td><?
				if ($isEditMode) :
					?>
					<textarea name="SHOW_PAGE" cols="45" rows="6" wrap="OFF"><?=Main\Text\HtmlFilter::encode($str_SHOW_PAGE)?></textarea>
					<br>
					<?=GetMessage("AD_PAGES_ALT1")?>
					<?
				else:
					$arr = $arrSHOW_PAGE;
					if (is_array($arr) && count($arr) > 0)
					{
						foreach ($arr as $page)
							echo htmlspecialcharsbx($page).'<br>';
					}
					else
					{
						echo GetMessage("ADV_NO_LIMITS");
					}
				endif;
				?></td>
		</tr>
		<tr valign="top">
			<td><?=GetMessage("AD_NOT_SHOW_PAGES");?></td>
			<td><?
				if ($isEditMode) :
					?>
					<textarea name="NOT_SHOW_PAGE" cols="45" rows="6" wrap="OFF"><?=Main\Text\HtmlFilter::encode($str_NOT_SHOW_PAGE)?></textarea>
					<br>
					<?=GetMessage("AD_PAGES_ALT1")?>
					<?
				else:
					$arr = $arrNOT_SHOW_PAGE;
					if (is_array($arr) && count($arr) > 0)
					{
						foreach ($arr as $page)
							echo htmlspecialcharsbx($page).'<br>';
					}
					else
					{
						echo GetMessage("ADV_NO_LIMITS");
					}
				endif;
				?></td>
		</tr>

		<? if ($isEditMode):
			$rUserGroups = CGroup::GetList("name", "asc", array("ANONYMOUS"=>"N"));
			while ($arUserGroups = $rUserGroups->Fetch())
			{
				$ug_id[] = $arUserGroups["ID"];
				$ug[] = $arUserGroups["NAME"]." [".$arUserGroups["ID"]."]";
			}
			?>
			<tr valign="top">
				<td><?=GetMessage("AD_USER_GROUPS");?><br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""><br><?=GetMessage("AD_SELECT_WHAT_YOU_NEED")?></td>
				<td>
					<input type="radio" id="SHOW_USER_LABEL_Y" name="SHOW_USER_GROUP" value="Y" <? if ($str_SHOW_USER_GROUP=="Y") echo "checked";?>><label for="SHOW_USER_LABEL_Y"><?=GetMessage("AD_USER_GROUP_Y");?></label> <br>
					<input type="radio" id="SHOW_USER_LABEL_N" name="SHOW_USER_GROUP" value="N" <? if ($str_SHOW_USER_GROUP!="Y") echo "checked";?>><label for="SHOW_USER_LABEL_N"><?=GetMessage("AD_USER_GROUP_N");?></label><br>
					<?=SelectBoxMFromArray("arrUSERGROUP[]", array("REFERENCE" => $ug, "REFERENCE_ID" => $ug_id), $arrUSERGROUP, "", false, 10);?></td>
			</tr>
		<? else:
			$ug = '';
			$rUserGroups = CGroup::GetList("name", "asc", Array("ID"=>implode(" | ",$arrUSERGROUP), "ANONYMOUS"=>"N"));
			while ($arUserGroups = $rUserGroups->Fetch())
			{
				$ug .= $arUserGroups["NAME"].' [<a href="group_edit.php?ID='.$arUserGroups["ID"].'&lang='.LANGUAGE_ID.'" title="'.GetMessage("ADV_VIEW_UGROUP").'">'.$arUserGroups["ID"].'</a>]<br>';
			}
			?>
			<tr valign="top">
				<? if ($ug <> '' && !empty($arrUSERGROUP)): ?>
					<td><?=GetMessage("AD_USER_GROUP_".$str_SHOW_USER_GROUP);?>:</td>
					<td><?=$ug?></td>
				<? else: ?>
					<td><?=GetMessage("AD_USER_GROUP_Y");?>:</td>
					<td><?=GetMessage("AD_ALL_1");?></td>
				<? endif ?>
			</tr>
		<? endif ?>


		<? if ($isAdmin || $isManager || ($isDemo && !$isOwner)): ?>
			<tr valign="top">
				<td><?=GetMessage("AD_KEYWORDS");?></td>
				<td><?
					if ($isEditMode) :
						?><textarea name="KEYWORDS" cols="45" rows="6" wrap="OFF"><?=$str_KEYWORDS?></textarea><br><?=GetMessage("AD_KEYWORDS_ALT")?><?
					else:
						if (!empty($arrKEYWORDS))
							echo implode("<br>", $arrKEYWORDS);
						else
							echo GetMessage("ADV_NOT_SET");
					endif;
					?></td>
			</tr>
		<? endif ?>

		<?
		if (CModule::IncludeModule("statistic")):
			$arDisplay = array();
			if ($str_STAT_TYPE === "CITY")
			{
				if (is_array($arrSTAT_TYPE_VALUES) && (count($arrSTAT_TYPE_VALUES) > 0))
				{
					$arFilter = array();
					foreach ($arrSTAT_TYPE_VALUES as $ar)
						$arFilter[] = $ar["CITY_ID"];
					$rs = CCity::GetList("CITY", array("=CITY_ID" => $arFilter));
					while ($ar = $rs->GetNext())
						$arDisplay[$ar["CITY_ID"]] = "[".$ar["COUNTRY_ID"]."] [".$ar["REGION_NAME"]."] ".$ar["CITY_NAME"];
				}
			}
			elseif ($str_STAT_TYPE === "REGION")
			{
				if (is_array($arrSTAT_TYPE_VALUES))
				{
					foreach ($arrSTAT_TYPE_VALUES as $ar)
						$arDisplay[$ar["COUNTRY_ID"]."|".$ar["REGION"]] = "[".$ar["COUNTRY_ID"]."] ".$ar["REGION"];
				}
			}
			else
			{
				if (is_array($arrSTAT_TYPE_VALUES) && (count($arrSTAT_TYPE_VALUES) > 0))
				{
					$arr = array_flip($arrSTAT_TYPE_VALUES);
					$rs = CStatCountry::GetList("s_id");
					while ($ar = $rs->GetNext())
						if (array_key_exists($ar["REFERENCE_ID"], $arr))
							$arDisplay[$ar["REFERENCE_ID"]] = $ar["REFERENCE"];
				}
			}
			?>
			<tr valign="top">
				<td><?=GetMessage("ADV_STAT_WHAT_QUESTION")?>:</td>
				<td>
					<label><input type="radio" name="STAT_TYPE" value="COUNTRY" OnClick="stat_type_changed(this);" <?=$str_STAT_TYPE!=="CITY" && $str_STAT_TYPE!=="REGION"? "checked" : ""?><? if (!$isEditMode) echo ' disabled'?>><?=GetMessage("ADV_STAT_WHAT_COUNTRY")?></label><br>
					<label><input type="radio" name="STAT_TYPE" value="REGION" OnClick="stat_type_changed(this);" <?=$str_STAT_TYPE==="REGION"? "checked" : ""?><? if (!$isEditMode) echo ' disabled'?>><?=GetMessage("ADV_STAT_WHAT_REGION")?></label><br>
					<label><input type="radio" name="STAT_TYPE" value="CITY" OnClick="stat_type_changed(this);" <?=$str_STAT_TYPE==="CITY"? "checked" : ""?><? if (!$isEditMode) echo ' disabled'?>><?=GetMessage("ADV_STAT_WHAT_CITY")?></label><br>
					<select style="width:100%" size="10" id="STAT_TYPE_VALUES[]" name="STAT_TYPE_VALUES[]" multiple OnChange="stat_type_values_change()"<? if (!$isEditMode) echo ' disabled'?>>
						<?foreach ($arDisplay as $key => $value): ?>
							<option value="<?=$key?>"><?=$value?></option>
						<?endforeach;?>
					</select>
					<? if ($isEditMode): ?>
						<script>
							var V_STAT_TYPE = <?=CUtil::PHPToJsObject($str_STAT_TYPE);?>;
							var V_STAT_TYPE_VALUES = <?=CUtil::PHPToJsObject(array(
								"COUNTRY" => array(),
								"REGION" => array(),
								"CITY" => array()
							))?>;

							function stat_type_values_change()
							{
								var oSelect = document.getElementById('STAT_TYPE_VALUES[]');
								if (oSelect)
								{
									var v = '';
									var n = oSelect.length;
									for (var i=0; i<n; i++)
									{
										if (v.length)
											v += ','+oSelect[i].value;
										else
											v = oSelect[i].value;
									}

									document.getElementById('ALL_STAT_TYPE_VALUES').value = v;
								}
							}

							function stat_type_changed(target)
							{
								var oSelect = document.getElementById('STAT_TYPE_VALUES[]');
								if (oSelect)
								{
									//Save
									V_STAT_TYPE_VALUES[V_STAT_TYPE] = [];
									var n = oSelect.length;

									for (var i=0; i<n; i++)
										V_STAT_TYPE_VALUES[V_STAT_TYPE][oSelect[i].value] = oSelect[i].text;
									//Clear
									jsSelectUtils.selectAllOptions('STAT_TYPE_VALUES[]');
									jsSelectUtils.deleteSelectedOptions('STAT_TYPE_VALUES[]');
									//Restore
									for (var val in V_STAT_TYPE_VALUES[target.value])
										jsSelectUtils.addNewOption('STAT_TYPE_VALUES[]', val, V_STAT_TYPE_VALUES[target.value][val]);

									V_STAT_TYPE = target.value;
									stat_type_values_change();
								}
							}

							function stat_type_popup()
							{
								if (V_STAT_TYPE == 'CITY')
									jsUtils.OpenWindow('/bitrix/admin/stat_city_multiselect.php?lang=<?=LANGUAGE_ID?>&form=bx_adv_edit_form&field=STAT_TYPE_VALUES[]', 600, 600);
								else if (V_STAT_TYPE == 'REGION')
									jsUtils.OpenWindow('/bitrix/admin/stat_region_multiselect.php?lang=<?=LANGUAGE_ID?>&form=bx_adv_edit_form&field=STAT_TYPE_VALUES[]', 600, 600);
								else
									jsUtils.OpenWindow('/bitrix/admin/stat_country_multiselect.php?lang=<?=LANGUAGE_ID?>&form=bx_adv_edit_form&field=STAT_TYPE_VALUES[]', 600, 600);
							}
						</script>
						<input type="hidden" id="ALL_STAT_TYPE_VALUES" name="ALL_STAT_TYPE_VALUES" value="<?=implode(",", array_keys($arDisplay))?>">
						<input type="button" value="<?=GetMessage("ADV_STAT_WHAT_ADD")?>" OnClick="stat_type_popup();">&nbsp;&nbsp;<input type="button" value="<?=GetMessage("ADV_STAT_WHAT_DELETE")?>" OnClick="jsSelectUtils.deleteSelectedOptions('STAT_TYPE_VALUES[]');stat_type_values_change();">
					<? endif ?>
				</td>
			</tr>
			<?
			if ($isAdmin || ($isDemo && !$isOwner)):
				$ref = array();
				$ref_id = array();
				$rsAdv = CAdv::GetDropDownList("ORDER BY REFERER1, REFERER2");
				while ($arAdv = $rsAdv->Fetch())
				{
					$ref[] = $arAdv["REFERENCE"];
					$ref_id[] = $arAdv["REFERENCE_ID"];
				}
				if ($isEditMode):
					?>
					<tr valign="top">
						<td><?=GetMessage("AD_STAT_ADV")?><br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""><br><?=GetMessage("AD_SELECT_WHAT_YOU_NEED")?></td>
						<td><?=SelectBoxMFromArray("arrSTAT_ADV[]", array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id), $arrSTAT_ADV, "", true, 10);?></td>
					</tr>
				<? else: ?>
					<tr valign="top">
						<td><?=GetMessage("AD_STAT_ADV")?></td>
						<td><?
							if (is_array($arrSTAT_ADV) && count($arrSTAT_ADV) > 0)
							{
								foreach ($arrSTAT_ADV as $aid)
								{
									$key = array_search($aid, $ref_id);
									echo htmlspecialcharsbx($ref[$key])."<br>";
								}
							}
							else
								echo GetMessage("ADV_NOT_SET");
							?></td>
					</tr>
				<? endif ?>
			<? endif ?>

			<tr>
				<td><?=GetMessage("AD_VISITORS_TYPE")?></td>
				<td><?
					if ($isEditMode) :
						$arr = array(
							"reference" => array(
								GetMessage("AD_NEW_VISITORS_ONLY"),
								GetMessage("AD_RETURNED_VISITORS_ONLY")
							),
							"reference_id" => array(
								"Y",
								"N")
						);
						echo SelectBoxFromArray("FOR_NEW_GUEST", $arr, $str_FOR_NEW_GUEST, GetMessage("AD_ALL_VISITORS"));
					else:
						if ($str_FOR_NEW_GUEST=="Y")
							echo GetMessage("AD_NEW_VISITORS_ONLY");
						elseif ($str_FOR_NEW_GUEST=="Y")
							echo GetMessage("AD_RETURNED_VISITORS_ONLY");
						else
							echo GetMessage("AD_ALL_VISITORS");
					endif;
					?></td>
			</tr>

		<? endif ?>


		<tr valign="top">
			<td><?=GetMessage("AD_WEEKDAY");?></td>
			<td>
				<script>
				<!--
				function OnSelectAll(all_checked, name, vert)
				{
					if (vert)
					{
						for (i=0;i<=23;i++)
						{
							name1 = "arr"+name+"_"+i+"[]";
							if (document.getElementById(name1).disabled == false)
								document.getElementById(name1).checked = all_checked;
						}
					}
					else
					{
						ar = Array("MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY", "SUNDAY");
						for (i=0;i<7;i++)
						{
							name2 = ar[i];
							name1 = "arr"+name2+"_"+name+"[]";
							if (document.getElementById(name1).disabled == false)
								document.getElementById(name1).checked = all_checked;
						}

					}
				}
				//-->
				</script>
				<table cellspacing="6" cellpadding="0" border="0">
					<tr>
						<td>&nbsp;</td>
						<?
						$disabled = (!$isEditMode) ? "disabled" : "";
						$arrWDAY = array(
							"MONDAY"	=> GetMessage("AD_MONDAY"),
							"TUESDAY"	=> GetMessage("AD_TUESDAY"),
							"WEDNESDAY"	=> GetMessage("AD_WEDNESDAY"),
							"THURSDAY"	=> GetMessage("AD_THURSDAY"),
							"FRIDAY"	=> GetMessage("AD_FRIDAY"),
							"SATURDAY"	=> GetMessage("AD_SATURDAY"),
							"SUNDAY"	=> GetMessage("AD_SUNDAY")
						);
						foreach ($arrWDAY as $key => $value) :
							?>
							<td><label for="<?=$key?>"><?=$value?></label><br><input <?=$disabled?> type="checkbox" onclick="OnSelectAll(this.checked, '<?=$key?>', true)" id="<?=$key?>"></td>
							<?
						endforeach;
						?>
						<td>&nbsp;</td>
					</tr>
					<?
					$arrCONTRACT_WEEKDAY = CAdvContract::GetWeekdayArray($arContract["ID"]);
					for ($i=0;$i<=23;$i++):
						?>
						<tr>
							<td><label for="<?=$i?>"><?=$i."&nbsp;-&nbsp;".($i+1)?></label></td>
							<?
							foreach ($arrWDAY as $key => $value):
								$checked = "";
								$disabled = "";
								$disabled = (!is_array($arrCONTRACT_WEEKDAY[$key]) || !in_array($i, $arrCONTRACT_WEEKDAY[$key]) || !$isEditMode) ? "disabled" : "";

								if ($ID<=0 && $disabled!="disabled" && $strError == '') $checked = "checked";
								if (is_array(${"arr".$key}) && in_array($i,${"arr".$key}) && $disable!="disabled") $checked = "checked";
								?>
								<td><input <?=$disabled?> id="arr<?=$key?>_<?=$i?>[]" name="arr<?=$key?>[]" type="checkbox" value="<?=$i?>" <?=$checked?>></td>
								<?
							endforeach;
							$disabled = (!$isEditMode) ? "disabled" : "";
							?>
							<td><input <?=$disabled?> type="checkbox" onclick="OnSelectAll(this.checked, '<?=$i?>', false)" id="<?=$i?>"></td>
						</tr>
						<?
					endfor;
					?>
					<script>
						var ar = ["MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY", "SUNDAY"];
						for (var j = 0; j < 7; j++)
						{
							var valu = true;
							name = ar[j];

							for (var i = 0; i <= 23; i++)
							{
								var name1 = "arr" + name + "_" + i + "[]";
								if (document.getElementById(name1).checked == false)
								{
									valu = false;
									break;
								}
							}

							document.getElementById(name).checked = valu;
						}

						for (j = 0; j <= 23; j++)
						{
							valu = true;

							for ( i = 0; i < 7; i++)
							{
								name = ar[i];
								name1 = "arr" + name + "_" + j + "[]";

								if (document.getElementById(name1).checked == false)
								{
									valu = false;
									break;
								}
							}

							document.getElementById(j).checked = valu;
						}
					</script>
				</table></td>
		</tr>

		<?
		$tabControl->BeginNextTab();
		?>
		<tr>
			<td colspan="2" <? if ($isEditMode): ?>align="center"<? endif ?>><?
				if ($isEditMode):
					?>
					<textarea style="width:85%" name="COMMENTS" rows="7" wrap="VIRTUAL"><?=$str_COMMENTS?></textarea>
					<?
				else:
					echo TxtToHtml($str_COMMENTS);
				endif;
				?></td>
		</tr>
		<?
		$disable = true;
		if ($isManager || $isAdmin || ($isDemo && !$isOwner) || $isEditMode)
			$disable = false;

		$tabControl->Buttons(array("disabled" => $disable, "back_url"=>"/bitrix/admin/adv_banner_list.php?lang=".LANGUAGE_ID));
		$tabControl->End();
		?>
	</form>
	<script>
		<? if ($str_COMMENTS == '' && !$isEditMode): ?>
			tabControl.DisableTab("edit5");
		<? endif; ?>
		changeType('<?=$str_AD_TYPE?>', {params: <?=CUtil::PHPToJsObject($arPropsTemplate)?>, val: <?=CUtil::PHPToJsObject($arCurVal)?>});
	</script>
<?
if ($isEditMode && (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1)) :
	?>
	<?=BeginNote();?>
	<span class="required"><sup>1</sup></span>&nbsp;<?=GetMessage("AD_CONFIRMED_FIELDS")?><br><br>
	<span class="required"><sup>2</sup></span>&nbsp;<?=GetMessage("AD_NOTE_2")?><br><br>
	<span class="required"><sup>3</sup></span>&nbsp;<?=GetMessage("AD_JQUERY_WARNING")?>
	<?=EndNote();?>
<? endif ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>