<?php
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
/** @global CUser $USER */
global $USER;

if (!$USER->isAdmin() || !check_bitrix_sessid())
{
	echo GetMessage('UTFWIZ_ERROR_ACCESS_DENIED');
	require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
	die();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/wizard.php';

$lang = $_REQUEST['lang'];
if (!preg_match('/^[a-z0-9_]{2}$/i', $lang))
{
	$lang = 'en';
}

$wizard = new CWizard('bitrix:perfmon.utf8');
$wizard->IncludeWizardLang('scripts/convert.php', $lang);
require_once $_SERVER['DOCUMENT_ROOT'] . $wizard->path . '/wizard.php';

$ddl = '';
$error = '';
$databaseDefaultEncoding = CBaseUtf8WizardStep::getDatabaseDefaultEncoding();
if (!preg_match('/^utf8/i', $databaseDefaultEncoding))
{
	$connection = \Bitrix\Main\Application::getConnection();
	try
	{
		$ddl = CBaseUtf8WizardStep::getDatabaseAlter();
		$connection->query($ddl);
	}
	catch (\Bitrix\Main\DB\SqlException $e)
	{
		$error = $e->getMessage();
	}
}

if ($error)
{
	echo '<br />' . GetMessage('UTFWIZ_TABLE_CONVERT_ERROR') . '<br />';
	echo '<p class="utf8wiz_err">' . htmlspecialcharsEx($ddl) . '</p><p class="utf8wiz_err">' . htmlspecialcharsEx($error) . '</p>';
}
else
{
	$tablesToconvert = CBaseUtf8WizardStep::getTables();
	if ($tablesToconvert)
	{
		echo GetMessage('UTFWIZ_TABLE_PROGRESS', ['#tables#' => count($tablesToconvert)]) . '<br />';
		echo '<script>BX.Wizard.Utf8.action(\'convert\')</script>';
	}
	else
	{
		echo GetMessage('UTFWIZ_ALL_DONE');
		echo '<script>BX.Wizard.Utf8.EnableButton();</script>';
	}
}

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
