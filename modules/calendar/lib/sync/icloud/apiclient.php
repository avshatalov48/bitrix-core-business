<?php
	
namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Sync\Util\RequestLogger;

class ApiClient
{
	/** @var Helper $helper */
	protected Helper $helper;
	/** @var \CDavGroupdavClientCalendar $davClient*/
	protected \CDavGroupdavClientCalendar $davClient;
	/** @var ?RequestLogger $logger*/
	protected ?RequestLogger $logger = null;
	/** @var ?int $userId */
	protected ?int $userId = null;

	/**
	 * @param \CDavGroupdavClientCalendar $davClient
	 * @param int|null $userId
	 *
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function __construct(\CDavGroupdavClientCalendar $davClient, int $userId = null)
	{
		$this->helper = new Helper();
		$this->davClient = $davClient;
		$this->userId = $userId;

		if ($this->userId && RequestLogger::isEnabled())
		{
			$this->logger = new RequestLogger($this->userId, $this->helper::ACCOUNT_TYPE);
		}
	}
	
	/**
	 * @param string $url
	 * @param array|null $properties
	 * @param array|null $filter
	 * @param int $depth
	 *
	 * @return \CDavXmlDocument|null
	 */
	public function propfind(
		string $url,
		array $properties = null,
		array $filter = null,
		int $depth = 1
	): ?\CDavXmlDocument
	{
		$this->davClient->Connect();
		$result = $this->davClient->Propfind(
			$url,
			$properties,
			$filter,
			$depth,
			$this->logger
		);
		$this->davClient->Disconnect();

		if (!$result || $this->davClient->getError())
		{
			return null;
		}

		return $result->GetBodyXml();
	}

	/**
	 * @param string $url
	 * @param string $data
	 *
	 * @return mixed|null
	 */
	public function proppatch(string $url, string $data)
	{
		$this->davClient->Connect();
		$data = $this->davClient->Decode($data);
		$result = $this->davClient->Proppatch($url, $data, $this->logger);
		$this->davClient->Disconnect();

		if (!$result)
		{
			return null;
		}

		return $result->GetStatus();
	}

	/**
	 * @param string $url
	 * @param string $data
	 *
	 * @return mixed|null
	 */
	public function mkcol(string $url, string $data)
	{
		$this->davClient->Connect();
		$data = $this->davClient->Decode($data);
		$result = $this->davClient->Mkcol($url, $data, $this->logger);
		$this->davClient->Disconnect();

		if (!$result)
		{
			return null;
		}

		return $result->GetStatus();
	}

	/**
	 * @param string $url
	 *
	 * @return mixed|null
	 */
	public function delete(string $url)
	{
		$this->davClient->Connect();
		$result = $this->davClient->Delete($url, $this->logger);
		$this->davClient->Disconnect();

		if (!$result)
		{
			return null;
		}

		return $result->GetStatus();
	}

	/**
	 * @param string $url
	 * @param $data
	 *
	 * @return mixed|null
	 */
	public function put(string $url, $data)
	{
		$this->davClient->Connect();
		$data = $this->davClient->Decode($data);
		$result = $this->davClient->Put($url, $data, $this->logger);
		$this->davClient->Disconnect();

		if (!$result)
		{
			return null;
		}

		return $result->GetStatus();
	}
}