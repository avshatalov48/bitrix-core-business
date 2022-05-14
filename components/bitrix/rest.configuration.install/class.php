<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Rest\Configuration\Controller;
use Bitrix\Rest\Configuration\Structure;
use Bitrix\Rest\Configuration\Manifest;
use Bitrix\Rest\Configuration\Setting;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Rest\Configuration\DataProvider;
use Bitrix\Disk\Internals\FolderTable;
use Bitrix\Disk\SystemUser;
use Bitrix\Rest\Configuration\Action\Import;
use Bitrix\Rest\Configuration\Notification;

Loc::loadMessages(__FILE__);

class CRestConfigurationInstallComponent extends CBitrixComponent implements Controllerable
{
	/** @var ErrorCollection $errors */
	protected $errors;
	protected $actionError;
	protected $diskFolder = false;
	protected $savedActionUserContextPostfix = 'saved';

	protected function checkRequiredParams()
	{
		$this->errors = new ErrorCollection();

		$manifestCode = '';
		if (!empty($this->arParams['IMPORT_MANIFEST']['CODE']))
		{
			$manifestCode = $this->arParams['IMPORT_MANIFEST']['CODE'];
		}
		elseif ($this->arParams['MANIFEST_CODE'])
		{
			$manifestCode = $this->arParams['MANIFEST_CODE'];
		}
		$access = Manifest::checkAccess(Manifest::ACCESS_TYPE_IMPORT, $manifestCode);
		if ($access['result'] !== true)
		{
			$this->errors->setError(
				new Error(
					$access['message'] !== ''
						? htmlspecialcharsbx($access['message'])
						: Loc::getMessage('REST_CONFIGURATION_INSTALL_ACCESS_DENIED')
				)
			);

			return false;
		}

		if($this->arParams['IMPORT_DISK_FOLDER_ID'])
		{
			$this->diskFolder = $this->getDiskFolder();
			if($this->diskFolder === false)
			{
				$this->errors->setError(new Error(Loc::getMessage("REST_CONFIGURATION_INSTALL_DISK_FOLDER_NOT_FOUND")));
				return false;
			}
		}

		return true;
	}

	protected function listKeysSignedParameters()
	{
		return [
			'IMPORT_PATH',
			'IMPORT_CONTEXT',
			'IMPORT_MANIFEST',
			'IMPORT_DISK_FOLDER_ID',
			'IMPORT_DISK_STORAGE_PARAMS',
			'APP',
			'MODE',
			'MANIFEST_CODE',
			'UNINSTALL_APP_ON_FINISH',
			'PROCESS_ID',
		];
	}

	protected function isPreInstallAppMode()
	{
		return $this->request->getQuery('create_install') === 'Y';
	}

	protected function isImportByProcessId()
	{
		return (int)$this->arParams['PROCESS_ID'] > 0;
	}

