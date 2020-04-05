<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Security;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Integration;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderContactEditComponent extends \CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var Entity\Contact $entityContact */
	protected $entityContact;

	protected function checkRequiredParams()
	{
		return $this->errors->count() == 0;
	}

	protected function initParams()
	{
		$request = Context::getCurrent()->getRequest();

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? (bool) $this->arParams['SET_TITLE'] : true;
		$this->arParams['SHOW_SETS'] = isset($this->arParams['SHOW_SETS']) ? (bool) $this->arParams['SHOW_SETS'] : false;
		$this->arParams['SHOW_CAMPAIGNS'] = isset($this->arParams['SHOW_CAMPAIGNS'])
			?
			(bool) $this->arParams['SHOW_CAMPAIGNS']
			:
			Integration\Bitrix24\Service::isCampaignsAvailable();
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::current()->canModifySegments();

		if (!isset($this->arParams['ID']))
		{
			$this->arParams['ID'] = 0;
		}
		if (!$this->arParams['ID'])
		{
			$this->arParams['ID'] = (int) $request->get('ID');
		}
	}

	protected function preparePost()
	{
		$setList = $this->request->get('SET_LIST');
		$subList = $this->request->get('SUB_LIST');
		$unsubList = $this->request->get('UNSUB_LIST');

		$data = [
			"NAME"	=> $this->request->get('NAME'),
			"TYPE_ID"	=> $this->request->get('TYPE_ID'),
			"CODE"	=> $this->request->get('CODE'),
		];

		if ($this->arParams['SHOW_SETS'])
		{
			$data["SET_LIST"] = is_array($setList) ? $setList : [];
		}
		if ($this->arParams['SHOW_CAMPAIGNS'])
		{
			$data["SUB_LIST"] = is_array($subList) ? $subList : [];
			$data["UNSUB_LIST"] = is_array($unsubList) ? $unsubList : [];
		}

		$this->entityContact->mergeData($data)->save();
		$this->errors->add($this->entityContact->getErrors());

		if ($this->errors->isEmpty())
		{
			$path = str_replace('#id#', $this->entityContact->getId(), $this->arParams['PATH_TO_EDIT']);
			$uri = new Uri($path);
			if ($this->request->get('IFRAME') == 'Y')
			{
				$uri->addParams(array('IFRAME' => 'Y'));
				$uri->addParams(array('IS_SAVED' => 'Y'));
			}
			$path = $uri->getLocator();

			LocalRedirect($path);
		}
	}

	protected function prepareResult()
	{
		if ($this->arParams['SET_TITLE'] == 'Y')
		{
			$GLOBALS['APPLICATION']->SetTitle(
				$this->arParams['ID'] > 0
					?
					Loc::getMessage('SENDER_COMP_CONTACT_EDIT_TITLE_EDIT')
					:
					Loc::getMessage('SENDER_COMP_CONTACT_EDIT_TITLE_ADD')
			);
		}

		if (!Security\Access::current()->canViewSegments())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();

		$this->entityContact = new Entity\Contact($this->arParams['ID']);
		$this->arResult['ROW'] = $this->entityContact->getData();

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}

		$types = Recipient\Type::getNamedList();
		$this->arResult['TYPES'] = [];
		foreach ($types as $typeId => $typeName)
		{
			if (!in_array($typeId, [Recipient\Type::PHONE, Recipient\Type::EMAIL]))
			{
				continue;
			}

			$this->arResult['TYPES'][] = [
				'ID' => $typeId,
				'NAME' => $typeName,
				'SELECTED' => $this->arResult['ROW'] && $this->arResult['ROW']['TYPE_ID'] == $typeId,
			];
		}

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

		$this->printErrors();
		$this->includeComponentTemplate();
	}
}