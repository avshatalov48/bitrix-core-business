<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;

class BizprocScriptEditComponent extends \CBitrixComponent
{
	protected function listKeysSignedParameters()
	{
		return ['SCRIPT_ID'];
	}

	public function onPrepareComponentParams($params)
	{
		$params["SCRIPT_ID"] = (int) $params["SCRIPT_ID"];
		if (isset($params['DOCUMENT_TYPE_SIGNED']))
		{
			$params['DOCUMENT_TYPE_SIGNED'] = htmlspecialcharsback($params['DOCUMENT_TYPE_SIGNED']);
			$params['DOCUMENT_TYPE'] = CBPDocument::unSignDocumentType($params['DOCUMENT_TYPE_SIGNED']);
		}

		$params["PLACEMENT"] = $params["PLACEMENT"]? (string)$params["PLACEMENT"] : null;
		$params["SET_TITLE"] = ($params["SET_TITLE"] == "N" ? "N" : "Y");

		return $params;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		if (!Main\Loader::includeModule('bizproc'))
		{
			return false;
		}

		$scriptId = $this->arParams['SCRIPT_ID'];
		$isNew = empty($scriptId);

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$APPLICATION->SetTitle(GetMessage($isNew? "BP_SCR_ED_CMP_TITLE_NEW" : "BP_SCR_ED_CMP_TITLE"));
		}

		if ($isNew && (empty($this->arParams['DOCUMENT_TYPE']) || empty($this->arParams['PLACEMENT'])))
		{
			ShowError(GetMessage("BP_SCR_ED_CMP_SCRIPT_CREATE_ERROR"));
			return;
		}

		if ($isNew)
		{
			$script = \Bitrix\Bizproc\Script\Manager::createScript(
				$this->arParams['DOCUMENT_TYPE']
			);
		}
		else
		{
			$script = \Bitrix\Bizproc\Script\Manager::getById($scriptId)->collectValues();
		}

		if (!$script)
		{
			ShowError(GetMessage("BP_SCR_ED_CMP_SCRIPT_NOT_FOUND"));
			return;
		}

		$documentType = [$script['MODULE_ID'], $script['ENTITY'], $script['DOCUMENT_TYPE']];

		$this->arResult['SCRIPT'] = $script;
		$this->arResult['DOCUMENT_TYPE_SIGNED'] = CBPDocument::signDocumentType($documentType);

		$this->includeComponentTemplate();
	}
}