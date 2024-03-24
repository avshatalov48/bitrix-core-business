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

$connection = \Bitrix\Main\Application::getConnection();

$error = '';
$ddl = '';
$displayLinesCount = 15;
$lines = 0;
$etime = microtime(1) + 5;
$tablesToconvert = CBaseUtf8WizardStep::getTables();
foreach ($tablesToconvert as $tableName => $_)
{
	try
	{
		if ($tableName === 'b_geoname')
		{
			// Preserve llready utf data stored
			$connection->query('ALTER TABLE b_geoname MODIFY NAME BLOB');
			// Convert Table
			$connection->query(CBaseUtf8WizardStep::getTableAlter($tableName));
			// Make NAME column utf8 w/o conversion
			$connection->query('ALTER TABLE b_geoname MODIFY NAME varchar(600) CHARACTER SET utf8 COLLATE utf8_unicode_ci');
		}
		else
		{
			$ddl = CBaseUtf8WizardStep::getTableAlter($tableName);
			$connection->query($ddl);
		}
	}
	catch (\Bitrix\Main\DB\SqlException $e)
	{
		$error = $e->getMessage();
		break;
	}

	if ($lines < $displayLinesCount)
	{
		echo $tableName . '<br />';
	}

	$lines++;
	if (microtime(1) > $etime)
	{
		break;
	}
}

if ($lines > $displayLinesCount)
{
	echo GetMessage('UTFWIZ_MORE', ['#count#' => $lines - $displayLinesCount]) . '<br />';
}

if ($error)
{
	echo '<br />' . GetMessage('UTFWIZ_TABLE_CONVERT_ERROR') . '<br />';
	echo '<p class="utf8wiz_err">' . htmlspecialcharsEx($ddl) . '</p><p class="utf8wiz_err">' . htmlspecialcharsEx($error) . '</p>';
	echo GetMessage('UTFWIZ_TABLE_CONVERT_ERROR_ADVICE') . '<br />';
}
else
{
	$tablesToconvert = CBaseUtf8WizardStep::getTables();
	if ($tablesToconvert)
	{
		echo '<br />' . GetMessage('UTFWIZ_TABLE_PROGRESS', ['#tables#' => count($tablesToconvert)]) . '<br />';
		echo '<script>BX.Wizard.Utf8.action(\'convert\')</script>';
	}
	else
	{
		echo '<br />' . GetMessage('UTFWIZ_ALL_DONE');
		echo '<script>BX.Wizard.Utf8.EnableButton();</script>';
	}
}

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
