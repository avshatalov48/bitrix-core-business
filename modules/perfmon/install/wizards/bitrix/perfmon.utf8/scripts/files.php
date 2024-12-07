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
$wizard->IncludeWizardLang('scripts/files.php', $lang);
require_once $_SERVER['DOCUMENT_ROOT'] . $wizard->path . '/wizard.php';

$token = $_REQUEST['next'] ?? '' ;
$basePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
$pageSize = 100;

$processor = new FileProcessor($_REQUEST['sourceEncoding'] ?? 'cp1251');
$processor->setExclude(COption::GetOptionString('perfmon', 'utf_wizard_exclude_mask', ''));
$processor->init();

$error = '';
$filePath = '';
$displayLinesCount = 15;
$lines = 0;
$etime = microtime(1) + 2;
while (microtime(1) < $etime)
{
	$result = [];
	$token = list_files($basePath, $token, $pageSize, ($_REQUEST['skipLinks'] ?? '') === 'Y', $result);
	foreach ($result as $filePath)
	{
		switch ($processor->process($basePath, $filePath))
		{
		case FileProcessor::FILE_READ_ERROR:
			$error = CBaseUtf8WizardStep::GetMessage('UTFWIZ_READ_ERROR');
			break 3;
		case FileProcessor::FILE_WRITE_ERROR:
			$error = CBaseUtf8WizardStep::GetMessage('UTFWIZ_WRITE_ERROR');
			break 3;
		}

		if ($lines < $displayLinesCount)
		{
			echo htmlspecialcharsEx($filePath) . '<br />';
		}

		$lines++;
	}

	if (!$token)
	{
		break;
	}
}

if ($error)
{
	echo '<p class="utf8wiz_err">' . $error . ':<br />' . htmlspecialcharsEx($filePath) . '</p>';
	echo CBaseUtf8WizardStep::GetMessage('UTFWIZ_ROGHTS_CHECK_ADVICE', [
		'#CHECK_HREF#' => '/bitrix/admin/site_checker.php?lang=' . LANGUAGE_ID . '&tabControl_active_tab=edit2',
	]) . '<br />';
}
else
{
	if ($lines > $displayLinesCount)
	{
		echo CBaseUtf8WizardStep::GetMessage('UTFWIZ_MORE', ['#count#' => $lines - $displayLinesCount]) . '<br />';
	}

	if ($token)
	{
		echo '<script>BX.Wizard.Utf8.action(\'files\', ' . \Bitrix\Main\Web\Json::encode($token) . ')</script>';
	}
	else
	{
		$cultureList = \Bitrix\Main\Localization\CultureTable::getList();
		while ($culture = $cultureList->fetch())
		{
			if ($culture['CHARSET'] !== 'UTF-8')
			{
				//Save
				if (!COption::GetOptionString('perfmon', 'utf_wizard_ulture_' . $culture['ID'], ''))
				{
					COption::SetOptionString('perfmon', 'utf_wizard_culture_' . $culture['ID'], $culture['CHARSET']);
				}
				//And update
				\Bitrix\Main\Localization\CultureTable::update($culture['ID'], [
					'CHARSET' => 'UTF-8',
				]);
			}
		}

		echo '<br />' . CBaseUtf8WizardStep::GetMessage('UTFWIZ_ALL_DONE');
		echo '<script>BX.Wizard.Utf8.EnableButton();</script>';
	}
}

class FileProcessor
{
	const FILE_SKIPPED = 0;
	const FILE_READ_ERROR = 1;
	const FILE_IS_UTF = 2;
	const FILE_WRITE_ERROR = 3;
	const FILE_CONVERTED = 4;

	protected $sourceEncoding = '';
	protected $langDirs = [];
	protected $langDirRegexp = '';
	protected $siteDirs = [];

	public function __construct($sourceEncoding)
	{
		$this->sourceEncoding = $sourceEncoding;
	}

	public function init()
	{
		$this->langDirs = [];
		$cultureById = [];
		$cultureList = \Bitrix\Main\Localization\CultureTable::getList();
		while ($culture = $cultureList->fetch())
		{
			$charset = mb_strtolower($culture['CHARSET']);
			$this->langDirs['/lang/' . $culture['CODE'] . '/'] = $charset;
			$cultureById[$culture['ID']] = $culture;
		}
		$this->langDirRegexp = '#(' . implode('|', array_keys($this->langDirs)) . ')#';

		$this->siteDirs = [];
		$sites = \Bitrix\Main\SiteTable::getList([
			'select' => ['*'],
			'filter' => ['=ACTIVE' => 'Y'],
			'order' => [
				'DIR_LENGTH' => 'DESC',
				'DOMAIN_LIMITED' => 'DESC',
				'SORT' => 'ASC',
			],
		])->fetchAll();
		foreach ($sites as $site)
		{
			$charset = COption::GetOptionString('perfmon', 'utf_wizard_culture_' . $site['CULTURE_ID'], '') ?: $cultureById[$site['CULTURE_ID']]['CHARSET'];
			$charset = mb_strtolower($charset);
			$this->siteDirs[ltrim($site['DIR'], '/\\')] = $charset;
		}

	}

	protected $exclude = [];

	public function setExclude($str)
	{
		$search = ['\\', '.', '?', '*', "'"];
		$replace = ['/', '\\.', '.', '.*?', "\\'"];

		$this->exclude = [];

		$exc = str_replace($search, $replace, $str);
		foreach (explode(';', $exc) as $mask)
		{
			$mask = trim($mask);
			if ($mask !== '')
			{
				$this->exclude[] = "'^" . $mask . "$'";
			}
		}
	}

