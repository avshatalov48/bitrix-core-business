<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * Harvester of phrases without using index.
 */
class ExportFileList
	extends ExportAction
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var int */
	private $seekPathLangId;


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		$this->keepField('seekPathLangId');

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

		// part of the path after /lang/
		$subPath = '';
		if (\preg_match("#(.+/lang)(/?\w*)#", $path, $matches))
		{
			if (\preg_match("#(.+/lang/[^/]+/?)(.*)$#", $path, $subMatches))
			{
				$subPath = $subMatches[2];
			}
			$path = $matches[1];
		}
		unset($matches, $subMatches);


		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		if ($this->isNewProcess)
		{
			$this->totalItems = (int)Index\Internals\PathLangTable::getCount(array('=%PATH' => $path.'%'));
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

		return $this->performStep('runExporting', ['path' => $path, 'subPath' => $subPath]);
	}

	/**
	 * Collects lang files paths.
	 *
	 * @param array $params Path to export.
	 *
	 * @return array
	 */
	private function runExporting(array $params)
	{
		$path = \rtrim($params['path'], '/');
		$subPath = \trim($params['subPath'], '/');

		$csvFile = new Translate\IO\CsvFile($this->exportFilePath);
		$this->configureExportCsvFile($csvFile);
		$csvFile->openWrite( Main\IO\FileStreamOpenMode::APPEND);

		$pathFilter = array();
		$pathFilter[] = array(
			'LOGIC' => 'OR',
			'=PATH' => $path,
			'=%PATH' => $path.'/%',
		);
		if (!empty($this->seekPathLangId))
		{
			$pathFilter['>ID'] = $this->seekPathLangId;
		}

		$cachePathLangRes = Index\Internals\PathLangTable::getList(array(
			'filter' => $pathFilter,
			'order' => array('ID' => 'ASC'),
			'select' => ['ID', 'PATH'],
		));
		$processedItemCount = 0;
		while ($pathLang = $cachePathLangRes->fetch())
		{
			$lookThroughPath = $pathLang['PATH']. '/#LANG_ID#';
			if (!empty($subPath))
			{
				$lookThroughPath .= '/'. $subPath;
			}
			foreach ($this->lookThroughLangFolder($lookThroughPath) as $filePaths)
			{
				foreach ($filePaths as $langFilePath => $fullPaths)
				{
					$rows = $this->mergeLangFiles($langFilePath, $fullPaths, $this->collectUntranslated);
					foreach ($rows as $row)
					{
						$csvFile->put(array_values($row));
						$this->exportedPhraseCount ++;
					}
				}
			}

			$processedItemCount ++;

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				$this->seekPathLangId = (int)$pathLang['ID'];
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
}