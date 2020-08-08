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

	/** @var string */
	private $pathList;

	/** @var int */
	private $seekPathId;

	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
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
	public function run($path = '', $runBefore = false)
	{
		if (empty($path))
		{
			$path = Translate\Config::getDefaultPath();
		}
		if (preg_match("#(.+\/lang)(\/?\w*)#", $path, $matches))
		{
			$path = $matches[1];
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
				$this->createExportTempFile($this->exportFileName);
			}

			$this->saveProgressParameters();

			return array(
				'STATUS' => ($this->totalItems > 0 ? Translate\Controller\STATUS_PROGRESS : Translate\Controller\STATUS_COMPLETED),
				'PROCESSED_ITEMS' => 0,
				'TOTAL_ITEMS' => $this->totalItems,
				'TOTAL_PHRASES' => $this->exportedPhraseCount,
			);
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
	private function runExporting(array $params)
	{
		$path = rtrim($params['path'], '/');

		$csvFile = new Translate\IO\CsvFile($this->exportFilePath);
		$this->configureExportCsvFile($csvFile);
		$csvFile->openWrite( Main\IO\FileStreamOpenMode::APPEND);


		$phraseFilter = $this->processFilter($path);
		if (!empty($this->seekPathId))
		{
			$phraseFilter['>PATH_ID'] = $this->seekPathId;
		}

		$select = array('PATH_ID', 'PHRASE_CODE', 'FILE_PATH');

		foreach ($this->languages as $langId)
		{
			$select[] = mb_strtoupper($langId)."_LANG";
		}
		if (!in_array($this->filter['LANGUAGE_ID'], $this->languages))
		{
			$select[] = mb_strtoupper($this->filter['LANGUAGE_ID'])."_LANG";
		}

		/** @var Main\ORM\Query\Result $cachePathRes */
		$phraseInxRes = Index\PhraseIndexSearch::getList(array(
			'filter' => $phraseFilter,
			'order' => ['TITLE' => 'ASC', 'PATH_ID' => 'ASC'],
			'select' => $select,
			//todo: add limit here
		));

		$processedItemCount = 0;

		$prevPathId = -1;
		$fileInxCache = array();
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

			if (!isset($fileInxCache[$pathId]))
			{
				$fileInxRes = Translate\Index\Internals\FileIndexTable::getList(array(
					'filter' => ['=PATH_ID' => $pathId],
					'order' => ['ID' => 'ASC'],
					'select' => ['ID', 'PATH_ID', 'LANG_ID', 'FULL_PATH'],
				));
				$fullPaths = array();
				while ($fileInx = $fileInxRes->fetch())
				{
					$fullPaths[$fileInx['LANG_ID']] = $fileInx['FULL_PATH'];
				}

				$fileInxCache[$pathId] = $this->mergeLangFiles($phraseInx['FILE_PATH'], $fullPaths, $this->collectUntranslated);
			}

			$rows =& $fileInxCache[$pathId];

			if (isset($rows[$phraseInx['PHRASE_CODE']]))
			{
				$csvFile->put(array_values($rows[$phraseInx['PHRASE_CODE']]));
				$this->exportedPhraseCount ++;
			}

			$processedItemCount ++;

			$prevPathId = $pathId;
		}

		$this->exportFileSize = $csvFile->getSize();
		$csvFile->close();

		$this->processedItems += $processedItemCount;

		if ($this->instanceTimer()->hasTimeLimitReached() !== true)
		{
			$this->declareAccomplishment();
			$this->clearProgressParameters();
		}

		return array(
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
			'TOTAL_PHRASES' => $this->exportedPhraseCount,
		);
	}


	/**
	 * Process incoming filter from client.
	 *
	 * @param string $path Path to export.
	 *
	 * @return array
	 */
	private function processFilter($path)
	{
		$filterOut = array();

		if (
			$this->filter instanceof Translate\Filter &&
			$this->filter->count() > 0
		)
		{
			foreach ($this->filter as $key => $value)
			{
				if (!in_array($key, ['FILTER_ID', 'PRESET_ID', 'FILTER_APPLIED', 'FIND', 'tabId']))
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