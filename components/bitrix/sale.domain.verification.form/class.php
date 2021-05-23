<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader,
	Bitrix\Main\ErrorCollection,
	Bitrix\Main\Error,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Grid,
	Bitrix\Main\UI,
	Bitrix\Sale;

Loc::loadMessages(__FILE__);

/**
 * Class SaleDomainVerificationForm
 */
class SaleDomainVerificationForm extends CBitrixComponent
{
	private const DOMAIN_GRID_ID = "verified_domain_list";

	/** @var ErrorCollection $errors */
	private $errors;

	/** @var Sale\Domain\Verification\BaseManager $domainVerificationManager */
	private $domainVerificationManager;

	/**
	 * @param $params
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function onPrepareComponentParams($params)
	{
		$this->errors = new ErrorCollection();

		if (!Loader::includeModule("sale"))
		{
			$this->errors->setError(new Error(Loc::getMessage("SALE_DVF_COMPONENT_SALE_MODULE_ERROR")));
		}

		$params["ENTITY"] = (isset($params["ENTITY"]) ? trim($params["ENTITY"]) : "");
		$params["MANAGER"] = (isset($params["MANAGER"]) ? trim($params["MANAGER"]) : "");

		$this->checkRequiredParams($params);
		$this->initResult();

		return parent::onPrepareComponentParams($params);
	}

	/**
	 * @return array
	 */
	protected function listKeysSignedParameters(): array
	{
		return [
			'MANAGER',
		];
	}

	private function initResult()
	{
		$this->arResult = [
			"DOMAIN_GRID" => [
				"ID" => self::DOMAIN_GRID_ID,
				"NAV_OBJECT" => null,
				"TOTAL_ROWS_COUNT" => null,
				"VERIFIED_DOMAINS" => [],
			],
			"ERRORS" => [],
		];
	}

	/**
	 * @param $params
	 */
	private function checkRequiredParams($params)
	{
		$requiredParams = ["ENTITY", "MANAGER"];

		foreach ($requiredParams as $requiredParam)
		{
			if (empty($params[$requiredParam]))
			{
				$this->errors->setError(new Error(Loc::getMessage("SALE_DVF_COMPONENT_PARAM_REQUIRED_ERROR", [
					"#PARAM_NAME#" => $requiredParam
				])));
			}
		}

		if ($this->errors->isEmpty())
		{
			if (is_subclass_of($params["MANAGER"], Sale\Domain\Verification\BaseManager::class))
			{
				$this->domainVerificationManager = $params["MANAGER"];
			}
			else
			{
				$this->errors->setError(new Error(Loc::getMessage("SALE_DVF_COMPONENT_MANAGER_ERROR")));
			}
		}
	}

	private function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	/**
	 * @param $entity
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function prepareGrid($entity)
	{
		$gridOptions = new Grid\Options($this->arResult["DOMAIN_GRID"]["ID"]);
		$sort = $gridOptions->GetSorting(["sort" => ["ID" => "DESC"], "vars" => ["by" => "by", "order" => "order"]]);
		$navParams = $gridOptions->GetNavParams();

		$nav = new UI\PageNavigation($this->arResult["DOMAIN_GRID"]["ID"]);
		$nav->allowAllRecords(true)->setPageSize($navParams["nPageSize"])->initFromUri();

		$res = $this->domainVerificationManager::getList([
			"select" => ["ID", "DOMAIN", "PATH"],
			"filter" => [
				"ENTITY" => $entity,
			],
			"offset" => $nav->getOffset(),
			"limit" => $nav->getLimit(),
			"order" => $sort["sort"],
			"count_total" => true,
		]);
		$nav->setRecordCount($res->getCount());

		$this->arResult["DOMAIN_GRID"]["NAV_OBJECT"] = $nav;
		$this->arResult["DOMAIN_GRID"]["TOTAL_ROWS_COUNT"] = $nav->getRecordCount();

		while ($row = $res->fetch())
		{
			$this->arResult["DOMAIN_GRID"]["VERIFIED_DOMAINS"][] = [
				"data" => [
					"ID" => $row["ID"],
					"DOMAIN" => $row["DOMAIN"],
					"PATH" => $row["PATH"],
				],
				"actions" => [
					[
						"text" => Loc::getMessage("SALE_DVF_COMPONENT_GRID_ACTION_DELETE"),
						"onclick" => 'BX.Sale.DomainVerificationForm.deleteDomainAction('.$row["ID"].')',
					],
				],
			];
		}
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function initSiteList()
	{
		$this->arResult["SITE_LIST"] = $this->domainVerificationManager::getSiteList();
	}

	/**
	 * @return bool
	 */
	private function isSave()
	{
		return ($this->request->get("save") && $this->request->get("save") === "y");
	}


	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function saveDomain()
	{
		$domain = $this->request->get("domain_validation");
		if (!$domain)
		{
			$this->errors->setError(new Error(Loc::getMessage("SALE_DVF_COMPONENT_SAVE_DOMAIN_ERROR")));
			return false;
		}

		$file = $this->request->getFile("file_validation");
		if (empty($file["tmp_name"]) || empty($file["name"]))
		{
			$this->errors->setError(new Error(Loc::getMessage("SALE_DVF_COMPONENT_SAVE_FILE_ERROR")));
			return false;
		}

		$resultSetDomain = $this->domainVerificationManager::save(
			[
				"DOMAIN" => trim($domain),
				"PATH" => $file["name"],
				"ENTITY" => $this->request->get("entity"),
			],
			$file
		);
		if (!$resultSetDomain->isSuccess())
		{
			$errorMessagesList= $resultSetDomain->getErrorMessages();
			foreach ($errorMessagesList as $errorMessagesItem)
			{
				$this->errors->setError(new Error($errorMessagesItem));
			}

			return false;
		}

		return true;
	}

	private function prepareResult()
	{
		if (!$this->errors->isEmpty())
		{
			foreach ($this->errors as $error)
			{
				$this->arResult["ERRORS"][] = $error;
			}
		}
	}

	/**
	 * @return mixed|void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function executeComponent()
	{
		if (!$this->errors->isEmpty())
		{
			$this->printErrors();
			return;
		}

		if ($this->isSave())
		{
			$this->saveDomain();
		}

		$this->prepareGrid($this->arParams["ENTITY"]);
		$this->initSiteList();
		$this->prepareResult();

		$this->includeComponentTemplate();
	}
}