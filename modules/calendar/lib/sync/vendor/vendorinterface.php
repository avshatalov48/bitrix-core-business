<?php

namespace Bitrix\Calendar\Sync\Vendor;

use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Sync\Connection\ServerInterface;

interface VendorInterface
{
	/**
	 * @return string
	 */
	public function getCode(): string;

	/**
	 * @return ServerInterface
	 */
	public function getServer(): ServerInterface;

	/**
	 * @param Server $server
	 *
	 * @return $this
	 */
	public function setServer(Server $server): self;
}
