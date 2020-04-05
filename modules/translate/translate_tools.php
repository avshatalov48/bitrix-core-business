<?
use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;

Loc::loadLanguageFile(__FILE__);


$arLangDirs = NULL;
$arDirs = NULL;
$arFiles = NULL;
$arTLangs = NULL;
$arDirFiles = NULL;
$arLangDirFiles = NULL;
$arSearchParam = NULL;

/**
 * @param $arDirs
 * @param bool $showTranslationDifferences
 *
 * @global array $arLangDirs
 *
 * @return void
 */
function GetLangDirs($arDirs, $showTranslationDifferences = false)
{
	global $arLangDirs;
	if (is_array($arDirs))
	{
		if ($showTranslationDifferences)
		{
			foreach ($arDirs as $arr1)
			{
				if($arr1["IS_LANG"])
				{
					$arLangDirs[] = $arr1;
				}
			}
		}
		else
		{
			$arLangDirs = $arDirs;
		}
	}
}



/**
 * Loads file-folder structure into memory.
 *
 * @param string $path
 * @param bool $subDirs Recursively pass through into sub folders.
 * @param string[] $restrictLanguageList Restrict language list.
 *
 * @global array $arDirs
 * @global array $arFiles
 *
 * @return bool
 */
function GetTDirList($path, $subDirs = false, $restructLanguageList = array())
{
	global $arDirs, $arFiles;

	$fullPath = Translate\Path::tidy(Main\Application::getDocumentRoot(). '/'. $path. '/');

	if (preg_match('|^' . preg_quote(realpath(Main\Application::getDocumentRoot() . '/upload'), '|') . '|i' . BX_UTF_PCRE_MODIFIER, $fullPath))
	{
		return false;
	}

	$isLang = Translate\Path::isLangDir($path);

	if ($isLang)
	{
		$langId = Translate\Path::extractLangId($path);
		if (Main\Localization\Translation::useTranslationRepository() && in_array($langId, Translate\Translation::getTranslationRepositoryLanguages()))
		{
			$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $langId);
		}
		else
		{
			$fullPath = realpath($fullPath);
		}
	}
	else
	{
		$fullPath = realpath($fullPath);
	}

	$fullPath = Translate\Path::tidy($fullPath);


	$handle = @opendir($fullPath);
	if($handle)
	{
		$parent = Translate\Path::tidy('/'. $path. '/');
		$absParent = Main\Localization\Translation::convertLangPath(Main\Application::getDocumentRoot(). $parent, $langId);

		$arList = array();
		while (false !== ($file = readdir($handle)))
		{
			if (in_array($file, Translate\IGNORE_FS_NAMES))
			{
				continue;
			}

			$isDir = is_dir($absParent. $file);
			$pathPrepared = $parent. $file;

			if ($isDir && in_array($pathPrepared, Translate\IGNORE_BX_NAMES))
			{
				continue;
			}
			if (!$isDir && (substr($file, -4) !== '.php'))
			{
				continue;
			}

			$arList[$pathPrepared] = array(
				'IS_DIR' => ($isDir ? 'Y' : 'N'),
				'PARENT' => $parent,
				'PATH' => ($isDir ? $pathPrepared."/" : $pathPrepared),
				'FILE' => $file,
				'IS_LANG' => $isLang,
				'FULL_PATH' => $absParent. $file,
			);
			if (!$isDir)
			{
				$arList[$pathPrepared]['LANG'] = $isLang ? Translate\Path::extractLangId($pathPrepared) : '';
			}

			if (!$isDir && $isLang && Main\Localization\Translation::useTranslationRepository())
			{
				foreach (Translate\Translation::getTranslationRepositoryLanguages() as $langId)
				{
					if (!empty($restructLanguageList) && !in_array($langId, $restructLanguageList))
					{
						continue;
					}
					$langParent = Translate\Path::replaceLangId($arList[$pathPrepared]['PARENT'], $langId);
					$langPathPrepared = $langParent . $arList[$pathPrepared]['FILE'];

					$langPath = Main\Localization\Translation::convertLangPath(Main\Application::getDocumentRoot(). $langPathPrepared, $langId);

					if (file_exists($langPath))
					{
						$arList[$langPath] = array(
							'IS_DIR' => 'N',
							'PARENT' => $langParent,
							'PATH' => $langPathPrepared,
							'FILE' => $arList[$pathPrepared]['FILE'],
							'FULL_PATH' => $langPath,
							'IS_LANG' => true,
							'IS_LANG_REP' => true,
							'LANG' => $langId,
						);
					}
				}
			}
		}
		closedir($handle);

		ksort($arList);

		foreach($arList as $pathPrepared => $arr)
		{
			if($arr['IS_DIR'] == 'Y')
			{
				if($subDirs)
				{
					$arr['IS_LANG'] |= GetTDirList($pathPrepared. '/', $subDirs, $restructLanguageList);
				}

				$arDirs[] = $arr;
				//dir is lang if any of it's children is lang
				$isLang = $isLang || $arr['IS_LANG'];
			}
			elseif(Translate\Path::isLangDir($pathPrepared))
			{
				if(substr($arr['FILE'], -4) == '.php')
				{
					$arFiles[] = $arr;
				}
			}
		}
	}

	//flag for parent
	return $isLang;
}

