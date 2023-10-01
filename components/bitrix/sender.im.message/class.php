<?

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Security;
use Bitrix\Sender\Integration;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
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
		$this->arResult['TEMPLATE_OPTIONS_SELECTOR'] = \Bitrix\Sender\Message\Helper::getTemplateOptionSelector();
		$userId = Security\User::current()->getId();
		$this->arResult['AITextContextId'] = 'sender_marketing_im_message_text_' . $userId;

		$this->arResult['isAITextAvailable'] = Integration\AI\Controller::isAvailable(
			Integration\AI\Controller::TEXT_CATEGORY,
			$this->arResult['AITextContextId']
		);

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
