<?php

namespace Bitrix\Main\Engine\ActionFilter;

use Bitrix\Main;

/**
 * Class Cors
 * Set headers for CORS .
 * @package Bitrix\Main\Engine\ActionFilter
 */
final class Cors extends Base
{
	/** @var string|null */
	private $origin;

	/** @var bool */
	private $credentials;

	/**
	 * Constructor.
	 *
	 * @param string|null $origin Origin. NULL - take from Origin header, '*' or like 'https://example.com'.
	 * @param bool $credentials Set header `Access-Control-Allow-Credentials`.
	 */
	public function __construct(string $origin = null, bool $credentials = false)
	{
		$this->origin = $origin;
		$this->credentials = $credentials;

		parent::__construct();
	}

	/**
	 * Handler of event `onBeforeAction`.
	 *
	 * @param Main\Event $event Event.
	 * return void
	 */
	public function onAfterAction(Main\Event $event)
	{
		$response = Main\Context::getCurrent()->getResponse();
		$origin = $this->origin ?: Main\Context::getCurrent()->getRequest()->getHeader('Origin');
		if ($origin && $response instanceof Main\HttpResponse)
		{
			$response->addHeader('Access-Control-Allow-Origin', $origin);
			if ($this->credentials)
			{
				$response->addHeader('Access-Control-Allow-Credentials', 'true');
			}
		}
	}
}