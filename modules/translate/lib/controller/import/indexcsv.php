<?php
namespace Bitrix\Translate\Controller\Import;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;
use Bitrix\Translate\Index;


/**
 * Index paths from csv file.
 */
class IndexCsv
	extends Translate\Controller\Action
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var int */
	private $seekLine;

	/** @var string */
	private $seekPath;

	/** @var string */
	protected static $documentRoot;
	/** @var string[] */
	protected static $enabledLanguages;

	/** @var int Session tab counter. */
	private $tabId = 0;

	/** @var string */
	private $csvFilePath;

	/** @var Translate\IO\CsvFile */
	private $csvFile;

	/** @var string[] */
	private $languageList;

	/** @var string[] */
	private $columnList;


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		$fields = ['tabId', 'csvFilePath', 'seekLine', 'seekPath'];

		$this->keepField($fields);

		foreach ($fields as $key)
		{
			if (!empty($config[$key]))
			{
				$this->{$key} = $config[$key];
			}
		}

		self::$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		self::$enabledLanguages = Translate\Config::getEnabledLanguages();

		parent::__construct($name, $controller, $config);
	}

	/**
	 * Runs controller action.
	 *
	 * @param boolean $runBefore Flag to run onBeforeRun event handler.
	 *
	 * @return array
	 */
	public function run($runBefore = false)
	{
		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		$this->csvFile = new Translate\IO\CsvFile($this->csvFilePath);

		$this->csvFile
			->setFieldsType(Translate\IO\CsvFile::FIELDS_TYPE_WITH_DELIMITER)
			->setFirstHeader(false)
		;

		if (!$this->csvFile->openLoad())
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_EMPTY_FILE_ERROR')));

			return array(
				'STATUS' => Translate\Controller\STATUS_COMPLETED
			);
		}
		if (!$this->verifyCsvFile())
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_FILE_ERROR')));
			return array(
				'STATUS' => Translate\Controller\STATUS_COMPLETED
			);
		}

		if ($this->isNewProcess)
		{
			$this->clearProgressParameters();

			$this->totalItems = 0;
			$uniquePaths = array();
			$fileColumn = $this->columnList['file'];
			while ($csvRow = $this->csvFile->fetch())
			{
				$filePath = (isset($csvRow[$fileColumn]) ? $csvRow[$fileColumn] : '');
				if ($filePath == '')
				{
					continue;
				}
				if (isset($uniquePaths[$filePath]))
				{
					continue;
				}
				$uniquePaths[$filePath] = 1;
				$this->totalItems ++;
			}
			$this->csvFile->moveFirst();

			$this->processedItems = 0;
			$this->seekLine = 0;

			$this->saveProgressParameters();

			$this->isNewProcess = false;
		}
		else
		{
			$progressParams = $this->getProgressParameters();

			if (isset($progressParams['totalItems']))
			{
				$this->totalItems = $progressParams['totalItems'];
			}
			if (isset($progressParams['seekLine']))
			{
				$this->seekLine = $progressParams['seekLine'];
			}
			if (isset($progressParams['seekPath']))
			{
				$this->seekPath = $progressParams['seekPath'];
			}
		}

		return $this->performStep('runIndexing');
	}

	/**
	 * Imports data from csv file.
	 *
	 * @return array
	 */
	private function runIndexing()
	{
		$fileColumn = $this->columnList['file'];

		$uniquePaths = array();

		if (!empty($this->seekPath))
		{
			$uniquePaths[$this->seekPath] = 1;
		}

		$pathIndexer = new Index\PathIndexCollection();

		$currentLine = 0;
		while ($csvRow = $this->csvFile->fetch())
		{
			$currentLine ++;

			if ($this->seekLine > 0)
			{
				if ($currentLine <= $this->seekLine)
				{
					continue;
				}
			}

			if (
				!is_array($csvRow) ||
				empty($csvRow) ||
				(count($csvRow) == 1 && ($csvRow[0] === null || $csvRow[0] === ''))
			)
			{
				continue;
			}

			$filePath = (isset($csvRow[$fileColumn]) ? $csvRow[$fileColumn] : '');
			if ($filePath == '')
			{
				continue;
			}
			if (Translate\IO\Path::isLangDir($filePath, true) !== true)
			{
				continue;
			}
			$filePath = Translate\IO\Path::normalize('/'.$filePath);

			if (isset($uniquePaths[$filePath]))
			{
				continue;
			}

			foreach ($this->languageList as $languageId)
			{
				$langFilePath = Translate\IO\Path::replaceLangId($filePath, $languageId);

				$fullPath = self::$documentRoot. $langFilePath;
				$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $languageId);

				$langFile = new Translate\File($fullPath);
				$langFile->setLangId($languageId);

				if (!$langFile->load())
				{
					$this->addErrors($langFile->getErrors());
					continue;
				}

				if ($langFile->getFileIndex()->getId() <= 0)
				{
					$topPath = $pathIndexer->constructAncestorsByPath($filePath);

					if ($topPath['ID'] > 0)
					{
						$fileIndex = $langFile->getFileIndex();
						$fileIndex->setPathId($topPath['ID']);
						$fileIndex->save();
					}
				}
				$langFile->updatePhraseIndex();
			}

			$uniquePaths[$filePath] = 1;
			$this->processedItems ++;

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				$this->seekLine = $currentLine;
				$this->seekPath = $filePath;
				break;
			}
		}

		$this->csvFile->close();

		if ($this->instanceTimer()->hasTimeLimitReached() !== true)
		{
			$this->declareAccomplishment();
			$this->clearProgressParameters();
		}

		return array(
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
		);
	}


	/**
	 * Validates uploaded csv file.
	 *
	 * @return boolean
	 */
	private function verifyCsvFile()
	{
		$testDelimiters = array(
			Translate\IO\CsvFile::DELIMITER_TZP,
			Translate\IO\CsvFile::DELIMITER_TAB,
			Translate\IO\CsvFile::DELIMITER_ZPT,
		);
		foreach ($testDelimiters as $delimiter)
		{
			$this->csvFile->setFieldDelimiter($delimiter);

			$this->csvFile->moveFirst();
			$rowHead = $this->csvFile->fetch();
			if (
				!is_array($rowHead) ||
				empty($rowHead) ||
				empty($rowHead[0]) ||
				(count($rowHead) < 3)
			)
			{
				continue;
			}

			break;
		}

		if (
			!is_array($rowHead) ||
			empty($rowHead) ||
			empty($rowHead[0]) ||
			(count($rowHead) < 3)
		)
		{
			return false;
		}

		$this->languageList = self::$enabledLanguages;
		$this->columnList = array_flip($rowHead);
		foreach ($this->languageList as $keyLang => $langID)
		{
			if (!isset($this->columnList[$langID]))
			{
				unset($this->languageList[$keyLang]);
			}
		}
		if (!isset($this->columnList['file']))
		{
			return false;
		}

		return true;
	}
}