<?
namespace Bitrix\B24Connector;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialservices\ApTable;
use Bitrix\Socialservices\ContactConnectTable;

Loc::loadMessages(__FILE__);

/**
 * Class Connection
 * @package Bitrix\B24Connector
 */
class Connection
{
	/**
	 * @return bool|string
	 * @throws \Bitrix\Main\LoaderException
	 */
	private static function getAppId()
	{
		if(!Loader::includeModule('socialservices'))
			return '';

		if(!self::isLinkedToNet())
			self::linkToNet();

		$interface = new \CBitrix24NetOAuthInterface();
		return $interface->getAppID();
	}

	/**
	 * Link site to Bitrix24.Network
	 * Code borrowed from socialservices/options.php
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 *
	 */
	private static function linkToNet()
	{
		if(!Loader::includeModule('socialservices'))
			return false;

		if(self::isLinkedToNet())
			return true;

		$result = false;
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$host = ($request->isHttps() ? 'https://' : 'http://').$request->getHttpHost();
		$registerResult = \CSocServBitrix24Net::registerSite($host);

		if(is_array($registerResult) && isset($registerResult["client_id"]) && isset($registerResult["client_secret"]))
		{
			Option::set('socialservices', 'bitrix24net_domain', $host);
			Option::set('socialservices', 'bitrix24net_id', $registerResult["client_id"]);
			Option::set('socialservices', 'bitrix24net_secret', $registerResult["client_secret"]);
			$result = true;
		}

		return $result;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	private static function isLinkedToNet()
	{
		return Option::get('socialservices', 'bitrix24net_id', '') !== '';
	}

	/**
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Exception
	 */
	public static function delete()
	{
		$result = new Result();

		if(!Loader::includeModule('socialservices'))
		{
			$result->addError(new Error('Module socialservices is not installed'));
			return $result;
		}

		if($connection = self::getFields())
		{
			$res = ApTable::delete($connection['ID']);

			if(!$res->isSuccess())
				$result->addErrors($res->getErrors());

			$dbRes = ButtonTable::getList(array(
				'filter' => array(
					'=APP_ID' => $connection['ID']
				)
			));

			while($but = $dbRes->fetch())
			{
				$res = ButtonTable::delete($but['ID']);

				if(!$res->isSuccess())
					$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param string $title Button title
	 * @return string Button HTML.
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getButtonHtml($title = '')
	{
		global $APPLICATION;
		$onclick = '';
		$class = 'connector-btn-blue';
		$href = 'javascript:void(0)';
		$moduleAccess = $APPLICATION->GetGroupRight('b24connector');

		if($title == '')
			$title = Loc::getMessage('B24C_CONN_BUTT_CONNECT');

		if(!Loader::includeModule('socialservices') || $moduleAccess <= "R")
		{
			$class .= ' connector-btn-blue-disabled';
		}
		else
		{
			if(!self::isLinkedToNet())
				self::linkToNet();

			$hosts = self::getHostsList();

			if(!empty($hosts))
			{
				$urlTeml = self::getUrl('##HOST##');

				if(!empty($urlTeml))
				{
					$onclick = 'BX.B24Connector.showPortalChoosingDialog(\''.\CUtil::JSEscape($urlTeml).'\', '.\CUtil::PhpToJSObject($hosts).');';
				}
				else
				{
					$onclick = 'alert(\''.Loc::getMessage('B24C_CONN_CONNECT_ERROR').'\');';
				}
			}
			else
			{
				$href = self::getUrlNet();
			}
		}

		$result = '<a href="'.htmlspecialcharsbx($href).'"'.
			($onclick <> '' ? ' onclick="'.$onclick.'"' : '').
			' class="'.$class.'" >'.
			$title.'</a>';

		return $result;
	}

	/**
	 * @param string $title Button title
	 * @return string HTML for connect button.
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getOptionButtonHtml($title)
	{
		$onclick = '';
		$disabled = false;

		if(!\Bitrix\Main\Loader::includeModule('socialservices'))
		{
			$disabled = true;
		}
		else
		{
			if(!self::isLinkedToNet())
				self::linkToNet();

			$hosts = self::getHostsList();

			if(!empty($hosts))
			{
				$urlTeml = self::getUrl('##HOST##');

				if(!empty($urlTeml))
				{
					$onclick = 'BX.B24Connector.showPortalChoosingDialog(\''.\CUtil::JSEscape($urlTeml).'\', '.\CUtil::PhpToJSObject($hosts).');';
				}
				else
				{
					$onclick = 'alert(\''.\CUtil::JSEscape(Loc::getMessage('B24C_CONN_CONNECT_ERROR')).'\');';
				}
			}
			else
			{
				$onclick = 'window.location.href="'.\CUtil::JSEscape(self::getUrlNet()).'"';
			}
		}

		return '<input type="button" onclick="'.htmlspecialcharsbx($onclick).'" value="'.$title.'"'.($disabled ? ' disabled' : '').'>';
	}

	/**
	 * @return array Connection fields.
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getFields()
	{
		static $result = null;

		if($result === null)
		{
			$result = array();

			if(Loader::includeModule('socialservices'))
				$result = ApTable::getConnection();
		}

		return is_array($result) ? $result : array();
	}

	/**
	 * @return string Domain.
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getDomain()
	{
		$fields = self::getFields();
		return !empty($fields['DOMAIN']) ? $fields['DOMAIN'] : '';
	}

	/**
	 * Check if connection exists.
	 * @return bool
	 */
	public static function isExist()
	{
		$fields = self::getFields();
		return !empty($fields);
	}

	/**
	 * @param $host
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	private static function getUrl($host)
	{
		global $APPLICATION;

		if($host == '')
			return '';

		if(!Loader::includeModule("socialservices"))
			return '';

		$result = '';
		$appId = self::getAppID();

		if($appId <> '')
		{
			$result = $host.'apconnect/?client_id='.urlencode($appId).'&preset=ap&state='.urlencode(http_build_query(array(
				'check_key' => \CSocServAuthManager::GetUniqueKey(),
				'admin' => 1,
				'backurl' => $APPLICATION->GetCurPageParam(),
			)));
		}

		return $result;
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	private static function getUrlNet()
	{
		if(!Loader::includeModule("socialservices"))
			return '';

		global $APPLICATION;
		$appId = self::getAppID();
		$result = '';

		if($appId <> '')
		{
			$result = \CBitrix24NetOAuthInterface::NET_URL.'/oauth/select/?preset=ap&client_id='.urlencode($appId).'&state='.urlencode(http_build_query(array(
				'check_key' => \CSocServAuthManager::GetUniqueKey(),
				'admin' => 1,
				'backurl' => $APPLICATION->GetCurPageParam('', array('apaction', 'apID')),
			)));
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	private  static function getHostsList()
	{
		if(!Loader::includeModule('socialservices'))
			return array();

		$result = array();
		$query = \CBitrix24NetTransport::init();

		if($query)
			$result = $query->call('admin.profile.list', array());

		return !empty($result['result']) && is_array($result['result']) ? $result['result'] : array();
	}

	/**
	 * @return string Url to edit open lines settings.
	 */
	public static function getOpenLinesConfigUrl()
	{
		$res = self::getDataFromRest('imopenlines.config.path.get', array('result'));

		if(is_array($res) && !empty($res['SERVER_ADDRESS']) && !empty($res['PUBLIC_PATH']))
		{
			$result = $res['SERVER_ADDRESS'].$res['PUBLIC_PATH'];
		}
		else
		{
			$domain = self::getDomain();

			if($domain == '')
				return '';

			$result = 'https://'.htmlspecialcharsbx($domain).'/settings/openlines/'; //default for b24 cloud
		}

		return $result;
	}

	/**
	 * @return string Url to edit telephony settings.
	 */
	public static function getTelephonyConfigUrl()
	{
		return self::getDataFromRest('voximplant.url.get', array('result', 'lines'), '/telephony/lines.php');
	}

	/**
	 * @return string Url to edit webform settings.
	 */
	public static function getWebformConfigUrl()
	{
		return self::getDataFromRest('crm.webform.configuration.get', array('result', 'URL'), '/crm/webform/');
	}

	/**
	 * @return string Url to edit widgets.
	 */
	public static function getWidgetsConfigUrl()
	{
		return self::getDataFromRest('crm.sitebutton.configuration.get', array('result', 'URL'), '/crm/button/');
	}

	private static function getDataFromRest($method, $pathToData, $defaultPath = '')
	{
		if(!Loader::includeModule('socialservices'))
			return '';

		$result = '';

		if($client = \Bitrix\Socialservices\ApClient::init())
		{
			$result = $client->call($method);

			if(is_array($result))
			{
				foreach($pathToData as $idx)
				{
					if(!empty($result[$idx]))
					{
						$result = $result[$idx];
					}
					else
					{
						$result = '';
						break;
					}
				}
			}
		}

		if(is_array($result))
			return $result;

		if($result == '')
		{
			$domain = self::getDomain();

			if($domain == '')
				return '';

			$result = 'https://'.htmlspecialcharsbx($domain).$defaultPath; //default for b24 cloud
		}

		return $result;
	}
}