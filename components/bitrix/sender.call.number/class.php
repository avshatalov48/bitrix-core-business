<?

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

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

class SenderCallNumberComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (!\Bitrix\Main\Loader::includeModule('sender'))
		{
			$this->errors->setError(new \Bitrix\Main\Error('Module `sender` is not installed.'));
			return false;
		}
		if (!\Bitrix\Main\Loader::includeModule('voximplant'))
		{
			$this->errors->setError(new \Bitrix\Main\Error('Module `voximplant` is not installed.'));
			return false;
		}

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
		$this->arResult['HAS_REST'] = \Bitrix\Main\Loader::includeModule('rest') && false;
		if ($list)
		{
			$internal = [
				'fromRest' => false,
				'id' => 'internal',
				'name' => Loc::getMessage("SENDER_CALL_NUMBER_BITRIX_PHONES"),
				'numbers' => []
			];
			foreach ($list as $key => $value)
			{
				$internal['numbers'][] = array(
					'id' => $key,
					'name' => $value,
				);
			}
			$this->arResult['LIST'][] = $internal;
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