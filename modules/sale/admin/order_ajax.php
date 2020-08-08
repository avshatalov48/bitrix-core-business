<?
namespace Bitrix\Sale\AdminPage;

/**
 * Bitrix Framework
 * @global \CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Web;
use Bitrix\Sale;
use Bitrix\Sale\Result;
use Bitrix\Sale\Provider;
use Bitrix\Sale\Helpers\Admin;
use Bitrix\Main\SystemException;
use Bitrix\Main\Entity\EntityError;
use Bitrix\Sale\UserMessageException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\Services\Company;
use Bitrix\Sale\Cashbox;
use Bitrix\Currency\CurrencyManager;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);
define("ADMIN_AJAX_MODE", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

global $USER;
$arResult = array();
$result = new \Bitrix\Main\Result();
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if(!isset($_REQUEST["action"]))
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED")));
	$result->setData(array("SYSTEM_ERROR" => "REQUEST[action] not defined!"));
}
elseif($saleModulePermissions == "D" || !check_bitrix_sessid())
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED2")));
	$result->setData(array("SYSTEM_ERROR" => "Access denied!"));
}
elseif(!\Bitrix\Main\Loader::includeModule('sale'))
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED")));
	$result->setData(array("SYSTEM_ERROR" => "Error! Can't include module \"Sale\"!"));
}
else
{
	if($result->isSuccess())
	{
		$processor = new AjaxProcessor($USER->GetID(), $_REQUEST);
		$result = $processor->processRequest();
	}
}

if($result->isSuccess())
{
	$arResult["RESULT"] = "OK";
}
else
{
	$arResult["RESULT"] = "ERROR";
	$arResult["ERROR"] = '';
	$arResult["ERRORS"] = array();

	foreach($result->getErrorMessages() as $error)
	{
		if(!in_array($error, $arResult["ERRORS"]))
		{
			$arResult["ERRORS"][] = $error;

			if($arResult["ERROR"] <> '')
			{
				$arResult["ERROR"] .= "\n";
			}

			$arResult["ERROR"] .= $error;
		}
	}
}

if($result instanceof Result)
{
	if ($result->hasWarnings())
	{
		$arResult["WARNING"] = implode("\n", $result->getWarningMessages());
		$arResult["WARNINGS"] = array();

		foreach ($result->getWarningMessages() as $warning)
		{
			$arResult["WARNINGS"][] = $warning;
		}
	}

	$needConfirm = ($result->get('NEED_CONFIRM') === true);

	if ($needConfirm)
	{
		if ($result->get('CONFIRM_TITLE') != '')
		{
			$arResult["CONFIRM_TITLE"] = $result->get('CONFIRM_TITLE');
		}

		if ($result->get('CONFIRM_MESSAGE') != '')
		{
			$arResult["CONFIRM_MESSAGE"] = $result->get('CONFIRM_MESSAGE');
		}
	}
}

$data = $result->getData();

if(is_array($data))
{
	$arResult = array_merge($arResult, $data);
}
unset($data);

$arResult = AjaxProcessor::convertEncodingArray($arResult, SITE_CHARSET, 'UTF-8');

$APPLICATION->RestartBuffer();

header('Content-Type: application/json');
echo json_encode($arResult);
\CMain::FinalActions();
die();

/**
 * Class AjaxProcessor
 * @package Bitrix\Sale\AdminPage
 * Class helper for processing ajax requests
 */
class AjaxProcessor
{
	protected $userId;
	/** @var \Bitrix\Sale\Result $result*/
	protected $result;
	protected $request;
	/** @var \Bitrix\Sale\Order $order  */
	protected $order = null;
	protected $formDataChanged = false;

	protected $registry = null;

	public function __construct($userId, array $request)
	{
		$this->userId = $userId;
		$this->result = new Result();
		$this->request = $request;
		$this->registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
	}

	/**
	 * @return Result
	 * @throws SystemException
	 */
	public function processRequest()
	{
		if(!isset($this->request['action']))
			throw new SystemException("Undefined \"action\"");

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

		global $APPLICATION;

		if(mb_strtolower(SITE_CHARSET) != 'utf-8')
			$this->request = $APPLICATION->ConvertCharsetArray($this->request, 'utf-8', SITE_CHARSET);

		try
		{
			call_user_func(
				array($this, $this->request['action']."Action")
			);

			/* Caution!
			 * You must update $this->request by fresh data,
			 * or you will refresh and receive old data!
			 */
			if(
				isset($this->request["refreshOrderData"])
				&& $this->request["refreshOrderData"] == "Y"
				&& $this->request['action'] != "refreshOrderData"
			)
				$this->refreshOrderDataAction();
		}
		catch(UserMessageException $e)
		{
			$this->addResultError($e->getMessage());
		}

		return $this->result;
	}

	/**
	 * @param $message
	 */
	public function addResultError($message)
	{
		$this->result->addError(new EntityError($message));
	}

	/**
	 * @param $message
	 */
	public function addResultWarning($message)
	{
		$this->result->addWarning(new Sale\ResultWarning($message));
	}

	protected function addResultData($dataKey, $data)
	{
		if($dataKey == '')
			$this->result->addData($data);
		else
			$this->result->addData(array($dataKey => $data));
	}

	/* * * * * * requests actions handlers * * * * * * * * */

	protected function getProductIdBySkuPropsAction()
	{
		if(!$this->request["skuProps"] || !is_array($this->request["skuProps"])) throw new ArgumentNullException("skuProps");
		if(!$this->request["productId"] || intval($this->request["productId"]) <= 0) throw new ArgumentNullException("productId");
		if(!$this->request["iBlockId"] || intval($this->request["iBlockId"]) <= 0) throw new ArgumentNullException("iBlockId");
		if(!$this->request["skuOrder"] || !is_array($this->request["skuOrder"])) throw new ArgumentNullException("skuOrder");
		if(!$this->request["changedSkuId"] || intval($this->request["changedSkuId"]) <= 0) throw new ArgumentNullException("changedSkuId");

		$offerId = Admin\SkuProps::getProductId(
			$this->request["skuProps"],
			$this->request["productId"],
			$this->request["skuOrder"],
			$this->request["changedSkuId"]
		);

		$this->addResultData("OFFER_ID", $offerId);
	}

	protected function addProductToBasketAction()
	{
		global $APPLICATION, $USER;

		if(!$this->request["formData"]) throw new ArgumentNullException("formatData");
		//if(!$this->request["quantity"]) throw new ArgumentNullException("quantity");
		if(!$this->request["productId"]) throw new ArgumentNullException("productId");
		$orderId = (int)$this->request["formData"]['ID'];
		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P' && $orderId > 0)
		{
			$isUserResponsible = false;
			$isAllowCompany = false;


			/** @var Sale\Order $orderClass */
			$orderClass = $this->registry->getOrderClassName();

			$resOrder = $orderClass::getList(array(
				'filter' => array(
					'=ID' => $orderId,
				),
				'select' => array(
					'RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'
				)
			));

			if ($orderData = $resOrder->fetch())
			{
				/** @var Sale\OrderStatus $orderClass */
				$orderStatusClass = $this->registry->getOrderStatusClassName();

				$allowedStatusesView = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowView = in_array($orderData['STATUS_ID'], $allowedStatusesView);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowView)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_BASKET_ITEM_ADD'));
			}
		}

		$productId = isset($this->request['productId']) ? intval($this->request['productId']) : 0;
		$quantity = isset($this->request['quantity']) ? floatval($this->request['quantity']) : 1;
		$columns = isset($this->request['columns']) ? $this->request['columns'] : array();
		$customPrice = isset($this->request['customPrice']) ? $this->request['customPrice'] : false;
		$siteId = isset($this->request["formData"]["SITE_ID"]) ? $this->request["formData"]["SITE_ID"] : SITE_ID;
		$userId = intval($this->request["formData"]["USER_ID"]);

		$alreadyInBasketCode = "";
		$productParams = array();

		if(isset($this->request["formData"]["PRODUCT"]) && is_array($this->request["formData"]["PRODUCT"]))
		{
			foreach($this->request["formData"]["PRODUCT"] as $basketCode => &$params)
			{
				if(!isset($params["MODULE"]) || $params["MODULE"] != "catalog")
					continue;

				if(!isset($params["OFFER_ID"]) || $params["OFFER_ID"] != $productId)
					continue;

				$params["QUANTITY"] += $quantity;
				$this->request["ADD_QUANTITY_INSTEAD_ONLY"] = "Y";
				$alreadyInBasketCode = $basketCode;
				$productParams = $params;
				break;
			}
		}

		Admin\OrderEdit::initCouponsData($userId, $orderId);

		if(empty($productParams))
		{
			$productParams = Admin\Blocks\OrderBasket::getProductsData(
				array($productId),
				$siteId,
				$columns,
				$userId
			);

			$productParams[$productId]["QUANTITY"] = $quantity;
			$productParams[$productId]["JUST_ADDED"] = 'Y';
			$providerData = \Bitrix\Sale\Helpers\Admin\Product::getProviderData($productParams, $siteId, $userId);
			$productParams = $productParams[$productId];

			if(!empty($providerData))
			{
				$productParams = array_merge($productParams, current($providerData));
				$productParams["PROVIDER_DATA"] = serialize(current($providerData));
			}

			if($customPrice !== false)
			{
				$productParams["CUSTOM_PRICE"] = "Y";
				$productParams["PRICE"] = $customPrice;
			}
		}

