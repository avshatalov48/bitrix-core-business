<?php
namespace Bitrix\Main\UI;

use Bitrix\Main\Application;
use Bitrix\Main\Context;

class FileInputUtility
{
	protected static $instance = null;

	const SESSION_VAR_PREFIX = "MFI_UPLOADED_FILES_";
	const SESSION_LIST = "MFI_SESSIONS";
	const SESSION_TTL = 86400;

	/**
	 * @return FileInputUtility
	 */
	public static function instance()
	{
		if (!isset(static::$instance))
			static::$instance = new static();

		return static::$instance;
	}

	public function __construct()
	{
	}

	public function isAccessible(): bool
	{
		return Application::getInstance()->getSession()->isAccessible();
	}

	public function registerControl($CID, $controlId = "")
	{
		if (func_num_args() == 1)
		{
			$controlId = $CID;
			$CID = "";
		}
		$CID = (!empty($CID) ? $CID : md5(randString(15)));
		$this->initSession($CID, $controlId);
		return $CID;
	}

	public function registerFile($CID, $fileId)
	{
		Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID][] = $fileId;
	}

	public function unRegisterFile($CID, $fileId)
	{
		if (isset(Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID]))
		{
			$key = array_search($fileId, Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID]);
			if($key !== false)
			{
				unset(Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID][$key]);
				return true;
			}
		}
		return false;
	}

	public function checkFiles($controlId, $arFiles)
	{
		$arSessionFilesList = $this->getSessionControlFiles($controlId);

		if(is_array($arFiles))
		{
			foreach($arFiles as $key => $fileId)
			{
				if(!in_array($fileId, $arSessionFilesList))
				{
					unset($arFiles[$key]);
				}
			}

			$arFiles = array_values($arFiles);
		}

		return $arFiles;
	}

	public function checkDeletedFiles($controlId)
	{
		$arSessionFilesList = $this->getSessionControlFiles($controlId);
		$deletedRequestName = $controlId.'_deleted';

		$result = array();

		$request = Context::getCurrent()->getRequest();
		$requestValues = $request->getValues();

		// HACK for correct use file delete from BX.UI.ComponentAjax.doSubmit
		if (isset($requestValues['data']) && is_array($requestValues['data']))
		{
			$requestValues = $requestValues['data'];
		}

		if(isset($requestValues[$deletedRequestName]) && is_array($requestValues[$deletedRequestName]))
		{
			foreach($requestValues[$deletedRequestName] as $deletedFile)
			{
				if(
					in_array($deletedFile, $arSessionFilesList)
					&& \CFile::SaveFile(array(
						'old_file' => $deletedFile,
						'del' => 'Y',
					), ''))
				{
					$result[] = $deletedFile;
				}
			}
		}

		return $result;
	}

	public function checkFile($CID, $fileId)
	{
		return isset(Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID])
			&& in_array($fileId, Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID]);
	}

	public function getControlByCid($CID)
	{
		$ts = time();
		$found = null;
		foreach (Application::getInstance()->getSession()[self::SESSION_LIST] as $controlId => $d)
		{
			if (array_key_exists($CID, $d))
			{
				$r = $d[$CID];
				if($r["SESSID"] != bitrix_sessid()
					|| $ts-$r["TS"] > self::SESSION_TTL)
				{
					unset(Application::getInstance()->getSession()[self::SESSION_LIST][$controlId][$CID]);
					unset(Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID]);
				}
				else
				{
					$found = $controlId;
					break;
				}
			}
		}
		return $found;
	}
	public function isCidRegistered($CID)
	{
		return !is_null($this->getControlByCid($CID));
	}

	public function getUserFieldCid(array $userField)
	{
		$fieldName = $userField['MULTIPLE'] === 'Y' ? preg_replace("/\[.*\]$/", '', $userField['FIELD_NAME']) : $userField['FIELD_NAME'];
		return $userField["ENTITY_ID"]."-".$userField["ID"]."-".$fieldName;
	}

	protected function initSession($CID, $controlId)
	{
		$ts = time();

		if(!isset(Application::getInstance()->getSession()[self::SESSION_LIST][$controlId]))
		{
			Application::getInstance()->getSession()[self::SESSION_LIST][$controlId] = array();
		}
		else
		{
			foreach(Application::getInstance()->getSession()[self::SESSION_LIST][$controlId] as $key => $arSession)
			{
				if($arSession["SESSID"] != bitrix_sessid()
					|| $ts-$arSession["TS"] > self::SESSION_TTL)
				{
					unset(Application::getInstance()->getSession()[self::SESSION_LIST][$controlId][$key]);
					unset(Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$key]);
				}
			}
		}
		if (!array_key_exists($CID, Application::getInstance()->getSession()[self::SESSION_LIST][$controlId]))
		{
			Application::getInstance()->getSession()[self::SESSION_LIST][$controlId][$CID] = array(
				"TS" => $ts,
				"SESSID" => bitrix_sessid()
			);
			Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID] = array();
		}
	}

	protected function getSessionControlFiles($controlId)
	{
		$res = array();

		if(isset(Application::getInstance()->getSession()[self::SESSION_LIST][$controlId]))
		{
			foreach(Application::getInstance()->getSession()[self::SESSION_LIST][$controlId] as $CID => $arSession)
			{
				if(isset(Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID]) && is_array(
						Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID]))
				{
					$res = array_merge($res, Application::getInstance()->getSession()[self::SESSION_VAR_PREFIX.$CID]);
				}
			}
		}

		return $res;
	}
}
