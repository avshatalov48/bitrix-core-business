<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\OrderPropsTable;
use \Bitrix\Sale\Internals\Input;
use Bitrix\Sale\OrderUserProperties;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

class OrderBuyer
{
	public static function getEdit(Sale\Order $order, $showProfiles = false, $profileId = 0)
	{
		$data = self::prepareData($order);

		$result =  '
			<div class="adm-bus-table-container">
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
					<tbody>
						<tr'.(intval($data["USER_ID"]) > 0 ? ' style="display: none"': '' ).' id="sale-order-buyer-find-button-wrap">
							<td class="adm-detail-content-cell-l fwb" width="40%">
								&nbsp;
							</td>
							<td class="adm-detail-content-cell-r">
								<input type="button" name="FIND_BUYER" value="'.Loc::getMessage("SALE_ORDER_BUYER_FIND").'" onclick="BX.Sale.Admin.OrderBuyer.showChooseBuyerWindow(\''.LANGUAGE_ID.'\')"><br>
								<i>'.Loc::getMessage("SALE_ORDER_BUYER_START_TO_CREATE").':</i>
							</td>
						</tr>
						<tr'.(intval($data["USER_ID"]) <= 0 ? ' style="display: none"': '' ).' id="sale-order-buyer-name-wrap">
							<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage("SALE_ORDER_BUYER").':</td>
							<td class="adm-detail-content-cell-r">
								<div class="adm-s-order-person-choose">'.static::renderBuyerLink($data, ['id="BUYER_USER_NAME"']).'
									&nbsp;
									<a class="adm-s-bus-morelinkqhsw" onclick="BX.Sale.Admin.OrderBuyer.showChooseBuyerWindow(\''.LANGUAGE_ID.'\')" href="javascript:void(0);">
										'.Loc::getMessage("SALE_ORDER_BUYER_CHANGE").'
									</a>&nbsp;
									<a class="adm-s-bus-morelinkqhsw" onclick="BX.Sale.Admin.OrderBuyer.clearBuyer();" href="javascript:void(0);">
										'.Loc::getMessage("SALE_ORDER_BUYER_CLEAR").'
									</a>
									<input type="hidden" name="USER_ID" id="USER_ID" value="'.intval($data["USER_ID"]).'" onchange="BX.Sale.Admin.OrderBuyer.onBuyerIdChange(this);">
								</div>
							</td>
						</tr>
						<tr>
							<td class="adm-detail-content-cell-l fwb">'.Loc::getMessage("SALE_ORDER_BUYER_PAYER_TYPE").':</td>
							<td class="adm-detail-content-cell-r">'.
									\Bitrix\Sale\Helpers\Admin\OrderEdit::makeSelectHtml(
									"PERSON_TYPE_ID",
									self::getBuyerTypesList($order->getSiteId()),
									isset($data["PERSON_TYPE_ID"]) ? $data["PERSON_TYPE_ID"] : "",
									false,
									array(
										"class" => "adm-bus-select",
										"id" => "PERSON_TYPE_ID",
										"onchange" => "BX.Sale.Admin.OrderBuyer.onBuyerTypeChange(this.value);"
									)
								).
							'</td>
						</tr>
						<tr id="sale-order-buyer-profiles-list-row"'.($showProfiles ? '' : ' style="display:none;"').'>
							<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_BUYER_CHOOSE_PROFILE").':</td>
							<td class="adm-detail-content-cell-r"><div id="BUYER_PROFILE_ID_CONTAINER">';
		if($showProfiles)
		{
			$result .=	\Bitrix\Sale\Helpers\Admin\OrderEdit::makeSelectHtml(
							"BUYER_PROFILE_ID",
							self::getBuyerProfilesList($data["USER_ID"], $data["PERSON_TYPE_ID"]),
							$profileId,
							false,
							array(
								"class" => "adm-bus-select",
								"id" => "BUYER_PROFILE_ID",
								"onchange" => "BX.Sale.Admin.OrderBuyer.onBuyerProfileChange();"
							)
						);
		}

		$result .=			'</div></td>
						</tr>
					</tbody>
				</table>
			</div>'.
			'<div id="order_properties_container"></div>'.
			'<div>'.self::getOrderPropertiesByJS($order).'</div>'.
			'<div class="adm-bus-table-container caption border sale-order-props-group">'.
				'<div class="adm-bus-table-caption-title">'.Loc::getMessage("SALE_ORDER_BUYER_COMMENT").'</div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
					<tbody>
						<tr>
							<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage("SALE_ORDER_BUYER_ORDERCOMMENT").':</td>
							<td class="adm-detail-content-cell-r">
								<textarea style="width:400px;min-height:100px;" name="USER_DESCRIPTION" id="USER_DESCRIPTION">'.
									htmlspecialcharsbx($data["USER_DESCRIPTION"]).
								'</textarea>
							</td>
						</tr>
					</tbody>
				</table>
			</div>';

		return $result;
	}
	public static  function getPropsEdit(Sale\Order $order)
	{
		$result = '
				<div>
					<div id="order_properties_container_add"></div>'.
					'<div>'.self::getOrderRelPropertiesByJS($order).'</div>'.
				'</div>';

		return $result;
	}

