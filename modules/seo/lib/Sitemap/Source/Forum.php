<?php
namespace Bitrix\Seo\Sitemap\Source;

use Bitrix\Seo\Sitemap\Internals\ForumTable;
use Bitrix\Seo\Sitemap\Job;

class Forum
{
	public static function __callStatic($name, $arguments)
	{
		$name = mb_strtoupper($name);
		switch($name)
		{
			case 'ADDTOPIC':
			case 'UPDATETOPIC':
			case 'DELETETOPIC':

				if (
					isset($arguments[0])
					&& (int)$arguments[0]
					&& (!isset($arguments[1]["APPROVED"]) || $arguments[1]["APPROVED"] === 'Y'))
				{
					self::processTopic($arguments[0]);
				}

				break;
		}
	}

	protected static function processTopic(int $topicId): void
	{
		$topic = \CForumTopic::GetByID($topicId);
		$forumsForSitemap = ForumTable::query()
			->setSelect(['SITEMAP_ID'])
			->where('ENTITY_ID', $topic['FORUM_ID'])
			->exec()
		;

		foreach ($forumsForSitemap as $forum)
		{
			Job::markToRegenerate($forum['SITEMAP_ID']);
		}
	}
}
