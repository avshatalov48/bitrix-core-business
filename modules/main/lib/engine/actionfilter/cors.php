<?php

namespace Bitrix\Main\Engine\ActionFilter;

use Bitrix\Main;

/**
 * Class Cors
 * Set headers for CORS.
 * @package Bitrix\Main\Engine\ActionFilter
 */
final class Cors extends Base
{
	/** @var string|null */
	private ?string $origin;

	/** @var bool */
	private bool $credentials;

	private ?array $allowedMethods;
	private ?array $allowedHeaders;

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

	public function onBeforeAction(Main\Event $event): void
	{
		$this->setCorsHeaders();
	}

	public function onAfterAction(Main\Event $event): void
	{
		$this->setCorsHeaders();
	}

	public function setAllowedMethods(array $methods): self
	{
		$this->allowedMethods = $methods;
		return $this;
	}

	public function setAllowedHeaders(array $headers): self
	{
		$this->allowedHeaders = $headers;
		return $this;
	}

	private function setCorsHeaders(): void
	{
		$context = Main\Context::getCurrent();
		if (!$context)
		{
			return;
		}

		$response = $context->getResponse();
		$origin = $this->origin ?: $context->getRequest()->getHeader('Origin');
		if ($origin && $response instanceof Main\HttpResponse)
		{
			$currentHttpHeaders = $response->getHeaders();
			if (!$currentHttpHeaders->get('Access-Control-Allow-Origin'))
			{
				$currentHttpHeaders->add('Access-Control-Allow-Origin', $origin);
			}
			if ($this->credentials && !$currentHttpHeaders->get('Access-Control-Allow-Credentials'))
			{
				$currentHttpHeaders->add('Access-Control-Allow-Credentials', 'true');
			}
			if (!empty($this->allowedHeaders) && !$currentHttpHeaders->get('Access-Control-Allow-Headers'))
			{
				$currentHttpHeaders->add('Access-Control-Allow-Headers', implode(',', $this->allowedHeaders));
			}
			if (!empty($this->allowedMethods) && !$currentHttpHeaders->get('Access-Control-Allow-Methods'))
			{
				$currentHttpHeaders->add('Access-Control-Allow-Methods', implode(',', $this->allowedMethods));
			}
		}
	}
}