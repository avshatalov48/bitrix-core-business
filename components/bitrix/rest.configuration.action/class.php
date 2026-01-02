<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

class CRestConfigurationActionComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function prepareResult()
	{
		$result = [];
		$isAdmin = \CRestUtil::isAdmin();
		$result['ITEMS'] = [];

		if ($this->arParams['PATH_TO_IMPORT'])
		{
			if($isAdmin)
			{
				if(!empty($this->arParams['MANIFEST_CODE']) && !empty($this->arParams['PATH_TO_IMPORT_MANIFEST']))
				{
					$importPath = str_replace('#MANIFEST_CODE#', $this->arParams['MANIFEST_CODE'], $this->arParams['PATH_TO_IMPORT_MANIFEST']);
				}
				else
				{
					$importPath = $this->arParams['PATH_TO_IMPORT'];
				}
			}
			else
			{
				$importPath = '#';
			}

			$item = [
				'title' => Loc::getMessage('REST_CONFIGURATION_ACTION_TITLE_IMPORT'),
				'link' => $importPath,
				'icon' => '/bitrix/images/rest/configuration/rest-market-site-import.svg',
				'disabled' => !$isAdmin
			];

			if ($this->getTemplateName() === 'crm')
			{
				$item['title'] = Loc::getMessage('REST_CONFIGURATION_ACTION_IMPORT_CRM_TITLE_MSGVER_1');
				$item['description'] = Loc::getMessage('REST_CONFIGURATION_ACTION_IMPORT_CRM_DESCRIPTION_MSGVER_1');
				$item['action'] = Loc::getMessage('REST_CONFIGURATION_ACTION_IMPORT_CRM_ACTION');
				$item['icon'] = '/bitrix/images/rest/configuration/rest-market-site-import-crm.png';
				$result['ITEMS']['import'] = $item;
			}
			else
			{
				$result['ITEMS'][] = $item;
			}
		}

		if ($this->arParams['PATH_TO_EXPORT'] && $this->arParams['MANIFEST_CODE'])
		{
			$item = [
				'title' => Loc::getMessage('REST_CONFIGURATION_ACTION_TITLE_EXPORT'),
				'link' => $isAdmin ? str_replace('#MANIFEST_CODE#', $this->arParams['MANIFEST_CODE'], $this->arParams['PATH_TO_EXPORT']) : '#',
				'icon' => '/bitrix/images/rest/configuration/rest-market-site-export.svg',
				'disabled' => !$isAdmin
			];

			if ($this->getTemplateName() === 'crm')
			{
				$item['title'] = Loc::getMessage('REST_CONFIGURATION_ACTION_EXPORT_CRM_TITLE_MSGVER_1');
				$item['description'] = Loc::getMessage('REST_CONFIGURATION_ACTION_EXPORT_CRM_DESCRIPTION_MSGVER_1');
				$item['action'] = Loc::getMessage('REST_CONFIGURATION_ACTION_EXPORT_CRM_ACTION');
				$item['icon'] = '/bitrix/images/rest/configuration/rest-market-site-export-crm.png';
				$result['ITEMS']['export'] = $item;
			}
			else
			{
				$result['ITEMS'][] = $item;
			}
		}

		if ($this->arParams['MP_LOAD_PATH'])
		{
			$langMarket = Loc::getMessage('REST_CONFIGURATION_ACTION_TITLE_LOAD_MARKETPLACE_2');
			if (\Bitrix\Rest\Integration\Market\Label::isRenamedMarket())
			{
				$langMarket = Loc::getMessage('REST_CONFIGURATION_ACTION_TITLE_LOAD_MARKETPLACE_2_MSGVER_1');
			}

			$result['ITEMS'][] = [
				'title' => $langMarket,
				'link' => $this->arParams['MP_LOAD_PATH'],
				'icon' => '/bitrix/images/rest/configuration/rest-market-site-download.svg'
			];
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
