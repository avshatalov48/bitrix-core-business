<?php

namespace Bitrix\Im\V2\Link;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\File\FileService;
use Bitrix\Im\V2\Link\Url\UrlService;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;

class LinkFacade
{
	use ContextCustomer;
	private Message\Send\SendingConfig $config;

	public function __construct(?Message\Send\SendingConfig $config)
	{
		$this->config = $config ?? new Message\Send\SendingConfig();
	}

	public function saveLinksFromMessage(Message $message): Result
	{
		if (!$this->config->skipUrlIndex())
		{
			$resultUrls = (new UrlService())
				->setContext($this->context)
				->setBackgroundMode(false)
				->saveUrlsFromMessage($message)
			;
		}
		$resultFiles = (new FileService())->setContext($this->context)->save($message);

		return Result::merge($resultUrls ?? new Result(), $resultFiles);
	}
}
