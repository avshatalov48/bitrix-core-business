<?php
namespace Bitrix\Main\Rest;

class Smile extends \IRestService
{
	public static function getList($arParams, $n, \CRestServer $server)
	{
		$smiles = \CSmileGallery::getSmilesWithSets();

		return self::objectEncode([
			'SETS' => $smiles['SMILE_SET'],
			'SMILES' => array_values($smiles['SMILE']),
		], [
			'IMAGE_FIELD' => ['IMAGE']
		]);
	}

	/* Utils */
	public static function objectEncode($data, $options = [])
	{
		if (!is_array($options['IMAGE_FIELD']))
		{
			$options['IMAGE_FIELD'] = ['AVATAR', 'AVATAR_HR'];
		}

		if (is_array($data))
		{
			$result = [];
			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					$value = self::objectEncode($value, $options);
				}
				else if ($value instanceof \Bitrix\Main\Type\DateTime)
				{
					$value = date('c', $value->getTimestamp());
				}
				else if (is_string($key) && in_array($key, $options['IMAGE_FIELD']) && is_string($value) && $value && strpos($value, 'http') !== 0)
				{
					$value = self::getServerAddress().$value;
				}

				$key = str_replace('_', '', lcfirst(ucwords(strtolower($key), '_')));

				$result[$key] = $value;
			}
			$data = $result;
		}

		return $data;
	}

	public static function getServerAddress()
	{
		$publicUrl = \Bitrix\Main\Config\Option::get('main', 'last_site_url', '');

		if ($publicUrl)
		{
			return $publicUrl;
		}
		else
		{
			return (\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".$_SERVER['SERVER_NAME'].(in_array($_SERVER['SERVER_PORT'], Array(80, 443))?'':':'.$_SERVER['SERVER_PORT']);
		}
	}
}