<?php

namespace Bitrix\Mail\Helper\Message;

use Bitrix\Mail\MailMessageTable;
use Bitrix\Mail\MailMessageUidTable;
use Bitrix\Mail\Internals\MailboxDirectoryTable;
use Bitrix\Main\ORM;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type\DateTime;

final class MessageInternalDateHandler
{
	public static function getStartInternalDateForDir(
		$mailboxId,
		?string $dirPath = null,
		?string $dirMd5 = null,
	): ?DateTime
	{
		$filter = [
			'=MAILBOX_ID' => $mailboxId,
		];

		if (!is_null($dirPath))
		{
			$filter['=PATH'] = Emoji::encode($dirPath);
		}
		else if (!is_null($dirMd5))
		{
			$filter['=DIR_MD5'] = $dirMd5;
		}

		$startInternalData =
			MailboxDirectoryTable::query()
				->setSelect(['ID', 'INTERNAL_START_DATE', 'IS_DATE_CACHED'])
				->setFilter($filter)
				->setLimit(1)
				->fetch()
		;

		if (empty($startInternalData['ID'] ?? null))
		{
			return null;
		}

		if (!$startInternalData['IS_DATE_CACHED'])
		{
			$firstSyncMessage = self::getFirstSyncMessageFromMailMessageTable((int)$mailboxId, $dirPath, $dirMd5) ?? [];
			$startInternalData = array_merge($startInternalData, $firstSyncMessage);
			self::setStartInternalDateForDir($startInternalData);
		}

		return $startInternalData['INTERNAL_START_DATE'] ?? null;
	}

	public static function setStartInternalDateForDir(
		array $startInternalDate
	): void
	{
		MailboxDirectoryTable::update($startInternalDate['ID'], [
			'INTERNAL_START_DATE' => $startInternalDate['INTERNAL_START_DATE'] ?? null,
			'IS_DATE_CACHED' => true,
		]);
	}

	private static function getFirstSyncMessageFromMailMessageTable(
		int $mailboxId,
		?string $dirPath,
		?string $dirMd5 = null,
	): ?array
	{

		if (is_null($dirMd5) && !is_null($dirPath))
		{
			$dirMd5 = md5(Emoji::encode($dirPath));
		}

		$filter = [
			'=MESSAGE_UID.DELETE_TIME' => 0,
			'!@MESSAGE_UID.IS_OLD' => MailMessageUidTable::EXCLUDED_COUNTER_STATUSES,
		];

		$firstSyncMessage = MailMessageTable::getList(
			[
				'runtime' => [
					new ORM\Fields\Relations\Reference(
						'MESSAGE_UID', MailMessageUidTable::class, [
						'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
						'=this.ID' => 'ref.MESSAGE_ID',
					], [
							'join_type' => 'INNER',
						]
					),
				],
				'select' => [
					'INTERNAL_START_DATE' => 'MESSAGE_UID.INTERNALDATE',
				],
				'filter' => array_merge(
					[
						'=MAILBOX_ID' => $mailboxId,
						'=MESSAGE_UID.DIR_MD5' => $dirMd5,
					],
					$filter
				),
				'order' => [
					'FIELD_DATE' => 'ASC',
				],
				'limit' => 1,
			]
		)->fetchAll();

		return $firstSyncMessage[0] ?? null;
	}

	public static function clearStartInternalDate(?int $mailboxId = null, ?string $dirMd5 = null): ORM\Data\UpdateResult
	{
		$filter['=IS_DATE_CACHED'] = true;
		if ($mailboxId !== null)
		{
			$filter['=MAILBOX_ID'] = $mailboxId;

			if($dirMd5 !== null)
			{
				$filter['=DIR_MD5'] = $dirMd5;
			}
		}

		$directoryRows = MailboxDirectoryTable::getList([
			'select' => ['ID'],
			'filter' => $filter,
		]);

		$result = new ORM\Data\UpdateResult();
		$ids = array_column($directoryRows->fetchAll(), 'ID');
		if (empty($ids))
		{
			return $result;
		}

		return MailboxDirectoryTable::updateMulti(
			$ids,
			[
				'IS_DATE_CACHED' => false,
				'INTERNAL_START_DATE' => null,
			],
			true
		);
	}
}