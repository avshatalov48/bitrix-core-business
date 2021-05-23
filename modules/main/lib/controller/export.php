<?php
namespace Bitrix\Main\Controller;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Response\AjaxJson;


class Export extends Main\Engine\Controller
{
	/** @var string - Module Id. */
	protected $module = '';

	/** Where to store cloud files. */
	const EXPORT_PATH = '/export/';

	/** How many day keep files in the cloud. */
	const EXPIRE_DAYS = 24;

	const ACTION_EXPORT = 'export';
	const ACTION_UPLOAD = 'upload';
	const ACTION_CANCEL = 'cancel';
	const ACTION_FINISH = 'finish';
	const ACTION_DOWNLOAD = 'download';
	const ACTION_CLEAR = 'clear';
	const ACTION_VOID = 'void';
	const ACTION_PURGE = 'purge';

	const STATUS_COMPLETED = 'COMPLETED';
	const STATUS_PROGRESS = 'PROGRESS';


	/** @var string */
	protected $componentName = '';

	/** @var array */
	protected $componentParameters = array();

	/** @var string - Exporting file type csv|excel. */
	protected $exportType;

	const EXPORT_TYPE_CSV = 'csv';
	const EXPORT_TYPE_EXCEL = 'excel';

	/** @var string */
	protected $processToken;

	/** @var boolean */
	protected $isNewProcess = true;

	/** @var string */
	protected $fileName = '';

	/** @var string */
	protected $filePath = '';

	/** @var int */
	protected $fileSize = 0;

	/** @var string */
	protected $fileType = 'application/csv';

	/** How long to keep temporally files. */
	const KEEP_FILE_HOURS = 5;

	/** @var int */
	protected $processedItems = 0;

	/** @var int */
	protected $totalItems = 0;

	/** @var int */
	protected $pageSize = 0;

	/** Default limitation query result per page. */
	const ROWS_PER_PAGE = 100;

	/** @var int */
	protected $lastExportedId = -1;

	/** @var \CCloudStorageBucket */
	protected $bucket;

	/** @var int */
	protected $bucketId = -1;

	/** @var string */
	protected $uploadPath = '';

	/** @var int */
	protected $uploadedSize = 0;

	/** @var int */
	protected $uploadedPart = 0;

	/** @var int */
	protected $cloudMinChunkSize = 5 * 1024 * 1024; // 5M

	/** @var string[] */
	protected $fieldToStoreInProcess = array(
		'exportType',
		'fileName',
		'filePath',
		'fileSize',
		'fileType',
		'uploadedSize',
		'uploadPath',
		'uploadedPart',
		'pageSize',
		'processedItems',
		'totalItems',
		'lastExportedId',
		'cloudChunkSize',
		'bucketId',
		'isExportCompleted',
		'isUploadFinished',
		'timeStart',
		'stepCount',
		'useCloud',
	);

	/** @var boolean */
	protected $isBitrix24 = false;

	/** @var boolean */
	protected $isCloudAvailable = false;

	/** @var boolean */
	protected $isExportCompleted = false;

	/** @var boolean */
	protected $isUploadFinished = false;

	/** @var integer */
	protected $timeStart = 0;

	/** @var integer */
	protected $stepCount = 0;

	/** @var boolean */
	protected $useCloud = null;

	/** @var float seconds */
	protected $timeLimit = -1;

	/** @var float seconds */
	protected $hitStartTime = -1;

	/** @var boolean */
	protected $timeLimitReached = false;

	/** @const int */
	const TIME_LIMIT = 20; // 20 seconds

	/**
	 * Action
	 * @return array
	 */
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$configureActions[self::ACTION_DOWNLOAD] = array(
			'-prefilters' => array(
				Main\Engine\ActionFilter\Csrf::class
			)
		);

