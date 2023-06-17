<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Bitrix\Main\Application;

abstract class Queue
{
	public function __construct(bool $backgroundJob = true)
	{
		if ($backgroundJob)
		{
			// wait for promises if no one called wait()
			Application::getInstance()->addBackgroundJob([$this, 'wait'], [], Application::JOB_PRIORITY_LOW);
		}
	}

	/**
	 * Waits for promises and returns an array with fullfilled or rejected promises.
	 *
	 * @param Promise|null $targetPromise If specified, returns on fullfilling or rejecting this promise
	 * @return Promise[]
	 */
	abstract public function wait(?Promise $targetPromise = null): array;
}