/**
 * Collects phrases form files into array and convert encoding.
 *
 * @param string $filterKeyIndex Phrase key code.
 * @param string $targetEncoding Target encoding.
 * @param string[] $restrictLanguageList Restrict language list.
 *
 * @global array $arFiles
 *
 * @return array
 */
function GetTCSVArray($filterKeyIndex, $targetEncoding = '', $restructLanguageList = array())
{
	/** @global array $arFiles */
	global $arFiles;

	$arr = array();

	/**
	 * @global array $arFiles
	 * @var array $file
	 */
	foreach ($arFiles as $file)
	{
		$key = Translate\Path::replaceLangId($file['PATH'], '#LANG_ID#');
		if ($key != $filterKeyIndex)
		{
			continue;
		}

		if (isset($file['LANG']))
		{
			$langId = $file['LANG'];
		}
		else
		{
			$langId = Translate\Path::extractLangId($file['PATH']);
		}

		if (!empty($restructLanguageList) && !in_array($langId, $restructLanguageList))
		{
			continue;
		}

		if (isset($file['FULL_PATH']))
		{
			$fname = $file['FULL_PATH'];
		}
		else
		{
			$fname = Main\Application::getDocumentRoot(). $file['PATH'];
			$fname = Main\Localization\Translation::convertLangPath($fname, $langId);
		}

		$sourceEncoding = Main\Localization\Translation::getSourceEncoding($langId);

		$MESS = array();

		include($fname);

		if (!empty($MESS) && is_array($MESS))
		{
			foreach ($MESS as $phraseCode => $phrase)
			{
				$phraseCode = (string)$phraseCode;
				if ($phraseCode != '')
				{
					if ($targetEncoding != '' && $sourceEncoding != $targetEncoding)
					{
						$errorMessage = '';
						$phrase = Encoding::convertEncoding($phrase, $sourceEncoding, $targetEncoding, $errorMessage);
						/*
						if (!$phrase && !empty($errorMessage))
						{
							$this->addError(new Error($errorMessage));
						}
						*/
					}
					$arr[$key][$phraseCode][$langId] = $phrase;
				}
			}
		}
	}

	return $arr;
}

/**
 * @param string $filePath
 * @param string $encodingIn
 * @param bool $rewriteMode
 * @param bool $mergeMode
 * @param string[] $errors
 * @return bool
 */
