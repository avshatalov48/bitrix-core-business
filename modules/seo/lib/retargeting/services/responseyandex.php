<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Error;
use \Bitrix\Main\Web\Json;
use \Bitrix\Seo\Retargeting\Response;


class ResponseYandex extends Response
{
	const TYPE_CODE = 'yandex';

	protected function getSkippedErrorCodes()
	{
		return array(
			'400' // invalid_parameter: segment data not modified
		);
	}

	public function parse($data)
	{
		$endpointParts = explode('/', $this->getRequest()->getEndpoint());

		$parsed = Json::decode($data);

		if (isset($parsed['errors']))
		{
			if (in_array((string) $parsed['code'], $this->getSkippedErrorCodes()))
			{
				$this->setData(array());
			}
			else
			{
				$this->addError(new Error($parsed['message'], $parsed['code']));
			}
		}

		if (isset($parsed[$endpointParts[0]]))
		{
			$this->setData($parsed[$endpointParts[0]]);
		}
		else if(!isset($parsed['errors']))
		{
			$this->setData($parsed);
		}
	}
}