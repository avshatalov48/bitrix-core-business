<?php

use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\UI\FileInput;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class UIImageInput extends \CBitrixComponent implements Errorable
{
	use ErrorableImplementation;

	protected function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}

	public function onPrepareComponentParams($params): array
	{
		$params['FILE_SETTINGS'] = $params['FILE_SETTINGS'] ?? [];

		if (!is_array($params['FILE_SETTINGS']))
		{
			$this->errorCollection->setError(new \Bitrix\Main\Error('File parameters must be an array.'));
		}

		$params['FILE_VALUES'] = $params['FILE_VALUES'] ?? [];

		if (!is_array($params['FILE_VALUES']))
		{
			$this->errorCollection->setError(new \Bitrix\Main\Error('File values must be an array.'));
		}

		return parent::onPrepareComponentParams($params);
	}

	public function executeComponent(): void
	{
		if ($this->hasErrors())
		{
			$this->showErrors();

			return;
		}

		$this->initializeFileInstance();
		$this->includeComponentTemplate();
	}

	private function initializeFileInstance(): void
	{
		$this->arResult['FILE'] = new FileInput($this->arParams['FILE_SETTINGS']);
	}
}