<?php

namespace Bitrix\Calendar\Sync\Google\Builders;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Google\Factory;
use Bitrix\Calendar\Sync\Google\Helper;
use Bitrix\Calendar\Sync\Vendor\Vendor;

class BuilderConnectionFromExternalData implements Builder
{
	private Role $owner;

	public function __construct(Role $user)
	{
		$this->owner = $user;
	}

	/**
	 * @return Connection
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function build(): Connection
	{
		return (new Connection())
			->setVendor(new Vendor([
				'SERVER_SCHEME' => Helper::HTTP_SCHEME_DEFAULT,
				'SERVER_HOST' => Helper::GOOGLE_API_URL,
				'SERVER_PORT' => Helper::DEFAULT_HTTPS_PORT,
				'SERVER_PATH' => Helper::GOOGLE_API_V3_URI,
				'SERVER_USERNAME' => null,
				'SERVER_PASSWORD' => null,
				'ACCOUNT_TYPE' => Factory::SERVICE_NAME,
			]))
			->setDeleted(false)
			->setOwner($this->owner)
		;
	}
}
