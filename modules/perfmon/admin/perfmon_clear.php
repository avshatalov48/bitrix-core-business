<?php
use Bitrix\Main\Loader;

define('ADMIN_MODULE_NAME', 'perfmon');
define('PERFMON_STOP', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
/** @var CUser $USER */
Loader::includeModule('perfmon');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';

IncludeModuleLangFile(__FILE__);

$RIGHT = CMain::GetGroupRight('perfmon');
if (!$USER->IsAdmin() || ($RIGHT < 'W'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

/** @var \Bitrix\Main\HttpRequest $request */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if (
	$request->isPost()
	&& $request->getPost('clear') !== null
	&& check_bitrix_sessid()
)
{
	CPerfomanceComponent::Clear();
	CPerfomanceSQL::Clear();
	CPerfomanceHit::Clear();
	CPerfomanceError::Clear();
	CPerfomanceCache::Clear();
	$_SESSION['PERFMON_CLEAR_MESSAGE'] = GetMessage('PERFMON_CLEAR_MESSAGE');
	LocalRedirect('/bitrix/admin/perfmon_clear.php?lang=' . LANGUAGE_ID);
}

$APPLICATION->SetTitle(GetMessage('PERFMON_CLEAR_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

if ($_SESSION['PERFMON_CLEAR_MESSAGE'])
{
	$message = new CAdminMessage([
		'MESSAGE' => $_SESSION['PERFMON_CLEAR_MESSAGE'],
		'TYPE' => 'OK',
	]);
	echo $message->Show();
	unset($_SESSION['PERFMON_CLEAR_MESSAGE']);
}
?>

<form name="clear_form" method="post" action="<?php echo $APPLICATION->GetCurPage();?>">
	<?php echo bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?php echo LANG?>">
	<input type="submit" name="clear" value="<?php echo GetMessage('PERFMON_CLEAR_BUTTON');?>">
</form>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
