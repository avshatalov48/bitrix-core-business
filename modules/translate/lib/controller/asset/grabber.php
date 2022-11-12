<?php
namespace Bitrix\Translate\Controller\Asset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


class Grabber
	extends Translate\Controller\Controller
	implements Translate\Controller\IProcessParameters
{
	use Translate\Controller\ProcessParams;

	const START_PATH = '/bitrix/modules';

	const SETTING_ID = 'TRANSLATE_LANGPACK';

	const ACTION_COLLECT = 'collect';
	const ACTION_PACK = 'pack';
	const ACTION_DOWNLOAD = 'download';
	const ACTION_UPLOAD = 'upload';
	const ACTION_EXTRACT = 'extract';
	const ACTION_APPLY = 'apply';
	const ACTION_APPLY_PUBLIC = 'apply_public';
	const ACTION_FINALIZE = 'finalize';
	const ACTION_PURGE = 'purge';
	const ACTION_CANCEL = 'cancel';
	const ACTION_CLEAR = 'clear';

	/** @var string */
	private $archiveFilePath;
	/** @var string */
	private $archiveFileType;



	/**
	 * Configures actions.
	 *
	 * @return array
	 */
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$permission = new Translate\Controller\CheckPermission(Translate\Permission::WRITE);
		$permissionSource = new Translate\Controller\CheckPermission(Translate\Permission::SOURCE);

		$configureActions[self::ACTION_COLLECT] = array(
			'class' => Translate\Controller\Asset\Collect::class,
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_EXTRACT] = array(
			'class' => Translate\Controller\Asset\Extract::class,
			'+prefilters' => array(
				$permission,
				$permissionSource
			),
		);
		$configureActions[self::ACTION_APPLY] = array(
			'class' => Translate\Controller\Asset\Apply::class,
			'+prefilters' => array(
				$permission,
				$permissionSource
			),
		);
		$configureActions[self::ACTION_APPLY_PUBLIC] = array(
			'class' => Translate\Controller\Asset\ApplyPublic::class,
			'+prefilters' => array(
				$permission,
				$permissionSource
			),
		);
		$configureActions[self::ACTION_PACK] = array(
			'class' => Translate\Controller\Asset\Pack::class,
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_UPLOAD] = array(
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_DOWNLOAD] = array(
			'-prefilters' => array(
				Main\Engine\ActionFilter\Csrf::class,
			),
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_PURGE] = array(
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_CANCEL] = array(
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_CLEAR] = array(
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_FINALIZE] = array(
			'+prefilters' => array(
				$permission
			),
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
		$this->keepField(['archiveFilePath', 'archiveFileType']);
	}

	/**
	 * Handles uploaded file.
	 *
	 * @return array
	 */
	public function uploadAction()
	{
		$result = array();
		$success = false;
		if (
			isset($_FILES, $_FILES['tarFile'], $_FILES['tarFile']['tmp_name']) &&
			($_FILES['tarFile']['error'] == \UPLOAD_ERR_OK) &&
			\file_exists($_FILES['tarFile']['tmp_name'])
		)
		{
			if (
				(\filesize($_FILES['tarFile']['tmp_name']) > 0) &&
				(
					\mb_substr($_FILES['tarFile']['name'], -7) === '.tar.gz' ||
					\mb_substr($_FILES['tarFile']['name'], -4) === '.tar'
				)
			)
			{
				if (\mb_substr($_FILES['tarFile']['name'], -7) === '.tar.gz')
				{
					$suffix = '.tar.gz';
				}
				else
				{
					$suffix = '.tar';
				}

				if ($this->moveUploadedFile($_FILES['tarFile'], $suffix))
				{
					$this->saveProgressParameters();
					$success = ($this->hasErrors() === false);
				}
			}
			else
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_ERROR_TARFILE_EXTENTION')));
			}
		}
		else
		{
			if ($_FILES['tarFile']['error'] == UPLOAD_ERR_INI_SIZE)
			{
				$this->addError(
					new Main\Error(Loc::getMessage('TR_ERROR_UPLOAD_SIZE', [
						'#SIZE#' => \CFile::formatSize(self::getMaxUploadSize())
					]))
				);
			}
			else
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_ERROR_TARFILE')));
			}
		}

		if ($success)
		{
			$result['SUMMARY'] = Loc::getMessage('TR_IMPORT_UPLOAD_OK');
		}

		$result['STATUS'] = Translate\Controller\STATUS_COMPLETED;

		return $result;
	}



	/**
	 * Moves uploaded csv file into bxtmp folder.
	 *
	 * @param array $postedFile Uploaded file data from $_FILES.
	 * @param string $suffix Append file name with suffix.
	 * @param int $timeToLive Time to live in hours.
	 *
	 * @return boolean
	 */
	private function moveUploadedFile($postedFile, $suffix = '.tar', $timeToLive = 3)
	{
		if (
			isset($postedFile['tmp_name']) &&
			\file_exists($postedFile['tmp_name'])
		)
		{
			/** @var Translate\IO\File $tmpFile */
			$tmpFile = Translate\IO\File::generateTemporalFile('translate', $suffix, $timeToLive);
			if (@\copy($postedFile['tmp_name'], $tmpFile->getPhysicalPath()))
			{
				$this->archiveFileType = $suffix;
				$this->archiveFilePath = $tmpFile->getPhysicalPath();
				return true;
			}
		}

		$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_EMPTY_FILE_ERROR')));

		return false;
	}


	/**
	 * Deletes temporal folder and files.
	 *
	 * @return array
	 */
	public function finalizeAction()
	{
		$settings = $this->getProgressParameters();

		// delete tmp files
		if (!empty($settings['tmpFolderPath']))
		{
			$tempLanguageDir = new Translate\IO\Directory($settings['tmpFolderPath']);
			if ($tempLanguageDir->isExists())
			{
				if ($tempLanguageDir->delete() !== true)
				{
					$this->addError(new Main\Error(Loc::getMessage('TR_ERROR_DELETE_TEMP_FOLDER')));
				}
			}
		}

		return array(
			'STATUS' => Translate\Controller\STATUS_COMPLETED
		);
	}


	/**
	 * Deletes generated file.
	 *
	 * @return array
	 */
	public function clearAction()
	{
		return $this->purgeAction();
	}


	/**
	 * Deletes generated file.
	 *
	 * @return array
	 */
	public function purgeAction()
	{
		$settings = $this->getProgressParameters();

		if (!empty($settings['archiveFilePath']))
		{
			$path = new Main\IO\File($settings['archiveFilePath']);
			if ($path->isExists())
			{
				$path->delete();
			}
		}

		return array(
			'SUMMARY' => Loc::getMessage('TR_EXPORT_FILE_DROPPED'),
			'STATUS' => Translate\Controller\STATUS_COMPLETED
		);
	}


	/**
	 * Deletes generated file.
	 *
	 * @return array
	 */
	public function cancelAction()
	{
		$this->finalizeAction();
		$this->purgeAction();
		$this->clearProgressParameters();

		$cancelingAction = $this->request->get('cancelingAction');
		$summary =
			\in_array($cancelingAction, [self::ACTION_COLLECT, self::ACTION_PACK]) ?
				Loc::getMessage('TR_EXPORT_ACTION_CANCELED') :
				Loc::getMessage('TR_IMPORT_ACTION_CANCELED')
		;

		return array(
			'SUMMARY' => $summary,
			'STATUS' => Translate\Controller\STATUS_COMPLETED
		);
	}


	/**
	 * Starts downloading genereted file.
	 *
	 * @return \Bitrix\Main\HttpResponse|void
	 */
	public function downloadAction()
	{
		$settings = $this->getProgressParameters();

		if (!empty($settings['downloadParams']['filePath']) && !empty($settings['downloadParams']['fileName']))
		{
			$file = new Main\IO\File($settings['downloadParams']['filePath']);
			if ($file->isExists())
			{
				$response = new Main\Engine\Response\File(
					$file->getPath(),
					$settings['downloadParams']['fileName'],
					$settings['downloadParams']['fileType']
				);

				return $response;
			}
		}

		$this->addError(new Error('File not found'));
	}


	/**
	 * Returns progress option name
	 *
	 * @return string
	 */
	public function getProgressParameterOptionName()
	{
		return self::SETTING_ID;
	}


	/**
	 * Gets actual maximum upload size.
	 *
	 * @return int
	 */
	public static function getMaxUploadSize()
	{
		static $maxUploadSize = -1;
		if ($maxUploadSize < 0)
		{
			$maxUploadSize = \min(
				\CUtil::unformat('32M'),
				\CUtil::unformat(\ini_get('post_max_size')),
				\CUtil::unformat(\ini_get('upload_max_filesize'))
			);
		}

		return $maxUploadSize;
	}
}
