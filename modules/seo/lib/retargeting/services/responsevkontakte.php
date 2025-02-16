<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\Retargeting\Response;

Loc::loadMessages(__FILE__);
class ResponseVkontakte extends Response
{
	const TYPE_CODE = 'vkontakte';

	public function parse($data)
	{
		$parsed = is_array($data) ? $data : Json::decode($data);

		if (!is_array($parsed))
		{
			$this->setData([]);

			return;
		}

		if (isset($parsed['error']))
		{
			$errorMessage = ((string)($parsed['error']['error_msg'] ?? ''));
			$errorCode = ((string)($parsed['error']['error_code'] ?? ''));
			switch ($errorCode)
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
			$this->addError(new Error($errorMessage, $errorCode));
		}

		$result = [];
		if (isset($parsed['response']))
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