		if(
			isset($this->request["replaceBasketCode"])
			&& $this->request["replaceBasketCode"] <> ''
			&& isset($this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]])
		)
		{
			$this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]] = $productParams;
			$this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]]["REPLACED"] = "Y";

			if($alreadyInBasketCode <> '')
			{
				unset($this->request["formData"]["PRODUCT"][$alreadyInBasketCode]);
				$this->request["formData"]["ALREADY_IN_BASKET_CODE"] = $alreadyInBasketCode;
			}
		}
		elseif($alreadyInBasketCode == '')
		{
			$this->request["formData"]["PRODUCT"][Admin\OrderEdit::BASKET_CODE_NEW] = $productParams;
		}

		$this->formDataChanged = true;
	}

	protected function cancelOrderAction()
	{
		global $APPLICATION, $USER;
		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;
		$canceled = isset($this->request['canceled']) ? $this->request['canceled'] : "N";
		$comment = isset($this->request['comment']) ? trim($this->request['comment']) : "";
		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		$errors = array();

		if(!\CSaleOrder::CanUserCancelOrder($orderId, $USER->GetUserGroupArray(), $this->userId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_CANCEL_ORDER'));

		/** @var Sale\Order $saleOrder*/
		if(!$saleOrder = $orderClass::load($orderId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			/** @var Sale\OrderStatus $orderStatusClass */
			$orderStatusClass = $this->registry->getOrderStatusClassName();

			$isUserResponsible = false;
			$isAllowCompany = false;
			$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($saleOrder->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($saleOrder->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($saleOrder->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_ORDER_CANCEL'));
			}
		}

		$state = $saleOrder->getField("CANCELED");

		if($state != $canceled)
			throw new UserMessageException(
				$state == "Y" ? Loc::getMessage('SALE_OA_ERROR_CANCEL_ORDER_ALREADY') : Loc::getMessage('SALE_OA_ERROR_CANCEL_ORDER_NOT_YET')
			);

		/** @var \Bitrix\Sale\Result $res */
		$res = $saleOrder->setField("CANCELED", $canceled == "Y" ? "N" : "Y");

		if (!$res->isSuccess())
		{
			$errors = array_merge($errors, $res->getErrorMessages());
		}

		if (!$errors)
		{
			$saleOrder->setField("REASON_CANCELED", $canceled == "N" ? $comment : "");

			$res = $saleOrder->save();
			if ($res->isSuccess())
			{
				Sale\Provider::resetTrustData($saleOrder->getSiteId());
			}
			else
			{
				$errors = array_merge($errors, $res->getErrorMessages());
			}

			$canceled = $saleOrder->getField("CANCELED");
			$this->addResultData("CANCELED", $canceled);

			if ($canceled == "Y")
			{
				$userInfo = Admin\Blocks\OrderStatus::getUserInfo($saleOrder->getField("EMP_CANCELED_ID"));
				$this->addResultData("DATE_CANCELED", $saleOrder->getField("DATE_CANCELED")->toString());
				$this->addResultData("EMP_CANCELED_ID", $saleOrder->getField("EMP_CANCELED_ID"));
				$this->addResultData("EMP_CANCELED_NAME", $userInfo["NAME"]." (".$userInfo["LOGIN"].")");
			}
		}

		if (!empty($errors))
		{
			throw new UserMessageException(implode("<br>\n", $errors));
		}
	}

	protected function saveCommentsAction()
	{
		global $APPLICATION, $USER;
		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new SystemException("Wrong order id!");

		if(!isset($this->request['comments']))
			throw new SystemException("Can't find the comments content!");

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$isAllowUpdate = false;

			$resOrder = $orderClass::getList([
				'select' => ['RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'],
				'filter' => ['=ID' => intval($this->request["orderId"])],
			]);
			if ($orderData = $resOrder->fetch())
			{
				/** @var Sale\OrderStatus $orderStatusClass */
				$orderStatusClass = $this->registry->getOrderStatusClassName();
				$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowUpdate = in_array($orderData['STATUS_ID'], $allowedStatusesUpdate);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_ORDER_ADD_COMMENTS'));
			}
		}

		$order = $orderClass::load((int)$this->request['orderId']);
		if (!$order)
		{
			throw new SystemException("Wrong order id!");
		}
		$order->setField("COMMENTS", $this->request['comments']);
		$res = $order->save();

		if(!$res->isSuccess())
			$this->addResultError(join("\n", $res->getErrorMessages()));

		$CBXSanitizer = new \CBXSanitizer;
		$CBXSanitizer->SetLevel(\CBXSanitizer::SECURE_LEVEL_MIDDLE);
		$this->addResultData("COMMENTS", $CBXSanitizer->SanitizeHtml($this->request['comments']));
	}

	protected function saveStatusAction()
	{
		global $USER, $APPLICATION;
		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new SystemException("Wrong order id!");

		if(!isset($this->request['statusId']) || $this->request['statusId'] == '')
			throw new SystemException("Wrong status id!");

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		/** @var Sale\Order $order */
		$order = $orderClass::load($this->request['orderId']);

		if (!$order)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": \"".$this->request['orderId']."\"");

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{

			$isUserResponsible = false;
			$isAllowCompany = false;

			$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_ORDER_STATUS_CHANGE'));
			}
		}

		$statusesList = $orderStatusClass::getAllowedUserStatuses(
			$this->userId,
			$order->getField('STATUS_ID')
		);

		if(array_key_exists($this->request['statusId'], $statusesList))
		{
			$res = $order->setField("STATUS_ID", $this->request['statusId']);

			if(!$res->isSuccess())
				throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));

			$res = $order->save();

			if($res->isSuccess())
			{
				Sale\Provider::resetTrustData($order->getSiteId());
			}
			else
			{
				throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));
			}
		}
	}

	protected function getOrderFieldsAction()
	{
		if(!isset($this->request['demandFields']) || !array($this->request['demandFields']) || empty($this->request['demandFields']))
			throw new SystemException("Demand fields is empty!");

		$this->addResultData(
			"RESULT_FIELDS",
			$this->getDemandedFields(
				$this->request['demandFields'],
				$this->request['givenFields']
			)
		);
	}

	protected function refreshOrderDataAction()
	{
		global $APPLICATION, $USER;
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$additional = isset($this->request["additional"]) ? $this->request["additional"] : array();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		//delete product from basket
		if(!empty($additional["operation"]) && $additional["operation"] == "PRODUCT_DELETE" && !empty($additional["basketCode"]))
		{
			if(!empty($formData['PRODUCT'][$additional["basketCode"]]))
				unset($formData['PRODUCT'][$additional["basketCode"]]);
		}

		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = null;

		/** @var \Bitrix\Sale\Payment $payment */
		$payment = null;
		$opResults = new Result();

		//Use or not data from form and don't refresh data from provider
		Admin\OrderEdit::$isTrustProductFormData = (!empty($additional["operation"]) && $additional["operation"] == "DATA_ACTUALIZE") ? false : true;

		$order = $this->getOrder($formData, $opResults);
		$isStartField = $order->isStartField();

		if($order->getId() > 0)
		{
			$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

			if ($saleModulePermissions == 'P')
			{
				$isUserResponsible = false;
				$isAllowCompany = false;

				$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

				if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
				{
					throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_ORDER_REFRESH'));
				}
			}

			$order = Admin\OrderEdit::editOrderByFormData($formData, $order, $this->userId, false, array(), $opResults);

			if(!$order)
			{
				if(!$opResults->isSuccess())
				{
					$this->addFilteredErrors($opResults);
					return;
				}
			}
		}

		if ($order->getId() <= 0)
		{
			if(isset($formData['SHIPMENT']) && is_array($formData['SHIPMENT']))
			{
				$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
				$res->getErrorMessages();
				$data = $res->getData();
				$shipment = array_shift($data['SHIPMENT']);
				if (!$shipment->isCustomPrice())
				{
					$calcResult = Admin\Blocks\OrderShipment::calculateDeliveryPrice($shipment);
					if ($calcResult->isSuccess())
						$shipment->setField('BASE_PRICE_DELIVERY', $calcResult->getPrice());
				}
			}

			if(isset($formData['PAYMENT']) && is_array($formData['PAYMENT']))
			{
				$res = Admin\Blocks\OrderPayment::updateData($order, $formData['PAYMENT'], true);
				$res->getErrorMessages();
				$data = $res->getData();
				$payment = array_shift($data['PAYMENT']);
			}

			Admin\OrderEdit::fillOrderProperties($order, $formData);
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $order->hasMeaningfulField();

			/** @var Result $r */
			$r = $order->doFinalAction($hasMeaningfulFields);
		}

		$result['PAYABLE'] = $order->getPrice() - $order->getSumPaid();
		$result["BASE_PRICE"] = Admin\Blocks\OrderBasket::getBasePrice($order);

		$data = $this->result->getData();
		if ($shipment)
		{
			$calcResult = Admin\Blocks\OrderShipment::calculateDeliveryPrice($shipment);
			if ($calcResult->isSuccess())
			{
				if ($shipment->isCustomPrice())
					$result["CALCULATED_PRICE"] = $calcResult->getPrice();
				else
					$shipment->setField("BASE_PRICE_DELIVERY", $calcResult->getPrice());
			}
			elseif (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
			{
				$result['DELIVERY_ERROR'] = implode("\n", $calcResult->getErrorMessages());
			}

			if (!isset($data['SHIPMENT_DATA']['DELIVERY_WEIGHT']))
			{
				$result['DELIVERY_WEIGHT'] = $shipment->getWeight() / Admin\Blocks\OrderShipment::getWeightKoef($order->getSiteId());
			}

			$result["CALCULATED_WEIGHT"] = $shipment->getShipmentItemCollection()->getWeight() / Admin\Blocks\OrderShipment::getWeightKoef($order->getSiteId());

			if (!isset($data['SHIPMENT_DATA']['DELIVERY_SERVICE_LIST']))
			{
				$deliveryService = Admin\Blocks\OrderShipment::getDeliveryServiceList();
				$deliveryServiceTree = Admin\Blocks\OrderShipment::makeDeliveryServiceTree($deliveryService);
				$result['DELIVERY_SERVICE_LIST'] = Admin\Blocks\OrderShipment::getTemplate($deliveryServiceTree);
				if (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
				{
					foreach ($deliveryService as $delivery)
					{
						if ($shipment->getDeliveryId() == $delivery['ID'] && $delivery['RESTRICTED'] != Sale\Services\PaySystem\Restrictions\Manager::SEVERITY_NONE)
							$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
					}
				}
			}
			if (!isset($data['SHIPMENT_DATA']['PROFILES']))
			{
				if ($shipment->getDeliveryId())
				{
					$service = Sale\Delivery\Services\Manager::getObjectById($shipment->getDeliveryId());

					if($service)
					{
						$parentService = $service->getParentService();
						if ($parentService && $parentService->canHasProfiles())
						{
							$profiles = Admin\Blocks\OrderShipment::getDeliveryServiceProfiles($parentService->getId());
							$profiles = Admin\Blocks\OrderShipment::checkProfilesRestriction($profiles, $shipment);
							$result["PROFILES"] = Admin\Blocks\OrderShipment::getProfileEditControl($profiles);
							if (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
							{
								foreach ($profiles as $profile)
								{
									if ($shipment->getDeliveryId() == $profile['ID'] && $profile['RESTRICTED'] == Sale\Delivery\Restrictions\Manager::SEVERITY_SOFT)
										$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
								}
							}
						}
					}
				}
			}

			if (!isset($data['SHIPMENT_DATA']['EXTRA_SERVICES']))
			{
				$extraServiceManager = new Sale\Delivery\ExtraServices\Manager($shipment->getDeliveryId(), $order->getCurrency());
				$deliveryExtraService = $shipment->getExtraServices();

				if ($deliveryExtraService)
					$extraServiceManager->setValues($deliveryExtraService);

				$extraService = $extraServiceManager->getItems();

				if ($extraService)
					$result["EXTRA_SERVICES"] = Admin\Blocks\OrderShipment::getExtraServiceEditControl($extraService, 1, false, $shipment);
			}

			$companies = Company\Manager::getListWithRestrictions($shipment, Company\Restrictions\Manager::MODE_MANAGER);
			$result['SHIPMENT_COMPANY_ID'] = Admin\OrderEdit::makeSelectHtmlBodyWithRestricted($companies, $shipment->getField('COMPANY_ID'));
		}

		if ($payment)
		{
			$paySystemList = Admin\Blocks\OrderPayment::getPaySystemList($payment);

			if (isset($paySystemList[$payment->getPaymentSystemId()]['RESTRICTED']))
				$result['PAYSYSTEM_ERROR'] = Loc::getMessage('SALE_OA_ERROR_PAYSYSTEM_SERVICE');

			$result['PAY_SYSTEM_LIST'] = Admin\OrderEdit::makeSelectHtmlBodyWithRestricted($paySystemList, '', false);
			$result['PRICE_COD'] = $this->updatePriceCodAction($payment);

			$companies = Company\Manager::getListWithRestrictions($payment, Company\Restrictions\Manager::MODE_MANAGER);
			$result['PAYMENT_COMPANY_ID'] = Admin\OrderEdit::makeSelectHtmlBodyWithRestricted($companies, $payment->getField('COMPANY_ID'));
		}
		$orderBasket = new Admin\Blocks\OrderBasket($order, "", $this->request["formData"]["BASKET_PREFIX"]);
		$basketPrepareParams = array();

		if((
			!empty($additional["operation"]) && $additional["operation"] == "PRODUCT_ADD")
			|| ($this->request["action"] == "addProductToBasket"
				&& (!isset($this->request["ADD_QUANTITY_INSTEAD_ONLY"])
					|| $this->request["ADD_QUANTITY_INSTEAD_ONLY"] != "Y"
				)
			)
		)
		{
			$basketPrepareParams["SKIP_SKU_INFO"] = false;
			$basketPrepareParams["ADDED_PRODUCTS"] = array($this->request["productId"]);
		}
		else
		{
			$basketPrepareParams["SKIP_SKU_INFO"] = true;
			$basketPrepareParams["ADDED_PRODUCTS"] = array();
		}

		$result["BASKET"] = $orderBasket->prepareData($basketPrepareParams);
		$result["BASKET"]["LIGHT"] = "Y";
		// collect info about changed fields
		if($basketPrepareParams["SKIP_SKU_INFO"] && !empty($formData["PRODUCT"]) && is_array($formData["PRODUCT"]))
		{
			//prices
			$result["BASKET"]["PRICES_UPDATED"] = array();
			$errors = array();
			$PRECISE = 0.005;

			foreach($formData["PRODUCT"] as $basketCode => $itemParams)
			{
				if($basketCode == Admin\OrderEdit::BASKET_CODE_NEW)
					continue;

				if(!isset($result["BASKET"]["ITEMS"][$basketCode]["PRICE"]) || !isset($itemParams["PRICE"]))
				{
					$errors[] = "Product price with basket code \"".$basketCode."\" not found.";
					continue;
				}

				if(abs(floatval($result["BASKET"]["ITEMS"][$basketCode]["PRICE"]) - floatval($itemParams["PRICE"])) >= $PRECISE)
					$result["BASKET"]["PRICES_UPDATED"][$basketCode] = $result["BASKET"]["ITEMS"][$basketCode]["PRICE"];
			}

			if(!empty($errors))
				$this->addResultData("ERROR_PRICE_COMPARING", $errors);

		}

		$resData = $opResults->getData();

		if(!empty($resData["NEW_ITEM_BASKET_CODE"]))
			$result["BASKET"]["NEW_ITEM_BASKET_CODE"] = $resData["NEW_ITEM_BASKET_CODE"];

		$result['RELATED_PROPS'] = Admin\Blocks\OrderBuyer::getRelPropData($order);
		if ($result['RELATED_PROPS'])
		{
			$profileId = isset($formData["BUYER_PROFILE_ID"]) ? intval($formData["BUYER_PROFILE_ID"]) : 0;
			$profile = Admin\Blocks\OrderBuyer::getProfileParams($order->getUserId(), $profileId);
			if ($result['RELATED_PROPS']['properties'])
			{
				foreach ($result['RELATED_PROPS']['properties'] as $key => $property)
				{
					if (!isset($formData['PROPERTIES'][$property['ID']]))
					{
						if ($property['TYPE'] === 'ENUM')
						{
							$result['RELATED_PROPS']['properties'][$key]['VALUE'] = explode(',', $profile[$property['ID']]);
						}
						else
						{
							$result['RELATED_PROPS']['properties'][$key]['VALUE'] = array($profile[$property['ID']]);
						}
					}
				}
			}
		}

		$result["DISCOUNTS_LIST"] = Admin\OrderEdit::getOrderedDiscounts($order, false);

		if ($order->getBasket())
			$result['BASE_PRICE_DELIVERY'] = $result["DISCOUNTS_LIST"]['PRICES']['DELIVERY']['BASE_PRICE'];
		else
			$result['BASE_PRICE_DELIVERY'] = $order->getDeliveryPrice();

		$result['BASE_PRICE_DELIVERY'] = Sale\PriceMaths::roundByFormatCurrency($result['BASE_PRICE_DELIVERY'], $order->getCurrency());
		$result['DELIVERY_PRICE_DISCOUNT'] = Sale\PriceMaths::roundByFormatCurrency($result["DISCOUNTS_LIST"]['PRICES']['DELIVERY']['PRICE'], $order->getCurrency());
		$result["COUPONS_LIST"] = Admin\OrderEdit::getCouponList($order, false);
		$result["TOTAL_PRICES"] = Admin\OrderEdit::getTotalPrices($order, $orderBasket, false);
		$result["DELIVERY_DISCOUNT"] = $result["TOTAL_PRICES"]["DELIVERY_DISCOUNT"];

		$result = array_merge($result, $order->getFieldValues());

		if(!isset($result["PRICE"]))
			$result["PRICE"] = 0;

		/* DEMANDED */
		if(isset($additional["demandFields"]) && is_array($additional["demandFields"]))
		{
			if(isset($additional["givenFields"]) && is_array($additional["givenFields"]))
				$result=array_merge($result, $additional["givenFields"]);

			$demanded = $this->getDemandedFields($additional["demandFields"], $result, $order);
			$result = array_merge($result, $demanded);
		}

		$this->addFilteredErrors($opResults);
		$this->addResultData("ORDER_DATA", $result);
	}

	/* We don't show all errors during forming order via ajax requests */
	protected function addFilteredErrors(Result $opResults)
	{
		if(!$opResults->isSuccess())
		{
			foreach($opResults->getErrors() as $error)
			{
				if($error->getCode() == "CATALOG_QUANTITY_NOT_ENOGH"
					|| $error->getCode() == "SALE_ORDER_SYSTEM_SHIPMENT_LESS_QUANTITY"
					|| $error->getCode() == "CATALOG_NO_QUANTITY_PRODUCT"
					|| $error->getCode() == "SALE_SHIPMENT_SYSTEM_QUANTITY_ERROR"
					|| $error->getCode() == "SALE_BASKET_AVAILABLE_QUANTITY"
					|| $error->getCode() == "SALE_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY"
					|| $error->getCode() == "SALE_BASKET_ITEM_REMOVE_IMPOSSIBLE_BECAUSE_SHIPPED"
					|| $error->getCode() == "SALE_ORDEREDIT_ERROR_CHANGE_USER_WITH_PAID_PAYMENTS"
				)
					$this->addResultError($error->getMessage());
			}
		}
	}

	protected function changeResponsibleUserAction()
	{
		if(!isset($this->request['userId']) || intval($this->request['userId']) <= 0)
			throw new ArgumentNullException("userId");

		$siteId = $this->request['siteId'] <> '' ? $this->request['siteId'] : "";
		global $USER;

		$dateResponsible = new \Bitrix\Main\Type\DateTime();
		$this->addResultData("RESPONSIBLE", Admin\OrderEdit::getUserName($this->request['userId'], $siteId));
		$this->addResultData("EMP_RESPONSIBLE", Admin\OrderEdit::getUserName($USER->GetID(), $siteId));
		$this->addResultData("DATE_RESPONSIBLE", $dateResponsible->toString());
	}

	protected function updatePaymentStatusAction()
	{
		global $APPLICATION, $USER;

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new ArgumentNullException("orderId");

		if(!isset($this->request['paymentId']) || intval($this->request['paymentId']) <= 0)
			throw new ArgumentNullException("paymentId");

		$strict = (isset($this->request['strict']) && $this->request['strict'] == true);

		$fields = array();
		$orderStatusId = '';
		/** @var \Bitrix\Sale\Order $order */
		$order = $orderClass::load($this->request['orderId']);

		/** @var \Bitrix\Sale\Payment $payment */
		$payment = $order->getPaymentCollection()->getItemById($this->request['paymentId']);
		$hasErrors = false;

		$setPaymentFieldsResult = new Result();

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
				$payment->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($payment->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_PAYMENT_STATUS_CHANGE'));
			}
		}

		if ($strict)
		{
			Sale\Internals\Catalog\Provider::setIgnoreErrors(true);
		}

		if ($this->request['method'] == 'save')
		{
			$res = $payment->setPaid('Y');
			if ($strict)
			{
				$res = $this->removeErrorsForConfirmation($res);
			}

			if (!$res->isSuccess())
			{
				$res = $this->convertErrorsToConfirmation($res);

				$setResultMessage = $res->getErrorMessages();
				if (!empty($setResultMessage))
				{
					$this->addResultError(join("\n", $setResultMessage));
				}
				elseif ($res->hasWarnings())
				{
					$existSpecialErrors = $res->get('NEED_CONFIRM');
					if ($existSpecialErrors)
					{
						$this->addResultData('NEED_CONFIRM', $existSpecialErrors);
						$this->addResultData('CONFIRM_TITLE', Loc::getMessage('SALE_ORDER_SHIP_SHIPMENT_NOTICE_TITLE'));
						$this->addResultData('CONFIRM_MESSAGE', Loc::getMessage('SALE_ORDER_SHIP_SHIPMENT_NOTICE_MESSAGE'));
					}

					$this->addResultWarning(join("\n", $res->getWarningMessages()));
				}

				$hasErrors = true;

			}
			elseif ($res->hasWarnings())
			{
				$this->addResultWarning(join("\n", $res->getWarningMessages()));
			}

			if (!$hasErrors)
			{
				foreach ($this->request['data'] as $key => $value)
				{
					$newKey = mb_substr($key, 0, mb_strripos($key, '_'));
					if (mb_strpos($newKey, 'PAY_VOUCHER') !== false)
						$fields[$newKey] = $value;
					if ($newKey == 'ORDER_STATUS_ID')
						$orderStatusId = $value;
				}
				try
				{
					$fields['PAY_VOUCHER_DATE'] = new \Bitrix\Main\Type\Date($fields['PAY_VOUCHER_DATE']);
				}
				catch (ObjectException $exception)
				{
					$this->addResultError(Loc::getMessage('SALE_OA_ERROR_INCORRECT_DATE'));
					return;
				}

				$setPaymentFieldsResult = $this->setPaymentFields($payment, $fields, $strict);
			}
		}
		else
		{
			foreach ($this->request['data'] as $key => $value)
			{
				$newKey = mb_substr($key, 0, mb_strripos($key, '_'));
				if (mb_strpos($newKey, 'PAY_RETURN') !== false)
					$fields[$newKey] = $value;
			}

			try
			{
				$fields['PAY_RETURN_DATE'] = new Date($fields['PAY_RETURN_DATE']);
			}
			catch (ObjectException $exception)
			{
				$this->addResultError(Loc::getMessage('SALE_OA_ERROR_INCORRECT_DATE'));
				return;
			}

			$payReturnOperationId = $fields['PAY_RETURN_OPERATION_ID'] ?? null;
			if ($payReturnOperationId)
			{
				unset($fields['PAY_RETURN_OPERATION_ID']);
			}

			$setPaymentFieldsResult = $this->setPaymentFields($payment, $fields, $strict);
			if ($setPaymentFieldsResult->isSuccess())
			{
				if ($payReturnOperationId)
				{
					/** @var Result $refResult */
					$refResult = $payment->setReturn($payReturnOperationId);
					if (!$refResult->isSuccess())
					{
						$this->addResultError(implode("\n", $refResult->getErrorMessages()));
						return;
					}
					elseif ($refResult->hasWarnings())
					{
						$this->addResultWarning(implode("\n", $refResult->getWarningMessages()));
						return;
					}
				}
				else
				{
					$res = $payment->setPaid('N');
					if (!$res->isSuccess())
					{
						$this->addResultError(implode("\n", $res->getErrorMessages()));
					}
					elseif ($res->hasWarnings())
					{
						$this->addResultWarning(implode("\n", $res->getWarningMessages()));
					}
				}
			}
		}

		if (!$hasErrors)
		{
			if ($setPaymentFieldsResult->isSuccess())
			{
				if (!empty($orderStatusId))
				{
					if ($USER && $USER->isAuthorized())
						$statusesList = $orderStatusClass::getAllowedUserStatuses($USER->getID(), $order->getField('STATUS_ID'));
					else
						$statusesList = $orderStatusClass::getAllStatuses();

					if ($order->getField('STATUS_ID') != $orderStatusId && array_key_exists($orderStatusId, $statusesList))
					{
						/** @var Result $res */
						$res = $order->setField('STATUS_ID', $orderStatusId);
						if (!$res->isSuccess())
						{
							$this->addResultError(join("\n", $res->getErrorMessages()));
							return;
						}
					}
				}

				$result = $order->save();
				if ($strict)
				{
					$result = $this->removeErrorsForConfirmation($result);
				}

				if ($result->isSuccess())
				{
					Sale\Provider::resetTrustData($order->getSiteId());
					$preparedData = Admin\Blocks\OrderFinanceInfo::prepareData($order);
					$preparedData["PAYMENT_PAID_".$payment->getId()] = $payment->isPaid() ? "Y" : "N";

					$shipmentCollection = $order->getShipmentCollection();
					if ($shipmentCollection)
					{
						/** @var \Bitrix\Sale\Shipment $shipment */
						foreach ($shipmentCollection as $shipment)
						{
							if (!$shipment->isSystem())
							{
								$preparedData['DEDUCTED_'.$shipment->getId()] = $shipment->getField('DEDUCTED');
								$preparedData['ALLOW_DELIVERY_'.$shipment->getId()] = $shipment->getField('ALLOW_DELIVERY');

								$preparedStatusList = array();
								$statusList = Admin\Blocks\OrderShipmentStatus::getShipmentStatusList($shipment->getField('STATUS_ID'));
								foreach ($statusList as $id => $name)
								{
									if ($shipment->getField('STATUS_ID') === $id)
										continue;

									$preparedStatusList[] = array(
										'ID' => $id,
										'NAME' => htmlspecialcharsbx($name)
									);
								}

								$preparedData['SHIPMENT_STATUS_LIST_'.$shipment->getId()] = $preparedStatusList;
								$preparedData['SHIPMENT_STATUS_'.$shipment->getId()] = array('id' => $shipment->getField('STATUS_ID'), 'name' => htmlspecialcharsbx($statusList[$shipment->getField('STATUS_ID')]));
							}
						}
					}

					if ($result->hasWarnings())
					{
						$this->addResultWarning(join("\n", $result->getWarningMessages()));
					}

					$this->addResultData(
						"RESULT",
						$preparedData
					);

					$markerListHtml = Admin\Blocks\OrderMarker::getView($order->getId());

					if (!empty($markerListHtml))
					{
						$this->addResultData('MARKERS', $markerListHtml);
					}
				}
				else
				{
					$this->addResultError(join("\n", $result->getErrorMessages()));
					return;
				}
			}
			else
			{
				$setPaymentFieldsResult = $this->convertErrorsToConfirmation($setPaymentFieldsResult);

				$setResultMessage = $setPaymentFieldsResult->getErrorMessages();
				if (!empty($setResultMessage))
				{
					$this->addResultError(join("\n", $setResultMessage));
				}
				elseif ($setPaymentFieldsResult->hasWarnings())
				{
					$existSpecialErrors = $setPaymentFieldsResult->get('NEED_CONFIRM');
					if ($existSpecialErrors)
					{
						$this->addResultData('NEED_CONFIRM', $existSpecialErrors);
						$this->addResultData('CONFIRM_TITLE', Loc::getMessage('SALE_ORDER_SHIP_SHIPMENT_NOTICE_TITLE'));
						$this->addResultData('CONFIRM_MESSAGE', Loc::getMessage('SALE_ORDER_SHIP_SHIPMENT_NOTICE_MESSAGE'));
					}

					$this->addResultWarning(join("\n", $setPaymentFieldsResult->getWarningMessages()));
				}
			}
		}

		if ($strict)
		{
			Sale\Internals\Catalog\Provider::setIgnoreErrors(false);
		}
	}

	/**
	 * @param Sale\Payment $payment
	 * @param $fields
	 * @param $strict
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	private function setPaymentFields(Sale\Payment $payment , $fields, $strict): Result
	{
		$setFieldsResult = $payment->setFields($fields);
		if ($strict)
		{
			$setFieldsResult = $this->removeErrorsForConfirmation($setFieldsResult);
		}

		return $setFieldsResult;
	}

	protected function deletePaymentAction()
	{
		global $USER, $APPLICATION;
		$orderId = $this->request['orderId'];
		$paymentId = $this->request['paymentId'];

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		if ($orderId <= 0 || $paymentId <=0)
			throw new ArgumentNullException("paymentId or orderId");

		/** @var Sale\Order $order */
		$order = $orderClass::load($orderId);

		if (!$order)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->getItemById($paymentId);

		if (!$payment)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_PAYMENT').": ".$paymentId);

		$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
		if(!in_array($order->getField("STATUS_ID"), $allowedStatusesUpdate))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_DELETE_PAYMENT_PERMISSION').': '.$paymentId);
		}

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());


			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
				$payment->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($payment->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_PAYMENT_DELETE'));
			}
		}

		$delResult = $payment->delete();

		if ($delResult->isSuccess())
		{
			$result = $order->save();
			if ($result->isSuccess())
			{
				Sale\Provider::resetTrustData($order->getSiteId());
				$this->addResultData("RESULT", "OK");
			}
			else
			{
				throw new UserMessageException(join("\n", $result->getErrorMessages()));
			}
		}
		else
		{
			throw new UserMessageException(join("\n", $delResult->getErrorMessages()));
		}
	}

	protected function deleteShipmentAction()
	{
		global $USER, $APPLICATION;
		$orderId = $this->request['order_id'];
		$shipmentId = $this->request['shipment_id'];

		if ($orderId <= 0 || $shipmentId <= 0)
			throw new UserMessageException('Error');

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		/** @var Sale\Order $order */
		$order = $orderClass::load($orderId);

		if (!$order)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$shipmentCollection = $order->getShipmentCollection();
		$shipmentItem = $shipmentCollection->getItemById($shipmentId);

		if (!$shipmentItem)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_SHIPMENT').': '.$shipmentId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());


			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
				$shipmentItem->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($shipmentItem->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($shipmentItem->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_SHIPMENT_DELETE'));
			}
		}

		/** @var Sale\DeliveryStatus $deliveryStatusClass */
		$deliveryStatusClass = $this->registry->getDeliveryStatusClassName();

		$allowedStatusesDelete = $deliveryStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('delete'));
		if(!in_array($shipmentItem->getField("STATUS_ID"), $allowedStatusesDelete))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_DELETE_SHIPMENT_PERMISSION').': '.$shipmentId);
		}

		$delResult = $shipmentItem->delete();

		if ($delResult->isSuccess())
		{
			$saveResult = $order->save();
			if ($saveResult->isSuccess())
			{
				Sale\Provider::resetTrustData($order->getSiteId());
				$result["DELIVERY_PRICE"] = $shipmentCollection->getBasePriceDelivery();
				$result["DELIVERY_PRICE_DISCOUNT"] = $shipmentCollection->getPriceDelivery();
				$result['PRICE'] = $order->getPrice();
				$result['PAYABLE'] = $result['PRICE'] - $order->getSumPaid();

				$orderBasket = new Admin\Blocks\OrderBasket($order);
				$result["TOTAL_PRICES"] = Admin\OrderEdit::getTotalPrices($order, $orderBasket, false);

				$this->addResultData("RESULT", $result);
			}
			elseif ($saveResult->hasWarnings())
			{
				$this->addResultWarning(join("\n", $saveResult->getWarningMessages()));
			}
			else
			{
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
			}
		}
		else
		{
			$this->addResultError(join("\n", $delResult->getErrorMessages()));
		}
	}

	protected function saveBasketVisibleColumnsAction()
	{
		$columns = isset($this->request['columns']) ? $this->request['columns'] : array();
		$idPrefix = isset($this->request['idPrefix']) ? $this->request['idPrefix'] : "";

		if(\CUserOptions::SetOption($idPrefix."order_basket_table", "table_columns", array("columns" => implode(",", $columns))))
			$this->addResultData("RESULT", "OK");
		else
			$this->addResultError("Can't save columns!");
	}

	protected function updateShipmentStatusAction()
	{
		global $USER, $APPLICATION;
		$shipmentId = $this->request['shipmentId'];
		$orderId = $this->request['orderId'];
		$field = $this->request['field'];
		$newStatus = $this->request['status'];
		$strict = (isset($this->request['strict']) && $this->request['strict'] == true);

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\DeliveryStatus $deliveryStatusClass */
		$deliveryStatusClass = $this->registry->getDeliveryStatusClassName();

		/** @var \Bitrix\Sale\Order $order */
		$order = $orderClass::load($orderId);

		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $order->getShipmentCollection()->getItemById($shipmentId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			if ($field === 'DEDUCTED')
			{
				$operation = 'deduction';
			}
			elseif ($field === 'ALLOW_DELIVERY')
			{
				$operation = 'delivery';
			}
			else
			{
				$operation = 'update';
			}
			$allowedStatusesUpdate = $deliveryStatusClass::getStatusesUserCanDoOperations($USER->GetID(), [$operation]);
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
				$shipment->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($shipment->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($shipment->getField('STATUS_ID'), $allowedStatusesUpdate);


			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_SHIPMENT_STATUS_CHANGE'));
			}
		}

		if ($strict)
		{
			Sale\Internals\Catalog\Provider::setIgnoreErrors(true);
		}

		$setResult = $shipment->setField($field, $newStatus);

		if ($setResult->isSuccess())
		{
			if ($strict)
			{
				$setResult = $this->removeErrorsForConfirmation($setResult);
			}

			if ($setResult->hasWarnings())
			{
				$this->addResultWarning(join("\n", $setResult->getWarningMessages()));
			}

			$saveResult = $order->save();
			if ($strict)
			{
				$saveResult = $this->removeErrorsForConfirmation($saveResult);
			}

			if ($saveResult->isSuccess())
			{
				Sale\Provider::resetTrustData($order->getSiteId());
			}
			else
			{
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
			}

			if ($saveResult->hasWarnings())
			{
				$this->addResultWarning(join("\n", $saveResult->getWarningMessages()));
			}


		}
		else
		{
			$setResult = $this->convertErrorsToConfirmation($setResult);

			$setResultMessage = $setResult->getErrorMessages();
			if (!empty($setResultMessage))
			{
				$this->addResultError(join("\n", $setResultMessage));
			}
			elseif ($setResult->hasWarnings())
			{
				$existSpecialErrors = $setResult->get('NEED_CONFIRM');
				if ($existSpecialErrors)
				{
					$this->addResultData('NEED_CONFIRM', $existSpecialErrors);
					$this->addResultData('CONFIRM_TITLE', Loc::getMessage('SALE_ORDER_SHIP_SHIPMENT_NOTICE_TITLE'));
					$this->addResultData('CONFIRM_MESSAGE', Loc::getMessage('SALE_ORDER_SHIP_SHIPMENT_NOTICE_MESSAGE'));
				}

				$this->addResultWarning(join("\n", $setResult->getWarningMessages()));
			}
		}

		if ($strict)
		{
			Sale\Internals\Catalog\Provider::setIgnoreErrors(false);
		}

		if($shipment)
		{
			$preparedStatusList = array();
			$statusList = Admin\Blocks\OrderShipmentStatus::getShipmentStatusList($shipment->getField('STATUS_ID'));
			foreach ($statusList as $id => $name)
			{
				if ($shipment->getField('STATUS_ID') === $id)
					continue;

				$preparedStatusList[] = array(
					'ID' => $id,
					'NAME' => htmlspecialcharsbx($name)
				);
			}

			$result = array(
				'STATUS_ID' => $order->getField('STATUS_ID'),
				'DEDUCTED_'.$shipment->getId() => $shipment->getField('DEDUCTED'),
				'ALLOW_DELIVERY_'.$shipment->getId() => $shipment->getField('ALLOW_DELIVERY'),
				'SHIPMENT_STATUS_LIST_'.$shipment->getId() => $preparedStatusList,
				'SHIPMENT_STATUS_'.$shipment->getId() => array('id' => $shipment->getField('STATUS_ID'), 'name' => htmlspecialcharsbx($statusList[$shipment->getField('STATUS_ID')]))
			);

			/** @var \Bitrix\Sale\ShipmentItem $item */
			$shipmentItemCollection = $shipment->getShipmentItemCollection();
			foreach ($shipmentItemCollection as $item)
			{
				$basketItem = $item->getBasketItem();
				if ($basketItem->getField('PRODUCT_PROVIDER_CLASS') === "\\Bitrix\\Sale\\ProviderAccountPay")
				{
					$userAccount = new \CSaleUserAccount();
					$res = $userAccount->getList(
						array(),
						array(
							'USER_ID' => $order->getUserId(),
							'CURRENCY' => $order->getCurrency(),
							'LOCKED' => 'N'
						),
						false,
						false,
						array(
							'CURRENT_BUDGET'
						)
					);

					if($userAccount = $res->Fetch())
						$result["BUYER_BUDGET"] = $userAccount['CURRENT_BUDGET'];
					else
						$result["BUYER_BUDGET"] = 0;

					break;
				}
			}

			$this->addResultData("RESULT", $result);

			$markerListHtml = Admin\Blocks\OrderMarker::getView($order->getId(), $shipment->getId());

			if (!empty($markerListHtml))
			{
				$this->addResultData('MARKERS', $markerListHtml);
			}
		}
	}

	protected function createNewPaymentAction()
	{
		global $APPLICATION, $USER;
		$formData = $this->request['formData'];
		$index = $this->request['index'];

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		$order = $this->getOrder($formData);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_PAYMENT_ADD'));
			}
		}


		if(isset($formData['SHIPMENT']) && is_array($formData['SHIPMENT']))
		{
			$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
			$res->getErrorMessages();
		}

		if(isset($formData['PAYMENT']) && is_array($formData['PAYMENT']))
		{
			$res = Admin\Blocks\OrderPayment::updateData($order, $formData['PAYMENT']);
			$res->getErrorMessages();
		}

		$payment = $order->getPaymentCollection()->createItem();
		$this->addResultData("PAYMENT", Sale\Helpers\Admin\Blocks\OrderPayment::getEdit($payment, $index));
	}

	protected function getProductEditDialogHtmlAction()
	{
		$currency = isset($this->request['currency']) ? $this->request['currency'] : array();
		$objName = isset($this->request['objName']) ? $this->request['objName'] : "";
		$this->addResultData(
			'DIALOG_CONTENT',
			Admin\Blocks\OrderBasket::getProductEditDialogHtml(
				$currency,
				$objName
			)
		);
	}

	protected function changeDeliveryServiceAction()
	{
		global $APPLICATION, $USER;
		$result = array();
		$profiles = array();
		$index = $this->request['index'];
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$confirmed = isset($this->request["confirmed"]) ? $this->request["confirmed"] : 'N';
		$formData['ID'] = $formData['order_id'];
		$deliveryId = intval($formData['SHIPMENT'][$index]['DELIVERY_ID']);

		if ($deliveryId <= 0)
			return;

		$shipmentId = intval($formData['SHIPMENT'][$index]['SHIPMENT_ID']);

		if($shipmentId > 0 && Sale\Delivery\Requests\Manager::isShipmentSent($shipmentId) && $confirmed != 'Y')
		{
			$requestId = Sale\Delivery\Requests\Manager::getRequestIdByShipmentId($shipmentId);
			$this->addResultData("NEED_CONFIRM", "Y");
			$this->addResultData(
				"CONFIRM",
				array(
					"TEXT" =>
						Loc::getMessage(
							'SALE_OA_SHIPMENT_DELIVERY_REQ_SENT',
							array(
								'#REQUEST_ID#' => Sale\Delivery\Requests\Helper::getRequestViewLink($requestId)

			))));
			return;
		}

		Admin\OrderEdit::$isTrustProductFormData = true;
		$order = $this->getOrder($formData);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P' && (isset($this->request["orderId"]) && intval($this->request["orderId"]) > 0))
		{
			/** @var Sale\OrderStatus $orderStatusClass */
			$orderStatusClass = $this->registry->getOrderStatusClassName();

			$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			/** @var Sale\Shipment $shipmentClass */
			$shipmentClass = $this->registry->getShipmentClassName();
			$resShipment = $shipmentClass::getList([
				 'select' => ['RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'],
				 'filter' => [
					 '=ORDER_ID' => intval($this->request["orderId"]),
					 '=ID' => $shipmentId,
				 ]
			]);
			if ($shipmentData = $resShipment->fetch())
			{
				if ($shipmentData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($shipmentData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}
			}

			$isAllowUpdate = in_array($shipmentData['STATUS_ID'], $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_SHIPMENT_DELIVERY_CHANGE'));
			}
		}


		/** @var  \Bitrix\Sale\Delivery\Services\Base $service */
		$service = Sale\Delivery\Services\Manager::getObjectById($deliveryId);
		if ($service && $service->canHasProfiles())
		{
			$profiles = Admin\Blocks\OrderShipment::getDeliveryServiceProfiles($deliveryId);
			if (!isset($formData['SHIPMENT'][$index]['PROFILE']))
			{
				reset($profiles);
				$initProfile = current($profiles);
				$deliveryId = $initProfile['ID'];
				$formData['SHIPMENT'][$index]['PROFILE'] = $initProfile['ID'];
				$this->request["formData"]['SHIPMENT'][$index]['PROFILE'] = $initProfile['ID'];
			}
			else
			{
				$deliveryId = $formData['SHIPMENT'][$index]['PROFILE'];
			}
		}

		$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
		$data = $res->getData();
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = array_shift($data['SHIPMENT']);

		if ($service->canHasProfiles())
		{
			$profiles = Admin\Blocks\OrderShipment::checkProfilesRestriction($profiles, $shipment);
			$result["PROFILES"] = Admin\Blocks\OrderShipment::getProfileEditControl($profiles, $index, $shipment->getDeliveryId());

			foreach ($profiles as $profile)
			{
				if ($formData['SHIPMENT'][$index]['PROFILE'] == $profile['ID'] && $profile['RESTRICTED'] == Sale\Delivery\Restrictions\Manager::SEVERITY_SOFT)
				{
					$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
					break;
				}
			}
		}

		$deliveryService = Admin\Blocks\OrderShipment::getDeliveryServiceList($shipment);
		$deliveryServiceTree = Admin\Blocks\OrderShipment::makeDeliveryServiceTree($deliveryService);
		$result['DELIVERY_SERVICE_LIST'] = Admin\Blocks\OrderShipment::getTemplate($deliveryServiceTree);

		foreach ($deliveryService as $delivery)
		{
			if ($deliveryId == $delivery['ID'] && $delivery['RESTRICTED'] != Sale\Delivery\Restrictions\Manager::SEVERITY_NONE)
			{
				$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
				break;
			}
		}

		$storeMap = Admin\Blocks\OrderShipment::getMap($shipment->getDeliveryId(), $index);
		if ($storeMap)
			$result['MAP'] = $storeMap;

		$extraServiceManager = new Sale\Delivery\ExtraServices\Manager($deliveryId);
		$extraServiceManager->setOperationCurrency($order->getCurrency());
		$deliveryExtraService = $shipment->getExtraServices();

		if ($deliveryExtraService)
			$extraServiceManager->setValues($deliveryExtraService);

		$extraService = $extraServiceManager->getItems();

		if ($extraService)
			$result["EXTRA_SERVICES"] = Admin\Blocks\OrderShipment::getExtraServiceEditControl($extraService, $index, false, $shipment);

		$calcResult = Admin\Blocks\OrderShipment::calculateDeliveryPrice($shipment);

		if ($calcResult->isSuccess())
		{
			$result["CALCULATED_PRICE"] = $calcResult->getPrice();
			if ($shipment->getField('CUSTOM_PRICE_DELIVERY') != 'Y')
			{
				$shipment->setField('PRICE_DELIVERY', $calcResult->getPrice());
				$this->request['formData']['SHIPMENT'][$index]['PRICE_DELIVERY'] = $calcResult->getPrice();
			}
		}
		else
		{
			$result['DELIVERY_ERROR'] = implode("\n", $calcResult->getErrorMessages());
		}

		$result["CALCULATED_WEIGHT"] = $shipment->getShipmentItemCollection()->getWeight() / Admin\Blocks\OrderShipment::getWeightKoef($order->getSiteId());
		$this->addResultData("SHIPMENT_DATA", $result);

		$this->formDataChanged = true;
	}

	protected function getDefaultDeliveryPriceAction()
	{
		global $APPLICATION, $USER;
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$formData['ID'] = $formData['order_id'];

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		$order = $this->getOrder($formData);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;

			$allowedStatusesView = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowView = in_array($order->getField('STATUS_ID'), $allowedStatusesView);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowView)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_SHIPMENT_GET_DEFAULT_DELIVERY_PRICE'));
			}
		}

		$result = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);

		$data = $result->getData();
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = array_shift($data['SHIPMENT']);
		$calcResult = Admin\Blocks\OrderShipment::calculateDeliveryPrice($shipment);

		if ($calcResult->isSuccess())
			$this->addResultData("RESULT", array("CALCULATED_PRICE" => $calcResult->getPrice()));
		else
			$this->addResultError(implode("\n", $result->getErrorMessages()));
	}

	protected function checkProductBarcodeAction()
	{
		if(!\Bitrix\Main\Loader::includeModule("catalog"))
			throw new UserMessageException("ERROR");
		$basketItem = null;
		$result = false;

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		$barcode = $this->request['barcode'];
		$basketId = $this->request['basketId'];
		$orderId = $this->request['orderId'];
		$storeId = $this->request['storeId'];

		/** @var \Bitrix\Sale\Order $order */
		$order = $orderClass::load($orderId);
		if ($order)
		{
			$basket = $order->getBasket();
			if ($basket)
				$basketItem = $basket->getItemById($basketId);
		}

		if ($basketItem)
		{
			$params = array(
				'BARCODE' => $barcode,
				'STORE_ID' => $storeId
			);
			$result = Provider::checkProductBarcode($basketItem, $params);
		}

		if ($result)
			$this->addResultData('RESULT', 'OK');
		else
			$this->addResultError('ERROR');
	}

	protected function deleteCouponAction()
	{
		global $APPLICATION, $USER;
		if(!isset($this->request["userId"])) throw new ArgumentNullException("userId");
		if(!isset($this->request["coupon"])) throw new ArgumentNullException("coupon");
		if(!isset($this->request["orderId"])) throw new ArgumentNullException("orderId");

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		Admin\OrderEdit::$needUpdateNewProductPrice = true;
		Admin\OrderEdit::$isTrustProductFormData = false;

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$isAllowUpdate = false;

			$resOrder = $orderClass::getList([
				'filter' => ['=ID' => intval($this->request["orderId"])],
				'select' => ['RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID']
			]);
			if ($orderData = $resOrder->fetch())
			{
				$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowUpdate = in_array($orderData['STATUS_ID'], $allowedStatusesUpdate);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_ORDER_COUPON_DELETE'));
			}
		}

		Admin\OrderEdit::initCouponsData($this->request["userId"], $this->request["orderId"]);

		/** @var Sale\DiscountCouponsManager $discountCouponsClass */
		$discountCouponsClass = $this->registry->getDiscountCouponClassName();
		if($discountCouponsClass::delete($this->request["coupon"]))
			$this->addResultData('RESULT', 'OK');
		else
			$this->addResultError('ERROR');
	}

	protected function addCouponsAction()
	{
		global $APPLICATION, $USER;
		if(!isset($this->request["userId"])) throw new ArgumentNullException("userId");
		if(!isset($this->request["coupon"])) throw new ArgumentNullException("coupon");
		if(!isset($this->request["orderId"])) throw new ArgumentNullException("orderId");

		Admin\OrderEdit::$needUpdateNewProductPrice = true;
		Admin\OrderEdit::$isTrustProductFormData = false;

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$isAllowUpdate = false;

			$resOrder = $orderClass::getList([
				'select' => ['RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'],
				'filter' => ['=ID' => intval($this->request["orderId"])]
			]);
			if ($orderData = $resOrder->fetch())
			{
				$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowUpdate = in_array($orderData['STATUS_ID'], $allowedStatusesUpdate);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_ORDER_COUPON_ADD'));
			}
		}

		Admin\OrderEdit::initCouponsData($this->request["userId"], $this->request["orderId"]);

		if($this->request["coupon"] <> '')
		{
			$coupons = explode(",", $this->request["coupon"]);

			if(is_array($coupons) && count($coupons) > 0)
			{
				foreach ($coupons as $coupon)
				{
					if ($coupon <> '')
					{
						/** @var Sale\DiscountCouponsManager $discountCouponsClass */
						$discountCouponsClass = $this->registry->getDiscountCouponClassName();
						$discountCouponsClass::add($coupon);
					}
				}
			}
		}

		$this->addResultData('RESULT', 'OK');
	}

	protected function getProductIdByBarcodeAction()
	{
		\Bitrix\Main\Loader::includeModule('catalog');

		$barcode = $this->request['barcode'];

		if($barcode <> '')
		{
			$rsBarCode = \CCatalogStoreBarCode::getList(array(), array("BARCODE" => $barcode), false, false, array('PRODUCT_ID'));
			$arBarCode = $rsBarCode->Fetch();
		}

		$this->addResultData(
			'RESULT',
			array(
				"PRODUCT_ID" => isset($arBarCode["PRODUCT_ID"]) ? intval($arBarCode["PRODUCT_ID"]) : 0
			)
		);
	}

	/* * * * * * * accessory methods * * * * * * * */

	protected function getDemandedFields(array $demandedFields, array $incomingFields, \Bitrix\Sale\Order $order = null)
	{
		$result = array();
		$userId = isset($incomingFields["USER_ID"]) && intval($incomingFields["USER_ID"]) > 0 ? intval($incomingFields["USER_ID"])  : 0;
		$currency = isset($incomingFields["CURRENCY"]) ? trim($incomingFields["CURRENCY"]) : "";
		$personTypeId = isset($incomingFields['PERSON_TYPE_ID']) ? intval($incomingFields['PERSON_TYPE_ID']) : 0;
		$siteId = !empty($incomingFields["SITE_ID"]) ? trim($incomingFields["SITE_ID"])  : SITE_ID;
		$orderId = isset($incomingFields["ID"]) ? intval($incomingFields["ID"]) : 0;
		$buyerIdChanged = isset($incomingFields["BUYER_ID_CHANGED"]) && $incomingFields["BUYER_ID_CHANGED"] == 'Y' ? true : false;

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		if($buyerIdChanged)
		{
			Admin\OrderEdit::$needUpdateNewProductPrice = true;
			Admin\OrderEdit::$isBuyerIdChanged = true;
			Admin\OrderEdit::$isTrustProductFormData = false;
		}

		if($order === null && intval($orderId) > 0)
			$order = $orderClass::load($orderId);

		foreach($demandedFields as $demandedField)
		{
			switch($demandedField)
			{
				case "BUYER_USER_NAME":

					$siteId = (bool)$order ? $order->getSiteId() : "";
					$result["BUYER_USER_NAME"] = intval($userId) > 0 ? Admin\OrderEdit::getUserName(intval($userId), $siteId) : "";
					break;

				case "PROPERTIES":

					if($userId > 0)
					{
						$profileId = isset($incomingFields["BUYER_PROFILE_ID"]) ? intval($incomingFields["BUYER_PROFILE_ID"]) : 0;
						$result["PROPERTIES"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getProfileParams($userId, $profileId);
					}
					else
					{
						$porder = $orderClass::create($siteId);
						$porder->setPersonTypeId($personTypeId);
						$result["PROPERTIES"] = array();

						/** @var Sale\PropertyValue $propertyValueClass */
						$propertyValueClass = $this->registry->getPropertyValueClassName();

						/** @val \Bitrix\Sale\PropertyValue $prop */
						foreach($propertyValueClass::loadForOrder($porder) as $prop)
						{
							$p = $prop->getProperty();
							$result["PROPERTIES"][$prop->getPropertyId()] = !empty($p["DEFAULT_VALUE"]) ? $p["DEFAULT_VALUE"] : "";
						}
					}
					break;

				case "BUYER_PROFILES_LIST":

					if(intval($personTypeId)<=0)
						throw new \Bitrix\Main\ArgumentNullException("personTypeId");

					$result["BUYER_PROFILES_LIST"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getBuyerProfilesList($userId, $personTypeId);
					break;

				case "BUYER_PROFILES_DATA":

					$result["BUYER_PROFILES_DATA"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getUserProfiles($userId, $personTypeId);
					break;

				case "BUYER_BUDGET":
					$res = \CSaleUserAccount::getList(
						array(),
						array(
							'USER_ID' => $userId,
							'CURRENCY' => $currency,
							'LOCKED' => 'N'
						),
						false,
						false,
						array(
							'CURRENT_BUDGET'
						)
					);

					if($userAccount = $res->Fetch())
						$result["BUYER_BUDGET"] = $userAccount['CURRENT_BUDGET'];
					else
						$result["BUYER_BUDGET"] = 0;

					break;
				case "PROPERTIES_ARRAY":

					if(!$order)
						throw new \Bitrix\Main\SystemException("Can't init order");

					if(intval($personTypeId)<=0)
						throw new \Bitrix\Main\ArgumentNullException("personTypeId");

					$order->setPersonTypeId($personTypeId);

					$properties = $order->getPropertyCollection()->getArray();

					if (is_array($properties['properties']))
					{
						foreach ($properties['properties'] as &$property)
						{
							if ($property['TYPE'] === 'ENUM')
							{
								$property['OPTIONS_SORT'] = array_keys($property['OPTIONS']);
							}
						}
					}

					$result["PROPERTIES_ARRAY"] = $properties;
					break;

				case "PRODUCT":
					$result["PRODUCT"] = array();
					break;

				case "COUPONS":
					if(!$userId)
						throw new \Bitrix\Main\ArgumentNullException("userId");

					$result["COUPONS"] = Admin\OrderEdit::getCouponsData();

					break;

				case "COUPONS_LIST":

					$result["COUPONS_LIST"] = Admin\OrderEdit::getCouponList($order);

					break;

				default:
					throw new \Bitrix\Main\SystemException("Field: \"".$demandedField."\" is unknown!");
			}
		}

		return $result;
	}

	/**
	 * @param $formData
	 * @param Result &$result
	 * @return Sale\Order
	 * @throws ArgumentNullException
	 * @throws UserMessageException
	 */
	protected function getOrder(array $formData, Result &$result = null)
	{
		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		$formData["ID"] = (!isset($formData["ID"]) ? 0 : (int)$formData["ID"]);

		if($this->order !== null  && !$this->formDataChanged && $this->order->getId() == $formData["ID"])
			return $this->order;

		if(!$result)
			$result = new Result();

		$currentUserId = 0;
		$oldUserId = null;

		if ($formData["ID"] > 0)
		{
			if ((int)$formData["USER_ID"] > 0)
				$currentUserId = (int)$formData["USER_ID"];
			if ((int)$formData["OLD_USER_ID"] > 0)
				$oldUserId = (int)$formData["OLD_USER_ID"];
		}
		else
		{
			if (isset($formData["USER_ID"]))
				$currentUserId = (int)$formData["USER_ID"];
			if (isset($formData["OLD_USER_ID"]))
				$oldUserId = (int)$formData["OLD_USER_ID"];
		}

		Admin\OrderEdit::initCouponsData($currentUserId, $formData["ID"], $oldUserId);
		unset($oldUserId, $currentUserId);

		if($formData["ID"] > 0)
		{
			$this->order = $orderClass::load($formData["ID"]);

			if(!$this->order)
				throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$formData["ID"]);
		}
		else
		{
			$this->order = Admin\OrderEdit::createOrderFromForm($formData, $this->userId, false, array(), $result);

			if(!$result->isSuccess())
			{
				$this->addFilteredErrors($result);
			}

			if(!$this->order)
			{
				throw new UserMessageException;
			}
		}

		$this->formDataChanged = false;
		return $this->order;
	}

	public static function convertEncodingArray($arData, $charsetFrom, $charsetTo, &$errorMessage = "")
	{
		if (!is_array($arData))
		{
			if (is_string($arData))
			{
				$arData = Encoding::convertEncoding($arData, $charsetFrom, $charsetTo, $errorMessage);
			}
		}
		else
		{
			foreach ($arData as $key => $value)
			{
				$s = '';

				$newKey = Encoding::convertEncoding($key, $charsetFrom, $charsetTo, $s);
				$arData[$newKey] = Encoding::convertEncodingArray($value, $charsetFrom, $charsetTo, $s);

				if($newKey != $key)
					unset($arData[$key]);

				if($s!=='')
				{
					$errorMessage .= ($errorMessage == "" ? "" : "\n").$s;
				}
			}
		}

		return $arData;
	}

	protected function updatePaySystemInfoAction()
	{
		global $APPLICATION, $USER;

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		if ($this->request["orderId"])
			$orderId = $this->request["orderId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_ORDER_ID_WRONG'));

		if ($this->request["paymentId"])
			$paymentId = $this->request["paymentId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_PAYMENT_ID_WRONG'));

		/** @var \Bitrix\Sale\Order $order */
		$order = $orderClass::load($orderId);
		if ($order)
		{
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $order->getPaymentCollection();

			/** @var \Bitrix\Sale\Payment $payment */
			$payment = $paymentCollection->getItemById($paymentId);

			if ($payment)
			{
				$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

				if ($saleModulePermissions == 'P')
				{
					$isUserResponsible = false;
					$isAllowCompany = false;
					$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
					$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

					if ($order->getField('RESPONSIBLE_ID') == $USER->GetID()
						|| $payment->getField('RESPONSIBLE_ID') == $USER->GetID())
					{
						$isUserResponsible = true;
					}

					if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($payment->getField('COMPANY_ID'), $userCompanyList))
					{
						$isAllowCompany = true;
					}

					$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

					if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
					{
						throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_PAYMENT_INFO_CHANGE'));
					}
				}

				/** @var Sale\PaySystem\Service $service */
				$service = Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
				if ($service->isCheckable())
				{
					$res = $service->check($payment);
					if (!$res->isSuccess())
						$this->addResultError(join('\n', $res->getErrorMessages()));
				}
			}
		}
	}

	protected function saveTrackingNumberAction()
	{
		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		global $APPLICATION, $USER;
		$trackingNumber = '';

		if ($this->request["orderId"])
			$orderId = $this->request["orderId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_ORDER_ID_WRONG'));

		if ($this->request["shipmentId"])
			$shipmentId = $this->request["shipmentId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_SHIPMENT_ID_WRONG'));

		if ($this->request['trackingNumber'])
			$trackingNumber = $this->request['trackingNumber'];

		/** @var \Bitrix\Sale\Order $order */
		$order = $orderClass::load($orderId);
		if ($order)
		{
			/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
			$shipmentCollection = $order->getShipmentCollection();

			/** @var \Bitrix\Sale\Payment $payment */
			$shipment = $shipmentCollection->getItemById($shipmentId);

			if ($shipment)
			{
				$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

				if ($saleModulePermissions == 'P')
				{
					$isUserResponsible = false;
					$isAllowCompany = false;

					$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
					$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

					if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
					{
						$isUserResponsible = true;
					}

					if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($shipment->getField('COMPANY_ID'), $userCompanyList))
					{
						$isAllowCompany = true;
					}

					$isAllowUpdate = in_array($shipment->getField('STATUS_ID'), $allowedStatusesUpdate);

					if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
					{
						throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_SHIPMENT_TRACKING_NUMBER_CHANGE'));
					}
				}

				$result = $shipment->setField('TRACKING_NUMBER', $trackingNumber);
				if ($result->isSuccess())
				{
					$result = $order->save();
					if ($result->isSuccess())
					{
						Sale\Provider::resetTrustData($order->getSiteId());
					}
					else
					{
						$messages = join(', ', $result->getErrorMessages());
						$this->addResultError($messages);
					}

					if ($result->hasWarnings())
					{
						$this->addResultWarning(join(', ', $result->getWarningMessages()));
					}
				}
			}
		}
	}

	protected function refreshTrackingStatusAction()
	{
		global $APPLICATION, $USER;
		$shipmentId = !empty($this->request["shipmentId"]) && intval($this->request["shipmentId"]) > 0 ? intval($this->request["shipmentId"]) : 0;
		$trackingNumber = !empty($this->request["trackingNumber"]) && $this->request["trackingNumber"] <> '' ? $this->request["trackingNumber"] : '';

		if($shipmentId <= 0)
			throw new ArgumentNullException('shipmentId');

		if($trackingNumber == '')
			return;

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$isAllowUpdate = false;

			$resOrder = $orderClass::getList([
				'filter' => ['=ID' => intval($this->request["orderId"])],
				'select' => ['RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID']
			]);
			if ($orderData = $resOrder->fetch())
			{
				$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				/** @var Sale\Shipment $shipmentClass */
				$shipmentClass = $this->registry->getShipmentClassName();

				$resShipment = $shipmentClass::getList([
					 'filter' => [
						 '=ORDER_ID' => intval($this->request["orderId"]),
						 '=ID' => intval($this->request["shipmentId"]),
					 ],
					 'select' => ['RESPONSIBLE_ID', 'COMPANY_ID']
				]);
				if ($shipmentData = $resShipment->fetch())
				{
					if ($shipmentData['RESPONSIBLE_ID'] == $USER->GetID())
					{
						$isUserResponsible = true;
					}

					if (in_array($shipmentData['COMPANY_ID'], $userCompanyList))
					{
						$isAllowCompany = true;
					}
				}

				$isAllowUpdate = in_array($shipmentData['STATUS_ID'], $allowedStatusesUpdate);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_SHIPMENT_TRACKING_STATUS_CHANGE'));
			}
		}

		$manager = Sale\Delivery\Tracking\Manager::getInstance();
		$result = $manager->getStatusByShipmentId($shipmentId, $trackingNumber);

		if($result->isSuccess())
		{
			$this->addResultData(
				'TRACKING_STATUS',
				Sale\Delivery\Tracking\Manager::getStatusName($result->status)
			);
			$this->addResultData('TRACKING_DESCRIPTION', $result->description);

			$this->addResultData(
				'TRACKING_LAST_CHANGE',
				\Bitrix\Main\Type\DateTime::createFromTimestamp(
					$result->lastChangeTimestamp
				)->toString()
			);

			$res = $manager->updateShipment($shipmentId, $result);

			if(!$res->isSuccess())
				$this->addResultError(implode(", ", $res->getErrorMessages()));
		}
		else
		{
			$this->addResultError(implode("<br>\n", $result->getErrorMessages()));
		}
	}

	protected function unmarkOrderAction()
	{
		global $APPLICATION, $USER;
		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		if(!\CSaleOrder::CanUserMarkOrder($orderId, $USER->GetUserGroupArray(), $this->userId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_UNMARK_RIGHTS'));

		/** @var  Sale\Order $saleOrder*/
		if(!$saleOrder = $orderClass::load($orderId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($saleOrder->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($saleOrder->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($saleOrder->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_ORDER_UNMARK'));
			}
		}

		/** @var \Bitrix\Sale\Result $res */
		$res = $saleOrder->setField("MARKED", "N");

		$errors = array();
		$warnings = array();

		if(!$res->isSuccess())
			$errors = $res->getErrorMessages();

		$res = $saleOrder->save();
		if($res->isSuccess())
		{
			Sale\Provider::resetTrustData($saleOrder->getSiteId());
		}
		else
		{
			$errors = array_merge($errors, $res->getErrorMessages());
		}

		if ($res->hasWarnings())
		{
			$warnings = array_merge($warnings, $res->getWarningMessages());
		}

		if (!empty($errors))
		{
			$this->addResultError($errors);
		}

		if (!empty($warnings))
		{
			$this->addResultWarning($warnings);
		}

	}

	protected function updatePriceCodAction($payment = null)
	{
		global $APPLICATION, $USER;
		if ($payment === null)
		{
			if ($this->request["paySystemId"] !== null)
				$paySystemId = $this->request["paySystemId"];
			else
				throw new ArgumentNullException('paymentId');

			if ($this->request["orderId"] !== null)
				$orderId = $this->request["orderId"];
			else
				throw new ArgumentNullException('orderId');

			if ($this->request["paymentId"] !== null)
				$paymentId = $this->request["paymentId"];
			else
				throw new ArgumentNullException('paymentId');


			/** @var Sale\Order $orderClass */
			$orderClass = $this->registry->getOrderClassName();

			/** @var Sale\OrderStatus $orderStatusClass */
			$orderStatusClass = $this->registry->getOrderStatusClassName();

			if ($orderId > 0)
			{
				/** @var \Bitrix\Sale\Order $order */
				$order = $orderClass::load($orderId);
				if ($order)
				{
					/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
					$paymentCollection = $order->getPaymentCollection();
					if ($paymentCollection)
					{
						/** @var \Bitrix\Sale\Payment $payment */
						if ($paymentId > 0)
						{
							$payment = $paymentCollection->getItemById($paymentId);
							if ($payment)
							{
								$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

								if ($saleModulePermissions == 'P')
								{
									$isUserResponsible = false;
									$isAllowCompany = false;

									$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

									$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

									if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
										$payment->getField('RESPONSIBLE_ID') == $USER->GetID())
									{
										$isUserResponsible = true;
									}

									if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($payment->getField('COMPANY_ID'), $userCompanyList))
									{
										$isAllowCompany = true;
									}

									$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

									if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
									{
										throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_PAYMENT_PAYSYSTEM_CHANGE'));
									}
								}

								$payment->setField('PAY_SYSTEM_ID', $paySystemId);
							}
						}
						else
						{
							$payment = $paymentCollection->createItem(Sale\PaySystem\Manager::getObjectById($paySystemId));
							$price = floatval($this->request["price"]);
							$payment->setField('SUM', $price);
						}
					}
				}
			}
		}
		$priceCod = 0;

		if ($payment && $payment->getPaymentSystemId() > 0)
		{
			$service = Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
			if ($service !== null)
			{
				$priceCod = $service->getPaymentPrice($payment);
				$this->addResultData('PRICE_COD', $priceCod);
			}
		}
		return $priceCod;
	}

	protected function getOrderTailsAction()
	{
		global $APPLICATION, $USER;
		$orderId = isset($this->request["orderId"]) ? $this->request["orderId"] : array();
		$formType = isset($this->request["formType"]) && $this->request["formType"] == "edit" ? "edit" : "view";
		$idPrefix = isset($this->request["idPrefix"]) ? trim($this->request["idPrefix"]) : "";
		$factory = Admin\Blocks\FactoryMode::create(Sale\Exchange\Integration\Admin\Link::getInstance()->getType());

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		$result = array();
		/** @var \Bitrix\Sale\Order $order */
		$order = $orderClass::load($orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$allowedStatusesView = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
			$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

			$isAllowView = in_array($order->getField('STATUS_ID'), $allowedStatusesView) || in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}


			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowView)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_ORDER_GET_TAILS'));
			}
		}

		$orderBasket = new Admin\Blocks\OrderBasket(
			$order,
			"",
			$idPrefix,
			true,
			($formType == 'edit' ? Admin\Blocks\OrderBasket::EDIT_MODE : Admin\Blocks\OrderBasket::VIEW_MODE)
			);
		Admin\OrderEdit::initCouponsData($order->getUserId(), $orderId, null);
		$result["DISCOUNTS_LIST"] = $factory::create(Admin\Blocks\BlockType::DISCOUNT)::getOrderedDiscounts($order, false);
		$result["BASKET"] = $orderBasket->prepareData(
			array("DISCOUNTS" => $result["DISCOUNTS_LIST"])
		);
		$result["ANALYSIS"] = $factory::create(Admin\Blocks\BlockType::ANALYSIS)->getView($order, $orderBasket);
		$result["SHIPMENTS"] = "";
		Admin\Blocks\OrderShipment::setBackUrl($_SERVER['HTTP_REFERER']);

		$httpReferrer = new Web\Uri($_SERVER['HTTP_REFERER']);
		Admin\Blocks\OrderShipment::setBackUrl($httpReferrer->getPathQuery());

		$shipments = $order->getShipmentCollection();
		$index = 0;

		/** @var \Bitrix\Sale\Shipment  $shipment*/
		foreach ($shipments as $shipment)
		{
			if(!$shipment->isSystem())
			{
				$result["SHIPMENTS"] .= Admin\Blocks\OrderShipment::getView(
					$shipment,
					$index++,
					$formType == 'edit' ? 'edit' : ''
				);
			}
		}

		$this->addResultData("", $result);
	}


	protected function fixMarkerAction()
	{
		global $APPLICATION, $USER;

		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;
		$markerId = isset($this->request['markerId']) ? intval($this->request['markerId']) : 0;
		$entityId = isset($this->request['entityId']) ? intval($this->request['entityId']) : 0;
		$forEntity = isset($this->request['forEntity']) && $this->request['forEntity'] == 'Y' ? true : false;

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		$allowedStatusesMark = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('mark'));

		/** @var  \Bitrix\Sale\Order $order*/
		if(!$order = $orderClass::load($orderId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());
			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			if ((!$isUserResponsible && !$isAllowCompany))
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_MARKER_FIX'));
			}
		}

		if(!in_array($order->getField("STATUS_ID"), $allowedStatusesMark))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_UNMARK_RIGHTS'));
		}

		$errors = array();
		$warnings = array();

		/** @var Sale\EntityMarker $entityMarkerClass */
		$entityMarkerClass = $this->registry->getEntityMarkerClassName();

		$r = $entityMarkerClass::tryFixErrorsByOrder($order, $markerId);
		if(!$r->isSuccess())
		{
			$errors = $r->getErrorMessages();
		}
		else
		{
			if (($resultList = $r->getData()) && !empty($resultList) && is_array($resultList))
			{
				if (!empty($resultList['LIST']) && array_key_exists($markerId, $resultList['LIST']) && $resultList['LIST'][$markerId] === false)
				{
					if (!empty($resultList['ERRORS']) && !empty($resultList['ERRORS'][$markerId]) && is_array($resultList['ERRORS'][$markerId]))
					{
						$warnings = array_merge($warnings, $resultList['ERRORS'][$markerId]);
					}
				}
			}

		}

		$r = $order->save();
		if($r->isSuccess())
		{
			Sale\Provider::resetTrustData($order->getSiteId());
		}
		else
		{
			$errors = array_merge($errors, $r->getErrorMessages());
		}

		if ($r->hasWarnings())
		{
			$warnings = array_merge($warnings, $r->getWarningMessages());
		}

		if (!empty($errors))
		{
			foreach ($errors as $error)
			{
				$this->addResultError($error);
			}
		}
		else
		{
			if (!empty($warnings))
			{
				foreach ($warnings as $warning)
				{
					$this->addResultWarning($warning);
				}
			}

			if ($forEntity)
			{
				$markerListHtml = Admin\Blocks\OrderMarker::getViewForEntity($orderId, $entityId);
			}
			else
			{
				$markerListHtml = Admin\Blocks\OrderMarker::getView($orderId);
			}

			if (!empty($markerListHtml))
			{
				$this->addResultData('MARKERS', $markerListHtml);
			}
		}

	}


	protected function deleteMarkerAction()
	{
		global $APPLICATION, $USER;

		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;
		$markerId = isset($this->request['markerId']) ? intval($this->request['markerId']) : 0;
		$entityId = isset($this->request['entityId']) ? intval($this->request['entityId']) : 0;
		$forEntity = isset($this->request['forEntity']) && $this->request['forEntity'] == 'Y' ? true : false;

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $this->registry->getOrderStatusClassName();

		$allowedStatusesMark = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('mark'));

		/** @var  \Bitrix\Sale\Order $saleOrder*/
		if (!$order = $orderClass::load($orderId))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);
		}

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());
			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			if ((!$isUserResponsible && !$isAllowCompany))
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION_MARKER_DELETE'));
			}
		}

		if (!in_array($order->getField("STATUS_ID"), $allowedStatusesMark))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_UNMARK_RIGHTS'));
		}

		/** @var Sale\EntityMarker $entityMarkerClass */
		$entityMarkerClass = $this->registry->getEntityMarkerClassName();

		$r = $entityMarkerClass::delete($markerId);
		if(!$r->isSuccess())
		{
			$errors = $r->getErrorMessages();
			foreach ($errors as $error)
			{
				$this->addResultError($error);
			}
		}
		else
		{
			$r = $order->save();
			if (!$r->isSuccess())
			{
				$errors = $r->getErrorMessages();
				foreach ($errors as $error)
				{
					$this->addResultError($error);
				}
			}

			if ($r->hasWarnings())
			{
				$warnings = $r->getWarningMessages();
				foreach ($warnings as $warning)
				{
					$this->addResultWarning($warning);
				}
			}

			if ($forEntity)
			{
				$markerListHtml = Admin\Blocks\OrderMarker::getViewForEntity($orderId, $entityId);
			}
			else
			{
				$markerListHtml = Admin\Blocks\OrderMarker::getView($orderId);
			}
			if (!empty($markerListHtml))
			{
				$this->addResultData('MARKERS', $markerListHtml);
			}
		}
	}

	/**
	 * Create HTML for create check window
	 *
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	protected function addCheckPaymentAction()
	{
		$paymentId = $this->request['paymentId'];
		if ((int)$paymentId <= 0)
		{
			$this->addResultError("Wrong payment id");
			return ;
		}

		$typeList = Cashbox\CheckManager::getCheckTypeMap();
		$checkType = null;
		$typeListHtml = '';
		/** @var Cashbox\Check $typeClass */
		foreach ($typeList as $id => $typeClass)
		{
			if (class_exists($typeClass))
			{
				if ($typeClass::getSupportedEntityType() === Cashbox\Check::SUPPORTED_ENTITY_TYPE_SHIPMENT)
					continue;

				if ($checkType === null)
				{
					$checkType = $id;
				}

				$type = $typeClass::getName();
				$typeListHtml .= "<option value='".$id."'>".$type."</option>";
			}
		}

		$relatedEntities = Cashbox\CheckManager::getRelatedEntitiesForPayment($checkType, $paymentId);

		$entityHtml = '';

		if ($relatedEntities['SHIPMENTS'])
		{
			$useMulti = Cashbox\Manager::isSupportedFFD105() ? 'true' : 'false';
			foreach($relatedEntities['SHIPMENTS'] as $shipment)
			{
				$uuid = uniqid('check_'.$shipment['ID']);
				$entityHtml .= "
					<tr>
						<td colspan='2'>
							<input type='checkbox' id='".$uuid."' onclick='BX.Sale.Admin.OrderPayment.prototype.onCheckEntityChoose(this, $useMulti);' name='SHIPMENTS[".$shipment['ID']."][ID]' value='".$shipment['ID']."'>
							<label for='".$uuid."'>
								".Loc::getMessage('CASHBOX_OA_SHIPMENT')." ".$shipment['ID'].": ".$shipment['NAME']."
							</label>
						</td>
					</tr>";
			}
		}

		if ($relatedEntities['PAYMENTS'])
		{
			foreach($relatedEntities['PAYMENTS'] as $payment)
			{
				$uuid = uniqid('check_'.$payment['ID']);

				$entityHtml .= "
					<tr>
						<td>
							<input type='checkbox' id='".$uuid."' onclick='BX.Sale.Admin.OrderPayment.prototype.onCheckEntityChoose(this, true)' name='PAYMENTS[".$payment['ID']."][ID]' value='".$payment['ID']."'>
							<label for='".$uuid."'>
								".Loc::getMessage('CASHBOX_OA_PAYMENT')." ".$payment['ID'].": ".$payment['NAME']."
							</label>
						</td>
						<td>
							<select name='PAYMENTS[".$payment['ID']."][TYPE]' disabled='true' id='".$uuid."_type'>
								<option value='".Cashbox\Check::PAYMENT_TYPE_ADVANCE."'>".Loc::getMessage('CASHBOX_OA_PAYMENT_TYPE_ADVANCE')."</option>
								<option value='".Cashbox\Check::PAYMENT_TYPE_CREDIT."'>".Loc::getMessage('CASHBOX_OA_PAYMENT_TYPE_CREDIT')."</option>
							</select>
						</td>
					</tr>";
			}
		}

		$resultHtml = "
			<form name='check_payment' id='check_payment'>
				<table>
					<tr>
						<input type='hidden' name='action' value='saveCheck'>
						<td>
							<label for='checkTypeSelect'>".Loc::getMessage("SALE_CASHBOX_SELECT_TYPE").": </label>
						</td>
						<td>
							<select name='CHECK_TYPE' id='checkTypeSelect'>
							".$typeListHtml."
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label for='checkShipmentSelect'>".Loc::getMessage("SALE_CASHBOX_SELECT_ENTITIES").": </label>
						</td>
						<td>
							<table>
								".$entityHtml."
							</table>
						</td>
					</tr>
				</table>
				<input type='hidden' name='PAYMENT_ID' value='".$paymentId."'>
			</form>
		";

		$this->addResultData("HTML", $resultHtml);
	}

	/**
	 * Create HTML for create check window
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	protected function addCheckShipmentAction()
	{
		$shipmentId = $this->request['shipmentId'];
		if ((int)$shipmentId <= 0)
		{
			$this->addResultError("Wrong payment id");
			return ;
		}

		$checkType = null;
		$typeList = Cashbox\CheckManager::getCheckTypeMap();
		$typeListHtml = '';
		/** @var Cashbox\Check $typeClass */
		foreach ($typeList as $id => $typeClass)
		{
			if (class_exists($typeClass))
			{
				if ($typeClass::getSupportedEntityType() === Cashbox\Check::SUPPORTED_ENTITY_TYPE_PAYMENT)
					continue;

				if ($checkType === null)
				{
					$checkType = $id;
				}

				$type = $typeClass::getName();
				$typeListHtml .= "<option value='".$id."'>".$type."</option>";
			}
		}

		$relatedEntities = Cashbox\CheckManager::getRelatedEntitiesForShipment($checkType, $shipmentId);

		$entityHtml = '';
		if ($relatedEntities['PAYMENTS'])
		{
			foreach($relatedEntities['PAYMENTS'] as $payment)
			{
				$uuid = uniqid('check_'.$payment['ID'].'_');

				$entityHtml .= "
					<tr>
						<td>
							<input type='checkbox' id='".$uuid."' onclick='BX.Sale.Admin.OrderShipment.prototype.onCheckEntityChoose(this, true);' name='PAYMENTS[".$payment['ID']."][ID]' value='".$payment['ID']."'>
							<label for='".$uuid."' id=\"".$uuid."_title\">
								".Loc::getMessage('CASHBOX_OA_PAYMENT')." ".$payment['ID'].": ".$payment['NAME']."
							</label>
						</td>
						<td>
							<select name='PAYMENTS[".$payment['ID']."][TYPE]' disabled='true' id='".$uuid."_type'>
								<option value='".Cashbox\Check::PAYMENT_TYPE_ADVANCE."'>".Loc::getMessage('CASHBOX_OA_PAYMENT_TYPE_ADVANCE')."</option>
								<option value='".Cashbox\Check::PAYMENT_TYPE_CREDIT."'>".Loc::getMessage('CASHBOX_OA_PAYMENT_TYPE_CREDIT')."</option>
							</select>
						</td>
					</tr>";
			}
		}

		/** @var Sale\Shipment $shipmentClass */
		$shipmentClass = $this->registry->getShipmentClassName();

		$dbRes = $shipmentClass::getList(array(
			'select' => array('PRICE_DELIVERY'),
			'filter' => array('=ID' => $shipmentId)
		));
		$shipmentInfo = $dbRes->fetch();

		$totalSum = $shipmentInfo['PRICE_DELIVERY'];

		/** @var Sale\ShipmentItemCollection $shipmentItemCollectionClass */
		$shipmentItemCollectionClass = $this->registry->getShipmentItemCollectionClassName();

		$dbRes = $shipmentItemCollectionClass::getList(array(
			'select' => array(
				'PRICE' => 'BASKET.PRICE',
				'QUANTITY',
				'CURRENCY' => 'BASKET.CURRENCY'
			),
			'filter' => array('=ORDER_DELIVERY_ID' => $shipmentId)
		));

		$currency = '';
		while ($item = $dbRes->fetch())
		{
			if ($currency === '')
			{
				$currency = $item['CURRENCY'];
			}

			$totalSum += $item['PRICE'] * $item['QUANTITY'];
		}


		$resultHtml = "
			<form name='check_shipment' id='check_shipment'>
				<table>
					<tr>
						<td colspan='2' style='padding-bottom: 7px'>".Loc::getMessage('SALE_CASHBOX_SHIPMENT_PRICE').": ".SaleFormatCurrency($totalSum, $currency)."</td>
					</tr>
					<tr>
						<td>
							<input type='hidden' name='action' value='saveCheck'>
							<label for='checkTypeSelect'>".Loc::getMessage("SALE_CASHBOX_SELECT_TYPE").": </label>
						</td>
						<td>
							<select name='CHECK_TYPE' id='checkTypeSelect'>
							".$typeListHtml."
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label for='checkShipmentSelect'>".Loc::getMessage("SALE_CASHBOX_SELECT_ENTITIES").": </label>
						</td>
						<td>
							<table>
								".$entityHtml."
							</table>
						</td>
					</tr>
				</table>
				<input type='hidden' name='SHIPMENT_ID' value='".$shipmentId."'>
			</form>
		";

		$this->addResultData("HTML", $resultHtml);
	}

	/**
	 * Add new check
	 *
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	protected function saveCheckAction()
	{
		global $APPLICATION, $USER;

		$orderId = 0;
		$typeId = $this->request['formData']['data']['CHECK_TYPE'];
		$paymentId = (int)$this->request['formData']['data']['PAYMENT_ID'];
		$shipmentId = (int)$this->request['formData']['data']['SHIPMENT_ID'];
		$paymentData = $this->request['formData']['data']['PAYMENTS'];
		$shipmentData = $this->request['formData']['data']['SHIPMENTS'];

		if ($paymentId > 0)
		{
			/** @var Sale\Payment $paymentClass */
			$paymentClass = $this->registry->getPaymentClassName();

			$dbRes = $paymentClass::getList(
				array(
					"filter" => array("ID" => (int)$paymentId)
				)
			);

			$data = $dbRes->fetch();
			$orderId = $data['ORDER_ID'];

		}
		elseif ($shipmentId > 0)
		{

			/** @var Sale\Shipment $shipmentClass */
			$shipmentClass = $this->registry->getShipmentClassName();

			$dbRes = $shipmentClass::getList(
				array(
					"filter" => array("ID" => (int)$shipmentId)
				)
			);

			$data = $dbRes->fetch();
			$orderId = $data['ORDER_ID'];
		}

		if ($orderId <= 0)
		{
			return;
		}

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		$order = $orderClass::load($orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$userId = $USER->GetID();
			$userCompanyList = Sale\Services\Company\Manager::getUserCompanyList($userId);
			if ($order->getUserId() !== $userId && !in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$this->addResultError(Loc::getMessage('CASHBOX_CREATE_CHECK_ERROR_ORDER_ID'));
				return;
			}
		}

		$paymentCollection = $order->getPaymentCollection();
		$entities = array();
		if ($paymentId > 0)
		{
			$entities[] = $paymentCollection->getItemById($paymentId);
		}

		$shipmentCollection = $order->getShipmentCollection();
		if ($shipmentId > 0)
		{
			$entities[] = $shipmentCollection->getItemById($shipmentId);
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
			$this->addResultError(implode("\n", $addResult->getErrorMessages()));

		if ($paymentId > 0)
		{
			$filter = array(
				'PAYMENT_ID' => $paymentId,
				'ENTITY_REGISTRY_TYPE' => Sale\Registry::REGISTRY_TYPE_ORDER
			);
			$checkData = Cashbox\CheckManager::collectInfo($filter);
			$htmlCheckList = Sale\Helpers\Admin\Blocks\OrderPayment::buildCheckHtml($checkData);
		}
		else if ($shipmentId > 0)
		{
			$filter = array(
				'SHIPMENT_ID' => $shipmentId,
				'ENTITY_REGISTRY_TYPE' => Sale\Registry::REGISTRY_TYPE_ORDER
			);
			$checkData = Cashbox\CheckManager::collectInfo($filter);
			$htmlCheckList = Sale\Helpers\Admin\Blocks\OrderShipment::buildCheckHtml($checkData);
		}
		else
		{
			$htmlCheckList = '';
		}

		$this->addResultData("CHECK_LIST_HTML", $htmlCheckList);
	}

	protected function sendQueryCheckStatusAction()
	{
		$checkId = (int)$this->request['checkId'];

		$check = Cashbox\CheckManager::getObjectById($checkId);
		if ($check->getField('STATUS') === 'P')
		{
			$cashbox = Cashbox\Manager::getObjectById($check->getField('CASHBOX_ID'));
			if ($cashbox && $cashbox->isCheckable())
			{
				$r = $cashbox->check($check);
				if (!$r->isSuccess())
				{
					$err = implode("\n", $r->getErrorMessages());
					$this->addResultError($err);
				}
			}
		}

		if ($check->getField('PAYMENT_ID') > 0)
		{
			$filter = array(
				'PAYMENT_ID' => $check->getField('PAYMENT_ID'),
				'ENTITY_REGISTRY_TYPE' => Sale\Registry::REGISTRY_TYPE_ORDER
			);
			$checkData = Cashbox\CheckManager::collectInfo($filter);
			$htmlCheckList = Sale\Helpers\Admin\Blocks\OrderPayment::buildCheckHtml($checkData);
			$this->addResultData("PAYMENT_ID", $check->getField('PAYMENT_ID'));
			$this->addResultData("CHECK_LIST_HTML", $htmlCheckList);
		}
		elseif ($check->getField('SHIPMENT_ID') > 0)
		{
			$filter = array(
				'SHIPMENT_ID' => $check->getField('SHIPMENT_ID'),
				'ENTITY_REGISTRY_TYPE' => Sale\Registry::REGISTRY_TYPE_ORDER
			);
			$checkData = Cashbox\CheckManager::collectInfo($filter);
			$htmlCheckList = Sale\Helpers\Admin\Blocks\OrderShipment::buildCheckHtml($checkData);
			$this->addResultData("SHIPMENT_ID", $check->getField('SHIPMENT_ID'));
			$this->addResultData("CHECK_LIST_HTML", $htmlCheckList);
		}
		else
		{
			throw new SystemException();
		}
	}

	/**
	 * @param array $filter
	 *
	 * @return array
	 */
	private function calculateCheckSum($filter = array())
	{
		$result = array();
		$defaultSum = SaleFormatCurrency( 0, CurrencyManager::getBaseCurrency());

		$checkData = Cashbox\CheckManager::getList(
			array(
				'select' => array('CHECK_SUM', 'CURRENCY', 'TYPE'),
				'filter' => $filter,
				'group' => array('TYPE'),
				'runtime' => array(
					new \Bitrix\Main\Entity\ExpressionField(
						'CHECK_SUM',
						'SUM(%s)',
						array('SUM')
					)
				)
			)
		);

		while($data = $checkData->fetch())
		{
			if ($data['TYPE'] == 'sellreturn' || $data['TYPE'] == 'prepaymentreturn')
			{
				$result['RETURN_SUM'] += $data['CHECK_SUM'];
			}
			else
			{
				$result['SUM'] += $data['CHECK_SUM'];
			}
			if (empty($result['CURRENCY']))
			{
				$result['CURRENCY'] = $data['CURRENCY'];
			}
		}

		if (isset($result['SUM']) && isset($result['CURRENCY']))
		{
			$result['FORMATED_SUM'] = SaleFormatCurrency($result['SUM'], $result['CURRENCY']);
		}
		else
		{
			$result['FORMATED_SUM'] = $defaultSum;
		}

		if (isset($result['RETURN_SUM']) && isset($result['CURRENCY']))
		{
			$result['FORMATED_RETURN_SUM'] = SaleFormatCurrency($result['RETURN_SUM'], $result['CURRENCY']);
		}
		else
		{
			$result['FORMATED_RETURN_SUM'] = $defaultSum;
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function loadCashboxCheckInfoAction()
	{
		$result = array();
		$cashboxId = (int)$this->request['cashboxId'];

		if ($cashboxId <= 0)
		{
			$defaulValue = SaleFormatCurrency( 0, CurrencyManager::getBaseCurrency());
			$result['CUMULATIVE']['FORMATED_SUM'] = $defaulValue;
			$result['CASHLESS']['FORMATED_SUM'] = $defaulValue;
			$result['CASH']['FORMATED_SUM'] = $defaulValue;

			$this->addResultData("FRAME", $result);

			return;
		}

		$today = new Date();
		$result['CASHLESS'] = $this->calculateCheckSum(array(
			'PAYMENT.PAY_SYSTEM.IS_CASH' => 'N',
			'>DATE_CREATE' => $today,
			'CASHBOX_ID' => $cashboxId
		));

		$result['CASH'] = $this->calculateCheckSum(array(
			'PAYMENT.PAY_SYSTEM.IS_CASH' => 'Y',
			'>DATE_CREATE' => $today,
			'CASHBOX_ID' => $cashboxId
		));

		$zreportData = Cashbox\Internals\CashboxZReportTable::getList(
			array(
				'limit' => 1,
				'select' => array('CUMULATIVE_SUM', 'CURRENCY'),
				'filter' => array('CASHBOX_ID' => $cashboxId),
				'order'=> array('DATE_CREATE' => 'DESC')
			)
		);

		$result['CUMULATIVE'] = $zreportData->fetch();

		if (isset($result['CUMULATIVE']['CUMULATIVE_SUM']) && isset($result['CUMULATIVE']['CURRENCY']))
		{
			$result['CUMULATIVE']['FORMATED_SUM'] = SaleFormatCurrency($result['CUMULATIVE']['CUMULATIVE_SUM'], $result['CUMULATIVE']['CURRENCY']);
		}
		else
		{
			$result['CUMULATIVE']['FORMATED_SUM'] = SaleFormatCurrency( 0, CurrencyManager::getBaseCurrency());;
		}

		$this->addResultData(null, $result);
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function addZReportAction()
	{
		$cashboxId = (int)$this->request['cashboxId'];

		$cashboxData = Cashbox\Internals\CashboxTable::getList(
			array(
				'filter' => array(
					'=ID' => $cashboxId,
					'USE_OFFLINE' => 'N',
					'!%HANDLER' => '\\Bitrix\\Sale\\Cashbox\\Cashbox1C'
				)
			)
		);

		if ($cashbox = $cashboxData->fetch())
		{
			Cashbox\ReportManager::addZReport($cashboxId);
		}
		else
		{
			$this->addResultError(Loc::getMessage('CASHBOX_ADD_ZREPORT_WRONG_CHECKBOX'));
		}
	}

	/**
	 * @param Result $result
	 *
	 * @return Result
	 */
	protected function convertErrorsToConfirmation(Result $result)
	{
		if ($result->isSuccess())
		{
			return $result;
		}

		$resultOutput = new Result();
		$resultData = array();

		if ($result->hasWarnings())
		{
			$resultOutput->addWarnings($result->getWarnings());
		}

		$errorsListForConfirm = $this->getListErrorsForConfirmation();
		foreach ($result->getErrors() as $error)
		{
			if (in_array($error->getCode(), $errorsListForConfirm, true))
			{
				$warningExists = false;
				$resultData['NEED_CONFIRM'] = true;
				foreach ($resultOutput->getWarningMessages() as $warningCode => $warningMessage)
				{
					if ($warningCode == $error->getCode())
					{
						$warningExists = true;
					}
				}

				if (!$warningExists)
				{
					$resultOutput->addWarning($error);
				}
			}
			else
			{
				$resultOutput->addError($error);
			}
		}

		$resultOutput->setData($resultData);
		return $resultOutput;
	}

	/**
	 * @param Result $result
	 *
	 * @return bool
	 */
	protected function hasErrorsForConfirmation(Result $result)
	{
		$errorsListForConfirm = $this->getListErrorsForConfirmation();
		foreach ($result->getErrors() as $error)
		{
			if (in_array($error->getCode(), $errorsListForConfirm))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param Result $result
	 *
	 * @return Result
	 */
	protected function removeErrorsForConfirmation(Result $result)
	{
		$resultOutput = new Result();

		$resultOutput->setData($result->getData());
		$resultOutput->setId($result->getId());

		$errorsListForConfirm = $this->getListErrorsForConfirmation();
		foreach ($result->getErrors() as $error)
		{
			if (!in_array($error->getCode(), $errorsListForConfirm))
			{
				$resultOutput->addError($error);
			}
		}

		foreach ($result->getWarnings() as $warning)
		{
			if (!in_array($warning->getCode(), $errorsListForConfirm))
			{
				$resultOutput->addWarning($warning);
			}
		}

		return $resultOutput;
	}

	/**
	 * @return array
	 */
	protected function getListErrorsForConfirmation()
	{
		return array(
			'SALE_PROVIDER_PRODUCT_NOT_AVAILABLE'
		);
	}
}