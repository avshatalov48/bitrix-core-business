<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Security;
use Bitrix\Sender\PostingRecipientTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderTriggerStatComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var Entity\TriggerCampaign $campaignEntity */
	protected $campaignEntity;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$request = Context::getCurrent()->getRequest();
		if (empty($this->arParams['ID']))
		{
			$this->arParams['ID'] = (int) $request->get('ID');
		}

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] === 'Y' : true;
		$this->arParams['PATH_TO_RECIPIENT'] = isset($this->arParams['PATH_TO_RECIPIENT']) ? $this->arParams['PATH_TO_RECIPIENT'] : '';
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_TRIGGER_STAT_COMP_TITLE'));
		}

		if (!Security\Access::current()->canViewLetters())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['ACTION_URL'] = $this->getPath() . '/ajax.php';

		$this->campaignEntity = new Entity\TriggerCampaign($this->arParams['ID']);
		if (!$this->campaignEntity->getId())
		{
			$this->errors->setError(new Error(Loc::getMessage("SENDER_TRIGGER_STAT_COMP_NO_CAMPAIGN")));
			return false;
		}

		$this->arResult['ROW'] = $this->campaignEntity->getData();
		$this->arResult['DATA'] = $this->getTriggerStat();

		$uri = new \Bitrix\Main\Web\Uri(str_replace('#id#', $this->arParams['CHAIN_ID'], $this->arParams['PATH_TO_RECIPIENT']));
		$uri->addParams(['apply_filter' => 'Y']);
		$readUri = clone $uri; $clickUri = clone $uri; $unsubUri = clone $uri;
		$sentErrorUri = clone $uri; $sentSuccessUri = clone $uri;
		$this->arResult['URLS'] = [
			'READ' => $readUri->addParams(['IS_READ' => 'Y'])->getLocator(),
			'CLICK' => $clickUri->addParams(['IS_CLICK' => 'Y'])->getLocator(),
			'UNSUB' => $unsubUri->addParams(['IS_UNSUB' => 'Y'])->getLocator(),
			'SENT_ERROR' => $sentErrorUri->addParams(['STATUS' => PostingRecipientTable::SEND_RESULT_ERROR])->getLocator(),
			'SENT_SUCCESS' => $sentSuccessUri->addParams(['STATUS' => PostingRecipientTable::SEND_RESULT_SUCCESS])->getLocator(),
			'SEND_ALL' => $uri->getLocator(),
		];

		return $this->errors->isEmpty();
	}

	protected function getTriggerStat()
	{
		$result = [];
		$i = 1;
		$chainList = \Bitrix\Sender\MailingTable::getChain($this->campaignEntity->getId());
		foreach($chainList as $chain)
		{
			$stat = array(
				'NAME' => Loc::getMessage("SENDER_TRIGGER_STAT_COMP_LETTER", ['%number%' => $i++]),
				'SUBJECT' => $chain['TITLE'],
				'CNT' => array(
					'SENT_SUCCESS' => 0,
					'SENT_ERROR' => 0,
					'READ' => 0,
					'CLICK' => 0,
					'UNSUB' => 0,
					'GOAL' => 0,
					'START' => 0,
				)
			);
			$statRawDb = \Bitrix\Sender\PostingTable::getList(array(
				'select' => array(
					'CNT', 'READ_CNT', 'CLICK_CNT', 'UNSUB_CNT', 'ERROR_CNT',
				),
				'filter' => array(
					'=MAILING_CHAIN_ID' => $chain['ID'],
				),
				'runtime' => array(
					new \Bitrix\Main\Entity\ExpressionField('CNT', 'SUM(%s)', 'COUNT_SEND_SUCCESS'),
					new \Bitrix\Main\Entity\ExpressionField('ERROR_CNT', 'SUM(%s)', 'COUNT_SEND_ERROR'),
					new \Bitrix\Main\Entity\ExpressionField('READ_CNT', 'SUM(%s)', 'COUNT_READ'),
					new \Bitrix\Main\Entity\ExpressionField('CLICK_CNT', 'SUM(%s)', 'COUNT_CLICK'),
					new \Bitrix\Main\Entity\ExpressionField('UNSUB_CNT', 'SUM(%s)', 'COUNT_UNSUB')
				),
			));
			while($statRaw = $statRawDb->fetch())
			{
				$stat['CNT']['SENT_SUCCESS'] += $statRaw['CNT'];
				$stat['CNT']['SEND_ERROR'] += $statRaw['ERROR_CNT'];
				$stat['CNT']['READ'] += $statRaw['READ_CNT'];
				$stat['CNT']['CLICK'] += $statRaw['CLICK_CNT'];
				$stat['CNT']['UNSUB'] += $statRaw['UNSUB_CNT'];

				$stat['CNT']['START'] += $statRaw['CNT'];
			}

			$statRawDb = \Bitrix\Sender\PostingRecipientTable::getList(array(
				'select' => array('ID'),
				'filter' => array(
					'=POSTING.MAILING_CHAIN_ID' => $chain['ID'],
					'=STATUS' => array(
						\Bitrix\Sender\PostingRecipientTable::SEND_RESULT_SUCCESS,
						\Bitrix\Sender\PostingRecipientTable::SEND_RESULT_DENY,
					),
					'!DATE_DENY' => null
				)
			));
			$stat['CNT']['GOAL'] = $statRawDb->getSelectedRowsCount();

			$result['CHAIN'][] = $stat;
		}

		if(!empty($result))
		{
			foreach($result['CHAIN'] as $chain)
			{
				foreach($chain['CNT'] as $k => $v)
				{
					if(!isset($result['CNT'][$k]))
					{
						$result['CNT'][$k] = 0;
					}

					$result['CNT'][$k] += $v;
				}
			}
			$result['CNT']['START'] = $result['CHAIN'][0]['CNT']['START'];

			$goalStart = 0;
			foreach($result['CHAIN'] as $k => $chain)
			{
				$goalEnd = $goalStart + $chain['CNT']['GOAL'];
				$result['CHAIN'][$k]['GOAL_START'] = $goalStart;
				$result['CHAIN'][$k]['GOAL_END'] = $goalEnd;

				foreach($chain['CNT'] as $cntKey => $cntValue)
				{
					$result['CHAIN'][$k]['CNT_'.$cntKey] = $cntValue;
				}

				$result['CHAIN'][$k]['color'] = '#04D215';
				$goalStart = $goalEnd;
			}
		}

		return $result;
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