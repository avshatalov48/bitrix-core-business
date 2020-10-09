<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ModuleManager;
use Bitrix\Rest\Preset\Data\Section;
use Bitrix\Rest\Preset\Data\Element;

Loc::loadMessages(__FILE__);

class RestIntegratorsListComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams() : bool
	{
		if (!Loader::includeModule('rest'))
		{
			$this->errors->setError(new Error('Module `rest` is not installed.'));

			return false;
		}

		return true;
	}

	protected function initParams() : void
	{
		if ($this->arParams['PATH_TO_SECTION'])
		{
			$this->arParams['PATH_TO'] = $this->arParams['PATH_TO_SECTION'];
			$this->arParams['ADD_INTEGRATION_MODE'] = 'N';
		}
		elseif ($this->arParams['PATH_TO_ADD'])
		{
			$this->arParams['PATH_TO'] = $this->arParams['PATH_TO_ADD'];
			$this->arParams['ADD_INTEGRATION_MODE'] = 'Y';
		}

		$this->arParams['PATH_TO'] = $this->arParams['PATH_TO'] ?? '';
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] === 'Y' : true;
		$this->arParams['TYPE'] = ($this->arParams['TYPE']) ? : 'SECTION';
		$this->arParams['CODE'] = ($this->arParams['CODE']) ? : '';
	}


	protected function prepareResult()
	{
		$this->arResult['ERRORS'] = [];
		$code = $this->arParams['CODE'];
		$items = Section::get();

		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			$title = Loc::getMessage('REST_INTEGRATION_INDEX_TITLE');
			if (!empty($code) && !empty($items[$code]['TITLE']))
			{
				$title = $items[$code]['TITLE'];
			}
			/**@var \CAllMain */
			$GLOBALS['APPLICATION']->SetTitle($title);
		}

		$isAdmin = \CRestUtil::isAdmin();
		if ($this->arParams['TYPE'] === 'LIST')
		{
			if (!$isAdmin && isset($items[$code]) && $items[$code]['ADMIN_ONLY'] === 'Y')
			{
				$this->errors->setError(new Error(Loc::getMessage('REST_INTEGRATION_LIST_ERROR_ACCESS_DENIED')));

				return false;
			}
			$items = Element::getList($code);
		}

		$items = array_filter(
			$items,
			function ($item) use ($isAdmin)
			{
				$need = true;
				if (
					!$isAdmin
					&&
					(
						$item['ADMIN_ONLY'] === 'Y'
						||
						(
							!empty($item['OPTIONS'])
							&&
							(
								$item['OPTIONS']['WIDGET_NEEDED'] !== 'D'
								|| $item['OPTIONS']['APPLICATION_NEEDED'] !== 'D'
							)
						)
					)
				)
				{
					$need = false;
				}

				return $need;
			}
		);

		$this->arResult['ITEMS'] = $this->getItems($items);

		return true;
	}

	protected function getItems($items) : array
	{
		$result = [];
		if($this->arParams['PATH_TO'])
		{
			$url = $this->arParams['PATH_TO'];
		}
		else
		{
			return $result;
		}

		foreach($items as $item)
		{
			if(is_array($item['REQUIRED_MODULES']))
			{
				foreach($item['REQUIRED_MODULES'] as $module)
				{
					if(!ModuleManager::isModuleInstalled($module))
					{
						continue 2;
					}
				}
			}

			if($item['ACTIVE'] !== 'Y')
			{
				continue;
			}

			$element = [
				'id' => $item['CODE'],
				'code' => $item['CODE'],
				'title' => $item['TITLE'],
				'description' => $item['DESCRIPTION'],
				'iconClass' => $item['ICON_CLASS'],
				'iconIClass' => $item['ICON_I_CLASS'],
				'iconIBgColor' => $item['ICON_I_BG_COLOR'],
				'iconColor' => $item['ICON_COLOR'],
			];
			if (isset($this->arParams['ADD_INTEGRATION_MODE']) && $this->arParams['ADD_INTEGRATION_MODE'] == 'Y')
			{
				if($item['ELEMENT_CODE'])
				{
					$element['integrationCode'] = $item['ELEMENT_CODE'];
				}
				else
				{
					continue;
				}
			}
			else
			{
				$element['url'] = str_replace(
					[
						'#SECTION_CODE#',
						'#ELEMENT_CODE#',
						'#ID#'
					],
					[
						$item['SECTION_CODE'],
						$item['ELEMENT_CODE'],
						$item['ID']
					],
					$url
				);
			}

			$result[] = $element;
		}

		return $result;
	}

	protected function printErrors() : void
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();

		$this->initParams();
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