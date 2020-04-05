<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Copy\Integration\Helper as Helper;

// todo Improve the component! Show more informative messages and even give the opportunity to copy the entity again.
class SocialnetworkCopyChecker extends CBitrixComponent implements Controllerable, Errorable
{
	use ErrorableImplementation;

	/**
	 * @var Helper|null
	 */
	private $helper = null;
	private $moduleId = "";

	public function configureActions()
	{
		return [];
	}

	protected function listKeysSignedParameters()
	{
		return [
			"QUEUE_ID"
		];
	}

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		$params["QUEUE_ID"] = (int) ($params["QUEUE_ID"] ?? 0);
		$params["HELPER"] = ($params["HELPER"] instanceof Helper ? $params["HELPER"] : null);

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();

			$this->helper = $this->arParams["HELPER"];
			if (!$this->helper)
			{
				throw new SystemException("System error");
			}
			$this->moduleId = $this->helper->getModuleId();

			$this->setResult();

			$this->includeComponentTemplate();
		}
		catch (SystemException $exception)
		{
			ShowError($exception->getMessage());
		}
	}

	public function deleteErrorOptionAction()
	{
		$request = Context::getCurrent()->getRequest();
		$post = $request->getPostList()->toArray();

		if (!empty($post["moduleId"]) && !empty($post["errorOptionName"]))
		{
			Option::delete(
				$post["moduleId"],
				["name" => $post["errorOptionName"].$this->arParams["QUEUE_ID"]]
			);
		}
	}

	/**
	 * @throws SystemException
	 */
	private function checkModules()
	{
		try
		{
			if (!Loader::includeModule("socialnetwork"))
			{
				throw new SystemException("Module \"socialnetwork\" not found");
			}
		}
		catch (LoaderException $exception)
		{
			throw new SystemException("System error");
		}
	}

	private function setResult()
	{
		$this->arResult = [];

		$this->arResult["QUEUE_ID"] = $this->arParams["QUEUE_ID"];

		$this->arResult["HELPER"] = $this->helper;

		$this->arResult["SHOW_PROGRESS"] = $this->isShowProgress();
		$this->arResult["IDS_WITH_ERRORS"] = $this->getMapIdsWithErrors();
	}

	private function isShowProgress()
	{
		return (
			Option::get(
				$this->moduleId,
				$this->helper->getOptionNames()["checker"].$this->arParams["QUEUE_ID"],
				""
			) == "Y"
		);
	}

	private function getMapIdsWithErrors()
	{
		$option = Option::get(
			$this->moduleId,
			$this->helper->getOptionNames()["error"].$this->arParams["QUEUE_ID"],
			""
		);
		$option = ($option !== "" ? unserialize($option) : []);
		return (is_array($option) ? $option : []);
	}
}