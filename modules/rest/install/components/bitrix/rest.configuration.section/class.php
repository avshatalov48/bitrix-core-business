<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Rest\Configuration\Manifest;
use Bitrix\Main\ErrorCollection;

class CRestConfigurationSectionComponent extends CBitrixComponent
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
		$result['ITEMS'] = Manifest::getList();

		$code = $this->arParams['PLACEMENT_CODE'] ? : false;
		$result['ITEMS'] = array_values(
			array_filter(
				$result['ITEMS'],
				function($manifest) use ($code)
				{
					return $manifest['ACTIVE'] == 'Y' && (($code) ? in_array($code, $manifest['PLACEMENT']) : true);
				}
			)
		);

		$result['ITEMS_JS'] = array_map(
			function($item)
			{
				return [
					'title' => $item['TITLE'],
					'description' => $item['DESCRIPTION'],
					'link' => str_replace('#MANIFEST_CODE#', $item['CODE'], $this->arParams['PATH_TO_SECTION']),
					'icon' => $item['ICON'],
					'color' => $item['COLOR'],
				];
			},
			$result['ITEMS']
		);

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