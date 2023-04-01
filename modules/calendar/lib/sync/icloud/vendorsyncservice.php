<?php
	
namespace Bitrix\Calendar\Sync\Icloud;

class VendorSyncService
{
	/** @var Helper $helper */
	protected Helper $helper;
	/** @var ?ApiService $apiService */
	protected ?ApiService $apiService = null;
	/** @var ?string $error */
	protected ?string $error = null;
	
	public function __construct()
	{
		$this->helper = new Helper();
	}

	//DavConnection data or only authorization data
	public function getCalendarServerPath(array $connection): ?string
	{
		if (empty($connection['ID']))
		{
			$server = $this->getApiService()->prepareUrl($this->helper::SERVER_PATH);

			$server['path'] = $this->getApiService()->getPrinciples($connection, $server);
			if (!$server['path'])
			{
				return null;
			}

			$calendarPath = $this->getApiService()->getCalendarPath($connection, $server);

			if ($calendarPath)
			{
				return $calendarPath;
			}

			return null;
		}

		return $connection['SERVER_SCHEME']
			. '://'
			. $connection['SERVER_HOST']
			. $connection['SERVER_PATH']
		;
	}

	public static function generateUuid(): string
	{
		$word = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
		
		return mb_strtoupper($word);
	}

	private function getApiService(): ApiService
	{
		if (!$this->apiService)
		{
			$this->apiService = new ApiService();
		}

		return $this->apiService;
	}
	
	public function getError(): ?string
	{
		return $this->error;
	}
}