<?php

use Bitrix\MobileApp\Janative\Entity\Component;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Class MobileJSComponent
 */
class JNRouterComponent extends \CBitrixComponent
{
	const VERSION = "1";

	private $name;
	private $namespace;

	public function onPrepareComponentParams($arParams)
	{
		if ($arParams["componentName"])
		{
			$componentName = $arParams["componentName"];
			$namespace = $arParams["namespace"];
			if (!$namespace)
			{
				if (strpos($componentName, ":") > 0)
				{
					list($namespace, $componentName) = explode(":", $componentName);
				}
				else
				{
					$namespace = "bitrix";
				}
			}

			$this->namespace = $namespace;
			$this->name = ($this->namespace ? $this->namespace : "") . ":" . $componentName;
		}

		return $arParams;
	}

	/**
	 * @return mixed|void
	 * @throws \Bitrix\Main\LoaderException
	 * @throws Exception
	 */
	public function executeComponent()
	{
		global $USER;
		$allowExecute = true;
		if(!$USER->isAuthorized() && $this->arParams["needAuth"] === true)
		{
			$allowExecute = $USER->LoginByHttpAuth();
		}

		if(!$allowExecute)
		{
			header("HTTP/1.0 401 Not Authorized");
			header('WWW-Authenticate: Basic realm="Bitrix24"');
			header("Content-Type: application/json");
			header("BX-Authorize: ".bitrix_sessid());
			echo \Bitrix\Main\Web\Json::encode([
				"status" => "failed",
				"bitrix_sessid"=>bitrix_sessid()
			]);
			die();
		}

		\Bitrix\Main\Loader::includeModule("mobileapp");
		$component = Component::createInstanceByName($this->name);
		header('Content-Type: text/javascript;charset=UTF-8');
		if ($component == null)
		{
			header("BX-Component-Not-Found: true");
			echo <<<JS
console.warn("Component not found");
JS;
		}
		else
		{
			$resultOnly = array_key_exists("get_result", $_REQUEST);
			$component->execute($resultOnly);
		}
	}
}