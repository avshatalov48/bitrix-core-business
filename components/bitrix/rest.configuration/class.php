<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Rest\Configuration\Manifest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;;

Loc::loadMessages(__FILE__);

class RestConfigurationComponent extends CBitrixComponent
{

	/**
	 * Check required params.
	 *
	 * @throws SystemException
	 * @throws LoaderException
	 */
	protected function checkRequiredParams()
	{
		if (!Loader::includeModule('rest'))
		{
			throw new SystemException('Module `rest` is not installed.');
		}

		return true;
	}

	/**
	 * Check required params.
	 *
	 * @throws SystemException
	 */
	protected function initParams()
	{
		$appTag = [
			'configuration'
		];
		$analyticFrom = 'configuration';
		$this->arParams['SEF_MODE'] = isset($this->arParams['SEF_MODE']) ? $this->arParams['SEF_MODE'] : 'Y';
		$this->arParams['SEF_FOLDER'] = isset($this->arParams['SEF_FOLDER']) ? $this->arParams['SEF_FOLDER'] : '';

		if (!isset($this->arParams['VARIABLE_ALIASES']))
		{
			$this->arParams['VARIABLE_ALIASES'] = [
				'index' => [],
				'placement' => [],
				'section' => [],
				'import' => [],
				'import_app' => [],
				'import_zip' => [],
				'import_rollback' => [],
				'import_manifest' => [],
				'export' => [],
				'export_element' => []
			];
		}

		$defaultUrlTemplate404List = [
			'index' => '',
			'placement' => 'placement/#PLACEMENT_CODE#/',
			'section' => 'section/#MANIFEST_CODE#/',
			'import' => 'import/',
			'import_app' => 'import/#APP#/',
			'import_zip' => 'import_zip/#ZIP_ID#/',
			'import_rollback' => 'import_rollback/#APP#/',
			'import_manifest' => 'import_#MANIFEST_CODE#/',
			'export' => 'export_#MANIFEST_CODE#/',
			'export_element' => 'export_#MANIFEST_CODE#/#ITEM_CODE#/'
		];

		if ($this->arParams['SEF_MODE'] == 'Y')
		{
			$defaultVariableAliases404 = [];
			$componentVariableList = [
				'PLACEMENT_TYPE',
				'SECTION_TYPE',
				'APP'
			];
			$variableList = [];
			$urlTemplateList = CComponentEngine::makeComponentUrlTemplates(
				$defaultUrlTemplate404List,
				$this->arParams['SEF_URL_TEMPLATES']
			);
			$variableAliasList = CComponentEngine::makeComponentVariableAliases(
				$defaultVariableAliases404,
				$this->arParams['VARIABLE_ALIASES']
			);
			$componentPage = CComponentEngine::parseComponentPath(
				$this->arParams['SEF_FOLDER'],
				$urlTemplateList,
				$variableList
			);

			if (!(is_string($componentPage) &&
				isset($componentPage[0]) &&
				isset($defaultUrlTemplate404List[$componentPage])))
			{
				$componentPage = 'index';
			}

			CComponentEngine::initComponentVariables(
				$componentPage,
				$componentVariableList,
				$variableAliasList,
				$variableList
			);
			foreach ($urlTemplateList as $url => $value)
			{
				$key = 'PATH_TO_'.mb_strtoupper($url);
				$this->arResult[$key] = isset($this->arParams[$key][0]) ? $this->arParams[$key]
					: $this->arParams['SEF_FOLDER'] . $value;
			}
		}
		else
		{
			throw new SystemException('support only sef mode.');
		}

		if (!is_array($this->arResult))
		{
			$this->arResult = [];
		}

		if($componentPage == 'placement')
		{
			if (isset($variableList['PLACEMENT_CODE']))
			{
				$code =  strval($variableList['PLACEMENT_CODE']);
				$manifestList = Manifest::getList();
				$manifestList = array_filter(
					$manifestList,
					function($manifest) use ($code)
					{
						return in_array($code, $manifest['PLACEMENT']) && $manifest['ACTIVE'] === 'Y';
					}
				);
				if(!$manifestList)
				{
					$this->arResult['ERROR'] = Loc::getMessage('REST_CONFIGURATION_ERROR_PLACEMENT');
				}
				elseif(count($manifestList) > 1)
				{
					$appTag[] = $code;
				}
				else
				{
					$manifest = end($manifestList);
					$variableList['MANIFEST_CODE'] = $manifest['CODE'];
					$appTag[] = $manifest['CODE'];
					$componentPage = 'section';
				}
				$analyticFrom .= '_' . mb_strtolower($code);
			}
		}
		elseif ($componentPage == 'section')
		{
			if (!empty($variableList['MANIFEST_CODE']))
			{
				$manifest = Manifest::get($variableList['MANIFEST_CODE']);
				if (!empty($manifest['CODE']))
				{
					$appTag[] = $manifest['CODE'];
				}
			}
		}
		$variableList['ADDITIONAL_PARAMS'] = $this->request->get('additional') ?? [];

		if (!empty($this->request->get('from')))
		{
			$analyticFrom .= '_' . htmlspecialcharsbx($this->request->get('from'));
		}

		$appTagBanner = $appTag;
		$appTagBanner[] = 'premium';

		$pathTag = '';
		if (!empty($appTag))
		{
			$uri = new \Bitrix\Main\Web\Uri(\Bitrix\Rest\Marketplace\Url::getMarketplaceUrl($analyticFrom));
			$uri->addParams(['tag' => $appTag]);
			$pathTag = $uri->getUri();
		}
		$this->arResult = array_merge(
			[
				'COMPONENT_PAGE' => $componentPage,
				'VARIABLES' => $variableList,
				'TITLE' => $this->getTitle($componentPage),
				'ALIASES' => $this->arParams['SEF_MODE'] == 'Y' ? [] : $variableAliasList,
				'MANIFEST_CODE' => isset($variableList['MANIFEST_CODE']) ? strval($variableList['MANIFEST_CODE']) : '',
				'PLACEMENT_CODE' => isset($variableList['PLACEMENT_CODE']) ? strval($variableList['PLACEMENT_CODE']) : '',
				'APP' => isset($variableList['APP']) ? strval($variableList['APP']) : '',
				'MP_DETAIL_URL_TPL' => \Bitrix\Rest\Marketplace\Url::getApplicationDetailUrl(null, $analyticFrom),
				'MP_INDEX_PATH' => \Bitrix\Rest\Marketplace\Url::getMarketplaceUrl($analyticFrom),
				'MP_TAG_PATH' => $pathTag,
				'TAG' => $appTag,
				'TAG_BANNER' => $appTagBanner
			],
			$this->arResult
		);
		return true;
	}

	private function getTitle($page)
	{
		return Loc::getMessage('REST_CONFIGURATION_TITLE_PAGE_'.mb_strtoupper($page));
	}

	protected function prepareResult()
	{
		return true;
	}

	public function executeComponent()
	{
		try
		{
			$this->initParams();
			$this->checkRequiredParams();
			$this->prepareResult();
			$this->includeComponentTemplate($this->arResult['COMPONENT_PAGE']);
		}
		catch (SystemException $e)
		{
			ShowError($e->getMessage());
		}
		catch (LoaderException $e)
		{
			ShowError($e->getMessage());
		}
	}
}