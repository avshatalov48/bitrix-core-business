<?php
namespace Bitrix\Sender\Internals\Agent;

use Bitrix\Sender\Internals\Model\MessageFieldTable;
use Bitrix\Sender\Internals\Model\MessageUtmTable;

class UtmUpdater
{
	/**
	 * Use for install agent and install data to DB
	 * @return string
	 */
	public static function installAgent()
	{
		self::updateUtm();
		return '';
	}

	/**
	 * fill data by presetted array
	 */
	private static function updateUtm() :void
	{
		$messages = MessageFieldTable::getList(
			[
				'select' => [
					'MESSAGE_ID',
					'VALUE'
				],
				'filter' => [
					'=CODE' => 'LINK_PARAMS'
				],
				'limit' => 50,
				'order' => [
					'MESSAGE_ID' => 'desc'
				]
			]
		)->fetchAll();

		foreach ($messages as $message) {
			parse_str($message['VALUE'],$utmTags);
			MessageUtmTable::deleteByMessageId($message['MESSAGE_ID']);

			foreach ($utmTags as $utmTag => $value)
			{
				MessageUtmTable::add(
					[
						'MESSAGE_ID' => $message['MESSAGE_ID'],
						'CODE'       => $utmTag,
						'VALUE'      => $value
					]
				);
			}
		}
	}
}