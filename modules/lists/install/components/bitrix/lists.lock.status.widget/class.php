<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;

class ListsLockStatusWidget extends CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		$params["ELEMENT_ID"] = (isset($params["ELEMENT_ID"])) ? $params["ELEMENT_ID"] : null;
		$params["ELEMENT_NAME"] = (isset($params["ELEMENT_NAME"])) ? $params["ELEMENT_NAME"] : "";

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();

			$this->formatResult();

			$this->includeComponentTemplate();
		}
		catch (SystemException $exception)
		{
			ShowError($exception->getMessage());
		}
	}

	/**
	 * @throws SystemException
	 */
	private function checkModules()
	{
		try
		{
			if (!Loader::includeModule("lists"))
			{
				throw new SystemException("Module \"lists\" not found");
			}
		}
		catch (LoaderException $exception)
		{
			throw new SystemException("System error");
		}
	}

	private function formatResult()
	{
		$this->arResult = [];

		$this->arResult["ELEMENT_NAME"] = $this->arParams["ELEMENT_NAME"];

		list($this->arResult["LOCK_STATUS"], $this->arResult["LOCKED_BY"]) = $this->getLockStatus();

		$this->arResult["LOCKED_USER_NAME"] = $this->getLockedUserName($this->arResult["LOCKED_BY"]);
	}

	private function getLockStatus()
	{

		$lockStatus = CIBlockElement::WF_GetLockStatus($this->arParams["ELEMENT_ID"], $lockedBy, $dateLock);

		return [$lockStatus, $lockedBy];
	}

	private function getLockedUserName($lockedBy)
	{
		$lockedUserName = "";

		$lockedBy = (int) $lockedBy;
		if ($lockedBy > 0)
		{
			$queryObject = CUser::getList("ID", "ASC", ["ID_EQUAL_EXACT" => $lockedBy]);
			if ($user = $queryObject->getNext())
			{
				$lockedUserName = rtrim($user["NAME"]." ".$user["LAST_NAME"]);
			}
		}

		return $lockedUserName;
	}
}