function SaveTCSVFile($filePath, $encodingIn, $rewriteMode, $mergeMode, &$errors)
{
	$languageList = Translate\Translation::getEnabledLanguages();
	$isUtfMode = Translate\Translation::isUtfMode();
	$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();


	$phraseList = array();
	$columnList = [];
	$fileIndex = null;
	$keyIndex = null;

	$csvFile = new Translate\CsvFile($filePath);
	if (!$csvFile->openLoad())
	{
		$errors[] = Loc::getMessage('TR_TOOLS_ERROR_EMPTY_FILE');
		return false;
	}

	if ($csvFile->hasUtf8Bom())
	{
		$encodingIn = 'utf-8';
	}

	$csvFile
		->setFieldsType(Translate\CsvFile::FIELDS_TYPE_WITH_DELIMITER)
		->setFirstHeader(false)
		->setFieldDelimiter(Translate\CsvFile::DELIMITER_TZP);

	$rowHead = $csvFile->fetch();
	if (
		!is_array($rowHead) ||
		empty($rowHead) ||
		(count($rowHead) == 1 && ($rowHead[0] === null || $rowHead[0] === ''))
	)
	{
		$errors[] = Loc::getMessage('BX_TRANSLATE_IMPORT_ERR_EMPTY_FIRST_ROW');
	}
	else
	{
		$columnList = array_flip($rowHead);
		foreach ($languageList as $keyLang => $langID)
		{
			if (!isset($columnList[$langID]))
			{
				unset($languageList[$keyLang]);
			}
		}
		if (!isset($columnList['file']))
		{
			$errors[] = Loc::getMessage('BX_TRANSLATE_IMPORT_ERR_DESTINATION_FIELD_ABSENT');
		}
		else
		{
			$fileIndex = $columnList['file'];
		}
		if (!isset($columnList['key']))
		{
			$errors[] = Loc::getMessage('BX_TRANSLATE_IMPORT_ERR_PHRASE_CODE_FIELD_ABSENT');
		}
		else
		{
			$keyIndex = $columnList['key'];
		}
		if (empty($languageList))
		{
			$errors[] = Loc::getMessage('BX_TRANSLATE_IMPORT_ERR_LANGUAGE_LIST_ABSENT');
		}
	}

	if (empty($errors))
	{
		$csvRowCounter = 1;
		while ($csvRow = $csvFile->fetch())
		{
			$csvRowCounter ++;
			if (
				!is_array($csvRow) ||
				empty($csvRow) ||
				(count($csvRow) == 1 && ($csvRow[0] === null || $csvRow[0] === ''))
			)
			{
				continue;
			}
			$file = (isset($csvRow[$fileIndex]) ? $csvRow[$fileIndex] : '');
			$key = (isset($csvRow[$keyIndex]) ? $csvRow[$keyIndex] : '');
			if ($file == '' || $key == '')
			{
				$rowErrors = [];
				if ($file == '')
				{
					$rowErrors[] = Loc::getMessage('BX_TRANSLATE_IMPORT_ERR_DESTINATION_FILEPATH_ABSENT');
				}
				if ($key == '')
				{
					$rowErrors[] = Loc::getMessage('BX_TRANSLATE_IMPORT_ERR_PHRASE_CODE_ABSENT');
				}
				$errors[] = Loc::getMessage(
					'TR_TOOLS_ERROR_LINE_FILE_EXT',
					['#LINE#' => $csvRowCounter, '#ERROR#' => implode('; ', $rowErrors)]
				);
				unset($rowErrors);
				continue;
			}



			$rowErrors = [];

			if (!isset($phraseList[$file]))
			{
				$phraseList[$file] = [];
			}
			foreach ($languageList as $languageId)
			{
				if (!isset($phraseList[$file][$languageId]))
				{
					$phraseList[$file][$languageId] = [];
				}

				$langIndex = $columnList[$languageId];
				if (!isset($csvRow[$langIndex]))
				{
					$rowErrors[] = Loc::getMessage(
						'BX_TRANSLATE_IMPORT_ERR_ROW_LANG_ABSENT',
						['#LANG#' => $languageId]
					);
					continue;
				}
				if ($csvRow[$langIndex] === '')
				{
					continue;
				}

				$phrase = str_replace("\\\\", "\\", $csvRow[$langIndex]);

				if ($useTranslationRepository)
				{
					$encodingOut = Main\Localization\Translation::getSourceEncoding($languageId);
				}
				elseif ($isUtfMode)
				{
					$encodingOut = 'utf-8';
				}
				else
				{
					$encodingOut = Translate\Translation::getCultureEncoding($languageId);
					if (!$encodingOut)
					{
						$encodingOut = Main\Localization\Translation::getCurrentEncoding();
					}
				}

				if (
					$encodingIn !== '' &&
					$encodingOut !== '' &&
					$encodingIn !== $encodingOut
				)
				{
					$errorMessage = '';
					$phrase = Encoding::convertEncoding($phrase, $encodingIn, $encodingOut, $errorMessage);

					if (!$phrase && !empty($errorMessage))
					{
						$rowErrors[] = $errorMessage;
						continue;
					}
				}

				$checked = true;
				if ($encodingOut == 'utf-8')
				{
					$validPhrase = preg_replace("/[^\x01-\x7F]/",'', $phrase);// remove ASCII characters
					if ($validPhrase !== $phrase)
					{
						$checked = \Bitrix\Main\Text\Encoding::detectUtf8($phrase);
					}
					unset($validPhrase);
				}

				if ($checked)
				{
					$phraseList[$file][$languageId][$key] = $phrase;
				}
				else
				{
					$rowErrors[] = Loc::getMessage(
						'BX_TRANSLATE_IMPORT_ERR_NO_VALID_UTF8_PHRASE',
						['#LANG#' => $languageId]
					);
				}

				unset($checked, $phrase);
			}

			if (!empty($rowErrors))
			{
				$errors[] = Loc::getMessage(
					'TR_TOOLS_ERROR_LINE_FILE_BIG',
					[
						'#LINE#' => $csvRowCounter,
						'#FILENAME#' => $file,
						'#PHRASE#' => $key,
						'#ERROR#' => implode('; ', $rowErrors),
					]
				);
			}
			unset($rowErrors);
		}
		unset($csvRow);
	}
	$csvFile->close();
	unset($csvFile);

	foreach ($phraseList as $fileIndex => $translationList)
	{
		if (Translate\Path::isLangDir($fileIndex, true) !== true)
		{
			$errors[] = Loc::getMessage('TR_TOOLS_ERROR_FILE_NOT_LANG', array('%FILE%' => $fileIndex));
			continue;
		}
		foreach ($translationList as $languageId => $fileMessages)
		{
			if (empty($fileMessages))
			{
				continue;
			}

			$rawFile = Translate\Path::replaceLangId($fileIndex, $languageId);
			$file = Rel2Abs('/', $rawFile);
			if ($file !== $rawFile)
			{
				$errors[] = Loc::getMessage(
					'BX_TRANSLATE_IMPORT_ERR_BAD_FILEPATH',
					['#FILE#' => $fileIndex]
				);
				break;
			}

			$fullPath = Main\Application::getDocumentRoot(). $file;

			if (Main\Localization\Translation::useTranslationRepository())
			{
				if (in_array($languageId, Translate\Translation::getTranslationRepositoryLanguages()))
				{
					$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $languageId);
				}
				else
				{
					$fullPath = realpath($fullPath);
				}
			}
			else
			{
				$fullPath = realpath($fullPath);
			}

			$fullPath = Translate\Path::tidy($fullPath);

			$MESS = [];
			if (!$rewriteMode && file_exists($fullPath))
			{

				include $fullPath;

				if (!is_array($MESS))
				{
					$MESS = [];
				}
				else
				{
					foreach (array_keys($MESS) as $index)
					{
						if ($MESS[$index] === '')
						{
							unset($MESS[$index]);
						}
					}
					unset($index);
				}
			}

			if ($mergeMode)
			{
				$MESS = array_merge($MESS, $fileMessages);
			}
			else
			{
				$MESS = array_merge($fileMessages, $MESS);
			}

			if (!empty($MESS))
			{
				saveTranslationFile($file, $MESS, $errors);
			}
		}
	}

	return empty($errors);
}


