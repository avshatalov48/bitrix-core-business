<?

namespace Bitrix\Main\UI\Selector;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Entities
{
	const CODE_USER_REGEX = '/^U(\d+)$/i';
	const CODE_USERALL_REGEX = '/^UA$/i';
	const CODE_SONETGROUP_REGEX = '/^SG(\d+)$/i';
	const CODE_GROUP_REGEX = '/^G(\d+)$/i';
	const CODE_DEPT_REGEX = '/^D(\d+)$/i';
	const CODE_DEPTR_REGEX = '/^DR(\d+)$/i';
	const CODE_CRMCONTACT_REGEX = '/^CRMCONTACT(\d+)$/i';
	const CODE_CRMCOMPANY_REGEX = '/^CRMCOMPANY(\d+)$/i';
	const CODE_CRMLEAD_REGEX = '/^CRMLEAD(\d+)$/i';
	const CODE_CRMDEAL_REGEX = '/^CRMDEAL(\d+)$/i';

	public static function getList($params = array())
	{
		$result = array();

		if (empty($params['context']))
		{
			return $result;
		}

		if (empty($params['itemsSelected']))
		{
			return $result;
		}

		$event = new Event("main", "OnUISelectorEntitiesGetList", $params);
		$event->send();
		$eventResultList = $event->getResults();

		if (is_array($eventResultList) && !empty($eventResultList))
		{
			foreach ($eventResultList as $eventResult)
			{
				if ($eventResult->getType() == EventResult::SUCCESS)
				{
					$resultParams = $eventResult->getParameters();
					$result = $resultParams['result'];
					break;
				}
			}
		}

		return $result;
	}

	public static function getEntityType($params)
	{
		$result = false;

		if (
			empty($params)
			|| empty($params['itemCode'])
		)
		{
			return $result;
		}

		$itemCode = $params['itemCode'];

		if (preg_match(self::CODE_USER_REGEX, $itemCode, $matches))
		{
			$result = 'users';
		}
		elseif (preg_match(self::CODE_SONETGROUP_REGEX, $itemCode, $matches))
		{
			$result = 'sonetgroups';
		}
		elseif (
			preg_match(self::CODE_DEPT_REGEX, $itemCode, $matches)
			|| preg_match(self::CODE_DEPTR_REGEX, $itemCode, $matches)
		)
		{
			$result = 'department';
		}
		elseif (
			preg_match(self::CODE_USERALL_REGEX, $itemCode, $matches)
			|| preg_match(self::CODE_GROUP_REGEX, $itemCode, $matches)
		)
		{
			$result = 'groups';
		}

		return $result;
	}
}