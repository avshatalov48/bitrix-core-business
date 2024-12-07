<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use \Bitrix\Main\Web\Json;
use \Bitrix\Seo\Retargeting\Response;

Loc::loadMessages(__FILE__);
class ResponseVkads extends Response
{
	const TYPE_CODE = 'vkads';

	public function parse($data)
	{
		$parsed = is_array($data) ? $data : Json::decode($data);
		if ($parsed['error'])
		{
			$errorMessage = $parsed['error']['error_msg'];
			if ((string)$parsed['error']['error_code'] === '100')
			{
				$errorMessage = Loc::getMessage(
					'SEO_RETARGETING_SERVICE_RESPONSE_VKONTAKTE_ERROR_100',
					[
						'%code%' => htmlspecialcharsbx($parsed['error']['error_code']),
						'%msg%' => htmlspecialcharsbx($parsed['error']['error_msg']),
					]
				);
			}

			$errorMessage = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_VKONTAKTE_ERROR')
				. ': '
				. $errorMessage;
			$this->addError(new Error($errorMessage, $parsed['error']['error_code']));
		}

		$result = [];
		if ($parsed['response'])
		{
			$result = $parsed['response'];
		}
		elseif(!isset($parsed['error']))
		{
			$result = $parsed;
		}

		$this->setData(is_array($result) ? $result : [$result]);
	}
}
