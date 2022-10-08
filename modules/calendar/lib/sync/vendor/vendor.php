<?php
namespace Bitrix\Calendar\Sync\Vendor;

use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Sync\Connection\ServerInterface;
use Bitrix\Main\ObjectException;

class Vendor implements VendorInterface
{
	/** @var string */
	protected $code;
	/** @var string */
	protected $title;
	/** @var ServerInterface */
	private $server;

	/**
	 * @param array $data
	 *
	 * @throws ObjectException
	 */
	public function __construct(array $data)
	{
		if (empty($data['ACCOUNT_TYPE']))
		{
			throw new ObjectException("Account type is not defined");
		}
		$this->server = new Server($data);
		$this->code = $data['ACCOUNT_TYPE'];
		$this->title = $data['ACCOUNT_TYPE'];
	}

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @return ServerInterface
	 */
	public function getServer(): ServerInterface
	{
		return $this->server;
	}

	/**
	 * @param Server $server
	 *
	 * @return $this
	 */
	public function setServer(Server $server): self
	{
		$this->server = $server;

		return $this;
	}
}
