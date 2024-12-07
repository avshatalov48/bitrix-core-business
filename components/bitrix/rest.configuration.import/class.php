<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Error;
USE Bitrix\Main\IO\File;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Configuration\Manifest;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Rest\Configuration\Setting;
use Bitrix\Rest\Configuration\Structure;
use Bitrix\Rest\Configuration\Action\Import;
use Bitrix\Rest\Marketplace\Client;
use Bitrix\Rest\AppLogTable;
use Bitrix\Disk\Driver;

Loc::loadMessages(__FILE__);
class CRestConfigurationImportComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;
	protected $contextPostfix = 'import';
	protected $type = 'configuration';

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function getContextPostFix()
	{
		return $this->contextPostfix.$this->arParams['MANIFEST_CODE'].$this->arParams['APP'];
	}

	protected function getContext()
	{
		return Helper::getInstance()->getContextUser($this->getContextPostFix());
	}

	protected function prepareConfigurationUrl($url)
	{
		if ("UTF-8" !== mb_strtoupper(LANG_CHARSET))
		{
			$uri = new Uri($url);
			$path = $uri->getPath();

			$name = bx_basename($path);
			$prepareName = $name;
			$prepareName = rawurlencode($prepareName);

			$path = str_replace($name, $prepareName, $path);

			$uri->setPath($path);
			$url = $uri->getUri();
		}

		return $url;
	}

	private function getArchive($url, $app = [])
	{
		$result = [];
		$fileInfo = \CFile::MakeFileArray($url);

		if (!empty($fileInfo['tmp_name']))
		{
			$result['ERRORS_UPLOAD_FILE'] = \CFile::CheckFile(
				$fileInfo,
				0,
				[
					'application/gzip',
					'application/x-gzip',
					'application/zip',
					'application/x-zip-compressed',
					'application/x-tar'
				]
			);

			if ($result['ERRORS_UPLOAD_FILE'] === '')
			{
				$context = $this->getContext();

				$setting = new Setting($context);
				$setting->deleteFull();

				$structure = new Structure($context);
				if ($structure->unpack($fileInfo))
				{
					$result['IMPORT_CONTEXT'] = $context;
					$result['APP'] = $app;
					$result['IMPORT_FOLDER_FILES'] = $structure->getFolder();
					$result['IMPORT_ACCESS'] = true;
				}
			}
		}

		return $result;
	}

	private function registerImport(array $app, array $config)
	{
		$result = [];
		if (!empty($config['MANIFEST']['CODE']))
		{
			$additional = [];
			if (!empty($this->arParams['ADDITIONAL']) && is_array($this->arParams['ADDITIONAL']))
			{
				$additional = $this->arParams['ADDITIONAL'];
			}

			$userId = 0;
			global $USER;
			if ($USER->isAuthorized())
			{
				$userId = $USER->getId();
			}

			$import = new Import();
			$register = $import->register(
				$config,
				$additional,
				$userId,
				$app['CODE'] ?? '',
				false
			);
			if ($register['processId'] > 0)
			{
				$result['IMPORT_PROCESS_ID'] = $register['processId'];
				$result['IMPORT_ACCESS'] = true;
				$result['APP'] = $app;
				$result['MANIFEST_CODE'] = $config['MANIFEST']['CODE'];
			}
			else
			{
				$this->errors->setError(new Error(Loc::getMessage('REST_CONFIGURATION_IMPORT_ERROR_PROCESS_REGISTRATION')));
				return false;
			}
		}
		else
		{
			$this->errors->setError(new Error(Loc::getMessage('REST_CONFIGURATION_IMPORT_MANIFEST_NOT_FOUND')));
			return false;
		}

		return $result;
	}

	private function isManifestAccess($code = '')
	{
		$access = Manifest::checkAccess(Manifest::ACCESS_TYPE_IMPORT, $code);
		if ($access['result'] !== true)
		{
			$this->errors->setError(
				new Error(
					$access['message'] !== ''
						? htmlspecialcharsbx($access['message'])
						: Loc::getMessage('REST_CONFIGURATION_IMPORT_ACCESS_DENIED')
				)
			);

			return false;
		}

		return true;
	}

	protected function prepareResult()
	{
		$result = [
			'IMPORT_ACCESS' => false,
			'IMPORT_FOLDER_FILES' => '',
			'IMPORT_MANIFEST_FILE' => [],
			'MANIFEST' => [],
			'MANIFEST_CODE' => '',
			'FROM' => $this->arParams['FROM'] ?? '',
		];
		$title = '';

		$result['MAX_FILE_SIZE']['MEGABYTE'] = Helper::getInstance()->getMaxFileSize();
		$result['MAX_FILE_SIZE']['BYTE'] = round($result['MAX_FILE_SIZE']['MEGABYTE']*1024*1024, 2);

		if(!empty($this->arParams['MANIFEST_CODE']))
		{
			$result['MANIFEST'] = Manifest::get($this->arParams['MANIFEST_CODE']);
			if(is_null($result['MANIFEST']))
			{
				$this->errors->setError(new Error(Loc::getMessage('REST_CONFIGURATION_IMPORT_MANIFEST_NOT_FOUND')));
				return false;
			}
			else
			{
				$result['MANIFEST_CODE'] = $result['MANIFEST']['CODE'];
			}
		}

		if (isset($this->arParams['MODE']) && $this->arParams['MODE'] == 'ROLLBACK')
		{
			if (!$this->isManifestAccess($result['MANIFEST_CODE']))
			{
				return false;
			}

			$expertMode = ($this->request->getQuery('expert') && $this->request->getQuery('expert') == 'Y') ? true : false;
			$result['ROLLBACK_ITEMS'] = [];
			$appList = Helper::getInstance()->getBasicAppList();
			$manifestCode = array_search($this->arParams['ROLLBACK_APP'], $appList);
			if($manifestCode !== false)
			{
				$storage = Helper::getInstance()->getStorageBackup();
				if($storage)
				{
					$fakeSecurityContext = Driver::getInstance()->getFakeSecurityContext();
					foreach($storage->getChildren($fakeSecurityContext, []) as $child)
					{
						if($child instanceof \Bitrix\Disk\Folder)
						{
							$createTime = $child->getCreateTime();
							$result['ROLLBACK_ITEMS'][] = [
								'ID' => 'DEFAULT_' . $child->getId(),
								'CODE' => $child->getId(),
								'NAME' => Loc::getMessage(
									'REST_CONFIGURATION_ROLLBACK_DEFAULT_TITLE',
									[
										'#CREATE_TIME#' => $createTime->toString()
									]
								),
								'IS_DEFAULT' => 'Y'
							];
						}
					}
				}
			}

			if(empty($result['ROLLBACK_ITEMS']) && $manifestCode !== false)
			{
				$res = AppTable::getList(
					[
						'filter' => array(
							"=CODE" => $appList[$manifestCode],
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
						AppTable::uninstall($appInfo['ID'], $clean);
						$appFields = [
							'ACTIVE' => 'N',
							'INSTALLED' => 'N',
						];
						AppTable::update($appInfo['ID'], $appFields);
						AppLogTable::log($appInfo['ID'], AppLogTable::ACTION_TYPE_UNINSTALL);
						unset($result);
						$result['DELETE_FINISH'] = true;
					}
				}
			}
			elseif(check_bitrix_sessid())
			{
				$id = $this->request->getPost("ROLLBACK_ID");
				if($id !== NULL)
				{
					$key = array_search($id, array_column($result['ROLLBACK_ITEMS'],'ID'));
					if($key !== false)
					{
						$result['ROLLBACK_SELECTED'] = $result['ROLLBACK_ITEMS'][$key];
						if($result['ROLLBACK_SELECTED']['IS_DEFAULT'] == 'N')
						{
							$this->arParams['APP'] = $result['ROLLBACK_SELECTED']['CODE'];
						}
						else
						{
							$result['IMPORT_ROLLBACK_DISK_FOLDER_ID'] = $result['ROLLBACK_SELECTED']['CODE'];
							$result['IMPORT_ROLLBACK_STORAGE_PARAMS'] = Helper::getInstance()->getStorageBackupParam();
						}

					}
				}
			}

			if(!$expertMode && !empty($result['ROLLBACK_ITEMS']))
			{
				$item = reset($result['ROLLBACK_ITEMS']);
				if($item['IS_DEFAULT'] == 'N')
				{
					$this->arParams['APP'] = $item['CODE'];
				}
				else
				{
					$result['IMPORT_ROLLBACK_DISK_FOLDER_ID'] = $item['CODE'];
					$result['IMPORT_ROLLBACK_STORAGE_PARAMS'] = Helper::getInstance()->getStorageBackupParam();
				}
			}

			if($manifestCode !== false)
			{
				$result['UNINSTALL_APP_ON_FINISH'] = $appList[$manifestCode];
			}
		}

		if (
			isset($this->arParams['MODE'])
			&& $this->arParams['MODE'] === 'ZIP'
			&& (int)$this->arParams['ZIP_ID'] > 0
		)
		{
			$site = Client::getSite((int)$this->arParams['ZIP_ID']);
			if (!$this->isManifestAccess($site['CONFIG']['MANIFEST']['CODE'] ?? ''))
			{
				return false;
			}

			$app = AppTable::getByClientId($site['APP_CODE']);

			if (
				!empty($app['ACTIVE'])
				&& $app['ACTIVE'] === 'Y'
				&& !empty($app['INSTALLED'])
				&& $app['INSTALLED'] === 'Y'
			)
			{
				if (!empty($site['CONFIG']))
				{
					$registerResult = $this->registerImport($app, $site['CONFIG']);
					if ($registerResult === false)
					{
						return $result;
					}
					else
					{
						$result = array_merge($result, $registerResult);
					}
				}
				elseif (!empty($site['PATH']))
				{
					$result = array_merge($result, $this->getArchive($site['PATH'], $app));
				}
			}
			else
			{
				$result['INSTALL_APP'] = $site['APP_CODE'];
				$title = Loc::getMessage('REST_CONFIGURATION_IMPORT_PREPARATION_TITLE');
			}
		}
		elseif (!empty($this->arParams['APP']))
		{
			if (!$this->isManifestAccess($result['MANIFEST_CODE']))
			{
				return false;
			}

			$app = AppTable::getByClientId($this->arParams['APP']);
			if ($app['ACTIVE'] === 'Y')
			{
				$request = Application::getInstance()->getContext()->getRequest();
				$check_hash = $request->getQuery("check_hash");
				$install_hash = $request->getQuery("install_hash");
				$appInfo = Client::getApp(
					$app['CODE'],
					$app['VERSION'],
					($check_hash)?:false,
					($install_hash)?:false
				);

				if ($appInfo)
				{
					$appInfo = $appInfo["ITEMS"];

					if (
						($appInfo['TYPE'] === AppTable::TYPE_CONFIGURATION || $appInfo['TYPE'] === AppTable::TYPE_BIC_DASHBOARD)
						&& !empty($appInfo['CONFIG_URL'])
					)
					{
						$url = $this->prepareConfigurationUrl($appInfo['CONFIG_URL']);
						$result = array_merge($result, $this->getArchive($url, $app));
					}
				}
			}
		}
		else
		{
			$result['IMPORT_ACCESS'] = true;

			if(
				!empty($_FILES["CONFIGURATION"])
				&& file_exists($_FILES["CONFIGURATION"]["tmp_name"])
				&& check_bitrix_sessid()
			)
			{
				if (!$this->isManifestAccess($result['MANIFEST_CODE']))
				{
					return false;
				}

				$result['ERRORS_UPLOAD_FILE'] = CFile::CheckFile(
					$_FILES["CONFIGURATION"],
					$result['MAX_FILE_SIZE']['BYTE'],
					[
						'application/gzip',
						'application/x-gzip',
						'application/zip',
						'application/x-zip-compressed',
						'application/x-tar'
					],
					'gz,tar,zip'
				);

				if($result['ERRORS_UPLOAD_FILE'] === '')
				{
					try
					{
						$context = $this->getContext();

						$setting = new Setting($context);
						$setting->deleteFull();

						$structure = new Structure($context);
						if($structure->unpack($_FILES["CONFIGURATION"]))
						{
							$result['IMPORT_CONTEXT'] = $context;
							$result['IMPORT_FOLDER_FILES'] = $structure->getFolder();
						}
					}
					catch (\Exception $e)
					{
						$result['ERRORS_UPLOAD_FILE'] = $e->getMessage();
					}
				}
			}
		}

		if($result['IMPORT_FOLDER_FILES'])
		{
			if (!$this->isManifestAccess($result['MANIFEST_CODE']))
			{
				return false;
			}

			$fileList = scandir($result['IMPORT_FOLDER_FILES']);
			$key = array_search('manifest.json', $fileList);
			if($key !== false)
			{
				$data = file_get_contents($result['IMPORT_FOLDER_FILES'].$fileList[$key]);
				try
				{
					$result['IMPORT_MANIFEST_FILE'] = Json::decode($data);
					if(!empty($result['IMPORT_MANIFEST_FILE']))
					{
						if(!empty($result['MANIFEST']))
						{
							if($result['IMPORT_MANIFEST_FILE']['CODE'] != $result['MANIFEST']['CODE'])
							{
								$this->errors->setError(new Error(Loc::getMessage('REST_CONFIGURATION_IMPORT_MANIFEST_NOT_CURRENT')));
								return false;
							}
							else
							{
								$result['MANIFEST_CODE'] = htmlspecialcharsbx($result['IMPORT_MANIFEST_FILE']['CODE']);
							}
						}
						elseif(!empty($result['APP']['ID']))
						{
							$result['MANIFEST_CODE'] = htmlspecialcharsbx($result['IMPORT_MANIFEST_FILE']['CODE']);
						}
					}
				}
				catch(ArgumentException $e)
				{
				}
			}
			else
			{
				$this->errors->setError(new Error(Loc::getMessage('REST_CONFIGURATION_IMPORT_MANIFEST_NOT_CURRENT')));
				return false;
			}
		}
		elseif(
			!empty($result['IMPORT_ROLLBACK_DISK_FOLDER_ID'])
			&& !empty($result['IMPORT_ROLLBACK_STORAGE_PARAMS'])
		)
		{
			if (!$this->isManifestAccess($result['MANIFEST_CODE']))
			{
				return false;
			}

			try
			{
				$storage = Driver::getInstance()->addStorageIfNotExist(
					$result['IMPORT_ROLLBACK_STORAGE_PARAMS']
				);
				if($storage)
				{
					$folder = $storage->getChild(
						[
							'=ID' => $result['IMPORT_ROLLBACK_DISK_FOLDER_ID']
						]
					);
					if($folder)
					{
						$file = $folder->getChild(
							[
								'=NAME' => 'manifest.json'
							]
						);
						if($file && $file->getFileId() > 0)
						{
							$server = Application::getInstance()->getContext()->getServer();
							$documentRoot = $server->getDocumentRoot();
							$filePath = $documentRoot.\CFile::GetPath(
									$file->getFileId()
							);
							if(File::isFileExists($filePath))
							{
								$manifestContent =  File::getFileContents($filePath);
								if($manifestContent != '')
								{
									$manifest = Json::decode($manifestContent);
									if($manifest['CODE'])
									{
										$result['MANIFEST_CODE'] = $manifest['CODE'];
										$result['IMPORT_MANIFEST_FILE'] = $manifest;
									}
								}
							}
						}
					}
				}
			}
			catch (\Exception $e)
			{
			}
		}

		if (isset($this->arParams['SET_TITLE']) && $this->arParams['SET_TITLE'] == 'Y')
		{
			global $APPLICATION;
			if (isset($this->arParams['MODE']) && $this->arParams['MODE'] == 'ROLLBACK')
			{
				$APPLICATION->SetTitle(Loc::getMessage('REST_CONFIGURATION_IMPORT_ROLLBACK_TITLE'));
			}
			else
			{
				if (empty($result['MANIFEST']))
				{
					if (!empty($result['IMPORT_MANIFEST_FILE']['CODE']))
					{
						$result['MANIFEST'] = Manifest::get($result['IMPORT_MANIFEST_FILE']['CODE']);
					}
					elseif (!empty($result['MANIFEST_CODE']))
					{
						$result['MANIFEST'] = Manifest::get($result['MANIFEST_CODE']);
					}
				}

				if ($title === '')
				{
					if (!empty($result['MANIFEST']['IMPORT_TITLE_PAGE']))
					{
						$title = $result['MANIFEST']['IMPORT_TITLE_PAGE'];
					}
					else
					{
						$title = Loc::getMessage('REST_CONFIGURATION_IMPORT_TITLE');
					}
				}
				$APPLICATION->SetTitle($title);
			}
		}

		$this->arResult = $result;
		return true;
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
		$this->errors = new ErrorCollection();

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

}
