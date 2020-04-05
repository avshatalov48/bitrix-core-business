<?php
namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\PersonTypeTable;
use Bitrix\Sale\Internals\YandexSettingsTable;

Loc::loadMessages(__FILE__);

class YandexCert
{
	static public $pkey = null;
	static public $csr = null;
	static public $sign = null;
	static public $cn = '';
	static public $errors = array();

	/**
	 * @param $shopId
	 * @param $companyName
	 */
	static public function generate($shopId, $companyName)
	{
		$yandexCsr = self::loadFromOptions($shopId);
		if ($yandexCsr === '')
		{
			self::$cn = "/business/".$companyName;

			$config = array(
				"digest_alg" => "sha1",
				"private_key_bits" => 2048,
				"private_key_type" => OPENSSL_KEYTYPE_RSA,
			);

			$dnFull = array(
				"countryName" => "RU",
				"stateOrProvinceName" => "Russia",
				"localityName" => "Moscow",
				"commonName" => self::$cn,
			);

			$res = openssl_pkey_new($config);
			$csr_origin = openssl_csr_new($dnFull, $res);
			if ($csr_origin === false)
				return;
			$csr_full = "";
			openssl_pkey_export($res, self::$pkey);
			openssl_csr_export($csr_origin, self::$csr);

			openssl_csr_export($csr_origin, $csr_full, false);
			preg_match('"Signature Algorithm\: (.*)-----BEGIN"ims', $csr_full, $sign);
			$sign = str_replace("\t", "", $sign);
			if ($sign)
			{
				$sign = $sign[1];
				$a = explode("\n", $sign);
				unset($a[0]);
				$sign = str_replace("         ", "", trim(join("\n", $a)));
			}
			self::$sign = $sign;

			$dbRes = YandexSettingsTable::getById($shopId);
			if ($dbRes->fetch())
				YandexSettingsTable::update($shopId, array('SIGN' => self::$sign, 'CSR' => self::$csr, 'PKEY' => self::$pkey, 'CERT' => ''));
			else
				YandexSettingsTable::add(array('SHOP_ID' => $shopId, 'SIGN' => self::$sign, 'CSR' => self::$csr, 'PKEY' => self::$pkey));
		}
    }

	/**
	 * @param $shopId
	 * @param bool $all
	 * @throws \Exception
	 */
    static public function clear($shopId, $all = false)
	{
		if ($all)
			$settings = array('CERT' => '', 'SIGN' => '', 'CSR' => '', 'PKEY' => '');
		else
			$settings = array('CERT' => '');

		YandexSettingsTable::update($shopId, $settings);
    }

	/**
	 * @param $shopId
	 * @return string
	 */
	static public function getCn($shopId)
	{
		$yandexCsr = self::getValue('CSR', $shopId);

		$subjects = openssl_csr_get_subject($yandexCsr);
		if (!isset($subjects['CN']) || empty($subjects['CN']))
			return '';

		return $subjects['CN'];
	}

	/**
	 * @param $shopId
	 * @return string
	 */
	static private function loadFromOptions($shopId)
	{
		$dbRes = PersonTypeTable::getList(array('select' => array('ID', 'PT_SITE_ID' => 'PERSON_TYPE_SITE.SITE_ID')));
		while ($data = $dbRes->fetch())
		{
			$csr = Option::get('yandexmoney.ycms', 'KASSA_MWS_CSR', '', $data['PT_SITE_ID']);
			if ($csr === '')
				continue;

			$csr = Option::get('yandexmoney.ycms', 'KASSA_MWS_CSR', '', $data['PT_SITE_ID']);
			$pkey = Option::get('yandexmoney.ycms', 'KASSA_MWS_PKEY', '', $data['PT_SITE_ID']);
			$sign = Option::get('yandexmoney.ycms', 'KASSA_MWS_SIGN', '', $data['PT_SITE_ID']);
			$cert = Option::get('yandexmoney.ycms', 'KASSA_MWS_CERT', '', $data['PT_SITE_ID']);

			$dbRes = YandexSettingsTable::getById($shopId);
			if (!$dbRes->fetch())
			{
				YandexSettingsTable::add(array('SHOP_ID' => $shopId, 'CSR' => $csr, 'PKEY' => $pkey, 'SIGN' => $sign, 'CERT' => $cert));
				return $csr;
			}
		}

		return '';
	}

	/**
	 * @param $shopId
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	static public function isLoaded($shopId)
	{
		$cert = self::getValue('CERT', $shopId);
        return !empty($cert);
	}

	/**
	 * @param $file
	 * @param $shopId
	 */
	static public function setCert($file, $shopId)
	{
		if (!empty($file['name']))
		{
			if (substr($file['name'], -4) != '.cer')
				self::$errors[]  = Loc::getMessage('YANDEX_CERT_ERR_EXT');
			elseif ($file['error'] != UPLOAD_ERR_OK)
				self::$errors[]  = Loc::getMessage('YANDEX_CERT_ERR_LOAD');
			elseif (filesize($file['tmp_name']) > 2048)
				self::$errors[]  = Loc::getMessage('YANDEX_CERT_ERR_SIZE');
		}
		else
		{
			self::$errors[]  = Loc::getMessage('YANDEX_CERT_ERR_LOAD');
		}

		if (empty(self::$errors))
		{
			$cert = file_get_contents($file['tmp_name']);
			$cert_info = openssl_x509_parse($cert);
			if (isset($cert_info['subject']['CN']))
			{
				if ($cert_info['subject']['CN'] != self::getCn($shopId))
				{
					self::$errors[] = Loc::getMessage('YANDEX_CERT_ERR_CN');
				}
				else
				{
					$pkey = static::getValue('PKEY', $shopId);
					if (openssl_x509_check_private_key($cert, $pkey))
					{
						YandexSettingsTable::update($shopId, array('CERT' => $cert));
					}
					else
					{
						self::$errors[] = Loc::getMessage('YANDEX_CERT_ERR_ACCORDING_PKEY_TO_CERT');
					}
				}
			}
			else
			{
				self::$errors[] = Loc::getMessage('YANDEX_CERT_ERR_NULL');
			}
		}
	}

	/**
	 * @param $shopId
	 * @return mixed
	 */
	static public function getSign($shopId)
	{
		return self::getValue('SIGN', $shopId);
	}

	/**
	 * @param $shopId
	 * @return mixed
	 */
	static public function getCert($shopId)
	{
		return self::getValue('CERT', $shopId);
	}

	/**
	 * @param $shopId
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	static public function getCsr($shopId)
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=csr_for_yamoney.csr');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		echo self::getValue('CSR', $shopId);
		die();
	}

	/**
	 * @param $field
	 * @param $shopId
	 * @return mixed|string
	 */
	static public function getValue($field, $shopId)
	{
		$dbRes = YandexSettingsTable::getList(array('filter' => array('SHOP_ID' => $shopId)));
		if ($data = $dbRes->fetch())
			return $data[$field];

		return '';
	}
}