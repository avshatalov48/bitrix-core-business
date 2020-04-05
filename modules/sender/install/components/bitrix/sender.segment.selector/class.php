<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;

use Bitrix\Sender\Message;
use Bitrix\Sender\Segment;
use Bitrix\Sender\Dispatch;
use Bitrix\Sender\Security;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderSegmentSelectorComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['READONLY'] = isset($this->arParams['READONLY']) ? (bool) $this->arParams['READONLY'] : false;
		$this->arParams['RECIPIENT_COUNT'] = isset($this->arParams['RECIPIENT_COUNT']) ? $this->arParams['RECIPIENT_COUNT'] : null;
		$this->arParams['IS_RECIPIENT_COUNT_EXACT'] = isset($this->arParams['IS_RECIPIENT_COUNT_EXACT']) ? $this->arParams['IS_RECIPIENT_COUNT_EXACT'] : true;
		$this->arParams['DURATION_FORMATTED'] = isset($this->arParams['DURATION_FORMATTED']) ? $this->arParams['DURATION_FORMATTED'] : null;
		$this->arParams['SHOW_COUNTERS'] = isset($this->arParams['SHOW_COUNTERS']) ? $this->arParams['SHOW_COUNTERS'] : true;

		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : '';
		if (!isset($this->arParams['INCLUDE']) || !is_array($this->arParams['INCLUDE']))
		{
			$this->arParams['INCLUDE'] = array();
		}
		if (!isset($this->arParams['EXCLUDE']) || !is_array($this->arParams['EXCLUDE']))
		{
			$this->arParams['EXCLUDE'] = array();
		}

		if (!isset($this->arParams['MESSAGE_CODE']))
		{
			$this->arParams['MESSAGE_CODE'] = Message\iBase::CODE_MAIL;
		}

		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::current()->canModifySegments();
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		$message = null;
		if ($this->arParams['MESSAGE_CODE'])
		{
			$message = Message\Adapter::create($this->arParams['MESSAGE_CODE']);
		}

		$this->arResult['SEGMENTS'] = array();
		$scopes = array('INCLUDE' => true, 'EXCLUDE' => false);
		foreach ($scopes as $scopeCode => $scopeValue)
		{
			$list = $this->arParams[$scopeCode] ?: array();
			$tiles = Segment\TileView::create($scopeValue)
				->setMessage($message)
				->getTiles(array('filter' => array('=ID' => $list)));
			$this->arResult['SEGMENTS'][$scopeCode] = $tiles;
		}

		$this->arResult['HAS_EXCLUDE_SEGMENTS'] = count($this->arResult['SEGMENTS']['EXCLUDE']) > 0;

		$this->arResult['DURATION'] = array(
			'warnInterval' => Dispatch\Duration::getWarnInterval(),
			'minimalInterval' => Dispatch\Duration::getMinimalInterval(),
			'maximalInterval' => Dispatch\Duration::getMaximalInterval(),
			'formattedMinimalInterval' => Dispatch\Duration::getFormattedMinimalInterval(),
			'formattedMaximalInterval' => Dispatch\Duration::getFormattedMaximalInterval(),
		);

		$this->arResult['RECIPIENT_TYPES'] = $message->getSupportedRecipientTypes();

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