/**
 * @param $path
 * @param bool $IS_LANG_DIR
 *
 * @global array $arTLangs
 * @global array $arFiles
 * @global array $arDirFiles
 * @global array $arLangDirFiles
 */
function GetTLangFiles($path, $IS_LANG_DIR = false)
{
	global $arTLangs, $arFiles, $arDirFiles, $arLangDirFiles;

	if (is_dir(Translate\Path::tidy($_SERVER["DOCUMENT_ROOT"]."/".$path."/")))
	{
		if ($IS_LANG_DIR)
		{
			if (is_array($arTLangs))
			{
				foreach ($arTLangs as $lng)
				{
					$path = Translate\Path::replaceLangId($path, $lng);
					$path_l = strlen($path);

					/** @global array $arFiles */
					/** @var array $arr */
					foreach($arFiles as $arr)
					{
						if($arr["IS_DIR"]=="N" && (strncmp($arr["PATH"], $path, $path_l) == 0))
						{
							$arDirFiles[] = $arr["PATH"];
						}
					}
				}
			}
		}
		else
		{
			if (is_array($arLangDirFiles))
			{
				$path_l = strlen($path);

				foreach ($arLangDirFiles as $arr)
				{
					if($arr["IS_DIR"]=="N" && (strncmp($arr["PATH"], $path, $path_l) == 0))
					{
						$arDirFiles[] = $arr["PATH"];
					}
				}
			}
		}
	}
	else
	{
		foreach ($arTLangs as $lng)
		{
			$arDirFiles[] = Translate\Path::replaceLangId($path, $lng);
		}
	}
}

/**
 * @param string $file Path
 * @param int $count Count of coincidences
 *
 * @return bool
 * @throws Main\IO\FileNotFoundException
 */
function TSEARCH($arFile, &$count)
{
	global $arSearchParam;

	$MESS = [];
	include $arFile['FULL_PATH'];


	if (empty($MESS))
	{
		return false;
	}

	$_phrase = $phrase = $arSearchParam['search'];
	if (!$arSearchParam['bCaseSens'])
	{
		$_phrase = strtolower($arSearchParam['search']);
	}
	$I_PCRE_MODIFIER = $arSearchParam['bCaseSens'] ? '' : 'i';

	$_bMessage = true;
	$_bMnemonic = false;
	$_arSearchData = array();
	if ($arSearchParam['bSearchMessage'] && $arSearchParam['bSearchMnemonic'])
	{
		$_bMessage = true;
		$_bMnemonic = true;
	}
	elseif ($arSearchParam['bSearchMnemonic'])
	{
		$_bMnemonic = true;
	}

	$_bResult = false;
	$count = 0;
	foreach ($MESS as $_sMn =>  $_sMe)
	{
		$__sMe = $_sMe;
		$__sMn = $_sMn;
		if (!$arSearchParam['bCaseSens'])
		{
			$__sMe = strtolower($_sMe);
			$__sMn = strtolower($_sMn);
		}

		$_bSearch = false;

		if ($_bMessage)
		{
			if (strpos($__sMe, $_phrase) !== false)
					$_bSearch = true;
		}
		if ($_bMnemonic)
		{
			if (strpos($__sMn, $_phrase) !== false)
				$_bSearch = true;
		}

		if ($_bSearch)
		{
			$_bResult = true;
			$res = array();
			//Replace
			if ($arSearchParam['is_replace'])
			{
				$pattern = '/'.preg_quote($phrase, '/').'/S'.$I_PCRE_MODIFIER.BX_UTF_PCRE_MODIFIER;

				TR_BACKUP($arFile['PATH']);
				if ($_bMessage)
				{
					preg_match_all($pattern, $_sMe, $res);
					$count += count($res[0]);
					$_sMe = preg_replace($pattern, $arSearchParam['replace'], $_sMe);
				}
				if ($_bMnemonic)
				{
					preg_match_all($pattern, $_sMn, $res);
					$count += count($res[0]);
					$_sMn = preg_replace($pattern, $arSearchParam['replace'], $_sMn);
				}
			}
			else
			{
				$pattern = '/'.preg_quote($phrase, '/').'/'.$I_PCRE_MODIFIER.BX_UTF_PCRE_MODIFIER;
				if ($_bMessage)
				{
					preg_match_all($pattern, $_sMe, $res);
					$count += count($res[0]);
				}
				if ($_bMnemonic)
				{
					preg_match_all($pattern, $_sMn, $res);
					$count += count($res[0]);
				}
			}
		}

		if ($arSearchParam['is_replace'])
		{
			$_arSearchData[$_sMn] = $_sMe;
		}
	}

	if ($arSearchParam['is_replace'] && $_bResult)
	{
		$errorCollection = [];
		saveTranslationFile($arFile['PATH'], $_arSearchData, $errorCollection);
		$_bResult = empty($errorCollection);
	}

	return $_bResult;
}

