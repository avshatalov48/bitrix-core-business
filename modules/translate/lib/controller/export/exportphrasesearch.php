<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * Harvester of phrases using search result with index.
 */
class ExportPhraseSearch
	extends ExportAction
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	private string $pathList = '';

	private int $seekPathId = 0;

	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, array $config = [])
	{
		$this->keepField(['pathList', 'seekPathId']);

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
		if (empty($path))
		{
			$path = Translate\Config::getDefaultPath();
		}

		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		if ($this->isNewProcess)
		{
			$pathList = $this->controller->getRequest()->get('pathList');
			if (!empty($pathList))
			{
				$this->pathList = $pathList;
			}

			$this->totalItems = (int)Index\PhraseIndexSearch::getCount($this->processFilter($path));
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
				'TOTAL_PHRASES' => $this->exportedPhraseCount,
				'TOTAL_SAMPLES' => $this->exportedSamplesCount,
			];
		}

		return $this->performStep('runExporting', ['path' => $path]);
	}


	/**
	 * Collects lang files paths.
	 *
	 * @param array $params Path to indexing.
	 *
	 * @return array
	 */
	private function runExporting(array $params): array
	{
		$path = \rtrim($params['path'], '/');

		$csvFile = new Translate\IO\CsvFile($this->exportFilePath);
		$this->configureExportCsvFile($csvFile);
		$csvFile->openWrite( Main\IO\FileStreamOpenMode::APPEND);

		if ($this->appendSamples)
		{
			$samplesFile = new Translate\IO\CsvFile($this->samplesFilePath);
			$this->configureExportCsvFile($samplesFile);
			$samplesFile->openWrite( Main\IO\FileStreamOpenMode::APPEND);
		}

		$phraseFilter = $this->processFilter($path);
		if (!empty($this->seekPathId))
		{
			$phraseFilter['>PATH_ID'] = $this->seekPathId;
		}

		$select = ['PATH_ID', 'PHRASE_CODE', 'FILE_PATH'];

		foreach ($this->languages as $langId)
		{
			$select[] = \mb_strtoupper($langId)."_LANG";
		}
		if (!\in_array($this->filter['LANGUAGE_ID'], $this->languages))
		{
			$select[] = \mb_strtoupper($this->filter['LANGUAGE_ID'])."_LANG";
		}

		$currentLangId = Loc::getCurrentLang();

		/** @var Main\ORM\Query\Result $cachePathRes */
		$phraseInxRes = Index\PhraseIndexSearch::getList([
			'filter' => $phraseFilter,
			'order' => ['PATH_ID' => 'ASC'],
			'select' => $select,
		]);

		$processedItemCount = 0;

		$prevPathId = -1;
		$fileInxCache = [];
		while ($phraseInx = $phraseInxRes->fetch())
		{
			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				if ($prevPathId != (int)$phraseInx['PATH_ID'])
				{
					$this->seekPathId = $prevPathId;
					break;
				}
			}

			$pathId = (int)$phraseInx['PATH_ID'];
			$phraseCode = $phraseInx['PHRASE_CODE'];

			if (!isset($fileInxCache[$pathId]))
			{
				$fullPaths = $this->getFullPath($pathId);

				$fileInxCache[$pathId] = $this->mergeLangFiles($phraseInx['FILE_PATH'], $fullPaths, $this->collectUntranslated);
			}

			if (isset($fileInxCache[$pathId][$phraseCode]))
			{
				$row = &$fileInxCache[$pathId][$phraseCode];
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
						$pathId,
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
			}

			$processedItemCount ++;

			$prevPathId = $pathId;
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

		return [
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
			'TOTAL_PHRASES' => $this->exportedPhraseCount,
			'TOTAL_SAMPLES' => $this->exportedSamplesCount,
		];
	}


	/**
	 * Process incoming filter from client.
	 *
	 * @param string $path Path to export.
	 *
	 * @return array
	 */
	private function processFilter(string $path): array
	{
		$filterOut = [];

		if ($this->filter->count() > 0)
		{
			foreach ($this->filter as $key => $value)
			{
				if (!\in_array($key, ['FILTER_ID', 'PRESET_ID', 'FILTER_APPLIED', 'FIND', 'tabId']))
				{
					$filterOut[$key] = $value;
				}
			}
		}

		$filterOut['PATH'] = Translate\IO\Path::replaceLangId($path, '#LANG_ID#');

		if (!empty($this->pathList))
		{
			$filterOut['INCLUDE_PATHS'] = $this->pathList;
		}

		return $filterOut;
	}
}