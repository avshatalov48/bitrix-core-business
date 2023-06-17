<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http\Socket;

use Http\Promise\Promise as PromiseInterface;
use Bitrix\Main\Web\Http;

class Queue extends Http\Queue
{
	/** @var Promise[] */
	protected array $promises = [];
	protected int $activeQueries = 20;
	protected int $selectTimeout = 20000;
	protected array $readSockets = [];
	protected array $writeSockets = [];

	/**
	 * Adds the promise to the queue.
	 *
	 * @param Promise $promise
	 * @return void
	 */
	public function add(Promise $promise): void
	{
		$this->promises[$promise->getId()] = $promise;
	}

	public function delete(string $promiseId): void
	{
		unset($this->promises[$promiseId]);
		unset($this->readSockets[$promiseId]);
		unset($this->writeSockets[$promiseId]);
	}

	/**
	 * @inheritdoc
	 */
	public function wait(?Http\Promise $targetPromise = null): array
	{
		$jobCounter = 0;
		$processedPromises = [];

		while (!empty($this->promises))
		{
			$currentPromise = current($this->promises);

			if ($currentPromise === false)
			{
				$currentPromise = reset($this->promises);
				$jobCounter = 0;
			}

			$removedPromises = [];

			$currentPromiseId = $currentPromise->getId();
			$currentHandler = $currentPromise->getHandler();

			if ($currentHandler->getState() == Handler::PENDING)
			{
				// yet not connected, "connect" inside
				$currentHandler->process($currentPromise);

				if ($currentPromise->getState() !== PromiseInterface::PENDING)
				{
					// the promise is rejected, go to the next promise
					$removedPromises[] = $currentPromise;
				}
				else
				{
					// now connected, can "select" the socket for writing
					$this->writeSockets[$currentPromiseId] = $currentHandler->getSocket()->getResource();
				}
			}

			$read = $this->readSockets;
			$write = $this->writeSockets;
			$except = null;

			if (!empty($read) || !empty($write))
			{
				if (stream_select($read, $write, $except, 0, $this->selectTimeout) > 0)
				{
					foreach (array_merge($write, $read) as $promiseId => $dummy)
					{
						$promise = $this->promises[$promiseId];
						$handler = $promise->getHandler();

						// do real work
						$handler->process($promise);

						// put the socket into the reading or writing list to minimize calls
						$this->switchSocket($promise);

						if ($promise->getState() !== PromiseInterface::PENDING)
						{
							// job done, the promise is fullfilled or rejected
							$removedPromises[] = $promise;
						}
					}
				}
			}

			// time out control
			foreach (array_merge($this->writeSockets, $this->readSockets) as $promiseId => $dummy)
			{
				$promise = $this->promises[$promiseId];

				if ($promise->getState() === PromiseInterface::PENDING)
				{
					$handler = $promise->getHandler();
					if ($handler->getSocket()->timedOut())
					{
						$exception = new Http\NetworkException($promise->getRequest(), 'Stream timeout has been reached.');
						$promise->reject($exception);

						if ($logger = $handler->getLogger())
						{
							$logger->error($exception->getMessage());
						}

						$removedPromises[] = $promise;
					}
				}
			}

			foreach ($removedPromises as $promise)
			{
				// job done, the promise is fullfilled or rejected
				$processedPromises[] = $promise;

				$promiseId = $promise->getId();

				$this->delete($promiseId);
				$jobCounter--;

				if ($targetPromise && $promiseId === $targetPromise->getId())
				{
					// we were waiting for the specific promise
					return $processedPromises;
				}
			}

			// go to the next job in the queue
			$jobCounter++;
			if ($jobCounter >= $this->activeQueries)
			{
				$jobCounter = 0;
				reset($this->promises);
			}
			elseif (isset($this->promises[$currentPromiseId]))
			{
				// unsetting an element the current pointer points to, moves the pointer forward
				next($this->promises);
			}
		}

		return $processedPromises;
	}

	protected function switchSocket(Promise $promise): void
	{
		$promiseId = $promise->getId();
		$handler = $promise->getHandler();
		$state = $handler->getState();

		if ($state === Handler::BODY_SENT || $state === Handler::CONNECT_SENT)
		{
			// switch the socket to "reading"
			if (isset($this->writeSockets[$promiseId]))
			{
				unset($this->writeSockets[$promiseId]);
			}
			if (!isset($this->readSockets[$promiseId]))
			{
				$this->readSockets[$promiseId] = $handler->getSocket()->getResource();
			}
		}
		elseif ($state === Handler::CONNECT_RECEIVED)
		{
			// switch the socket to "writing"
			if (isset($this->readSockets[$promiseId]))
			{
				unset($this->readSockets[$promiseId]);
			}
			if (!isset($this->writeSockets[$promiseId]))
			{
				$this->writeSockets[$promiseId] = $handler->getSocket()->getResource();
			}
		}
	}
}