/**
 * @param $langFile
 *
 * @return bool
 */
function TR_BACKUP($langFile)
{
	if (Main\Config\Option::get('translate', 'BACKUP_FILES', 'N') !== 'Y')
	{
		return true;
	}

	$langId = Translate\Path::extractLangId($langFile);

	$fullPath = Translate\Path::tidy(Main\Application::getDocumentRoot(). $langFile);

	if (Main\Localization\Translation::useTranslationRepository())
	{
		if (in_array($langId, Translate\Translation::getTranslationRepositoryLanguages()))
		{
			$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $langId);
		}
		else
		{
			$fullPath = realpath($fullPath);
		}
	}
	else
	{
		$fullPath = realpath($fullPath);
	}

	$fullPath = Translate\Path::tidy($fullPath);
	if (!file_exists($fullPath))
	{
		return true;
	}

	$endpointBackupFolder = Main\Application::getDocumentRoot(). \Bitrix\Translate\BACKUP_PATH. '/'. dirname($langFile). '/';
	CheckDirPath($endpointBackupFolder);
	if (!file_exists($endpointBackupFolder) || !is_dir($endpointBackupFolder))
	{
		return false;
	}

	$sourceFilename = basename($langFile);
	$prefix = date('YmdHi');
	$endpointBackupFilename = $prefix. '_'. $sourceFilename;
	if (file_exists($endpointBackupFolder. $endpointBackupFilename))
	{
		$i = 1;
		while (file_exists($endpointBackupFolder. '/'. $endpointBackupFilename))
		{
			$i ++;
			$endpointBackupFilename = $prefix. '_'. $i. '_'. $sourceFilename;
		}
	}

	$isSuccessfull = (bool) @copy($fullPath, $endpointBackupFolder. '/'. $endpointBackupFilename);
	@chmod($endpointBackupFolder. '/'. $endpointBackupFilename, BX_FILE_PERMISSIONS);

	return $isSuccessfull;
}


/**
 * Class CTranslateEventHandlers
 */
class CTranslateEventHandlers
{
	public static function TranslatOnPanelCreate()
	{
		global $APPLICATION, $USER;

		if ($APPLICATION->GetGroupRight("translate") <= Translate\Permission::DENY)
			return;

		if (!$USER->IsAuthorized())
			return;

		$show_button = (string)Main\Config\Option::get('translate', 'BUTTON_LANG_FILES');

		if ($show_button == 'Y')
		{
			$cmd = 'Y';
			$checked = 'N';
			if (isset($_SESSION['SHOW_LANG_FILES']))
			{
				$cmd = $_SESSION['SHOW_LANG_FILES'] == 'Y' ? 'N' : 'Y';
				$checked = $_SESSION['SHOW_LANG_FILES'] == 'Y' ? 'Y' : 'N';
			}

			$url = $APPLICATION->GetCurPageParam("show_lang_files=".$cmd, array('show_lang_files'));
			$arMenu = array(
				array(
					"TEXT"=> Loc::getMessage("TRANSLATE_SHOW_LANG_FILES_TEXT"),
					"TITLE"=> Loc::getMessage("TRANSLATE_SHOW_LANG_FILES_TITLE"),
					"CHECKED"=>($checked == "Y"),
					"LINK"=>$url,
					"DEFAULT"=>false,
				));

			$APPLICATION->AddPanelButton(array(
				"HREF"=> '',
				"ID"=>"translate",
				"ICON" => "bx-panel-translate-icon",
				"ALT"=> Loc::getMessage('TRANSLATE_ICON_ALT'),
				"TEXT"=> Loc::getMessage('TRANSLATE_ICON_TEXT'),
				"MAIN_SORT"=>"1000",
				"SORT"=> 50,
				"MODE"=>array("configure"),
				"MENU" => $arMenu,
				"HINT" => array(
					'TITLE' => Loc::getMessage('TRANSLATE_ICON_TEXT'),
					'TEXT' => Loc::getMessage('TRANSLATE_ICON_HINT'),
				),
			));
		}
	}
}

