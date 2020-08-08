<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Conversion\DayContext;
use Bitrix\Main\Context;
use Bitrix\Sender\MailingChainTable;
use Bitrix\Sender\PostingRecipientTable;

Loc::loadMessages(__FILE__);

class ConversionHandler
{
	const CLICK_PARAM_NAME = 'BITRIX_SENDER_TO_CONVERSION_CLICK_ID';

	public static function onAfterRecipientClick($data)
	{
		if(isset($data['RECIPIENT']) && isset($data['RECIPIENT']['ID']))
		{
			$_SESSION[self::CLICK_PARAM_NAME] = $data['RECIPIENT']['ID'];
		}

		return $data;
	}

	public static function onBeforeProlog()
	{
		$id = Context::getCurrent()->getRequest()->getQuery('bx_sender_conversion_id');
		if(is_numeric($id) && $id > 0)
		{
			$_SESSION[self::CLICK_PARAM_NAME] = $id;
		}
	}

	public static function onSetDayContextAttributes(DayContext $context)
	{
		$id = null;
		if(isset($_SESSION[self::CLICK_PARAM_NAME]))
			$id = $_SESSION[self::CLICK_PARAM_NAME];

		if(!is_numeric($id) || $id <= 0)
			return;

		$recipientDb = PostingRecipientTable::getList(array(
			'select' => array('MAILING_CHAIN_ID' => 'POSTING.MAILING_CHAIN_ID'),
			'filter' => array(
				'ID' => $id
			)
		));
		if ($recipient = $recipientDb->fetch())
		{
			$context->setAttribute('sender_chain_source', $recipient['MAILING_CHAIN_ID']);
		}
	}

	public static function onGetAttributeTypes()
	{
		return array(
			'sender_chain_source' => array(
				'MODULE' => 'sender',
				'GROUP' => 'source',
				'NAME' => Loc::getMessage('sender_conversion_chain_source'),
				'SORT' => 5100,
				'SPLIT_BY' => 'sender_chain_source',
				'BG_COLOR' => '#cf4343',
				'GET_VALUES' => function (array $list)
				{
					$itemList = array();
					$filter = array();
					if($list)
					{
						$filter['=ID'] = $list;

						$itemDb = MailingChainTable::getList(array(
							'select' => array(
								'ID',
								'TITLE',
							),
							'filter' => $filter,
						));

						while ($item = $itemDb->fetch())
						{
							if ($item['ID'] == '')
							{
								continue;
							}

							$itemList[$item['ID']] = array(
								'NAME' => $item['TITLE']
							);
						}
					}

					return $itemList;
				}
			)
		);
	}
}