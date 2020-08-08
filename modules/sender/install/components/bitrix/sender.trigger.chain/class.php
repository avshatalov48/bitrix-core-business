<?

use Bitrix\Main\Context;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Security;

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

class SenderTriggerChainComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var Entity\Campaign $entityCampaign */
	protected $entityCampaign;

	protected function checkRequiredParams()
	{
		return $this->errors->count() == 0;
	}

	protected function initParams()
	{
		$request = Context::getCurrent()->getRequest();

		parent::initParams();
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? (bool) $this->arParams['SET_TITLE'] : true;

		if (empty($this->arParams['ID']))
		{
			$this->arParams['ID'] = (int) $request->get('ID');
		}
	}

	protected function prepareResult()
	{
		if ($this->arParams['SET_TITLE'] == 'Y')
		{
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_COMP_TRIGGER_CHAIN_TITLE'));
		}

		if (!Security\Access::getInstance()->canViewLetters())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		$this->entityCampaign = new Entity\TriggerCampaign($this->arParams['ID']);
		$this->arResult['ROW'] = $this->entityCampaign->getData();

		$this->arResult['LETTERS'] = [];
		$letters = $this->entityCampaign->getChain()->getList();
		foreach ($letters as $letter)
		{
			$title = $letter->get('TITLE');
			/*
			if ($letter->getMessage()->getCode() === Message\iBase::CODE_MAIL)
			{
				$title = $letter->getMessage()->getConfiguration()->get('SUBJECT');
			}
			*/

			$this->arResult['LETTERS'][] = [
				'ID' => $letter->getId(),
				'TITLE' => $title,
				'DATE_INSERT' => $letter->get('DATE_INSERT'),
				'CREATED_BY' => $letter->get('CREATED_BY'),
				'CREATED_BY_DISPLAY' => $letter->get('CREATED_BY'),
				'TIME_SHIFT' => $letter->get('TIME_SHIFT'),
				'USER_NAME' => $letter->get('USER_NAME'),
				'USER_LAST_NAME' => $letter->get('USER_LAST_NAME'),
				'USER_ID' => $letter->get('USER_ID'),
				'URLS' => [
					'EDIT' => str_replace(
						['#campaign_id#', '#id#'],
						[$this->arParams['ID'], $letter->getId()],
						$this->arParams['PATH_TO_LETTER_EDIT']
					),
				]
			];
		}

		$this->arParams['PATH_TO_LETTER_ADD'] = str_replace(
			['#campaign_id#'], [$this->arParams['ID']], $this->arParams['PATH_TO_LETTER_ADD']
		);
		$this->arResult['IS_SAVED'] = $this->request->get('IS_SAVED') == 'Y';

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
		parent::executeComponent();
		parent::prepareResultAndTemplate();
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_MAILING_EMAIL_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_MAILING_VIEW;
	}
}