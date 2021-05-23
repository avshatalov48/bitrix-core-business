<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;

/**
 * Harvester of the lang files disposition.
 *
 * @method array run(string $path, bool $runBefore)
 *
 */
abstract class ExportAction
	extends Translate\Controller\Action
{

	/** @var string */
	protected static $documentRoot;

	/** @var bool */
	protected static $useTranslationRepository;
	/** @var string[] */
	protected static $translationRepositoryLanguages;

	/** @var int */
	protected $tabId;

	/** @var string */
	protected $exportFilePath;
	/** @var string */
	protected $exportFileName;
	/** @var string */
	protected $exportFileType = 'application/csv';
	/** @var int */
	protected $exportFileSize = 0;

	/** @var bool */
	protected $convertEncoding;

	/** @var string */
	protected $encodingOut;

	/** @var bool */
	protected $collectUntranslated;

	/** @var string[] */
	protected $languages;

	/** @var int */
	protected $exportedPhraseCount = 0;

	/** @var Translate\Filter */
	protected $filter;


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		Loc::loadLanguageFile(__FILE__);

		if ($this instanceof Translate\Controller\IProcessParameters)
		{
			$this->keepField([
				'tabId', 'exportFileName', 'exportFilePath', 'exportFileSize', 'exportedPhraseCount',
				'collectUntranslated', 'convertEncoding', 'encodingOut', 'languages'
			]);
		}

		foreach (['collectUntranslated', 'convertEncoding', 'encodingOut', 'languages', 'filter'] as $key)
		{
			if (isset($config[$key]))
			{
				if ($key == 'filter')
				{
					if (!$config[$key] instanceof Translate\Filter)
					{
						continue;
					}
				}
				$this->{$key} = $config[$key];
			}
		}

		self::$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		self::$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();
		if (self::$useTranslationRepository)
		{
			self::$translationRepositoryLanguages = Translate\Config::getTranslationRepositoryLanguages();
		}

		if ($this->languages === 'all' || empty($this->languages))
		{
			$this->languages = Translate\Config::getEnabledLanguages();
		}

		parent::__construct($name, $controller, $config);
	}


	/**
	 * Creates temporary file for writing data.
	 *
	 * @param string $exportFileName
	 * @return Translate\IO\CsvFile
	 */
	protected function createExportTempFile(string $exportFileName)
	{
		/** @var Translate\IO\CsvFile $csvFile */
		$exportFolder = Translate\Config::getExportFolder();
		if (!empty($exportFolder))
		{
			$tempDir = new Translate\IO\Directory($exportFolder);
			if ($tempDir->isExists())
			{
				$tempDir->wipe(function(Main\IO\FileSystemEntry $entry){
					// clear .csv files older than 3 hours
					return (
						$entry->isFile() &&
						preg_match("#.+_([0-9]+)\.csv$#", $entry->getName(), $matches) &&
						(time() - (int)$matches[1] > 3 * 3600)
					);
				});
			}
			else
			{
				$tempDir->create();
			}

			$fileName = preg_replace("#(.+)\.csv$#", "$1_".time().'.csv',  $exportFileName);

			$csvFile = new Translate\IO\CsvFile($tempDir->getPhysicalPath() .'/'. $fileName);
		}
		else
		{
			$csvFile = Translate\IO\CsvFile::generateTemporalFile('translate', '.csv', 3);
		}

		$this->configureExportCsvFile($csvFile);

		$csvFile->openWrite();

		$row = array('file', 'key');
		foreach ($this->languages as $langId)
		{
			$row[] = $langId;
		}
		$csvFile->put($row);
		$csvFile->close();

		$this->exportFilePath = $csvFile->getPhysicalPath();
		$this->exportFileSize = $csvFile->getSize();

		return $csvFile;
	}

	/**
	 * Apply module configuration to exporting csv file.
	 *
	 * @param Translate\IO\CsvFile $csvFile Object to configure.
	 *
	 * @return void
	 */
	protected function configureExportCsvFile(Translate\IO\CsvFile $csvFile)
	{
		$csvFile
			->setRowDelimiter(Translate\IO\CsvFile::LINE_DELIMITER_WIN)
			->prefaceWithUtf8Bom($this->encodingOut === 'utf-8')
		;

		switch (Translate\Config::getOption(Translate\Config::OPTION_EXPORT_CSV_DELIMITER))
		{
			case 'TAB':
				$csvFile->setFieldDelimiter(Translate\IO\CsvFile::DELIMITER_TAB);
				break;
			case 'ZPT':
				$csvFile->setFieldDelimiter(Translate\IO\CsvFile::DELIMITER_ZPT);
				break;
			case 'TZP':
			default:
				$csvFile->setFieldDelimiter(Translate\IO\CsvFile::DELIMITER_TZP);
		}
	}

	/**
	 * Generate name for exporting file.
	 *
	 * @param string $path Exporting path.
	 * @param string[] $languages List of exporting languages.
	 *
	 * @return string
	 */
	protected function generateExportFileName($path, $languages)
	{
		return trim(str_replace(['.php', '/'], ['', '_'], $path), '_').'_'.implode('_', $languages).'.csv';
	}

	/**
	 * Returns exported file properties.
	 *
	 * @return array
	 */
	public function getDownloadingParameters()
	{
		return array(
			'fileName' => $this->exportFileName,
			'filePath' => $this->exportFilePath,
			'fileType' => $this->exportFileType,
			'fileSize' => $this->exportFileSize,
		);
	}

	/**
	 * Merges all language files into one array.
	 *
	 * @param string $langFilePath Relative project path of the language file.
	 * @param string[] $fullLangFilePaths Array of full paths to lang files.
	 * @param bool $collectUntranslated Collect only untranslated phrases.
	 * @param string[] $filterByCodeList Array of prase codes to filter.
	 *
	 * @return array
	 */
	protected function mergeLangFiles($langFilePath, $fullLangFilePaths, $collectUntranslated = false, $filterByCodeList = [])
	{
		$mergedContent = array();

		$rowLang0 = array();
		foreach ($this->languages as $langId)
		{
			$rowLang0[$langId] = '';
		}

		$filterByCode = is_array($filterByCodeList) && (count($filterByCodeList) > 0);

		foreach ($this->languages as $langId)
		{
			if (empty($fullLangFilePaths[$langId]))
			{
				continue;
			}

			$fullPath = $fullLangFilePaths[$langId];
			$file = new Translate\File($fullPath);
			$file->setLangId($langId);

			if ($this->convertEncoding)
			{
				$file->setOperatingEncoding($this->encodingOut);
			}
			else
			{
				$file->setOperatingEncoding(Main\Localization\Translation::getSourceEncoding($langId));
			}

			if (!$file->loadTokens())
			{
				if (!$file->load())
				{
					continue;
				}
			}

			foreach ($file as $code => $phrase)
			{
				if ($filterByCode)
				{
					if (!in_array($code, $filterByCodeList))
					{
						continue;
					}
				}
				if (!isset($mergedContent[$code]))
				{
					$mergedContent[$code] = array_merge(array('file' => $langFilePath, 'key' => $code), $rowLang0);
				}
				$mergedContent[$code][$langId] = $phrase;
			}
		}

		if ($collectUntranslated)
		{
			// settings
			$hasObligatorySetting = false;
			if ($settingsFile = Translate\Settings::instantiateByPath(self::$documentRoot. '/'. $langFilePath))
			{
				if ($settingsFile->load())
				{
					$langSettings = $settingsFile->getOptions($langFilePath);
					$hasObligatorySetting = !empty($langSettings[Translate\Settings::OPTION_LANGUAGES]);
				}
			}

			foreach ($mergedContent as $code => $row)
			{
				foreach ($row as $langId => $phr)
				{
					if ($langId == 'file' || $langId == 'key')
					{
						continue;
					}
					$isObligatory = true;
					if ($hasObligatorySetting)
					{
						$isObligatory = in_array($langId, $langSettings[Translate\Settings::OPTION_LANGUAGES]);
					}
					if (empty($phr) && ($phr !== '0') && $isObligatory)
					{
						continue 2;
					}
				}
				unset($mergedContent[$code]);
			}
		}

		return $mergedContent;
	}


	/**
	 * Runs through lang folder and collects full path to lang files.
	 *
	 * @param string $langPath Relative project path of the language folder.
	 *
	 * @return \Generator|array
	 */
	protected function lookThroughLangFolder($langPath)
	{
		$files = [];
		$folders = [];

		foreach ($this->languages as $langId)
		{
			$langFolderRelPath = Translate\IO\Path::replaceLangId($langPath, $langId);
			$langFolderFullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$langFolderRelPath);

			if (self::$useTranslationRepository && in_array($langId, self::$translationRepositoryLanguages))
			{
				$langFolderFullPath = Main\Localization\Translation::convertLangPath($langFolderFullPath, $langId);
			}

			$childrenList = Translate\IO\FileSystemHelper::getFileList($langFolderFullPath);
			if (!empty($childrenList))
			{
				foreach ($childrenList as $fullPath)
				{
					$name = basename($fullPath);
					if (in_array($name, Translate\IGNORE_FS_NAMES))
					{
						continue;
					}

					if ((mb_substr($name, -4) === '.php') && is_file($fullPath))
					{
						$files[$langPath.'/'.$name][$langId] = $fullPath;
					}
				}
			}

			// dir only
			$childrenList = Translate\IO\FileSystemHelper::getFolderList($langFolderFullPath);
			if (!empty($childrenList))
			{
				$ignoreDev = implode('|', Translate\IGNORE_MODULE_NAMES);
				foreach ($childrenList as $fullPath)
				{
					$name = basename($fullPath);
					if (in_array($name, Translate\IGNORE_FS_NAMES))
					{
						continue;
					}

					$relPath = $langFolderRelPath.'/'.$name;

					if (!is_dir($fullPath))
					{
						continue;
					}

					if (in_array($relPath, Translate\IGNORE_BX_NAMES))
					{
						continue;
					}

					// /bitrix/modules/[smth]/dev/
					if (preg_match("#^bitrix/modules/[^/]+/({$ignoreDev})$#", trim($relPath, '/')))
					{
						continue;
					}

					if (in_array($name, Translate\IGNORE_LANG_NAMES))
					{
						continue;
					}

					$folders[$langPath.'/'.$name] = $langPath.'/'.$name;
				}
			}
		}

		if (count($files) > 0)
		{
			yield $files;
		}

		if (count($folders) > 0)
		{
			foreach ($folders as $subFolderPath)
			{
				foreach ($this->lookThroughLangFolder($subFolderPath) as $subFiles)// go deeper
				{
					yield $subFiles;
				}
			}
		}
	}
}