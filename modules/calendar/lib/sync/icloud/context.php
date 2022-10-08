<?php
	
namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Internals\ContextInterface;

class Context implements ContextInterface
{
	/** @var ?Helper $helper */
	private Helper $helper;
	/** @var ?Connection  $connection*/
	private Connection $connection;
	/** @var ?VendorSyncManager $syncManager*/
	private ?VendorSyncManager $syncManager = null;
	/** @var ?VendorSyncService $syncService*/
	private ?VendorSyncService $syncService = null;
	/** @var ?ApiService $apiService*/
	private ?ApiService $apiService = null;
	/** @var ?ApiClient  $apiClient*/
	private ?ApiClient $apiClient = null;

	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$this->helper = new Helper();
	}

	public function getSyncManager(): VendorSyncManager
	{
		if (!$this->syncManager)
		{
			$this->syncManager = new VendorSyncManager();
		}

		return $this->syncManager;
	}

	public function getSyncService(): VendorSyncService
	{
		if (!$this->syncService)
		{
			$this->syncService = new VendorSyncService();
		}

		return $this->syncService;
	}

	public function getApiService(): ApiService
	{
		if (!$this->apiService)
		{
			$this->apiService = new ApiService();
		}

		return $this->apiService;
	}

	public function getHelper(): Helper
	{
		return $this->helper;
	}

	public function getConnection(): Connection
	{
		return $this->connection;
	}
}