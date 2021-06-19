<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/csv_user_import.php");

if(!$USER->CanDoOperation('edit_php'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//Download sample
$filename = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/sample.csv";
if(isset($_REQUEST["getSample"]) && $_REQUEST["getSample"] == "csv" && is_file($filename))
{
	$file = @fopen($filename, "rb");
	$contents = @fread($file, filesize($filename));
	fclose($file);

	header("Content-Type: application/octet-stream");
	header("Content-Length: ".mb_strlen($contents));
	header("Content-Disposition: attachment; filename=\"sample.csv\"");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	echo $contents;
	die();
}


set_time_limit(0);
define("HELP_FILE", "users/user_import.php");
IncludeModuleLangFile(__FILE__);

$ldapExists = CModule::IncludeModule("ldap");
$importFrom1C = IsModuleInstalled("iblock") && IsModuleInstalled("intranet");

$arDelimeters = array(
	"semicolon" => ";",
	"comma" => ",",
	"tabulation" => "\t",
	"space" => " ",
);

define("USER_IMPORT_EXECUTION_TIME", 20);

//Params
$strError = false;
$dataSource = (isset($_REQUEST["dataSource"]) && in_array($_REQUEST["dataSource"], array("ldap", "1c")) ? $_REQUEST["dataSource"] : "csv");
$csvDataFile = (isset($_REQUEST["csvDataFile"]) ? $_REQUEST["csvDataFile"] : "");
$delimeter = (isset($_REQUEST["delimeter"]) && array_key_exists($_REQUEST["delimeter"], $arDelimeters) ? $_REQUEST["delimeter"] : "semicolon");
$sendEmail = (isset($_REQUEST["sendEmail"]) && $_REQUEST["sendEmail"] == "Y" ? "Y" : "N");
$eventLangID = (isset($_REQUEST["eventLangID"]) ? $_REQUEST["eventLangID"] : "");
$eventLdapLangID = (isset($_REQUEST["eventLdapLangID"]) ? $_REQUEST["eventLdapLangID"] : "");
$userGroups = (isset($_REQUEST["userGroups"]) && is_array($_REQUEST["userGroups"]) ? $_REQUEST["userGroups"] : array());
$ignoreDuplicate = (isset($_REQUEST["ignoreDuplicate"]) && $_REQUEST["ignoreDuplicate"] == "Y" ? "Y" : "N");
$pathToImages = (isset($_REQUEST["pathToImages"]) ? $_REQUEST["pathToImages"] : "");
$ldapServer = (isset($_REQUEST["ldapServer"]) ? intval($_REQUEST["ldapServer"]) : 0);
$attachIBlockID = (isset($_REQUEST["attachIBlockID"]) && intval($_REQUEST["attachIBlockID"]) > 0 ? intval($_REQUEST["attachIBlockID"]) : 0);

$create1cUser = (isset($_REQUEST["create1cUser"]) && $_REQUEST["create1cUser"] == "Y" ? "Y" : "N");
$newUserLogin = (isset($_REQUEST["newUserLogin"]) && $_REQUEST["newUserLogin"] <> '' ? $_REQUEST["newUserLogin"] : "");
$newUserPass = (isset($_REQUEST["newUserPass"]) && $_REQUEST["newUserPass"] <> '' ? $_REQUEST["newUserPass"] : "");
$newUserConfirmPass = (isset($_REQUEST["newUserConfirmPass"]) && $_REQUEST["newUserConfirmPass"] <> '' ? $_REQUEST["newUserConfirmPass"] : "");
$newUserEmail = (isset($_REQUEST["newUserEmail"]) && $_REQUEST["newUserEmail"] <> '' ? $_REQUEST["newUserEmail"] : "");
$newUserGroups = isset($_REQUEST['newUserGroups']) && is_array($_REQUEST['newUserGroups']) ? $_REQUEST['newUserGroups'] : array();

//Step
$tabStep = (isset($_REQUEST["tabStep"]) && intval($_REQUEST["tabStep"]) > 1 ? intval($_REQUEST["tabStep"]) : 1);
if (isset($_REQUEST["backButton"]))
	$tabStep = $tabStep - 2;
else if (isset($_REQUEST["backToStart"]))
	$tabStep = 1;

if (!$ldapExists && $dataSource == "ldap")
	$tabStep = 1;

if (!$importFrom1C && $dataSource == "1c")
	$tabStep = 1;

//Functions
$cntUsersImport = 0;
$defaultUserEmail = "";
function _OnUserAdd(&$arFields, &$userID)
{
	$GLOBALS["cntUsersImport"]++;

	unset($arFields["GROUP_ID"]);
	$arFields["ID"] = $arFields["USER_ID"] = $userID;
	$arFields["URL_LOGIN"] = urlencode($arFields["LOGIN"]);

	if (isset($arFields["EXTERNAL_AUTH_ID"]) && $arFields["EXTERNAL_AUTH_ID"] <> '' && $GLOBALS["eventLdapLangID"] <> '')
	{
		$arFields["BACK_URL"] = "/";
		$event = new CEvent;
		$event->Send("LDAP_USER_CONFIRM", $GLOBALS["eventLdapLangID"], $arFields);
	}
	elseif ($GLOBALS["sendEmail"] == "Y" && $arFields["EMAIL"] <> '' && $arFields["EMAIL"] <> $GLOBALS["defaultUserEmail"] && $GLOBALS["eventLangID"] <> '')
	{
		$event = new CEvent;
		$event->Send("USER_INVITE", $GLOBALS["eventLangID"], $arFields);
	}
}

//Check and save data
if ($_SERVER["REQUEST_METHOD"] == "POST" && $tabStep > 2 && check_bitrix_sessid())
{
	//Check
	$csvImport = false;
	$ldp = false;

	if ($dataSource == "csv")
	{
		$csvFilePath = $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $csvDataFile);
		if (is_file($csvFilePath) && is_readable($csvFilePath))
		{
			$csvImport = new CSVUserImport($csvFilePath, $arDelimeters[$delimeter]);
			$csvImport->SetUserGroups($userGroups);
			$csvImport->IgnoreDuplicate($ignoreDuplicate == "Y");
			$csvImport->SetCallback("_OnUserAdd");
			$csvImport->SetImageFilePath($pathToImages);
			$csvImport->AttachUsersToIBlock($attachIBlockID);

			$defaultUserEmail = $csvImport->GetDefaultEmail();

			if ($ldapServer > 0 && $ldapExists)
			{
				$dbLdap = CLdapServer::GetByID($ldapServer);
				if ($dbLdap->Fetch())
					$csvImport->externalAuthID = "LDAP#".$ldapServer;
			}

			if ($csvImport->IsErrorOccured())
				$strError = $csvImport->GetErrorMessage();
		}
		else
			$strError = GetMessage("USER_IMPORT_CSV_NOT_FOUND");
	}
	elseif ($dataSource == "ldap")
	{
		$dbLdap = CLdapServer::GetByID($ldapServer);
		if ($arLdap = $dbLdap->Fetch())
		{
			// this is a test connection, thus any parameters other than related to establishing a connection, have no effect here
			$ldap = new CLDAP();
			$ldap->arFields = array(
				"SERVER" => $arLdap['SERVER'],
				"PORT" => $arLdap['PORT'],
				"ADMIN_LOGIN" => $arLdap['ADMIN_LOGIN'],
				"ADMIN_PASSWORD" => $arLdap['ADMIN_PASSWORD'],
				"BASE_DN" => $arLdap['BASE_DN'],
				"GROUP_FILTER" => $arLdap['GROUP_FILTER'],
				"GROUP_ID_ATTR" => $arLdap['GROUP_ID_ATTR'],
				"USER_GROUP_ACCESSORY" => $arLdap['USER_GROUP_ACCESSORY'],
				"USER_FILTER" => $arLdap['USER_FILTER'],
				"GROUP_NAME_ATTR" => $arLdap['GROUP_NAME_ATTR'],
				"CONVERT_UTF8" => $arLdap['CONVERT_UTF8'],
				"MAX_PAGE_SIZE" => $arLdap['MAX_PAGE_SIZE']
			);

			if(isset($arLdap['CONNECTION_TYPE']))
				$ldap->arFields["CONNECTION_TYPE"] = $arLdap['CONNECTION_TYPE'];
				
			if($ldap->Connect())
			{
				$ldp = $ldap;

				if(!$ldp->BindAdmin())
				{
					$strError = GetMessage("USER_IMPORT_LDAP_SERVER_AUTH_ERROR");
				}
				$ldp->Disconnect();
			}
			else
			{
				$strError = GetMessage("USER_IMPORT_LDAP_SERVER_CONN_ERROR");
			}
		}
		else
			$strError = GetMessage("USER_IMPORT_LDAP_SERVER_NOT_FOUND");

	}
	elseif ($dataSource == "1c" && $create1cUser == "Y")
	{
		$user = new CUser;
		$arFields = array(
			"EMAIL" => $newUserEmail,
			"LOGIN" => $newUserLogin,
			"ACTIVE" => "Y",
			"PASSWORD" => $newUserPass,
			"CONFIRM_PASSWORD" => $newUserConfirmPass,
		);

		$arGroups = array();

		if (!empty($newUserGroups))
		{
			$arGroups = array_column(
				\Bitrix\Main\GroupTable::getList(array(
					'select' => array('ID'),
					'filter' => array('@ID' => $newUserGroups),
				))->fetchAll(),
				'ID'
			);
		}

		if (empty($arGroups))
		{
			$strError = getMessage('USER_IMPORT_1C_USER_GROUP_EMPTY');
		}
		else
		{
			$userID = $user->add($arFields);
			if (intval($userID) > 1)
			{
				\CUser::setUserGroup($userID, $arGroups);
			}
			else
			{
				$strError = $user->LAST_ERROR;
			}
		}
	}

	if ($strError !== false)
		$tabStep = 2;

	//Ajax (Main form action)
	if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "import" && $strError === false)
	{
		if ($csvImport)
		{
			$csvFile =& $csvImport->GetCsvObject();

			$position = (isset($_REQUEST["position"]) && intval($_REQUEST["position"]) > 0 ? intval($_REQUEST["position"]) : false);
			if ($position !== false)
				$csvFile->SetPos($position);

			while ($csvImport->ImportUser())
			{
				if(($mess = $csvImport->GetErrorMessage()) <> '')
					echo "<script type=\"text/javascript\">parent.window.ShowError('".CUtil::JSEscape(_ShowHtmlspec($mess))."');</script>";

				if (USER_IMPORT_EXECUTION_TIME > 0 && (getmicrotime()-START_EXEC_TIME) > USER_IMPORT_EXECUTION_TIME)
					die("<script type=\"text/javascript\">parent.window.Start('".$csvFile->GetPos()."',".$cntUsersImport.");</script>");
			}

			die("<script type=\"text/javascript\">parent.window.End(".$cntUsersImport.");</script>");
		}
		elseif ($ldp)
		{
			//Ldap Ajax
			function OnLdapBeforeSyncMainImport($arParams)
			{
				if(empty($arParams['oLdapServer']))
					return;

				if(!($arParams['oLdapServer'] instanceof CLDAP))
					return;

				$arParams['oLdapServer']->arFields["SYNC_USER_ADD"] = 'Y';

				if(is_array($_REQUEST['LDAPMAP']))
				{
					$arParams['oLdapServer']->arFields["FIELD_MAP"] = array_merge(
						$arParams['oLdapServer']->arFields["FIELD_MAP"],
						$_REQUEST['LDAPMAP']
					);
				}
			}
			
			AddEventHandler("ldap", "OnLdapBeforeSync", "OnLdapBeforeSyncMainImport");
			$cntUsersImport = \CLdapServer::Sync($ldapServer);

			//avoid module dependencies
			if(property_exists('CLdapServer', 'syncErrors'))
				$strUserImportError = implode("\n", CLdapServer::$syncErrors);
			else
				$strUserImportError = "";

			if (!empty($strUserImportError))
			{
				echo "<script type=\"text/javascript\">parent.window.ShowError('".CUtil::JSEscape(_ShowHtmlspec($strUserImportError))."');</script>";
			}

			die("<script type=\"text/javascript\">parent.window.End($cntUsersImport);</script>");
		}
	}
}

