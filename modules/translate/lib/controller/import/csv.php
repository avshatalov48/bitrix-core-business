<?php
namespace Bitrix\Translate\Controller\Import;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


class Csv
	extends Translate\Controller\Controller
	implements Translate\Controller\IProcessParameters
{
	use Translate\Controller\ProcessParams;

	const SETTING_ID = 'TRANSLATE_IMPORT';

	const ACTION_IMPORT = 'import';
	const ACTION_PURGE = 'purge';
	const ACTION_CANCEL = 'cancel';
	const ACTION_UPLOAD = 'upload';
	const ACTION_INDEX = 'index';
	const ACTION_FINALIZE = 'finalize';

	const METHOD_ADD_UPDATE = 'ADD_UPDATE';
	const METHOD_UPDATE_ONLY = 'UPDATE_ONLY';
	const METHOD_ADD_ONLY = 'ADD_ONLY';

	/** @var int Session tab counter. */
	private $tabId = 0;

	/** @var string */
	private $encodingIn;

	/** @var string */
	private $updateMethod;

	/** @var string[] */
	private $languages;

	/** @var string */
	private $csvFilePath;

	/** @var boolean */
	private $reindex;



	/**
	 * Configures actions.
	 *
	 * @return array
	 */
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$permission = new Translate\Controller\CheckPermission(Translate\Permission::WRITE);

		$configureActions[self::ACTION_UPLOAD] = [
			'+prefilters' => [
				new Main\Engine\ActionFilter\HttpMethod([Main\Engine\ActionFilter\HttpMethod::METHOD_POST]),
				$permission
			],
		];
		$configureActions[self::ACTION_IMPORT] = [
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_PURGE] = [
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_CANCEL] = [
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_FINALIZE] = [
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_INDEX] = [
			'+prefilters' => [
				new Translate\Controller\CheckPermission(Translate\Permission::READ)
			],
		];

		return $configureActions;
	}


	/**
	 * Initializes controller.
	 *
	 * @return void
	 * @throws Main\ArgumentException
	 */
	protected function init()
	{
		parent::init();

		$tabId = $this->request->get('tabId');
		if (empty($tabId) || (int)$tabId <= 0)
		{
			throw new Main\ArgumentException("Missing 'tabId' parameter");
		}
		$this->tabId = (int)$tabId;

		$this->keepField(['encodingIn', 'updateMethod', 'csvFilePath', 'languages']);
		$params = $this->getProgressParameters();

		// languages
		$this->languages = Translate\Config::getEnabledLanguages();

		//  encoding
		$enc = $this->request->get('encodingIn');
		if ($enc !== null && \in_array(\mb_strtolower($enc), Translate\Config::getAllowedEncodings()))
		{
			$this->encodingIn = \mb_strtolower($enc);
		}
		elseif (isset($params['encodingIn']) && \in_array($params['encodingIn'], Translate\Config::getAllowedEncodings()))
		{
			$this->encodingIn = $params['encodingIn'];
		}

		// update method
		$updateMethod = $this->request->get('updateMethod');
		if ($updateMethod !== null)
		{
			if (\in_array($updateMethod, [self::METHOD_ADD_ONLY, self::METHOD_UPDATE_ONLY, self::METHOD_ADD_UPDATE]))
			{
				$this->updateMethod = $updateMethod;
			}
		}
		if (empty($this->updateMethod) && isset($params['updateMethod']))
		{
			$this->updateMethod = $params['updateMethod'];
		}
		if (empty($this->updateMethod))
		{
			$this->updateMethod = self::METHOD_ADD_ONLY;
		}

		// update index
		$reindex = $this->request->get('reindex');
		$this->reindex = ($reindex === 'Y');

			// file to import
		if (isset($params['csvFilePath']))
		{
			$this->csvFilePath = $params['csvFilePath'];
		}

		$this->saveProgressParameters();
	}


	/**
	 * Runs controller import action.
	 *
	 * @return array
	 */
	public function importAction(): array
	{
		$action = new Translate\Controller\Import\ImportCsv(
			self::ACTION_IMPORT,
			$this,
			[
				'tabId' => $this->tabId,
				'encodingIn' => $this->encodingIn,
				'updateMethod' => $this->updateMethod,
				'csvFilePath' => $this->csvFilePath,
			]
		);

		$result = $action->run(true);

		if (\count($action->getErrors()) > 0)
		{
			$this->addErrors($action->getErrors());
		}

		if ($action instanceof Translate\Controller\ITimeLimit)
		{
			if ($action->hasProcessCompleted() && $result['TOTAL_ITEMS'] == 0)
			{
				$result['SUMMARY'] = Loc::getMessage('TR_IMPORT_VOID');
			}
			else
			{
				$messagePlaceholders = [
					'#TOTAL_PHRASES#' => $result['TOTAL_ITEMS'],
					'#PROCESSED_PHRASES#' => $result['PROCESSED_ITEMS'],
				];
				if ($action->hasProcessCompleted())
				{
					$result['SUMMARY'] =
						Loc::getMessage('TR_IMPORT_COMPLETED')."\n".
						Loc::getMessage('TR_IMPORT_ACTION_STATS', $messagePlaceholders);
				}
				else
				{
					$result['SUMMARY'] = Loc::getMessage('TR_IMPORT_ACTION_STATS', $messagePlaceholders);
				}
			}
		}
		else
		{
			if ($result['TOTAL_ITEMS'] == 0)
			{
				$result['SUMMARY'] = Loc::getMessage('TR_IMPORT_VOID');
			}
			else
			{
				$messagePlaceholders = [
					'#TOTAL_PHRASES#' => $result['TOTAL_ITEMS'],
					'#PROCESSED_PHRASES#' => $result['PROCESSED_ITEMS'],
				];

				$result['SUMMARY'] =
					Loc::getMessage('TR_IMPORT_COMPLETED')."\n".
					Loc::getMessage('TR_IMPORT_ACTION_STATS', $messagePlaceholders);
			}
		}

		return $result;
	}

	/**
	 * Runs controller index action.
	 *
	 * @return array
	 */
	public function indexAction(): array
	{
		if ($this->reindex !== true)
		{
			return [
				'STATUS' => Translate\Controller\STATUS_COMPLETED,
				'SUMMARY' => Loc::getMessage('TR_IMPORT_COMPLETED')
			];
		}

		$action = new Translate\Controller\Import\IndexCsv(
			self::ACTION_INDEX,
			$this,
			[
				'tabId' => $this->tabId,
				'csvFilePath' => $this->csvFilePath,
			]
		);

		$result = $action->run(true);

		if (\count($action->getErrors()) > 0)
		{
			$this->addErrors($action->getErrors());
		}

		if ($action instanceof Translate\Controller\ITimeLimit)
		{
			if ($action->hasProcessCompleted())
			{
				$result['SUMMARY'] = Loc::getMessage('TR_IMPORT_COMPLETED');
			}
			else
			{
				$messagePlaceholders = [
					'#TOTAL_FILES#' => $result['TOTAL_ITEMS'],
					'#PROCESSED_FILES#' => $result['PROCESSED_ITEMS'],
				];
				$result['SUMMARY'] = Loc::getMessage('TR_INDEX_ACTION_STATS', $messagePlaceholders);
			}
		}
		else
		{
			$result['SUMMARY'] = Loc::getMessage('TR_IMPORT_COMPLETED');
		}

		return $result;
	}


	/**
	 * Handles uploaded file.
	 *
	 * @return array
	 */
	public function uploadAction(): array
	{
		$result = [];
		$success = false;
		if (
			isset($_FILES['csvFile'], $_FILES['csvFile']['tmp_name'])
			&& ($_FILES['csvFile']['error'] == 0)
			&& \file_exists($_FILES['csvFile']['tmp_name'])
		)
		{
			if (
				(\filesize($_FILES['csvFile']['tmp_name']) > 0)
				&& (\mb_substr($_FILES['csvFile']['name'], -4) === '.csv')
			)
			{
				if ($this->moveUploadedFile($_FILES['csvFile'], '.csv'))
				{
					$this->saveProgressParameters();
					$success = true;
				}
			}
			else
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_EMPTY_FILE_ERROR')));
			}
		}
		else
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_EMPTY_FILE_ERROR')));
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
	private function moveUploadedFile($postedFile, $suffix = '.csv', $timeToLive = 3): bool
	{
		if (
			isset($postedFile['tmp_name'])
			&& \file_exists($postedFile['tmp_name'])
		)
		{
			/** @var Translate\IO\CsvFile $csvFile */
			$tmpFile = Translate\IO\CsvFile::generateTemporalFile('translate', $suffix, $timeToLive);
			if (@\copy($postedFile['tmp_name'], $tmpFile->getPhysicalPath()))
			{
				$this->csvFilePath = $tmpFile->getPhysicalPath();
				return true;
			}
		}

		$this->addError(new Main\Error(Loc::getMessage('TR_IMPORT_EMPTY_FILE_ERROR')));

		return false;
	}


	/**
	 * Deletes genereted file.
	 *
	 * @param int $tabId Id of session storage.
	 *
	 * @return array
	 */
	public function cancelAction($tabId): array
	{
		$result = $this->purgeAction($tabId);
		$result['SUMMARY'] = Loc::getMessage('TR_IMPORT_ACTION_CANCEL');

		return $result;
	}


	/**
	 * Deletes genereted file.
	 *
	 * @param int $tabId Id of session storage.
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	public function purgeAction($tabId): array
	{
		if (empty($tabId) || (int)$tabId <= 0)
		{
			throw new Main\ArgumentException("Missing 'tabId' parameter");
		}

		$settings = $this->getProgressParameters();

		if (!empty($settings['csvFilePath']))
		{
			$path = new Main\IO\File($settings['csvFilePath']);
			if ($path->isExists())
			{
				$path->delete();
			}
		}

		$this->clearProgressParameters();

		return [
			'SUMMARY' => Loc::getMessage('TR_IMPORT_FILE_DROPPED'),
			'STATUS' => Translate\Controller\STATUS_COMPLETED
		];
	}

	/**
	 * Deletes genereted file.
	 *
	 * @return array
	 */
	public function finalizeAction(): array
	{
		$settings = $this->getProgressParameters();

		if (!empty($settings['csvFilePath']))
		{
			$path = new Main\IO\File($settings['csvFilePath']);
			if ($path->isExists())
			{
				$path->delete();
			}
		}

		$this->clearProgressParameters();

		return [
			'STATUS' => Translate\Controller\STATUS_COMPLETED
		];
	}
}
