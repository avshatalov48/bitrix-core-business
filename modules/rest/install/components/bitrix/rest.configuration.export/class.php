<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\Configuration\Setting;
use Bitrix\Rest\Configuration\Controller;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Rest\Configuration\Manifest;
use Bitrix\Rest\Configuration\Structure;

class CRestConfigurationExportComponent extends CBitrixComponent implements Controllerable
{
	/** @var ErrorCollection $errors */
	protected $errors;
	protected $type = 'configuration';
	protected $contextPostfix = 'export';
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
		$result = [];
		global $APPLICATION;

		$manifest = Manifest::get($this->arParams['MANIFEST_CODE']);
		if(is_null($manifest))
		{
			$this->errors->setError(new Error(Loc::getMessage("REST_CONFIGURATION_EXPORT_NOT_FOUND")));
			return false;
		}
		$result['MANIFEST'] = $manifest;

		$APPLICATION->SetTitle( $result['MANIFEST']['EXPORT_TITLE_PAGE'] ?: Loc::getMessage('REST_CONFIGURATION_EXPORT_TITLE') );

		$result['ENABLED_ZIP_MODE'] = Helper::getInstance()->enabledZipMod();
		$result['ENABLED_EXPORT'] = $result['ENABLED_ZIP_MODE'];
		if($result['ENABLED_ZIP_MODE'] != 'Y')
		{
			$result['REST_SETTING_PATH'] = BX_ROOT.'/admin/settings.php?lang='.LANGUAGE_ID.'&mid=rest';

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

	protected function getContextPostFix()
	{
		return $this->contextPostfix.$this->arParams['MANIFEST_CODE'];
	}

	protected function getContext()
	{
		return Helper::getInstance()->getContextUser($this->getContextPostFix());
	}

	public function startAction()
	{
		$result = [];
		if($this->checkRequiredParams())
		{
			$context = $this->getContext();

			$setting = new Setting($context);
			$setting->deleteFull();

			$structure = new Structure($context);
			if($structure->getFolder())
			{
				$result = Controller::getEntityCodeList();
			}
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
				$context = $this->getContext();
				$structure = new Structure($context);
				$structure->saveContent(false, 'manifest', $manifest);
				$result['result'] = true;

				$setting = new Setting($context);

				Controller::callEventFinish(
					[
						'TYPE' => 'EXPORT',
						'CONTEXT' => $this->getContextPostFix(),
						'CONTEXT_USER' => $context,
						'MANIFEST_CODE' => $manifest['CODE'],
						'IMPORT_MANIFEST' => [],//TODO: delete this after fix crm
						'MANIFEST' => $manifest,
						'ITEM_CODE' => $this->arParams['ITEM_CODE']
					]
				);

				$setting->delete(Setting::SETTING_MANIFEST);

				$uri = UrlManager::getInstance()->getEndPoint();
				$uri->addParams(
					[
						'action' => 'rest.controller.configuration.download',
						'postfix' => $this->getContextPostFix()
					]
				);
				$result['download'] = $uri->getUri();
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
			$step = intval($request->getPost("step"));
			$next = htmlspecialcharsbx($request->getPost("next"));
			if($code)
			{
				$structure = new Structure($this->getContext());
				$items = Controller::callEventExport(
					$this->arParams['MANIFEST_CODE'],
					$code,
					$step,
					$next,
					$this->arParams['ITEM_CODE'],
					$this->getContext()
				);
				foreach ($items as $item)
				{
					$fileName = !is_array($item['FILE_NAME']) ? (string) $item['FILE_NAME'] : '';
					if ($fileName <> '')
					{
						$structure->saveContent($code, $fileName, $item['CONTENT']);
					}
					if ($item['ERROR_MESSAGES'])
					{
						$result['errors'][] = $item['ERROR_MESSAGES'];
					}
					if ($item['ERROR_ACTION'])
					{
						$result['errorsNotice'][] = $item['ERROR_ACTION'];
					}
					if (isset($item['FILES']) && is_array($item['FILES']))
					{
						foreach ($item['FILES'] as $file)
						{
							if(isset($file['ID']))
							{
								$structure->saveFile($file['ID'], $file);
							}
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
			$items = Manifest::callEventInit(
				$this->arParams['MANIFEST_CODE'],
				[
					'TYPE' => 'EXPORT',
					'STEP' => $step,
					'NEXT' => $next,
					'ITEM_CODE' => $this->arParams['ITEM_CODE'],
					'CONTEXT_USER' => $this->getContext()
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
					$result['errorsNotice'][] = $item['ERROR_ACTION'];
				}

				$result['next'] = $item['NEXT'];
			}
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
			],
			'loadManifest' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf()
				]
			]
		];
	}
}