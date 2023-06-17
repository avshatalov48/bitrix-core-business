<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock,
	Bitrix\Highloadblock\HighloadBlockTable;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

$result = array();
$request = Main\Context::getCurrent()->getRequest();

if (check_bitrix_sessid() && $request->isPost() && Loader::includeModule('iblock'))
{
	$propertyId = (int)$request->get('propertyId');
	if ($propertyId > 0)
	{
		$property = Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'PROPERTY_TYPE', 'USER_TYPE', 'USER_TYPE_SETTINGS'),
			'filter' => array('=ID' => $propertyId)
		))->fetch();
		if (!empty($property))
		{
			$property['USER_TYPE'] = (string)$property['USER_TYPE'];
			if ($property['USER_TYPE'] != '')
			{
				if (!is_array($property['USER_TYPE_SETTINGS']))
				{
					$property['USER_TYPE_SETTINGS'] = (string)$property['USER_TYPE_SETTINGS'];
					if (CheckSerializedData($property['USER_TYPE_SETTINGS']))
						$property['USER_TYPE_SETTINGS'] = unserialize($property['USER_TYPE_SETTINGS'], ['allowed_classes' => false]);
					if (!is_array($property['USER_TYPE_SETTINGS']))
						$property['USER_TYPE_SETTINGS'] = array();
				}
			}

			if ($property['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_STRING && $property['USER_TYPE'] === 'directory')
			{
				if (Loader::includeModule('highloadblock') && !empty($property['USER_TYPE_SETTINGS']['TABLE_NAME']))
				{
					$hlBlock = HighloadBlockTable::getList(array(
						'filter' => array('=TABLE_NAME' => $property['USER_TYPE_SETTINGS']['TABLE_NAME'])
					))->fetch();
					if (!empty($hlBlock))
					{
						$entity = HighloadBlockTable::compileEntity($hlBlock);

						$fieldsList = $entity->getFields();
						$sortExist = isset($fieldsList['UF_SORT']);
						$directorySelect = array('ID', 'UF_NAME', 'UF_XML_ID');
						$directoryOrder = array();
						if ($sortExist)
						{
							$directorySelect[] = 'UF_SORT';
							$directoryOrder['UF_SORT'] = 'ASC';
						}
						$directoryOrder['UF_NAME'] = 'ASC';

						$entityDataClass = $entity->getDataClass();
						$iterator = $entityDataClass::getList(array(
							'select' => $directorySelect,
							'order' => $directoryOrder
						));
						while ($row = $iterator->fetch())
						{
							$result[] = array(
								'value' => $row['UF_XML_ID'],
								'label' => $row['UF_NAME']
							);
						}
						unset($row, $iterator);
					}
				}
			}
			elseif ($property['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_LIST)
			{
				$iterator = Iblock\PropertyEnumerationTable::getList(array(
					'select' => array('*'),
					'filter' => array('=PROPERTY_ID' => $propertyId),
					'order' => array('DEF' => 'DESC', 'SORT' => 'ASC')
				));
				while ($row = $iterator->fetch())
				{
					$result[] = array(
						'value' => $row['ID'],
						'label' => $row['VALUE']
					);
				}
				unset($row, $iterator);
			}
		}
		unset($property);
	}
}

$APPLICATION->RestartBuffer();
header('Content-Type: application/json');
echo Bitrix\Main\Web\Json::encode($result);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
die();