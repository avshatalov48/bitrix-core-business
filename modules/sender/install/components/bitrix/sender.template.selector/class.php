<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;

use Bitrix\Sender\Templates;
use Bitrix\Sender\Message;
use Bitrix\Sender\Security;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderTemplateSelectorComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		if (!isset($this->arParams['MESSAGE_CODE']))
		{
			$this->arParams['MESSAGE_CODE'] = Message\iBase::CODE_MAIL;
		}
		$this->arParams['IS_TRIGGER'] = isset($this->arParams['IS_TRIGGER']) ? (bool) $this->arParams['IS_TRIGGER'] : false;
	}

	protected function prepareResult()
	{
		if (!Security\Access::current()->canViewLetters())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';
		$this->arResult['GRID'] = array(
			'rows'=> array(),
			'items'=> array(),
			'type'=> $this->arParams['MESSAGE_CODE'],
		);

		$selector = Templates\Selector::create()
			->withMessageCode($this->arParams['MESSAGE_CODE'])
			->withVersion(2)
			->withTriggers($this->arParams['IS_TRIGGER']);

		$this->arResult['GRID']['rows'] = $selector->getCategories();
		$templateCounter = 0;
		foreach ($selector->getList() as $template)
		{
			$messageFields = array();
			foreach ($template['FIELDS'] as $field)
			{
				$onDemand = isset($field['ON_DEMAND']) && $field['ON_DEMAND'];
				$messageFields[] = array(
					'code' => $field['CODE'],
					'value' => $onDemand ? null : $field['VALUE'],
					'onDemand' => $onDemand,
				);
			}
			
			$this->arResult['GRID']['items'][] = array(
				'id' => $template['TYPE'] . '|' . $template['ID'] . '|' . (++$templateCounter),
				'name' => $template['NAME'],
				'description' => $template['DESC'],
				'image' => $template['ICON'],
				'hot' => $template['HOT'],
				'hint' => $template['HINT'],
				'rowId' => $template['CATEGORY'],
				'data' => array(
					'templateId' => $template['ID'],
					'templateType' => $template['TYPE'],
					'messageFields' => $messageFields,
					'segments' => $template['SEGMENTS'],
					'dispatch' => $template['DISPATCH'],
				)
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