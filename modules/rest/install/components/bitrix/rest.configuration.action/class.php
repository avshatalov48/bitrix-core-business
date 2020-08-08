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

			$result['ITEMS'][] = [
				'title' => Loc::getMessage('REST_CONFIGURATION_ACTION_TITLE_IMPORT'),
				'link' => $importPath,
				'icon' => '/bitrix/images/rest/configuration/rest-market-site-import.svg',
				'disabled' => !$isAdmin
			];
		}

		if ($this->arParams['PATH_TO_EXPORT'] && $this->arParams['MANIFEST_CODE'])
		{
			$result['ITEMS'][] = [
				'title' => Loc::getMessage('REST_CONFIGURATION_ACTION_TITLE_EXPORT'),
				'link' => $isAdmin ? str_replace('#MANIFEST_CODE#', $this->arParams['MANIFEST_CODE'], $this->arParams['PATH_TO_EXPORT']) : '#',
				'icon' => '/bitrix/images/rest/configuration/rest-market-site-export.svg',
				'disabled' => !$isAdmin
			];
		}

		if ($this->arParams['MP_LOAD_PATH'])
		{
			$result['ITEMS'][] = [
				'title' => Loc::getMessage('REST_CONFIGURATION_ACTION_TITLE_LOAD_MARKETPLACE'),
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