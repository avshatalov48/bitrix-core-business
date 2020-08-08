<?

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
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

class SenderCampaignSelectorComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['ID'] = !empty($this->arParams['ID'])
			?
			$this->arParams['ID']
			:
			null;

		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::getInstance()->canModifyLetters();

		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : 'CAMPAIGN_ID';
		$this->arParams['READONLY'] = isset($this->arParams['READONLY']) ? (bool) $this->arParams['READONLY'] : false;
		$this->arParams['SELECT_ONLY'] = isset($this->arParams['SELECT_ONLY']) ? (bool) $this->arParams['SELECT_ONLY'] : false;
		$this->arParams['MULTIPLE'] = isset($this->arParams['MULTIPLE']) ? (bool) $this->arParams['MULTIPLE'] : false;
		if (!$this->arParams['CAN_EDIT'])
		{
			$this->arParams['READONLY'] = true;
		}
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		$this->arResult['SUBSCRIBER_COUNT'] = '';
		$this->arResult['SITE_NAME'] = '';

		$list = is_array($this->arParams['ID']) ? $this->arParams['ID'] : [$this->arParams['ID']];
		$tileView = UI\TileView::create();
		foreach ($list as $id)
		{
			$entityCampaign = Entity\Campaign::create($id);
			$this->arResult['SUBSCRIBER_COUNT'] = $entityCampaign->getSubscriberCount();
			$this->arResult['SITE_NAME'] = $entityCampaign->getSiteName();
			if ($entityCampaign->getId())
			{
				$tileView->addTile($entityCampaign->getId(), $entityCampaign->get('NAME'));

			}
		}
		$this->arResult['TILES'] = $tileView->getTiles();

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