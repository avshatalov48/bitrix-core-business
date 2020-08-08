<?php
namespace Bitrix\Translate\Controller\Asset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


/**
 * Harvester of the lang folder disposition.
 */
class Pack
	extends Translate\Controller\Action
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var string */
	public static $documentRoot;

	/** @var string */
	private $seekPath;

	/** @var string */
	private $languageId;

	/** @var bool */
	private $packFile;

	/** @var int */
	private $totalFileCount;

	/** @var string */
	private $tmpFolderPath;

	/** @var string */
	private $archiveFilePath;

	/** @var string */
	private $archiveFileName;

	/** @var Translate\IO\Archiver */
	private $archiveFile;

	/** @var array */
	private $downloadParams;

	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		$this->keepField([
			'packFile', 'languageId', 'tmpFolderPath', 'archiveFilePath',
			'archiveFileName', 'seekPath', 'totalFileCount', 'downloadParams',
		]);

		parent::__construct($name, $controller, $config);

		self::$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');
	}

	/**
	 * Runs controller action.
	 *
	 * @param boolean $runBefore Flag to run onBeforeRun event handler.
	 * @return array
	 */
	public function run($runBefore = false)
	{
		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		// continue previous process
		$progressParams = $this->getProgressParameters();
		$this->packFile = (bool)$progressParams['packFile'];
		$this->languageId = $progressParams['languageId'];
		$this->tmpFolderPath = $progressParams['tmpFolderPath'];

		$this->totalFileCount = (int)$progressParams['totalFileCount'];

		if ($this->isNewProcess)
		{
			$this->totalItems = $this->totalFileCount;
			$this->processedItems = 0;
			$this->archiveFileName = $this->generateExportFileName();

			$exportFolder = Translate\Config::getExportFolder();
			if (!empty($exportFolder))
			{
				$tempDir = new Translate\IO\Directory($exportFolder);
			}
			else
			{
				$tempDir = Translate\IO\Directory::generateTemporalDirectory('translate');
			}

			if (!$tempDir->isExists() || !$tempDir->isDirectory())
			{
				$this->addError(new Error(
					Loc::getMessage('TR_ERROR_CREATE_TEMP_FOLDER', array('#PATH#' => $tempDir->getPhysicalPath()))
				));
			}
			else
			{
				$this->archiveFilePath = $tempDir->getPhysicalPath(). '/'. $this->archiveFileName;
			}

			$this->seekPath = null;
		}
		else
		{
			$this->processedItems = (int)$progressParams['processedItems'];
			$this->archiveFilePath = $progressParams['archiveFilePath'];
			$this->archiveFileName = $progressParams['archiveFileName'];
			$this->seekPath = $progressParams['seekPath'];

			$tempDir = new Translate\IO\Directory($this->tmpFolderPath);
			if (!$tempDir->isExists() || !$tempDir->isDirectory())
			{
				$this->addError(
					new Error(Loc::getMessage('TR_ERROR_SOURCE_FOLDER', array('#PATH#' => $this->tmpFolderPath)))
				);
			}
		}


		$this->archiveFile = new Translate\IO\Archiver($this->archiveFilePath);

		if ($this->isNewProcess)
		{
			if ($this->archiveFile->isExists())
			{
				$this->archiveFile->delete();
			}
		}

		if ($this->isNewProcess)
		{
			$this->saveProgressParameters();

			return array(
				'STATUS' => ($this->totalItems > 0 ? Translate\Controller\STATUS_PROGRESS : Translate\Controller\STATUS_COMPLETED),
				'PROCESSED_ITEMS' => $this->processedItems,
				'TOTAL_ITEMS' => $this->totalItems,
			);
		}

		$this->archiveFile->setOptions(array(
			'COMPRESS' => $this->packFile,
		));

		return $this->performStep('runPacking');
	}

	/**
	 * @return array
	 */
	private function runPacking()
	{
		$langDir = new Translate\IO\Directory($this->tmpFolderPath);

		$this->totalItems = $this->totalFileCount;

		$result = array();

		switch ($this->archiveFile->pack($langDir, $this->seekPath))
		{
			case \IBXArchive::StatusContinue:
				$this->seekPath = $this->archiveFile->getSeekPosition();
				$this->processedItems += $this->archiveFile->getProcessedFileCount();

				$this->saveProgressParameters();
				break;

			case \IBXArchive::StatusSuccess:
				$this->processedItems += $this->archiveFile->getProcessedFileCount();
				$this->declareAccomplishment();

				$this->downloadParams = $this->getDownloadingParameters();
				$result['FILE_NAME'] = $this->downloadParams['fileName'];
				$result['DOWNLOAD_LINK'] = $this->generateDownloadLink();

				$messagePlaceholders = array(
					'#TOTAL_FILES#' => $this->processedItems,
					'#FILE_SIZE_FORMAT#' => \CFile::FormatSize($this->downloadParams['fileSize']),
					'#LANG#' => mb_strtoupper($this->languageId),
					'#FILE_PATH#' => $this->archiveFileName,
					'#LINK#' => $result['DOWNLOAD_LINK'],
				);

				$result['SUMMARY'] =
					Loc::getMessage('TR_LANGUAGE_COLLECTED_ARCHIVE', $messagePlaceholders)."\n".
					Loc::getMessage('TR_PACK_ACTION_EXPORT', $messagePlaceholders);

				// we have to continue process in next action
				$this->processToken = null;
				$this->seekPath = null;
				$this->saveProgressParameters();
				break;

			case \IBXArchive::StatusError:
				if ($this->archiveFile->hasErrors())
				{
					$this->addErrors($this->archiveFile->getErrors());
				}
				else
				{
					$this->addError(
						new Main\Error(Loc::getMessage('TR_ERROR_ARCHIVE'))
					);
				}
				break;
		}

		$result['PROCESSED_ITEMS'] = $this->processedItems;
		$result['TOTAL_ITEMS'] = $this->totalItems;

		return $result;

	}


	/**
	 * Generate link to download local exported temporally file.
	 *
	 *
	 * @return string
	 */
	private function generateDownloadLink()
	{
		return $this->controller->getActionUri(Grabber::ACTION_DOWNLOAD, ['langId' => $this->languageId])->getUri();
	}

	/**
	 * Generate name for exporting file.
	 *
	 * @return string
	 */
	private function generateExportFileName()
	{
		if ($this->packFile && Translate\IO\Archiver::libAvailable())
		{
			$fileName = 'file-'.$this->languageId.'.tar.gz';
		}
		else
		{
			$fileName = 'file-'.$this->languageId.'.tar';
		}

		return $fileName;
	}

	/**
	 * Returns exported file properties.
	 *
	 * @return array
	 */
	private function getDownloadingParameters()
	{
		return array(
			'fileName' => $this->archiveFileName,
			'filePath' => $this->archiveFilePath,
			'fileType' => $this->packFile ? 'application/tar+gzip' : 'application/tar',
			'fileSize' => $this->archiveFile->getSize(),
		);
	}

	/**
	 * Returns progress option name
	 *
	 * @return string
	 */
	public function getProgressParameterOptionName()
	{
		$controller = $this->getController();
		return $controller::SETTING_ID;
	}
}