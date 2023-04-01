<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Managers\SectionManagerInterface;
use Bitrix\Calendar\Sync\Util\SectionContext;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Calendar\Sync\Util\Result;

class SectionManager extends Manager implements SectionManagerInterface
{

	public const CREATE_PATH = '/calendars/';
	public const CALENDAR_PATH = '/calendars/%CALENDAR_ID%';
	public const CALENDAR_LIST_PATH = '/users/me/calendarList/%CALENDAR_ID%';

	/**
	 * @param Core\Section\Section $section
	 * @param SectionContext|null $context
	 *
	 * @return Result
	 *
	 * @throws ConflictException
	 */
	public function create(Core\Section\Section $section, SectionContext $context = null): Result
	{
		$result = new Result();

		try
		{
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->httpClient->query(
				HttpClient::HTTP_POST,
				$this->prepareCreateUrl(),
				$this->encode((new SectionConverter($section))->convertForEdit())
			);

			if ($this->isRequestSuccess())
			{
				$resultData = $this->prepareResult($this->httpClient->getResult(), $section);
				$this->updateSectionColor($resultData['syncSection']);
				$result->setData($resultData);
			}
			else
			{
				$response = Json::decode($this->httpClient->getResult());
				if (!empty($response['error']))
				{
					$error = $response['error'];
					switch ($error['code'])
					{
						case 409:
							throw new ConflictException($error['message'], $error['code']);
						case 401:
							$this->handleUnauthorize($this->connection);
							$result->addError(new Error($error['message'], $error['code']));
							break;
						default:
							if (!empty($error['code']))
							{
								$result->addError(new Error($error['message'], $error['code']));
							}
							else
							{
								$result->addError(new Error('Uncknown Google API error', 400));
							}
					}
				}
				else
				{
					$result->addError(new Error('do not create section'));
				}
			}
		}
		catch (ArgumentException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to create an section in google'));
		}
		catch (ObjectException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to convert section'));
		}

		return $result;
	}

	/**
	 * @param Core\Section\Section $section
	 * @param SectionContext $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function update(Core\Section\Section $section, SectionContext $context): Result
	{
		$result = new Result();

		try
		{
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->httpClient->query(
				HttpClient::HTTP_PUT,
				$this->prepareCalendarUrl($context->getSectionConnection()->getVendorSectionId()),
				$this->encode((new SectionConverter($section))->convertForEdit())
			);

			if ($this->isRequestSuccess())
			{
				$resultData = $this->prepareResult($this->httpClient->getResult(), $section);
				$this->updateSectionColor($resultData['syncSection']);
				$result->setData($resultData);
			}
			else
			{
				$response = Json::decode($this->httpClient->getResult());
				if (!empty($response['error']))
				{
					$error = $response['error'];
					switch ($error['code'])
					{
						case 401:
							$this->handleUnauthorize($this->connection);
							$result->addError(new Error($error['message'], $error['code']));
							break;
						default:
							if (!empty($error['code']))
							{
								$result->addError(new Error($error['message'], $error['code']));
							}
							else
							{
								$result->addError(new Error('Uncknown Google API error', 400));
							}
					}
				}
				$result->addError(new Error('do not update section'));
			}
		}
		catch (ArgumentException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to update an section in google'));
		}

		return $result;
	}

	/**
	 * @param Core\Section\Section $section
	 * @param SectionContext $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function delete(Core\Section\Section $section, SectionContext $context): Result
	{
		$result = new Result();

		// TODO: Remake it: move this logic to parent::request().
		// Or, better, in separate class.
		$this->httpClient->query(
			HttpClient::HTTP_DELETE,
			$this->prepareCalendarUrl($context->getSectionConnection()->getVendorSectionId())
		);
		if (!$this->isRequestDeleteSuccess())
		{
			$response = Json::decode($this->httpClient->getResult());
			if (!empty($response['error']))
			{
				$error = $response['error'];
				switch ($error['code'])
				{
					case 401:
						$this->handleUnauthorize($this->connection);
						$result->addError(new Error($error['message'], $error['code']));
						break;
					default:
						if (!empty($error['code']))
						{
							$result->addError(new Error($error['message'], $error['code']));
						}
						else
						{
							$result->addError(new Error('Uncknown Google API error', 400));
						}
				}
			}
			else
			{
				$result->addError(new Error('failed to delete an section in google'));
			}

		}

		return $result;
	}

	private function prepareCreateUrl(): string
	{
		return $this->connection->getServer()->getFullPath() . self::CREATE_PATH;
	}

	private function prepareCalendarUrl(string $vendorSectionId): string
	{
		return Server::mapUri(
			$this->connection->getServer()->getFullPath()
			. self::CALENDAR_PATH,
			[
				'%CALENDAR_ID%' => Server::getEncodePath($vendorSectionId)
			]
		);
	}

	/**
	 * @param string $result
	 * @param $section
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function prepareResult(string $result, Core\Section\Section $section): array
	{
		$externalSection = Json::decode($result);

		$sectionConnection = (new Sync\Google\Builders\BuilderSectionConnectionFromExternalSection(
			$externalSection,
			$section,
			$this->connection)
		)->build();

		$syncSection = (new Sync\Entities\SyncSection())
			->setSection($section)
			->setVendorName(Core\Section\Section::LOCAL_EXTERNAL_TYPE)
			->setAction(Sync\Dictionary::SYNC_EVENT_ACTION['success'])
			->setSectionConnection($sectionConnection)
		;

		// TODO: get rid of array structure. It's better to replace with SyncSection object
		return [
			'id' => $externalSection['id'],
			'version' => $externalSection['etag'],
			'syncSection' => $syncSection,
		];
	}

	/**
	 * @param array $section
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function encode(array $section)
	{
		return Json::encode($section, JSON_UNESCAPED_SLASHES);
	}

	/**
	 * @return array
	 */
	public function getAvailableExternalType(): array
	{
		return array_values(Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE);
	}

	/**
	 * @throws ArgumentException
	 */
	private function updateSectionColor(Sync\Entities\SyncSection $syncSection): void
	{
		$this->httpClient->put(
			$this->createCalendarListUpdateUrl($syncSection->getSectionConnection()->getVendorSectionId()),
			$this->prepareUpdateColorParams($syncSection)
		);
	}

	private function createCalendarListUpdateUrl(?string $getVendorSectionId): string
	{
		return Server::mapUri(
			$this->connection->getServer()->getFullPath()
			. self::CALENDAR_LIST_PATH
			. '?' . preg_replace('/(%3D)/', '=', http_build_query(['colorRgbFormat' => "True"])),
			[
				'%CALENDAR_ID%' => Server::getEncodePath($getVendorSectionId),
			]
		);
	}

	/**
	 * @throws ArgumentException
	 */
	private function prepareUpdateColorParams(Sync\Entities\SyncSection $syncSection)
	{
		$parameters = [];

		if ($color = $syncSection->getSection()->getColor())
		{
			$parameters['backgroundColor'] = $color;
			$parameters['foregroundColor'] = '#ffffff';
		}

		$parameters['selected'] = 'true';

		return Json::encode($parameters, JSON_UNESCAPED_SLASHES);
	}
}
