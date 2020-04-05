<?
namespace Bitrix\Main\Service\GeoIp;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class MaxMind
 * @package Bitrix\Main\Service\GeoIp
 * @link https://www.maxmind.com
 */
final class MaxMind extends Base
{
	/**
	 * @return string Title of handler.
	 */
	public function getTitle()
	{
		return 'MaxMind';
	}

	/**
	 * @return string Handler description.
	 */
	public function getDescription()
	{
		return Loc::getMessage('MAIN_SRV_GEOIP_MM_DESCRIPTION');
	}

	/**
	 * @param string $ipAddress Ip address
	 * @param string $userId user identifier obtained from www.maxmind.com
	 * @param string $licenseKey
	 * @return Main\Result
	 */
	protected function sendRequest($ipAddress, $userId, $licenseKey)
	{
		$result = new Main\Result();
		$httpClient = $this->getHttpClient();
		$httpClient->setHeader('Authorization', 'Basic '.base64_encode($userId.':'.$licenseKey));
		$httpRes = $httpClient->get("https://geoip.maxmind.com/geoip/v2.1/city/".$ipAddress.'?pretty');

		$errors = $httpClient->getError();

		if (!$httpRes && !empty($errors))
		{
			$strError = "";

			foreach($errors as $errorCode => $errMes)
				$strError .= $errorCode.": ".$errMes;

			$result->addError(new Error($strError));
		}
		else
		{
			$status = $httpClient->getStatus();

			if ($status != 200)
				$result->addError(new Error('Http status: '.$status));

			$arRes = json_decode($httpRes, true);

			if(is_array($arRes))
			{
				if(strtolower(SITE_CHARSET) != 'utf-8')
					$arRes = Encoding::convertEncoding($arRes, 'UTF-8', SITE_CHARSET);

				if ($status == 200)
				{
					$result->setData($arRes);
				}
				else
				{
					$result->addError(new Error('['.$arRes['code'].'] '.$arRes['error']));
				}
			}
			else
			{
				$result->addError(new Error('Can\'t decode json result'));
			}
		}

		return $result;
	}

	/**
	 * @return HttpClient
	 */
	protected static function getHttpClient()
	{
		return new HttpClient(array(
			"version" => "1.1",
			"socketTimeout" => 5,
			"streamTimeout" => 5,
			"redirect" => true,
			"redirectMax" => 5,
		));
	}

	/**
	 * Languages supported by handler ISO 639-1
	 * @return array
	 */
	public function getSupportedLanguages()
	{
		return array('de', 'en', 'es', 'fr', 'ja', 'pt-BR', 'ru', 'zh-CN');
	}

	/**
	 * @param string $ipAddress Ip address
	 * @param string $lang Language identifier
	 * @return Result | null
	 */
	public function getDataResult($ipAddress, $lang = '')
	{
		$dataResult = new Result();
		$geoData = new Data();
		
		$geoData->ip = $ipAddress;
		$geoData->lang = $lang = strlen($lang) > 0 ? $lang : 'en';

		if(strlen($this->config['USER_ID']) <=0 || strlen($this->config['LICENSE_KEY']) <= 0)
		{
			$dataResult->addError(new Error(Loc::getMessage('MAIN_SRV_GEOIP_MM_SETT_EMPTY')));
			return $dataResult;
		}

		$res = $this->sendRequest($ipAddress, $this->config['USER_ID'], $this->config['LICENSE_KEY']);

		if($res->isSuccess())
		{
			$data = $res->getData();

			if(!empty($data['country']['names'][$lang]))
				$geoData->countryName = $data['country']['names'][$lang];

			if(!empty($data['country']['iso_code']))
				$geoData->countryCode = $data['country']['iso_code'];

			if(!empty($data['subdivisions'][0]['names'][$lang]))
				$geoData->regionName = $data['subdivisions'][0]['names'][$lang];

			if(!empty($data['subdivisions'][0]['iso_code']))
				$geoData->regionCode = $data['subdivisions'][0]['iso_code'];

			if(!empty($data['city']['names'][$lang]))
				$geoData->cityName = $data['city']['names'][$lang];

			if(!empty($data['location']['latitude']))
				$geoData->latitude = $data['location']['latitude'];

			if(!empty($data['location']['longitude']))
				$geoData->longitude = $data['location']['longitude'];

			if(!empty($data['location']['time_zone']))
				$geoData->timezone = $data['location']['time_zone'];

			if(!empty($data['postal']['code']))
				$geoData->zipCode = $data['postal']['code'];

			if(!empty($data['traits']['isp']))
				$geoData->ispName = $data['traits']['isp'];

			if(!empty($data['traits']['organization']))
				$geoData->organizationName = $data['traits']['organization'];
		}
		else
		{
			$dataResult->addErrors($res->getErrors());
		}

		$dataResult->setGeoData($geoData);
		return $dataResult;
	}

	/**
	 * Is this handler installed and ready for using.
	 * @return bool
	 */
	public function isInstalled()
	{
		return !empty($this->config['USER_ID']) && !empty($this->config['LICENSE_KEY']);
	}

	/**
	 * @param array $postFields  Admin form posted fields during saving process.
	 * @return array Field CONFIG for saving to DB in admin edit form.
	 */
	public function createConfigField(array $postFields)
	{
		return array(
			'USER_ID' => isset($postFields['USER_ID']) ? $postFields['USER_ID'] : '',
			'LICENSE_KEY' => isset($postFields['LICENSE_KEY']) ? $postFields['LICENSE_KEY'] : '',
		);
	}

	/**
	 * @return array Set of fields description for administration purposes.
	 */
	public function getConfigForAdmin()
	{
		return array(
			array(
				'NAME' => 'USER_ID',
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_MM_F_USER_ID'),
				'TYPE' => 'TEXT',
				'VALUE' => htmlspecialcharsbx($this->config['USER_ID']),
				'REQUIRED' => true
			),
			array(
				'NAME' => 'LICENSE_KEY',
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_MM_F_LICENSE_KEY'),
				'TYPE' => 'TEXT',
				'VALUE' => htmlspecialcharsbx($this->config['LICENSE_KEY']),
				'REQUIRED' => true			)
		);
	}

	/**
	 * @return ProvidingData Geolocation information witch handler can return.
	 */
	public function getProvidingData()
	{
		$result = new ProvidingData();
		$result->countryName = true;
		$result->countryCode = true;
		$result->regionName = true;
		$result->regionCode = true;
		$result->cityName = true;
		$result->latitude = true;
		$result->longitude = true;
		$result->timezone = true;
		$result->zipCode = true;
		$result->ispName = true;
		$result->organizationName = true;
		return $result;
	}
}