<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2016 Bitrix
 */

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class PersonalProfileList extends CBitrixComponent
{
	const E_SALE_MODULE_NOT_INSTALLED 		= 10000;
	const E_NOT_AUTHORIZED					= 10001;

	/** @var  Main\ErrorCollection $errorCollection*/
	protected $errorCollection;

	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param mixed[] $params List of unchecked parameters
	 * @return mixed[] Checked and valid parameters
	 */
	public function onPrepareComponentParams($params)
	{
		$this->errorCollection = new Main\ErrorCollection();

		$params["PATH_TO_DETAIL"] = trim($params["PATH_TO_DETAIL"]);

		if (strlen($params["PATH_TO_DETAIL"]) <= 0)
		{
			$params["PATH_TO_DETAIL"] = htmlspecialcharsbx(Main\Context::getCurrent()->getRequest()->getRequestedPage()."?ID=#ID#");
		}

		$params["PER_PAGE"] = ((int)($params["PER_PAGE"]) <= 0 ? 20 : (int)($params["PER_PAGE"]));

		return $params;
	}

	public function executeComponent()
	{
		global $APPLICATION, $USER;

		Loc::loadMessages(__FILE__);

		$this->setFrameMode(false);

		$this->checkRequiredModules();

		$this->arResult['ERRORS'] = array();
		if (!$USER->IsAuthorized())
		{
			if(!$this->arParams['AUTH_FORM_IN_TEMPLATE'])
			{
				$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"), false, false, 'N', false);
			}
			else
			{
				$this->arResult['ERRORS'][self::E_NOT_AUTHORIZED] = GetMessage("SALE_ACCESS_DENIED");
			}
		}

		if($this->arParams["SET_TITLE"] == 'Y')
			$APPLICATION->SetTitle(GetMessage("SPPL_DEFAULT_TITLE"));

		$request = Main\Context::getCurrent()->getRequest();

		$errorMessage = "";
		$deleteElementId = (int)($request->get("del_id"));
		if ($deleteElementId > 0 && check_bitrix_sessid())
		{
			$dbUserProps = CSaleOrderUserProps::GetList(
				array(),
				array(
					"ID" => $deleteElementId,
					"USER_ID" => (int)($USER->GetID())
				)
			);
			if ($arUserProps = $dbUserProps->Fetch())
			{
				if (!CSaleOrderUserProps::Delete($arUserProps["ID"]))
				{
					$errorMessage = GetMessage("SALE_DEL_PROFILE");
				}
			}
			else
			{
				$errorMessage = GetMessage("SALE_NO_PROFILE");
			}
			if(strlen($errorMessage) > 0)
				LocalRedirect($APPLICATION->GetCurPageParam("del_id=".$deleteElementId, Array("del_id", "sessid")));
			else
				LocalRedirect($APPLICATION->GetCurPageParam("success_del_id=".$deleteElementId, Array("del_id", "sessid")));
		}

		if((int)($_REQUEST["del_id"]) > 0)
			$errorMessage = GetMessage("SALE_DEL_PROFILE", array("#ID#" => (int)($_REQUEST["del_id"])));
		elseif((int)($_REQUEST["success_del_id"]) > 0)
			$errorMessage = GetMessage("SALE_DEL_PROFILE_SUC", array("#ID#" => (int)($_REQUEST["success_del_id"])));

		if(strlen($errorMessage) > 0)
		{
			$this->arResult["ERROR_MESSAGE"] = $errorMessage;
			$this->arResult['ERRORS'][] = $errorMessage;
		}

		$by = (strlen($_REQUEST["by"])>0 ? $_REQUEST["by"]: "DATE_UPDATE");
		$order = (strlen($_REQUEST["order"])>0 ? $_REQUEST["order"]: "DESC");

		$dbUserProps = CSaleOrderUserProps::GetList(
			array($by => $order),
			array("USER_ID" => (int)($GLOBALS["USER"]->GetID()))
		);
		$dbUserProps->NavStart($this->arParams["PER_PAGE"]);
		$this->arResult["NAV_STRING"] = $dbUserProps->GetPageNavString(GetMessage("SPPL_PAGES"));
		$this->arResult["PROFILES"] = Array();
		while($arUserProps = $dbUserProps->GetNext())
		{
			$arResultTmp = $arUserProps;
			$personTypeList = Bitrix\Sale\PersonType::load(SITE_ID, $arUserProps["PERSON_TYPE_ID"]);
			$arResultTmp["PERSON_TYPE"] = $personTypeList[$arUserProps["PERSON_TYPE_ID"]];
			$arResultTmp["PERSON_TYPE"]["NAME"] = htmlspecialcharsEx($arResultTmp["PERSON_TYPE"]["NAME"]);

			$arResultTmp["URL_TO_DETAIL"] = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_DETAIL"], Array("ID" => $arUserProps["ID"]));
			if (empty($this->arParams['PATH_TO_DELETE']))
			{
				$arResultTmp["URL_TO_DETELE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?del_id=".$arUserProps["ID"]."&".bitrix_sessid_get());
			}
			else
			{
				$arResultTmp["URL_TO_DETELE"] = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_DELETE"], Array("ID" => $arUserProps["ID"]))."&".bitrix_sessid_get();
			}
			$this->arResult["PROFILES"][] = $arResultTmp;
		}

		if ($request->get('SECTION'))
		{
			$this->arResult["URL"] = htmlspecialcharsbx($request->getRequestedPage()."?SECTION=".$request->get('SECTION')."&");
		}
		else
		{
			$this->arResult["URL"] = htmlspecialcharsbx($request->getRequestedPage()."?");
		}

		$this->includeComponentTemplate();
	}


	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function checkRequiredModules()
	{
		if (!Loader::includeModule('sale'))
		{
			throw new Main\SystemException(Loc::getMessage("SALE_MODULE_NOT_INSTALL"), self::E_SALE_MODULE_NOT_INSTALLED);
		}
	}
}