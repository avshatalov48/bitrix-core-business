<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\Configuration\Controller;
use Bitrix\Rest\Configuration\Manifest;

class CRestConfigurationExportComponent extends CBitrixComponent implements Controllerable
{
	/** @var ErrorCollection $errors */
	protected $errors;
	protected $type = 'configuration';
	protected $optionPath = '~tmp_export_path_configuration';

	protected function checkRequiredParams()
	{
		if(!\CRestUtil::isAdmin())
		{
			$this->errors->setError(new Error(Loc::getMessage('REST_CONFIGURATION_EXPORT_ACCESS_DENIED')));
			return false;
		}

		if(empty($this->arParams['MANIFEST_CODE']))
		{
			$this->errors->setError(new Error(Loc::getMessage("REST_CONFIGURATION_EXPORT_MANIFEST_EMPTY")));
			return false;
		}
		return true;
	}

	protected function listKeysSignedParameters()
	{
		return [
			'MANIFEST_CODE',
			'ITEM_CODE'
		];
	}

	protected function prepareResult()
	{
		global $APPLICATION;

		$manifest = Manifest::get($this->arParams['MANIFEST_CODE']);
		if(is_null($manifest))
		{
			$this->errors->setError(new Error(Loc::getMessage("REST_CONFIGURATION_EXPORT_NOT_FOUND")));
			return false;
		}
		$result = [
			'DOWNLOAD_URL' => $APPLICATION->GetCurPageParam('download=Y', ['download']),
			'MANIFEST' => $manifest
		];

		$APPLICATION->SetTitle( $result['MANIFEST']['EXPORT_TITLE_PAGE'] ?: Loc::getMessage('REST_CONFIGURATION_EXPORT_TITLE') );
		$request = Application::getInstance()->getContext()->getRequest();
		$download = $request->getQuery("download");

		if($download == 'Y')
		{
			$folder = Option::get('rest', $this->optionPath,'');
			if(file_exists($folder) && is_dir($folder))
			{
				$dir = array_diff(scandir($folder),['.','..']);
				if(empty($dir))
				{
					$this->errors->setError(new Error(Loc::getMessage("REST_CONFIGURATION_EXPORT_ERROR_ARCHIVE_NOT_FOUND")));
					return false;
				}
				$name = $this->type.'.tar.gz';
				$archive = CBXArchive::GetArchive($folder.$name, 'TAR.GZ');
				$archive->SetOptions(
					array(
						"COMPRESS"			=> false,
						"ADD_PATH"			=> false,
						"REMOVE_PATH"		=> $folder,
						"CHECK_PERMISSIONS" => false
					)
				);
				$res = $archive->Pack($folder);
				if($res === IBXArchive::StatusSuccess)
				{
					$APPLICATION->restartBuffer();
					if(!headers_sent())
					{
						header('Content-Type: application/tar');
						header("Content-Disposition: attachment; filename=\"".$name."\"");
						header("Content-Length: ".filesize($folder.$name));
						readfile($folder.$name);
					}
					unlink($folder.$name);
					exit();
				}
				else
				{
					$this->errors->setError(new Error(Loc::getMessage("REST_CONFIGURATION_EXPORT_ERROR_ARCHIVE_NOT_FOUND")));
					return false;
				}
			}
			else
			{
				$this->errors->setError(new Error(Loc::getMessage("REST_CONFIGURATION_EXPORT_ERROR_ARCHIVE_NOT_FOUND")));
				return false;
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

	protected function saveContent($type, $code, $content)
	{
		$return = false;
		$tmpPath = Option::get('rest', $this->optionPath,'');
		if(!$tmpPath)
		{
			return $return;
		}

		try
		{
			$path = $tmpPath . ($type === false ? '' : $type . '/');
			if(is_array($content))
			{
				$content = Json::encode($content);
			}
			elseif(!is_string($content))
			{
				return $return;
			}

			if (CheckDirPath($path))
			{
				$name = $path.$code.'.json';

				if(file_put_contents($name, $content) !== false)
				{
					$return = true;
				}
			}
		}
		catch (\Exception $e)
		{
		}

		return $return;
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

	public function startAction()
	{
		$result = [];
		if($this->checkRequiredParams())
		{
			//on start export creat folder
			$sTmpFolderPath = CTempFile::GetDirectoryName(
				4,
				[
					'rest',
					uniqid($this->type . '_export_', true)
				]
			);
			CheckDirPath($sTmpFolderPath);
			Option::set('rest', $this->optionPath, $sTmpFolderPath);
			$result = Controller::getEntityCodeList();
		}
		return $result;
	}

	public function finishAction()
	{
		$result = [
			'result' => false
		];
		if($this->checkRequiredParams())
		{
			$manifest = Manifest::get($this->arParams['MANIFEST_CODE']);
			if(!is_null($manifest))
			{
				$manifest = [
					'CODE' => $manifest['CODE'],
					'VERSION' => $manifest['VERSION'],
					'USES' => $manifest['USES']
				];
				$this->saveContent(false, 'manifest', $manifest);
				$result['result'] = true;
			}
		}

		return $result;
	}

	public function loadAction()
	{
		$result = [];

		if($this->checkRequiredParams())
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$code = preg_replace('/[^a-zA-Z0-9_]/', '', $request->getPost("code"));
			$step = htmlspecialcharsbx($request->getPost("step"));
			$next = intVal($request->getPost("next"));
			if($code)
			{
				$items = Controller::callEventExport(
					$this->arParams['MANIFEST_CODE'],
					$code,
					$step,
					$next,
					$this->arParams['ITEM_CODE']
				);
				foreach ($items as $item)
				{
					if($item['FILE_NAME'] != '')
					{
						$this->saveContent($code, $item['FILE_NAME'], $item['CONTENT']);
					}
					if ($item['ERROR_MESSAGES'])
					{
						$result['errors'][] = $item['ERROR_MESSAGES'];
					}
					if ($item['ERROR_ACTION'])
					{
						$result['errorsNotice'][] = $item['ERROR_ACTION'];
					}

					$result['next'] = $item['NEXT'];
				}
			}
		}

		if(!isset($result['next']))
		{
			$result['next'] = false;
		}

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
			'load' => [
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
			'finish' => [
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