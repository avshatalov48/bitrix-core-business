<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http\Curl;

use Bitrix\Main\Web\Http;
use Bitrix\Main\Web\HttpDebug;

class Queue extends Http\Queue
{
	/** @var Promise[] */
	protected array $promises = [];
	protected \CurlMultiHandle $handle;

	public function __construct(bool $backgroundJob = true)
	{
		parent::__construct($backgroundJob);

		$this->handle = curl_multi_init();
	}

	public function __destruct()
	{
		curl_multi_close($this->handle);
	}

	/**
	 * Adds the promise to the queue.
	 *
	 * @param Promise $promise
	 * @return void
	 */
	public function add(Promise $promise): void
	{
		$this->promises[$promise->getId()] = $promise;

		curl_multi_add_handle($this->handle, $promise->getHandler()->getHandle());
	}

	protected function delete(string $promiseId): void
	{
		curl_multi_remove_handle($this->handle, $this->promises[$promiseId]->getHandler()->getHandle());

		unset($this->promises[$promiseId]);
	}

	/**
	 * @inheritdoc
	 */
	public function wait(?Http\Promise $targetPromise = null): array
	{
		$processedPromises = [];

		if (empty($this->promises))
		{
			return $processedPromises;
		}

		do
		{
			$fetchBody = true;

			try
			{
				$status = curl_multi_exec($this->handle, $active);
			}
			catch (SkipBodyException $e)
			{
				$fetchBody = false;
			}

			$info = curl_multi_info_read($this->handle);

			if ($info !== false)
			{
				$promiseId = spl_object_hash($info['handle']);

				$promise = $this->promises[$promiseId];
				$handler = $promise->getHandler();

				if (!$fetchBody)
				{
					// we don't want a body, just fulfil a promise with response headers
					$promise->fulfill($handler->getResponse());
				}
				elseif ($info['result'] === CURLE_OK)
				{
					$response = $handler->getResponse();

					if ($handler->getDebugLevel() & HttpDebug::RESPONSE_BODY)
					{
						$handler->log($response->getBody(), HttpDebug::RESPONSE_BODY);
					}

					// need to ajust the response headers (PSR-18)
					$response->adjustHeaders();

					$promise->fulfill($response);
				}
				else
				{
					$error = curl_error($info['handle']);

					$promise->reject(new Http\NetworkException($promise->getRequest(), $error));

					if ($logger = $handler->getLogger())
					{
						$logger->error($error);
					}
				}

				// job done, the promise is fullfilled or rejected
				$processedPromises[] = $promise;

				$this->delete($promiseId);

				if ($targetPromise && $promiseId === $targetPromise->getId())
				{
					// we were waiting for the specific promise
					return $processedPromises;
				}
			}
		}
		while ($status === CURLM_CALL_MULTI_PERFORM || $active);

		return $processedPromises;
	}
}
