<?

/**
 * @var  CUser $USER
 * @var  CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc,
	Bitrix\Sale\DiscountCouponsManager,
	Bitrix\Sale\Helpers\Admin\OrderEdit,
	Bitrix\Sale\Helpers\Admin\Blocks,
	Bitrix\Sale,
	Bitrix\Catalog,
	Bitrix\Sale\Exchange\Integration\Admin\Link,
	Bitrix\Sale\Exchange\Integration\Admin\Registry;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

Bitrix\Main\Loader::includeModule('sale');
Loc::loadMessages(__FILE__);
$ID = isset($_REQUEST["ID"]) ? intval($_REQUEST["ID"]) : 0;

$isSavingOperation = (
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& (
		isset($_POST["apply"])
		|| isset($_POST["save"])
	)
	&& check_bitrix_sessid()
);
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$needFieldsRestore = $_SERVER["REQUEST_METHOD"] == "POST" && !$isSavingOperation;
$isCopyingOrderOperation = $ID > 0;
$isRestoringOrderOperation = ((int)$_GET['restoreID'] > 0);
$createWithProducts = (isset($_GET["USER_ID"]) && isset($_GET["SITE_ID"]) || isset($_GET["product"]));
$showProfiles = false;
$profileId = 0;
$link = Link::getInstance();

$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

/** @var Sale\Order $orderClass */
$orderClass = $registry->getOrderClassName();

$arUserGroups = $USER->GetUserGroupArray();
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if (
	$saleModulePermissions == "D"
	|| ($isSavingOperation && $saleModulePermissions < "P")
	|| ($isRestoringOrderOperation && $saleModulePermissions < "P")
)
{
	$APPLICATION->AuthForm(Loc::getMessage("SALE_OK_ACCESS_DENIED"));
}

$moduleId = "sale";
Bitrix\Main\Loader::includeModule('sale');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

$siteId = isset($_REQUEST["SITE_ID"]) ? htmlspecialcharsbx($_REQUEST["SITE_ID"]) : "";
$siteName = OrderEdit::getSiteName($siteId);
/** @var \Bitrix\Sale\Order $order */
$order = null;
$result = new \Bitrix\Sale\Result();

$customTabber = new CAdminTabEngine("OnAdminSaleOrderCreate");
$customDraggableBlocks = new CAdminDraggableBlockEngine('OnAdminSaleOrderCreateDraggable');


