<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

use Bitrix\Main\Localization\Loc;
use Bitrix\B24Connector\Button;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class B24CButtonsListComponent extends \CBitrixComponent
{
	protected $errors = array();

	protected function prepareResult()
	{
		/**@var $USER \CUser*/
		global $USER;

		$this->arResult['ITEMS'] = array();
		$this->arResult['TYPE_LIST'] = Button::getTypeList();
		$locationList = Button::getLocationList();
		$localData = $this->getLocalData();
		$remoteData = $this->getRemoteData();
		$this->arResult['LOCAL_DATA'] = $localData;
		$this->arResult['REMOTE_DATA'] = $remoteData;

		foreach ($remoteData as $buttonData)
		{
			if(!empty($this->arParams['FILTER']) && is_array($this->arParams['FILTER']))
				if(!empty($this->arParams['FILTER']['TYPE']))
					if(empty($buttonData['ITEMS'][$this->arParams['FILTER']['TYPE']]))
						continue;

			$buttonData['LOCATION_DISPLAY'] = $locationList[$buttonData['LOCATION']];
			$buttonData['LOCAL_ACTIVE'] = !empty($localData[$buttonData['ID']]);
			$buttonData['LOCAL_ADD_DATE'] = !empty($localData[$buttonData['ID']]['ADD_DATE']) ? $localData[$buttonData['ID']]['ADD_DATE'] : '';
			$buttonData['LOCAL_ADD_BY'] = !empty($localData[$buttonData['ID']]['ADD_BY']) ? $localData[$buttonData['ID']]['ADD_BY'] : '';

			if(!empty($buttonData['PATH_EDIT']))
				$buttonData['PATH_TO_BUTTON_EDIT'] = $buttonData['PATH_EDIT'];
			else
				$buttonData['PATH_TO_BUTTON_EDIT'] = 'https://'.\Bitrix\B24Connector\Connection::getDomain().'/crm/button/edit/'.$buttonData['ID'].'/';

			if ($buttonData['IS_PAGES_USED'])
			{
				$buttonData['PAGES_USE_DISPLAY'] = Loc::getMessage('B24C_BL_USE_DISPLAY_USER');
			}
			else
			{
				$buttonData['PAGES_USE_DISPLAY'] = Loc::getMessage('B24C_BL_USE_DISPLAY_ALL');
			}

			/** @var DateTime $dateCreate */
			$dateCreate = new DateTime($buttonData['DATE_CREATE'], 'Y-m-d\TH:i:sT');
			$buttonData['DATE_CREATE_DISPLAY'] = $dateCreate ? $dateCreate->format(Date::getFormat()) : '';

			$activeChangeDate = $buttonData['LOCAL_ADD_DATE'];
			/** @var DateTime $activeChangeDate */
			if($activeChangeDate)
			{
				$buttonData['ACTIVE_CHANGE_DATE_DISPLAY'] = $activeChangeDate->toUserTime()->format(IsAmPmMode() ? 'g:i a': 'H:i');
				$buttonData['ACTIVE_CHANGE_DATE_DISPLAY'] .= ', '. $activeChangeDate->format(Date::getFormat());
			}

			$buttonData['ACTIVE_CHANGE_BY_DISPLAY'] = $this->getUserInfo($buttonData['LOCAL_ADD_BY']);
			$buttonData['ACTIVE_CHANGE_BY_NOW_DISPLAY'] = $this->getUserInfo($USER->GetID());
			$this->arResult['ITEMS'][] = $buttonData;
		}

		if(empty($this->arResult['ITEMS']) && !empty($this->arParams['EMPTY_BUTTON']['TITLE']))
		{
			$url = '';

			if(!empty($this->arParams['EMPTY_BUTTON']['URL']))
				$url = $this->arParams['EMPTY_BUTTON']['URL'];
			elseif(!empty($this->arParams['EMPTY_BUTTON']['URL_METHOD']) && is_callable($this->arParams['EMPTY_BUTTON']['URL_METHOD']))
				$url = call_user_func($this->arParams['EMPTY_BUTTON']['URL_METHOD']);

			if(strlen($url) > 0)
			{
				$this->arResult['EMPTY_BUTTON'] = array(
					'TITLE' => 	$this->arParams['EMPTY_BUTTON']['TITLE'],
					'URL' => $url
				);
			}
		}

		if (LANGUAGE_ID == "ru")
			$this->arResult['B24_LANG'] = "ru";
		elseif (LANGUAGE_ID == "de")
			$this->arResult['B24_LANG'] = "de";
		else
			$this->arResult['B24_LANG'] = "com";
	}

	protected function checkParams()
	{
		$this->arParams['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);

		return true;
	}

	protected function getUserInfo($userId)
	{
		static $users = array();

		if(!$userId)
		{
			return null;
		}

		if(!$users[$userId])
		{
			$link = "/bitrix/admin/user_edit.php?lang=ru&ID=".$userId;
			$userFields = \Bitrix\Main\UserTable::getRowById($userId);

			if(!$userFields)
				return null;

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
			
			$userIcon = !empty($fileTmp['src']) ? $fileTmp['src'] : '';

			$users[$userId] = array(
				'ID' => $userId,
				'NAME' => $userName,
				'LINK' => $link,
				'ICON' => $userIcon
			);
		}

		return $users[$userId];
	}

	public function executeComponent()
	{
		global $APPLICATION;

		if (!$this->checkModules())
		{
			$this->showErrors();
			return;
		}

		if (!$this->checkParams())
		{
			$this->showErrors();
			return;
		}

		$moduleAccess = $APPLICATION->GetGroupRight('b24connector');

		if($moduleAccess < "R")
		{
			ShowError(Loc::getMessage('CRM_PERMISSION_DENIED'));
			return;
		}

		$this->arResult['PERM_CAN_EDIT'] = ($moduleAccess > "R");

		$this->prepareResult();
		$this->includeComponentTemplate();
	}

	protected function checkModules()
	{
		$errors = array();

		if(!Loader::includeModule('b24connector'))
			$errors[] = Loc::getMessage('B24C_MODULE_NOT_INSTALLED');

		if(!Loader::includeModule('socialservices'))
			$errors[] = Loc::getMessage('B24C_SS_MODULE_NOT_INSTALLED');

		if(!empty($errors))
			$this->errors = array_merge($this->errors, $errors);

		return empty($errors);
	}

	protected function hasErrors()
	{
		return (count($this->errors) > 0);
	}

	protected function showErrors()
	{
		if(count($this->errors) <= 0)
			return;

		foreach($this->errors as $error)
			ShowError($error);
	}

	protected function getLocalData()
	{
		$result = array();

		if($connection = \Bitrix\B24Connector\Connection::getFields())
		{
			$dbRes = \Bitrix\B24connector\ButtonTable::getList(array(
				'filter' => array(
					'=APP_ID' => $connection['ID']
				)
			));

			while($row = $dbRes->fetch())
				$result[$row['ID']] = $row;
		}

		return $result;
	}

	protected function getRemoteData()
	{
		$client = \Bitrix\Socialservices\ApClient::init();

		if(!$client)
		{
			$this->errors[] = Loc::getMessage('B24C_NOT_CONNECTED');
			//$this->showErrors();
			return array();
		}

		$result = array();
		$res = $client->call('crm.button.list');

		if(!empty($res['error']))
		{
			$error = $res['error'];

			if(!empty($res['error_description']))
				$error .= ': '.$res['error_description'];

			$this->errors[] = Loc::getMessage('B24C_REMOTE_DATA_ERROR').' ('.$error.')';
			$this->showErrors();
			$result = array();
		}
		elseif(!empty($res['result']) && is_array($res['result']))
		{
			foreach($res['result'] as  $button)
				$result[$button['ID']] = $button;
		}

		return $result;
	}
}