class CTranslateUtils
{
	const LANGUAGES_DEFAULT = 0;
	const LANGUAGES_EXIST = 1;
	const LANGUAGES_ACTIVE = 2;
	const LANGUAGES_CUSTOM = 3;

	protected static $languageList = array("ru", "en", "de", "ua");

	public static function setLanguageList($languages = self::LANGUAGES_DEFAULT, $customList = array())
	{
		if ($languages == self::LANGUAGES_ACTIVE || $languages == self::LANGUAGES_EXIST)
		{
			self::$languageList = array();
			if ($languages == self::LANGUAGES_ACTIVE)
			{
				$languageIterator = LanguageTable::getList(array(
					'select' => array('ID'),
					'filter' => array('ACTIVE' => 'Y'),
				));
			}
			else
			{
				$languageIterator = LanguageTable::getList(array(
					'select' => array('ID'),
				));
			}
			while ($lang = $languageIterator->fetch())
			{
				self::$languageList[] = $lang['ID'];
			}
			unset($lang, $languageIterator);
		}
		elseif ($languages == self::LANGUAGES_CUSTOM)
		{
			if (!is_array($customList))
				$customList = array($customList);
			self::$languageList = $customList;
		}
		else
		{
			self::$languageList = array("ru", "en", "de", "ua");
		}

	}

	public static function CopyMessage($code, $fileFrom, $fileTo, $newCode = '')
	{
		$newCode = (string)$newCode;
		if ($newCode === '')
			$newCode = $code;
		$langDir = $fileName = "";
		$filePath = $fileFrom;
		while(($slashPos = strrpos($filePath, "/")) !== false)
		{
			$filePath = substr($filePath, 0, $slashPos);
			if(is_dir($filePath."/lang"))
			{
				$langDir = $filePath."/lang";
				$fileName = substr($fileFrom, $slashPos);
				break;
			}
		}
		if($langDir <> '')
		{
			$langDirTo = $fileNameTo = "";
			$filePath = $fileTo;
			while(($slashPos = strrpos($filePath, "/")) !== false)
			{
				$filePath = substr($filePath, 0, $slashPos);
				if(is_dir($filePath."/lang"))
				{
					$langDirTo = $filePath."/lang";
					$fileNameTo = substr($fileTo, $slashPos);
					break;
				}
			}

			if($langDirTo <> '')
			{
				$langs = self::$languageList;
				foreach($langs as $lang)
				{
					$MESS = array();
					if (file_exists($langDir."/".$lang.$fileName))
					{
						include($langDir."/".$lang.$fileName);
						if(isset($MESS[$code]))
						{
							$message = $MESS[$code];
							$MESS = array();
							if (file_exists($langDirTo."/".$lang.$fileNameTo))
							{
								include($langDirTo."/".$lang.$fileNameTo);
							}
							else
							{
								@mkdir(dirname($langDirTo."/".$lang.$fileNameTo), 0777, true);
							}
							$MESS[$newCode] = $message;
							$s = "<?\n";
							foreach($MESS as $c => $m)
							{
								$s .= "\$MESS[\"".EscapePHPString($c)."\"] = \"".EscapePHPString($m)."\";\n";
							}
							$s .= "?>";
							file_put_contents($langDirTo."/".$lang.$fileNameTo, $s);
						}
					}
				}
			}
		}
	}

	public static function FindAndCopy($sourceDir, $lang, $pattern, $destinationFile)
	{
		$insideLangDir = (strpos($sourceDir."/", "/lang/".$lang."/") !== false);

		foreach(scandir($sourceDir) as $file)
		{
			if($file == "." || $file == "..")
			{
				continue;
			}

			if($file == ".description.php" || $file == ".parameters.php")
			{
				continue;
			}

			if($sourceDir."/".$file == $destinationFile)
			{
				continue;
			}

			if(is_dir($sourceDir."/".$file))
			{
				self::FindAndCopy($sourceDir."/".$file, $lang, $pattern, $destinationFile);
			}
			elseif($insideLangDir)
			{
				$MESS = array();
				include($sourceDir."/".$file);

				$copyMess = array();
				foreach($MESS as $code => $val)
				{
					if(preg_match($pattern, $val))
					{
						$copyMess[$code] = $val;
					}
				}

				if(!empty($copyMess))
				{
					foreach(self::$languageList as $destLang)
					{
						if($destLang <> $lang)
						{
							$MESS = array();
							$sourceFile = str_replace("/lang/".$lang."/", "/lang/".$destLang."/", $sourceDir."/".$file);
							if(file_exists($sourceFile))
							{
								include($sourceFile);
							}

							$destMess = array();
							foreach($MESS as $code => $val)
							{
								if(isset($copyMess[$code]))
								{
									$destMess[$code] = $val;
								}
							}
							$destFile = str_replace("/lang/".$lang."/", "/lang/".$destLang."/", $destinationFile);
						}
						else
						{
							$destMess = $copyMess;
							$destFile = $destinationFile;
						}

						$MESS = array();
						if(file_exists($destFile))
						{
							include($destFile);
						}
						else
						{
							@mkdir(dirname($destFile), 0777, true);
						}

						foreach($destMess as $code => $val)
						{
							if(isset($MESS[$code]) && $MESS[$code] <> $val)
							{
								echo $sourceDir."/".$file.": ".$code." already exists in the destination file.\n";
							}
							else
							{
								$MESS[$code] = $val;
							}
						}

						$s = "<?\n";
						foreach($MESS as $c => $m)
						{
							$s .= "\$MESS[\"".EscapePHPString($c)."\"] = \"".EscapePHPString($m)."\";\n";
						}
						$s .= "?>";
						file_put_contents($destFile, $s);
					}
				}
			}
		}
	}
}


