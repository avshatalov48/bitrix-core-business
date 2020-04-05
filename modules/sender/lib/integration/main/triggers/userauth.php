<?

namespace Bitrix\Sender\Integration\Main\Triggers;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Trigger\Base as TriggerBase;

Loc::loadMessages(__FILE__);

class UserAuth extends TriggerBase
{
	public function getName()
	{
		return Loc::getMessage('sender_trigger_user_auth_name');
	}

	public function getCode()
	{
		return "user_auth";
	}

	public function getEventModuleId()
	{
		return 'main';
	}

	public function getEventType()
	{
		return "OnAfterUserAuthorize";
	}

	public function filter()
	{
		if($this->getSiteId() == Context::getCurrent()->getSite())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function getRecipient()
	{
		$eventData = $this->getParam('EVENT');

		if(empty($eventData[0]['user_fields']))
			return null;

		return array(
			'EMAIL' => $eventData[0]['user_fields']['EMAIL'],
			'USER_ID' => $eventData[0]['user_fields']['ID'],
			'NAME' => $eventData[0]['user_fields']['NAME'],
		);
	}
}