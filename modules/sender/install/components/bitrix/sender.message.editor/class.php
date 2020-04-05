<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

use Bitrix\Sender\Message;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderMessageEditorComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (!$this->arParams['MESSAGE_CODE'])
		{
			$this->errors->setError(new Error('Message code is not set.'));
		}

		return $this->errors->count() == 0;
	}

	protected function initParams()
	{
		$this->arParams['VALUE'] = isset($this->arParams['VALUE']) ? $this->arParams['VALUE'] : '';
		$this->arParams['MESSAGE_CODE'] = isset($this->arParams['MESSAGE_CODE']) ? $this->arParams['MESSAGE_CODE'] : null;
		$this->arParams['MESSAGE_ID'] = isset($this->arParams['MESSAGE_ID']) ? $this->arParams['MESSAGE_ID'] : null;
		$this->arParams['MESSAGE'] = isset($this->arParams['MESSAGE']) ? $this->arParams['MESSAGE'] : null;

		$this->arParams['TEMPLATE_TYPE'] = isset($this->arParams['TEMPLATE_TYPE']) ? $this->arParams['TEMPLATE_TYPE'] : null;
		$this->arParams['TEMPLATE_ID'] = isset($this->arParams['TEMPLATE_ID']) ? $this->arParams['TEMPLATE_ID'] : null;
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';
		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();

		// init message
		$message = $this->arParams['MESSAGE'];
		if (!$message)
		{
			try
			{
				$message = Message\Adapter::getInstance($this->arParams['MESSAGE_CODE']);
				$message->loadConfiguration($this->arParams['MESSAGE_ID']);
			}
			catch (\Bitrix\Main\SystemException $exception)
			{
				$this->errors->setError(new Error(Loc::getMessage(
					'SENDER_MESSAGE_EDITOR_ERROR_UNKNOWN_CODE',
					array('%code%' => $this->arParams['MESSAGE_CODE'])
				)));

				return false;
			}
		}


		// get options list
		$configuration = $message->getConfiguration();
		$this->arResult['MESSAGE_VIEW'] = $configuration->getView();
		$this->arResult['LIST'] = array(
			array(
				'options' => Message\Configuration::convertToArray(
					$configuration->getOptionsByGroup(Message\ConfigurationOption::GROUP_DEFAULT)
				),
				'isAdditional' => false,
			)
		);

		$options = Message\Configuration::convertToArray(
			$configuration->getOptionsByGroup(Message\ConfigurationOption::GROUP_ADDITIONAL)
		);
		if (count($options) > 0)
		{
			$this->arResult['LIST'][] = array(
				'options' => $options,
				'isAdditional' => true,
			);
		}


		$this->arResult['IS_SUPPORT_TESTING'] = $message->getTester()->isSupport();
		$this->arResult['MESSAGE_CODE'] = $message->getCode();
		$this->arResult['MESSAGE_ID'] = $message->getId();

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
		if (!Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			$this->printErrors();
			return;
		}

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