/** @var Sale\DiscountCouponsManager $discountCouponsClass */
$discountCouponsClass = $registry->getDiscountCouponClassName();
$discountCouponsClass::init(
	$discountCouponsClass::MODE_MANAGER,
	array(
		'userId' => isset($_POST["USER_ID"]) ? $_POST["USER_ID"] : 0
	)
);
// try to create order from form data & save it
if($isSavingOperation || $needFieldsRestore)
{
	if($isSavingOperation)
		OrderEdit::$isTrustProductFormData = true;

	$order = OrderEdit::createOrderFromForm($_POST, $USER->GetID(), true, $_FILES, $result);

	if($order && $result->isSuccess())
	{
		$errorMessage = '';

		if (!$customTabber->Check())
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString();
			else
				$errorMessage .= "Custom tabber check unknown error!";

			$result->addError(new \Bitrix\Main\Entity\EntityError($errorMessage));
		}

		if (!$customDraggableBlocks->check())
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString();
			else
				$errorMessage .= "Custom draggable block check unknown error!";

			$result->addError(new \Bitrix\Main\Entity\EntityError($errorMessage));
		}

		if(isset($_POST["SHIPMENT"]) && $_POST["SHIPMENT"])
		{
			$dlvRes = Blocks\OrderShipment::updateData($order, $_POST['SHIPMENT']);

			if(!$dlvRes->isSuccess())
				$result->addErrors($dlvRes->getErrors());
		}

		if(isset($_POST["PAYMENT"]) && $_POST["PAYMENT"])
		{
			$payRes = Blocks\OrderPayment::updateData($order, $_POST['PAYMENT'], !$result->isSuccess());

			if(!$payRes->isSuccess())
				$result->addErrors($payRes->getErrors());
		}

		OrderEdit::fillOrderProperties($order, $_POST, $_FILES);

		if($isSavingOperation && $result->isSuccess())
		{
			$res = OrderEdit::saveCoupons($order->getUserId(), $_POST);

			if(!$res)
				$result->addError(new \Bitrix\Main\Entity\EntityError("Can't save coupons!"));

			/* To apply discounts depended on paysystems, or delivery services */
			if (!($basket = $order->getBasket()))
				throw new \Bitrix\Main\ObjectNotFoundException('Entity "Basket" not found');

			$res = $basket->refreshData(array('PRICE', 'QUANTITY', 'COUPONS'));

			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}

			$res = $order->verify();
			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}

			if ($result->isSuccess())
			{
				$res = $order->save();
				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}

			if ($result->isSuccess())
			{
				if(isset($_POST["BUYER_PROFILE_ID"]))
					$profileId = intval($_POST["BUYER_PROFILE_ID"]);
				else
					$profileId = 0;

				$profResult = OrderEdit::saveProfileData($profileId, $order, $_POST);
				\CSaleMobileOrderPush::send("ORDER_CREATED", array("ORDER_ID" => $order->getId()));

				$customTabber->SetArgs(array("ID" => $order->getId()));

				if (!$customTabber->Action())
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= "Custom tabber action unknown error!";
				}

				$customDraggableBlocks->setArgs(array('ORDER' => $order));

				if (!$customDraggableBlocks->action())
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= "Custom draggable block action unknown error!";
				}

				if(!empty($errorMessage))
					$_SESSION['SALE_ORDER_EDIT_ERROR'] = $errorMessage;

				if (
					isset($_POST["ABANDONED_USER_ID"])
					&& (int)$_POST["ABANDONED_USER_ID"] === $order->getUserId()
					&& (int)$_POST["ABANDONED_FUSER_ID"] > 0
				)
				{
					/** @var Sale\Basket $basketClass */
					$basketClass = $registry->getBasketClassName();

					$itemsDataList = $basketClass::getList(
						array(
							"filter" => array(
								"=ORDER_ID" => NULL,
								"=FUSER_ID" => (int)$_POST["ABANDONED_FUSER_ID"],
							),
							"select" => array("ID")
						)
					);

					while ($item = $itemsDataList->fetch())
					{
						Sale\Internals\BasketTable::deleteWithItems($item['ID']);
					}
				}

				if(isset($_POST["save"]))
				{
					$link
						->create()
						->setPageByType(Registry::SALE_ORDER)
						->setFilterParams(false)
						->fill()
						->redirect();
				}
				else
				{
					$link
						->create()
						->setPageByType(Registry::SALE_ORDER_EDIT)
						->setFilterParams(false)
						->setField('ID', $order->getId())
						->fill()
						->redirect();
				}
			}
		}
	}
	else
	{
		$result->addError(new \Bitrix\Main\Error(Loc::getMessage('SALE_OK_ORDER_CREATE_ERROR')));
	}
}
elseif($createWithProducts)
{
	$showProfiles = true;
	$formData = array(
		"USER_ID" => $_GET["USER_ID"],
		"SITE_ID" => $_GET["SITE_ID"]
	);


	$formData["PRODUCT"] = array();
	$basketCode = 1;
	$userProfiles = array();

	if(isset($_GET["product"]) && is_array($_GET["product"]))
	{
		$productParams = Blocks\OrderBasket::getProductsData(array_keys($_GET["product"]), $formData["SITE_ID"], array(), intval($_GET["USER_ID"]));

		foreach($_GET["product"] as $productId => $quantity)
		{
			if(
				!is_array($productParams[$productId])
				|| empty($productParams[$productId])
				|| intval($productParams[$productId]["PRODUCT_ID"]) <= 0
				|| $productParams[$productId]["MODULE"] == ''
			)
			{
				continue;
			}

			$formData["PRODUCT"][$basketCode] = $productParams[$productId];
			$formData["PRODUCT"][$basketCode]["BASKET_CODE"] = $basketCode;
			$formData["PRODUCT"][$basketCode]["QUANTITY"] = $quantity;
			$basketCode++;
		}
	}
	else
	{
		if(isset($_GET['FUSER_ID']) && intval($_GET['FUSER_ID']) > 0)
			$fuserId = $_GET['FUSER_ID'];
		else
			$fuserId = \Bitrix\Sale\Fuser::getIdByUserId($_GET["USER_ID"]);

		if(intval($fuserId) > 0)
		{
			$basketList = array();

			/** @var Sale\Basket $basketClass */
			$basketClass = $registry->getBasketClassName();
			$fakeBasket = $basketClass::create($_GET['SITE_ID']);

			$context = array(
				"SITE_ID" => $_GET['SITE_ID'],
			);

			if (!empty($_GET["USER_ID"]))
			{
				$context['USER_ID'] = $_GET["USER_ID"];
			}

			Bitrix\Main\Loader::includeModule('catalog');
			$basketFilter = array(
				'filter' => array(
					'LID' => $_GET['SITE_ID'],
					'FUSER_ID' => intval($fuserId),
					'DELAY' => "N",
					'ORDER_ID' => null,
					'SET_PARENT_ID' => false,
				),
				'select' => array('PRODUCT_ID', 'QUANTITY', 'CAN_BUY', 'NAME', 'MODULE', 'PRODUCT_PROVIDER_CLASS', 'CALLBACK_FUNC', 'PAY_CALLBACK_FUNC', 'PRICE', 'SUBSCRIBE'),
				'order' => array('ID' => 'ASC'),
			);

			$resBasketDataList = $basketClass::getList($basketFilter);
			while($basketData = $resBasketDataList->fetch())
			{
				if ($basketData['CAN_BUY'] != 'Y')
				{
					$result->addError(
						new \Bitrix\Main\Error(
							Loc::getMessage(
								'SALE_OK_ORDER_CREATE_ERROR_NO_PRODUCT',
								array('##NAME##' => $basketData['NAME'])
							)
						)
					);
					continue;
				}

				$basketFields = array(
					'PRODUCT_ID' => $basketData['PRODUCT_ID'],
					'QUANTITY' => $basketData['QUANTITY'],
					'SUBSCRIBE' => $basketData['SUBSCRIBE'],
				);

				if (!empty($basketData['MODULE']))
				{
					$basketFields['MODULE'] = $basketData['MODULE'];
				}

				if (!empty($basketData['PRODUCT_PROVIDER_CLASS']))
				{
					$basketFields['PRODUCT_PROVIDER_CLASS'] = $basketData['PRODUCT_PROVIDER_CLASS'];
				}

				if (!empty($basketData['CALLBACK_FUNC']))
				{
					$basketFields['CALLBACK_FUNC'] = $basketData['CALLBACK_FUNC'];
				}

				if (!empty($basketData['PAY_CALLBACK_FUNC']))
				{
					$basketFields['PAY_CALLBACK_FUNC'] = $basketData['PAY_CALLBACK_FUNC'];
				}

				$r = Catalog\Product\Basket::addProductToBasket($fakeBasket, $basketFields, $context);
				if ($r->isSuccess())
				{
					$resultData = $r->getData();
					if (isset($resultData['BASKET_ITEM']))
					{
						/** @var \Bitrix\Sale\BasketItem $basketItem */
						$basketItem = $resultData['BASKET_ITEM'];
						$basketCode = $basketItem->getBasketCode();
					}
					else
					{
						$result->addError(
							new \Bitrix\Main\Error(
								Loc::getMessage(
									'SALE_OK_ORDER_CREATE_ERROR_BASKET_ITEM_NOT_CREATED',
									array('##NAME##' => $basketData['NAME'])
								)
							)
						);
						continue;
					}
				}
				else
				{
					$result->addErrors($r->getErrors());
					continue;
				}

				$basketList[$basketCode] = $basketData;
			}

			$providerItemDataList = Sale\Provider::getProductData($fakeBasket, array('QUANTITY'));
			unset($fakeBasket);

			if (!empty($basketList))
			{
				foreach ($basketList as $basketCode => $basketItem)
				{
					if (empty($providerItemDataList[$basketCode]))
					{
						$result->addError(
							new \Bitrix\Main\Error(
								Loc::getMessage(
									'SALE_OK_ORDER_CREATE_ERROR_NO_PRODUCT',
									array('##NAME##' => $basketItem['NAME'])
								)
							)
						);
						continue;
					}

					$productId = $basketItem['PRODUCT_ID'];
					if ($basketItem['MODULE'] == 'catalog')
					{
						// Temporary fix for custom products
						$productParams = Blocks\OrderBasket::getProductsData(array($productId), $formData["SITE_ID"], array(), intval($_GET["USER_ID"]));
					}
					elseif (empty($basketItem['PRODUCT_PROVIDER_CLASS']))
					{
						$productParams[$productId] = $basketItem;
					}

					if(!is_array($productParams[$productId]) || empty($productParams[$productId]))
						continue;

					if($productParams[$productId]['PRODUCT_ID'] == '')
					{
						$result->addError(
							new \Bitrix\Main\Error(
								Loc::getMessage(
									'SALE_OK_ORDER_CREATE_ERROR_NO_PRODUCT',
									array('##NAME##' => $basketItem['NAME'])
								)
							)
						);
						continue;
					}

					$formData["PRODUCT"][$basketCode] = $productParams[$productId];
					$formData["PRODUCT"][$basketCode]["BASKET_CODE"] = $basketCode;
					$formData["PRODUCT"][$basketCode]["QUANTITY"] = $basketItem['QUANTITY'];
				}
			}
		}
	}

	if(empty($formData["PRODUCT"]))
		unset($formData["PRODUCT"]);

	$res = new \Bitrix\Sale\Result();
	$order = OrderEdit::createOrderFromForm($formData, $USER->GetID(), false, array(), $res);
	$userProfiles = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getUserProfiles($_GET['USER_ID']);

	//Just get first available profile
	if($order && !empty($userProfiles))
	{
		$propCollection = $order->getPropertyCollection();
		$ptList = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getBuyerTypesList($order->getSiteId());
		$ptIndex = 0;
		$userPersonTypeId = $order->getPersonTypeId();

		if(!empty($ptList[$userPersonTypeId]) && is_array($userProfiles[$userPersonTypeId]))
		{
			reset($userProfiles[$userPersonTypeId]);
			$userProfile = current($userProfiles[$userPersonTypeId]);
			$profileId = key($userProfiles[$userPersonTypeId]);
			$order->setPersonTypeId($userPersonTypeId);

			foreach($userProfile as $propId => $propValue)
			{
				$property = $propCollection->getItemByOrderPropertyId($propId);

				if($property)
				{
					try
					{
						$property->setValue($propValue);
					}
					catch(\Exception $e)
					{}
				}
			}
		}
	}

	if(!$order)
	{
		if(!$res->isSuccess())
			$result->addErrors($res->getErrors());
		else
			$result->addError(
				new \Bitrix\Main\Entity\EntityError(
					Loc::getMessage('SALE_OK_ORDER_CREATE_ERROR')
				)
			);
	}
}
elseif($isRestoringOrderOperation) // Restore order from archive
{
	$profileList = array();

	$archivedOrder = Sale\Archive\Manager::returnArchivedOrder((int)$_GET['restoreID']);

	/** @var Sale\OrderStatus $orderStatusClass */
	$orderStatusClass = $registry->getOrderStatusClassName();

	$allowedStatusUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

	if (!in_array($archivedOrder->getField("STATUS_ID"), $allowedStatusUpdate))
	{
		LocalRedirect("/bitrix/admin/sale_order_archive.php?lang=".LANGUAGE_ID);
	}

	if ($saleModulePermissions == 'P')
	{
		$userCompanyList = Sale\Services\Company\Manager::getUserCompanyList($USER->GetID());
		if (
			!in_array($archivedOrder->getField('COMPANY_ID'), $userCompanyList)
			&& $archivedOrder->getField('RESPONSIBLE_ID') !== $USER->GetID()
		)
		{
			LocalRedirect("/bitrix/admin/sale_order_archive.php?lang=".LANGUAGE_ID);
		}
	}

	//Create order for form from a returned archive
	$order = $orderClass::create($archivedOrder->getSiteId(), $archivedOrder->getUserId(), $archivedOrder->getCurrency());

	$availableFields = array_flip($orderClass::getAvailableFields());
	$orderFields = array_intersect_key($archivedOrder->getFieldValues(), $availableFields);
	$order->setFields($orderFields);

	//Copy properties to current order
	$propertyCollection = $order->getPropertyCollection();
	$userProfiles = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getUserProfiles($order->getUserId());
	if (empty($userProfiles[$order->getPersonTypeId()]))
	{
		$propertyArchivedCollection = $archivedOrder->getPropertyCollection();
		foreach ($propertyArchivedCollection as $propertyOld)
		{
			$profileList[$propertyOld->getField('ORDER_PROPS_ID')] = $propertyOld->getValue();
		}
	}
	else
	{
		$profileList = current($userProfiles[$order->getPersonTypeId()]);
		$profileId = key($userProfiles[$order->getPersonTypeId()]);
		$showProfiles = true;
	}

	foreach ($profileList as $id => $propertyProfileValue)
	{
		$property = $propertyCollection->getItemByOrderPropertyId($id);
		if($property)
		{
			try
			{
				$property->setValue($propertyProfileValue);
			}
			catch(\Exception $e)
			{}
		}
	}

	//Copy basket to current order
	$archivedBasket = $archivedOrder->getBasket();
	$archivedBasketItems = $archivedBasket->getBasketItems();
	//Check exists products in basket
	/** @var Sale\BasketItem $archivedItem */
	$errorMessage = "";
	foreach ($archivedBasketItems as $archivedItem)
	{
		$archivedItemModule = $archivedItem->getField('MODULE');
		if ($archivedItemModule == "sale")
			continue;
		Bitrix\Main\Loader::includeModule('catalog');
		$product = Catalog\ProductTable::getById($archivedItem->getProductId());
		if (!($product->fetch()))
		{
			$errorAbsentProductMessage .= Loc::getMessage(
				"ARCHIVE_ERROR_PRODUCT_NOT_FOUND",
				array(
					"#NAME#" => $archivedItem->getField("NAME"),
					"#ID#" => $archivedItem->getProductId(),
				)
			);
			$errorAbsentProductMessage .= "<br>";
		}
	}
	$order->setBasket($archivedBasket);

	//Fill one order's shipment from archived shipments (limit - creation with single shipment)
	$shipmentCollection = $order->getShipmentCollection();
	$archivedShipmentCollection = $archivedOrder->getShipmentCollection();
	/** @var \Bitrix\Sale\Shipment $archivedShipment */
	foreach ($archivedShipmentCollection as $archivedShipment)
	{
		if (!$archivedShipment->isSystem())
		{
			$shipmentItem = $shipmentCollection->createItem();
			$shipmentItem->setField('DELIVERY_ID', $archivedShipment->getDeliveryId());
			break;
		}
	}

	//Fill one order's payment from archived payments (limit - creation with single payment)
	$paymentCollection = $order->getPaymentCollection();
	$archivedPaymentCollection = $archivedOrder->getPaymentCollection();
	/** @var \Bitrix\Sale\Payment $archivedPayment */
	foreach ($archivedPaymentCollection as $archivedPayment)
	{
		if ($archivedPaymentCollection->count() > 1 && $archivedPayment->isInner())
			continue;
		/** @var \Bitrix\Sale\Payment $paymentItem */
		$paymentItem = $paymentCollection->createItem();
		$paymentItem->setField("PAY_SYSTEM_ID",$archivedPayment->getField("PAY_SYSTEM_ID"));
		break;

	}

	foreach ($profileList as $id => $propertyProfileValue)
	{
		$property = $propertyCollection->getItemByOrderPropertyId($id);
		if($property)
		{
			try
			{
				$property->setValue($propertyProfileValue);
			}
			catch(\Exception $e)
			{}
		}
	}
}
elseif($isCopyingOrderOperation) // copy order
{
	/** @var Sale\Order $originalOrder */
	$originalOrder = $orderClass::load($ID);
	if ($originalOrder)
	{
		$order = $orderClass::create($originalOrder->getSiteId(), $originalOrder->getUserId(), $originalOrder->getCurrency());
		$order->setPersonTypeId($originalOrder->getPersonTypeId());
		$userProfiles = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getUserProfiles($originalOrder->getUserId());

		if(!empty($userProfiles[$originalOrder->getPersonTypeId()]))
		{
			$profileList = current($userProfiles[$originalOrder->getPersonTypeId()]);
			$profileId = key($userProfiles[$originalOrder->getPersonTypeId()]);
			$showProfiles = true;
		}

		$originalPropCollection = $originalOrder->getPropertyCollection();
		$properties['PROPERTIES'] = array();
		$files = array();

		/** @var \Bitrix\Sale\PropertyValue $prop */
		foreach ($originalPropCollection as $prop)
		{
			if ($prop->getField('TYPE') == 'FILE')
			{
				$propValue = $prop->getValue();
				if ($propValue)
				{
					$files[] = CAllFile::MakeFileArray($propValue['ID']);
					$properties['PROPERTIES'][$prop->getPropertyId()] = $propValue['ID'];
				}
			}
			else
			{
				$properties['PROPERTIES'][$prop->getPropertyId()] = $prop->getValue();
			}
		}

		$propCollection = $order->getPropertyCollection();
		$propCollection->setValuesFromPost($properties, $files);
		$originalBasket = $originalOrder->getBasket();
		$originalBasketProviderData = Sale\Provider::getProductData($originalBasket);
		$originalBasketItems = $originalBasket->getBasketItems();

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();
		$basket = $basketClass::create($originalOrder->getSiteId());
		$basket->setFUserId($originalBasket->getFUserId());

		/** @var \Bitrix\Sale\BasketItem $originalBasketItem */
		foreach($originalBasketItems as $originalBasketItem)
		{
			$obBasketCode = $originalBasketItem->getBasketCode();
			$module = $originalBasketItem->getField("MODULE");
			$name = $originalBasketItem->getField("NAME");
			$isCatalogProductDeleted = !isset($originalBasketProviderData[$obBasketCode])&& $module == 'catalog';

			if($isCatalogProductDeleted)
			{
				//Make it custom
				$module = '';
				//And warn
				$errorMessage .= Loc::getMessage('SALE_OK_ORDER_COPY_ERROR_BASKET_ITEM_NOT_FOUND',[
						"#NAME#" => $name
					])."<br>\n";
			}

			$item = $basket->createItem($module, $originalBasketItem->getProductId());
			$item->setField('NAME', $name);

			$item->setFields(
				array_intersect_key(
					$originalBasketItem->getFields()->getValues(),
					array_flip(
						$originalBasketItem->getAvailableFields()
					)
				)
			);

			if($isCatalogProductDeleted)
			{
				$item->setField('PRODUCT_PROVIDER_CLASS', '');
			}

			$item->getPropertyCollection()->setProperty(
				$originalBasketItem->getPropertyCollection()->getPropertyValues()
			);
		}

		$res = $order->setBasket($basket);

		if(!$res->isSuccess())
			$result->addErrors($res->getErrors());

		$paymentCollection = $originalOrder->getPaymentCollection();
		$originalPayment = $paymentCollection->current();

		if ($originalPayment)
		{
			$payment = $order->getPaymentCollection()->createItem();
			/** @var \Bitrix\Sale\Payment $payment */
			$payment->setField('PAY_SYSTEM_ID', $originalPayment->getPaymentSystemId());
		}

		$originalDeliveryId = 0;
		$originalStoreId = 0;
		$shipmentCollection = $originalOrder->getShipmentCollection();
		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$originalDeliveryId = $shipment->getDeliveryId();
				$customPriceDelivery = $shipment->getField('CUSTOM_PRICE_DELIVERY');
				$basePrice = $shipment->getField('BASE_PRICE_DELIVERY');
				$originalStoreId = $shipment->getStoreId();
				break;
			}
		}
		if ($originalDeliveryId > 0)
		{
			$shipment = $order->getShipmentCollection()->createItem();
			$shipment->setField('DELIVERY_ID', $originalDeliveryId);

			if(intval($originalStoreId) > 0)
				$shipment->setStoreId($originalStoreId);

			$shipment->setBasePriceDelivery($basePrice, ($customPriceDelivery == 'Y'));
		}

		$propCollection->setValuesFromPost($properties, $files);

		$order->getDiscount()->calculate();
	}
}

