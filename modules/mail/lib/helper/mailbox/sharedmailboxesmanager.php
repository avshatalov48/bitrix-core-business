<?php
namespace Bitrix\Mail\Helper\Mailbox;

use Bitrix\Mail\Internals\MailboxAccessTable;
use Bitrix\Mail\MailboxTable;
use Bitrix\Main\Entity\Query\Filter\Expression\Column;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Query;

class SharedMailboxesManager
{
	public static function getSharedMailboxesCount()
	{
		$count = static::getBaseQueryForSharedMailboxes()
			->addSelect(Query::expr()->countDistinct('MAILBOX_ID'), 'CNT')
			->exec()
			->fetch();
		return !empty($count['CNT']) ? $count['CNT'] : 0;
	}

	public static function getSharedMailboxesIds()
	{
		$mailboxesIds = static::getBaseQueryForSharedMailboxes()
			->addSelect('MAILBOX_ID')
			->addGroup('MAILBOX_ID')
			->exec()
			->fetchAll();
		return array_map('intval', array_column($mailboxesIds, 'MAILBOX_ID'));
	}

	public static function getUserIdsWithAccessToMailbox($mailboxId)
	{
		$userCodes = MailboxAccessTable::query()
			->addSelect('ACCESS_CODE')
			->where('MAILBOX_ID', $mailboxId)
			->whereLike('ACCESS_CODE', 'U%')
			->exec()
			->fetchAll();
		$results = [];
		foreach ($userCodes as $userAccessCode)
		{
			// @TODO: departments
			if (preg_match('#U[0-9]+#', $userAccessCode['ACCESS_CODE']) === 1)
			{
				$results[] = mb_substr($userAccessCode['ACCESS_CODE'], 1);
			}
		}
		return $results;
	}

	/**
	 * @return \Bitrix\Mail\Internals\EO_MailboxAccess_Query|Query
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getBaseQueryForSharedMailboxes()
	{
		return MailboxAccessTable::query()
			->registerRuntimeField('', new ReferenceField('ref', MailboxTable::class, ['=this.MAILBOX_ID' => 'ref.ID'], ['join_type' => 'INNER']))
			->where(new ExpressionField('ac', 'CONCAT("U", %s)', 'ref.USER_ID'), '!=', new Column('ACCESS_CODE'))
			->where('ref.ACTIVE', 'Y')
			->where('ref.LID', SITE_ID);
	}
}