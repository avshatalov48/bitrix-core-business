<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Seo\Retargeting\Request;
use Bitrix\Seo\Engine\Bitrix as EngineBitrix;

class RequestGoogle extends Request
{
	const TYPE_CODE = 'google';

	public function query(array $params = array())
	{
		$methodName = 'seo.client.ads.google.' . $params['methodName'];
		$parameters = $params['parameters'];
		$engine = new EngineBitrix();
		if (!$engine->isRegistered())
		{
			return false;
		}

		$response = $engine->getInterface()->getTransport()->call($methodName, $parameters);
		return (
			(isset($response['result']['RESULT']) && $response['result']['RESULT'])
				?
				$response['result']['RESULT']
				: array()
		);
	}
}