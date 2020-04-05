<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Rest\Marketplace\Client;
use Bitrix\Rest\AppLogTable;

Loc::loadMessages(__FILE__);
class CRestConfigurationImportComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;
	protected $type = 'configuration';

	protected function checkRequiredParams()
	{
		if(!\CRestUtil::isAdmin())
		{
			$this->errors->setError(new Error(Loc::getMessage('REST_CONFIGURATION_IMPORT_ACCESS_DENIED')));
			return false;
		}
		return true;
	}

	private function getTmpFolder($code)
	{
		$sTmpFolderPath = CTempFile::GetDirectoryName(
			4,
			[
				'rest',
				uniqid($this->type . '_import_', true),
				$code
			]
		);
		CheckDirPath($sTmpFolderPath);
		return $sTmpFolderPath;
	}

	protected function prepareResult()
	{
		if(isset($this->arParams['SET_TITLE']) && $this->arParams['SET_TITLE'] == 'Y')
		{
			global $APPLICATION;
			if($this->arParams['MODE'] == 'ROLLBACK')
			{
				$APPLICATION->SetTitle(Loc::getMessage('REST_CONFIGURATION_IMPORT_ROLLBACK_TITLE'));
			}
			else
			{
				$APPLICATION->SetTitle(Loc::getMessage('REST_CONFIGURATION_IMPORT_TITLE'));
			}
		}

		$result = [
			'IMPORT_ACCESS' => false,
			'IMPORT_FOLDER_FILES' => '',
			'IMPORT_MANIFEST_FILE' => []
		];

		if($this->arParams['MODE'] == 'ROLLBACK')
		{
			$expertMode = ($this->request->getQuery('expert') && $this->request->getQuery('expert') == 'Y')? true : false;
			$result['ROLLBACK_ITEMS'] = [];
			$result['USES_APP'] = Helper::getInstance()->getUsesConfigurationApp();

			$storage = Helper::getInstance()->getStorageBackup();
			if($storage)
			{
				$fakeSecurityContext = \Bitrix\Disk\Driver::getInstance()->getFakeSecurityContext();
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

			$res = AppTable::getList(
				[
					'filter' => [
						'!=STATUS' => AppTable::STATUS_LOCAL,
						'=INSTALLED' => AppTable::INSTALLED,
						'=ACTIVE' => AppTable::ACTIVE,
					],
					'select' => [
						'ID', 'CODE', 'VERSION', 'APP_NAME'
					]
				]
			);
			while($app = $res->fetch())
			{
				if(
					AppTable::getAppType($app['CODE'], $app['VERSION']) == AppTable::TYPE_CONFIGURATION
					&& $app['CODE'] != $result['USES_APP']
				)
				{
					$result['ROLLBACK_ITEMS'][] = [
						'ID' => $app['ID'],
						'NAME' => $app['APP_NAME'],
						'CODE' => $app['CODE'],
						'IS_DEFAULT' => 'N'
					];
				}
			}

			if(empty($result['ROLLBACK_ITEMS']) && $result['USES_APP'] != '')
			{
				$res = AppTable::getList(
					[
						'filter' => array(
							"=CODE" => $result['USES_APP'],
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
						$result = true;
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
					$result['IMPORT_ROLLBACK_DISK_FOLDER_ID'] =$item['CODE'];
					$result['IMPORT_ROLLBACK_STORAGE_PARAMS'] = Helper::getInstance()->getStorageBackupParam();
				}
			}

			if($result['USES_APP'] != '')
			{
				$result['UNINSTALL_APP_ON_FINISH'] = $result['USES_APP'];
			}
		}

		if(!empty($this->arParams['APP']))
		{
			$app = AppTable::getByClientId($this->arParams['APP']);
			if($app['ACTIVE'] == 'Y')
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

				if($appInfo)
				{
					$appInfo = $appInfo["ITEMS"];
					if($appInfo['TYPE'] === AppTable::TYPE_CONFIGURATION && !empty($appInfo['CONFIG_URL']))
					{
						$appConfigurationName = 'app'.$app['CODE'];
						$sTmpFolderPath = $this->getTmpFolder($appConfigurationName);
						$filePath = $sTmpFolderPath.$appConfigurationName;

						$httpClient = new HttpClient;
						$fileContent = $httpClient->get($appInfo['CONFIG_URL']);
						file_put_contents($filePath, $fileContent);
						$archive = CBXArchive::GetArchive($filePath, 'TAR.GZ');
						$res = $archive->Unpack($sTmpFolderPath);
						if($res)
						{
							$result['APP'] = $app;
							$result['IMPORT_ACCESS'] = true;
							$result['IMPORT_FOLDER_FILES'] = $sTmpFolderPath;
							unlink($filePath);
						}
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
				$result['ERRORS_UPLOAD_FILE'] = CFile::CheckFile(
					$_FILES["CONFIGURATION"],
					0,
					[
						'application/gzip',
						'application/x-gzip'
					],
					'gz,tar'
				);
				if($result['ERRORS_UPLOAD_FILE'] === '')
				{
					try
					{
						$fileContent = file_get_contents($_FILES["CONFIGURATION"]["tmp_name"]);
						$configurationName = 'app';
						$sTmpFolderPath = $this->getTmpFolder($configurationName);
						$filePath = $sTmpFolderPath.$configurationName;
						file_put_contents($filePath, $fileContent);

						$archive = CBXArchive::GetArchive( $filePath, 'TAR.GZ');
						$res = $archive->Unpack($sTmpFolderPath);
						if ($res)
						{
							$result['IMPORT_FOLDER_FILES'] = $sTmpFolderPath;
							unlink($filePath);
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
			$fileList = scandir($result['IMPORT_FOLDER_FILES']);
			$key = array_search('manifest.json', $fileList);
			if($key !== false)
			{
				$data = file_get_contents($result['IMPORT_FOLDER_FILES'].$fileList[$key]);
				try
				{
					$result['IMPORT_MANIFEST_FILE'] = Json::decode($data);
				}
				catch(ArgumentException $e)
				{
				}
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