	protected function prepareResult()
	{
		$result = [
			'MANIFEST' => [],
			'NEED_CLEAR_FULL' => true,
			'PRE_INSTALL_APP_MODE' => false,
			'SKIP_CLEARING' => false,
			'NEED_START_BTN' => true,
			'NEED_CLEAR_FULL_CONFIRM' => true,
			'IMPORT_BY_PROCESS_ID' => $this->isImportByProcessId(),
		];
		$manifest = null;

		if (!empty($this->arParams['IMPORT_MANIFEST']['CODE']))
		{
			$manifest = Manifest::get($this->arParams['IMPORT_MANIFEST']['CODE']);
			if ((int)$this->arParams['IMPORT_MANIFEST']['VERSION'] > Setting::VERSION)
			{
				$result['NOTIFY'][] = Loc::getMessage("REST_CONFIGURATION_INSTALL_ERROR_MANIFEST_OLD");
			}
			elseif (!is_null($manifest))
			{
				if ((int)$this->arParams['IMPORT_MANIFEST']['MANIFEST_VERSION'] > 0)
				{
					if ((int)$this->arParams['IMPORT_MANIFEST']['MANIFEST_VERSION'] > (int)$manifest['VERSION'])
					{
						$result['NOTIFY'][] = Loc::getMessage("REST_CONFIGURATION_INSTALL_ERROR_MANIFEST_OLD");
					}
				}
				elseif ((int)$this->arParams['IMPORT_MANIFEST']['VERSION'] > (int)$manifest['VERSION'])
				{
					$result['NOTIFY'][] = Loc::getMessage("REST_CONFIGURATION_INSTALL_ERROR_MANIFEST_OLD");
				}
			}
			else
			{
				$result['NOTIFY'][] = Loc::getMessage("REST_CONFIGURATION_INSTALL_ERROR_MANIFEST_NOT_FOUND");
			}
		}
		elseif($this->arParams['MANIFEST_CODE'])
		{
			$manifest = Manifest::get($this->arParams['MANIFEST_CODE']);
		}

		if (!is_null($manifest))
		{
			if (is_array($this->arParams['IMPORT_MANIFEST']))
			{
				$manifest = array_merge($this->arParams['IMPORT_MANIFEST'],  $manifest);
			}

			$result['MANIFEST'] = $manifest;
			if ($result['MANIFEST']['SKIP_CLEARING'] === 'Y')
			{
				$manifest['DISABLE_CLEAR_FULL'] = 'Y';
				$result['SKIP_CLEARING'] = true;
			}
			$result['NEED_CLEAR_FULL_CONFIRM'] = $manifest['DISABLE_CLEAR_FULL'] !== 'Y';
			$result['NEED_CLEAR_FULL'] = $result['NEED_CLEAR_FULL_CONFIRM'];
			$result['NEED_START_BTN'] = !(empty($result['NOTIFY'])
				&& $manifest['DISABLE_NEED_START_BTN'] === 'Y'
				&& !$manifest['NEED_CLEAR_FULL_CONFIRM']);
		}

		if($this->isPreInstallAppMode())
		{
			$result['NEED_START_BTN'] = true;
			$result['NEED_CLEAR_FULL_CONFIRM'] = false;
			$result['PRE_INSTALL_APP_MODE'] = true;
		}

		$this->arResult = $result;
		return true;
	}

	protected function setActionError($message)
	{
		if(is_array($message))
		{
			if(!$this->actionError)
			{
				$this->actionError = [];
			}
			$this->actionError = array_merge($this->actionError, $message);
		}
		else
		{
			$this->actionError[] = $message;
		}

		return true;
	}

	protected function getActionError()
	{
		return $this->actionError;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}

	protected function getItemContent($type, $step): array
	{
		$result = [];
		$count = 0;
		$path = '';
		$providerCode = '';

		if ($this->diskFolder)
		{
			$providerCode = DataProvider\Controller::CODE_DISK;
			$path = $type;
		}
		else
		{
			if (!empty($this->arParams['IMPORT_PATH']))
			{
				$folder = $this->arParams['IMPORT_PATH'];
			}
			else
			{
				$structure = new \Bitrix\Rest\Configuration\Structure($this->getUserContext());
				$folder = $structure->getFolder();
			}

			if (is_dir($folder . $type))
			{
				$fileList = array_values(array_diff(scandir($folder . $type), ['.', '..']));
				$count = count($fileList);
				if (isset($fileList[$step]))
				{
					$providerCode = DataProvider\Controller::CODE_IO;
					$path = $folder . $type . '/' . $fileList[$step];
				}
			}
		}

		if ($providerCode)
		{
			/** @var DataProvider\ProviderBase $disk */
			$provider = DataProvider\Controller::getInstance()->get(
				$providerCode,
				[
					'CONTEXT' => $this->getImportContext(),
					'CONTEXT_USER' => $this->getUserContext(),
				]
			);
			if ($provider)
			{
				$result = $provider->getContent($path, (int)$step);
				if ($result['COUNT'] === 0)
				{
					$result['COUNT'] = $count;
				}

				if ($result['ERROR_CODE'] === $provider::ERROR_DECODE_DATA)
				{
					$this->setActionError(
						Loc::getMessage(
							"REST_CONFIGURATION_INSTALL_FILE_CONTENT_ERROR_DECODE",
							[
								'#STEP#' => $step
							]
						)
					);
				}

				if ($result['SANITIZE'])
				{
					$this->setActionError(
						Loc::getMessage(
							"REST_CONFIGURATION_INSTALL_FILE_CONTENT_ERROR_SANITIZE_SHORT",
							[
								'#STEP#' => $step
							]
						)
					);
				}
			}
		}

		return $result;
	}