/**
 * @param array $arCommon
 * @param string $path
 * @param string $key
 * @param array $enabledLanguages
 *
 * @global array $arCommonCounter
 * @global int $Counter
 *
 * @return void
 */
//function GetPhraseCounters($arCommon, $path, $key, $enabledLanguages)
function GetPhraseCounters($arCommon, $entry, $enabledLanguages)
{
	global $arCommonCounter, $Counter;
	$Counter++;

	$path = $entry["PATH"];
	$key = Translate\Path::removeLangId($path, $enabledLanguages);


	$arDirFiles = array();

	// is directory
	//if (is_dir(Translate\Path::tidy($_SERVER["DOCUMENT_ROOT"]."/".$path."/")))
	if ($entry['IS_DIR'] === 'Y')
	{
		if (\Bitrix\Translate\Path::isLangDir($path))
		{
			// files array for directory language
			foreach ($enabledLanguages as $lng)
			{
				$path = \Bitrix\Translate\Path::replaceLangId($path, $lng);
				$pathLength = strlen($path);

				foreach($arCommon as $arr)
				{
					if($arr['IS_DIR'] == 'N' && (strncmp($arr['PATH'], $path, $pathLength) == 0))
					{
						$arDirFiles[$arr['PATH']] = $arr['FULL_PATH'];
					}
				}
			}
		}
		else
		{
			if (is_array($arCommon))
			{
				$pathLength = strlen($path);
				// array files for directory
				foreach ($arCommon as $arr)
				{
					if($arr['IS_DIR']=='N' && (strncmp($arr['PATH'], $path, $pathLength) == 0))
					{
						$arDirFiles[$arr['PATH']] = $arr["FULL_PATH"];
					}
				}
			}
		}
	}
	else
	{
		foreach ($enabledLanguages as $lng)
		{
			//$arDirFiles[] = \Bitrix\Translate\Path::replaceLangId($path, $lng);
			$path = \Bitrix\Translate\Path::replaceLangId($path, $lng);
			$pathLength = strlen($path);

			foreach($arCommon as $arr)
			{
				if($arr['IS_DIR'] == 'N' && (strncmp($arr['PATH'], $path, $pathLength) == 0))
				{
					$arDirFiles[$arr['PATH']] = $arr['FULL_PATH'];
				}
			}
		}
	}

	$arFilesLng = array();
	// array for every files
	foreach ($arDirFiles as $fpath => $file)
	{
		//if(file_exists($_SERVER["DOCUMENT_ROOT"].$file) && preg_match("#/lang/([^/]*?)/#", $file, $arMatch))
		if(file_exists($file) && preg_match("#/lang/([^/]*?)/#", $file, $arMatch))
		{
			$file_lang = $arMatch[1];
			if(in_array($file_lang, $enabledLanguages))
			{
				if(substr($file, -4) != '.php')
				{
					continue;
				}
				$MESS = array();
				include $file;
				$fileName = \Bitrix\Translate\Path::removeLangId($fpath, $enabledLanguages);
				$arFilesLng[$fileName][$file_lang] = array_keys($MESS);
			}
		}
	}

	$arFilesLngCounter = array();
	// rashogdenia for files
	foreach($arFilesLng as $fileName => $arLns)
	{
		$total_arr = array();

		// summarize
		foreach($arLns as $ln => $arLn)
		{
			$total_arr = array_merge($total_arr, $arLn);
		}
		$total_arr = array_unique($total_arr);
		$total = count($total_arr);

		foreach($enabledLanguages as $lang)
		{
			$arr = array();
			$arLn = is_array($arLns[$lang]) ? $arLns[$lang] : array();
			$diff = array_diff($total_arr, $arLn);
			$arr["TOTAL"] = $total;
			$arr["DIFF"] = count($diff);
			$arFilesLngCounter[$fileName][$lang] = $arr;
		}
	}

	foreach($arFilesLngCounter as $fileName => $arCount)
	{
		foreach($arCount as $ln => $arLn)
		{
			$file_path = str_replace("/lang/", "/lang/".$ln."/", $fileName);
			$arCommonCounter[$key][$ln][$file_path]["TOTAL"] += $arLn["TOTAL"];
			$arCommonCounter[$key][$ln][$file_path]["DIFF"] += $arLn["DIFF"];
		}
	}
}

