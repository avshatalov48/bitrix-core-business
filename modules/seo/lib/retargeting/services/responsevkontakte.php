<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use \Bitrix\Main\Web\Json;
use \Bitrix\Seo\Retargeting\Response;

Loc::loadMessages(__FILE__);
class ResponseVkontakte extends Response
{
	const TYPE_CODE = 'vkontakte';

	public function parse($data)
	{
		// Need for preserve double UTF-conversion, because VK return JSON answer in result
		if (is_string($data))
		{
			$data = Encoding::convertEncoding($data, SITE_CHARSET, 'UTF-8');
		}

		$parsed = is_array($data) ? $data : Json::decode($data);
		if ($parsed['error'])
		{
			$errorMessage = $parsed['error']['error_msg'];
			switch ((string) $parsed['error']['error_code'])
			{
				case '100':
					$errorMessage = Loc::getMessage(
						'SEO_RETARGETING_SERVICE_RESPONSE_VKONTAKTE_ERROR_100',
						array(
							'%code%' => htmlspecialcharsbx($parsed['error']['error_code']),
							'%msg%' => htmlspecialcharsbx($parsed['error']['error_msg']),
						)
					);
					break;
			}

			$errorMessage = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_VKONTAKTE_ERROR')
				. ': '
				. $errorMessage;
			$this->addError(new Error($errorMessage, $parsed['error']['error_code']));
		}

		$result = array();
		if ($parsed['response'])
		{
			$result = $parsed['response'];
		}
		else if(!isset($parsed['error']))
		{
			$result = $parsed;
		}

		$this->setData(is_array($result) ? $result : array($result));
	}
}