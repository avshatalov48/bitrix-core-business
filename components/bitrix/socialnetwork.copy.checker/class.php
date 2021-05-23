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

// todo Improve the component! Show more informative messages and even give the opportunity to copy the entity again.
class SocialnetworkCopyChecker extends CBitrixComponent implements Controllerable, Errorable
{
	use ErrorableImplementation;

	public function configureActions()
	{
		return [];
	}

	protected function listKeysSignedParameters()
	{
		return [
			"queueId"
		];
	}

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		$params["moduleId"] = (string) ($params["moduleId"] ?? "");
		$params["queueId"] = (int) ($params["queueId"] ?? 0);

		$params["stepperClassName"] = (string) ($params["stepperClassName"] ?? "");
		$params["checkerOption"] = (string) ($params["checkerOption"] ?? "");
		$params["errorOption"] = (string) ($params["errorOption"] ?? "");

		$params["titleMessage"] = (string) ($params["titleMessage"] ?? "");
		$params["errorMessage"] = (string) ($params["errorMessage"] ?? "");

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();

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

		if (!empty($post["moduleId"]) && !empty($post["errorOption"]))
		{
			Option::delete(
				$post["moduleId"],
				["name" => $post["errorOption"].$this->arParams["queueId"]]
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

		$this->arResult["moduleId"] = $this->arParams["moduleId"];
		$this->arResult["queueId"] = $this->arParams["queueId"];

		$this->arResult["stepperClassName"] = $this->arParams["stepperClassName"];
		$this->arResult["checkerOption"] = $this->arParams["checkerOption"];
		$this->arResult["errorOption"] = $this->arParams["errorOption"];

		$this->arResult["showProgress"] = $this->isShowProgress();
		$this->arResult["showError"] = (!empty($this->getErrorIds()));

		$this->arResult["titleMessage"] = $this->arParams["titleMessage"];
		$this->arResult["errorMessage"] = $this->getErrorMessage();
	}

	private function isShowProgress()
	{
		return (
			Option::get(
				$this->arParams["moduleId"],
				$this->arParams["checkerOption"].$this->arParams["queueId"],
				""
			) == "Y"
		);
	}

	private function getErrorIds(): array
	{
		$option = Option::get(
			$this->arParams["moduleId"],
			$this->arResult["errorOption"].$this->arParams["queueId"],
			""
		);
		$option = ($option !== "" ? unserialize($option, [ 'allowed_classes' => false ]) : []);

		return (is_array($option) ? $option : []);
	}

	private function getErrorMessage(): string
	{
		return $this->arParams["errorMessage"].implode(", ", $this->getErrorIds());
	}
}