	protected function getImportContext()
	{
		$id = !empty($this->arParams['APP']['ID']) ? $this->arParams['APP']['ID'] : 0;
		return Helper::getInstance()->getContextAction($id);
	}

	protected function getUserContext($postfix = false)
	{
		return $this->arParams['IMPORT_CONTEXT'].(($postfix !== false) ? $postfix : '');
	}

	public function startAction()
	{
		$result = [];
		if ($this->checkRequiredParams())
		{
			$import = new Import();
			$import->setContext($this->getUserContext());
			$import->getNotificationInstance()->clean();
			$result = $import->doStart(
				$this->arParams['APP'],
				$this->arParams['MODE'] ?? Helper::MODE_IMPORT,
				[
					'UNINSTALL_APP_ON_FINISH' => $this->arParams['UNINSTALL_APP_ON_FINISH'],
				]
			);

			if (Helper::getInstance()->isBasicManifest($this->arParams['MANIFEST_CODE']))
			{
				$usesApp = Helper::getInstance()->getBasicApp($this->arParams['MANIFEST_CODE']);
				if (
					$usesApp === false
					&& $this->arParams['MODE'] !== Helper::MODE_ROLLBACK
					&& Loader::includeModule('disk')
				)
				{
					$result['next'] = 'save';
					$setting = new Setting(
						$this->getUserContext(
							$this->savedActionUserContextPostfix
						)
					);
					$setting->deleteFull();
					$this->deleteBackupFolder();
				}
			}

			if ($this->arParams['IMPORT_DISK_FOLDER_ID'] && $this->arParams['IMPORT_DISK_STORAGE_PARAMS'])
			{
				$structure = new Structure($this->getUserContext());
				$structure->setUnpackFilesFromDisk($this->arParams['IMPORT_DISK_FOLDER_ID'], $this->arParams['IMPORT_DISK_STORAGE_PARAMS']);
			}
		}

		$result['notice'] = $this->getActionError();

		return $result;
	}

	private function deleteBackupFolder()
	{
		$result = false;

		/** @var DataProvider\Disk\Base $disk */
		$disk = DataProvider\Controller::getInstance()->get(
			DataProvider\Controller::CODE_DISK,
			[
				'CONTEXT' => $this->getImportContext(),
				'CONTEXT_USER' => $this->getUserContext(),
			]
		);
		if ($disk)
		{
			$result = $disk->deleteFolder();
			if (!$result)
			{
				$this->setActionError($disk->listError());
			}
		}

		return $result;
	}

	private function addDiskBackupFiles($files)
	{
		$result = [
			'success' => false,
		];
		if (is_array($files))
		{
			/** @var DataProvider\Disk\Base $disk */
			$disk = DataProvider\Controller::getInstance()->get(
				DataProvider\Controller::CODE_DISK,
				[
					'CONTEXT' => $this->getImportContext(),
					'CONTEXT_USER' => $this->getUserContext(),
				]
			);
			if ($disk)
			{
				$result = $disk->addFiles($files);
			}
		}

		return $result;
	}

