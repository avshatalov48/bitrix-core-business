<?
namespace Bitrix\Calendar\Controller;

use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Error;
use \Bitrix\Main\Engine\Response;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ResourceBookingAjax
 */

class ResourceBookingAjax extends \Bitrix\Main\Engine\Controller
{
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
		return \Bitrix\Calendar\UserField\ResourceBooking::prepareSettings();
	}

	public function initB24LimitationAction()
	{
		return \Bitrix\Calendar\UserField\ResourceBooking::getB24LimitationPopupParams();
	}

	public function getBitrix24LimitationAction()
	{
		return \Bitrix\Calendar\UserField\ResourceBooking::getBitrx24Limitation();
	}
}