<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;

use Bitrix\Sender\UI;
use Bitrix\Sender\Security;
use Bitrix\Sender\ListTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderContactSetSelectorComponent extends CBitrixComponent
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
			Security\Access::current()->canModifySegments();

		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : 'CAMPAIGN_ID';
		$this->arParams['READONLY'] = isset($this->arParams['READONLY']) ? (bool) $this->arParams['READONLY'] : false;
		$this->arParams['SELECT_ONLY'] = isset($this->arParams['SELECT_ONLY']) ? (bool) $this->arParams['SELECT_ONLY'] : true;
		$this->arParams['MULTIPLE'] = isset($this->arParams['MULTIPLE']) ? (bool) $this->arParams['MULTIPLE'] : true;
		if (!$this->arParams['CAN_EDIT'])
		{
			$this->arParams['READONLY'] = true;
		}
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		$list = is_array($this->arParams['ID']) ? $this->arParams['ID'] : [$this->arParams['ID']];
		TrimArr($list);
		$tileView = UI\TileView::create();
		foreach ($list as $id)
		{
			$row = ListTable::getRowById($id);
			if ($row)
			{
				$tileView->addTile($row['ID'], $row['NAME']);

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