	private function addDiskBackupContent($type, $code, $content)
	{
		$result = false;
		/** @var DataProvider\Disk\Base $disk */
		$disk = DataProvider\Controller::getInstance()->get(
			DataProvider\Controller::CODE_DISK,
			[
				'CONTEXT' => $this->getImportContext(),
				'CONTEXT_USER' => $this->getUserContext(),
			]
		);
		if ($disk)
		{
			$result = $disk->addContent($type, $code, $content);
		}

		return $result;
	}

	private function getDiskFolder()
	{
		$folder = false;
		if(
			!empty($this->arParams['IMPORT_DISK_FOLDER_ID'])
			&& !empty($this->arParams['IMPORT_DISK_STORAGE_PARAMS'])
			&& Loader::includeModule('disk')
		)
		{
			/** @var DataProvider\Disk\Base $disk */
			$disk = DataProvider\Controller::getInstance()->get(
				DataProvider\Controller::CODE_DISK,
				[
					'CONTEXT' => $this->getImportContext(),
					'CONTEXT_USER' => $this->getUserContext(),
				]
			);
			$disk->setFolderFilter(
				[
					'=ID' => $this->arParams['IMPORT_DISK_FOLDER_ID'],
				]
			);
			$folder = $disk->getFolder();
		}
		return $folder;
	}

	public function finishAction()
	{
		$result = [
			'result' => false,
			'createItemList' => []
		];
		if ($this->checkRequiredParams())
		{
			$import = new Import();
			$import->setContext($this->getUserContext());
			$import->setManifestCode($this->arParams['MANIFEST_CODE']);
			$result = $import->doFinish(
				$this->arParams['MODE'] ?? Helper::MODE_IMPORT
			);
		}

		$result['notice'] = $this->getActionError();
		return $result;
	}

	public function clearAction()
	{
		$result = [];
		if($this->checkRequiredParams())
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$code = preg_replace('/[^a-zA-Z0-9_]/', '', $request->getPost('code'));
			$step = (int) $request->getPost('step');
			$next = htmlspecialcharsbx($request->getPost('next'));
			$clearFull = $request->getPost('clear') == 'true' ? : false;

			if ($code)
			{
				$import = new Import();
				$import->setContext($this->getUserContext());
				$import->setManifestCode($this->arParams['MANIFEST_CODE']);
				$result = $import->doClean(
					$code,
					$step,
					$next,
					$clearFull
				);
				$result = array_merge(
					$result,
					$this->getNotification(
						$import->getNotificationInstance()
					)
				);
			}
		}

		$result['notice'] = $this->getActionError();

