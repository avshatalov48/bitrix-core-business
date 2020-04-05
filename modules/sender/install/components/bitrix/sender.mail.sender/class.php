<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;

use Bitrix\Main\Mail\Address;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderUiMailboxSelectorComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : '';
		$this->arParams['~VALUE'] = isset($this->arParams['~VALUE']) ? $this->arParams['~VALUE'] : '';
		$this->arParams['VALUE'] = (new Address($this->arParams['~VALUE']))->get();

		$this->arParams['ID'] = isset($this->arParams['ID']) ? $this->arParams['ID'] : '';
		$this->arParams['LIST'] = isset($this->arParams['LIST']) ? $this->arParams['LIST'] : null;
		$this->arParams['BUTTON_SELECT_CAPTION'] = isset($this->arParams['BUTTON_SELECT_CAPTION']) ? $this->arParams['BUTTON_SELECT_CAPTION'] : null;

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



	public function getUserInfo($userId)
	{
		static $users = array();

		if(!$userId)
		{
			return null;
		}

		if(!$users[$userId])
		{
			// prepare link to profile
			$replaceList = array('user_id' => $userId);
			$link = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_USER_PROFILE'], $replaceList);

			$userFields = \Bitrix\Main\UserTable::getRowById($userId);
			if(!$userFields)
			{
				return null;
			}

			// format name
			$userName = CUser::FormatName(
				$this->arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => $userFields['LOGIN'],
					'NAME' => $userFields['NAME'],
					'LAST_NAME' => $userFields['LAST_NAME'],
					'SECOND_NAME' => $userFields['SECOND_NAME']
				),
				true, false
			);

			// prepare icon
			$fileTmp = CFile::ResizeImageGet(
				$userFields['PERSONAL_PHOTO'],
				array('width' => 42, 'height' => 42),
				BX_RESIZE_IMAGE_EXACT,
				false
			);
			$userIcon = $fileTmp['src'];

			$users[$userId] = array(
				'ID' => $userId,
				'NAME' => $userName,
				'LINK' => $link,
				'ICON' => $userIcon
			);
		}

		return $users[$userId];
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URL'] = $this->getPath() . '/ajax.php';


		$list = $GLOBALS['APPLICATION']->IncludeComponent('bitrix:main.mail.confirm', '', array());
		$address = new Address();
		foreach ($list as $item)
		{
			$item['sender'] = $address
				->setEmail($item['email'])
				->setName($item['name'])
				->get();

			$item['id'] = base64_encode($item['sender']);
			$this->arResult['LIST'][] = $item;
		}

		$this->arResult['CURRENT'] = null;
		if ($this->arParams['VALUE'])
		{
			foreach ($this->arResult['LIST'] as $item)
			{
				if ($item['sender'] == $this->arParams['VALUE'])
				{
					$this->arResult['CURRENT'] = $item;
					break;
				}
			}
		}

		if (!$this->arResult['CURRENT'] && count($this->arResult['LIST']) > 0)
		{
			$this->arResult['CURRENT'] = $this->arResult['LIST'][0];
		}

		$this->arResult['CURRENT']['sender'] = $this->arResult['CURRENT']['name'];
		$this->arResult['CURRENT']['sender'] .= '<' . $this->arResult['CURRENT']['email'] . '>';

		$userInfo = $this->getUserInfo($GLOBALS['USER']->GetID());
		$this->arResult['CURRENT']['icon'] = $userInfo['ICON'];


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