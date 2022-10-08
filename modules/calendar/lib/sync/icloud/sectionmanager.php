<?php
	
namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Managers\SectionManagerInterface;
use Bitrix\Calendar\Sync\Push\Push;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Sync\Util\SectionContext;
use Bitrix\Main\Error;
use Bitrix\Main\LoaderException;

class SectionManager extends AbstractManager implements SectionManagerInterface
{
	/**
	 * @param Section $section
	 * @param SectionContext|null $context
	 *
	 * @return Result
	 * @throws LoaderException
	 * @throws \CDavArgumentNullException
	 */
	public function create(Section $section, SectionContext $context): Result
	{
		$result = new Result();
		$path = $this->connection->getServer()->getBasePath() . VendorSyncService::generateUuid();
		$data = $this->getApiService()->createSection($path, $section);

		if ($this->getApiService()->getError())
		{
			$this->processConnectionError($this->connection, $this->getApiService()->getError());
		}

		if ($data)
		{
			$result->setData([
				'id' => $data['XML_ID'],
				'version' => $data['MODIFICATION_LABEL']
			]);
		}
		else
		{
			$result->addError(new Error('Error while trying to save section'));
		}

		return $result;
	}

	/**
	 * @param Section $section
	 * @param SectionContext|null $context
	 *
	 * @return Result
	 * @throws LoaderException
	 * @throws \CDavArgumentNullException
	 */
	public function update(Section $section, SectionContext $context): Result
	{
		$result = new Result();
		$data = $this->getApiService()->updateSection($context->getSectionConnection()->getVendorSectionId(), $section);

		if ($this->getApiService()->getError())
		{
			$this->processConnectionError($this->connection, $this->getApiService()->getError());
		}

		if ($data['XML_ID'])
		{
			$result->setData([
				'id' => $data['XML_ID'],
				'version' => $data['MODIFICATION_LABEL']
			]);
		}
		else
		{
			if ($data['ERROR'])
			{
				$result->setData([
					'error' => $data['ERROR']
				]);
			}

			$result->addError(new Error('Error while trying to update section'));
		}

		return $result;
	}

	/**
	 * @param Section $section
	 * @param SectionContext|null $context
	 *
	 * @return Result
	 * @throws LoaderException
	 * @throws \CDavArgumentNullException
	 */
	public function delete(Section $section, SectionContext $context): Result
	{
		$result = new Result();
		$data = $this->getApiService()->deleteSection($context->getSectionConnection()->getVendorSectionId());

		if ($this->getApiService()->getError())
		{
			$this->processConnectionError($this->connection, $this->getApiService()->getError());
		}

		if (!$data)
		{
			$result->addError(new Error('Error while trying to delete section'));
		}

		return $result;
	}

	/**
	 * @return ApiService
	 */
	private function getApiService(): ApiService
	{
		if (!$this->apiService)
		{
			$this->apiService = new ApiService();
		}

		return $this->apiService;
	}

	/**
	 * @param SectionConnection $link
	 *
	 * @return Result
	 */
	public function subscribe(SectionConnection $link): Result
	{
		return new Result();
	}

	/**
	 * @param Push $push
	 *
	 * @return Result
	 */
	public function resubscribe(Push $push): Result
	{
		return new Result();
	}

	/**
	 * @param Connection $connection
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function getSections(Connection $connection): array
	{
		$result = [];
		$server = $this->prepareServerData($connection->getServer());
		$data = $this->getApiService($server)->getSectionsList($connection->getServer()->getBasePath());
		if ($data && is_array($data))
		{
			foreach ($data as $section)
			{
				if ($section['supported-calendar-component-set'] === 'VEVENT')
				{
					$result[] = [
						'XML_ID' => $section['href'],
						'NAME' => $section['displayname'],
						'DESCRIPTION' => $section['calendar-description'],
						'TYPE' => $section['supported-calendar-component-set'],
						'COLOR' => $section['calendar-color'],
						'MODIFICATION_LABEL' => $section['getctag'],
					];
				}
			}
		}

		return $result;
	}

	public function getAvailableExternalType(): array
	{
		return [Helper::ACCOUNT_TYPE];
	}

	/**
	 * @param Connection $connection
	 * @param array $error
	 * @return void
	 * @throws \CDavArgumentNullException
	 */
	private function processConnectionError(Connection $connection, array $error): void
	{
		$parsedError = '[' . $error[0] . '] ' . $error[1];
		\CDavConnection::SetLastResult($connection->getId(), $parsedError);
	}
}
