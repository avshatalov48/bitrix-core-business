<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;
use Bitrix\B24connector\ButtonTable;
use Bitrix\B24connector\ButtonSiteTable;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::includeModule('b24connector'))
{
	return;
}

Loc::loadMessages(__FILE__);

class B24CButtonListAjaxController
{
	protected $errors = array();
	protected $action = null;
	protected $responseData = array();
	protected $requestData = array();

	/** @var HttpRequest $request */
	protected $request = array();

	protected function getActions()
	{
		return array(
			'activate',
			'deactivate',
			'saveSiteRestrictions',
		);
	}

	protected function activate()
	{
		global $USER;

		if(!$connection = \Bitrix\B24Connector\Connection::getFields())
		{
			$this->responseData['LOCAL_DATA'] = array();
			return;
		}

		$dbRes = \Bitrix\B24connector\ButtonTable::getList(array(
			'filter' => array(
				'=ID' => $this->requestData['REMOTE_DATA']['ID'],
				'=APP_ID' => $connection['ID']
			)
		));

		if($row = $dbRes->fetch())
		{
			$localData = $row;
		}
		else
		{
			$localData = array(
				'ID' => $this->requestData['REMOTE_DATA']['ID'],
				'APP_ID' => $connection['ID'],
				'NAME' => $this->requestData['REMOTE_DATA']['NAME'],
				'SCRIPT' => $this->requestData['REMOTE_DATA']['SCRIPT'],
				'ADD_BY' => $USER->GetID(),
				'ADD_DATE' => new \Bitrix\Main\Type\DateTime()
			);

			$dbRes = ButtonTable::add($localData);

			if(!$dbRes->isSuccess())
			{
				foreach($dbRes->getErrorMessages() as $error)
					$this->errors[] = $error;
			}
			else
			{
				$localData['ADD_DATE'] = $localData['ADD_DATE']->toString();
			}
		}

		$this->responseData['LOCAL_DATA'] = $localData;
	}

	protected function deactivate()
	{
		$dbRes = ButtonTable::delete($this->requestData['BUTTON_ID']);

		if(!$dbRes->isSuccess())
			foreach($dbRes->getErrorMessages() as $error)
				$this->errors[] = $error;
	}

	protected function saveSiteRestrictions()
	{
		$buttonId = (int)$this->requestData['BUTTON_ID'];
		if ($buttonId <= 0)
		{
			return;
		}

		$allowedSites = [];
		$rows = \CSite::GetList('sort', 'asc', ['ACTIVE' => 'Y']);
		while ($row = $rows->Fetch())
		{
			$allowedSites[] = $row['LID'];
		}

		ButtonSiteTable::deleteByButtonId($buttonId);

		foreach ($this->requestData['SITE_ID'] as $siteId)
		{
			if (in_array($siteId, $allowedSites))
			{
				$result = ButtonSiteTable::add([
					'BUTTON_ID' => $buttonId,
					'SITE_ID' => $siteId,
				]);
				if (!$result->isSuccess())
				{
					foreach($result->getErrorMessages() as $error)
					{
						$this->errors[] = $error;
					}
					return;
				}
			}
		}
	}

	protected function checkPermissions()
	{
		global $APPLICATION;
		$moduleAccess = $APPLICATION->GetGroupRight('b24connector');
		return $moduleAccess >= "R";
	}

	protected function giveResponse()
	{
		global $APPLICATION;
		$APPLICATION->restartBuffer();

		header('Content-Type:application/json; charset=UTF-8');
		echo \Bitrix\Main\Web\Json::encode(
			$this->responseData + array(
				'error' => $this->hasErrors(),
				'text' => implode('<br>', $this->errors),
			)
		);

		\CMain::finalActions();
		exit;
	}

	protected function getActionCall()
	{
		return array($this, $this->action);
	}

	protected function hasErrors()
	{
		return count($this->errors) > 0;
	}

	protected function check()
	{
		if(!$this->checkPermissions())
			$this->errors[] = Loc::getMessage('B24C_PERMISSION_DENIED');
		elseif(!in_array($this->action, $this->getActions()))
			$this->errors[] = 'Action "' . $this->action . '" not found.';
		elseif(!check_bitrix_sessid() || !$this->request->isPost())
			$this->errors[] = 'Security error.';
		elseif(!is_callable($this->getActionCall()))
			$this->errors[] = 'Action method "' . $this->action . '" not found.';

		return !$this->hasErrors();
	}

	protected function prepareRequestData()
	{
		$this->requestData = $this->request->get('data');

		if(mb_strtolower(SITE_CHARSET) != 'utf-8')
			$this->requestData = Encoding::convertEncoding($this->requestData, 'UTF-8', SITE_CHARSET);

	}
	public function exec()
	{
		$this->request = Context::getCurrent()->getRequest();
		$this->action = $this->request->get('action');
		$this->prepareRequestData();

		if($this->check())
		{
			call_user_func_array($this->getActionCall(), array($this->requestData));
		}
		$this->giveResponse();
	}
}

$controller = new B24CButtonListAjaxController();
$controller->exec();