		return $configureActions;
	}

	/**
	 * Initializes controller.
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		Loc::loadMessages(__FILE__);

		$this->isBitrix24 = Main\ModuleManager::isModuleInstalled('bitrix24');

		$this->isCloudAvailable =
			Main\ModuleManager::isModuleInstalled('clouds') &&
			Main\Loader::includeModule('clouds');

		$this->processToken = $this->request->get('PROCESS_TOKEN');
		$this->exportType = $this->request->get('EXPORT_TYPE');
		$this->componentName = $this->request->get('COMPONENT_NAME');

		$signedParameters = $this->request->getPost('signedParameters');
		if (!empty($signedParameters))
		{
			$this->componentParameters = $this->decodeSignedParameters($this->componentName, $signedParameters);
		}

		$initialOptions = $this->request->getPost('INITIAL_OPTIONS');
		if (!empty($initialOptions))
		{
			$this->componentParameters['STEXPORT_INITIAL_OPTIONS'] = $initialOptions;
		}

		$progressData = $this->getProgressParameters();
		if (count($progressData) > 0)
		{
			$this->isNewProcess = (empty($progressData['processToken']) || $progressData['processToken'] !== $this->processToken);
			if (!$this->isNewProcess)
			{
				// restore state
				foreach ($this->fieldToStoreInProcess as $fieldName)
				{
					if (isset($progressData[$fieldName]))
					{
						$this->{$fieldName} = $progressData[$fieldName];
					}
				}
			}
		}

		if ($this->isCloudAvailable)
		{
			$bucketList = $this->getBucketList();
			if (count($bucketList) == 0)
			{
				$this->isCloudAvailable = false;
			}
		}
	}


	/**
	 * Common operations before process action.
	 * @param Main\Engine\Action $action Action.
	 * @return bool If method will return false, then action will not execute.
	 */
	protected function processBeforeAction(Main\Engine\Action $action)
	{
		if (parent::processBeforeAction($action))
		{
			if (!$this->checkCommonErrors($action))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Add name on filed to keep during in the process.
	 *
	 * @param string $fieldName Field name.
	 *
	 * @return self
	 */
	protected function keepFieldInProcess($fieldName)
	{
		$this->fieldToStoreInProcess[] = $fieldName;

		return $this;
	}

	/**
	 * @return array|Main\Engine\Response\AjaxJson
	 */
	public function dispatcherAction()
	{
		// can we use cloud
		if ($this->isCloudAvailable && $this->useCloud === null)
		{
			if (
				($this->bucketId < 0) &&
				($this->totalItems > 0) &&
				($this->processedItems > 0) &&
				($this->fileName !== '') &&
				($this->getSizeTempFile() > 0) &&
				(($this->getSizeTempFile() > $this->cloudMinChunkSize) || $this->isExportCompleted)
			)
			{
				if ($this->isExportCompleted)
				{
					$predictedFileSize = $this->getSizeTempFile();
				}
				else
				{
					$predictedFileSize = round($this->getSizeTempFile() * ceil($this->totalItems / $this->processedItems) * 1.5);
				}
				// check cloud bucket rules
				if ($this->findInitBucket(array('fileSize' => $predictedFileSize, 'fileName' => $this->fileName)) === true)
				{
					// allow
					$this->useCloud = true;
				}
				else
				{
					// deny
					$this->useCloud = false;
					$this->isCloudAvailable = false;
				}
			}
		}

		if ($this->isNewProcess)
		{
			// start export
			$this->stepCount = 0;
			$this->timeStart = time();
			$nextAction = self::ACTION_EXPORT;
		}
		elseif ($this->isBitrix24)
		{
			if ($this->isExportCompleted && !$this->isUploadFinished)
			{
				// upload last chunk
				$nextAction = self::ACTION_UPLOAD;
			}
			elseif ($this->isExportCompleted && $this->isUploadFinished)
			{
				// purge cloud and finish
				$nextAction = self::ACTION_PURGE;
			}
			elseif ($this->getSizeTempFile() > $this->cloudMinChunkSize)
			{
				// upload chunk
				$nextAction = self::ACTION_UPLOAD;
			}
			else
			{
				// continue export into temporally file
				$nextAction = self::ACTION_EXPORT;
			}
		}
		else
		{
			// non b24
			if ($this->isExportCompleted && ($this->useCloud === true) && !$this->isUploadFinished)
			{
				// upload last chunk
				$nextAction = self::ACTION_UPLOAD;
			}
			elseif ($this->isExportCompleted && ($this->useCloud === true) && $this->isUploadFinished)
			{
				// purge cloud and finish
				$nextAction = self::ACTION_PURGE;
			}
			elseif ($this->isExportCompleted && ($this->useCloud === false || $this->useCloud === null))
			{
				// completed
				$nextAction = self::ACTION_FINISH;
			}
			elseif (($this->useCloud === true) && ($this->getSizeTempFile() > $this->cloudMinChunkSize))
			{
				// upload chunk
				$nextAction = self::ACTION_UPLOAD;
			}
			else
			{
				// continue export into temporally file
				$nextAction = self::ACTION_EXPORT;
			}
		}

		if ($nextAction === self::ACTION_PURGE)
		{
			return $this->purgeAction();
		}
		if ($nextAction === self::ACTION_EXPORT)
		{
			$this->stepCount ++;
			return $this->exportAction();
		}
		if ($nextAction === self::ACTION_UPLOAD)
		{
			$this->stepCount ++;
			return $this->uploadAction();
		}
		if ($nextAction === self::ACTION_FINISH)
		{
			return $this->finishAction();
		}

		return $this->cancelAction();
	}


	/**
	 * @return Main\HttpResponse|void
	 */
	public function downloadAction()
	{
		if (
			$this->filePath !== '' &&
			$this->fileName !== ''
		)
		{
			$path = new Main\IO\File($this->filePath);
			if ($path->isExists())
			{

				$response = new Main\Engine\Response\File(
					$path->getPath(),
					$this->fileName,
					$this->getFileType()
				);

				return $response;
			}
		}

		$this->addError(new Error('File not found'));
	}


	/**
	 * Performs exporting action.
	 *
	 * @return array|Main\Engine\Response\AjaxJson
	 */
	public function exportAction()
	{
		/** @global \CMain */
		global $APPLICATION;

		if ($this->isNewProcess)
		{
			$this->fileType = $this->getFileType();
			$this->fileName = $this->generateExportFileName();
			$this->filePath = $this->generateTempDirPath(). $this->fileName;
			$this->processedItems = 0;
			$this->totalItems = 0;
			$this->pageSize = self::ROWS_PER_PAGE;
			$this->saveProgressParameters();
		}

		$this->startTimer(self::TIME_LIMIT);
		do
		{
			$nextPage = (int)floor($this->processedItems / $this->pageSize) + 1;

			$componentParameters = array_merge(
				$this->componentParameters,
				array(
					'STEXPORT_MODE' => 'Y',
					'EXPORT_TYPE' => $this->exportType,
					'STEXPORT_PAGE_SIZE' => $this->pageSize,
					'STEXPORT_TOTAL_ITEMS' => $this->totalItems,
					'STEXPORT_LAST_EXPORTED_ID' => $this->lastExportedId,
					'PAGE_NUMBER' => $nextPage,
				)
			);

			ob_start();
			$componentResult = $APPLICATION->IncludeComponent(
				$this->componentName,
				'',
				$componentParameters
			);
			$exportData = ob_get_contents();
			ob_end_clean();

			$processedItemsOnStep = 0;

			if (is_array($componentResult))
			{
				if (isset($componentResult['ERROR']))
				{
					$this->addError(new Error($componentResult['ERROR']));
					break;
				}
				else
				{
					if (isset($componentResult['PROCESSED_ITEMS']))
					{
						$processedItemsOnStep = (int)$componentResult['PROCESSED_ITEMS'];
					}

					// Get total items quantity on 1st step.
					if ($nextPage === 1 && isset($componentResult['TOTAL_ITEMS']))
					{
						$this->totalItems = (int)$componentResult['TOTAL_ITEMS'];
					}

					if (isset($componentResult['LAST_EXPORTED_ID']))
					{
						$this->lastExportedId = (int)$componentResult['LAST_EXPORTED_ID'];
					}
				}
			}

			if ($this->totalItems == 0)
			{
				break;
			}

			if ($processedItemsOnStep > 0)
			{
				$this->processedItems += $processedItemsOnStep;

				$this->writeTempFile($exportData, ($nextPage === 1));
				unset($exportData);

				$this->isExportCompleted = ($this->processedItems >= $this->totalItems);

				if ($this->isExportCompleted && !$this->isCloudAvailable)
				{
					// adjust completed file size
					$this->fileSize = $this->getSizeTempFile();
				}
			}
			elseif ($processedItemsOnStep == 0)
			{
				// Smth went wrong - terminate process.
				$this->isExportCompleted = true;

				if (!$this->isCloudAvailable)
				{
					$this->fileSize = $this->getSizeTempFile();
				}
			}

			if ($this->isExportCompleted === true)
			{
				// finish
				break;
			}
			if ($nextPage === 1)
			{
				// to answer faster
				break;
			}
		}
		while ($this->hasTimeLimitReached() !== true);

		if ($this->totalItems == 0)
		{
			$this->isExportCompleted = true;

			// Save progress
			$this->saveProgressParameters();

			// finish
			$result = $this->preformAnswer(self::ACTION_VOID);
			$result['STATUS'] = self::STATUS_COMPLETED;
		}
		else
		{
			// Save progress
			$this->saveProgressParameters();

			$result = $this->preformAnswer(self::ACTION_EXPORT);
			$result['STATUS'] = self::STATUS_PROGRESS;
		}

		return $result;
	}


	/**
	 * Performs of the action uploading into the cloud.
	 *
	 * @return array
	 */
	public function uploadAction()
	{
		$chunkSize = $this->getSizeTempFile();

		$isUploadStarted = ($this->uploadedPart > 0);
		if (!$isUploadStarted)
		{
			$reservedSize = round($chunkSize * ceil($this->totalItems / $this->pageSize) * 1.5);

			$this->uploadPath = $this->generateUploadDir(). $this->fileName;

			$this->findInitBucket(array(
				'fileSize' => $reservedSize,
				'fileName' => $this->uploadPath,
			));

			if ($this->checkCloudErrors())
			{
				$this->saveProgressParameters();

				if ($this->bucket->FileExists($this->uploadPath))
				{
					if(!$this->bucket->DeleteFile($this->uploadPath))
					{
						$this->addError(new Error('File exists in a cloud.'));
					}
				}
			}
		}
		else
		{
			$this->instanceBucket();

			$this->checkCloudErrors();
		}


		if (!file_exists($this->filePath))
		{
			$this->addError(new Error('Uploading file not exists.'));
		}

		if (count($this->getErrors()) > 0)
		{
			return AjaxJson::createError($this->errorCollection);
		}


		$isSuccess = false;

		if ($this->isExportCompleted && !$isUploadStarted)
		{
			// just only one file
			$uploadFile = array(
				'name' => $this->fileName,
				'size' => $this->fileSize,
				'type' => $this->fileType,
				'tmp_name' => $this->filePath,
			);

			if ($this->bucket->SaveFile($this->uploadPath, $uploadFile))
			{
				$this->uploadedPart ++;
				$this->uploadedSize += $chunkSize;
				$isSuccess = true;
				$this->isUploadFinished = true;
			}
			else
			{
				$this->addError(new Error('Uploading error.'));
			}
		}
		else
		{
			$uploader = new \CCloudStorageUpload($this->uploadPath);
			if (!$uploader->isStarted())
			{
				if (!$uploader->Start($this->bucketId, $reservedSize, $this->fileType))
				{
					$this->addError(new Error('Start uploading error.'));

					return AjaxJson::createError($this->errorCollection);
				}
			}

			$part = $this->getContentTempFile();

			while ($uploader->hasRetries())
			{
				if ($uploader->Next($part, $this->bucket))
				{
					$this->uploadedPart ++;
					$this->uploadedSize += $chunkSize;
					$isSuccess = true;
					break;
				}
			}
			unset($part);

			// finish
			if ($isSuccess && $this->isExportCompleted)
			{
				if ($uploader->Finish())
				{
					$this->isUploadFinished = true;
				}
				else
				{
					$this->addError(new Error('FILE_UNKNOWN_ERROR'));
				}
			}
		}

		if ($isSuccess)
		{
			$this->dropTempFile();
		}

		// Save progress
		$this->saveProgressParameters();

		// continue export into temporally file
		$result = $this->preformAnswer(self::ACTION_UPLOAD);
		$result['STATUS'] = self::STATUS_PROGRESS;

		return $result;
	}


	/**
	 * Drops temporally and cloud files.
	 *
	 * @return array
	 */
	public function clearAction()
	{
		$this->cancelAction();

		$result = $this->preformAnswer(self::ACTION_CLEAR);

		$result['STATUS'] = self::STATUS_COMPLETED;

		return $result;

	}


	/**
	 * Cancels exporting and uploading processes. Drops temporally and cloud files.
	 *
	 * @return array
	 */
	public function cancelAction()
	{
		$this->processToken = null;
		$this->isNewProcess = true;

		$this->dropTempFile();

		$this->instanceBucket();

		if ($this->bucket instanceof \CCloudStorageBucket)
		{
			if ($this->bucket->FileExists($this->uploadPath))
			{
				if (!$this->bucket->DeleteFile($this->uploadPath))
				{
					$this->addError(new Error('Cloud drop error.'));
				}
			}
		}

		$this->clearProgressParameters();

		$result = $this->preformAnswer(self::ACTION_CANCEL);

		$result['STATUS'] = self::STATUS_COMPLETED;

		return $result;
	}


	/**
	 * Drops all outdated cloud files.
	 *
	 * @return array
	 */
	public function purgeAction()
	{
		$this->instanceBucket();

		if ($this->bucket instanceof \CCloudStorageBucket)
		{
			$dirFiles = $this->bucket->ListFiles($this->generateUploadDir(), true);

			$now = new \DateTime('now');
			foreach ($dirFiles['file'] as $fileName)
			{
				if (preg_match('/^([^_]+)_([0-9]{8})_([^_]+)_.+$/i', $fileName, $parts))
				{
					//$type = $parts[1];
					//$hash = $parts[3];
					$date = \DateTime::createFromFormat('Ymd', $parts[2]);
					$interval = $now->diff($date);
					if ($interval->d > self::EXPIRE_DAYS)
					{
						$this->bucket->DeleteFile($this->generateUploadDir(). $fileName);
					}
				}
			}
		}

		return $this->finishAction();
	}


	/**
	 * Finishes exporting and uploading processes. Drops temporally.
	 *
	 * @return array
	 */
	public function finishAction()
	{
		if ($this->isExportCompleted && !$this->isCloudAvailable)
		{
			// adjust completed local file size
			$this->fileSize = $this->getSizeTempFile();
		}

		$result = $this->preformAnswer(self::ACTION_FINISH);

		$result['STATUS'] = self::STATUS_COMPLETED;

		return $result;
	}


	/**
	 * Preforms action answer.
	 * @param string $action Action.
	 * @return array
	 */
	protected function preformAnswer($action)
	{
		if ($action == self::ACTION_CLEAR || $action == self::ACTION_CANCEL || $action == self::ACTION_PURGE)
		{
			$result = array();
		}
		else
		{
			$fileSize = $this->fileSize;
			if (!$this->isExportCompleted && $this->uploadedSize > 0)
			{
				$fileSize = $this->uploadedSize + $this->fileSize;
			}
			elseif ($this->isExportCompleted && $this->uploadedSize > $this->fileSize)
			{
				$fileSize = $this->uploadedSize;
			}

			$result = array(
				'STATUS' => ($this->isExportCompleted ? self::STATUS_COMPLETED : self::STATUS_PROGRESS),
				'PROCESSED_ITEMS' => $this->processedItems,
				'TOTAL_ITEMS' => $this->totalItems,
				'UPLOADED_PART' => $this->uploadedPart,
				'UPLOADED_SIZE' => $this->uploadedSize,
				'UPLOADED_SIZE_FORMAT' => \CFile::FormatSize($this->uploadedSize),
				'FILE_SIZE_FORMAT' => \CFile::FormatSize($fileSize),
			);

			$messagePlaceholders = array(
				'#PROCESSED_ITEMS#' => $this->processedItems,
				'#TOTAL_ITEMS#' => $this->totalItems,
				'#UPLOADED_PART#' => $this->uploadedPart,
				'#UPLOADED_SIZE#' => $this->uploadedSize,
				'#UPLOADED_SIZE_FORMAT#' => \CFile::FormatSize($this->uploadedSize),
				'#FILE_SIZE_FORMAT#' => \CFile::FormatSize($fileSize),
			);
		}

		if ($action == self::ACTION_VOID)
		{
			$message = htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_VOID'));
		}
		elseif ($action == self::ACTION_PURGE)
		{
			$message = htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_PURGE'));
		}
		elseif ($action == self::ACTION_CLEAR)
		{
			$message = htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_FILE_DROPPED'));
		}
		elseif ($action == self::ACTION_CANCEL)
		{
			$message = htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_ACTION_CANCEL'));
		}
		elseif ($action == self::ACTION_UPLOAD)
		{
			$message =
				htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_ACTION_UPLOAD', $messagePlaceholders)). '<br>'.
				$this->preformExpectedDuration();
		}
		elseif ($action == self::ACTION_EXPORT)
		{
			$message =
				htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_ACTION_EXPORT', $messagePlaceholders)). '<br>'.
				$this->preformExpectedDuration();
		}
		elseif ($this->isExportCompleted)
		{
			$message =
				htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_COMPLETED')). '<br>'.
				htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_ACTION_EXPORT', $messagePlaceholders));

			$downloadLink = '';
			if ($this->isBitrix24 || $this->useCloud === true)
			{
				if ($this->isUploadFinished)
				{
					$downloadLink = $this->generateCloudLink();
				}
			}
			else
			{
				$downloadLink = $this->generateDownloadLink();
			}
			if ($downloadLink !== '')
			{
				$result['DOWNLOAD_LINK'] = $downloadLink;
				$result['FILE_NAME'] = $this->fileName;
				$result['DOWNLOAD_LINK_NAME'] = htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_DOWNLOAD'));
				$result['CLEAR_LINK_NAME'] = htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_CLEAR'));
			}
		}
		else
		{
			$message =
				htmlspecialcharsbx(Loc::getMessage('MAIN_EXPORT_ACTION_EXPORT', $messagePlaceholders)). '<br>'.
				$this->preformExpectedDuration();
		}

		$result['SUMMARY_HTML'] = $message;

		return $result;
	}

	/**
	 * Preform expected duration message.
	 *
	 * @return string
	 */
	protected function preformExpectedDuration()
	{
		$message = '';
		$avgStepDuration = $predictedStepCount = $predictedTimeDuration = 0;
		$avgRowsPerStep = self::ROWS_PER_PAGE;
		if ($this->stepCount > 0 && $this->timeStart > 0)
		{
			$avgStepDuration = round((time() - $this->timeStart) / $this->stepCount);
			if ($this->processedItems > 0)
			{
				$avgRowsPerStep = round($this->processedItems / $this->stepCount);
			}
		}
		if ($this->totalItems > 0)
		{
			$predictedStepCount = round(($this->totalItems - $this->processedItems) / $avgRowsPerStep);
			if ($this->useCloud === true)
			{
				$predictedStepCount *= 2;
			}
		}
		if ($avgStepDuration > 0 && $predictedStepCount > 0)
		{
			$predictedTimeDuration = $avgStepDuration * $predictedStepCount * 1.1;
		}
		if ($predictedTimeDuration > 0)
		{
			$predictedTimeDurationHours = floor($predictedTimeDuration / 3600);
			if ($predictedTimeDurationHours > 0)
			{
				$predictedTimeDurationMinutes = ceil(($predictedTimeDuration - $predictedTimeDurationHours * 3600) / 60);
				$message =
					Loc::getMessage('MAIN_EXPORT_EXPECTED_DURATION').' '.
					Loc::getMessage('MAIN_EXPORT_EXPECTED_DURATION_HOURS', array(
						'#HOURS#' => $predictedTimeDurationHours,
						'#MINUTES#' => $predictedTimeDurationMinutes,
					));
			}
			else
			{
				$predictedTimeDurationMinutes = round($predictedTimeDuration / 60);
				$message =
					Loc::getMessage('MAIN_EXPORT_EXPECTED_DURATION').' '.
					Loc::getMessage('MAIN_EXPORT_EXPECTED_DURATION_MINUTES', array(
						'#MINUTES#' => ($predictedTimeDurationMinutes < 1 ? "&lt;&nbsp;1" : $predictedTimeDurationMinutes),
					));
			}
		}

		return $message;
	}


	/**
	 * Generate link to download local exported temporally file.
	 *
	 * @return string
	 */
	protected function generateDownloadLink()
	{
		$params = array(
			'PROCESS_TOKEN' => $this->processToken,
			'EXPORT_TYPE' => $this->exportType,
			'COMPONENT_NAME' => $this->componentName,
		);

		return $this->getActionUri(self::ACTION_DOWNLOAD, $params);
	}

	/**
	 * Generate link to download exported file from cloud.
	 *
	 * @return string
	 */
	protected function generateCloudLink()
	{
		$this->instanceBucket();

		if ($this->checkCloudErrors())
		{
			return $this->bucket->GetFileSRC($this->uploadPath);
		}

		return '';
	}


	/**
	 * Checks for common errors.
	 *
	 * @param \Bitrix\Main\Engine\Action $action Action.
	 * @return bool - True if errors not exist.
	 */
	protected function checkCommonErrors($action)
	{
		if (empty($this->module))
		{
			$this->addError(new Error('Module Id property is not filled.'));
		}
		if (!Main\Loader::includeModule($this->module))
		{
			$this->addError(new Error('Module '.$this->module.' is not included.'));
		}

		if ($this->isBitrix24)
		{
			if ($this->isCloudAvailable !== true)
			{
				$this->addError(new Error(Loc::getMessage('MAIN_EXPORT_ERROR_NO_CLOUD_BUCKET')));
			}
		}

		if ($action->getName() === self::ACTION_PURGE)
		{
			return true;
		}

		if ($this->componentName === '')
		{
			$this->addError(new Error('Component name is not specified.'));
		}
		if (!in_array($this->exportType, array(self::EXPORT_TYPE_CSV, self::EXPORT_TYPE_EXCEL), true))
		{
			$this->addError(new Error('The export type is not supported.'));
		}

		if($this->processToken === '')
		{
			$this->addError(new Error('Process token is not specified.'));
		}

		return count($this->getErrors()) === 0;
	}


	/**
	 * Checks for cloud errors.
	 *
	 * @return bool - True if errors not exist.
	 */
	protected function checkCloudErrors()
	{
		if (!($this->bucket instanceof \CCloudStorageBucket))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('MAIN_EXPORT_ERROR_NO_CLOUD_BUCKET'));
		}

		return count($this->getErrors()) === 0;
	}


	/**
	 * Finds and instantiates the cloud bucket for file attributes.
	 *
	 * @param array $attributes File attributes for bucket search.
	 *
	 * @return bool
	 */
	protected function findInitBucket(array $attributes)
	{
		if (!($this->bucket instanceof \CCloudStorageBucket))
		{
			$this->bucket = \CCloudStorage::FindBucketForFile(
				array(
					'FILE_SIZE' => $attributes['fileSize'],
					'MODULE_ID' => $this->module,
				),
				$attributes['fileName']
			);

			if (
				$this->bucket === null ||
				!($this->bucket instanceof \CCloudStorageBucket) ||
				!$this->bucket->init()
			)
			{
				return false;
			}

			$this->bucketId = $this->bucket->ID;
			$this->cloudMinChunkSize = $this->bucket->GetService()->GetMinUploadPartSize(); //5M
		}

		return true;
	}

	/**
	 * Returns the cloud bucket for file attributes.
	 *
	 * @return \CCloudStorageBucket
	 */
	protected function instanceBucket()
	{
		if (!($this->bucket instanceof \CCloudStorageBucket))
		{
			if ($this->bucketId > 0)
			{
				$this->bucket = new \CCloudStorageBucket($this->bucketId);
				$this->bucket->init();
			}
		}

		return $this->bucket;
	}

	/**
	 * Returns active cloud bucket list.
	 *
	 * @param array $filter Filter params.
	 *
	 * @return array
	 */
	protected function getBucketList($filter = array())
	{
		$result = array();
		$res = \CCloudStorageBucket::GetList(array(), array_merge(array('ACTIVE' => 'Y', 'READ_ONLY' => 'N'), $filter));
		while($bucket = $res->Fetch())
		{
			$result[] = $bucket;
		}

		return $result;
	}

	/**
	 * Save progress parameters.
	 *
	 * @return boolean
	 */
	protected function saveProgressParameters()
	{
		// store state
		$progressData = array(
			'processToken' => $this->processToken,
		);
		foreach ($this->fieldToStoreInProcess as $fieldName)
		{
			$progressData[$fieldName] = $this->{$fieldName};
		}

		$res = \CUserOptions::SetOption($this->module, $this->getProgressParameterOptionName(), $progressData);
		return  $res;
	}

	/**
	 * Load progress parameters.
	 *
	 * @return array
	 */
	protected function getProgressParameters()
	{
		$progressData = \CUserOptions::GetOption($this->module, $this->getProgressParameterOptionName());
		if (!is_array($progressData))
		{
			$progressData = array();
		}

		return $progressData;
	}

	/**
	 * Removes progress parameters.
	 *
	 * @return boolean
	 */
	protected function clearProgressParameters()
	{
		return \CUserOptions::DeleteOption($this->module, $this->getProgressParameterOptionName());
	}

	/**
	 * Returns progress option name
	 *
	 * @return string
	 */
	protected function getProgressParameterOptionName()
	{
		return $this->module. '_cloud_export';
	}

	/**
	 * Returns file name
	 *
	 * @return string
	 */
	protected function generateExportFileName()
	{
		if ($this->exportType === self::EXPORT_TYPE_CSV)
		{
			$fileExt = 'csv';
		}
		elseif ($this->exportType === self::EXPORT_TYPE_EXCEL)
		{
			$fileExt = 'xls';
		}

		$prefix = date('Ymd');
		$hash = str_pad(dechex(crc32($prefix)), 8, '0', STR_PAD_LEFT);

		return uniqid($prefix. '_'. $hash. '_', false).'.'.$fileExt;
	}

	/**
	 * Returns file mime/type
	 *
	 * @return string
	 */
	protected function getFileType()
	{
		if ($this->exportType === self::EXPORT_TYPE_CSV)
		{
			$fileType = 'application/csv';
		}
		elseif ($this->exportType === self::EXPORT_TYPE_EXCEL)
		{
			$fileType = 'application/vnd.ms-excel';
		}

		return $fileType;
	}

	/**
	 * Returns temporally directory
	 *
	 * @return string
	 */
	protected function generateTempDirPath()
	{
		$tempDir = \CTempFile::GetDirectoryName(self::KEEP_FILE_HOURS, array($this->module, uniqid( 'export_', true)));

		\CheckDirPath($tempDir);

		return $tempDir;
	}

	/**
	 * Returns path to upload
	 *
	 * @return string
	 */
	protected function generateUploadDir()
	{
		return self::EXPORT_PATH;
	}


	/**
	 * Sings and returns parameters as string in format "{message}{separator}{signature}".
	 *
	 * @param string $componentName Component name.
	 * @param array $params Parameters of component.
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	protected function getSignedParameters($componentName, array $params)
	{
		return Main\Component\ParameterSigner::signParameters($componentName, $params);
	}


	/**
	 * Sings and stores parameters.
	 *
	 * @param string $componentName Component name.
	 * @param string $signedParameters Signed parameters of component as string.
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	protected function decodeSignedParameters($componentName, $signedParameters)
	{
		return Main\Component\ParameterSigner::unsignParameters($componentName, $signedParameters);
	}

	/**
	 * @param string $data Bytes to write.
	 * @param boolean $precedeUtf8Bom Precede first bites with UTF8 BOM mark.
	 * @return void
	 */
	protected function writeTempFile($data, $precedeUtf8Bom = true)
	{
		$file = fopen($this->filePath, 'ab');
		if(is_resource($file))
		{
			// add UTF-8 BOM marker
			if (\Bitrix\Main\Application::isUtfMode() || defined('BX_UTF'))
			{
				if($precedeUtf8Bom === true && (filesize($this->filePath) === 0))
				{
					fwrite($file, chr(239).chr(187).chr(191));
				}
			}
			fwrite($file, $data);
			fclose($file);
			unset($file);

			$this->fileSize = filesize($this->filePath);
		}
	}

	/**
	 * @return void
	 */
	protected function dropTempFile()
	{
		if (file_exists($this->filePath))
		{
			@unlink($this->filePath);
		}
		$this->fileSize = 0;
	}

	/**
	 * @return string
	 */
	protected function getContentTempFile()
	{
		return file_get_contents($this->filePath);
	}

	/**
	 * @return int
	 */
	protected function getSizeTempFile()
	{
		$this->fileSize = filesize($this->filePath);

		return $this->fileSize;
	}

	/**
	 * Start up timer.
	 *
	 * @param int $timeLimit Time limit.
	 * @return void
	 */
	protected function startTimer($timeLimit = 25)
	{
		$this->timeLimit = $timeLimit;

		if (defined('START_EXEC_TIME') && START_EXEC_TIME > 0)
		{
			$this->hitStartTime = (int)START_EXEC_TIME;
		}
		else
		{
			$this->hitStartTime = time();
		}
	}


	/**
	 * Tells true if time limit reached.
	 *
	 * @return boolean
	 */
	protected function hasTimeLimitReached()
	{
		if ($this->timeLimit > 0)
		{
			if ($this->timeLimitReached)
			{
				return true;
			}
			if ((time() - $this->hitStartTime) >= $this->timeLimit)
			{
				$this->timeLimitReached = true;

				return true;
			}
		}

		return false;
	}
}
