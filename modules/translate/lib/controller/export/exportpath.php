<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;

/**
 * Harvester of phrases the list of files and folders.
 */
class ExportPath
	extends ExportAction
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var string[] */
	private array $pathList = [];

	/** @var string[] */
	private array $codeList = [];

	private int $seekOffset = 0;

	private int $seekPathLangId = 0;

	private string $seekLangFilePath = '';
	private string $seekPhraseCode = '';


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, array $config = [])
	{
		$this->keepField(['pathList', 'codeList', 'seekOffset', 'seekPathLangId', 'seekPhraseCode', 'seekLangFilePath']);

		Loc::loadLanguageFile(__DIR__ . '/exportaction.php');

		parent::__construct($name, $controller, $config);
	}


	/**
	 * Runs controller action.
	 *
	 * @param string $path Path to export.
	 * @param boolean $runBefore Flag to run onBeforeRun event handler.
	 *
	 * @return array
	 */
	public function run(string $path = '', bool $runBefore = false): array
	{
		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		if ($this->isNewProcess)
		{
			$pathList = $this->controller->getRequest()->get('pathList');

			$pathList = \preg_split("/[\r\n]+/", $pathList);
			\array_walk($pathList, 'trim');
			$pathList = \array_unique(\array_filter($pathList));
			if (empty($pathList))
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_EXPORT_EMPTY_PATH_LIST')));

				return [
					'STATUS' => Translate\Controller\STATUS_COMPLETED,
				];
			}

			foreach ($pathList as $testPath)
			{
				if (Translate\IO\Path::isPhpFile($testPath))
				{
					if (Translate\IO\Path::isLangDir($testPath))
					{
						$this->pathList[] = $testPath;
					}
				}
				else
				{
					$this->pathList[] = $testPath;
				}
			}

			// phrase codes
			$codeList = $this->controller->getRequest()->get('codeList');
			if (!empty($codeList))
			{
				$codeList = \preg_split("/[\r\n]+/", $codeList);
				\array_walk($codeList, 'trim');
				$this->codeList = \array_unique(\array_filter($codeList));
			}

			$this->totalItems = \count($this->pathList);
			$this->processedItems = 0;

			if ($this->totalItems > 0)
			{
				$this->exportFileName = $this->generateExportFileName($path, $this->languages);
				$csvFile = $this->createExportTempFile($this->exportFileName);
				$this->exportFilePath = $csvFile->getPhysicalPath();
				$this->exportFileSize = $csvFile->getSize();
			}
			if ($this->appendSamples)
			{
				$this->samplesFileName = $this->generateExportFileName($path.'-samples', $this->languages);
				$sampleFile = $this->createExportTempFile($this->samplesFileName);
				$this->samplesFilePath = $sampleFile->getPhysicalPath();
				$this->samplesFileSize = $sampleFile->getSize();
			}

			$this->saveProgressParameters();

			return [
				'STATUS' => ($this->totalItems > 0 ? Translate\Controller\STATUS_PROGRESS : Translate\Controller\STATUS_COMPLETED),
				'PROCESSED_ITEMS' => 0,
				'TOTAL_ITEMS' => $this->totalItems,
			];
		}

		return $this->performStep('runExporting');
	}


	/**
	 * Collects lang files paths.
	 *
	 * @return array
	 */
	private function runExporting(): array
	{
		$csvFile = new Translate\IO\CsvFile($this->exportFilePath);
		$this->configureExportCsvFile($csvFile);
		$csvFile->openWrite( Main\IO\FileStreamOpenMode::APPEND);

		if ($this->appendSamples)
		{
			$samplesFile = new Translate\IO\CsvFile($this->samplesFilePath);
			$this->configureExportCsvFile($samplesFile);
			$samplesFile->openWrite( Main\IO\FileStreamOpenMode::APPEND);
		}

		$processedItemCount = 0;

		$filterCodeList = $this->codeList ?: [];
		$fileCodeList = [];
		foreach ($filterCodeList as $pathCode)
		{
			[$path, $code] = \explode('::', $pathCode);
			if ($path && $code)
			{
				$langFilePath = Translate\IO\Path::replaceLangId($path, '#LANG_ID#');
				if (!isset($fileCodeList[$langFilePath]))
				{
					$fileCodeList[$langFilePath] = [];
				}
				$fileCodeList[$langFilePath][] = $code;
			}
		}

		$currentLangId = Loc::getCurrentLang();

		for ($pos = ($this->seekOffset > 0 ? $this->seekOffset : 0), $total = \count($this->pathList); $pos < $total; $pos ++)
		{
			$exportingPath = $this->pathList[$pos];

			// file
			if (Translate\IO\Path::isPhpFile($exportingPath))
			{
				$langFilePath = Translate\IO\Path::replaceLangId($exportingPath, '#LANG_ID#');
				if (!empty($this->seekLangFilePath))
				{
					if ($langFilePath == $this->seekLangFilePath)
					{
						$this->seekLangFilePath = '';
					}
					else
					{
						continue;
					}
				}

				$fullPaths = [];
				foreach ($this->languages as $langId)
				{
					$langRelPath = Translate\IO\Path::replaceLangId($exportingPath, $langId);
					$langFullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$langRelPath);

					if (self::$useTranslationRepository && \in_array($langId, self::$translationRepositoryLanguages))
					{
						$langFullPath = Main\Localization\Translation::convertLangPath($langFullPath, $langId);
					}

					$fullPaths[$langId] = $langFullPath;
				}

				$rows = $this->mergeLangFiles($langFilePath, $fullPaths, $this->collectUntranslated, $fileCodeList[$langFilePath] ?? []);
				foreach ($rows as $code => $row)
				{
					if (!empty($this->seekPhraseCode))
					{
						if ($code == $this->seekPhraseCode)
						{
							$this->seekPhraseCode = '';
						}
						continue;
					}

					$csvFile->put(\array_values($row));

					if (
						$this->appendSamples
						&& !empty($row[$currentLangId])
						&& mb_strlen($row[$currentLangId]) < $this->maxSampleSourceLength
					)
					{
						$samples = $this->findSamples(
							$row[$currentLangId],
							$currentLangId,
							$langFilePath,
							$this->samplesCount,
							$this->samplesRestriction
						);
						foreach ($samples as $sample)
						{
							$samplesFile->put(\array_values($sample));
							$this->exportedSamplesCount ++;
						}
					}

					$this->exportedPhraseCount ++;

					if ($this->instanceTimer()->hasTimeLimitReached())
					{
						$this->seekPhraseCode = $code;
					}
					else
					{
						$this->seekPhraseCode = '';
					}
				}

				if ($this->instanceTimer()->hasTimeLimitReached())
				{
					$this->seekLangFilePath = $langFilePath;
					break;
				}
				else
				{
					$this->seekLangFilePath = '';
				}
			}

			// folder
			else
			{
				$exportingPath = Translate\IO\Path::tidy($exportingPath. '/');
				if (\preg_match("#(.+/lang)(/?\w*)#", $exportingPath, $matches))
				{
					$lookForLangPath = $matches[1];
					$lookForLangSubPath = '';
					if (\preg_match("#(.+/lang/[^/]+/?)(.*)$#", $exportingPath, $subMatches))
					{
						$lookForLangSubPath = $subMatches[2];
					}
				}
				else
				{
					$lookForLangPath = $exportingPath;
					$lookForLangSubPath = '';
				}
				unset($matches, $subMatches);


				// now let's find lang files
				$pathFilter = [
					'=%PATH' => $lookForLangPath.'%'
				];
				if ($this->seekPathLangId > 0)
				{
					$pathFilter['>=ID'] = $this->seekPathLangId;
				}

				$cachePathLangRes = Translate\Index\Internals\PathLangTable::getList([
					'filter' => $pathFilter,
					'order' => ['ID' => 'ASC'],
					'select' => ['ID', 'PATH'],
				]);
				while ($pathLang = $cachePathLangRes->fetch())
				{
					$lookThroughPath = $pathLang['PATH']. '/#LANG_ID#';
					if (!empty($lookForLangSubPath))
					{
						$lookThroughPath .= '/'. \trim($lookForLangSubPath, '/');
					}
					foreach ($this->lookThroughLangFolder($lookThroughPath) as $filePaths)
					{
						foreach ($filePaths as $langFilePath => $fullPaths)
						{
							if (!empty($this->seekLangFilePath))
							{
								if ($langFilePath == $this->seekLangFilePath)
								{
									$this->seekLangFilePath = '';
								}
								else
								{
									continue;
								}
							}

							$rows = $this->mergeLangFiles($langFilePath, $fullPaths, $this->collectUntranslated, $fileCodeList[$langFilePath] ?? []);
							foreach ($rows as $code => $row)
							{
								if (!empty($this->seekPhraseCode))
								{
									if ($code == $this->seekPhraseCode)
									{
										$this->seekPhraseCode = '';
									}
									continue;
								}

								$csvFile->put(\array_values($row));

								if (
									$this->appendSamples
									&& !empty($row[$currentLangId])
									&& mb_strlen($row[$currentLangId]) < $this->maxSampleSourceLength
								)
								{
									$samples = $this->findSamples(
										$row[$currentLangId],
										$currentLangId,
										$langFilePath,
										$this->samplesCount,
										$this->samplesRestriction
									);
									foreach ($samples as $sample)
									{
										$samplesFile->put(\array_values($sample));
										$this->exportedSamplesCount ++;
									}
								}

								$this->exportedPhraseCount ++;

								if ($this->instanceTimer()->hasTimeLimitReached())
								{
									$this->seekPhraseCode = $code;
									break;
								}
								else
								{
									$this->seekPhraseCode = '';
								}
							}

							if ($this->instanceTimer()->hasTimeLimitReached())
							{
								$this->seekLangFilePath = $langFilePath;
								break;
							}
							else
							{
								$this->seekLangFilePath = '';
							}
						}
					}

					if ($this->instanceTimer()->hasTimeLimitReached())
					{
						$this->seekPathLangId = (int)$pathLang['ID'];
						break 2;
					}
					else
					{
						$this->seekPathLangId = 0;
					}
				}
			}

			$processedItemCount ++;

			if (isset($this->pathList[$pos + 1]))
			{
				$this->seekOffset = $pos + 1;//next
			}
			else
			{
				$this->seekOffset = 0;
				$this->declareAccomplishment();
				$this->clearProgressParameters();
			}

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				break;
			}
		}

		$this->exportFileSize = $csvFile->getSize();

		$csvFile->close();
		if ($this->appendSamples)
		{
			$this->samplesFileSize = $samplesFile->getSize();
			$samplesFile->close();
		}

		$this->processedItems += $processedItemCount;

		if ($this->instanceTimer()->hasTimeLimitReached() !== true)
		{
			$this->declareAccomplishment();
			$this->clearProgressParameters();
		}

		$result = [
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
			'TOTAL_PHRASES' => $this->exportedPhraseCount,
			'TOTAL_SAMPLES' => $this->exportedSamplesCount,
		];

		if ($csvFile->hasErrors())
		{
			$errors = $csvFile->getErrors();
			foreach ($errors as $err)
			{
				if ($err->getCode() == Translate\IO\CsvFile::ERROR_32K_FIELD_LENGTH)
				{
					$result['WARNING'] = Loc::getMessage('TR_EXPORT_ERROR_32K_LENGTH');
				}
				else
				{
					$this->addError($err);
				}
			}
		}

		return $result;
	}
}