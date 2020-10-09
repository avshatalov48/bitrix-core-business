<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
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
		if ($data['error'])
		{
			if (is_array($data['error']))
			{
				if ($data['error']['status'] && $data['error']['status'] == 'UNAUTHENTICATED')
				{
					$this->addError(new Error("Unauthorized"));
					$this->setData([]);
					return;
				}
				$data['error'] = $data['error']['message'];
			}
			$errorMessage = $data['error'];
			if (mb_strpos($errorMessage, 'AuthenticationError.CUSTOMER_NOT_FOUND') !== false
				|| mb_strpos($errorMessage, 'AuthenticationError.NOT_ADS_USER') !== false) // google user hasn't google ads accounts
			{
				$this->setData([]);
				return;
			}
			if (mb_strpos($errorMessage, 'UserListError.ADVERTISER_NOT_WHITELISTED_FOR_USING_UPLOADED_DATA') !== false)
			{
				$errorMessage = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_GOOGLE_CANT_ADD_AUDIENCE', ['#LINK#' => 'https://support.google.com/adspolicy/answer/6299717']);
			}
			if (mb_strpos($errorMessage, 'UserListError.NAME_ALREADY_USED') !== false)
			{
				$errorMessage = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_GOOGLE_NAME_ALREADY_USED');
			}
			$errorMessage = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_GOOGLE_ERROR', ['#ERROR#' => $errorMessage]);
			$this->addError(new Error($errorMessage));
		}
		$this->setData($data);
	}
}