$APPLICATION->SetTitle(GetMessage("USER_IMPORT_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

CAdminMessage::ShowMessage($strError);

$arTabs = array(
	array("DIV" => "tabSource", "TAB" => GetMessage("USER_IMPORT_SOURCE_TAB"), "TITLE"=>GetMessage("USER_IMPORT_SOURCE_TAB_DESC")),
	array("DIV" => "tabSettings", "TAB" => GetMessage("USER_IMPORT_SETTINGS_TAB"), "TITLE"=>GetMessage("USER_IMPORT_SETTINGS_TAB_DESC")),
	array("DIV" => "tabResults", "TAB" => GetMessage("USER_IMPORT_RESULT_TAB"), "TITLE"=>GetMessage("USER_IMPORT_RESULT_TAB_DESC")),
);
$tabControl = new CAdminTabControl("tabControl", $arTabs, false, true);
?>

<form method="post" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>" name="import_user_form">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
if ($tabStep == 1):
?>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top" width="30%"><?=GetMessage("USER_IMPORT_FROM")?>:</td>
		<td>
			<input type="radio" name="dataSource" id="source-csv" value="csv"<?if($dataSource == "csv"):?> checked<?endif?>/><label for="source-csv"> <?=GetMessage("USER_IMPORT_FROM_CSV")?></label><br />
			<input type="radio" name="dataSource" id="source-ldap" value="ldap"<?if($dataSource == "ldap"):?> checked<?endif?><?if(!$ldapExists):?> disabled="disabled"<?endif?>/><label for="source-ldap" <?if(!$ldapExists):?> disabled="true"<?endif?>> <?=GetMessage("USER_IMPORT_FROM_LDAP")?></label><?if(!$ldapExists):?> (<?=GetMessage("LDAP_MODULE_NOT_INSTALLED")?>)<?endif?><br />
			<input type="radio" name="dataSource" id="source-1c" value="1c"<?if($dataSource == "1c"):?> checked<?endif?><?if(!$importFrom1C):?> disabled="disabled"<?endif?>/><label for="source-1c"<?if(!$importFrom1C):?> disabled="true"<?endif?>> <?=GetMessage("USER_IMPORT_FROM_1C")?></label><?if(!$importFrom1C):?> (<?=GetMessage("IMPORT_FROM_1C_REQ_NOTES")?>)<?endif?>
		</td>
	</tr>
<?
endif;
$tabControl->EndTab();
$tabControl->BeginNextTab();
if ($tabStep == 2 && $dataSource == "csv"):
?>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("USER_IMPORT_DATA_FILE")?>:</td>
		<td>
			<input type="text" name="csvDataFile" size="30" value="<?=htmlspecialcharsbx($csvDataFile);?>">
			<input type="button" value="<?=GetMessage("USER_IMPORT_OPEN_DIALOG")?>" onclick="SelectCSVFile()">
			<?
			CAdminFileDialog::ShowScript
			(
				array(
					"event" => "SelectCSVFile",
					"arResultDest" => array("FORM_NAME" => "import_user_form", "FORM_ELEMENT_NAME" => "csvDataFile"),
					"arPath" => array("SITE" => SITE_ID, "PATH" =>"/upload"),
					"select" => 'F',// F - file only, D - folder only
					"operation" => 'O',// O - open, S - save
					"showUploadTab" => true,
					"showAddToMenuTab" => false,
					"fileFilter" => 'csv',
					"allowAllFiles" => true,
					"SaveConfig" => true,
				)
			)
			?>
			<?if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/sample.csv")):?>
				<a href="?getSample=csv"><?=GetMessage("USER_IMPORT_CSV_SAMPLE")?></a>
			<?endif?>
		</td>
	</tr>
<?
if(defined("BX_UTF")):
?>
	<tr>
		<td></td>
		<td><?echo BeginNote()?><?echo GetMessage("USER_IMPORT_UTF")?><?echo EndNote()?></td>
	</tr>
<?
endif;
?>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?=GetMessage("USER_IMPORT_DELIMETER")?>:</td>
		<td>
			<input type="radio" name="delimeter" id="delimeter-semicolon" value="semicolon"<?if($delimeter == "semicolon"):?> checked<?endif?>/><label for="delimeter-semicolon"><?=GetMessage("USER_IMPORT_DELIMETER_SEMICOLON")?></label><br />

			<input type="radio" name="delimeter" id="delimeter-comma" value="comma"<?if($delimeter == "comma"):?> checked<?endif?>/><label for="delimeter-comma"><?=GetMessage("USER_IMPORT_DELIMETER_COMMA")?></label><br />

			<input type="radio" name="delimeter" id="delimeter-tabulation" value="tabulation"<?if($delimeter == "tabulation"):?> checked<?endif?>/><label for="delimeter-tabulation"><?=GetMessage("USER_IMPORT_DELIMETER_TABULATION")?></label><br />

			<input type="radio" name="delimeter" id="delimeter-space" value="space"<?if($delimeter == "space"):?> checked<?endif?>/><label for="delimeter-space"><?=GetMessage("USER_IMPORT_DELIMETER_SPACE")?></label><br />
		</td>
	</tr>

	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("USER_IMPORT_ATTTACH_GROUP")?>:</td>
		<td>
			<select name="userGroups[]" style="width:300px" size="7" multiple="multiple">
			<?
			$dbGroup = CGroup::GetList("name", "asc");
			while ($arGroup = $dbGroup->GetNext()):?>
				<option value="<?=$arGroup["ID"]?>"<?if (in_array($arGroup["ID"], $userGroups)):?> selected<?endif?>><?=$arGroup["NAME"]?></option>
			<?endwhile?>
			</select>
		</td>
	</tr>

	<tr>
		<td><?=GetMessage("USER_IMPORT_IMAGE_PATH")?>:</td>
		<td><input type="text" name="pathToImages" size="30" value="<?=htmlspecialcharsbx($pathToImages)?>">
		<input type="button" value="<?=GetMessage("USER_IMPORT_OPEN_DIALOG")?>" onclick="SelectImagePath()">
		<?
		CAdminFileDialog::ShowScript
		(
			array(
				"event" => "SelectImagePath",
				"arResultDest" => array("FORM_NAME" => "import_user_form", "FORM_ELEMENT_NAME" => "pathToImages"),
				"arPath" => array("SITE" => SITE_ID, "PATH" =>"/upload"),
				"select" => 'D',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => false,
				"showAddToMenuTab" => false,
				"allowAllFiles" => false,
				"SaveConfig" => true,
			)
		)
			?>
		</td>
	</tr>
<?
if(CModule::IncludeModule("iblock")):
?>
	<tr>
		<td><?=GetMessage("USER_IMPORT_ATTACH_TO_IBLOCK")?>:</td>
		<td>
			<select name="attachIBlockID" style="width:300px">
				<option value="0"><?=GetMessage("USER_IMPORT_SELECT_IBLOCK")?></option>
			<?
			$dbIBlock = CIBlock::GetList(array("NAME" => "ASC", "ID" => "DESC"), array());
			while ($arIBlock = $dbIBlock->GetNext()):?>
				<option value="<?=$arIBlock["ID"]?>"<?if ($attachIBlockID == $arIBlock["ID"]):?> selected<?endif?>><?=$arIBlock["NAME"]?></option>
			<?endwhile?>
			</select>
		</td>
	</tr>
<?endif?>
	<?if ($ldapExists):?>
	<tr>
		<td><?=GetMessage("USER_IMPORT_ALLOW_LDAP_AUTH")?>:</td>
		<td>
			<select name="ldapServer" onchange="OnLdapServerChange(this)">
				<option value="0"><?=GetMessage("USER_IMPORT_SELECT_FROM_LIST")?></option>
			<?
			$dbLdap = CLdapServer::GetList(array("NAME" => "ASC"), array("ACTIVE" => "Y"));
			while ($arLdap = $dbLdap->GetNext()):?>
				<option value="<?=$arLdap["ID"]?>"<?if ($ldapServer == $arLdap["ID"]):?> selected<?endif?>><?=$arLdap["NAME"]?></option>
			<?endwhile?>
			</select>
			&nbsp;<a href="/bitrix/admin/ldap_server_edit.php?lang=<?=LANGUAGE_ID?>"><?=GetMessage("USER_IMPORT_NEW_LDAP_SERVER")?></a>
		</td>
	</tr>
	<?endif?>

	<?if ($ldapExists):?>
	<tr<?if ($ldapServer < 1):?> style="display:none;"<?endif?> id="eventLdapRow">
		<td class="adm-detail-valign-top"></td>
		<td>
			<input type="checkbox" checked="checked" disabled="disabled" /> <label disabled="true"><?=GetMessage("USER_IMPORT_SEND_MAIL")?></label>
			<br />
			<label for="eventLdapLangID" id="eventLdapLangLabel"><?=GetMessage("USER_IMPORT_EMAIL_TEMPLATE1")?>:</label>
			<select id="eventLdapLangID" name="eventLdapLangID" style="width:300px;" <?if ($ldapServer < 1):?>disabled="disabled"<?endif?>>
			<?
			$dbSites = CSite::GetList("name", "asc");
			while ($arSite = $dbSites->Fetch()):
			?>
					<option value="<?=htmlspecialcharsbx($arSite["LID"])?>" <?if ($eventLdapLangID == $arSite["LID"]):?> selected<?endif?>><?=htmlspecialcharsbx($arSite["NAME"])?> (<?=htmlspecialcharsbx($arSite["LID"])?>)</option>
			<?
			endwhile;
			?>
			</select><br />
		</td>
	</tr>
	<?endif?>

	<tr<?if ($ldapServer > 1):?> style="display:none;"<?endif?> id="eventRow">
		<td class="adm-detail-valign-top"></td>
		<td>
			<input type="checkbox" name="sendEmail" id="send-email" onclick="OnSendEmail(this.checked)" value="Y"<?if($sendEmail == "Y"):?> checked<?endif?>/> <label id="sendEmailLabel" for="send-email"><?=GetMessage("USER_IMPORT_SEND_MAIL")?></label>
			<br />
			<label for="event-lang" id="eventLangLabel"><?=GetMessage("USER_IMPORT_EMAIL_TEMPLATE1")?>:</label>
			<select id="event-lang" name="eventLangID" style="width:300px;">
			<?
			$dbSites = CSite::GetList("name", "asc");
			while ($arSite = $dbSites->Fetch()):
			?>
					<option value="<?=htmlspecialcharsbx($arSite["LID"])?>" <?if ($eventLangID == $arSite["LID"]):?> selected<?endif?>><?=htmlspecialcharsbx($arSite["NAME"])?> (<?=htmlspecialcharsbx($arSite["LID"])?>)</option>
			<?
			endwhile;
			?>
			</select><br />
		</td>
	</tr>

	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ignoreDuplicate" id="ignore-duplicate" value="Y"<?if($ignoreDuplicate == "Y"):?> checked<?endif?>/> <label for="ignore-duplicate"><?=GetMessage("USER_IMPORT_IGNORE_DUPLICATE")?></label>
		</td>
	</tr>

<?elseif ($tabStep == 2 && $dataSource == "ldap"):?>

	<tr class="adm-detail-required-field">
		<td width="50%"><?=GetMessage("USER_IMPORT_LDAP_SERVER")?>:</td>
		<td width="50%">
			<select name="ldapServer" onChange="OnLdapSelect(this.selectedIndex - 1);">
				<option value="0"><?=GetMessage("USER_IMPORT_SELECT_FROM_LIST")?></option>
			<?
			$arAllFields = CLdapUtil::GetSynFields(); // all user fields that are currently set up in the system

			$arFieldMaps = array();
			$indSelected = -1;
			$i=-1;
			$dbLdap = CLdapServer::GetList(array("NAME" => "ASC"), array("ACTIVE" => "Y"));
			while ($arLdap = $dbLdap->GetNext()):
				$i++;
				$map = $arLdap["FIELD_MAP"];
				foreach ($map as $user_f=>$ldap_f)
				{
					if (!array_key_exists($user_f,$arAllFields))
					{
						unset($map[$user_f]);
					}
				}
				$arFieldMaps[] = $map;
				?>
				<option value="<?=$arLdap["ID"]?>"<?if ($ldapServer == $arLdap["ID"]): $indSelected = $i;?> selected<?endif?>><?=$arLdap["NAME"]?></option>
			<?endwhile?>
			</select>
			<script type="text/javascript">
				<?
				$arMapFields = array();

				foreach($arAllFields as $field=>$arFieldParams)
					if($arFieldParams['AD'])
						$arMapFields[$field] = array('NAME'=>$arFieldParams['NAME'], 'MAP'=>$arFieldParams['AD']);
				?>
				function ChCh(c)
				{
					var eall = document.getElementById("eall");
					if(!c)
					{
						eall.checked = false;
						return;
					}

					var chTbl = document.getElementById("chb");
					var r = chTbl.rows;
					for(var i=1; i<r.length; i++)
					{
						if(!r[i].cells[0].childNodes[0].checked)
						{
							eall.checked = false;
							return;
						}
					}
					eall.checked = true;
				}

				function Ch(c)
				{
					var chTbl = document.getElementById("chb");
					var r = chTbl.rows;
					for(var i=1; i<r.length; i++)
					{
						r[i].cells[0].childNodes[0].checked = c;
					}
				}

				var LdapMaps = <?=CUtil::PhpToJSObject($arFieldMaps)?>;
				var AllMaps = <?=CUtil::PhpToJSObject($arMapFields)?>;
				var AllFields = <?=CUtil::PhpToJSObject($arAllFields)?>;

				function OnLdapSelect(num)
				{
					var fields = LdapMaps[num];
					var field;

					if(!fields)
					{
						document.getElementById('predef').style.display = 'none';
						document.getElementById('def').style.display = 'none';
						return;
					}
					else
					{
						try
						{
							document.getElementById('predef').style.display = 'table-row';
							document.getElementById('def').style.display = 'table-row';
						}
						catch(e)
						{
							document.getElementById('predef').style.display = 'block';
							document.getElementById('def').style.display = 'block';
						}
					}

					var predefinedfields = document.getElementById('predefinedfields');
					predefinedfields.innerHTML = '';
					for(field in fields)
					{
						predefinedfields.innerHTML += '<input type="checkbox" checked disabled>'+AllFields[field]['NAME']+'<br>';
					}

					var definedfields = document.getElementById('definedfields');
					definedfields.innerHTML = '<?=GetMessage("USER_IMPORT_NO")?>';
					var res = '';
					for(field in AllMaps)
					{
						if(!fields[field])
						{
							res += '<tr><td><input type="checkbox" name="LDAPMAP['+field+']" id="'+field+'" value="'+AllMaps[field]['MAP']+'" onclick="ChCh(this.checked)" checked></td><td><label for="'+field+'">'+AllFields[field]['NAME']+'</label></td></tr>';
						}
					}
					if(res.length>0)
						definedfields.innerHTML = '<table id="chb"><tr><td><input type="checkbox" id="eall" onclick="Ch(this.checked)" checked></td><td><label for="eall"><?echo GetMessage("USER_IMPORT_ALL")?></label></td></tr>'+res+'</table>';
				}
				setTimeout('OnLdapSelect(<?=$indSelected?>);', 1);
			</script>
			&nbsp;<a href="/bitrix/admin/ldap_server_edit.php?lang=<?=LANGUAGE_ID?>&back_url=<?=urlencode('/bitrix/admin/user_import.php?lang='.$lang.'&dataSource=ldap&tabStep=2')?>"><?=GetMessage("USER_IMPORT_NEW_LDAP_SERVER")?></a>
		</td>
	</tr>
	<tr id="predef">
		<td class="adm-detail-valign-top"><?echo GetMessage("USER_IMPORT_LDAP_IMP_DEF")?><br><?echo GetMessage("USER_IMPORT_LDAP_IMP_DEF_NOTE")?></td>
		<td>
			<div id="predefinedfields"></div>
		</td>
	</tr>
	<tr id="def">
		<td class="adm-detail-valign-top"><?echo GetMessage("USER_IMPORT_LDAP_IMP_ADD")?></td>
		<td>
			<div id="definedfields"></div>
		</td>
	</tr>
<?elseif ($tabStep == 2 && $dataSource == "1c"):?>
	<tr>
		<td></td>
		<td><input type="checkbox" name="create1cUser" id="create-1c-user" value="Y"<?if($create1cUser == "Y"):?> checked<?endif?> onclick="EnableNewUserFields(this.checked)" /> <label for="create-1c-user"><?=GetMessage("USER_IMPORT_CREATE_1C_USER")?></label>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><label disabled="true" id="newUserLoginLabel"><?=GetMessage("USER_IMPORT_1C_USER_LOGIN")?>:</label></td>
		<td><input name="newUserLogin" size="30" maxlength="50" value="<?=htmlspecialcharsEx($newUserLogin)?>" type="text"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><label id="newUserPassLabel" disabled="true"><?=GetMessage("USER_IMPORT_1C_USER_PASS")?>:</label></td>
		<td><input name="newUserPass" size="30" maxlength="50" value="<?=htmlspecialcharsEx($newUserPass)?>" autocomplete="off" type="password"></td>

	</tr>
	<tr class="adm-detail-required-field">
		<td><label disabled="true" id="newUserConfirmPassLabel"><?=GetMessage("USER_IMPORT_1C_USER_CONFIRM_PASS")?>:</label></td>
		<td><input name="newUserConfirmPass" size="30" maxlength="50" value="<?=htmlspecialcharsEx($newUserConfirmPass)?>" autocomplete="off" type="password"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><label disabled="true" id="newUserEmailLabel"><?=GetMessage("USER_IMPORT_1C_USER_EMAIL")?>:</label></td>
		<td><input name="newUserEmail" size="30" maxlength="50" value="<?=htmlspecialcharsEx($newUserEmail)?>" type="text"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><label disabled="true" id="newUserGroupsLabel"><?=getMessage('USER_IMPORT_1C_USER_GROUP') ?>:</label></td>
		<td>
			<select name="newUserGroups[]" style="width: 300px; " size="7" multiple="multiple">
				<? $groupRes = \CGroup::getList('name', 'asc'); ?>
				<? while ($item = $groupRes->fetch()): ?>
					<option value="<?=intval($item['ID']) ?>"><?=htmlspecialcharsbx($item['NAME']) ?></option>
				<? endwhile ?>
			</select>
		</td>
	</tr>
<?endif;
$tabControl->EndTab();
$tabControl->BeginNextTab();
if ($tabStep == 3):
?>
	<?if ($dataSource == "csv"):?>
		<tr>
			<td colspan="2"><div id="import_errors" style="color:red; margin:8px 0 8px 0;"></div>
			<div id="importFinishedLabel" style="display:none; margin:8px 0 8px 0;"><b><?=GetMessage("USER_IMPORT_FINISHED")?></b></div>
			<?=GetMessage("USER_IMPORT_USERS_COUNT")?>: <span id="cntExecuted">0</span></td>
		</tr>

	<?elseif ($dataSource == "ldap"):?>

		<tr>
			<td colspan="2">
			<div id="import_errors" style="color:red; margin:8px 0 8px 0;"></div>
			<div id="importFinishedLabel" style="display:none; margin:8px 0 8px 0;"><?=GetMessage("USER_IMPORT_FINISHED")?></div>
			<?=GetMessage("USER_IMPORT_USERS_COUNT")?>: <span id="cntExecuted">0</span></td>
		</tr>

	<?elseif ($dataSource == "1c"):?>

		<tr>
			<td colspan="2"><?=GetMessage("USER_IMPORT_1C_HELP")?></td>
		</tr>

	<?endif?>

<?
endif;
$tabControl->EndTab();
$tabControl->Buttons();
?>

<input type="hidden" name="tabStep" value="<?=($tabStep + 1)?>">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
<input type="hidden" name="position" id="action" value="0" />

<?if ($tabStep != 1):?>
	<input type="hidden" name="dataSource" value="<?=$dataSource?>" />
<?endif?>

<?if ($tabStep != 2):?>
	<input type="hidden" name="csvDataFile" value="<?=htmlspecialcharsbx($csvDataFile)?>" />
	<input type="hidden" name="delimeter" value="<?=$delimeter?>" />
	<input type="hidden" name="sendEmail" value="<?=$sendEmail?>" />
	<input type="hidden" name="eventLangID" value="<?=htmlspecialcharsbx($eventLangID)?>" />
	<input type="hidden" name="eventLdapLangID" value="<?=htmlspecialcharsbx($eventLdapLangID)?>" />
	<input type="hidden" name="ignoreDuplicate" value="<?=$ignoreDuplicate?>" />
	<input type="hidden" name="pathToImages" value="<?=htmlspecialcharsbx($pathToImages)?>" />
	<input type="hidden" name="ldapServer" value="<?=$ldapServer?>" />
	<input type="hidden" name="attachIBlockID" value="<?=$attachIBlockID?>" />
	<?foreach ($userGroups as $groupID):?>
	<input type="hidden" name="userGroups[]" value="<?=intval($groupID)?>" />
	<?endforeach?>
<?endif?>

<?if(is_array($_REQUEST["LDAPMAP"]) &&  $tabStep != 2):?>
	<?foreach($_REQUEST["LDAPMAP"] as $map=>$y):?>
		<input type="hidden" name="LDAPMAP[<?=htmlspecialcharsbx($map)?>]" value="<?=htmlspecialcharsbx($y)?>" />
	<?endforeach?>
<?endif?>

<?if ($tabStep == 3):?>
	<input type="hidden" name="action" id="action" value="import" />
<?endif?>

<?=bitrix_sessid_post()?>

<?if ($tabStep == 2):?>
	<input type="submit" name="backButton" value="&lt; <?=GetMessage("USER_IMPORT_PREV_BUTTON")?>">
<?elseif ($tabStep == 3): ?>
	<input type="submit" name="backToStart" value="&lt; <?=GetMessage("USER_IMPORT_BACK_TO_START")?>" <?if ($dataSource == "csv" || $dataSource == "ldap"):?>disabled="disabled"<?endif?>>
<?endif?>

<?if ($tabStep < 3):?>
	<input type="submit" value="<?=GetMessage("USER_IMPORT_NEXT_BUTTON")?> &gt;" name="nextButton" class="adm-btn-save">
<?endif?>

<?$tabControl->End();?>
</form>

<iframe style="display:none;" id="progress" name="progress" src="javascript:''"></iframe>

<script type="text/javascript">
<!--

function Start(position, cntExecuted)
{
	ShowWaitWindow();

	var form = document.forms["import_user_form"];

	if (!form)
		return;

	form.target = "progress";
	form.elements["position"].value = position;

	var spanExecuted = document.getElementById("cntExecuted");
	if (spanExecuted && cntExecuted)
		spanExecuted.innerHTML = parseInt(spanExecuted.innerHTML) + cntExecuted;

	form.submit();
}

function End(cntExecuted)
{
	var spanExecuted = document.getElementById("cntExecuted");
	if (spanExecuted && cntExecuted)
		spanExecuted.innerHTML = parseInt(spanExecuted.innerHTML) + cntExecuted;

	var form = document.forms["import_user_form"];

	if (!form)
		return;

	var label = document.getElementById("importFinishedLabel");
	if (label)
		label.style.display = "block";

	form.target = "_self";
	form.elements["backToStart"].disabled = false;
	form.elements["action"].value = "";

	CloseWaitWindow();
}

function ShowError(msg)
{
	document.getElementById("import_errors").innerHTML += msg;
}

function EnableNewUserFields(enabled)
{
	var form = document.forms["import_user_form"];

	if (!form)
		return;

	form.elements["newUserLogin"].disabled = !enabled;
	form.elements["newUserPass"].disabled = !enabled;
	form.elements["newUserConfirmPass"].disabled = !enabled;
	form.elements["newUserEmail"].disabled = !enabled;
	form.elements["newUserGroups[]"].disabled = !enabled;

	var newUserLoginLabel = document.getElementById("newUserLoginLabel");
	var newUserPassLabel = document.getElementById("newUserPassLabel");
	var newUserConfirmPassLabel = document.getElementById("newUserConfirmPassLabel");
	var newUserGroupsLabel = document.getElementById("newUserGroupsLabel");

	if (enabled)
	{
		newUserLoginLabel.setAttribute("disabled", "false");
		newUserPassLabel.setAttribute("disabled", "false");
		newUserConfirmPassLabel.setAttribute("disabled", "false");
		newUserEmailLabel.setAttribute("disabled", "false");
		newUserGroupsLabel.setAttribute("disabled", "false");
		newUserLoginLabel.disabled = newUserPassLabel.disabled = newUserConfirmPassLabel.disabled = newUserEmailLabel.disabled = newUserGroupsLabel.disabled = false;
	}
	else
	{
		newUserLoginLabel.setAttribute("disabled", "true");
		newUserPassLabel.setAttribute("disabled", "true");
		newUserConfirmPassLabel.setAttribute("disabled", "true");
		newUserEmailLabel.setAttribute("disabled", "true");
		newUserGroupsLabel.setAttribute("disabled", "true");
		newUserLoginLabel.disabled = newUserPassLabel.disabled = newUserConfirmPassLabel.disabled = newUserEmailLabel.disabled = newUserGroupsLabel.disabled = true;
	}
}

function OnSendEmail(enabled)
{
	var form = document.forms["import_user_form"];

	if (!form)
		return;

	form.elements["eventLangID"].disabled = !enabled;
	var eventLangLabel = document.getElementById("eventLangLabel");
	if (enabled)
	{
		eventLangLabel.setAttribute("disabled", "false");
		eventLangLabel.disabled = false;
	}
	else
	{
		eventLangLabel.setAttribute("disabled", "true");
		eventLangLabel.disabled = true;
	}
}

function OnLdapServerChange(select)
{
	var showLdapEvent = (select.value > 0);

	var sendEmailInput = document.getElementById("send-email");
	var sendEmailLabel = document.getElementById("sendEmailLabel");
	var eventLdapRow = document.getElementById("eventLdapRow");
	var eventRow = document.getElementById("eventRow");
	var eventLdapLangID = document.getElementById("eventLdapLangID");

	if (showLdapEvent)
	{
		sendEmailLabel.setAttribute("disabled", "true");
		sendEmailLabel.disabled = true;
		sendEmailInput.disabled = true;
		OnSendEmail(false);

		eventLdapRow.style.display = "";
		eventRow.style.display = "none";
		eventLdapLangID.disabled = false;
	}
	else
	{
		sendEmailLabel.setAttribute("disabled", "false");
		sendEmailLabel.disabled = false;
		sendEmailInput.disabled = false;
		eventLdapLangID.disabled = true;

		eventLdapRow.style.display = "none";
		eventRow.style.display = "";

		OnSendEmail(sendEmailInput.checked);

	}
}

<?if ($tabStep == 1):?>
	tabControl.SelectTab("tabSource");
	tabControl.DisableTab("tabSettings");
	tabControl.DisableTab("tabResults");
<?elseif ($tabStep == 2):?>
	tabControl.SelectTab("tabSettings");
	tabControl.DisableTab("tabSource");
	tabControl.DisableTab("tabResults");

	<?if ($dataSource == "csv"):?>
		OnSendEmail(<?=($sendEmail == "Y" ? "true" : "false")?>);
	<?elseif($dataSource == "1c"):?>
		EnableNewUserFields(<?=($create1cUser == "Y" ? "true" : "false")?>);
	<?endif?>

<?elseif ($tabStep == 3):?>
	tabControl.SelectTab("tabResults");
	tabControl.DisableTab("tabSource");
	tabControl.DisableTab("tabSettings");

	<?if ($dataSource == "csv" || $dataSource == "ldap"):?>
		jsUtils.addEvent(window, "load", function() {window.Start(0,0);});
	<?endif?>
<?endif;?>
//-->
</script>

<?require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
