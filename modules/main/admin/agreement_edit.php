<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
use Bitrix\Main\Localization\Loc;

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/agreement_edit.php");

Loc::loadMessages(__FILE__);

$canEdit = $USER->CanDoOperation('edit_other_settings');
$canView = $USER->CanDoOperation('view_other_settings');
if(!$canEdit && !$canView)
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"	=> Loc::getMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"	=> "/bitrix/admin/agreement_admin.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("MAIN_ADMIN_MENU_LIST"),
		"ICON"	=> "btn_list"
	)
);

if((int) $_REQUEST['ID'] > 0 && $canEdit)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("MAIN_ADMIN_MENU_ADD"),
		"LINK"	=> BX_ROOT . "/admin/agreement_edit.php?ID=0&lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("MAIN_ADMIN_MENU_ADD"),
		"ICON"	=> "btn_new"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

?>
<div id="USER_CONSENT_AGREEMENT_EDIT" style="background: white; padding: 20px;">
<?

$APPLICATION->IncludeComponent(
	"bitrix:main.userconsent.edit",
	"",
	array(
		'ID' => $_REQUEST['ID'],
		'PATH_TO_USER_PROFILE' => BX_ROOT . '/admin/user_edit.php?ID=#user_id#&lang=' . LANGUAGE_ID,
		'PATH_TO_LIST' => BX_ROOT . '/admin/agreement_admin.php?lang=' . LANGUAGE_ID,
		'PATH_TO_ADD' => BX_ROOT . '/admin/agreement_edit.php?ID=0&lang=' . LANGUAGE_ID,
		'PATH_TO_EDIT' => BX_ROOT . '/admin/agreement_edit.php?ID=#id#&lang=' . LANGUAGE_ID,
		'PATH_TO_CONSENT_LIST' => BX_ROOT . '/admin/agreement_consents.php?AGREEMENT_ID=#id#&lang=' . LANGUAGE_ID,
		'CAN_EDIT' => $canEdit
	)
);

?>
</div>
<script type="text/javascript">
	(function () {
		var list = BX('USER_CONSENT_AGREEMENT_EDIT').querySelectorAll('.webform-small-button');
		list = BX.convert.nodeListToArray(list);
		list.forEach(function (node) {
			if (BX.hasClass(node, 'webform-small-button-accept'))
			{
				BX.addClass(node, 'adm-btn-save');
			}
			else
			{
				BX.addClass(node, 'adm-btn');
			}
			node.style['margin-right'] = '10px';
			node.parentNode.style['text-align'] = 'left';
		})
	})();
</script>
<?

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
