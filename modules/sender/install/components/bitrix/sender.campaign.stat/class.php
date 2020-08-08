<?

use Bitrix\Fileman\Block\Editor as BlockEditor;
use Bitrix\Main\Context;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\MailingChainTable;
use Bitrix\Sender\PostingTable;
use Bitrix\Sender\Stat\Statistics;

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

class SenderCampaignStatComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$request = Context::getCurrent()->getRequest();
		if (!isset($this->arParams['MAILING_ID']))
		{
			$this->arParams['MAILING_ID'] = intval($request->get('MAILING_ID'));
		}
		if (!$this->arParams['MAILING_ID'])
		{
			$this->arParams['MAILING_ID'] = intval($request->get('mailingId'));
		}

		if (!isset($this->arParams['ID']))
		{
			$this->arParams['CHAIN_ID'] = intval($request->get('ID'));
		}

		if (!isset($this->arParams['ID']))
		{
			$this->arParams['CHAIN_ID'] = intval($request->get('chainId'));
		}

		$this->arParams['POSTING_ID'] = null;
	}

	protected function prepareResult()
	{
		$this->arResult['DATA'] = array();
		$this->arResult['MAILING_COUNTERS'] = array();
		$this->arResult['ERRORS'] = array();

		$postingFilter = array(
			'=MAILING_ID' => $this->arParams['MAILING_ID'],
			'!DATE_SENT' => null
		);
		if ($this->arParams['CHAIN_ID'])
		{
			$postingFilter['=MAILING_CHAIN_ID'] = $this->arParams['CHAIN_ID'];
		}
		$postingDb = PostingTable::getList(array(
			'select' => array(
				'ID', 'MAILING_CHAIN_ID',
				'TITLE' => 'MAILING_CHAIN.TITLE', 'SUBJECT' => 'MAILING_CHAIN.SUBJECT',
				'MAILING_NAME' => 'MAILING.NAME', 'DATE_SENT',
				'LINK_PARAMS' => 'MAILING_CHAIN.LINK_PARAMS',
				'CREATED_BY' => 'MAILING_CHAIN.CREATED_BY',
				'CREATED_BY_NAME' => 'MAILING_CHAIN.CREATED_BY_USER.NAME',
				'CREATED_BY_LAST_NAME' => 'MAILING_CHAIN.CREATED_BY_USER.LAST_NAME',
				'CREATED_BY_SECOND_NAME' => 'MAILING_CHAIN.CREATED_BY_USER.SECOND_NAME',
				'CREATED_BY_LOGIN' => 'MAILING_CHAIN.CREATED_BY_USER.LOGIN',
				'CREATED_BY_TITLE' => 'MAILING_CHAIN.CREATED_BY_USER.TITLE',
			),
			'filter' => $postingFilter,
			'limit' => 1,
			'order' => array('DATE_SENT' => 'DESC', 'DATE_CREATE' => 'DESC'),
		));
		$posting = $postingDb->fetch();
		if($posting)
		{
			$this->arParams['CHAIN_ID'] = intval($posting['MAILING_CHAIN_ID']);
			$this->arParams['POSTING_ID'] = intval($posting['ID']);
		}


		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('MAIN_USER_CONSENTS_COMP_TITLE'));
		}




		$chainId = $this->arParams['CHAIN_ID'];
		$postingId = $this->arParams['POSTING_ID'];
		$mailingId = $this->arParams['MAILING_ID'];
		$arResult = array();

		$action = $this->request->get('action');
		if($action == 'get_template' && $chainId)
		{
			$GLOBALS['APPLICATION']->RestartBuffer();
			$message = MailingChainTable::getMessageById($chainId);
			$message = BlockEditor::getHtmlForEditor($message, Context::getCurrent()->getCulture()->getCharset());
			echo $message;
			exit;
		}
		elseif ($action == 'get_read_by_time')
		{
			$GLOBALS['APPLICATION']->RestartBuffer();
			$mailingStat = Statistics::create()->filter('chainId', $chainId);
			$mailingStat->setCacheTtl(0);
			echo Json::encode(array(
				'recommendedTime' => $mailingStat->getRecommendedSendTime(),
				'readingByTimeList' => $mailingStat->getReadingByDayTime(),
			));
			\CMain::FinalActions();
			exit;
		}
		elseif($postingId)
		{
			$arResult['POSTING'] = $posting;
			if (!$arResult['POSTING']['TITLE'])
			{
				$arResult['POSTING']['TITLE'] = $arResult['POSTING']['SUBJECT'];
			}

			$mailingStat = Statistics::create()->filter('mailingId', $mailingId);
			$mailingStat->setCacheTtl(0);
			$arResult['CHAIN_LIST'] = $mailingStat->getChainList(7);
			$arResult['EFFICIENCY'] = $mailingStat->getEfficiency();
			$mailingCounters = $mailingStat->getCounters();
			foreach ($mailingCounters as $counter)
			{
				$arResult['MAILING_COUNTERS'][$counter['CODE']] = $counter;
			}


			$arResult['DATA']['posting']['linkParams'] = $arResult['POSTING']['LINK_PARAMS'];
			if ($arResult['POSTING']['DATE_SENT'])
			{
				$arResult['DATA']['posting']['dateSent'] = FormatDate('x', $arResult['POSTING']['DATE_SENT']->getTimestamp());
			}

			$arResult['DATA']['posting']['createdBy'] = array(
				'id' => $arResult['POSTING']['CREATED_BY'],
				'name' => '',
				'url' => '/bitrix/admin/user_edit.php?ID=' . intval($arResult['POSTING']['CREATED_BY']) . '&lang=' . LANGUAGE_ID,
			);
			$arResult['DATA']['posting']['createdBy']['name'] = \CUser::FormatName(
				\CSite::GetNameFormat(true),
				array(
					"TITLE" => $arResult['POSTING']['CREATED_BY_TITLE'],
					"NAME" => $arResult['POSTING']['CREATED_BY_NAME'],
					"SECOND_NAME" => $arResult['POSTING']['CREATED_BY_SECOND_NAME'],
					"LAST_NAME" => $arResult['POSTING']['CREATED_BY_LAST_NAME'],
					"LOGIN" => $arResult['POSTING']['CREATED_BY_LOGIN'],
				),
				true, true
			);

			$postingStat = Statistics::create()->filter('mailingId', $mailingId)->filter('postingId', $postingId);
			$postingStat->setCacheTtl(0);
			$arResult['DATA']['clickList'] = $postingStat->getClickLinks();
			$arResult['DATA']['counters'] = array();
			$counters = $postingStat->getCounters();
			foreach ($counters as $counter)
			{
				$arResult['DATA']['counters'][$counter['CODE']] = $counter;
			}
		}
		else
		{
			$arResult['ERROR'] = GetMessage("SENDER_MAILING_STATS_NO_POSTINGS");
		}


		if ($action == 'get_data' && empty($arResult['ERROR']))
		{
			$GLOBALS['APPLICATION']->RestartBuffer();
			echo Json::encode($arResult['DATA']);
			\CMain::FinalActions();
			exit;
		}


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