<?php

namespace Bitrix\Im\Update;

use Bitrix\Im\Common;
use Bitrix\Im\Model\EO_LinkUrl;
use Bitrix\Im\Model\EO_LinkUrl_Collection;
use Bitrix\Im\Model\EO_MessageParam_Collection;
use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Im\Model\LinkUrlIndexTable;
use Bitrix\Im\Model\LinkUrlTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Web\Uri;
use Bitrix\Pull\Event;

final class LinkUrlMigration extends Stepper
{
	protected static $moduleId = 'im';
	public const OPTION_NAME = 'im_link_url_migration';
	public const OPTION_NAME_LIMIT = 'im_link_url_migration_limit';
	public const OPTION_NAME_ITERATION_COUNT = 'im_link_url_migration_iteration';
	public const LIMIT_DEFAULT = 500;
	public const ITERATION_COUNT_DEFAULT = 4;

	function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return self::CONTINUE_EXECUTION;
		}

		$numOfIterations = (int)Option::get(self::$moduleId, self::OPTION_NAME_ITERATION_COUNT, self::ITERATION_COUNT_DEFAULT);

		$result = self::CONTINUE_EXECUTION;
		for ($i = 0; $i < $numOfIterations; ++$i)
		{
			$result = $this->makeMigrationIteration($option);

			if ($result === self::FINISH_EXECUTION)
			{
				return $result;
			}
		}

		return $result;
	}

	private function makeMigrationIteration(array &$option): bool
	{
		$isFinished = Option::get(self::$moduleId, self::OPTION_NAME, '');

		if ($isFinished === '')
		{
			Option::set(self::$moduleId, self::OPTION_NAME, 'N');
		}

		if ($isFinished === 'Y')
		{
			return self::FINISH_EXECUTION;
		}

		$lastId = $option['lastId'] ?? 0;
		$params = $this->getParams($lastId);

		if ($params->count() === 0)
		{
			Option::set(self::$moduleId, self::OPTION_NAME, 'Y');
			if (\Bitrix\Main\Loader::includeModule('pull'))
			{
				Event::add(
					Event::SHARED_CHANNEL,
					[
						'module_id' => 'im',
						'command' => 'linkUrlMigrationFinished',
						'extra' => Common::getPullExtra(),
					],
					\CPullChannel::TYPE_SHARED
				);
			}

			return self::FINISH_EXECUTION;
		}

		$ids = $params->getParamValueList();
		$lastId = max($params->getIdList());

		$urlsPreview = \Bitrix\Main\UrlPreview\UrlPreview::getMetadataByIds($ids);
		$urlCollection = new EO_LinkUrl_Collection();

		foreach ($params as $param)
		{
			$urlPreview = $urlsPreview[$param->getParamValue()];
			$uri = new Uri($urlPreview['URL']);
			if ($uri->getHost() === '')
			{
				continue;
			}
			$message = $param->getMessage();
			if ($message === null)
			{
				continue;
			}
			$url = new EO_LinkUrl();
			$url
				->setChatId($message->getChatId())
				->setAuthorId($message->getAuthorId())
				->setMessageId($param->getMessageId())
				->setDateCreate($message->getDateCreate())
				->setPreviewUrlId((int)$urlPreview['ID'])
				->setUrl($urlPreview['URL'])
			;
			$urlCollection[] = $url;
		}
		$urlCollection->save(true);
		LinkUrlIndexTable::index((int)Option::get(self::$moduleId, self::OPTION_NAME_LIMIT, self::LIMIT_DEFAULT));
		$option['lastId'] = $lastId;
		$steps = LinkUrlTable::getCount();
		$count = MessageParamTable::getCount(Query::filter()->where('PARAM_NAME', 'URL_ID'));
		$option['steps'] = $steps;
		$option['count'] = $count;

		return self::CONTINUE_EXECUTION;
	}

	private function getParams(int $lastId): EO_MessageParam_Collection
	{
		$params = MessageParamTable::query()
			->setSelect(['ID'])
			->where('PARAM_NAME', 'URL_ID')
			->where('ID', '>', $lastId)
			->setOrder(['ID' => 'ASC'])
			->setLimit((int)Option::get(self::$moduleId, self::OPTION_NAME_LIMIT, self::LIMIT_DEFAULT))
			->fetchCollection()
		;

		if ($params->count() === 0)
		{
			return $params;
		}

		$params->fill(['MESSAGE_ID', 'PARAM_VALUE']);

		$messageIds = $params->getMessageIdList();

		if (empty($messageIds))
		{
			return $params;
		}

		$messages = MessageTable::query()
			->setSelect(['ID', 'AUTHOR_ID', 'DATE_CREATE', 'CHAT_ID'])
			->whereIn('ID', $messageIds)
			->fetchCollection()
		;

		foreach ($params as $param)
		{
			$message = $messages->getByPrimary($param->getMessageId());
			if ($message !== null)
			{
				$param->setMessage($message);
			}
		}

		return $params;
	}
}