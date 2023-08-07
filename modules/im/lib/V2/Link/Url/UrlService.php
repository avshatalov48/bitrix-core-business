<?php

namespace Bitrix\Im\V2\Link\Url;

use Bitrix\Im\Model\LinkUrlTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\Push;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Query\Query;

class UrlService
{
	use ContextCustomer;

	protected const ADD_URL_EVENT = 'urlAdd';
	protected const DELETE_URL_EVENT = 'urlDelete';

	private bool $isBackgroundMode = true;
	private int $quotaOfFetchMetadata = 5;

	public function getCount(int $chatId, ?int $startId = null): int
	{
		$filter = Query::filter()->where('CHAT_ID', $chatId);

		if (isset($startId) && $startId > 0)
		{
			$filter->where('MESSAGE_ID', '>=', $startId);
		}

		return LinkUrlTable::getCount($filter);
	}

	/**
	 * @param Message $message
	 * @return Result
	 */
	public function saveUrlsFromMessage(Message $message): Result
	{
		if ($this->isBackgroundMode)
		{
			Application::getInstance()->addBackgroundJob(fn () => $this->saveUrlsFromMessageInternal($message));

			return new Result();
		}

		return $this->saveUrlsFromMessageInternal($message);
	}

	/**
	 * @param Message $message
	 * @return Result
	 */
	public function updateUrlsFromMessage(Message $message): Result
	{
		$result = new Result();

		$deleteResult = $this->deleteUrlsByMessage($message);

		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}

		$saveResult = $this->saveUrlsFromMessage($message);

		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		return $result;
	}

	public function deleteUrlsByMessage(Message $message): Result
	{
		$result = new Result();

		$urls = $this->getUrlsByMessage($message);

		if (count($urls) === 0)
		{
			return $result;
		}

		return $this->deleteUrls($urls);
	}

	public function deleteUrls(UrlCollection $urls): Result
	{
		$deleteResult = $urls->delete();

		if (!$deleteResult->isSuccess())
		{
			return $deleteResult;
		}

		foreach ($urls as $url)
		{
			Push::getInstance()
				->setContext($this->context)
				->sendIdOnly($url, UrlService::DELETE_URL_EVENT, ['CHAT_ID' => $url->getChatId()])
			;
		}

		return $deleteResult;
	}

	public function setBackgroundMode(bool $isBackgroundMode): self
	{
		$this->isBackgroundMode = $isBackgroundMode;

		return $this;
	}

	public function setQuotaOfFetchMetadata(int $quota): self
	{
		$this->quotaOfFetchMetadata = $quota;

		return $this;
	}

	protected function saveUrlsFromMessageInternal(Message $message): Result
	{
		$result = new Result();
		$urlCollection = $this->initUrlsByMessage($message);

		if ($urlCollection->hasUnsaved())
		{
			$saveResult = $this->saveUrls($urlCollection);
			if ($saveResult->isSuccess())
			{
				$this->sendAddPush($urlCollection);
			}
			else
			{
				$result->addErrors($saveResult->getErrors());
			}
		}

		return $result;
	}

	protected function saveUrls(UrlCollection $urls): Result
	{
		return $urls->save();
	}

	protected function getUrlsByMessage(Message $message): UrlCollection
	{
		$urlEntities = LinkUrlTable::query()
			->setSelect(['*'])
			->where('MESSAGE_ID', $message->getMessageId())
			->fetchCollection();

		return (new UrlCollection($urlEntities))->fillMetadata();
	}

	protected function initUrlsByMessage(Message $message): UrlCollection
	{
		$urls = \Bitrix\Im\V2\Entity\Url\UrlItem::getUrlsFromText($message->getMessage());
		$urlCollection = new \Bitrix\Im\V2\Entity\Url\UrlCollection();
		$countUrlsWithMetadata = 0;

		foreach ($urls as $url)
		{
			$withFetchMetadata = $countUrlsWithMetadata < $this->quotaOfFetchMetadata;
			$urlCollection[] = new \Bitrix\Im\V2\Entity\Url\UrlItem($url, $withFetchMetadata);
			$countUrlsWithMetadata++;
		}

		return UrlCollection::linkEntityToMessage($urlCollection, $message);
	}

	protected function sendAddPush(UrlCollection $urls): void
	{
		foreach ($urls as $url)
		{
			$recipient = $url->getEntity()->getRichData()->getAllowedUsers();
			if ($recipient === null)
			{
				Push::getInstance()
					->setContext($this->context)
					->sendFull($url, self::ADD_URL_EVENT, ['CHAT_ID' => $url->getChatId()])
				;
			}
			else
			{
				Push::getInstance()
					->setContext($this->context)
					->sendFull($url, self::ADD_URL_EVENT, ['RECIPIENT' => $recipient])
				;
			}
		}
	}
}