/**
 * @param string $masterLanguage
 * @param array $listIds
 * @param array $errorCollection
 *
 * @return bool
 */
function removePhrasesByMasterFile($masterLanguage, $listIds, &$errorCollection)
{
	/** @global array $arFiles */
	global $arFiles;

	if (empty($arFiles))
	{
		return false;
	}

	$currentFileList = [];
	$currentFileListAbs = [];
	$masterFileList = [];
	foreach ($arFiles as $row)
	{
		if (!$row['IS_LANG'])
		{
			continue;
		}
		$file = $row['FILE'];
		if (!isset($currentFileList[$file]))
		{
			$currentFileList[$file] = [];
			$currentFileListAbs[$file] = [];
		}
		$currentFileList[$file][$row['LANG']] = $row['PATH'];
		$currentFileListAbs[$file][$row['LANG']] = $row['FULL_PATH'];
		if ($row['LANG'] == $masterLanguage)
		{
			$masterFileList[$file] = true;
		}
	}
	unset($file, $row);

	if (empty($masterFileList))
	{
		return false;
	}

	foreach ($listIds as $file)
	{
		$masterFileIsFounded = false;
		$masterMask = [];
		if (isset($masterFileList[$file]))
		{
			$MESS = [];
			if (file_exists($currentFileListAbs[$file][$masterLanguage]))
			{
				$masterFileIsFounded = true;
				/** @noinspection PhpIncludeInspection */
				include($currentFileListAbs[$file][$masterLanguage]);
			}
			if (!empty($MESS))
			{
				$masterMask = array_fill_keys(array_keys($MESS), true);
			}
		}
		if (!$masterFileIsFounded || empty($masterMask))
		{
			continue;
		}

		$newList = [];
		$newListAbs = [];
		foreach ($currentFileList[$file] as $phraseLanguage => $phrasePath)
		{
			if ($phraseLanguage == $masterLanguage)
			{
				continue;
			}

			$MESS = [];
			$phrasePathAbs = $currentFileListAbs[$file][$phraseLanguage];
			if (file_exists($phrasePathAbs))
			{
				/** @noinspection PhpIncludeInspection */
				include($phrasePathAbs);
			}
			if (!empty($MESS))
			{
				$MESS = array_intersect_key($MESS, $masterMask);
			}

			$newList[$phrasePath] = $MESS;
			$newListAbs[$phrasePath] = $phrasePathAbs;
		}
		unset($phrasePath, $phrasePathAbs);

		if (empty($newList))
		{
			continue;
		}

		foreach ($newList as $langFileName => $phrases)
		{
			saveTranslationFile($langFileName, $phrases, $errorCollection);
		}
		unset($langFileName, $phrases, $newList);
	}

	return true;
}


/**
 * @param string $langFileName
 * @param array $phrases
 * @param array $errorCollection
 *
 * @return bool
 * @throws Main\IO\FileNotFoundException
 */
function saveTranslationFile($langFileName, $phrases, &$errorCollection)
{
	$langId = Translate\Path::extractLangId($langFileName);

	// sort phrases by key, except russian
	if ($langId != 'ru')
	{
		ksort($phrases, SORT_STRING);
	}

	$content = '';
	foreach ($phrases as $phraseId => $phrase)
	{
		$phrase = str_replace(["\n\r", "\r"], ["\n", ''], $phrase);
		$row = "\$MESS['". EscapePHPString($phraseId). "'] = \"". EscapePHPString($phrase). "\"";
		$content .= "\n". $row. ';';
	}
	unset($phraseId, $phrase, $row);

	if (!TR_BACKUP($langFileName))
	{
		$errorCollection[] = Loc::getMessage('TR_CREATE_BACKUP_ERROR', array('%FILE%' => $langFileName));
	}
	else
	{
		$fullPath = Translate\Path::tidy(Main\Application::getDocumentRoot(). $langFileName);
		if (Main\Localization\Translation::useTranslationRepository())
		{
			if (in_array($langId, Translate\Translation::getTranslationRepositoryLanguages()))
			{
				$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $langId);
			}
		}

		$checkFullPath = realpath($fullPath);
		//if ($checkFullPath === false)


		$file = new Translate\File(Translate\Path::tidy($fullPath));

		if (strlen($content) > 0)
		{
			if ($file->putContents('<?'. $content. "\n?". '>') === false)
			{
				$errorCollection[] = Loc::getMessage('TR_TOOLS_ERROR_WRITE_FILE', array('%FILE%' => $langFileName));
			}
		}
		else
		{
			if ($file->isExists())
			{
				$file->markWritable();
				$file->delete();
			}
		}
	}

	return true;
}