	public static function getView(Sale\Order $order)
	{
		$data = self::prepareData($order);
		$buyersList = self::getBuyerTypesList($order->getSiteId());

		return '
			<div class="adm-bus-table-container">
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
					<tbody>
					<tr>
						<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage("SALE_ORDER_BUYER").':</td>
						<td class="adm-detail-content-cell-r">
							<div>'.static::renderBuyerLink($data).'</div>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_BUYER_PAYER_TYPE").':</td>
						<td class="adm-detail-content-cell-r">'.htmlspecialcharsbx($buyersList[$data["PERSON_TYPE_ID"]]).
						'</td>
					</tr>
					</tbody>
				</table>
			</div>'.
		self::getOrderPropertiesHtml($order, true).
		'<div class="adm-bus-table-container caption border sale-order-props-group">'.
		'<div class="adm-bus-table-caption-title">'.Loc::getMessage("SALE_ORDER_BUYER_COMMENT").'</div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
					<tbody>
						<tr>
							<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage("SALE_ORDER_BUYER_ORDERCOMMENT").':</td>
							<td class="adm-detail-content-cell-r"><p id="sale-adm-user-description-view" style="color:gray; max-width:800px; overflow:auto;">'.($data["USER_DESCRIPTION"] <> '' ? nl2br(htmlspecialcharsbx($data["USER_DESCRIPTION"])) : Loc::getMessage("SALE_ORDER_BUYER_NO")).'</p></td>
						</tr>
					</tbody>
				</table>
			</div>';

	}

	protected static function renderBuyerLink($data, $attr=[])
	{
		return '<a href="'.$data["BUYER_URL"].'" '.(count($attr)>0 ? implode(' ', $attr):"").'>'.htmlspecialcharsbx($data["BUYER_USER_NAME"]).'</a>'; die;
	}

	public static function getScripts()
	{
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_buyer.js");
		\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/sale/input.js');
		\Bitrix\Sale\PropertyValueCollection::initJs();
		$langPhrases = array("SALE_ORDER_BUYER_CREATE_NEW", "SALE_ORDER_BUYER_UNKNOWN_GROUP");

		$result = '<script>'.
			'BX.Sale.Admin.OrderBuyer.isFeatureSaleAccountsEnabled = '.(\CBXFeatures::IsFeatureEnabled('SaleAccounts') ? 'true' : 'false').';';

		foreach($langPhrases as $phrase)
			$result .= ' BX.message({'.$phrase.': "'.\CUtil::jsEscape(Loc::getMessage($phrase)).'"});';

		$result .= '
				BX.ready(function(){
					BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters( BX.Sale.Admin.OrderBuyer.getFieldsUpdaters() );
				});
			</script>
		';

		return $result;
	}

	public static function getDefaultPersonType($siteId)
	{
		$personTypes = self::getBuyerTypesList($siteId);
		reset($personTypes);
		return key($personTypes);
	}

	public static function 	prepareData(Sale\Order $order)
	{
		if (\CBXFeatures::IsFeatureEnabled('SaleAccounts'))
		{
			$strBuyerProfileUrl = '/bitrix/admin/sale_buyers_profile.php?USER_ID='.intval($order->getUserId()).'&lang='.LANGUAGE_ID;
		}
		else
		{
			$strBuyerProfileUrl = '/bitrix/admin/user_edit.php?ID='.$order->getUserId().'&lang='.LANGUAGE_ID;
		}

		$result = array(
			"USER_ID" => intval($order->getUserId()),
			"PERSON_TYPE_ID" => $order->getPersonTypeId(),
			"BUYER_USER_NAME" => OrderEdit::getUserName(
				$order->getUserId(),
				$order->getSiteId()
			),
			"USER_DESCRIPTION" => $order->getField("USER_DESCRIPTION"),
			"BUYER_URL" => $strBuyerProfileUrl
		);

		return $result;
	}

