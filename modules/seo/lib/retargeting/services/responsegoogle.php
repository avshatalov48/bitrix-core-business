<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Error;
use \Bitrix\Main\Web\Json;
use \Bitrix\Seo\Retargeting\Response;


class ResponseGoogle extends Response
{
	const TYPE_CODE = 'google';

	protected function getSkippedErrorCodes()
	{
		return array(
			'400' // invalid_parameter: segment data not modified
		);
	}

	public function parse($data)
	{
		if (!is_array($data))
		{
			$data = array();
		}

		$this->setData($data);
	}
}