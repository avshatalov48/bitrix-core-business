<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Psr\Log;
use Bitrix\Main\Diag;
use Psr\Http\Message\RequestInterface;

class Handler implements Log\LoggerAwareInterface, DebugInterface
{
	use Log\LoggerAwareTrait;
	use DebugInterfaceTrait;

	protected bool $waitResponse = true;
	protected int $bodyLengthMax = 0;

	protected RequestInterface $request;
	protected ResponseBuilder $responseBuilder;
	protected $shouldFetchBody = null;
	protected string $responseHeaders = '';
	protected ?Response $response = null;
	private bool $logStarted = false;

	/**
	 * @param RequestInterface $request
	 * @param ResponseBuilder $responseBuilder
	 * @param array $options
	 */
	public function __construct(RequestInterface $request, ResponseBuilder $responseBuilder, array $options = [])
	{
		$this->request = $request;
		$this->responseBuilder = $responseBuilder;

		if (isset($options['waitResponse']))
		{
			$this->waitResponse = (bool)$options['waitResponse'];
		}
		if (isset($options['bodyLengthMax']))
		{
			$this->bodyLengthMax = (int)$options['bodyLengthMax'];
		}
	}

	/**
	 * @return RequestInterface
	 */
	public function getRequest(): RequestInterface
	{
		return $this->request;
	}

	/**
	 * @return Response | null
	 */
	public function getResponse(): ?Response
	{
		return $this->response;
	}

	/**
	 * Returns the logger from the configuration settings.
	 *
	 * @return Log\LoggerInterface|null
	 */
	public function getLogger()
	{
		if ($this->logger === null)
		{
			$logger = Diag\Logger::create('main.HttpClient', [$this, $this->request]);

			if ($logger !== null)
			{
				$this->setLogger($logger);
			}
		}

		return $this->logger;
	}

	public function log(string $logMessage, int $level): void
	{
		if (($logger = $this->getLogger()) && ($this->debugLevel & $level))
		{
			$message = '';
			$context = [];
			if (!$this->logStarted)
			{
				$this->logStarted = true;
				$message = "\n{delimiter}\n{date} - {host}\n{trace}";
				$context =  ['trace' => Diag\Helper::getBackTrace(10, DEBUG_BACKTRACE_IGNORE_ARGS, 5)];
			}

			$message .= $logMessage;

			$logger->debug($message, $context);
		}
	}

	/**
	 * Sets a callback called before fetching a message body.
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function shouldFetchBody(callable $callback): void
	{
		$this->shouldFetchBody = $callback;
	}
}
