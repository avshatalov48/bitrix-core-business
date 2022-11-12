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

		\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

		if (!Main\Loader::includeModule('bizproc'))
		{
			return false;
		}

		$scriptId = $this->arParams['SCRIPT_ID'];
		$isNew = empty($scriptId);

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$helpButton = \Bitrix\UI\Buttons\CreateButton::create([
				'text' => Main\Localization\Loc::getMessage('BP_SCR_ED_CMP_HELP_BUTTON_TITLE'),
				'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
				'dataset' => [
					'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::INFO,
				],
				'click' => new \Bitrix\UI\Buttons\JsCode(
					"top.BX.Helper.show('redirect=detail&code=13281632');",
				),
			]);

			$APPLICATION->SetTitle(GetMessage($isNew? "BP_SCR_ED_CMP_TITLE_NEW" : "BP_SCR_ED_CMP_TITLE"));
			\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($helpButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
		}

		if ($isNew && empty($this->arParams['DOCUMENT_TYPE']))
		{
			return $this->showError(GetMessage("BP_SCR_ED_CMP_SCRIPT_CREATE_ERROR"));
		}

		$userId = Main\Engine\CurrentUser::get()->getId();

		if ($isNew && !\Bitrix\Bizproc\Script\Manager::canUserCreateScript($this->arParams['DOCUMENT_TYPE'], $userId))
		{
			return $this->showError(GetMessage("BP_SCR_ED_CMP_SCRIPT_CAN_CREATE_ERROR"));
		}

		if (!$isNew && !\Bitrix\Bizproc\Script\Manager::canUserEditScript($scriptId, $userId))
		{
			return $this->showError(GetMessage("BP_SCR_ED_CMP_SCRIPT_CAN_EDIT_ERROR"));
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
			return $this->showError(GetMessage("BP_SCR_ED_CMP_SCRIPT_NOT_FOUND"));
		}

		$documentType = [$script['MODULE_ID'], $script['ENTITY'], $script['DOCUMENT_TYPE']];

		$this->arResult['SCRIPT'] = $script;
		$this->arResult['DOCUMENT_TYPE_SIGNED'] = CBPDocument::signDocumentType($documentType);

		$this->includeComponentTemplate();
	}

	protected function showError($message): bool
	{
		$this->arResult['errorMessage'] = (string)$message;
		$this->includeComponentTemplate('error');

		return false;
	}
}