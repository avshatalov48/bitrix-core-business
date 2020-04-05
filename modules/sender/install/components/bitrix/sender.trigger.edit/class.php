<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Security;
use Bitrix\Sender\UI;
use Bitrix\Sender\Trigger;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderTriggerEditComponent extends \CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var Entity\TriggerCampaign $entityCampaign */
	protected $entityCampaign;

	protected function checkRequiredParams()
	{
		return $this->errors->count() == 0;
	}

	protected function initParams()
	{
		$request = Context::getCurrent()->getRequest();

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? (bool) $this->arParams['SET_TITLE'] : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::current()->canModifyLetters();

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
		$endpoint = $this->request->get('ENDPOINT');
		$endpoint = is_array($endpoint) ? $endpoint : [];

		$existsList = [
			'START' => null,
			'END' => null
		];
		foreach($existsList as $type => $value)
		{
			if (empty($endpoint[$type]))
			{
				continue;
			}

			$trigger = Trigger\Manager::getOnce($endpoint[$type]);
			if($trigger)
			{
				$existsList[$type] = $endpoint[$type] + Trigger\Settings::getArrayFromTrigger($trigger);
			}
		}

		$data = [
			"NAME"	=> $this->request->get('NAME'),
			"DESCRIPTION"	=> $this->request->get('DESCRIPTION'),
			"SITE_ID"	=> $this->request->get('SITE_ID'),
			"TRIGGER_FIELDS" => $existsList,
		];
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
					Loc::getMessage('SENDER_COMP_TRIGGER_EDIT_TITLE_EDIT')
					:
					Loc::getMessage('SENDER_COMP_TRIGGER_EDIT_TITLE_ADD')
			);
		}

		if (!Security\Access::current()->canViewLetters())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();

		$this->entityCampaign = new Entity\TriggerCampaign($this->arParams['ID']);
		$this->arResult['ROW'] = $this->entityCampaign->getData();
		$this->arResult['SITES'] = $this->getCampaignSites();

		// set triggers
		$this->setCampaignTriggers();

		// process post
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

	protected function setCampaignTriggers()
	{
		$this->arResult['TRIGGERS']['AVAILABLE'] = [];
		$this->arResult['TRIGGERS']['EXISTS'] = [
			'START' => null,
			'END' => null
		];


		$list = [];
		$triggerList = Trigger\Manager::getList();
		foreach($triggerList as $trigger)
		{
			foreach(array('START', 'END') as $type)
			{
				if($type == 'END' && !$trigger->canBeTarget())
				{
					continue;
				}

				$list[$type][$trigger->getId()] = Trigger\Settings::getArrayFromTrigger($trigger);
				$list[$type][$trigger->getId()]['ID'] = $trigger->getId();
				$list[$type][$trigger->getId()]['NAME'] = $trigger->getName();

				$trigger->setFieldFormName('post_form');
				$trigger->setFieldPrefix('ENDPOINT['.$type.'][FIELDS]');
				$list[$type][$trigger->getId()]['FORM'] = $trigger->getForm();
			}
		}
		$this->arResult['TRIGGERS']['AVAILABLE'] = $list;

		if(empty($this->arResult['ROW']['TRIGGER_FIELDS']))
		{
			return;
		}

		$emptyKeys = ['CAN_RUN_FOR_OLD_DATA', 'IS_CLOSED_TRIGGER'];
		$fields = $this->arResult['ROW']['TRIGGER_FIELDS'];
		$existsList = $this->arResult['TRIGGERS']['EXISTS'];
		foreach($existsList as $type => $values)
		{
			if(!is_array($fields[$type])) continue;
			$trigger = Trigger\Manager::getOnce($fields[$type]);
			if ($trigger)
			{
				foreach ($emptyKeys as $emptyKey)
				{
					if (!isset($fields[$type][$emptyKey]))
					{
						continue;
					}
					if (!empty($fields[$type][$emptyKey]))
					{
						continue;
					}

					unset($fields[$type][$emptyKey]);
				}

				$existsList[$type] = $fields[$type] + $list[$type][$trigger->getId()];

				$trigger->setFieldFormName('post_form');
				$trigger->setFieldPrefix('ENDPOINT['.$type.'][FIELDS]');
				$trigger->setFields($existsList[$type]['FIELDS']);
				$existsList[$type]['FORM'] = $trigger->getForm();
			}
		}
		$this->arResult['TRIGGERS']['EXISTS'] = $existsList;
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