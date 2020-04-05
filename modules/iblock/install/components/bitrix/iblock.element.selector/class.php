<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class IblockElementSelector extends CBitrixComponent
{
	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	protected function checkModules()
	{
		if(!Loader::includeModule('iblock'))
		{
			throw new SystemException(Loc::getMessage('IES_MODULE_NOT_INSTALLED', array('MODULE_ID' => 'iblock')));
		}
	}

	public function onPrepareComponentParams($params)
	{
		$params['SELECTOR_ID'] = !empty($params['SELECTOR_ID']) ? $params['SELECTOR_ID'] : '';
		$params['IBLOCK_ID'] = !empty($params['IBLOCK_ID']) ? $params['IBLOCK_ID'] : 0;

		$params['SEARCH_INPUT_ID'] = !empty($params['SEARCH_INPUT_ID']) && preg_match('/^[a-zA-Z0-9_-]+$/',
			$params['SEARCH_INPUT_ID']) ? $params['SEARCH_INPUT_ID'] : '';
		$params['POPUP'] = !empty($params['POPUP']) ? $params['POPUP'] : 'Y';
		$params['INPUT_NAME'] = !empty($params['INPUT_NAME']) ? $params['INPUT_NAME'] : $params['SELECTOR_ID'];
		$params['PANEL_SELECTED_VALUES'] = !empty($params['PANEL_SELECTED_VALUES']) ?
			$params['PANEL_SELECTED_VALUES'] : 'Y';
		$params['MULTIPLE'] = !empty($params['MULTIPLE']) ? $params['MULTIPLE'] : 'Y';
		$params['ONLY_READ'] = !empty($params['ONLY_READ']) ? $params['ONLY_READ'] : 'N';
		$params['CURRENT_ELEMENTS_ID'] = !empty($params['CURRENT_ELEMENTS_ID']) ?
			$params['CURRENT_ELEMENTS_ID'] : array();
		if(!is_array($params['CURRENT_ELEMENTS_ID']))
		{
			$params['CURRENT_ELEMENTS_ID'] = array($params['CURRENT_ELEMENTS_ID']);
		}
		$params['ON_CHANGE'] = !empty($params['ON_CHANGE']) ? $params['ON_CHANGE'] : '';
		$params['ON_SELECT'] = !empty($params['ON_SELECT']) ? $params['ON_SELECT'] : '';
		$params['ON_UNSELECT'] = !empty($params['ON_UNSELECT']) ? $params['ON_UNSELECT'] : '';

		$params['LAST_ELEMENTS'] = array();
		$params['CURRENT_ELEMENTS'] = array();

		$params['ACCESS_DENIED'] = 'N';

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();
			$this->checkRequiredParams();
			$this->checkPermissions();

			$this->getLastElements();
			$this->getCurrentElements();

			$this->fillResult();
			$this->includeComponentTemplate();
		}
		catch (SystemException $exception)
		{
			ShowError($exception->getMessage());
		}
	}

	protected function checkRequiredParams()
	{
		$listRequiredParams = array('SELECTOR_ID', 'IBLOCK_ID');
		foreach($listRequiredParams as $requiredParam)
		{
			if(empty($this->arParams[$requiredParam]))
			{
				throw new SystemException(Loc::getMessage(
					'IES_ERROR_REQUIRED_PARAMETER', array('#PARAM#' => $requiredParam)));
			}
		}
	}

	protected function checkPermissions()
	{
		if(!CIBlockRights::userHasRightTo($this->arParams['IBLOCK_ID'], $this->arParams['IBLOCK_ID'], 'element_read'))
		{
			$this->arParams['ACCESS_DENIED'] = 'Y';
		}
	}

	protected function getLastElements()
	{
		if($this->arParams['ACCESS_DENIED'] == 'Y')
		{
			return;
		}

		$queryObject = CIBlockElement::getList(
			array('ID' => 'DESC'),
			array(
				'=IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R'
			),
			false, array('nTopCount' => 50), array('ID', 'NAME'));
		while($element = $queryObject->fetch())
		{
			$this->arParams['LAST_ELEMENTS'][] = array('ID' => $element['ID'], 'NAME' => $element['NAME']);
		}
	}

	protected function getCurrentElements()
	{
		if(empty($this->arParams['CURRENT_ELEMENTS_ID']) || $this->arParams['ACCESS_DENIED'] == 'Y')
		{
			return;
		}

		$queryObject = CIBlockElement::getList(array('NAME' => 'ASC'),
			array('=ID' => $this->arParams['CURRENT_ELEMENTS_ID']), false, false, array('ID', 'NAME'));
		while($element = $queryObject->fetch())
		{
			$this->arParams['CURRENT_ELEMENTS'][] = array('ID' => $element['ID'], 'NAME' => $element['NAME']);
		}
	}

	protected function fillResult()
	{
		$this->arResult['SELECTOR_ID'] = $this->arParams['SELECTOR_ID'];
		$this->arResult['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];

		$this->arResult['SEARCH_INPUT_ID'] = $this->arParams['SEARCH_INPUT_ID'];
		$this->arResult['POPUP'] = $this->arParams['POPUP'];
		$this->arResult['INPUT_NAME'] = $this->arParams['INPUT_NAME'];
		$this->arResult['PANEL_SELECTED_VALUES'] = $this->arParams['PANEL_SELECTED_VALUES'];
		$this->arResult['MULTIPLE'] = $this->arParams['MULTIPLE'];
		$this->arResult['ONLY_READ'] = $this->arParams['ONLY_READ'];
		$this->arResult['ON_CHANGE'] = $this->arParams['ON_CHANGE'];
		$this->arResult['ON_SELECT'] = $this->arParams['ON_SELECT'];
		$this->arResult['ON_UNSELECT'] = $this->arParams['ON_UNSELECT'];

		$this->arResult['ACCESS_DENIED'] = $this->arParams['ACCESS_DENIED'];

		$this->arResult['CURRENT_ELEMENTS_ID'] = $this->arParams['CURRENT_ELEMENTS_ID'];
		$this->arResult['CURRENT_ELEMENTS'] = $this->arParams['CURRENT_ELEMENTS'];
		$this->arResult['LAST_ELEMENTS'] = $this->arParams['LAST_ELEMENTS'];
	}
}