<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange;

/**
 * Class ProfileDocument
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 * For backward compatibility
 */
class ProfileDocument extends UserProfileDocument
{
	/**
	 * @return int
	 */
	public function getOwnerEntityTypeId()
	{
		return Exchange\EntityType::PROFILE;
	}

	/**
	 * @return array
	 */
	static protected function getMessageExport()
	{
		return Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/general/export.php');
	}

	public function outputXml(array $fields, $level = 0)
	{
		$xml = '';
		foreach ($fields as $name=>$value)
		{
			if(is_array($value))
			{
				switch ($name)
				{
					case 'REGISTRATION_ADDRESS':
					case 'UR_ADDRESS':
					case 'ADDRESS':
					case 'CONTACTS':
					case 'REPRESENTATIVES':
						$xml .= $this->openNodeDirectory($level+2, $name);
						$xml .= $this->outputXmlAddress($level+3, $value);
						$xml .= $this->closeNodeDirectory($level+2, $name);
						break;
				}
			}
			else
				$xml .= $this->formatXMLNode($level+2, $name, $value);
		}
		return $xml;
	}

	public function getNameNodeDocument()
	{
		return 'AGENT';
	}
}