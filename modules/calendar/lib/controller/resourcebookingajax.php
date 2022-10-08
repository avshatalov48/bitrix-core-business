<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\UserField\ResourceBooking;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

/**
 * Class ResourceBookingAjax
 */

class ResourceBookingAjax extends \Bitrix\Main\Engine\Controller
{
	public function configureActions()
	{
		return [
			'getFillFormData' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class
				],
				'+postfilters' => [
					new ActionFilter\Cors()
				],
			],
		];
	}

	public function getPlannerDataAction()
	{
		$request = $this->getRequest();

		return \CCalendarPlanner::prepareData(array(
			'user_id' => \CCalendar::getCurUserId(),
			'codes' => $request->getPost('codes'),
			'resources' => $request->getPost('resources'),
			'date_from' => \CCalendar::date(\CCalendar::timestamp($request->getPost('from')), false),
			'date_to' => \CCalendar::date(\CCalendar::timestamp($request->getPost('to')), false),
			'timezone' => \CCalendar::getUserTimezoneName(\CCalendar::getCurUserId()),
			'skipEntryList' => $request->getPost('currentEventList')
		));
	}

	public function getDefaultUserfieldSettingsAction()
	{
		return ResourceBooking::prepareSettings();
	}

	public function getBitrix24LimitationAction()
	{
		return ResourceBooking::getBitrx24Limitation();
	}

	public function getUserSelectorDataAction()
	{
		$request = $this->getRequest();

		$selectedUserList = [];
		if (!empty($request['selectedUserList']) && is_array($request['selectedUserList']))
		{
			$selectedUserList = $request['selectedUserList'];
		}

		return \CCalendar::getSocNetDestination(false, array(), $selectedUserList);
	}

	public function getFieldParamsAction()
	{
		$request = $this->getRequest();
		return ResourceBooking::getUserFieldByFieldName($request['fieldname'], $request['selectedUsers']);
	}

	public function getFillFormDataAction()
	{
		$request = $this->getRequest();
		$settingsData = $request['settingsData'];

		if (is_string($settingsData) && $settingsData)
		{
			$settingsData = Encoding::convertEncoding($settingsData, SITE_CHARSET, 'UTF-8');
			$settingsData = Json::decode($settingsData);
		}

		return ResourceBooking::getFillFormData(
			$settingsData,
			[
				'fieldName' => $request['fieldName'],
				'from' => $request['from'],
				'to' => $request['to']
			]
		);
	}
}