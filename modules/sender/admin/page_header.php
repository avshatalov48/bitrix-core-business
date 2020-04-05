<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Security;

if(!Loader::includeModule("sender"))
{
	ShowError(Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));
}

if (!Security\User::current()->canView())
{
	/** @var \CAllMain $APPLICATION */
	$APPLICATION->AuthForm(Security\AccessChecker::getError()->getMessage());
}

$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

$senderPathPrefix = '/bitrix/admin/sender_';
$senderAdminPaths = [
	'CONTACT_LIST' => $senderPathPrefix . 'contacts.php',
	'CONTACT_IMPORT' => $senderPathPrefix . 'contacts.php?import',
	'SEGMENT_EDIT' => $senderPathPrefix . 'segments.php?edit&ID=#id#',
	'SEGMENT_ADD' => $senderPathPrefix . 'segments.php?edit&ID=0',
	'CAMPAIGN_EDIT' => $senderPathPrefix . 'campaign.php?edit&ID=#id#',
	'CAMPAIGN_ADD' => $senderPathPrefix . 'campaign.php?edit&ID=0',
	'LETTER_LIST' => $senderPathPrefix . 'letters.php',
	'LETTER_EDIT' => $senderPathPrefix . 'letters.php?edit&ID=#id#',
	'LETTER_ADD' => $senderPathPrefix . 'letters.php?edit&ID=0',
	'ABUSES' => $senderPathPrefix . 'letters.php?abuses',
];

?>
<script type="text/javascript">
	if (BX('adm-workarea'))
	{
		BX.removeClass(BX('adm-workarea'), 'adm-workarea');
		BX.addClass(BX('adm-workarea'), 'adm-workarea-sender');
	}
</script>
