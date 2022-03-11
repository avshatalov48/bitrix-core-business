<?php
namespace Bitrix\Sender\Install;

use Bitrix\Sender\Access\Role\RoleTable;
use Bitrix\Sender\Access\Role\RoleUtil;
use Bitrix\Sender\Access\Service\RolePermissionService;
use Bitrix\Sender\Entity\Letter;
use Bitrix\Sender\FileTable;
use Bitrix\Sender\Integration\Sender\Mail\MessageMail;
use Bitrix\Sender\TemplateTable;

class FileTableInstaller
{
	/**
	 * Use for install agent and install data to DB
	 * @return string
	 */
	public static function installAgent(int $offset = 0)
	{
		return self::fillFileTableFromTemplates($offset);
	}

	/**
	 * fill data by presetted array
	 */
	public static function fillFileTableFromTemplates(int $offset = 0):string
	{
		$templates = TemplateTable::getList([
			'select' => [
				'ID',
				'CONTENT'
			]
		]);

		while ($template = $templates->fetch())
		{
			FileTable::syncFiles(
				$template['ID'],
				FileTable::TYPES['TEMPLATE'],
				$template['CONTENT'],
				false
			);
		}

		$letters = Letter::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=MESSAGE_CODE' => MessageMail::CODE
			],
			'offset' => $offset,
			'limit' => 100,
			'order' => [
				'ID' => 'ASC'
			]
		]);
		$counter = 0;
		while ($letter = $letters->fetch())
		{
			$offset = $letter['ID'];
			$letter = Letter::createInstanceById($letter['ID']);
			FileTable::syncFiles(
				$letter->getId(),
				FileTable::TYPES['LETTER'],
				$letter->getMessage()->getConfiguration()->get('MESSAGE'),
				false
			);
			$counter++;
		}

		if ($counter < 100)
		{
			\COption::SetOptionInt('sender', 'sender_file_load_completed', 1);
			return '';
		}

		return '\\Bitrix\Sender\\Install\\FileTableInstaller::installAgent('.$offset.');';
	}
}