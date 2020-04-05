<?php


namespace Bitrix\Calendar\Sync;

use Bitrix\Calendar\Internals;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;

class GoogleApiSection
{
	private $userId,
			$connection;

	/**
	 * GoogleApiSection constructor.
	 * @param $userId
	 */
	public function __construct($userId)
	{
		if (!$userId)
		{
			$userId = \CCalendar::GetUserId();
		}

		$this->userId = $userId;
	}

	/**
	 * @return array|bool
	 */
	public function sendSections()
	{
		$sectionIds = [];
		$this->connection = $this->getGoogleConnection();

		if (!$this->connection)
		{
			return false;
		}

		$sections = $this->getLocalSections();

		if (!empty($sections) && count($sections) > 0)
		{
			foreach ($sections as $section)
			{
				$responseFields = $this->sendSection($section);

				if ($responseFields)
				{
					$sectionId = $this->saveSection($responseFields, $section);

					if (isset($sectionId))
					{
						$sectionIds[] = $sectionId;
					}
				}
				else
				{
					AddMessage2Log('Failed to save section in google :'.$section['ID']);
				}
			}
		}
		else
		{
			AddMessage2Log('User '.$this->userId.' has no sections to sync');
			return false;
		}

		return !empty($sectionIds) ? $sectionIds : false;
	}

	/**
	 * @param $section
	 *
	 * @return array|null
	 */
	public function sendSection($section)
	{
		$googleApiConnection = new GoogleApiSync($this->userId);
		return $googleApiConnection->createCalendar($section);
	}

	/**
	 * @param $responseFields
	 * @param $section
	 * @return integer
	 */
	private function saveSection($responseFields, $section)
	{
		$sectionId = \CCalendarSect::Edit(
			['arFields' => [
				'ID' => $section['ID'],
				'GAPI_CALENDAR_ID' => $responseFields['GAPI_CALENDAR_ID'],
				'CAL_DAV_CON' => $this->connection,
			]]);

		return $sectionId ?: false;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getLocalSections()
	{
		$sections =[];

		$filter = Query::filter()
			->where('CAL_TYPE', 'user')
			->where('OWNER_ID', $this->userId)
			->whereNull('GAPI_CALENDAR_ID', 'CAL_DAV_CON');

		$sectionList = Internals\SectionTable::getList(
			array(
				'filter' => $filter,
				'order' => [
					'ID' => 'ASC',
				],
			)
		);

		while ($section = $sectionList->fetch())
		{
			$sections[] = $section;
		}

		return $sections;
	}

	/**
	 * @return bool|mixed
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function getGoogleConnection()
	{
		if (Loader::includeModule('dav'))
		{
			$davConnections = \CDavConnection::GetList(
				[],
				[
					'ACCOUNT_TYPE' => 'google_api_oauth',
					'ENTITY_TYPE' => 'user',
					'ENTITY_ID' => $this->userId,
				]
			);

			if ($connection = $davConnections->fetch())
			{
				return $connection['ID'];
			}
		}

		return false;
	}

	/**
	 * @param $sectionId
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getSectionById($sectionId)
	{
		return Internals\SectionTable::getById($sectionId)->fetch();
	}
}