	protected function checkExclude($filePath)
	{
		foreach ($this->exclude as $pattern)
		{
			if (preg_match($pattern, $filePath))
			{
				return true;
			}
		}
		return false;
	}

	protected function checkBitrixExclude($filePath)
	{
		return
			str_starts_with($filePath, 'bitrix/modules/bxmobile/resources/templates/')
			|| str_starts_with($filePath, 'bitrix/modules/fileman/install/components/bitrix/player/mediaplayer')
			|| str_starts_with($filePath, 'bitrix/modules/fileman/install/components/bitrix/pdf.viewer/pdfjs')
			|| str_starts_with($filePath, 'bitrix/modules/photogallery/install/components/bitrix/photogallery.imagerotator/templates/.default/imagerotator')
			|| str_starts_with($filePath, 'bitrix/modules/crm/lib/requisite/phrases/')
		;
	}

	protected function checkCacheExclude($filePath)
	{
		return
			str_starts_with($filePath, 'bitrix/cache/')
			|| str_starts_with($filePath, 'bitrix/managed_cache/')
			|| str_starts_with($filePath, 'bitrix/stack_cache/')
		;
	}

	protected function checkExtentionExclude($filePath)
	{
		return preg_match('/\.(
			png|jpg|gif|woff|docx|pptx|xlsx|xpi|reg|zip|otf|ttf|eot|svg|woff2|
			http|json|xml|rar|webp|mp4|mp3|ogg|puml|ico|fb2|jpeg|swf|pws|exe|
			doc|prc|csv|phar|cer|db|webm|mem|data|cab|ts|flv|xls|pdf|ods|dia|
			cur|gadget|tar\.gz|pbit)$/x', $filePath
		) > 0;
	}

	public function process($basePath, $filePath)
	{
		if (
			$this->checkBitrixExclude($filePath)
			|| $this->checkCacheExclude($filePath)
			|| $this->checkExtentionExclude($filePath)
			|| $this->checkExclude($filePath)
		)
		{
			return static::FILE_SKIPPED;
		}

		//avoid memory overflow on archives etc.
		if (!preg_match('/\.(php|css|js)$/', $filePath))
		{
			return static::FILE_SKIPPED;
		}

		$contents = @file_get_contents($basePath . '/' . $filePath);
		if ($contents === false)
		{
			return static::FILE_READ_ERROR;
		}

		if (\Bitrix\Main\Text\Encoding::detectUtf8($contents, false))
		{
			return static::FILE_IS_UTF;
		}

		$encoding = $this->sourceEncoding;
		if (preg_match($this->langDirRegexp, $filePath, $match))
		{
			if ($this->langDirs[$match[1]] !== 'utf-8')
			{
				$encoding = $this->langDirs[$match[1]];
			}
		}
		else
		{
			foreach ($this->siteDirs as $sitePath => $charset)
			{
				if (str_starts_with($filePath, $sitePath))
				{
					$encoding = $charset;
					break;
				}
			}
		}

		if ($encoding === 'utf-8')
		{
			//return static::FILE_IS_UTF;
		}

		$contents = \Bitrix\Main\Text\Encoding::convertEncoding($contents, $encoding, 'utf-8');
		if ($contents)
		{
			$bytesWritten = file_put_contents($basePath . '/' . $filePath, $contents);
			if ($bytesWritten === false)
			{
				return static::FILE_WRITE_ERROR;
			}
		}

		return static::FILE_CONVERTED;
	}
}

function path_compare_function($path1, $path2)
{
	$path1 = explode('/', $path1);
	$path2 = explode('/', $path2);
	$c = min(count($path1), count($path2));
	for ($i = 0; $i < $c; $i++)
	{
		$len1 = strlen($path1[$i]);
		$len2 = strlen($path2[$i]);
		$comp = strncmp($path1[$i], $path2[$i], min($len1, $len2));
		if ($comp !== 0)
		{
			return $comp;
		}
		elseif ($len1 < $len2)
		{
			return -1;
		}
		elseif ($len1 > $len2)
		{
			return 1;
		}
	}
	return 0;
}

function directory_list_function($basePath)
{
	$directory = [];
	$basePath = rtrim($basePath, '/');
	if (is_dir($basePath))
	{
		$dh = opendir($basePath);
		if ($dh)
		{
			while (($entry = readdir($dh)) !== false)
			{
				if ($entry === '.' || $entry === '..')
				{
					continue;
				}
				$directory[] = $entry;
			}
			closedir($dh);
			sort($directory, SORT_STRING);
		}
	}

	return $directory;
}

function list_files($basePath, $token, $pageSize, $skipLink, &$result)
{
	$list_files_function = function ($list_files_function, $basePath, $token, $pageSize, $skipLink, &$result)
	{
		foreach (directory_list_function($basePath) as $entry)
		{
			$entry = $basePath . '/' . $entry;

			if ($skipLink && is_link($entry))
			{
				continue;
			}

			if (is_dir($entry))
			{
				if ($token && (path_compare_function($token, $entry) > 0))
				{
					continue;
				}
				$sub_entry = $list_files_function($list_files_function, $entry, $token, $pageSize, $skipLink, $result);
				if (count($result) == $pageSize)
				{
					return $sub_entry;
				}
			}
			elseif (is_file($entry))
			{
				if ($token && (path_compare_function($token, $entry) >= 0))
				{
					continue;
				}
				$result[] = $entry;
				if (count($result) == $pageSize)
				{
					return $entry;
				}
			}
		}
	};

	$result = [];
	$token = $list_files_function($list_files_function, $basePath, $token, $pageSize, $skipLink, $result);
	foreach ($result as $i => $file)
	{
		$result[$i] = ltrim(substr($file, strlen($basePath)), '/');
	}
	return $token;
}

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
