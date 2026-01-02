<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Bitrix\Main\Diag;
use Bitrix\Main\Web\HttpDebug;
use Psr\Log;
use Psr\Http\Message\RequestInterface;

abstract class Handler implements Log\LoggerAwareInterface, DebugInterface
{
	use Log\LoggerAwareTrait;
	use DebugInterfaceTrait;

	protected bool $waitResponse = true;
	protected int $bodyLengthMax = 0;
	protected bool $async = false;

	protected RequestInterface $request;
	protected ResponseBuilderInterface $responseBuilder;
	protected $shouldFetchBody = null;
	protected string $responseHeaders = '';
	protected ?Response $response = null;
	private bool $logStarted = false;

	/**
	 * @param RequestInterface $request
	 * @param ResponseBuilderInterface $responseBuilder
	 * @param array $options
	 */
	public function __construct(RequestInterface $request, ResponseBuilderInterface $responseBuilder, array $options = [])
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
		if (isset($options['async']))
		{
			$this->async = (bool)$options['async'];
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

			$this->setLogger($logger ?? new Log\NullLogger());
		}

		return ($this->logger instanceof Log\NullLogger ? null : $this->logger);
	}

	public function log(string $logMessage, int $level, array $context = []): void
	{
		if (($logger = $this->getLogger()) && ($this->debugLevel & $level))
		{
			if (!$this->logStarted)
			{
				$this->logStarted = true;

				$headMessage = "\n{delimiter}\n{date} - {host}\n{trace}";
				$headContext =  ['trace' => Diag\Helper::getBackTrace(10, DEBUG_BACKTRACE_IGNORE_ARGS, 5)];

				$logger->debug($headMessage, $headContext);
			}

			$logger->debug($logMessage, $context);
		}
	}

	public function logDiagnostics(): void
	{
		if ($this->debugLevel & HttpDebug::DIAGNOSTICS)
		{
			$this->log(
				"\n***TIME connect={connect}, handshake={handshake}, request={request}, total={total}\n",
				HttpDebug::DIAGNOSTICS,
				$this->getDiagnostics()
			);
		}
	}

	abstract protected function getDiagnostics(): array;

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

	abstract public function execute(): Response;
}
