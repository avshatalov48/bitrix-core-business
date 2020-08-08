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
use Bitrix\Rest\AppLogTable;
use Bitrix\Rest\AppTable;
use Bitrix\Disk\Internals\ObjectTable;
use Bitrix\Disk\Internals\FolderTable;
use Bitrix\Disk\SystemUser;
use Bitrix\Disk\Driver;

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
		if(!\CRestUtil::isAdmin())
		{
			$this->errors->setError(new Error(Loc::getMessage('REST_CONFIGURATION_INSTALL_ACCESS_DENIED')));
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
			'UNINSTALL_APP_ON_FINISH'
		];
	}

	protected function isPreInstallAppMode()
	{
		return $this->request->getQuery('create_install') === 'Y';
	}

	protected function prepareResult()
	{
		$result = [
			'MANIFEST' => [],
			'NEED_CLEAR_FULL' => true,
			'PRE_INSTALL_APP_MODE' => false,
			'NEED_START_BTN' => true,
			'NEED_CLEAR_FULL_CONFIRM' => true
		];
		$manifest = null;

		if(!empty($this->arParams['IMPORT_MANIFEST']['CODE']))
		{
			$manifest = Manifest::get($this->arParams['IMPORT_MANIFEST']['CODE']);
			if(!is_null($manifest))
			{
				if(intVal($manifest['VERSION']) < intVal($this->arParams['IMPORT_MANIFEST']['VERSION']))
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

		if(!is_null($manifest))
		{
			$result['MANIFEST'] = $manifest;
			$result['NEED_CLEAR_FULL_CONFIRM'] = $manifest['DISABLE_CLEAR_FULL'] == 'Y' ? false : true;
			$result['NEED_CLEAR_FULL'] = $result['NEED_CLEAR_FULL_CONFIRM'];
			$result['NEED_START_BTN'] = $manifest['DISABLE_NEED_START_BTN'] == 'Y' && !$manifest['NEED_CLEAR_FULL_CONFIRM'] ? false : true;
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

	protected function getItemContent($type, $step)
	{
		$result = [];
		if($this->diskFolder)
		{
			$folder = $this->diskFolder->getChild(
				[
					'=NAME' => $type
				]
			);
			if($folder)
			{
				$fakeSecurityContext = Driver::getInstance()->getFakeSecurityContext();
				$fileList = $folder->getChildren(
					$fakeSecurityContext,
					[
						'filter' => [
							'=TYPE' => ObjectTable::TYPE_FILE
						]
					]
				);
				$i = 0;
				foreach($fileList as $child)
				{
					if($i == $step && $child instanceof \Bitrix\Disk\File)
					{
						$server = Application::getInstance()->getContext()->getServer();
						$documentRoot = $server->getDocumentRoot();
						$result['FILE_NAME'] = $child->getName();
						$filePath = $documentRoot.\CFile::GetPath(
								$child->getFileId()
							);
						break;
					}
					$i++;
				}
				$result['COUNT'] = count($fileList);
			}
		}
		else
		{
			if(!empty($this->arParams['IMPORT_PATH']))
			{
				$folder = $this->arParams['IMPORT_PATH'];
			}
			else
			{
				$structure = new \Bitrix\Rest\Configuration\Structure($this->getUserContext());
				$folder = $structure->getFolder();
			}

			if(is_dir($folder.$type))
			{
				$fileList = array_values(array_diff(scandir($folder . $type), ['.', '..']));
				$result['COUNT'] = count($fileList);
				if(isset($fileList[$step]))
				{
					$result['FILE_NAME'] = $fileList[$step];
					$filePath = $folder . $type . '/' . $result['FILE_NAME'];
				}
			}
		}

		if(!empty($filePath) && file_exists($filePath))
		{
			$step++;
			$content = file_get_contents($filePath);
			try
			{
				$result['DATA'] = Json::decode($content);
			}
			catch (ArgumentException $e)
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

			$result['SANITIZE'] = false;
			$result['~DATA'] = $result['DATA'];
			$result['DATA'] = Helper::getInstance()->sanitize($result['DATA'], $result['SANITIZE']);
			if($result['SANITIZE'])
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
		if($result['FILE_NAME'])
		{
			$result['FILE_NAME'] = preg_replace('/('.Helper::CONFIGURATION_FILE_EXTENSION.')$/i', '', $result['FILE_NAME']);
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
		if($this->checkRequiredParams())
		{
			$section = Controller::getEntityCodeList();
			$result['section'] = array_values($section);

			if(!empty($this->arParams['APP']))
			{
				$setting = new Setting($this->getUserContext());
				$setting->set(Setting::SETTING_APP_INFO, $this->arParams['APP']);
			}

			if(Helper::getInstance()->isBasicManifest($this->arParams['MANIFEST_CODE']))
			{
				$usesApp = Helper::getInstance()->getBasicApp($this->arParams['MANIFEST_CODE']);
				if($usesApp === false && $this->arParams['MODE'] != 'ROLLBACK' &&  Loader::includeModule('disk'))
				{
					$result['next'] = 'save';
					$setting = new Setting($this->getUserContext($this->savedActionUserContextPostfix));
					$setting->deleteFull();
					$this->deleteBackupFolder();
				}
			}

			if($this->arParams['IMPORT_DISK_FOLDER_ID'] && $this->arParams['IMPORT_DISK_STORAGE_PARAMS'])
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
		if(!Loader::includeModule('disk'))
		{
			return false;
		}
		$storage = Helper::getInstance()->getStorageBackup();
		if($storage)
		{
			$folderName = $this->getImportContext();
			$folder = $storage->getChild(
				[
					'=NAME' => $folderName,
					'TYPE' => FolderTable::TYPE_FOLDER
				]
			);
			if($folder)
			{
				global $USER;
				if (!$folder->deleteTree($USER->GetID()))
				{
					$this->setActionError($folder->getErrors());
				}
			}
		}

		return true;
	}

	private function addDiskBackupFiles($files)
	{
		if(is_array($files))
		{
			$structure = new Structure($this->getUserContext($this->savedActionUserContextPostfix));

			$storage = Helper::getInstance()->getStorageBackup();
			if($storage)
			{
				$folderName = $this->getImportContext();
				$folder = $storage->getChild(
					[
						'=NAME' => $folderName,
						'TYPE' => FolderTable::TYPE_FOLDER
					]
				);
				if(!$folder)
				{
					$folder = $storage->addFolder(
						[
							'NAME' => $folderName,
							'CREATED_BY' => SystemUser::SYSTEM_USER_ID,
						]
					);
				}

				$subFolder = $folder->getChild(
					[
						'=NAME' => Helper::STRUCTURE_FILES_NAME,
						'TYPE' => FolderTable::TYPE_FOLDER
					]
				);
				if(!$subFolder)
				{
					$subFolder = $folder->addSubFolder(
						[
							'NAME' => Helper::STRUCTURE_FILES_NAME,
							'CREATED_BY' => SystemUser::SYSTEM_USER_ID
						]
					);
				}

				if($subFolder)
				{
					foreach ($files as $file)
					{
						if($file['ID'])
						{
							$id = intVal($file['ID']);
							$structure->saveFile($id, $file);

							$fileData = \CFile::MakeFileArray($id);
							$subFolder->uploadFile(
								$fileData,
								[
									'NAME' => $id,
									'CREATED_BY' => SystemUser::SYSTEM_USER_ID
								]
							);
						}
					}
				}
			}
		}
	}

	private function addDiskBackupContent($type, $code, $content)
	{
		$return = false;
		if(is_array($content))
		{
			$content = Json::encode($content);
		}
		elseif(!is_string($content))
		{
			return $return;
		}
		$name = $code.Helper::CONFIGURATION_FILE_EXTENSION;
		$storage = Helper::getInstance()->getStorageBackup();
		if($storage)
		{
			$folderName = $this->getImportContext();
			$folder = $storage->getChild(
				[
					'=NAME' => $folderName,
					'TYPE' => FolderTable::TYPE_FOLDER
				]
			);
			if(!$folder)
			{
				$folder = $storage->addFolder(
					[
						'NAME' => $folderName,
						'CREATED_BY' => SystemUser::SYSTEM_USER_ID,
					]
				);
			}
			if($type !== false)
			{
				$subFolder = $folder->getChild(
					[
						'=NAME' => $type,
						'TYPE' => FolderTable::TYPE_FOLDER
					]
				);
				if(!$subFolder)
				{
					$folder = $folder->addSubFolder(
						[
							'NAME' => $type,
							'CREATED_BY' => SystemUser::SYSTEM_USER_ID
						]
					);
				}
				else
				{
					$folder = $subFolder;
				}
			}

			if ($folder)
			{
				$file = $folder->uploadFile(
					[
						'name' => $name,
						'content' => $content,
						'type' => 'application/json',
					],
					[
						'NAME' => $name,
						'CREATED_BY' => SystemUser::SYSTEM_USER_ID
					]
				);
				if($file)
				{
					$return = true;
				}
			}
		}

		return $return;
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
			try
			{
				$storage = Driver::getInstance()->addStorageIfNotExist(
					$this->arParams['IMPORT_DISK_STORAGE_PARAMS']
				);
				if($storage)
				{
					$folder = $storage->getChild(
						[
							'=ID' => $this->arParams['IMPORT_DISK_FOLDER_ID']
						]
					);
				}
			}
			catch (\Exception $e)
			{
			}
		}
		return $folder;
	}

	public function finishAction()
	{
		$result = [
			'result' => false,
			'createItemList' => []
		];
		if($this->checkRequiredParams())
		{
			if(!empty($this->arParams['APP']['ID']))
			{
				if($this->arParams['APP']['INSTALLED'] == AppTable::NOT_INSTALLED)
				{
					AppTable::setSkipRemoteUpdate(true);
					$updateResult = AppTable::update(
						$this->arParams['APP']['ID'],
						[
							'INSTALLED' => AppTable::INSTALLED
						]
					);
					AppTable::setSkipRemoteUpdate(false);
					AppTable::install($this->arParams['APP']['ID']);
					AppLogTable::log($this->arParams['APP']['ID'], AppLogTable::ACTION_TYPE_INSTALL);
					$result['result'] = $updateResult->isSuccess();
				}
				else
				{
					$result['result'] = true;
				}
				Helper::getInstance()->setBasicApp($this->arParams['MANIFEST_CODE'], $this->arParams['APP']['CODE']);
			}
			else
			{
				Helper::getInstance()->deleteBasicApp($this->arParams['MANIFEST_CODE']);
			}

			if(!empty($this->arParams['UNINSTALL_APP_ON_FINISH']))
			{
				$res = AppTable::getList(
					[
						'filter' => array(
							"=CODE" => $this->arParams['UNINSTALL_APP_ON_FINISH'],
							"!=STATUS" => AppTable::STATUS_LOCAL
						),
					]
				);
				if($appInfo = $res->fetch())
				{
					$clean = true;
					$checkResult = AppTable::checkUninstallAvailability($appInfo['ID'], $clean);
					if($checkResult->isEmpty())
					{
						$result['result'] = true;
						AppTable::uninstall($appInfo['ID'], $clean);
						$appFields = [
							'ACTIVE' => 'N',
							'INSTALLED' => 'N',
						];
						AppTable::update($appInfo['ID'], $appFields);
						AppLogTable::log($appInfo['ID'], AppLogTable::ACTION_TYPE_UNINSTALL);
					}
				}
			}

			$setting = new Setting($this->getUserContext());
			$ratio = $setting->get(Setting::SETTING_RATIO);
			$app = $setting->get(Setting::SETTING_APP_INFO);
			$eventResult = Controller::callEventFinish(
				[
					'TYPE' => 'IMPORT',
					'CONTEXT' => $this->getImportContext(),
					'CONTEXT_USER' => $this->getUserContext(),
					'RATIO' => $ratio,
					'MANIFEST_CODE' => $this->arParams['MANIFEST_CODE'],
					'IMPORT_MANIFEST' => $this->arParams['IMPORT_MANIFEST'],
					'APP_ID' => ($app['ID'] > 0) ? $app['ID'] : 0
				]
			);

			foreach ($eventResult as $data)
			{
				if(is_array($data['CREATE_DOM_LIST']))
				{
					$result['createItemList'] = array_merge($result['createItemList'], $data['CREATE_DOM_LIST']);
				}
			}

		}

		$result['notice'] = $this->getActionError();
		return $result;
	}

	public function clearAction()
	{
		$result = [];
		if($this->checkRequiredParams() && Loader::includeModule('disk'))
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$code = preg_replace('/[^a-zA-Z0-9_]/', '', $request->getPost("code"));
			$step = intVal( $request->getPost("step"));
			$next = htmlspecialcharsbx( $request->getPost("next"));
			$clearFull = $request->getPost("clear") == 'true'?:false;

			if($code)
			{
				$data = Controller::callEventClear(
					[
						'CODE' => $code,
						'STEP' => $step,
						'NEXT' => $next,
						'CONTEXT' => $this->getImportContext(),
						'CONTEXT_USER' => $this->getUserContext(),
						'CLEAR_FULL' => $clearFull,
						'PREFIX_NAME' => Loc::getMessage("REST_CONFIGURATION_INSTALL_CLEAR_PREFIX_NAME"),
						'MANIFEST_CODE' => $this->arParams['MANIFEST_CODE'],
						'IMPORT_MANIFEST' => $this->arParams['IMPORT_MANIFEST']
					]
				);

				if ($data['ERROR_MESSAGES'])
				{
					$result['errors'] = is_array($data['ERROR_MESSAGES']) ? $data['ERROR_MESSAGES'] : [$data['ERROR_MESSAGES']];
				}
				if ($data['ERROR_ACTION'])
				{
					$this->setActionError($data['ERROR_ACTION']);
				}

				if ($data['ERROR_EXCEPTION'])
				{
					$result['exception'] = $data['ERROR_EXCEPTION'];
				}

				if(!isset($data['NEXT']))
				{
					$result['next'] = false;
				}
				else
				{
					$result['next'] = $data['NEXT'];
				}
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
		if($this->checkRequiredParams() && Loader::includeModule('disk'))
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$code = preg_replace('/[^a-zA-Z0-9_]/', '', $request->getPost("code"));
			$step = intVal( $request->getPost("step"));
			if ($code)
			{
				$content = $this->getItemContent($code, $step);

				if ($content['COUNT'] > $step)
				{
					$result['result'] = false;
				}

				if ($content['DATA'])
				{
					$setting = new Setting($this->getUserContext());
					$ratio = $setting->get(Setting::SETTING_RATIO);
					$dataList = Controller::callEventImport(
						[
							'CODE' => $code,
							'CONTENT' => $content,
							'RATIO' => $ratio,
							'CONTEXT' => $this->getImportContext(),
							'CONTEXT_USER' => $this->getUserContext(),
							'MANIFEST_CODE' => $this->arParams['MANIFEST_CODE'],
							'IMPORT_MANIFEST' => $this->arParams['IMPORT_MANIFEST']
						]
					);

					foreach ($dataList as $data)
					{
						if (is_array($data['RATIO']))
						{
							if (!$ratio[$code])
							{
								$ratio[$code] = [];
							}
							foreach ($data['RATIO'] as $old => $new)
							{
								$ratio[$code][$old] = $new;
							}
						}
						if ($data['ERROR_EXCEPTION'])
						{
							$result['exception'] = $data['ERROR_EXCEPTION'];
						}
						if ($data['ERROR_MESSAGES'])
						{
							$result['errors'] = is_array($data['ERROR_MESSAGES']) ? $data['ERROR_MESSAGES'] : [$data['ERROR_MESSAGES']];
						}
						if ($data['ERROR_ACTION'])
						{
							$this->setActionError($data['ERROR_ACTION']);
						}
					}

					$setting->set(Setting::SETTING_RATIO, $ratio);
				}
			}
		}
		$result['notice'] = $this->getActionError();

		return $result;
	}

	public function saveAction()
	{
		$result = [];
		if($this->checkRequiredParams())
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$code = preg_replace('/[^a-zA-Z0-9_]/', '', $request->getPost("code"));
			$step = intVal($request->getPost("step"));
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
			$step = intVal($request->getPost("step"));
			$next = htmlspecialcharsbx($request->getPost("next"));
			$type = $request->getPost("type");
			if($type != 'import')
			{
				$type = 'EXPORT';
				$contextUser = $this->getUserContext($this->savedActionUserContextPostfix);
			}
			else
			{
				$type = 'IMPORT';
				$contextUser = $this->getUserContext();
			}

			$items = Manifest::callEventInit(
				$this->arParams['MANIFEST_CODE'],
				[
					'TYPE' => $type ,
					'STEP' => $step,
					'NEXT' => $next,
					'ITEM_CODE' => $this->arParams['ITEM_CODE'],
					'CONTEXT_USER' => $contextUser
				]
			);
			foreach ($items as $item)
			{
				if ($item['ERROR_MESSAGES'])
				{
					$result['errors'][] = $item['ERROR_MESSAGES'];
				}
				if ($item['ERROR_ACTION'])
				{
					$result['notice'][] = $item['ERROR_ACTION'];
				}

				$result['next'] = $item['NEXT'];
			}
		}
		return $result;
	}

	public function finishSaveAction()
	{
		$structure = new Structure($this->getUserContext($this->savedActionUserContextPostfix));
		$manifest = Manifest::get($this->arParams['MANIFEST_CODE']);
		if(!is_null($manifest))
		{
			$manifest = [
				'CODE' => $manifest['CODE'],
				'VERSION' => $manifest['VERSION'],
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

	public function configureActions()
	{
		return [
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