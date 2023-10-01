<?php

namespace Bitrix\Mail\Helper;
use Bitrix\Main;
use Bitrix\Mail;

class MessageSearchContentStepper extends Main\Update\Stepper
{
	private const LIMIT = 100;
	protected static $moduleId = 'mail';

	public function execute(array &$option): bool
	{
		if (empty($option))
		{
			$option['lastId'] = 0;
			$maxId = Mail\MailMessageTable::getList([
				'order' => ['ID' => 'DESC'],
				'limit' => 1,
				'select' => ['ID'],
			])->fetch();
			$option['maxId'] = (int) $maxId['ID'];
		}

		if ($option['lastId'] >= $option['maxId'])
		{
			return self::FINISH_EXECUTION;
		}

		$result = Mail\MailMessageTable::getList([
			'select' => [
				'ID',
				'SEARCH_CONTENT',
			],
			'filter' => [
				'><ID' => [max($option['lastId'], 0),$option['maxId']],
			],
			'order' => ['ID' => 'ASC'],
			'limit' => self::LIMIT,
		]);

		while ($message = $result->fetch())
		{
			$message['SEARCH_CONTENT'] = self::isolateBase64Files(str_rot13($message['SEARCH_CONTENT']));
			Mail\MailMessageTable::update($message['ID'], [
				'SEARCH_CONTENT' => $message['SEARCH_CONTENT'],
			]);

			$option['lastId'] = $message['ID'];
		}

		return self::CONTINUE_EXECUTION;
	}


	private function isolateBase64Files(string $text): string
	{
		$pattern = '/\[\s*data:(?!text\b)[^;]+;base64,\S+ \]/';
		$clearText = preg_replace($pattern, '', $text);
		return str_rot13($clearText);
	}
}