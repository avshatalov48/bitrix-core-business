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
	/** @var CMain $APPLICATION */
	$APPLICATION->AuthForm(Security\AccessChecker::getError()->getMessage());
}

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
]);

$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

$senderPathPrefix = '/bitrix/admin/sender_';
$senderAdminPaths = [
	'CONTACT_LIST' => $senderPathPrefix . 'contacts.php?lang=' . LANGUAGE_ID,
	'CONTACT_IMPORT' => $senderPathPrefix . 'contacts.php?import&lang=' . LANGUAGE_ID,
	'SEGMENT_EDIT' => $senderPathPrefix . 'segments.php?edit&ID=#id#&lang=' . LANGUAGE_ID,
	'SEGMENT_ADD' => $senderPathPrefix . 'segments.php?edit&ID=0&lang=' . LANGUAGE_ID,
	'CAMPAIGN_EDIT' => $senderPathPrefix . 'campaign.php?edit&ID=#id#&lang=' . LANGUAGE_ID,
	'CAMPAIGN_ADD' => $senderPathPrefix . 'campaign.php?edit&ID=0&lang=' . LANGUAGE_ID,
	'LETTER_LIST' => $senderPathPrefix . 'letters.php?lang=' . LANGUAGE_ID,
	'LETTER_EDIT' => $senderPathPrefix . 'letters.php?edit&ID=#id#&lang=' . LANGUAGE_ID,
	'LETTER_ADD' => $senderPathPrefix . 'letters.php?edit&ID=0&lang=' . LANGUAGE_ID,
	'ABUSES' => $senderPathPrefix . 'letters.php?abuses&lang=' . LANGUAGE_ID,
];

?>
<script>
	if (BX('adm-workarea'))
	{
		BX.removeClass(BX('adm-workarea'), 'adm-workarea');
		BX.addClass(BX('adm-workarea'), 'adm-workarea-sender');
	}
	if (!BX.message.SITE_ID)
	{
		BX.message['SITE_ID'] = '';
	}
</script>
