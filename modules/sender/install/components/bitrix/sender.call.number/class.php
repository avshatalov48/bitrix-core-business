<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Sender\Integration\MessageService\Sms\Service;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderCallNumberComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : 'SENDER';
		$this->arParams['VALUE'] = isset($this->arParams['VALUE']) ? $this->arParams['VALUE'] : '';
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URL'] = $this->getPath() . '/ajax.php';

		$list = \CVoxImplantConfig::GetPortalNumbers(false);
		$this->arResult['VALUE'] = $this->arParams['VALUE'];
		$this->arResult['SETUP_URI'] = '/telephony/lines.php';
		$this->arResult['LIST'] = array();
		foreach ($list as $key => $value)
		{
			$this->arResult['LIST'][] = array(
				'id' => $key,
				'name' => $value,
			);
		}

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