	public static function getBuyerTypesList($siteId)
	{
		static $result = array();

		if(!isset($result[$siteId]))
		{
			$result[$siteId] = array();
			$dbPersonType = \CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array("ACTIVE" => "Y", "LID"=> $siteId));

			while ($personType = $dbPersonType->Fetch())
				$result[$siteId][$personType["ID"]] = $personType["NAME"]." [".$personType["ID"]."]";
		}

		return $result[$siteId];
	}

	public static function getProfileParams($userId, $profileId)
	{
		return OrderUserProperties::getProfileValues($profileId);
	}

	public static function getUserProfiles($userId, $personTypeId = null)
	{
		if(intval($userId) <=0)
			return array();

		$result = array();
		$profilesResult = OrderUserProperties::loadProfiles($userId, $personTypeId);
		$profiles = $profilesResult->getData();

		if(is_array($profiles))
		{
			foreach($profiles as $typeId => $types)
			{
				if(!isset($result[$typeId]))
					$result[$typeId] = array();

				foreach($types as $key => $value)
				{
					$result[$typeId][$key] = $value["VALUES"];
				}
			}
		}

		if($personTypeId && empty($result[$personTypeId]))
		{
			$result[$personTypeId] = array(self::getProfileValuesFromPrevOrder($userId, $personTypeId));

			if(empty($result[$personTypeId]))
				$result[$personTypeId] = array(self::getProfileValuesFromUser($userId, $personTypeId));
		}

		return $result;
	}

	protected static function getProfileValuesFromPrevOrder($userId, $personTypeId)
	{
		if(intval($personTypeId) <= 0)
			throw new ArgumentNullException('userId');

		if(intval($personTypeId) <= 0)
			throw new ArgumentNullException('personTypeId');

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$res = $orderClass::getList(array(
			'filter' => array(
				'USER_ID' => $userId
			),
			'order' => array('DATE_INSERT' => 'DESC'),
			'select' => array('ID')
		));

		if(!$order = $res->fetch())
		{
			return array();
		}

		/** @var \Bitrix\Sale\Order $order */
		$order = $orderClass::load($order['ID']);

		if(!$order)
			return array();

		$propCollection = $order->getPropertyCollection();

		if(!$propCollection)
			return array();

		$result = array();

		$pRes = OrderPropsTable::getList(array(
			'filter' => array(
				'PERSON_TYPE_ID' => $personTypeId,
				'ACTIVE' => 'Y',
				'USER_PROPS' => 'Y'
			)
		));

		while($prop = $pRes->fetch())
		{
			if($prop['DEFAULT_VALUE'] <> '')
			{
				$result[$prop['ID']] = $prop['DEFAULT_VALUE'];
			}
			else
			{
				$property = null;

				if($prop['IS_EMAIL'] == 'Y')
					$property = $propCollection->getUserEmail();
				elseif($prop['IS_PAYER'] == 'Y')
					$property = $propCollection->getPayerName();
				elseif($prop['IS_PHONE'] == 'Y')
					$property = $propCollection->getPhone();
				elseif($prop['IS_ADDRESS'] == 'Y')
					$property = $propCollection->getAddress();

				if($property)
					$result[$prop['ID']] = $property->getValue();
			}
		}

		return $result;
	}

	protected static function getProfileValuesFromUser($userId, $personTypeId)
	{
		if(intval($personTypeId) <= 0)
			throw new ArgumentNullException('userId');

		if(intval($personTypeId) <= 0)
			throw new ArgumentNullException('personTypeId');

		$uRes = UserTable::getById($userId);

		if(!$user= $uRes->fetch())
			return array();

		$result = array();

		$pRes = OrderPropsTable::getList(array(
			'filter' => array(
				'PERSON_TYPE_ID' => $personTypeId,
				'ACTIVE' => 'Y',
				'USER_PROPS' => 'Y'
			)
		));

		while($prop = $pRes->fetch())
		{
			if($prop['DEFAULT_VALUE'] <> '')
			{
				$result[$prop['ID']] = $prop['DEFAULT_VALUE'];

			}
			elseif($prop['IS_EMAIL'] == 'Y' && !empty($user['EMAIL']))
			{
				$result[$prop['ID']] = $user['EMAIL'];
			}
			elseif($prop['IS_PAYER'] == 'Y')
			{
				$name = '';

				if(!empty($user['LAST_NAME']))
					$name .= $user['LAST_NAME'];

				if(!empty($user['NAME']))
					$name .= $user['NAME'];

				if(!empty($user['SECOND_NAME']))
					$name .= $user['SECOND_NAME'];

				if($name <> '')
					$result[$prop['ID']] = $name;
			}
			elseif($prop['IS_PHONE'] == 'Y' && !empty($user['PERSONAL_MOBILE']))
			{
				$result[$prop['ID']] = $user['PERSONAL_MOBILE'];
			}
			elseif($prop['IS_ADDRESS'] == 'Y')
			{
				$address = '';

				if(!empty($user['PERSONAL_STREET']))
					$address .= $user['PERSONAL_STREET'];

				if(!empty($user['PERSONAL_CITY']))
					$address .= $user['PERSONAL_CITY'];

				if(!empty($user['PERSONAL_STATE']))
					$address .= $user['PERSONAL_STATE'];

				if(!empty($user['PERSONAL_ZIP']))
					$address .= $user['PERSONAL_ZIP'];

				if(!empty($user['PERSONAL_COUNTRY']))
					$address .= $user['PERSONAL_COUNTRY'];

				$result[$prop['ID']] = $address;
			}
		}

		return $result;
	}

	public static function getBuyerProfilesList($userId, $personTypeId = null)
	{
		$result = array(0 => Loc::getMessage("SALE_ORDER_BUYER_CREATE_NEW"));

		if(intval($userId) > 0)
		{
			$profilesResult = OrderUserProperties::loadProfiles($userId, $personTypeId);
			$profiles = $profilesResult->getData();

			if(is_array($profiles))
				foreach($profiles as $types)
					foreach($types as $key => $value)
						$result[$key] = htmlspecialcharsback($value["NAME"]);
		}

		return $result;
	}

	public static function getOrderPropertiesHtml(Sale\Order $order, $readonly = false)
	{
		$propertyCollection = $order->getPropertyCollection();
		$result = "";

		foreach ($propertyCollection->getGroups() as $group)
		{
			$resultBody = "";

			$groupProperties = $propertyCollection->getPropertiesByGroupId($group['ID']);

			if(!is_array($groupProperties))
				continue;

			/** @var \Bitrix\Sale\PropertyValue $property */
			foreach ($propertyCollection->getPropertiesByGroupId($group['ID']) as $property)
			{
				$propertyValue = $property->getValue();

				if ($readonly
					&& (
						!isset($propertyValue)
						|| (is_array($propertyValue) && empty($propertyValue))
						|| $propertyValue === ""
					)
				)
					continue;

				$p = $property->getProperty();

				if($p['IS_PHONE'] == 'Y' && $readonly)
				{
					$phoneVal = $property->getValue();

					if($phoneVal != '')
					{
						if(!is_array($phoneVal))
							$phoneVal = array($phoneVal);

						$showHtml = '';

						foreach($phoneVal as $number)
						{
							$number = str_replace("'", "", htmlspecialcharsbx($number));

							if($showHtml <> '')
								$showHtml .= ', ';

							$showHtml .= '<a href="javascript:void(0)" onclick="BX.Sale.Admin.OrderEditPage.desktopMakeCall(\''.$number.'\');">'.
								$number.
								'</a>';
						}
					}
					else
					{
						$showHtml = '';
					}
				}
				else
				{
					$showHtml = (($readonly) ? $property->getViewHtml() : $property->getEditHtml());
				}

				$resultBody .= '
					<tr>
						<td class="adm-detail-content-cell-l" width="40%" valign="top">'.htmlspecialcharsbx($property->getName()).':</td>
						<td class="adm-detail-content-cell-r"><div>'.$showHtml.'</div></td>
					</tr>';
			}

			if (!empty($resultBody))
			{
				$result .= '<div class="adm-bus-table-container caption border sale-order-props-group">
					<div class="adm-bus-table-caption-title">'.htmlspecialcharsbx($group['NAME']).'</div>
					<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
						<tbody>'.$resultBody.'
						</tbody>
					</table>
				</div>';
			}

		}

		return $result;
	}

	public static function getRelPropData(Sale\Order $order)
	{
		$result = array();
		$groups = array();

		$items = $order->getPropertyCollection()->getArray();
		foreach ($items as $key => $item)
		{
			if ($key == 'properties')
			{
				$result[$key] = array();
				foreach ($item as $property)
				{
					if (!empty($property['RELATION']))
					{
						if ($property['TYPE'] === 'ENUM' && is_array($property['OPTIONS']))
						{
							$property['OPTIONS_SORT'] = array_keys($property['OPTIONS']);
						}
						$result[$key][] = $property;
						$groups[$property['PROPS_GROUP_ID']] = true;
					}
				}
			}
			else
			{
				$result[$key] = $item;
			}
		}

		foreach ($result['groups'] as $i => $group)
		{
			if (!isset($groups[$group['ID']]))
				unset($result['groups'][$i]);
		}

		return $result;
	}

	public static function getNotRelPropData(Sale\Order $order)
	{
		$result = array();
		$groups = array();

		$items = $order->getPropertyCollection()->getArray();
		foreach ($items as $key => $item)
		{
			if ($key == 'properties')
			{
				$result[$key] = array();
				foreach ($item as $property)
				{
					if (empty($property['RELATION']))
					{
						$result[$key][] = $property;
						$groups[$property['PROPS_GROUP_ID']] = true;
					}
				}
			}
			else
			{
				$result[$key] = $item;
			}
		}

		foreach ($result['groups'] as $i => $group)
		{
			if (!isset($groups[$group['ID']]) && $group['ID'] != 0)
				unset($result['groups'][$i]);
		}

		return $result;
	}

	public static function getOrderPropertiesByJS(Sale\Order $order)
	{
		if ($order->getId() > 0)
			$result = $order->getPropertyCollection()->getArray();
		else
			$result = self::getNotRelPropData($order);

		if (!empty($result['properties']))
		{
			$propertyTypes = Input\Manager::getTypes();
			$baseTypes = array();
			foreach ($propertyTypes as $typeName => $typeData)
			{
				if (mb_strpos($typeData['CLASS'], 'Bitrix\\Sale\\Internals\\Input') !== false)
					$baseTypes[] = $typeName;
			}

			foreach ($result['properties'] as &$property)
			{
				$propertyClassName = $propertyTypes[$property['TYPE']]['CLASS'];

				if (
					!in_array($property['TYPE'], $baseTypes)
					&& class_exists($propertyClassName)
					&& new $propertyClassName instanceof Input\Base
				)
				{
					ob_start();
					$propertyCustomName = "PROPERTIES[".$property['ID']."]";
					/** @var Input\Base $propertyClassName */
					echo $propertyClassName::getEditHtml($propertyCustomName, $property, $property['VALUE']);
					$property['EDIT_HTML'] = ob_get_contents();
					ob_end_clean();
					$property['TYPE'] = 'UF';
				}

				if ($property['TYPE'] === 'ENUM' && is_array($property['OPTIONS']))
				{
					$property['OPTIONS_SORT'] = array_keys($property['OPTIONS']);
				}
			}
		}

		if (!empty($result['groups']) && !empty($result['properties']))
		{
			$groupIndexList = array();
			foreach ($result['groups'] as $groupdData)
			{
				$groupIndexList[] = intval($groupdData['ID']);
			}

			if (!empty($groupIndexList))
			{
				foreach ($result['properties'] as $index => $propertyData)
				{
					if (array_key_exists('PROPS_GROUP_ID', $propertyData))
					{
						if (!in_array($propertyData['PROPS_GROUP_ID'], $groupIndexList))
						{
							$result['properties'][$index]['PROPS_GROUP_ID'] = 0;
						}
					}
				}
			}

		}

		return '
			<script>
				BX.ready(function(){
					BX.Sale.Admin.OrderBuyer.setOrderPropsArray('.\CUtil::PhpToJSObject($result).');
				});
			</script>
		';
	}

	public static function getOrderRelPropertiesByJS(Sale\Order $order)
	{
		$result = self::getRelPropData($order);

		return '
			<script>
				BX.ready(function(){
					BX.Sale.Admin.OrderBuyer.setOrderRelPropsArray('.\CUtil::PhpToJSObject($result).');

					var updater = [];
					updater["RELATED_PROPS"] = {
						callback: BX.Sale.Admin.OrderBuyer.setOrderRelPropsArray,
						context: this
					};

					BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters(updater);
				});
			</script>
		';
	}
}