<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Http\Promise\Promise as PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Promise implements PromiseInterface
{
	protected string $state = self::PENDING;

	/** @var callable[] */
	protected $onFulfilled = [];

	/** @var callable[] */
	protected $onRejected = [];

	protected $handler;

	protected Queue $queue;

	protected ResponseInterface $response;

	protected ?ClientException $exception = null;

	protected string $id = '';

	public function __construct($handler, Queue $queue)
	{
		$this->handler = $handler;
		$this->queue = $queue;
	}

	/**
	 * @inheritdoc
	 */
	public function then(callable $onFulfilled = null, callable $onRejected = null)
	{
		$state = $this->getState();

		if ($onFulfilled)
		{
			if ($state == self::PENDING)
			{
				$this->onFulfilled[] = $onFulfilled;
			}
			elseif ($state == self::FULFILLED)
			{
				call_user_func($onFulfilled, $this->response);
			}
		}

		if ($onRejected)
		{
			if ($state == self::PENDING)
			{
				$this->onRejected[] = $onRejected;
			}
			elseif ($state == self::REJECTED)
			{
				$this->exception = call_user_func($onRejected, $this->exception);
			}
		}

		return new static($this->handler, $this->queue);
	}

	/**
	 * @inheritdoc
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @inheritdoc
	 */
	public function wait($unwrap = true)
	{
		$this->queue->wait($this);

		if ($unwrap)
		{
			if ($this->getState() == self::REJECTED)
			{
				throw $this->exception;
			}

			return $this->response;
		}

		return null;
	}

	/**
	 * Fulfills the promise.
	 *
	 * @param ResponseInterface $response
	 * @return void
	 */
	public function fulfill(ResponseInterface $response): void
	{
		$this->state = self::FULFILLED;
		$this->response = $response;

		while (!empty($this->onFulfilled))
		{
			$callback = array_shift($this->onFulfilled);
			$response = call_user_func($callback, $this->response);

			if ($response instanceof ResponseInterface)
			{
				$this->response = $response;
			}
		}
	}

	/**
	 * Rejects the promise.
	 *
	 * @param ClientException $exception
	 * @return void
	 */
	public function reject(ClientException $exception): void
	{
		$this->state = self::REJECTED;
		$this->exception = $exception;

		while (!empty($this->onRejected))
		{
			$callback = array_shift($this->onRejected);
			try
			{
				$exception = call_user_func($callback, $exception);
				$this->exception = $exception;
			}
			catch (ClientException $exception)
			{
				$this->exception = $exception;
			}
		}
	}

	/**
	 * @return RequestInterface
	 */
	public function getRequest(): RequestInterface
	{
		return $this->handler->getRequest();
	}

	/**
	 * Returns the promise's id string.
	 *
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	abstract public function getHandler();
}
