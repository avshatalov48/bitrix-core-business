<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

IncludeModuleLangFile(__FILE__);
Bitrix\Main\Loader::includeModule('abtest');

$error = getMessage('ACCESS_DENIED');

if ($APPLICATION->getGroupRight('abtest') >= 'W')
{
	$error = false;

	$arSites = array();
	$dbSites = Bitrix\Main\SiteTable::getList(array('select' => array('LID')));
	while ($arSite = $dbSites->fetch())
		$arSites[] = $arSite['LID'];

	switch ($_REQUEST['action'])
	{
		case 'copy':

			$site   = isset($_REQUEST['site']) ? $_REQUEST['site'] : null;
			$type   = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
			$source = isset($_REQUEST['source']) ? $_REQUEST['source'] : null;

			if (!check_bitrix_sessid())
				$error = getMessage('ABTEST_CSRF_ERROR');

			if (empty($site) || empty($type) || empty($source))
				$error = getMessage('ABTEST_AJAX_ERROR');

			if (!in_array($type, array('page')))
				$error = getMessage('ABTEST_AJAX_ERROR');

			if (!in_array($site, $arSites))
				$error = getMessage('ABTEST_AJAX_ERROR');

			if ($error === false)
			{
				$source = Bitrix\Main\Text\Encoding::convertEncodingToCurrent($source);
				$source = Bitrix\ABTest\AdminHelper::getRealPath($site, $source);

				if (empty($source))
					$error = getMessage('ABTEST_UNKNOWN_PAGE');

				if ($error === false)
				{
					$docRoot = rtrim(\Bitrix\Main\SiteTable::getDocumentRoot($site), '/');

					$source = new Bitrix\Main\IO\File($docRoot.$source);

					$k = 0;
					do
					{
						$targetPath = BX_ROOT.'/abtest/'.date('Ymd').'/'.sprintf('%u', crc32(rand().time())).$source->getName();
						$target = new Bitrix\Main\IO\File($docRoot.$targetPath);
					}
					while ($target->isExists() && $k++ < 10);

					if ($target->isExists())
					{
						$error = getMessage('ABTEST_AJAX_ERROR');
					}
					else
					{
						$success = copyDirFiles(
							$source->getPath(), $target->getPath(),
							false, false, false
						);

						if ($success)
							$result = $targetPath;
						else
							$error = getMessage('ABTEST_AJAX_ERROR');
					}
				}
			}

			break;

		case 'check':

			$site  = isset($_REQUEST['site']) ? $_REQUEST['site'] : null;
			$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
			$value = isset($_REQUEST['value']) ? $_REQUEST['value'] : null;

			if (empty($site) || empty($type) || empty($value))
				$error = getMessage('ABTEST_AJAX_ERROR');

			if (!in_array($type, array('page')))
				$error = getMessage('ABTEST_AJAX_ERROR');

			if (!in_array($site, $arSites))
				$error = getMessage('ABTEST_AJAX_ERROR');

			if ($error === false)
			{
				$value = Bitrix\Main\Text\Encoding::convertEncodingToCurrent($value);
				$value = Bitrix\ABTest\AdminHelper::getRealPath($site, $value);

				if (empty($value))
					$error = getMessage('ABTEST_UNKNOWN_PAGE');

				if ($error === false)
					$result = $value;
			}

			break;

		default:
			$error = getMessage('ABTEST_AJAX_ERROR');
	}
}

if ($error === false)
{
	$data = array(
		'result' => $result,
		'error'  => false
	);
}
else
{
	$data = array(
		'result' => 'error',
		'error'  => $error
	);
}

$APPLICATION->RestartBuffer();

header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
echo json_encode($data);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
