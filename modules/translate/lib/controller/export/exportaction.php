<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate\Index;

/**
 * Harvester of the lang files disposition.
 *
 * @method array run(string $path, bool $runBefore)
 *
 */
abstract class ExportAction extends Translate\Controller\Action
{
	protected static string $documentRoot = '';

	protected static bool $useTranslationRepository = false;
	/** @var string[] */
	protected static array $translationRepositoryLanguages = [];

	protected int $tabId = 0;

	protected string $exportFilePath = '';
	protected string $exportFileName = '';
	protected string $exportFileType = 'application/csv';
	protected int $exportFileSize = 0;

	protected bool $convertEncoding;

	protected string $encodingOut;

	protected bool $collectUntranslated;

	/* Look for translation samples */
	protected bool $appendSamples = false;
	protected int $samplesCount = 10;
	protected array $samplesRestriction = [];
	protected string $samplesFilePath = '';
	protected string $samplesFileName = '';
	protected int $samplesFileSize = 0;

	/* Don't look for samples for a long text */
	protected int $maxSampleSourceLength = 500;

	/** @var string[] */
	protected array $languages = [];

	protected int $exportedPhraseCount = 0;

	protected int $exportedSamplesCount = 0;

	protected Translate\Filter $filter;

	protected array $fullPathCache = [];


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, array $config = [])
	{
		$this->filter = new Translate\Filter();

		Loc::loadLanguageFile(__FILE__);

		if ($this instanceof Translate\Controller\IProcessParameters)
		{
			$this->keepField([
				'tabId',
				'exportFileName',
				'exportFilePath',
				'exportFileSize',
				'exportedPhraseCount',
				'collectUntranslated',
				'appendSamples',
				'samplesCount',
				'samplesRestriction',
				'samplesFileName',
				'samplesFilePath',
				'samplesFileSize',
				'exportedSamplesCount',
				'convertEncoding',
				'encodingOut',
				'languages'
			]);
		}

		$fields = [
			'collectUntranslated',
			'appendSamples',
			'samplesCount',
			'samplesRestriction',
			'convertEncoding',
			'encodingOut',
			'languages',
			'filter'
		];
		foreach ($fields as $key)
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

		self::$documentRoot = \rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		self::$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();
		if (self::$useTranslationRepository)
		{
			self::$translationRepositoryLanguages = Translate\Config::getTranslationRepositoryLanguages();
		}

		if (in_array('all', $this->languages) || empty($this->languages))
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
	protected function createExportTempFile(string $exportFileName): Translate\IO\CsvFile
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
						\preg_match("#.+_([0-9]+)\.csv$#", $entry->getName(), $matches) &&
						(\time() - (int)$matches[1] > 3 * 3600)
					);
				});
			}
			else
			{
				$tempDir->create();
			}

			$fileName = \preg_replace("#(.+)\.csv$#", "$1_".\time().'.csv',  $exportFileName);

			$csvFile = new Translate\IO\CsvFile($tempDir->getPhysicalPath() .'/'. $fileName);
		}
		else
		{
			$csvFile = Translate\IO\CsvFile::generateTemporalFile('translate', '.csv', 3);
		}

		$this->configureExportCsvFile($csvFile);

		$csvFile->openWrite();

		$row = ['file', 'key'];
		foreach ($this->languages as $langId)
		{
			$row[] = $langId;
		}
		$csvFile->put($row);
		$csvFile->close();

		return $csvFile;
	}

	/**
	 * Apply module configuration to exporting csv file.
	 *
	 * @param Translate\IO\CsvFile $csvFile Object to configure.
	 *
	 * @return void
	 */
	protected function configureExportCsvFile(Translate\IO\CsvFile $csvFile): void
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
	protected function generateExportFileName(string $path, array $languages): string
	{
		return \trim(\str_replace(['.php', '/'], ['', '_'], $path), '_').'_'.\implode('_', $languages).'.csv';
	}

	/**
	 * Returns exported file properties.
	 *
	 * @return array
	 */
	public function getDownloadingParameters(): array
	{
		return [
			'fileName' => $this->exportFileName,
			'filePath' => $this->exportFilePath,
			'fileType' => $this->exportFileType,
			'fileSize' => $this->exportFileSize,
		];
	}

	/**
	 * Returns exported file properties.
	 *
	 * @return array
	 */
	public function getDownloadingSamplesParameters(): array
	{
		return [
			'fileName' => $this->samplesFileName,
			'filePath' => $this->samplesFilePath,
			'fileType' => $this->exportFileType,
			'fileSize' => $this->samplesFileSize,
		];
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
	public function mergeLangFiles(
		string $langFilePath,
		array $fullLangFilePaths,
		bool $collectUntranslated = false,
		array $filterByCodeList = []
	): array
	{
		$mergedContent = [];

		$rowLang0 = [];
		foreach ($this->languages as $langId)
		{
			$rowLang0[$langId] = '';
		}

		$filterByCode = !empty($filterByCodeList);

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
					if (!\in_array($code, $filterByCodeList))
					{
						continue;
					}
				}
				if (!isset($mergedContent[$code]))
				{
					$mergedContent[$code] = \array_merge(['file' => $langFilePath, 'key' => $code], $rowLang0);
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
						$isObligatory = \in_array($langId, $langSettings[Translate\Settings::OPTION_LANGUAGES]);
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
	 * @return \Generator|array|iterable
	 */
	public function lookThroughLangFolder(string $langPath): iterable
	{
		$files = [];
		$folders = [];

		foreach ($this->languages as $langId)
		{
			$langFolderRelPath = Translate\IO\Path::replaceLangId($langPath, $langId);
			$langFolderFullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$langFolderRelPath);

			if (self::$useTranslationRepository && \in_array($langId, self::$translationRepositoryLanguages))
			{
				$langFolderFullPath = Main\Localization\Translation::convertLangPath($langFolderFullPath, $langId);
			}

			$childrenList = Translate\IO\FileSystemHelper::getFileList($langFolderFullPath);
			if (!empty($childrenList))
			{
				foreach ($childrenList as $fullPath)
				{
					$name = \basename($fullPath);
					if (\in_array($name, Translate\IGNORE_FS_NAMES))
					{
						continue;
					}

					if (Translate\IO\Path::isPhpFile($fullPath, true))
					{
						$files[$langPath.'/'.$name][$langId] = $fullPath;
					}
				}
			}

			// dir only
			$childrenList = Translate\IO\FileSystemHelper::getFolderList($langFolderFullPath);
			if (!empty($childrenList))
			{
				$ignoreDev = \implode('|', Translate\IGNORE_MODULE_NAMES);
				foreach ($childrenList as $fullPath)
				{
					$name = \basename($fullPath);
					if (\in_array($name, Translate\IGNORE_FS_NAMES))
					{
						continue;
					}

					$relPath = $langFolderRelPath.'/'.$name;

					if (!\is_dir($fullPath))
					{
						continue;
					}

					if (\in_array($relPath, Translate\IGNORE_BX_NAMES))
					{
						continue;
					}

					// /bitrix/modules/[smth]/dev/
					if (\preg_match("#^bitrix/modules/[^/]+/({$ignoreDev})$#", \trim($relPath, '/')))
					{
						continue;
					}

					if (\in_array($name, Translate\IGNORE_LANG_NAMES))
					{
						continue;
					}

					$folders[$langPath.'/'.$name] = $langPath.'/'.$name;
				}
			}
		}

		if (\count($files) > 0)
		{
			yield $files;
		}

		if (\count($folders) > 0)
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

	/**
	 * Looks for exact translation sample of the phrase.
	 *
	 * @param string $searchPhrase Phrase to look for.
	 * @param string $searchLangId Phrase lang to look for.
	 * @param int|string $stripPath Strip current file.
	 * @param int $limit Limit search result.
	 * @param int[] $restrictByPathId
	 * @return array
	 */
	public function findSamples(string $searchPhrase, string $searchLangId, $stripPath, int $limit = 50, array $restrictByPathId = []): array
	{
		$select = [
			'PATH_ID' => 'PATH_ID',
			'PHRASE_CODE' => 'CODE',
			'FILE_PATH' => 'PATH.PATH',
		];

		$phraseFilter = [];

		if (!Index\PhraseIndexSearch::disallowFtsIndex($searchLangId))
		{
			$minLengthFulltextWorld = Index\PhraseIndexSearch::getFullTextMinLength();
			$fulltextIndexSearchStr = Index\PhraseIndexSearch::prepareTextForFulltextSearch($searchPhrase);
			if (\mb_strlen($fulltextIndexSearchStr) > $minLengthFulltextWorld)
			{
				$phraseFilter['*=PHRASE'] = $fulltextIndexSearchStr;
			}
		}

		$phraseFilter['=PHRASE'] = $searchPhrase;
		if (is_numeric($stripPath))
		{
			$phraseFilter['!=PATH_ID'] = $stripPath;
		}
		else
		{
			$phraseFilter['!=PATH.PATH'] = $stripPath;
		}

		if (!empty($restrictByPathId))
		{
			$phraseFilter['=PATH.DESCENDANTS.PARENT_ID'] = $restrictByPathId; //ancestor
		}

		$ftsClass = Index\Internals\PhraseFts::getFtsEntityClass($searchLangId);

		/** @var Main\ORM\Query\Result $cachePathRes */
		$phraseInxRes = $ftsClass::getList([
			'filter' => $phraseFilter,
			'select' => $select,
			'limit' => $limit,
		]);

		$samples = [];
		$fileInxCache = [];
		while ($phraseInx = $phraseInxRes->fetch())
		{
			$pathId = (int)$phraseInx['PATH_ID'];
			$phraseCode = $phraseInx['PHRASE_CODE'];

			if (!isset($fileInxCache[$pathId]))
			{
				$fullPaths = $this->getFullPath($pathId);
				$fileInxCache[$pathId] = $this->mergeLangFiles($phraseInx['FILE_PATH'], $fullPaths);
			}

			if (
				isset($fileInxCache[$pathId][$phraseCode])
				&& $fileInxCache[$pathId][$phraseCode][$searchLangId] == $searchPhrase
			)
			{
				$samples[] = $fileInxCache[$pathId][$phraseCode];
			}
		}

		return $samples;
	}

	/**
	 * Returns list of full paths for lang path.
	 * @param int $pathId
	 * @return array
	 */
	protected function getFullPath(int $pathId): array
	{
		if (!isset($this->fullPathCache[$pathId]))
		{
			$this->fullPathCache[$pathId] = [];
			$fileInxRes = Translate\Index\Internals\FileIndexTable::getList([
				'filter' => ['=PATH_ID' => $pathId],
				'order' => ['ID' => 'ASC'],
				'select' => ['LANG_ID', 'FULL_PATH'],
			]);
			while ($fileInx = $fileInxRes->fetch())
			{
				$this->fullPathCache[$pathId][$fileInx['LANG_ID']] = $fileInx['FULL_PATH'];
			}
		}

		return $this->fullPathCache[$pathId];
	}
}