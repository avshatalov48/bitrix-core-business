<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * Harvester of phrases using search result with index.
 */
class ExportFileSearch
	extends ExportAction
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

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
		$this->keepField('seekPathId');

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
		if (\preg_match("#(.+\/lang)(\/?\w*)#", $path, $matches))
		{
			$path = $matches[1];
		}

		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		if ($this->isNewProcess)
		{
			$this->totalItems = (int)Index\FileIndexSearch::getCount($this->processFilter($path));
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
		$path = \rtrim($params['path'], '/');

		$csvFile = new Translate\IO\CsvFile($this->exportFilePath);
		$this->configureExportCsvFile($csvFile);
		$csvFile->openWrite( Main\IO\FileStreamOpenMode::APPEND);


		$pathFilter = $this->processFilter($path);
		if (!empty($this->seekPathId))
		{
			$pathFilter['>PATH_ID'] = $this->seekPathId;
		};

		$select = array('PATH_ID', 'PATH');

		foreach ($this->languages as $langId)
		{
			$select[] = \mb_strtoupper($langId)."_LANG";
		}
		if (!\in_array($this->filter['LANGUAGE_ID'], $this->languages))
		{
			$select[] = \mb_strtoupper($this->filter['LANGUAGE_ID'])."_LANG";
		}

		/** @var Main\ORM\Query\Result $cachePathRes */
		$pathInxRes = Index\FileIndexSearch::getList(array(
			'filter' => $pathFilter,
			'order' => ['PATH_ID' => 'ASC'],
			'select' => $select,
		));

		$processedItemCount = 0;
		while ($pathInx = $pathInxRes->fetch())
		{
			$fileInxRes = Index\Internals\FileIndexTable::getList(array(
				'filter' => ['=PATH_ID' => $pathInx['PATH_ID']],
				'order' => ['ID' => 'ASC'],
				'select' => ['ID', 'PATH_ID', 'LANG_ID', 'FULL_PATH'],
			));
			$fullPaths = array();
			while ($fileInx = $fileInxRes->fetch())
			{
				$fullPaths[$fileInx['LANG_ID']] = $fileInx['FULL_PATH'];
			}

			$rows = $this->mergeLangFiles($pathInx['PATH'], $fullPaths, $this->collectUntranslated);
			foreach ($rows as $row)
			{
				$csvFile->put(array_values($row));
				$this->exportedPhraseCount ++;
			}

			$processedItemCount ++;

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				$this->seekPathId = (int)$pathInx['PATH_ID'];
				break;
			}
		}

		$this->exportFileSize = $csvFile->getSize();
		$csvFile->close();

		$this->processedItems += $processedItemCount;

		if ($this->instanceTimer()->hasTimeLimitReached() !== true)
		{
			$this->declareAccomplishment();
			$this->clearProgressParameters();
		}

		$result = array(
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
			'TOTAL_PHRASES' => $this->exportedPhraseCount,
		);

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
				if (!\in_array($key, ['FILTER_ID', 'PRESET_ID', 'FILTER_APPLIED', 'FIND', 'tabId']))
				{
					$filterOut[$key] = $value;
				}
			}
		}

		$filterOut['PATH'] = Translate\IO\Path::replaceLangId($path, '#LANG_ID#');

		return $filterOut;
	}
}