<?php
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2016 Bitrix             #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;

const NOT_CHECK_PERMISSIONS = true;
const STOP_STATISTICS = true;

$publicMode = defined('SELF_FOLDER_URL');

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

$ajaxMode = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] === 'Y');
$useSiteFormat = (isset($_REQUEST['format']) && $_REQUEST['format'] === 'Y');
$getRawData = false;
if ($ajaxMode)
{
	$getRawData = (isset($_REQUEST['raw']) && $_REQUEST['raw'] === 'Y');
}

$ID = (int)($_REQUEST['ID'] ?? 0);

$auth = false;
if ($USER->IsAuthorized())
{
	$auth = ($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users'));
	if (!$auth)
	{
		if (ModuleManager::isModuleInstalled('intranet') && Loader::includeModule('socialnetwork'))
		{
			$auth = CSocNetUser::CanProfileView($USER->GetID(), $ID);
		}
	}
}

$res = '';

if ($auth)
{
	$rsUser = CUser::GetByID($ID);
	$arUser = $rsUser->Fetch();
	if (is_array($arUser))
	{
		if ($useSiteFormat)
		{
			$res = CUser::FormatName(CSite::GetNameFormat(), $arUser, true, !$getRawData);
		}
		else
		{
			$res = htmlspecialcharsbx('(' . $arUser['LOGIN'] . ') ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME']); // old format
		}
		if (!$ajaxMode)
		{
			if ($publicMode)
			{
				$res = '[' . $arUser['ID'] .'] ' . $res;
			}
			else
			{
				$res = '[<a title="' . GetMessage('MAIN_EDIT_USER_PROFILE') . '" class="tablebodylink"'
					.' href="/bitrix/admin/user_edit.php?ID=' . $arUser['ID'] . '&lang=' . LANGUAGE_ID . '">'
					. $arUser['ID'] . '</a>] ' . $res
				;
			}
		}
	}
}

if ($ajaxMode)
{
	$APPLICATION->RestartBuffer();
	header('Content-Type: application/json');
	echo Json::encode([
		'ID' => $ID,
		'NAME' => $res,
	]);
}
else
{
	$strName = preg_replace(
		"/[^a-z0-9_\\[\\]:]/i",
		'',
		$_REQUEST['strName'] ?? ''
	);
?>
<script type="text/javascript">
if (window.parent.document.getElementById("div_<?=$strName?>"))
{
	window.parent.document.getElementById("div_<?=$strName?>").innerHTML = '<?=CUtil::JSEscape($res)?>';
}
</script>
	<?php
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_after.php');
