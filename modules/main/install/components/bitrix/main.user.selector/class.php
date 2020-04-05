<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;

Loc::loadMessages(__FILE__);

/**
 * Class MainUserSelectorComponent
 */
class MainUserSelectorComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (empty($this->arParams['INPUT_NAME']))
		{
			$this->errors->setError(new Error('Parameter `INPUT_NAME` required.'));
			return false;
		}
		if (empty($this->arParams['ID']))
		{
			$this->errors->setError(new Error('Parameter `ID` required.'));
			return false;
		}

		return true;
	}

	protected function initParams()
	{
		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : '';
		$this->arParams['ID'] = isset($this->arParams['ID']) ?
			$this->arParams['ID']
			:
			str_replace(['[', ']'], ['_', ''], $this->arParams['INPUT_NAME']);

		$this->arParams['LIST'] = isset($this->arParams['LIST']) ? $this->arParams['LIST'] : [];
		$this->arParams['READONLY'] = isset($this->arParams['READONLY']) ? (bool) $this->arParams['READONLY'] : false;
		$this->arParams['BUTTON_SELECT_CAPTION'] = isset($this->arParams['BUTTON_SELECT_CAPTION']) ? $this->arParams['BUTTON_SELECT_CAPTION'] : null;
		$this->arParams['BUTTON_SELECT_CAPTION_MORE'] = isset($this->arParams['BUTTON_SELECT_CAPTION_MORE']) ? $this->arParams['BUTTON_SELECT_CAPTION_MORE'] : $this->arParams['BUTTON_SELECT_CAPTION'];
		$this->arParams['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? \CAllSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);
		$this->arParams['SELECTOR_OPTIONS'] = is_array($this->arParams['SELECTOR_OPTIONS']) ? $this->arParams['SELECTOR_OPTIONS'] : [];
		$this->arParams['FIRE_CLICK_EVENT'] = isset($this->arParams['FIRE_CLICK_EVENT']) && $this->arParams['FIRE_CLICK_EVENT'] == 'Y' ? 'Y' : 'N';

		if (isset($this->arParams['SHOW_BUTTON_SELECT']))
		{
			$this->arParams['SHOW_BUTTON_SELECT'] = (bool) $this->arParams['SHOW_BUTTON_SELECT'];
		}
		else
		{
			$this->arParams['SHOW_BUTTON_SELECT'] = true;
		}

		if (isset($this->arParams['DUPLICATES']))
		{
			$this->arParams['DUPLICATES'] = (bool) $this->arParams['DUPLICATES'];
		}
		else
		{
			$this->arParams['DUPLICATES'] = false;
		}
		if (isset($this->arParams['USE_SYMBOLIC_ID']))
		{
			$this->arParams['USE_SYMBOLIC_ID'] = (bool) $this->arParams['USE_SYMBOLIC_ID'];
		}
		else
		{
			$this->arParams['USE_SYMBOLIC_ID'] = false;
			if (isset($this->arParams['SELECTOR_OPTIONS']['departmentSelectDisable']) &&
				$this->arParams['SELECTOR_OPTIONS']['departmentSelectDisable'] === 'N')
			{
				$this->arParams['USE_SYMBOLIC_ID'] = true;
			}
		}
		$this->arParams['OPEN_DIALOG_WHEN_INIT'] = (
			isset($this->arParams['OPEN_DIALOG_WHEN_INIT'])
				? (bool) $this->arParams['OPEN_DIALOG_WHEN_INIT']
				: false
		);
	}

	protected function prepareResult()
	{
		$this->arResult['TILE_ID_LIST'] = [];
		$this->arResult['LIST'] = [];
/*
		$list = is_array($this->arParams['LIST']) ? $this->arParams['LIST'] : [];

		if ($this->arParams['USE_SYMBOLIC_ID'])
		{
			$this->buildItemsWithSymbolicId($list);
		}
		else
		{
			$this->buildUserItems($list);
		}
*/
		$this->arResult['ITEMS_SELECTED'] = $this->arParams['LIST'];
		if (!$this->arParams['USE_SYMBOLIC_ID'])
		{
			$res = array();
			foreach($this->arResult['ITEMS_SELECTED'] as $userId)
			{
				$res['U'.$userId] = 'users';
			}
			$this->arResult['ITEMS_SELECTED'] = $res;
		}

		$this->arResult['IS_INPUT_MULTIPLE'] = substr($this->arParams['INPUT_NAME'], -2) == '[]';
		$this->arResult['FIRE_CLICK_EVENT'] = (
			$this->arParams['FIRE_CLICK_EVENT'] == 'Y'
			&& empty($this->arParams['LIST'])
		);

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function onPrepareComponentParams($arParams)
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Loader::includeModule('socialnetwork'))
		{
			$this->errors->setError(new Error('Module `socialnetwork` is not installed.'));
			return $arParams;
		}

		$this->arParams = $arParams;
		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
		}

		return $this->arParams;
	}

	public function executeComponent()
	{
		if (!$this->errors->isEmpty())
		{
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}

	private function buildItem($id, $name, $data = [], $bgcolor = null, $color = null)
	{
		return array(
			'name' => $name,
			'data' => $data,
			'id' => $id,
			'bgcolor' => $bgcolor,
			'color' => $color,
		);
	}

	private function buildItemsWithSymbolicId(array $list)
	{
		$possiblePrefixes = ['U', 'DR'];

		$listByType = [];
		foreach ($list as $itemId)
		{
			$matches = [];
			if (preg_match('#(' . implode('|', $possiblePrefixes) . ')([0-9]+)#', $itemId, $matches) === 1
				&& !empty($matches[1]) && !empty($matches[2])
			)
			{
				$symbol = $matches[1];
				$id = $matches[2];

				$listByType[$symbol][] = $id;
			}
		}

		foreach ($listByType as $symbol => $ids)
		{
			switch ($symbol)
			{
				case 'U':
					$this->buildUserItems($ids, $symbol);
					break;
				case 'DR':
					$this->buildDepartmentsItems($ids, $symbol);
					break;
			}
		}
	}

	private function buildUserItems($ids, $userIdPrefix = '')
	{
		$tileIds = [];
		$userList = \Bitrix\Main\UserTable::getList([
			'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN'],
			'filter' => ['=ID' => $ids]
		]);
		foreach ($userList as $userData)
		{
			$id = (int) $userData['ID'];
			if (!in_array($id, $ids))
			{
				continue;
			}

			// format name
			$userName = \CAllUser::FormatName(
				$this->arParams['NAME_TEMPLATE'],
				[
					'LOGIN' => $userData['LOGIN'],
					'NAME' => $userData['NAME'],
					'LAST_NAME' => $userData['LAST_NAME'],
					'SECOND_NAME' => $userData['SECOND_NAME']
				],
				true, false
			);

			$item = [
				'id' => $userData['ID'],
				'name' => $userName,
				'data' => [],
			];

			if (!$this->arParams['DUPLICATES'] && in_array($id, $tileIds))
			{
				continue;
			}

			$tileIds[] = $userIdPrefix . $id;
			$this->arResult['LIST'][] = $this->buildItem(
				$userIdPrefix . $id,
				$item['name'],
				$item['data'],
				isset($item['bgcolor']) ? $item['bgcolor'] : null,
				isset($item['color']) ? $item['color'] : null
			);
		}
		$this->arResult['TILE_ID_LIST'] = $tileIds;
	}

	private function buildDepartmentsItems($departmentsIds, $departmentsIdPrefix)
	{
		if (!Loader::includeModule('intranet'))
		{
			return;
		}

		$departmentsData = CIntranetUtils::getDepartmentsData($departmentsIds);
		foreach ($departmentsData as $depId => $depName)
		{
			if (!$this->arParams['DUPLICATES'] && in_array($depId, $this->arResult['TILE_ID_LIST']))
			{
				continue;
			}
			$this->arResult['TILE_ID_LIST'][] = $departmentsIdPrefix . $depId;

			$this->arResult['LIST'][] = $this->buildItem(
				$departmentsIdPrefix . $depId,
				$depName
			);
		}
	}
}