if(!$order)
{
	$order = $orderClass::create($siteId);
	$order->setPersonTypeId(
		Blocks\OrderBuyer::getDefaultPersonType(
			$siteId
		)
	);
}

if($siteName <> '')
	$APPLICATION->SetTitle(str_replace("##SITE##", $siteName, Loc::getMessage("SALE_OK_TITLE_SITE")));
else
	$APPLICATION->SetTitle(Loc::getMessage("SALE_OK_TITLE_NO_SITE"));

CUtil::InitJSCore();
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_edit.js");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

Blocks\OrderBasket::getCatalogMeasures();
// context menu
$aMenu = array();

if ($isRestoringOrderOperation)
{
	$aMenu[] = array(
		"ICON" => "btn_list",
		"TEXT" => Loc::getMessage("SALE_OK_ARCHIVE_LIST"),
		"TITLE"=> Loc::getMessage("SALE_OK_ARCHIVE_LIST_TITLE"),
		"LINK" => "/bitrix/admin/sale_order_archive.php?lang=".LANGUAGE_ID
	);
}
else
{
	$aMenu[] = array(
		"ICON" => "btn_list",
		"TEXT" => Loc::getMessage("SALE_OK_LIST"),
		"TITLE"=> Loc::getMessage("SALE_OK_LIST_TITLE"),
		"LINK" => $link
			->create()
			->setPageByType(Registry::SALE_ORDER_VIEW)
			->setFilterParams(false)
			->fill()
			->build()
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

//errors

if(!empty($_SESSION['SALE_ORDER_EDIT_ERROR']))
{
	$errorMessage .= $_SESSION['SALE_ORDER_EDIT_ERROR']."<br>\n";
	unset($_SESSION['SALE_ORDER_EDIT_ERROR']);
}

if(!$result->isSuccess() && !$needFieldsRestore)
	foreach($result->getErrors() as $error)
		$errorMessage .= $error->getMessage()."<br>\n";

if(!empty($errorMessage))
{
	$admMessage = new CAdminMessage($errorMessage);
	echo $admMessage->Show();
}

if($request->get('HANDLER') == Sale\Exchange\Integration\HandlerType::ORDER_NEW
	&& $request->get('entityTypeId')>0
	&& $request->get('entityId')>0)
{
	$admMessage = new CAdminMessage(['MESSAGE'=>Loc::getMessage('SALE_INTEGRATION_TITLE'), 'TYPE'=>'OK', 'DETAILS'=>Loc::getMessage('SALE_INTEGRATION_DETAILS').$request->get('entityId')]);
	echo $admMessage->Show();
}

//prepare blocks order
$defaultBlocksOrder = array(
	"basket",
	"buyer",
	"financeinfo",
	"delivery",
	"payment",
	"relprops",
	"additional",
	"statusorder",
);

$formId = "sale_order_create";
$basketPrefix = "sale_order_basket";

$orderBasket = new Blocks\OrderBasket($order,"BX.Sale.Admin.OrderBasketObj", $basketPrefix);

echo OrderEdit::getScripts($order, $formId);
echo Blocks\OrderBuyer::getScripts();
echo Blocks\OrderAdditional::getScripts();
echo Blocks\OrderPayment::getScripts();
echo Blocks\OrderShipment::getScripts();
echo Blocks\OrderFinanceInfo::getScripts();
echo $orderBasket->getScripts(false);
echo $customDraggableBlocks->getScripts();

// navigation socket
?><div id="sale-order-edit-block-fast-nav-socket"></div><?


$aTabs = array(
	array("DIV" => "tab_order", "TAB" => Loc::getMessage("SALE_OK_TAB_ORDER"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y"),
);

$url = $link
	->create()
	->setPage($APPLICATION->GetCurPage())
	->setFilterParams(false)
	->setField('entityTypeId', $request->get('entityTypeId'))
	->setField('entityId', $request->get('entityId'))
	->setField('HANDLER', $request->get('HANDLER'))
	->fill()
	->setField('SITE_ID', $siteId)
	->build();

?><form method="POST" action="<?=$url?>" name="<?=$formId?>_form" id="<?=$formId?>_form" enctype="multipart/form-data"><?
$tabControl = new CAdminTabControlDrag($formId, $aTabs, $moduleId, false, true);
$tabControl->AddTabs($customTabber);
$tabControl->Begin();
//TAB order --
$tabControl->BeginNextTab();
$customFastNavItems = array();
$customBlocksOrder = array();
$fastNavItems = array();

foreach($customDraggableBlocks->getBlocksBrief() as $blockId => $blockParams)
{
	$defaultBlocksOrder[] = $blockId;
	$customFastNavItems[$blockId] = $blockParams['TITLE'];
	$customBlocksOrder[] = $blockId;
}

$blocksOrder = $tabControl->getCurrentTabBlocksOrder($defaultBlocksOrder);
$customNewBlockIds = array_diff($customBlocksOrder, $blocksOrder);
$blocksOrder = array_merge($blocksOrder, $customNewBlockIds);

foreach($blocksOrder as $item)
{
	if(isset($customFastNavItems[$item]))
		$fastNavItems[$item] = $customFastNavItems[$item];
	else
		$fastNavItems[$item] = Loc::getMessage("SALE_OK_BLOCK_TITLE_".toUpper($item));
}

?>
<tr><td>
	<input type="hidden" id="SITE_ID" name="SITE_ID" value="<?=htmlspecialcharsbx($siteId)?>">
	<input type="hidden" id="OLD_USER_ID" name="OLD_USER_ID" value="0">
	<input type="hidden" name="BASKET_PREFIX" value="<?=$basketPrefix?>">
	<?
	if ($_REQUEST["ABANDONED"] === 'Y')
	{
		?>
		<input type="hidden" id="ABANDONED_USER_ID" name="ABANDONED_USER_ID" value="<?=(int)$_REQUEST["USER_ID"]?>">
		<input type="hidden" id="ABANDONED_FUSER_ID" name="ABANDONED_FUSER_ID" value="<?=(int)$_REQUEST["FUSER_ID"]?>">
		<?
	}
	?>
	<?=bitrix_sessid_post()?>
	<div style="position: relative; vertical-align: top">
		<?$tabControl->DraggableBlocksStart();?>
		<?
		foreach ($blocksOrder as $blockCode)
		{
			echo '<a id="'.$blockCode.'" class="adm-sale-fastnav-anchor"></a>';
			$tabControl->DraggableBlockBegin($fastNavItems[$blockCode], $blockCode);
			switch ($blockCode)
			{
				case "basket":
					if($errorAbsentProductMessage <> '')
					{
						$admMessage = new CAdminMessage($errorAbsentProductMessage);
						echo $admMessage->Show();
					}
					echo $orderBasket->getEdit(false);
					break;
				case "buyer":
					echo Blocks\OrderBuyer::getEdit($order, $showProfiles, $profileId);
					break;
				case "delivery":

					$shipments = $order->getShipmentCollection();

					if(count($shipments) < 2)
						$order->getShipmentCollection()->createItem();

					/** @var \Bitrix\Sale\Shipment  $shipment*/
					foreach ($shipments as $shipment)
						if (!$shipment->isSystem())
							echo Blocks\OrderShipment::getEdit($shipment, 0, '', $_POST['SHIPMENT'][1]);

					break;
				case "payment":
					$payments = $order->getPaymentCollection();

					if(count($payments) == 0)
						$order->getPaymentCollection()->createItem();

					$index = 0;
					foreach ($payments as $payment)
						echo Blocks\OrderPayment::getEdit($payment, ++$index, $_POST['PAYMENT'][$index]);

					echo Blocks\OrderPayment::createButtonAddPayment(['formType'=>'edit']);
					break;
				case 'relprops' :
					echo Blocks\OrderBuyer::getPropsEdit($order);
					break;
				case "financeinfo":
					echo Blocks\OrderFinanceInfo::getView($order);
					break;
				case "additional":
					echo Blocks\OrderAdditional::getEdit($order, $formId."_form", 'ORDER', (!empty($_POST['ORDER']) ? $_POST['ORDER'] : array()));
					break;
				case "statusorder":
					$orderStatusClass = $registry->getOrderStatusClassName();
					echo Blocks\OrderStatus::getEditSimple($USER->GetID(), 'STATUS_ID', $orderStatusClass::getInitialStatus());
					break;
				default:
					echo $customDraggableBlocks->getBlockContent($blockCode, $tabControl->selectedTab);
					break;
			}
			$tabControl->DraggableBlockEnd();
		}
		?>
	</div>
</td></tr>
<?

$tabControl->EndTab();

$url = $link
	->create()
	->setPageByType(Sale\Exchange\Integration\Admin\Registry::SALE_ORDER_CREATE)
	->setFilterParams(false)
	->fill()
	->setField('entityTypeId', $request->get('entityTypeId'))
	->setField('entityId', $request->get('entityId'))
	->setField('HANDLER', $request->get('HANDLER'))
	->setField('SITE_ID', $siteId)
	->build();

$tabControl->Buttons(
	array(
		"back_url" => $url)
);

$tabControl->End();
?>
<div style="display: none;">
	<?=$orderBasket->getSettingsDialogContent();?>
</div>

<div style="display: none;"><?=OrderEdit::getFastNavigationHtml($fastNavItems);?></div>

<script type="text/javascript">
	BX.ready( function(){
		BX.Sale.Admin.OrderEditPage.setFixHashCorrection();

		//place navigation data to navigation socket
		BX('sale-order-edit-block-fast-nav-socket').appendChild(
			BX('sale-order-edit-block-fast-nav')
		);
	});
</script>

<?if(!$result->isSuccess() || $needFieldsRestore):?>
	<script type="text/javascript">
		BX.ready( function(){
			BX.Sale.Admin.OrderEditPage.restoreFormData(
				<?=CUtil::PhpToJSObject(OrderEdit::restoreFieldsNames(
					array_diff_key($_POST, array("USER_ID" => true))
				));
				?>
			);
		});
	</script>
<?endif;?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");