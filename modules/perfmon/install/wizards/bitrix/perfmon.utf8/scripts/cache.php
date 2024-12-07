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
$wizard->IncludeWizardLang('scripts/cache.php', $lang);
require_once $_SERVER['DOCUMENT_ROOT'] . $wizard->path . '/wizard.php';

$displayLinesCount = 15;
$lines = 0;
$etime = microtime(1) + 5;

$path = $_REQUEST['next'] ?? '' ;
if ($path === '')
{
	if (\Bitrix\Main\Loader::includeModule('landing'))
	{
		\Bitrix\Landing\Block::clearRepositoryCache();
	}
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/cache_files_cleaner.php';
$obCacheCleaner = new CFileCacheCleaner('');
if ($obCacheCleaner->InitPath($path))
{
	$obCacheCleaner->Start();
	while ($path = $obCacheCleaner->GetNextFile())
	{
		if (
			is_string($path)
			&& !preg_match('/(\.enabled|\.size|\.config\.php)$/', $path)
		)
		{
			@unlink($path);

			if ($lines < $displayLinesCount)
			{
				echo htmlspecialcharsEx(mb_substr($path, mb_strlen($_SERVER['DOCUMENT_ROOT']))) . '<br>';
			}

			$lines++;
			if (microtime(1) >= $etime)
			{
				break;
			}
		}
	}
}


if ($lines > $displayLinesCount)
{
	echo GetMessage('UTFWIZ_MORE', ['#count#' => $lines - $displayLinesCount]) . '<br />';
}

if (is_string($path))
{
	echo '<script>BX.Wizard.Utf8.action(\'cache\', ' . \Bitrix\Main\Web\Json::encode(mb_substr($path, mb_strlen($_SERVER['DOCUMENT_ROOT']))) . ')</script>';
}
else
{
	$connection = \Bitrix\Main\Application::getConnection();
	$helper = $connection->getSqlHelper();
	$connection->query(
		'INSERT INTO ' . $helper->quote(\Bitrix\Main\UserAuthActionTable::getTableName())
		. ' (USER_ID, PRIORITY, ACTION, ACTION_DATE)'
		. " SELECT ID, '" . \Bitrix\Main\UserAuthActionTable::PRIORITY_LOW . "', '" . \Bitrix\Main\UserAuthActionTable::ACTION_UPDATE . "', now() FROM b_user WHERE ACTIVE='Y'"
	);

	BXClearCache(true);
	/** @var CCacheManager $CACHE_MANAGER */
	$CACHE_MANAGER->CleanAll();
	/** @var CStackCacheManager $stackCacheManager */
	$stackCacheManager->CleanAll();
	\Bitrix\Main\Application::getInstance()->getTaggedCache()->deleteAllTags();
	\Bitrix\Main\Composite\Page::getInstance()->deleteAll();

	echo '<br />' . GetMessage('UTFWIZ_ALL_DONE');
	echo '<script>BX.Wizard.Utf8.EnableButton();</script>';
}


require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
