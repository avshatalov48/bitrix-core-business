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
use Bitrix\Main\Application;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Rest\Configuration\Controller;
use Bitrix\Rest\Configuration\Manifest;
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
			'IMPORT_MANIFEST',
			'IMPORT_DISK_FOLDER_ID',
			'IMPORT_DISK_STORAGE_PARAMS',
			'APP',
			'MODE',
			'UNINSTALL_APP_ON_FINISH'
		];
	}

	protected function prepareResult()
	{
		$result = [];

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
			$folder = $this->arParams['IMPORT_PATH'];
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
			$bad = false;
			$result['DATA'] = Helper::getInstance()->sanitize($result['DATA'], $bad);
			if($bad)
			{
				unset($result['DATA']);
				$this->setActionError(
					Loc::getMessage(
						"REST_CONFIGURATION_INSTALL_FILE_CONTENT_ERROR_SANITIZE",
						[
							'#STEP#' => $step
						]
					)
				);
			}
		}
		if($result['FILE_NAME'])
		{
			$result['FILE_NAME'] = preg_replace('/(.json)$/i', '', $result['FILE_NAME']);
		}

		return $result;
	}

	protected function getImportContext()
	{
		$result = 'external';
		if(!empty($this->arParams['APP']['ID']))
		{
			$result = Helper::getInstance()->prefixAppContext.$this->arParams['APP']['ID'];
		}
		return $result;
	}

	public function startAction()
	{
		$result = [];
		if($this->checkRequiredParams())
		{
			$section = Controller::getEntityCodeList();
			$result['section'] = array_values($section);
			$usesApp = Helper::getInstance()->getUsesConfigurationApp();
			Helper::getInstance()->deleteRatio();
			if($usesApp == '' && Loader::includeModule('disk'))
			{
				$result['next'] = 'save';
				$this->deleteBackupFolder();
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
		$name = $code.'.json';
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
			'result' => false
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
				Helper::getInstance()->setUsesConfigurationApp($this->arParams['APP']['CODE']);
			}
			else
			{
				Helper::getInstance()->deleteUsesConfigurationApp();
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
		}
		$result['errorsNotice'] = $this->getActionError();
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
						'CLEAR_FULL' => $clearFull,
						'PREFIX_NAME' => Loc::getMessage("REST_CONFIGURATION_INSTALL_CLEAR_PREFIX_NAME")
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

				if(!isset($data['NEXT']))
				{
					$result['NEXT'] = false;
				}
				else
				{
					$result['next'] = $data['NEXT'];
				}
			}
		}

		$result['errorsNotice'] = $this->getActionError();

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
					$ratio = Helper::getInstance()->getRatio();
					$dataList = Controller::callEventImport(
						$code,
						$content,
						$ratio,
						$this->getImportContext()
					);

					foreach ($dataList as $data)
					{
						if ($data['RATIO'])
						{
							Helper::getInstance()->addRatio($code, $data['RATIO']);
						}
						if ($data['ERROR_MESSAGES'])
						{
							$result['errors'] = $data['ERROR_MESSAGES'];
						}
						if ($data['ERROR_ACTION'])
						{
							$this->setActionError($data['ERROR_ACTION']);
						}
					}
				}
			}
		}
		$result['errorsNotice'] = $this->getActionError();

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
				$items = Controller::callEventExport(Helper::TYPE_SECTION_TOTAL, $code, $step, $next);
				foreach ($items as $item)
				{
					if($item['FILE_NAME'] != '')
					{
						$this->addDiskBackupContent($code, $item['FILE_NAME'], $item['CONTENT']);
					}
					$result['next'] = $item['NEXT'];
				}
			}
		}
		if(!isset($result['next']))
		{
			$result['next'] = false;
		}

		$result['errorsNotice'] = $this->getActionError();

		return $result;
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
			]
		];
	}
}