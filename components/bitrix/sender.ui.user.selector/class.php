<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderUiUserSelectorComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['READONLY'] = isset($this->arParams['READONLY']) ? (bool) $this->arParams['READONLY'] : false;
		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : '';
		$this->arParams['ID'] = isset($this->arParams['ID']) ? $this->arParams['ID'] : '';
		$this->arParams['LIST'] = isset($this->arParams['LIST']) ? $this->arParams['LIST'] : [];
		$this->arParams['BUTTON_SELECT_CAPTION'] = isset($this->arParams['BUTTON_SELECT_CAPTION']) ? $this->arParams['BUTTON_SELECT_CAPTION'] : null;
		$this->arParams['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? \CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);

		if (isset($this->arParams['SHOW_BUTTON_ADD']))
		{
			$this->arParams['SHOW_BUTTON_ADD'] = (bool) $this->arParams['SHOW_BUTTON_ADD'];
		}
		else
		{
			$this->arParams['SHOW_BUTTON_ADD'] = false;
		}

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
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URL'] = $this->getPath() . '/ajax.php';

		$this->arResult['LIST_USER'] = [];
		$this->arResult['LIST'] = [];
		$list = is_array($this->arParams['LIST']) ? $this->arParams['LIST'] : [];
		if (empty($list))
		{
			/** @var \CAllUser {$GLOBALS['USER']} */
			$list[] = $GLOBALS['USER']->GetID();
		}

		$tileIds = [];
		$userList = \Bitrix\Main\UserTable::getList([
			'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN'],
			'filter' => ['=ID' => $list]
		]);
		foreach ($userList as $userData)
		{
			$id = $userData['ID'];
			if (!in_array($id, $list))
			{
				continue;
			}

			// format name
			$userName = \CUser::FormatName(
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

			$tileIds[] = $id;
			$this->arResult['LIST'][] = array(
				'name' => $item['name'],
				'data' => $item['data'],
				'id' => $id,
				'bgcolor' => isset($item['bgcolor']) ? $item['bgcolor'] : null,
				'color' => isset($item['color']) ? $item['color'] : null,
			);
		}
		$this->arResult['LIST_USER'] = $tileIds;

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