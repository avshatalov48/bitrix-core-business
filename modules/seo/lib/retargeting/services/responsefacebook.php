<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Json;
use \Bitrix\Seo\Retargeting\Response;

Loc::loadMessages(__FILE__);

class ResponseFacebook extends Response
{
	const TYPE_CODE = 'facebook';

	public function parse($data)
	{
		$parsed = Json::decode($data);
		if ($parsed['error'])
		{
			$errorText = (isset($parsed['error']['error_user_msg']) && $parsed['error']['error_user_msg']) ? $parsed['error']['error_user_msg'] : $parsed['error']['message'];
			if ($errorText == '(#100) The parameter follow_up_action_url is required')
			{
				$errorText = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_FACEBOOK_ERROR_URL_REQUIRED');
			}
			if ($errorText == 'To create or edit a Custom Audience made from a customer list, your admin needs to add this ad account to a business.')
			{
				$errorText = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_FACEBOOK_ERROR_ADD_TO_BUSINESS');
			}

			$errorText = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_FACEBOOK_ERROR')
				. ': '
				. $errorText;
			$this->addError(new Error($errorText, $parsed['error']['code']));
		}

		if ($parsed['data'])
		{
			$this->setData($parsed['data']);
		}
		else if(!isset($parsed['error']))
		{
			$this->setData($parsed);
		}
	}
}