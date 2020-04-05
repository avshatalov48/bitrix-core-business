<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Sender\Integration\MessageService\Sms\Service;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderImMessageEditorComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : 'TEXT';
		$this->arParams['VALUE'] = isset($this->arParams['VALUE']) ? $this->arParams['VALUE'] : null;
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URL'] = $this->getPath() . '/ajax.php';
		$this->arResult['VALUE'] = htmlspecialcharsback($this->arParams['VALUE']);
		$this->arResult['COUNT'] = count($this->arParams['VALUE']);

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}