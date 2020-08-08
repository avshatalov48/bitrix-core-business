<?php

namespace Bitrix\Sale\TradingPlatform\Ebay\Api;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentNullException;

Loc::loadMessages(__FILE__);

abstract class Entity
{
	protected $siteId;
	protected $apiCaller;
	protected $authToken;
	protected $ebaySiteId;
	protected $warningLevel = "High";

	public function __construct($siteId)
	{
		if(!isset($siteId))
			throw new ArgumentNullException("siteId");

		$this->siteId = $siteId;
		$ebay = \Bitrix\Sale\TradingPlatform\Ebay\Ebay::getInstance();
		$settings = $ebay->getSettings();

		if(empty($settings[$siteId]["API"]["SITE_ID"]))
			throw new SystemException(Loc::getMessage('SALE_EBAY_ENTITY_SETTINGS_EMPTY', array('#SITE_ID#' => $siteId)));

		if(empty($settings[$siteId]["API"]["SITE_ID"]))
			throw new ArgumentNullException(Loc::getMessage('SALE_EBAY_ENTITY_TOKEN_EMPTY', array('#SITE_ID#' => $siteId)));

		$this->ebaySiteId = $settings[$siteId]["API"]["SITE_ID"];
		$this->authToken = $settings[$siteId]["API"]["AUTH_TOKEN"];
		$this->apiCaller = new Caller( array(
			"EBAY_SITE_ID" => $settings[$siteId]["API"]["SITE_ID"],
			"URL" => $ebay->getApiUrl(),
		));
	}

	protected function array2Tags(array $params)
	{
		$result = "";

		foreach($params as $tag => $value)
		{
			if(is_array($value))
			{
				reset($value);

				if(key($value) !== 0)
				{
					$result .= $this->array2Tags($value);
				}
				else
				{
					foreach($value as $val)
					{
						$result .= '<'.$tag.'>'.$val.'</'.$tag.'>'."\n";
					}
				}
			}
			elseif($value <> '')
			{
				$result .= '<'.$tag.'>'.$value.'</'.$tag.'>'."\n";
			}
		}

		return $result;
	}
} 