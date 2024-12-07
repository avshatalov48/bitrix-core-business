<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Authentication\ApplicationPasswordTable;
use Bitrix\Main\Authentication\ApplicationManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CMainAppPasswords extends CBitrixComponent
{
	public function executeComponent()
	{
		global $APPLICATION;

		$this->setFrameMode(false);
		$APPLICATION->SetTitle(Loc::getMessage("MAIN_PASSWORDS_TITLE"));

		global $USER;

		if($USER->IsAuthorized())
		{
			//get data from database
			$this->prepareData();
		}
		else
		{
			$this->arResult["MESSAGE"] = array("MESSAGE" => Loc::getMessage("main_app_passwords_auth"), "TYPE" => "ERROR");
		}

		$this->IncludeComponentTemplate();
	}

	protected function prepareData()
	{
		global $USER;

		$appManager = ApplicationManager::getInstance();
		$applications = $appManager->getApplications();

		$passwordsList = ApplicationPasswordTable::getList(array(
			"filter" => array("=USER_ID" => $USER->GetID()),
			"order" => array("APPLICATION_ID" => "ASC", "DATE_CREATE"=>"ASC"),
		));

		$rows = array();
		while($password = $passwordsList->fetch())
		{
			if(!isset($applications[$password["APPLICATION_ID"]]))
			{
				$applications[$password["APPLICATION_ID"]] = array("NAME" => $password["APPLICATION_ID"]);
			}

			if(!isset($rows[$password["APPLICATION_ID"]]))
			{
				$rows[$password["APPLICATION_ID"]] = array();
			}
			$rows[$password["APPLICATION_ID"]][] = $password;
		}

		$this->arResult["ROWS"] = $rows;
		$this->arResult["APPLICATIONS"] = $applications;
	}
}
