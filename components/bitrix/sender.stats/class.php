<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;

use Bitrix\Sender\Stat\Statistics;
use Bitrix\Sender\MailingChainTable;
use Bitrix\Sender\PostingTable;
use Bitrix\Fileman\Block\Editor as BlockEditor;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::IncludeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class SenderStatsComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{

	}

	protected function prepareResult()
	{
		global $USER;

		$arResult = array(
			'DATA' => array(),
			'MAILING_COUNTERS' => array(),
		);

		$request = Context::getCurrent()->getRequest();
		$action = $request->get('action');
		if ($action == 'get_counters_dynamic')
		{
			$GLOBALS['APPLICATION']->RestartBuffer();
			$stat = Statistics::create()->setUserId($USER->GetID())->initFilterFromRequest();
			echo Json::encode(array(
				'countersDynamic' => $stat->getCountersDynamic(),
			));
			\CMain::FinalActions();
			exit;
		}


		$stat = Statistics::create()->setUserId($USER->GetID())->initFilterFromRequest();
		$arResult['DATA']['chainList'] = $stat->getChainList(3);
		$arResult['DATA']['counters'] = array();
		$counters = $stat->getCounters();
		$counters[] = $stat->getCounterSubscribers();
		$counters[] = $stat->getCounterPostings();
		foreach ($counters as $counter)
		{
			$arResult['DATA']['counters'][$counter['CODE']] = $counter;
		}

		$efficiency = $stat->getEfficiency();
		if (!$efficiency['VALUE'])
		{
			$globalStat = Statistics::create();
			$efficiency = $globalStat->getEfficiency();
		}
		$efficiency['PERCENT_VALUE'] *= 100;
		$efficiency['VALUE'] *= 100;
		$arResult['DATA']['efficiency'] = $efficiency;

		$arResult['COUNTERS_DYNAMIC_NAMES'] = array(
			'EFFICIENCY',
			'READ',
			'CLICK',
			'UNSUB',
		);


		if ($action == 'getData' && empty($arResult['ERROR']))
		{
			$GLOBALS['APPLICATION']->RestartBuffer();
			echo Json::encode($arResult['DATA']);
			\CMain::FinalActions();
			exit;
		}


		$arResult['FILTER_DATA'] = $stat->getGlobalFilterData();

		$this->arResult = $arResult + $this->arResult;







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