		return $result;
	}

	public function importAction()
	{
		$result = [
			'result' => true
		];
		$import = new Import();
		$import->setContext($this->getUserContext());
		if ($this->checkRequiredParams())
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$code = preg_replace('/[^a-zA-Z0-9_]/', '', $request->getPost("code"));
			$step = (int) $request->getPost("step");
			if ($code)
			{
				$content = $this->getItemContent($code, $step);
				$import->setManifestCode($this->arParams['MANIFEST_CODE']);
				$result = $import->doLoad(
					$step,
					$code,
					$content
				);
			}
		}

		return array_merge(
			$result,
			$this->getNotification(
				$import->getNotificationInstance()
			)
		);
	}

	public function saveAction()
	{
		$result = [];
		if($this->checkRequiredParams())
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$code = preg_replace('/[^a-zA-Z0-9_]/', '', $request->getPost("code"));
			$step = intval($request->getPost("step"));
			$next = htmlspecialcharsbx($request->getPost("next"));
			if($code)
			{
				$manifest = $this->arParams['MANIFEST_CODE'] != '' ? $this->arParams['MANIFEST_CODE'] : Helper::TYPE_SECTION_TOTAL;
				$items = Controller::callEventExport($manifest, $code, $step, $next);
				foreach ($items as $item)
				{
					if($item['FILE_NAME'] != '')
					{
						$this->addDiskBackupContent($code, $item['FILE_NAME'], $item['CONTENT']);
						if($item['FILES'])
						{
							$this->addDiskBackupFiles($item['FILES']);
						}
					}
					$result['next'] = $item['NEXT'];
				}
			}
		}
		if(!isset($result['next']))
		{
			$result['next'] = false;
		}

		$result['notice'] = $this->getActionError();

		return $result;
	}

	public function loadManifestAction()
	{
		$result = [
			'next' => false
		];

		if($this->checkRequiredParams())
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$step = intval($request->getPost("step"));
			$next = htmlspecialcharsbx($request->getPost("next"));
			$type = $request->getPost("type");
			if($type !== 'import')
			{
				$type = 'EXPORT';
				$contextUser = $this->getUserContext($this->savedActionUserContextPostfix);
			}
			else
			{
				$type = 'IMPORT';
				$contextUser = $this->getUserContext();
			}

			$import = new Import();
			$import->setContext($contextUser);
			$import->setManifestCode($this->arParams['MANIFEST_CODE']);
			$result = $import->doInitManifest(
				$next,
				$step,
				$type,
			);

			$result = array_merge(
				$result,
				$this->getNotification(
					$import->getNotificationInstance()
				)
			);

		}

		return $result;
	}

	private function getNotification(Notification $notification): array
	{
		$result = [];
		$result['errors'] = $notification->list(
			[
				'type' => Notification::TYPE_ERROR,
			]
		);
		$result['notice'] = $notification->list(
			[
				'type' => Notification::TYPE_NOTICE,
			]
		);
		$result['exception'] = $notification->list(
			[
				'type' => Notification::TYPE_EXCEPTION,
			]
		);

		return array_filter(
			$result,
			function ($value)
			{
				return !empty($value);
			}
		);
	}

	public function finishSaveAction()
	{
		$structure = new Structure($this->getUserContext($this->savedActionUserContextPostfix));
		$manifest = Manifest::get($this->arParams['MANIFEST_CODE']);
		if(!is_null($manifest))
		{
			$manifest = [
				'CODE' => $manifest['CODE'],
				'VERSION' => Setting::VERSION,
				'MANIFEST_VERSION' => $manifest['VERSION'],
				'USES' => $manifest['USES']
			];
			$this->addDiskBackupContent(false, 'manifest', $manifest);
		}
		$this->addDiskBackupContent(false, Helper::STRUCTURE_FILES_NAME, $structure->getFileList());
		return [
			'result' => true
		];
	}

	public function preInstallOffAction()
	{
		Option::set("rest", "import_configuration_app", '');
		return [
			'result' => true
		];
	}

	/**
	 * Does all steps by process id.
	 *
	 * @return array
	 */
	public function runAction(): array
	{
		$result = [
			'finish' => true,
		];

		if ((int)$this->arParams['PROCESS_ID'] > 0)
		{
			$import = new Import((int)$this->arParams['PROCESS_ID']);
			$isRun = $import->run();
			$result = $import->get();
			$result['step'] = $result['progress']['action'] ?? 'finish';
			if ($isRun && in_array($result['status'], [Import::STATUS_START, Import::STATUS_PROCESS]))
			{
				$result['finish'] = false;
			}
			else
			{
				$result['finish'] = true;
			}
			$import->getNotificationInstance()->clean();
		}

		return $result;
	}

	public function configureActions()
	{
		return [
			'run' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[
							ActionFilter\HttpMethod::METHOD_POST,
						]
					),
					new ActionFilter\Csrf(),
				],
			],
			'start' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'finish' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'clear' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'import' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'save' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf()
				],
				'postfilters' => [

				]
			],
			'finishSave' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf()
				],
				'postfilters' => [

				]
			],
			'loadManifest' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf()
				]
			],
			'preInstallOff' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf()
				]
			],
		];
	}
}
