<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main;
use Bitrix\Mail;

class MessageIndexStepper extends Main\Update\Stepper
{
	const INDEX_VERSION = 1;

	protected static $moduleId = 'mail';

	public function execute(array &$option)
	{
		$option['steps'] = Mail\MailMessageTable::getCount(array(
			'=INDEX_VERSION' => static::INDEX_VERSION,
		));
		$option['count'] = Mail\MailMessageTable::getCount(array(
			'<=INDEX_VERSION' => static::INDEX_VERSION,
		));

		if ($option['steps'] >= $option['count'])
		{
			return false;
		}

		$res = Mail\MailMessageTable::getList(array(
			'select' => array(
				'ID',
				'FIELD_FROM', 'FIELD_REPLY_TO',
				'FIELD_TO', 'FIELD_CC', 'FIELD_BCC',
				'SUBJECT', 'BODY',
			),
			'filter' => array(
				'<INDEX_VERSION' => static::INDEX_VERSION,
			),
			'order' => array('ID' => 'ASC'),
			'limit' => 1000,
		));

		while ($item = $res->fetch())
		{
			$option['steps']++;

			$fields = array(
				'SEARCH_CONTENT' => Message::prepareSearchContent($item),
				'INDEX_VERSION' => static::INDEX_VERSION,
			);

			Mail\MailMessageTable::update($item['ID'], $fields);
		}

		return true;
	}

}
