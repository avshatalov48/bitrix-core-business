<?

use Bitrix\Main\Context;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Security;
use Bitrix\Sender\UI;

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

class SenderCampaignEditComponent extends \Bitrix\Sender\Internals\CommonSenderComponent
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

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? (bool) $this->arParams['SET_TITLE'] : true;
		$this->arParams['IS_TRIGGER'] = isset($this->arParams['IS_TRIGGER']) ? (bool) $this->arParams['IS_TRIGGER'] : false;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::getInstance()->canModifyLetters();

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
		$data = Array(
			"NAME"	=> $this->request->get('NAME'),
			"DESCRIPTION"	=> $this->request->get('DESCRIPTION'),
			"ACTIVE"	=> $this->request->get('ACTIVE') === 'Y' ? 'Y' : 'N',
			"IS_PUBLIC"	=> $this->request->get('IS_PUBLIC') === 'Y' ? 'Y' : 'N',
			"SITE_ID"	=> $this->request->get('SITE_ID'),
		);

		$this->entityCampaign->mergeData($data)->save();
		$this->errors->add($this->entityCampaign->getErrors());

		if ($this->errors->isEmpty())
		{
			$path = str_replace('#id#', $this->entityCampaign->getId(), $this->arParams['PATH_TO_EDIT']);
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
					Loc::getMessage('SENDER_COMP_CAMPAIGN_EDIT_TITLE_EDIT')
					:
					Loc::getMessage('SENDER_COMP_CAMPAIGN_EDIT_TITLE_ADD')
			);
		}

		if (!Security\Access::getInstance()->canViewLetters())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();

		if ($this->arParams['IS_TRIGGER'])
		{
			$this->entityCampaign = new Entity\TriggerCampaign($this->arParams['ID']);
		}
		else
		{
			$this->entityCampaign = new Entity\Campaign($this->arParams['ID']);
		}

		$this->arResult['ROW'] = $this->entityCampaign->getData();
		$this->arResult['SITES'] = $this->getCampaignSites();

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}

		$this->arResult['CAMPAIGN_TILE'] = UI\TileView::create()->getTile(
			$this->arResult['ROW']['ID'],
			$this->arResult['ROW']['NAME']
		);
		$this->arResult['IS_SAVED'] = $this->request->get('IS_SAVED') == 'Y';

		return true;
	}

	protected function getCampaignSites()
	{
		static $sites = null;
		if ($sites === null)
		{
			$sites = \Bitrix\Main\SiteTable::getList(['select' => ['ID' => 'LID', 'NAME']])->fetchAll();
			foreach ($sites as $index => $site)
			{
				$site['SELECTED'] = $this->arResult['ROW']['SITE_ID'] === $site['ID'];
				$sites[$index] = $site;
			}
		}

		return $sites;
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
		return null;
	}

	public function getViewAction()
	{
		return \Bitrix\Sender\Access\ActionDictionary::ACTION_MAILING_VIEW;
	}
}