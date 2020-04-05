<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Order;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$lang = ($request->get('lang') !== null) ? trim($request->get('lang')) : "ru";
\Bitrix\Main\Context::getCurrent()->setLanguage($lang);

Loc::loadMessages(__FILE__);

$arResult = array("ERROR" => "");

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = "Error! Can't include module \"Sale\"";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/internals/input.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($arResult["ERROR"] === '' && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
	$action = ($request->get('action') !== null) ? trim($request->get('action')): '';

	switch ($action)
	{
		case "get_restriction_params_html":
			/** @var Bitrix\Sale\Services\Base\Restriction $className */
			$className = ($request->get('className') !== null) ? trim($request->get('className')): '';
			$params = ($request->get('params') !== null) ? $request->get('params') : array();
			$cashboxId = ($request->get('cashboxId') !== null) ? intval($request->get('cashboxId')) : 0;
			$sort = ($request->get('sort') !== null) ? intval($request->get('sort')) : 100;

			if(!$className)
				throw new \Bitrix\Main\ArgumentNullException("className");

			Cashbox\Restrictions\Manager::getClassesList();
			$paramsStructure = $className::getParamsStructure($cashboxId);
			$params = $className::prepareParamsValues($params, $cashboxId);

			$paramsField = "<table>";

			foreach ($paramsStructure as $name => $param)
			{
				$paramsField .= "<tr>".
					"<td>".(strlen($param["LABEL"]) > 0 ? $param["LABEL"].": " : "")."</td>".
					"<td>".\Bitrix\Sale\Internals\Input\Manager::getEditHtml("RESTRICTION[".$name."]", $param, (isset($params[$name]) ? $params[$name] : null))."</td>".
					"</tr>";
			}

			$paramsField .= '<tr>'.
				'<td>'.Loc::getMessage("SALE_CASHBOX_RESTRICTION_SORT").': </td>'.
				'<td><input type="text" name="SORT" value="'.$sort.'"></td>'.
				'</tr>';

			$arResult["RESTRICTION_HTML"] = $paramsField."</table>";
			break;

		case "save_restriction":
			Cashbox\Restrictions\Manager::getClassesList();

			/** @var Bitrix\Sale\Services\Base\Restriction $className */
			$className = ($request->get('className') !== null) ? trim($request->get('className')): '';
			$params = ($request->get('params') !== null) ? $request->get('params') : array();
			$sort = ($request->get('sort') !== null) ? (int)$request->get('sort') : 100;
			$cashboxId = ($request->get('cashboxId') !== null) ? (int)$request->get('cashboxId') : 0;
			$restrictionId = ($request->get('restrictionId') !== null) ? (int)$request->get('restrictionId') : 0;

			if(!class_exists($className))
				throw new \Bitrix\Main\ArgumentNullException("className");

			if(!$cashboxId)
				throw new \Bitrix\Main\ArgumentNullException("cashboxId");

			foreach ($className::getParamsStructure() as $key => $rParams)
			{
				$errors = \Bitrix\Sale\Internals\Input\Manager::getError($rParams, $params[$key]);
				if (!empty($errors))
					$arResult["ERROR"] .= Loc::getMessage('SALE_CASHBOX_ERROR_FIELD').': "'.$rParams["LABEL"].'" '.implode("\n", $errors)."\n";
			}

			if (!$params)
				$arResult["ERROR"] = Loc::getMessage('SALE_CASHBOX_ERROR_PARAMS');

			if ($arResult["ERROR"] == '')
			{
				$fields = array(
					"SERVICE_ID" => $cashboxId,
					"SERVICE_TYPE" => Cashbox\Restrictions\Manager::SERVICE_TYPE_CASHBOX,
					"SORT" => $sort,
					"PARAMS" => $params
				);

				/** @var \Bitrix\Sale\Result $res */
				$res = $className::save($fields, $restrictionId);

				if (!$res->isSuccess())
					$arResult["ERROR"] .= implode(".", $res->getErrorMessages());
				$arResult["HTML"] = getRestrictionHtml($cashboxId);
			}

			break;

		case "delete_restriction":
			Cashbox\Restrictions\Manager::getClassesList();
			$restrictionId = ($request->get('restrictionId') !== null) ? (int)$request->get('restrictionId') : 0;
			$cashboxId = ($request->get('cashboxId') !== null) ? (int)$request->get('cashboxId') : 0;

			if(!$restrictionId)
				throw new \Bitrix\Main\ArgumentNullException('restrictionId');

			$dbRes =  Cashbox\Restrictions\Manager::getById($restrictionId);

			if($fields = $dbRes->fetch())
			{
				/** @var \Bitrix\Sale\Result $res */
				$res = $fields["CLASS_NAME"]::delete($restrictionId, $cashboxId);

				if(!$res->isSuccess())
					$arResult["ERROR"] .= implode(".", $res->getErrorMessages());
			}
			else
			{
				$arResult["ERROR"] .= "Can't find restriction with id: ".$restrictionId;
			}

			$arResult["HTML"] = getRestrictionHtml($cashboxId);

			break;
		case "generate_link":
			$arResult["LINK"] = Cashbox\Manager::getConnectionLink();
			break;		
		case "reload_settings":
			$cashbox = array('HANDLER' => $request->get('handler'), 'KKM_ID' => $request->get('kkmId'));
			$handler = $cashbox['HANDLER'];
			if (class_exists($handler))
			{
				ob_start();
				require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/sale/admin/cashbox_settings.php");
				$arResult["HTML"] = ob_get_contents();
				ob_end_clean();

				$arResult['GENERAL_REQUIRED_FIELDS'] = $handler::getGeneralRequiredFields();

				$kkmList = $cashbox['HANDLER']::getSupportedKkmModels();
				if ($kkmList)
				{
					$requiredClass = '';
					if (isset($arResult['GENERAL_REQUIRED_FIELDS']['KKM_ID']))
						$requiredClass = 'class="adm-required-field"';

					$arResult["MODEL_HTML"] = '<tr id="tr_KKM_ID">
							<td width="40%" class="adm-detail-content-cell-l"><span '.$requiredClass.'>'.Loc::getMessage("SALE_CASHBOX_KKM_ID").'</span>:</td>
							<td width="60%" class="adm-detail-content-cell-r">
								<select name="KKM_ID" id="KKM_ID" onchange="BX.Sale.Cashbox.reloadSettings()">
									<option value="">'.Loc::getMessage('SALE_CASHBOX_KKM_NO_CHOOSE').'</option>';

					foreach ($kkmList as $code => $kkm)
					{
						$selected = ($code === $cashbox['KKM_ID']) ? 'selected' : '';
						$arResult["MODEL_HTML"] .= '<option value="'.$code.'" '.$selected.'>'.htmlspecialcharsbx($kkm['NAME']).'</option>';
					}

					$arResult["MODEL_HTML"] .= '</select></td></tr>';
				}
			}

			break;
		case "reload_ofd_settings":
			$cashbox = array('OFD' => $request->get('handler'));
			$handler = $cashbox['OFD'];

			ob_start();
			require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/sale/admin/cashbox_ofd_settings.php");
			$arResult["HTML"] = ob_get_contents();
			ob_end_clean();

			break;
		case "get_order_entities":
			global $USER, $APPLICATION;

			$formData = $request->get('formData');
			$orderId = $formData['data']['ORDER_ID'];
			if ($orderId > 0)
			{
				$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

				$order = Order::load($orderId);
				if ($order === null)
				{
					$arResult["ERROR"] = "Error! Access denied";
					break;
				}

				$userId = $USER->GetID();
				$userCompanyList = \Bitrix\Sale\Services\Company\Manager::getUserCompanyList($userId);

				if ($saleModulePermissions == 'P')
				{
					if ($order->getUserId() !== $userId && !in_array($order->getField("COMPANY_ID"), $userCompanyList))
					{
						$arResult["ERROR"] = "Error! Access denied";
						break;
					}
				}

				$paymentCollection = $order->getPaymentCollection();
				/** @var Payment $payment */
				foreach ($paymentCollection as $payment)
				{
					$arResult['PAYMENTS'][] = array(
						'ID' => $payment->getId(),
						'CODE' => 'P_'.$payment->getId()
					);
				}

				if (Cashbox\Manager::isSupportedFFD105())
				{
					$shipmentCollection = $order->getShipmentCollection();
					/** @var \Bitrix\Sale\Shipment $shipment */
					foreach ($shipmentCollection as $shipment)
					{
						if ($shipment->isSystem())
							continue;

						$arResult['SHIPMENTS'][] = array(
							'ID' => $shipment->getId(),
							'CODE' => 'S_'.$shipment->getId(),
						);
					}
				}
			}

			break;
		case "get_data_for_check":
			global $USER, $APPLICATION;

			$formData = $request->get('formData');
			$orderId = $formData['data']['ORDER_ID'];
			$entityCode = $formData['data']['ENTITY_CODE'];
			list($entityType, $entityId) = explode('_', $entityCode);
			$entityType = $entityType === 'S' ? Cashbox\Check::SUPPORTED_ENTITY_TYPE_SHIPMENT : Cashbox\Check::SUPPORTED_ENTITY_TYPE_PAYMENT;

			if ($orderId > 0)
			{
				$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

				$order = Order::load($orderId);
				if ($order === null)
				{
					$arResult["ERROR"] = "Order not found";
					break;
				}

				$userId = $USER->GetID();
				$userCompanyList = \Bitrix\Sale\Services\Company\Manager::getUserCompanyList($userId);

				if ($saleModulePermissions == 'P')
				{
					if ($order->getUserId() !== $userId && !in_array($order->getField("COMPANY_ID"), $userCompanyList))
					{
						$arResult["ERROR"] = "Error! Access denied";
						break;
					}
				}

				$typeList = Cashbox\CheckManager::getCheckTypeMap();
				/** @var Cashbox\Check $typeClass */
				foreach ($typeList as $id => $typeClass)
				{
					if (
						$typeClass::getSupportedEntityType() === $entityType ||
						$typeClass::getSupportedEntityType() === Cashbox\Check::SUPPORTED_ENTITY_TYPE_ALL
					)
					{
						if (class_exists($typeClass))
							$arResult['CHECK_TYPES'][] = array("ID" => $id, "NAME" => $typeClass::getName());
					}
				}

				$checkType = $arResult['CHECK_TYPES'][0]['ID'];
				if ($entityType === Cashbox\Check::SUPPORTED_ENTITY_TYPE_PAYMENT)
				{
					$relatedEntities = Cashbox\CheckManager::getRelatedEntitiesForPayment($checkType, $entityId);
				}
				else
				{
					$relatedEntities = Cashbox\CheckManager::getRelatedEntitiesForShipment($checkType, $entityId);
				}

				$arResult = $arResult + $relatedEntities;

				$arResult['FFD_105_ENABLED'] = Cashbox\Manager::isSupportedFFD105();
			}

			break;
		case 'get_related_entities':
			$entityCode = $request->get('entityCode');
			$checkType = $request->get('checkType');
			list($entityType, $entityId) = explode('_', $entityCode);
			if ($entityType === 'S')
			{
				$arResult = Cashbox\CheckManager::getRelatedEntitiesForShipment($checkType, $entityId);
			}
			else
			{
				$arResult = Cashbox\CheckManager::getRelatedEntitiesForPayment($checkType, $entityId);
			}

			$arResult['FFD_105_ENABLED'] = Cashbox\Manager::isSupportedFFD105();

			break;
		case "add_check":
			global $APPLICATION, $USER;

			$formData = $request->get('formData');
			$typeId = $formData['data']['CHECK_TYPE'];
			$orderId = (int)$formData['data']['ORDER_ID'];
			$paymentData = $formData['data']['PAYMENTS'];
			$shipmentData = $formData['data']['SHIPMENTS'];
			$entityCode = $formData['data']['ENTITY_CODE'];
			list($entityType, $entityId) = explode('_', $entityCode);

			$order = Order::load($orderId);

			$userId = $USER->GetID();
			$userCompanyList = \Bitrix\Sale\Services\Company\Manager::getUserCompanyList($userId);

			if ($saleModulePermissions == 'P')
			{
				if ($order->getUserId() !== $userId && !in_array($order->getField("COMPANY_ID"), $userCompanyList))
				{
					$arResult["ERROR"] = "Error! Access denied";
					break;
				}
			}

			$paymentCollection = $order->getPaymentCollection();
			$entities = array();
			if ($entityType === 'P')
			{
				$entities[] = $paymentCollection->getItemById($entityId);
			}

			$shipmentCollection = $order->getShipmentCollection();
			if ($entityType === 'S')
			{
				$entities[] = $shipmentCollection->getItemById($entityId);
			}

			$relatedEntities = array();
			if ($paymentData)
			{
				foreach ($paymentData as $id => $data)
				{
					$relatedEntities[$data['TYPE']][] = $paymentCollection->getItemById($id);
				}
			}

			if ($shipmentData)
			{
				foreach ($shipmentData as $id => $data)
				{
					$relatedEntities[Cashbox\Check::SHIPMENT_TYPE_NONE][] = $shipmentCollection->getItemById($id);
				}
			}

			if (!Cashbox\Manager::isSupportedFFD105())
			{
				foreach ($relatedEntities as $type => $entityList)
				{
					foreach ($entityList as $item)
					{
						$entities[] = $item;
					}
				}

				$relatedEntities = array();
			}

			$addResult = Cashbox\CheckManager::addByType($entities, $typeId, $relatedEntities);
			if (!$addResult->isSuccess())
				$arResult["ERROR"] = implode("\n", $addResult->getErrorMessages());

			break;
		default:
			$arResult["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if ($request->get('mode') == 'settings')
		getRestrictionHtml($request->get('ID'));
	elseif(strlen($arResult["ERROR"]) <= 0)
		$arResult["ERROR"] = "Error! Access denied";
}

if(strlen($arResult["ERROR"]) > 0)
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));

function getRestrictionHtml($cashboxId)
{
	if(intval($cashboxId) <= 0)
		throw new \Bitrix\Main\ArgumentNullException("cashboxId");

	$_REQUEST['table_id'] = 'table_cashbox_restrictions';
	$_REQUEST['admin_history'] = 'Y';
	$_GET['ID'] = $cashboxId;

	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/cashbox_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();

	return $restrictionsHtml;
}