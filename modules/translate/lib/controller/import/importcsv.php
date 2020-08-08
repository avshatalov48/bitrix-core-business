<?php
namespace Bitrix\Translate\Controller\Import;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


/**
 * Harvester of the lang folder disposition.
 */
class ImportCsv
	extends Translate\Controller\Action
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var int */
	private $seekLine;

	/** @var string */
	protected static $documentRoot;
	/** @var string[] */
	protected static $enabledLanguages;
	/** @var string[] */
	protected static $sourceEncoding;
	/** @var boolean */
	protected static $isUtfMode;

	/** @var int Session tab counter. */
	private $tabId = 0;

	/** @var string */
	private $encodingIn;

	/** @var string */
	private $updateMethod;

	/** @var string */
	private $csvFilePath;

	/** @var Translate\IO\CsvFile */
	private $csvFile;


	/** @var string[] */
	private $languageList;

	/** @var string[] */
	private $columnList;

	/** @var int */
	private $importedPhraseCount = 0;



	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		$fields = ['tabId', 'encodingIn', 'updateMethod', 'csvFilePath', 'seekLine', 'importedPhrasesCount'];

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

		foreach (self::$enabledLanguages as $languageId)
		{
			self::$sourceEncoding[$languageId] = mb_strtolower(Main\Localization\Translation::getSourceEncoding($languageId));
		}

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
			return array(
				'STATUS' => Translate\Controller\STATUS_COMPLETED
			);
		}

		if ($this->csvFile->hasUtf8Bom())
		{
			$this->encodingIn = 'utf-8';
		}

		if ($this->isNewProcess)
		{
			$this->clearProgressParameters();

			$this->totalItems = 0;
			while ($csvRow = $this->csvFile->fetch())
			{
				$this->totalItems ++;
			}

			$this->processedItems = 0;
			$this->seekLine = 0;

			$this->saveProgressParameters();

			return array(
				'STATUS' => Translate\Controller\STATUS_PROGRESS,
				'PROCESSED_ITEMS' => 0,
				'TOTAL_ITEMS' => $this->totalItems,
			);
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
			if (isset($progressParams['importedPhrasesCount']))
			{
				$this->importedPhraseCount = $progressParams['importedPhrasesCount'];
			}
		}

		return $this->performStep('runImporting');
	}

	/**
	 * Imports data from csv file.
	 *
	 * @return array
	 */
	private function runImporting()
	{
		$fileIndex = $this->columnList['file'];
		$keyIndex = $this->columnList['key'];

		$currentLine = 0;
		$maxLinePortion = 500;
		$hasFinishedReading = false;

		while (true)
		{
			$linePortion = 0;
			$phraseList = array();

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

				$rowErrors = array();

				$filePath = (isset($csvRow[$fileIndex]) ? $csvRow[$fileIndex] : '');
				$key = (isset($csvRow[$keyIndex]) ? $csvRow[$keyIndex] : '');
				if ($filePath == '' || $key == '')
				{
					if ($filePath == '')
					{
						$rowErrors[] = Loc::getMessage('TR_IMPORT_ERROR_DESTINATION_FILEPATH_ABSENT');
					}
					if ($key == '')
					{
						$rowErrors[] = Loc::getMessage('TR_IMPORT_ERROR_PHRASE_CODE_ABSENT');
					}
					$this->addError(new Main\Error(Loc::getMessage(
						'TR_IMPORT_ERROR_LINE_FILE_EXT',
						[
							'#LINE#' => ($currentLine + 1),
							'#ERROR#' => implode('; ', $rowErrors)
						]
					)));

					continue;
				}

				$linePortion ++;

				if (!isset($phraseList[$filePath]))
				{
					$phraseList[$filePath] = [];
				}
				foreach ($this->languageList as $languageId)
				{
					if (!isset($phraseList[$filePath][$languageId]))
					{
						$phraseList[$filePath][$languageId] = [];
					}

					$langIndex = $this->columnList[$languageId];
					if (!isset($csvRow[$langIndex]) || (empty($csvRow[$langIndex]) && $csvRow[$langIndex] !== '0'))
					{
						continue;
					}

					$phrase = str_replace("\\\\", "\\", $csvRow[$langIndex]);

					$encodingOut = self::$sourceEncoding[$languageId];

					if (!empty($this->encodingIn) && $this->encodingIn !== $encodingOut)
					{
						$errorMessage = '';
						$phrase = Main\Text\Encoding::convertEncoding($phrase, $this->encodingIn, $encodingOut, $errorMessage);

						if (!$phrase && !empty($errorMessage))
						{
							$rowErrors[] = $errorMessage;
							continue;
						}
					}

					$checked = true;
					if ($encodingOut === 'utf-8')
					{
						$validPhrase = preg_replace("/[^\x01-\x7F]/", '', $phrase);// remove ASCII characters
						if ($validPhrase !== $phrase)
						{
							$checked = Translate\Text\StringHelper::validateUtf8OctetSequences($phrase);
						}
						unset($validPhrase);
					}

					if ($checked)
					{
						$phraseList[$filePath][$languageId][$key] = $phrase;
					}
					else
					{
						$rowErrors[] = Loc::getMessage('TR_IMPORT_ERROR_NO_VALID_UTF8_PHRASE', ['#LANG#' => $languageId]);
					}

					unset($checked, $phrase);
				}

				if (!empty($rowErrors))
				{
					$this->addError(new Main\Error(Loc::getMessage(
						'TR_IMPORT_ERROR_LINE_FILE_BIG',
						[
							'#LINE#' => ($currentLine + 1),
							'#FILENAME#' => $filePath,
							'#PHRASE#' => $key,
							'#ERROR#' => implode('; ', $rowErrors),
						]
					)));
				}
				unset($rowErrors);


				if ($linePortion >= $maxLinePortion)
				{
					break;
				}
			}

			if ($csvRow === null)
			{
				$hasFinishedReading = true;
			}
			unset($csvRow);

			$this->processedItems += $linePortion;

			foreach ($phraseList as $filePath => $translationList)
			{
				if (Translate\IO\Path::isLangDir($filePath, true) !== true)
				{
					$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_ERROR_FILE_NOT_LANG', array('#FILE#' => $filePath))));
					continue;
				}

				$filePath = Translate\IO\Path::normalize('/'.$filePath);

				foreach ($translationList as $languageId => $fileMessages)
				{
					if (empty($fileMessages))
					{
						continue;
					}

					$langFilePath = Translate\IO\Path::replaceLangId($filePath, $languageId);

					if (\Rel2Abs('/', $langFilePath) !== $langFilePath)
					{
						$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_ERROR_BAD_FILEPATH', ['#FILE#' => $filePath])));
						break;
					}

					$fullPath = self::$documentRoot. $langFilePath;
					$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $languageId);

					$langFile = new Translate\File($fullPath);
					$langFile->setLangId($languageId);
					$langFile->setOperatingEncoding(self::$sourceEncoding[$languageId]);

					if (!$langFile->load() && $langFile->hasErrors())
					{
						$this->addErrors($langFile->getErrors());
						continue;
					}

					$hasDataToUpdate = false;

					/** @var \ArrayAccess $langFile */
					foreach ($fileMessages as $key => $phrase)
					{
						switch ($this->updateMethod)
						{
							// import only new messages
							case Translate\Controller\Import\Csv::METHOD_ADD_ONLY:
								if (!isset($langFile[$key]) || (empty($langFile[$key]) && $langFile[$key] !== '0'))
								{
									$langFile[$key] = $phrase;
									$hasDataToUpdate = true;
									$this->importedPhraseCount ++;
								}
								break;

							// update only existing messages
							case Translate\Controller\Import\Csv::METHOD_UPDATE_ONLY:
								if (isset($langFile[$key]) && $langFile[$key] !== $phrase)
								{
									$langFile[$key] = $phrase;
									$hasDataToUpdate = true;
									$this->importedPhraseCount ++;
								}
								break;


							// import new messages and replace all existing with new ones
							case Translate\Controller\Import\Csv::METHOD_ADD_UPDATE:
								if ($langFile[$key] !== $phrase)
								{
									$langFile[$key] = $phrase;
									$hasDataToUpdate = true;
									$this->importedPhraseCount ++;
								}
								break;
						}
					}

					if ($hasDataToUpdate)
					{
						// backup
						if ($langFile->isExists() && Translate\Config::needToBackUpFiles())
						{
							if (!$langFile->backup())
							{
								$this->addError(new Main\Error(
									Loc::getMessage('TR_IMPORT_ERROR_CREATE_BACKUP', ['#FILE#' => $langFilePath])
								));
							}
						}

						// sort phrases by key
						if (Translate\Config::needToSortPhrases())
						{
							if (in_array($languageId, Translate\Config::getNonSortPhraseLanguages()) === false)
							{
								$langFile->sortPhrases();
							}
						}

						try
						{
							if (!$langFile->save())
							{
								if ($langFile->hasErrors())
								{
									$this->addErrors($langFile->getErrors());
								}
							}
						}
						catch (Main\IO\IoException $exception)
						{
							if (!$langFile->isExists())
							{
								$this->addError(new Main\Error(
									Loc::getMessage('TR_IMPORT_ERROR_WRITE_CREATE', ['#FILE#' => $langFilePath])
								));
							}
							else
							{
								$this->addError(new Main\Error(
									Loc::getMessage('TR_IMPORT_ERROR_WRITE_UPDATE', ['#FILE#' => $langFilePath])
								));
							}
						}
					}
				}
			}

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				$this->seekLine = $currentLine;
				break;
			}

			if ($hasFinishedReading)
			{
				$this->declareAccomplishment();
				$this->clearProgressParameters();
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
			'TOTAL_PHRASES' => $this->importedPhraseCount,
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
			$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_ERR_EMPTY_FIRST_ROW')));

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
			$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_ERR_DESTINATION_FIELD_ABSENT')));
		}
		if (!isset($this->columnList['key']))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_ERR_PHRASE_CODE_FIELD_ABSENT')));
		}
		if (empty($this->languageList))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_ERR_LANGUAGE_LIST_ABSENT')));
		}

		return count($this->getErrors()) === 0;
	}
}