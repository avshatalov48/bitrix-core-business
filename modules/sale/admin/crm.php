<?
/** @global \CMain $APPLICATION */
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Catalog,
	Bitrix\Iblock;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('sale');
IncludeModuleLangFile(__FILE__);
//$APPLICATION->SetAdditionalCSS("/bitrix/js/intranet/intranet-common.css");

ClearVars();

$crmIntegrationData = COption::GetOptionString("sale", "~crm_integration", "");
$arCrmIntegration = unserialize($crmIntegrationData, ['allowed_classes' => false]);
if (!is_array($arCrmIntegration))
	$arCrmIntegration = array();

//$arCrmIntegration = array();

if ($_SERVER["REQUEST_METHOD"] != "POST" || $do_create_link != "Y")
	$_REQUEST["CRM_BUS_USER_SET_C"] = "Y";

$errorMessage = "";
$successMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && $do_create_link == "Y" && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
	if (!isset($_POST["CRM_URL_SERVER"]) || empty($_POST["CRM_URL_SERVER"]))
	{
		$errorMessage .= GetMessage("SPTEN_SCRM_NO_SITE")."<br />";
	}
	else
	{
		$arCrmUrl = parse_url($_POST["CRM_URL_SERVER"]);
		$crmUrlHost = $arCrmUrl["host"] ? $arCrmUrl["host"] : $arCrmUrl["path"];
		$crmUrlScheme = $arCrmUrl["scheme"]? mb_strtolower($arCrmUrl["scheme"]) : mb_strtolower($_POST["CRM_URL_SCHEME"]);
		$crmUrlPort = $arCrmUrl["port"] ? intval($arCrmUrl["port"]) : intval($_POST["CRM_URL_PORT"]);
		switch ($crmUrlScheme)
		{
			case 'https':
				if (!function_exists("openssl_verify"))
					$errorMessage .= "OpenSSL PHP extention required"."<br />";

				$crmUrlScheme = 'ssl://';
				if ($crmUrlPort <= 0)
					$crmUrlPort = 443;
				break;

			default:
				$crmUrlScheme = '';
				if ($crmUrlPort <= 0)
					$crmUrlPort = 80;
				break;
		}

		if (empty($crmUrlHost))
			$errorMessage .= GetMessage("SPTEN_SCRM_WRONG_SITE")."<br />";
	}

	$crmLogin = $_POST["CRM_LOGIN"];
	$crmPassword = $_POST["CRM_PASSWORD"];

	if ($crmLogin == '')
		$errorMessage .= GetMessage("SPTEN_SCRM_NO_LOGIN")."<br />";
	if ($crmPassword == '')
		$errorMessage .= GetMessage("SPTEN_SCRM_NO_PWD")."<br />";

	$createNewSaleUser = ($_POST["CRM_BUS_USER_SET_C"] == "Y");
	if (!$createNewSaleUser)
	{
		$saleLogin = $_POST["CRM_BUS_USER_LOGIN"];
		$salePassword = $_POST["CRM_BUS_USER_PASSWORD"];
		if ($saleLogin == '')
		{
			$errorMessage .= GetMessage("SPTEN_SCRM_NO_SALE_LOGIN")."<br />";
		}
		else
		{
			$dbSaleLoginUser = CUser::GetByLogin($saleLogin);
			if (!$arSaleLoginUser = $dbSaleLoginUser->Fetch())
				$errorMessage .= GetMessage("SPTEN_SCRM_WRONG_SALE_LOGIN")."<br />";
		}
		if ($salePassword == '')
			$errorMessage .= GetMessage("SPTEN_SCRM_NO_SALE_PWD")."<br />";
	}

	if ($createNewSaleUser)
	{
		$userId = 0;
		$groupId = 0;

		if (empty($errorMessage))
		{
			$saleLogin = "BX_CRM_IMPORT_USER_".randString(5, "ABCDEFGHIJKLNMOPQRSTUVWXYZ");

			$idx = 0;
			$saleLoginTmp = $saleLogin;
			$dbSaleLoginUser = CUser::GetByLogin($saleLogin);
			while ($arSaleLoginUser = $dbSaleLoginUser->Fetch())
			{
				$idx++;
				if ($idx > 10)
				{
					$saleLogin = $saleLogin.time();
					break;
				}
				else
				{
					$saleLogin = $saleLoginTmp.$idx;
				}
				$dbSaleLoginUser = CUser::GetByLogin($saleLogin);
			}
		}

		if (empty($errorMessage))
		{
			$defaultGroup = COption::GetOptionString("main", "new_user_registration_def_group", "");
			if ($defaultGroup != "")
			{
				$arDefaultGroup = explode(",", $defaultGroup);
			}
			else
			{
				$arDefaultGroup = [];
			}

			$salePassword = \CUser::GeneratePasswordByPolicy($arDefaultGroup);

			$saleEMail = $saleLogin.'@'.$_SERVER["SERVER_NAME"];
			if (!check_email($saleEMail))
				$saleEMail = $saleLogin.'@temporary.temp';

			$arUserFields = array(
				"LOGIN" => $saleLogin,
				"NAME" => "CRM",
				"LAST_NAME" => "IMPORT",
				"PASSWORD" => $salePassword,
				"PASSWORD_CONFIRM" => $salePassword,
				"EMAIL" => $saleEMail,
				"GROUP_ID" => $arDefaultGroup,
				"ACTIVE" => "Y",
				"LID" => SITE_ID,
			);
			$user = new CUser;
			$userId = $user->Add($arUserFields);
			$userId = intval($userId);
			if ($userId <= 0)
				$errorMessage .= GetMessage("SPTEN_SCRM_ERR_REG").(($user->LAST_ERROR <> '') ? ": ".$user->LAST_ERROR : "");
		}

		if (empty($errorMessage))
		{
			$group = new CGroup;

			$arGroupFields = array(
				"ACTIVE" => "Y",
				"NAME" => "CRM SALE IMPORT",
				"USER_ID" => array(
					array(
						"USER_ID" => $userId,
						"DATE_ACTIVE_FROM" => false,
						"DATE_ACTIVE_TO" => false,
					)
				)
			);

			$groupId = $group->Add($arGroupFields);
			$groupId = intval($groupId);
			if ($groupId <= 0)
				$errorMessage .= GetMessage("SPTEN_SCRM_ERR_GRP").(($group->LAST_ERROR <> '') ? ": ".$group->LAST_ERROR : "");
		}
	}

	function __CrmSaleQuery($crmUrlScheme, $crmUrlHost, $crmUrlPort, $crmLogin, $crmPassword, $head, $body, &$errorMessage)
	{
		// remove last slash from $crmUrlHost, eg. site.ru/
		if (mb_strpos($crmUrlHost, '/') === (mb_strlen($crmUrlHost) - 1))
		{
			$crmUrlHost = mb_substr($crmUrlHost, 0, -1);
		}

		$hServer = @fsockopen($crmUrlScheme.$crmUrlHost, $crmUrlPort, $errno, $errstr, 20);
		if (!$hServer)
			$errorMessage .= sprintf("[%s] %s", $errno, $errstr)."<br />";

		$arResponseHeaders = array();
		$responseBody = "";

		if (empty($errorMessage))
		{
			$buffer  = "POST /bitrix/components/bitrix/crm.config.external_sale.edit/bus.php HTTP/1.0\r\n";
			$buffer .= sprintf("Host: %s:%s\r\n", $crmUrlHost, $crmUrlPort);
			$buffer .= "Content-type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
			$buffer .= sprintf("Authorization: Basic %s\r\n", base64_encode($crmLogin.":".$crmPassword));
			$buffer .= sprintf("Content-length: %s\r\n", ((function_exists('mb_strlen')? mb_strlen($body, 'latin1') : mb_strlen($body))));
			$buffer .= $head;
			$buffer .= "\r\n";
			$buffer .= $body;

			fputs($hServer, $buffer);

			while ($line = fgets($hServer, 4096))
			{
				if ($line == "\r\n")
					break;

				$arResponseHeaders[] = trim($line);
			}

			if (count($arResponseHeaders) <= 0)
				$errorMessage .= GetMessage("SPTEN_SCRM_ERR_CONNECT")."<br />";
		}

		if (empty($errorMessage))
		{
			$contentLength = null;
			foreach ($arResponseHeaders as $value)
			{
				if (preg_match('#Content-Length:\s*([0-9]*)#i', $value, $arMatches))
				{
					$contentLength = intval($arMatches[1]);
					break;
				}
				if (preg_match('#HTTP/1\.1\s+204#i', $value))
				{
					$contentLength = 0;
					break;
				}
			}

			if ($contentLength === 0)
			{
			}
			elseif ($contentLength > 0)
			{
				$lb = $contentLength;
				while ($lb > 0)
				{
					$responseBody .= fread($hServer, $lb);
					$lb = $contentLength - ((function_exists('mb_strlen')? mb_strlen($responseBody, 'latin1') : mb_strlen($responseBody)));
				}
			}
			else
			{
				stream_set_timeout($hServer, 0);

				while (!feof($hServer))
				{
					$responseBody .= fread($hServer, 4096);
					if (mb_substr($responseBody, -9) == "\r\n\r\n0\r\n\r\n")
					{
						$responseBody = mb_substr($responseBody, 0, -9);
						break;
					}
				}

				stream_set_timeout($hServer, 20);
			}

			fclose($hServer);
		}

		return array($arResponseHeaders, $responseBody);
	}

	if (empty($errorMessage))
	{
		$body = array(
			"SERVER" => ($GLOBALS["APPLICATION"]->IsHTTPS() ? "https" : "http")."://".$_SERVER["HTTP_HOST"],
			"LOGIN" => $saleLogin,
			"PASSWORD" => $salePassword,
			"SITE_NAME" => COption::GetOptionString("main", "site_name", ""),
		);
		$body1 = http_build_query($body);
		if (!defined("BX_UTF"))
			$body1 = CharsetConverter::ConvertCharset($body1, SITE_CHARSET, "UTF-8");

		list($arResponseHeaders, $responseBody) = __CrmSaleQuery($crmUrlScheme, $crmUrlHost, $crmUrlPort, $crmLogin, $crmPassword, "", $body1, $errorMessageTmp);
		if (!empty($errorMessageTmp))
			$errorMessage .= $errorMessageTmp;
	}

	if (empty($errorMessage))
	{
		$isUTF = CUtil::DetectUTF8($responseBody);
		if (!$isUTF && SITE_CHARSET == "UTF-8")
		{
			$responseBody = CharsetConverter::ConvertCharset($responseBody, SITE_CHARSET, "CP1251");
		}

		if (mb_strpos($responseBody, "bsid=") !== false)
		{
			$p1 = mb_strpos($responseBody, "bsid=");
			$p2 = mb_strpos($responseBody, ";", $p1);

			$body["sessid"] = mb_substr($responseBody, $p1 + 5, $p2 - $p1 - 5);
			$body1 = http_build_query($body);
			if (!defined("BX_UTF"))
				$body1 = CharsetConverter::ConvertCharset($body1, SITE_CHARSET, "UTF-8");

			$cookies = [];
			$cookieRe = '/^Set-Cookie:(.+?)=(.+?);/';
			foreach ($arResponseHeaders as $h)
			{
				if (preg_match($cookieRe, $h, $m))
				{
					$cookies[] = trim($m[1]) . '=' . trim($m[2]);
				}
			}

			$head1 = "Cookie: " . join("; ", $cookies) . "\r\n";

			list($arResponseHeaders, $responseBody) = __CrmSaleQuery($crmUrlScheme, $crmUrlHost, $crmUrlPort, $crmLogin, $crmPassword, $head1, $body1, $errorMessageTmp);
			if (!empty($errorMessageTmp))
				$errorMessage .= $errorMessageTmp;
		}
	}

	if (empty($errorMessage))
	{
		list($httpVersion, $statusCode, $reasonPhrase) = explode(' ', $arResponseHeaders[0], 3);
		$responseBody = ltrim($responseBody);

		if(!defined("BX_UTF"))
			$responseBody = $APPLICATION->ConvertCharset($responseBody, "UTF-8", LANG_CHARSET);

		if (($statusCode == 401) || (mb_strpos($responseBody, "form_auth") !== false) || (mb_strpos($responseBody, "Permission denied") !== false))
		{
			$errorMessage .= GetMessage("SPTEN_SCRM_ERR_AUTH")."<br />";
		}
		else
		{
			$rcode = ToUpper(mb_substr($responseBody, 0, 2));
			if ($rcode == "ER")
				$errorMessage .= mb_substr($responseBody, 2)."<br />";
			elseif ($rcode != "OK")
				$errorMessage .= GetMessage("SPTEN_SCRM_ERR_ANSWER")."<br />";
			else
				$crmUrl4Import = trim(mb_substr($responseBody, 2));
		}
	}

	if (empty($errorMessage))
	{
		if ($createNewSaleUser)
		{
			$APPLICATION->SetGroupRight("sale", $groupId, "W", false);
			CGroup::SetModulePermission($groupId, "catalog", CTask::GetIdByLetter("R", "catalog"));
			CGroup::SetModulePermission($groupId, "main", CTask::GetIdByLetter("R", "main"));

			if (Loader::includeModule('iblock') && Loader::includeModule('catalog'))
			{
				$catalogs = array();
				$iterator = Catalog\CatalogIblockTable::getList(array(
					'select' => array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID')
				));
				while ($row = $iterator->fetch())
				{
					$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
					$catalogs[$row['IBLOCK_ID']] = $row['IBLOCK_ID'];
					$row['PRODUCT_IBLOCK_ID'] = (int)$row['PRODUCT_IBLOCK_ID'];
					if ($row['PRODUCT_IBLOCK_ID'] > 0)
						$catalogs[$row['PRODUCT_IBLOCK_ID']] = $row['PRODUCT_IBLOCK_ID'];
				}
				unset($row, $iterator);

				if (!empty($catalogs))
				{
					$iblockObject = new \CIBlock();

					$rightsId = null;
					$row = Main\TaskTable::getList(array(
						'select' => array('ID'),
						'filter' => array('=LETTER' => 'S', '=MODULE_ID' => 'iblock', '=SYS' => 'Y')
					))->fetch();
					if (!empty($row))
						$rightsId = $row['ID'];
					unset($row);
					$groupCode = 'G'.$groupId;

					foreach ($catalogs as $id)
					{
						$rightsMode = \CIBlock::GetArrayByID($id, 'RIGHTS_MODE');
						if ($rightsMode == Iblock\IblockTable::RIGHTS_SIMPLE)
						{
							$rights = \CIBlock::GetGroupPermissions($id);
							$rights[$groupId] = 'S';
							\CIBlock::SetPermission($id, $rights);
						}
						elseif ($rightsMode == Iblock\IblockTable::RIGHTS_EXTENDED && $rightsId !== null)
						{
							$rightsObject = new \CIBlockRights($id);
							$rights = $rightsObject->GetRights();
							$rights['n0'] = array(
								'GROUP_CODE'  => $groupCode,
								'DO_INHERIT' => 'Y',
								'IS_INHERITED' => 'N',
								'OVERWRITED' => 0,
								'TASK_ID' => $rightsId,
								'XML_ID' => null,
								'ENTITY_TYPE' => 'iblock',
								'ENTITY_ID' => $id
							);
							$rightsObject->SetRights($rights);
						}
					}
					unset($rights, $id);
					unset($iblockObject);
				}
				unset($catalogs);
			}

			$opt = COption::GetOptionString("sale", "1C_SALE_GROUP_PERMISSIONS", "");
			$opt .= (($opt != "") ? "," : "").$groupId;
			COption::SetOptionString("sale", "1C_SALE_GROUP_PERMISSIONS", $opt);

			function GetAccessArrTmp()
			{
				$PERM = array();
				@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/.access.php");
				return $PERM;
			}

			$arFPermsTmp = GetAccessArrTmp();
			$arFPerms = (array_key_exists("admin", $arFPermsTmp)) ? $arFPermsTmp["admin"] : array();
			$arFPerms[$groupId.""] = "R";
			$APPLICATION->SetFileAccessPermission(array(SITE_ID, "/bitrix/admin"), $arFPerms);
		}

		LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&success=Y&crm_imp_url=".urlencode(($crmUrlScheme == 'ssl://' ? "https" : "http")."://".$crmUrlHost.":".$crmUrlPort.$crmUrl4Import)."&crm_url=".urlencode(($crmUrlScheme == 'ssl://' ? "https" : "http")."://".$crmUrlHost.":".$crmUrlPort));
	}
	else
	{
		if ($createNewSaleUser)
		{
			if ($groupId > 0)
			{
				$group = new CGroup();
				$group->Delete($groupId);
			}

			if ($userId > 0)
			{
				CUser::Delete($userId);
			}
		}
	}
}
if ($_SERVER["REQUEST_METHOD"] == "GET" && $_REQUEST["clear_crm_stat"] == "Y" && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
	if (!isset($_REQUEST["clear_crm_stat_url"]))
	{
		COption::SetOptionString("sale", "~crm_integration", "");
	}
	else
	{
		$ar = array();
		foreach ($arCrmIntegration as $k => $v)
		{
			if ($k != $_REQUEST["clear_crm_stat_url"])
				$ar[$k] = $v;
		}
		COption::SetOptionString("sale", "~crm_integration", serialize($ar));
	}
	LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SPTEN_CRM_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CAdminMessage::ShowMessage($errorMessage);
if ($_REQUEST["success"] == "Y")
{
	$crmIntegrationUrl = htmlspecialcharsbx($_REQUEST["crm_url"]);
	$crmIntegrationImpUrl = htmlspecialcharsbx($_REQUEST["crm_imp_url"]);

	$find = "/^(http:\/\/|https:\/\/|ssl:\/\/)/i";
	if(!preg_match($find, $crmIntegrationUrl, $res) && !empty($_REQUEST["crm_url"]))
		$crmIntegrationUrl = "http://".$crmIntegrationUrl;

	if(!preg_match($find, $crmIntegrationImpUrl, $res) && !empty($_REQUEST["crm_imp_url"]))
		$crmIntegrationImpUrl = "http://".$crmIntegrationImpUrl;

	$successMessage = GetMessage(
		"SPTEN_SCRM_SUCCESS_MESS",
		array(
			"#URL#" => $crmIntegrationUrl,
			"#PATH#" => $crmIntegrationUrl."/crm/configs/external_sale/",
			"#IMP#" => !empty($crmIntegrationImpUrl) ? $crmIntegrationImpUrl : $crmIntegrationUrl."/crm/configs/external_sale/",
		)
	);

	$successMessage .= '<br /><br /><div class="crm-admin-buttons" id="id_new_crm_btns">
		<span class="crm-admin-button-wrap">
			<a target="_blank" href="'.(!empty($crmIntegrationImpUrl) ? $crmIntegrationImpUrl : $crmIntegrationUrl."/crm/configs/external_sale/").'" class="adm-btn adm-btn-green">'.GetMessage("SPTEN_SCRM_CRM_BTN").'</a>
		</span>
	</div>';
	CAdminMessage::ShowMessage(array("MESSAGE"=>$successMessage, "TYPE"=>"OK", "HTML"=>true));
}
?>

<div class="crm-admin-wrap">
	<?
	if (count($arCrmIntegration) <= 0)
	{
		?>
		<p class="crm-admin-paragraph">
			<?= GetMessage("SPTEN_SCRM_TEXT1") ?>
		</p>
		<p class="crm-admin-paragraph">
			<?= GetMessage("SPTEN_SCRM_TEXT2") ?>
		</p>
		<p class="crm-admin-paragraph">
			<?= GetMessage("SPTEN_SCRM_TEXT3") ?>
		</p>
		<div class="crm-admin-banner crm-admin-banner-<?= in_array(LANGUAGE_ID, array("ru", "en", "de")) ? LANGUAGE_ID : "en" ?>"></div>
		<p class="crm-admin-paragraph"><?= GetMessage("SPTEN_SCRM_TEXT4") ?></p>
		<?
	}
	else
	{
		?>
		<div class="crm-admin-title"><?= GetMessage("SPTEN_SCRM_SHOW_SUBTITLE_LIST") ?></div>
		<p class="crm-admin-paragraph">
			<?= GetMessage("SPTEN_SCRM_SHOW_SUBTITLE_LIST_HINT") ?>
		</p>
		<?
		foreach ($arCrmIntegration as $crmUrl => $arCrm)
		{
			?>
			<div class="crm-admin-statistics-block">
				<div class="crm-admin-stat-top">
					<a target="_blank" class="crm-admin-title-link" href="<?= htmlspecialcharsbx($crmUrl) ?>"><?= htmlspecialcharsbx($crmUrl) ?></a>
					<a class="crm-admin-reset" href="/bitrix/admin/sale_crm.php?clear_crm_stat=Y&clear_crm_stat_url=<?= urlencode($crmUrl) ?>&<?= bitrix_sessid_get() ?>"><?= GetMessage("SPTEN_SCRM_CLEAR_STAT") ?></a>
				</div>
				<table class="crm-admin-stat-content" cellspacing="0">
				<tr>
					<td class="crm-admin-stat-cont-left"><?= GetMessage("SPTEN_SCRM_SHOW_TOTSTAT") ?>:</td>
					<td><?= GetMessage("SPTEN_SCRM_SHOW_TEXT", array("#ORDERS#" => intval($arCrm["TOTAL_ORDERS"]), "#CONTACTS#" => intval($arCrm["TOTAL_CONTACTS"]), "#COMPANIES#" => intval($arCrm["TOTAL_COMPANIES"]))) ?></td>
				</tr>
				<tr>
					<td class="crm-admin-stat-cont-left"><nobr><?= GetMessage("SPTEN_SCRM_SHOW_DATE") ?>:</nobr></td>
					<td><?= ConvertTimeStamp($arCrm["DATE"], "FULL") ?></td>
				</tr>
				<tr>
					<td class="crm-admin-stat-cont-left"><?= GetMessage("SPTEN_SCRM_SHOW_LASTSTAT") ?>:</td>
					<td><?= GetMessage("SPTEN_SCRM_SHOW_TEXT", array("#ORDERS#" => intval($arCrm["NUM_ORDERS"]), "#CONTACTS#" => intval($arCrm["NUM_CONTACTS"]), "#COMPANIES#" => intval($arCrm["NUM_COMPANIES"]))) ?></td>
				</tr>
				</table>
				<a class="crm-admin-stat-link" target="_blank" href="<?= htmlspecialcharsbx($crmUrl) ?>/crm/configs/external_sale/"><?= GetMessage("SPTEN_SCRM_SHOW_SETUP") ?></a>
			</div>
			<?
		}
		?>
		<div class="crm-admin-add-integration">
		<div class="crm-admin-title"><?= GetMessage("SPTEN_SCRM_SHOW_SUBTITLE_ADD") ?></div>
		<?= GetMessage("SPTEN_SCRM_SHOW_SUBTITLE_ADD_HINT"); ?>
		<?
	}
	?>
	<div class="crm-admin-buttons" id="id_new_crm_btns">
		<span class="crm-admin-button-wrap">
			<a href="javascript:SaleCrmAdminShowRegForm(true)" class="adm-btn adm-btn-green"><?= GetMessage("SPTEN_SCRM_REG_BTN_SETUP") ?></a>
			<div class="crm-admin-button-text"><?= GetMessage("SPTEN_SCRM_REG_BTN_SETUP_HINT") ?></div>
		</span>
		<span class="crm-admin-button-or">&nbsp;&nbsp;<?= GetMessage("SPTEN_SCRM_REG_BTN_OR") ?></span>
		<span class="crm-admin-button-wrap">
			<a target="_blank" href="https://www.bitrix24.<? if (LANGUAGE_ID == "ru") echo "ru"; elseif (LANGUAGE_ID == "de") echo "de"; else echo "com"; ?>/" class="adm-btn adm-btn-green"><?= GetMessage("SPTEN_SCRM_REG_BTN_24") ?></a>
			<div class="crm-admin-button-text"><?= GetMessage("SPTEN_SCRM_REG_BTN_24_HINT") ?></div>
		</span>
	</div>
	<script type="text/javascript">
		function SaleCrmAdminShowRegForm(v)
		{
			document.getElementById("id_new_crm_reg_form").style.display = v ? "" : "none";
			document.getElementById("id_new_crm_btns").style.display = v ? "none" : "";
		}
	</script>
	<div class="crm-admin-set" style="display:none;" id="id_new_crm_reg_form">
		<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1_do_create_link">
			<div class="crm-admin-set-title"><?= GetMessage("SPTEN_SCRM_REG_TITLE") ?></div>
			<table class="crm-admin-set-content-table" cellspacing="0">
				<tr>
					<td class="crm-admin-set-left"><span class="required">*</span><?= GetMessage("SPTEN_SCRM_REG_URL") ?>:</td>
					<td class="crm-admin-set-right">
						<select class="crm-admin-set-select" name="CRM_URL_SCHEME">
							<option value="https"<?= (($_REQUEST["CRM_URL_SCHEME"]=="https") ? " selected" : "")?>>https</option>
							<option value="http"<?= (($_REQUEST["CRM_URL_SCHEME"]=="http") ? " selected" : "")?>>http</option>
						</select><span class="crm-admin-set-text">&nbsp;://&nbsp;</span><input type="text" class="crm-admin-set-input" name="CRM_URL_SERVER" value="<?= htmlspecialcharsbx($_REQUEST["CRM_URL_SERVER"]) ?>"/>
					</td>
				</tr>
				<tr>
					<td class="crm-admin-set-left"><nobr><span class="required">*</span><?= GetMessage("SPTEN_SCRM_REG_LOGIN") ?>:</nobr></td>
					<td class="crm-admin-set-right"><input class="crm-admin-set-input" type="text" name="CRM_LOGIN" value="<?= htmlspecialcharsbx($_REQUEST["CRM_LOGIN"]) ?>"/></td>
				</tr>
				<tr>
					<td class="crm-admin-set-left"><nobr><span class="required">*</span><?= GetMessage("SPTEN_SCRM_REG_PWD") ?>:</nobr></td>
					<td class="crm-admin-set-right"><input class="crm-admin-set-input" type="password" name="CRM_PASSWORD" value=""/></td>
				</tr>
				<tr>
					<td class="crm-admin-set-left"></td>
					<td class="crm-admin-set-right">
						<?= GetMessage("SPTEN_SCRM_REG_USER_BUS_SET_HINT") ?>
						<br/>
						<br/>
						<input type="checkbox" class="crm-admin-set-checkbox" id="id_CRM_BUS_USER_SET_C" name="CRM_BUS_USER_SET_C" value="Y"<?= ($_REQUEST["CRM_BUS_USER_SET_C"] == "Y") ? " checked" : "" ?> onclick="SaleCrmAdminShowRegFormUser(this.checked)"/><label for="id_CRM_BUS_USER_SET_C"><span class="crm-admin-set-checkbox-label"><?= GetMessage("SPTEN_SCRM_CRM_BUS_USER_SET") ?></span></label>
						<script type="text/javascript">
							function SaleCrmAdminShowRegFormUser(v)
							{
								document.getElementById("id_CRM_BUS_USER_SET_C_login").style.display = v ? "none" : "";
								document.getElementById("id_CRM_BUS_USER_SET_C_pwd").style.display = v ? "none" : "";
							}
						</script>
					</td>
				</tr>
				<tr id="id_CRM_BUS_USER_SET_C_login" style="display: none;">
					<td class="crm-admin-set-left"><span class="required">*</span><?= GetMessage("SPTEN_SCRM_REG_USER_SET_LOGIN") ?>:</td>
					<td class="crm-admin-set-right"><input class="crm-admin-set-input" type="text" name="CRM_BUS_USER_LOGIN" value="<?= htmlspecialcharsbx($_REQUEST["CRM_BUS_USER_LOGIN"]) ?>"/></td>
				</tr>
				<tr id="id_CRM_BUS_USER_SET_C_pwd" style="display: none;">
					<td class="crm-admin-set-left"><span class="required">*</span><?= GetMessage("SPTEN_SCRM_REG_USER_SET_PWD") ?>:</td>
					<td class="crm-admin-set-right"><input class="crm-admin-set-input" type="password" name="CRM_BUS_USER_PASSWORD" value=""/></td>
				</tr>
			</table>
			<div class="crm-admin-set-button">
				<a class="adm-btn adm-btn-green" href='javascript:document.forms["form1_do_create_link"].submit();'><?= GetMessage("SPTEN_SCRM_REG_SAVE") ?></a>&nbsp;&nbsp;
				<a class="adm-btn" href="javascript:SaleCrmAdminShowRegForm(false)"><?= GetMessage("SPTEN_SCRM_REG_CANCEL") ?></a>
			</div>
			<?=bitrix_sessid_post();?>
			<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
			<input type="hidden" name="do_create_link" value="Y">
		</form>
	</div>

	<?= (count($arCrmIntegration) > 0) ? "</div>" : "" ?>

</div>


<?
if ($_SERVER["REQUEST_METHOD"] == "POST" && $do_create_link == "Y")
{
	?>
	<script type="text/javascript">
		SaleCrmAdminShowRegForm(true);
		SaleCrmAdminShowRegFormUser(<?= ($_REQUEST["CRM_BUS_USER_SET_C"] == "Y") ? "true" : "false" ?>);
	</script>
	<?
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
