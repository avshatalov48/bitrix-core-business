<?php

namespace Bitrix\Main\Web\Http;

use Bitrix\Main\SystemException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends SystemException implements ClientExceptionInterface
{
	protected RequestInterface $request;

	public function __construct(RequestInterface $request, $message = '')
	{
		parent::__construct($message);

		$this->request = $request;
	}

	public function getRequest(): RequestInterface
	{
		return $this->request;
	}
}
