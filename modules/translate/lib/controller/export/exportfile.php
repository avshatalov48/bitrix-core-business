<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;

/**
 * Harvester of phrases the single file.
 */
class ExportFile
	extends ExportAction
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	private string $seekPhraseCode = '';

	private array $data = [];

	private string $langFilePath = '';

	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, array $config = [])
	{
		Loc::loadLanguageFile(__DIR__ . '/exportaction.php');

		$this->keepField('seekPhraseCode');

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
		if (empty($path) || !\preg_match("#(.+\/lang)(\/?\w*)#", $path, $matches))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EXPORT_EMPTY_PATH_LIST')));

			return [
				'STATUS' => Translate\Controller\STATUS_COMPLETED
			];
		}
		if (!Translate\IO\Path::isLangDir($path))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EXPORT_FILE_NOT_LANG', ['#FILE#' => $path])));

			return [
				'STATUS' => Translate\Controller\STATUS_COMPLETED
			];
		}

		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		$this->langFilePath = Translate\IO\Path::replaceLangId($path, '#LANG_ID#');

		$fullPaths = [];
		foreach ($this->languages as $langId)
		{
			$langRelPath = Translate\IO\Path::replaceLangId($path, $langId);
			$langFullPath = Translate\IO\Path::tidy(self::$documentRoot . '/' . $langRelPath);

			if (self::$useTranslationRepository && \in_array($langId, self::$translationRepositoryLanguages))
			{
				$langFullPath = Main\Localization\Translation::convertLangPath($langFullPath, $langId);
			}

			$fullPaths[$langId] = $langFullPath;
		}

		$this->data = $this->mergeLangFiles($this->langFilePath, $fullPaths, $this->collectUntranslated);

		if ($this->isNewProcess)
		{
			$this->totalItems = (int)count($this->data);
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
				$this->samplesFileName = $this->generateExportFileName('samples-'.$path, $this->languages);
				$sampleFile = $this->createExportTempFile($this->samplesFileName);
				$this->samplesFilePath = $sampleFile->getPhysicalPath();
				$this->samplesFileSize = $sampleFile->getSize();
			}

			$this->saveProgressParameters();

			return [
				'STATUS' => $this->totalItems > 0
					? Translate\Controller\STATUS_PROGRESS
					: Translate\Controller\STATUS_COMPLETED,
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
	 * @param array $params Path to export.
	 *
	 * @return array
	 */
	private function runExporting(array $params): array
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

		$currentLangId = Loc::getCurrentLang();

		$processedItemCount = 0;
		foreach ($this->data as $code => $row)
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
					$this->langFilePath,
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
			$processedItemCount ++;

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				$this->seekPhraseCode = $code;
				break;
			}
		}

		$this->processedItems += $processedItemCount;

		$this->exportFileSize = $csvFile->getSize();
		$csvFile->close();

		if ($this->appendSamples)
		{
			$this->samplesFileSize = $samplesFile->getSize();
			$samplesFile->close();
		}

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