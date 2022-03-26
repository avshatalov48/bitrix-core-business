<?php

CModule::AddAutoloadClasses(
	"sale",
	[
		"sale" => "install/index.php",
		"CAllSaleDelivery" => "general/delivery.php",
		"CSaleDelivery" => "mysql/delivery.php",
		"CSaleDeliveryHandler" => "mysql/delivery_handler.php",
		"CSaleDeliveryHelper" => "general/delivery_helper.php",
		"CSaleDelivery2PaySystem" => "general/delivery_2_pay_system.php",
		"CSaleLocation" => "mysql/location.php",
		"CSaleLocationGroup" => "mysql/location_group.php",

		"CSaleBasket" => "mysql/basket.php",
		"CSaleBasketHelper" => "general/basket_helper.php",
		"CSaleUser" => "mysql/basket.php",

		"CSaleOrder" => "mysql/order.php",
		"CSaleOrderPropsGroup" => "mysql/order_props_group.php",
		"CSaleOrderPropsVariant" => "mysql/order_props_variant.php",
		"CSaleOrderUserProps" => "mysql/order_user_props.php",
		"CSaleOrderUserPropsValue" => "mysql/order_user_props_value.php",
		"CSaleOrderTax" => "mysql/order_tax.php",
		"CSaleOrderHelper" => "general/order_helper.php",

		"CSalePaySystem" => "mysql/pay_system.php",
		"CSalePaySystemAction" => "mysql/pay_system_action.php",
		"CSalePaySystemsHelper" => "general/pay_system_helper.php",
		"CSalePaySystemTarif" => "general/pay_system_tarif.php",

		"CSaleTax" => "mysql/tax.php",
		"CSaleTaxRate" => "mysql/tax_rate.php",

		"CSalePersonType" => "mysql/person_type.php",
		"CSaleDiscount" => "mysql/discount.php",
		"CSaleBasketDiscountConvert" => "general/step_operations.php",
		"CSaleDiscountReindex" => "general/step_operations.php",
		"CSaleDiscountConvertExt" => "general/step_operations.php",
		"CSaleUserAccount" => "mysql/user.php",
		"CSaleUserTransact" => "mysql/user_transact.php",
		"CSaleUserCards" => "mysql/user_cards.php",
		"CSaleRecurring" => "mysql/recurring.php",


		"CSaleLang" => "mysql/settings.php",
		"CSaleGroupAccessToSite" => "mysql/settings.php",
		"CSaleGroupAccessToFlag" => "mysql/settings.php",

		"CSaleAuxiliary" => "mysql/auxiliary.php",

		"CSaleAffiliate" => "mysql/affiliate.php",
		"CSaleAffiliatePlan" => "mysql/affiliate_plan.php",
		"CSaleAffiliatePlanSection" => "mysql/affiliate_plan_section.php",
		"CSaleAffiliateTier" => "mysql/affiliate_tier.php",
		"CSaleAffiliateTransact" => "mysql/affiliate_transact.php",
		"CSaleExport" => "general/export.php",
		"ExportOneCCRM" => "general/export.php",
		"CSaleOrderLoader" => "general/order_loader.php",

		"CSaleMeasure" => "general/measurement.php",
		"CSaleProduct" => "mysql/product.php",

		"CSaleViewedProduct" => "mysql/product.php",

		"CSaleHelper" => "general/helper.php",
		"CSaleMobileOrderUtils" => "general/mobile_order.php",
		"CSaleMobileOrderPull" => "general/mobile_order.php",
		"CSaleMobileOrderPush" => "general/mobile_order.php",
		"CSaleMobileOrderFilter" => "general/mobile_order.php",

		"CBaseSaleReportHelper" => "general/sale_report_helper.php",
		"CSaleReportSaleOrderHelper" => "general/sale_report_helper.php",
		"CSaleReportUserHelper" => "general/sale_report_helper.php",
		"CSaleReportSaleFuserHelper" => "general/sale_report_helper.php",

		"IBXSaleProductProvider" => "general/product_provider.php",
		"CSaleStoreBarcode" => "mysql/store_barcode.php",

		"CSaleOrderChange" => "mysql/order_change.php",
		"CSaleOrderChangeFormat" => "general/order_change.php",

		"\\Bitrix\\Sale\\Internals\\FuserTable" => "lib/internals/fuser.php",
		"\\Bitrix\\Sale\\Fuser" => "lib/fuser.php",

		// begin lists
		'\Bitrix\Sale\Internals\Input\Manager' => 'lib/internals/input.php',
		'\Bitrix\Sale\Internals\Input\Base'    => 'lib/internals/input.php',
		'\Bitrix\Sale\Internals\Input\File'    => 'lib/internals/input.php',
		'\Bitrix\Sale\Internals\Input\StringInput'    => 'lib/internals/input.php',

		'\Bitrix\Sale\Internals\SiteCurrencyTable' => 'lib/internals/sitecurrency.php',

		'CSaleStatus' => 'general/status.php',
		'\Bitrix\Sale\StatusBase' => 'lib/statusbase.php',
		'\Bitrix\Sale\OrderStatus' => 'lib/orderstatus.php',
		'\Bitrix\Sale\DeliveryStatus' => 'lib/deliverystatus.php',
		'\Bitrix\Sale\Internals\StatusTable' => 'lib/internals/status.php',
		'\Bitrix\Sale\Internals\StatusLangTable' => 'lib/internals/status_lang.php',
		'\Bitrix\Sale\Internals\StatusGroupTaskTable' => 'lib/internals/status_grouptask.php',
		'CSaleOrderProps'                                => 'general/order_props.php',
		'CSaleOrderPropsAdapter'                         => 'general/order_props.php',
		'CSaleOrderPropsValue'                           => 'mysql/order_props_values.php',
		'\Bitrix\Sale\PropertyValueCollection'           => 'lib/propertyvaluecollection.php',
		'\Bitrix\Sale\Internals\OrderPropsTable'         => 'lib/internals/orderprops.php',
		'\Bitrix\Sale\Internals\OrderPropsGroupTable'    => 'lib/internals/orderprops_group.php',
		'\Bitrix\Sale\Internals\OrderPropsValueTable'    => 'lib/internals/orderprops_value.php',
		'\Bitrix\Sale\Internals\OrderPropsVariantTable'  => 'lib/internals/orderprops_variant.php',
		'\Bitrix\Sale\Internals\OrderPropsRelationTable' => 'lib/internals/orderprops_relation.php',
		'\Bitrix\Sale\Internals\UserPropsValueTable'     => 'lib/internals/userpropsvalue.php',
		'\Bitrix\Sale\Internals\UserPropsTable'          => 'lib/internals/userprops.php',
		'\Bitrix\Sale\BusinessValue'                            => 'lib/businessvalue.php',
		'\Bitrix\Sale\IBusinessValueProvider'                   => 'lib/businessvalueproviderinterface.php',
		'\Bitrix\Sale\Internals\BusinessValueTable'             => 'lib/internals/businessvalue.php',
		'\Bitrix\Sale\Internals\BusinessValuePersonDomainTable' => 'lib/internals/businessvalue_persondomain.php',
		'\Bitrix\Sale\Internals\BusinessValueCode1CTable'       => 'lib/internals/businessvalue_code_1c.php',
		'\Bitrix\Sale\Internals\PaySystemActionTable' => 'lib/internals/paysystemaction.php',
		'\Bitrix\Sale\Internals\PaySystemInner' => 'lib/internals/paysysteminner.php',
		'\Bitrix\Sale\Internals\DeliveryPaySystemTable' => 'lib/internals/delivery_paysystem.php',
		'\Bitrix\Sale\UserMessageException' => 'lib/exception.php',
		// end lists

		"\\Bitrix\\Sale\\Configuration" => "lib/configuration.php",
		"\\Bitrix\\Sale\\Order" => "lib/order.php",
		"\\Bitrix\\Sale\\PersonType" => "lib/persontype.php",

		"CSaleReportSaleGoodsHelper" => "general/sale_report_helper.php",
		"CSaleReportSaleProductHelper" => "general/sale_report_helper.php",

		"\\Bitrix\\Sale\\Internals\\ProductTable" => "lib/internals/product.php",
		"\\Bitrix\\Sale\\Internals\\GoodsSectionTable" => "lib/internals/goodssection.php",
		"\\Bitrix\\Sale\\Internals\\SectionTable" => "lib/internals/section.php",
		"\\Bitrix\\Sale\\Internals\\StoreProductTable" => "lib/internals/storeproduct.php",

		"\\Bitrix\\Sale\\SalesZone" => "lib/saleszone.php",
		"Bitrix\\Sale\\Internals\\OrderDeliveryReqTable" => "lib/internals/orderdeliveryreq.php",
		"\\Bitrix\\Sale\\Internals\\OrderDeliveryReqTable" => "lib/internals/orderdeliveryreq.php",

		"Bitrix\\Sale\\SenderEventHandler" => "lib/senderconnector.php",
		"Bitrix\\Sale\\SenderConnectorBuyer" => "lib/senderconnector.php",

		"\\Bitrix\\Sale\\UserConsent" => "lib/userconsent.php",

		"\\Bitrix\\Sale\\Internals\\Product2ProductTable" => "lib/internals/product2product.php",

		"Bitrix\\Sale\\Internals\\OrderProcessingTable" => "lib/internals/orderprocessing.php",

		"\\Bitrix\\Sale\\OrderBase" => "lib/orderbase.php",
		"\\Bitrix\\Sale\\Internals\\Entity" => "lib/internals/entity.php",
		"\\Bitrix\\Sale\\Internals\\EntityCollection" => "lib/internals/entitycollection.php",
		"\\Bitrix\\Sale\\Internals\\CollectionBase" => "lib/internals/collectionbase.php",

		"\\Bitrix\\Sale\\Shipment" => "lib/shipment.php",
		"\\Bitrix\\Sale\\ShipmentCollection" => "lib/shipmentcollection.php",
		"\\Bitrix\\Sale\\ShipmentItemCollection" => "lib/shipmentitemcollection.php",
		"\\Bitrix\\Sale\\ShipmentItem" => "lib/shipmentitem.php",
		"\\Bitrix\\Sale\\ShipmentItemStoreCollection" => "lib/shipmentitemstorecollection.php",
		"\\Bitrix\\Sale\\ShipmentItemStore" => "lib/shipmentitemstore.php",

		"\\Bitrix\\Sale\\PaymentCollectionBase" => "lib/internals/paymentcollectionbase.php",
		"\\Bitrix\\Sale\\PaymentCollection" => "lib/paymentcollection.php",
		"\\Bitrix\\Sale\\Payment" => "lib/payment.php",
		"\\Bitrix\\Sale\\PaysystemService" => "lib/paysystemservice.php",
		"\\Bitrix\\Sale\\Internals\\Fields" => "lib/internals/fields.php",
		"\\Bitrix\\Sale\\Result" => "lib/result.php",
		"\\Bitrix\\Sale\\ResultError" => "lib/result.php",
		"\\Bitrix\\Sale\\ResultSerializable" => "lib/resultserializable.php",
		"\\Bitrix\\Sale\\EventActions" => "lib/eventactions.php",

		"\\Bitrix\\Sale\\BasketBase" => "lib/basketbase.php",
		"\\Bitrix\\Sale\\BasketItemBase" => "lib/basketitembase.php",
		"\\Bitrix\\Sale\\Basket" => "lib/basket.php",

		"\\Bitrix\\Sale\\Internals\\BasketItemBase" => "lib/internals/basketitembase.php",
		"\\Bitrix\\Sale\\BasketItem" => "lib/basketitem.php",
		"\\Bitrix\\Sale\\BasketBundleCollection" => "lib/basketbundlecollection.php",

		"\\Bitrix\\Sale\\OrderProperties" => "lib/orderprops.php",
		"\\Bitrix\\Sale\\PropertyValue" => "lib/propertyvalue.php",

		"\\Bitrix\\Sale\\Compatible\\Internals\\EntityCompatibility" => "lib/compatible/internals/entitycompatibility.php",
		"\\Bitrix\\Sale\\Compatible\\OrderCompatibility" => "lib/compatible/ordercompatibility.php",
		"\\Bitrix\\Sale\\Compatible\\BasketCompatibility" => "lib/compatible/basketcompatibility.php",
		"\\Bitrix\\Sale\\Compatible\\EventCompatibility" => "lib/compatible/eventcompatibility.php",

		'\Bitrix\Sale\Compatible\OrderQuery'   => 'lib/compatible/compatible.php',
		'\Bitrix\Sale\Compatible\OrderQueryLocation'   => 'lib/compatible/compatible.php',
		'\Bitrix\Sale\Compatible\FetchAdapter' => 'lib/compatible/compatible.php',
		'\Bitrix\Sale\Compatible\Test'         => 'lib/compatible/compatible.php',

		"\\Bitrix\\Sale\\OrderUserProperties" => "lib/userprops.php",

		"\\Bitrix\\Sale\\BasketPropertiesCollectionBase" => "lib/basketpropertiesbase.php",
		"\\Bitrix\\Sale\\BasketPropertiesCollection" => "lib/basketproperties.php",
		"\\Bitrix\\Sale\\BasketPropertyItemBase" => "lib/basketpropertiesitembase.php",
		"\\Bitrix\\Sale\\BasketPropertyItem" => "lib/basketpropertiesitem.php",

		"\\Bitrix\\Sale\\Tax" => "lib/tax.php",

		"\\Bitrix\\Sale\\Internals\\OrderTable" => "lib/internals/order.php",

		"\\Bitrix\\Sale\\Internals\\BasketTable" => "lib/internals/basket.php",

		"\\Bitrix\\Sale\\Internals\\ShipmentTable" => "lib/internals/shipment.php",
		"\\Bitrix\\Sale\\Internals\\ShipmentItemTable" => "lib/internals/shipmentitem.php",

		"\\Bitrix\\Sale\\Internals\\PaySystemServiceTable" => "lib/internals/paysystemservice.php",
		"\\Bitrix\\Sale\\Internals\\PaymentTable" => "lib/internals/payment.php",

		"\\Bitrix\\Sale\\Internals\\ShipmentItemStoreTable" => "lib/internals/shipmentitemstore.php",
		"\\Bitrix\\Sale\\Internals\\ShipmentExtraService" => "lib/internals/shipmentextraservice.php",

		"\\Bitrix\\Sale\\Internals\\OrderUserPropertiesTable" => "lib/internals/userprops.php",

		"\\Bitrix\\Sale\\Internals\\CollectableEntity" => "lib/internals/collectableentity.php",

		"\\Bitrix\\Sale\\Provider" => "lib/provider.php",
		"\\Bitrix\\Sale\\ProviderBase" => "lib/providerbase.php",

		'\Bitrix\Sale\Internals\Catalog\Provider' => "lib/internals/catalog/provider.php",
		'\Bitrix\Sale\SaleProviderBase' => "lib/saleproviderbase.php",
		'Bitrix\Sale\SaleProviderBase' => "lib/saleproviderbase.php",
		'\Bitrix\Sale\Internals\TransferDataProvider' => "lib/internals/transferdataprovider.php",
		'\Bitrix\Sale\Internals\PoolQuantity' => "lib/internals/poolquantity.php",

		'\Bitrix\Sale\Internals\ProviderCreator' => "lib/internals/providercreator.php",
		'\Bitrix\Sale\Internals\ProviderBuilderBase' => "lib/internals/providerbuilderbase.php",
		'\Bitrix\Sale\Internals\ProviderBuilder' => "lib/internals/providerbuilder.php",
		'\Bitrix\Sale\Internals\ProviderBuilderCompatibility' => "lib/internals/providerbuildercompatibility.php",


		"\\Bitrix\\Sale\\OrderHistory" => "lib/orderhistory.php",

		'\Bitrix\Sale\Internals\CallbackRegistryTable' => "lib/internals/callbackregistry.php",

		"\\Bitrix\\Sale\\Internals\\BasketPropertyTable" => "lib/internals/basketproperties.php",
		"\\Bitrix\\Sale\\Internals\\CompanyTable" => "lib/internals/company.php",
		"\\Bitrix\\Sale\\Internals\\CompanyGroupTable" => "lib/internals/companygroup.php",
		"\\Bitrix\\Sale\\Internals\\CompanyResponsibleGroupTable" => "lib/internals/companyresponsiblegroup.php",

		"\\Bitrix\\Sale\\Internals\\PersonTypeTable" => "lib/internals/persontype.php",
		"\\Bitrix\\Sale\\Internals\\PersonTypeSiteTable" => "lib/internals/persontypesite.php",

		"\\Bitrix\\Sale\\Internals\\Pool" => "lib/internals/pool.php",
		"\\Bitrix\\Sale\\Internals\\UserBudgetPool" => "lib/internals/userbudgetpool.php",
		"\\Bitrix\\Sale\\Internals\\EventsPool" => "lib/internals/eventspool.php",
		"\\Bitrix\\Sale\\Internals\\Events" => "lib/internals/events.php",

		"\\Bitrix\\Sale\\PriceMaths" => "lib/pricemaths.php",
		"\\Bitrix\\Sale\\BasketComponentHelper" => "lib/basketcomponenthelper.php",
		"\\Bitrix\\Sale\\Registry" => "lib/registry.php",

		"IPaymentOrder" => "lib/internals/paymentinterface.php",
		"IShipmentOrder" => "lib/internals/shipmentinterface.php",
		"IEntityMarker" => "lib/internals/entitymarkerinterface.php",

		//archive
		"\\Bitrix\\Sale\\Internals\\OrderArchiveTable" => "lib/internals/orderarchive.php",
		"\\Bitrix\\Sale\\Internals\\BasketArchiveTable" => "lib/internals/basketarchive.php",
		"\\Bitrix\\Sale\\Internals\\OrderArchivePackedTable" => "lib/internals/orderarchivepacked.php",
		"\\Bitrix\\Sale\\Internals\\BasketArchivePackedTable" => "lib/internals/basketarchivepacked.php",
		"\\Bitrix\\Sale\\Archive\\Manager" => "lib/archive/manager.php",
		"\\Bitrix\\Sale\\Archive\\Recovery\\Base" => "lib/archive/recovery/base.php",
		"\\Bitrix\\Sale\\Archive\\Recovery\\Scheme" => "lib/archive/recovery/scheme.php",
		"\\Bitrix\\Sale\\Archive\\Recovery\\Version1" => "lib/archive/recovery/version1.php",


		"Bitrix\\Sale\\Tax\\RateTable" => "lib/tax/rate.php",

		////////////////////////////
		// new location 2.0
		////////////////////////////

		// data entities
		"Bitrix\\Sale\\Location\\LocationTable" => "lib/location/location.php",
		"Bitrix\\Sale\\Location\\Tree" => "lib/location/tree.php",
		"Bitrix\\Sale\\Location\\TypeTable" => "lib/location/type.php",
		"Bitrix\\Sale\\Location\\GroupTable" => "lib/location/group.php",
		"Bitrix\\Sale\\Location\\ExternalTable" => "lib/location/external.php",
		"Bitrix\\Sale\\Location\\ExternalServiceTable" => "lib/location/externalservice.php",

		// search
		"Bitrix\\Sale\\Location\\Search\\Finder" => "lib/location/search/finder.php",
		"Bitrix\\Sale\\Location\\Search\\WordTable" => "lib/location/search/word.php",
		"Bitrix\\Sale\\Location\\Search\\ChainTable" => "lib/location/search/chain.php",
		"Bitrix\\Sale\\Location\\Search\\SiteLinkTable" => "lib/location/search/sitelink.php",

		// lang entities
		"Bitrix\\Sale\\Location\\Name\\NameEntity" => "lib/location/name/nameentity.php",
		"Bitrix\\Sale\\Location\\Name\\LocationTable" => "lib/location/name/location.php",
		"Bitrix\\Sale\\Location\\Name\\TypeTable" => "lib/location/name/type.php",
		"Bitrix\\Sale\\Location\\Name\\GroupTable" => "lib/location/name/group.php",

		// connector from locations to other entities
		"Bitrix\\Sale\\Location\\Connector" => "lib/location/connector.php",

		// link entities
		"Bitrix\\Sale\\Location\\GroupLocationTable" => "lib/location/grouplocation.php",
		"Bitrix\\Sale\\Location\\SiteLocationTable" => "lib/location/sitelocation.php",
		"Bitrix\\Sale\\Location\\DefaultSiteTable" => "lib/location/defaultsite.php",

		// db util
		"Bitrix\\Sale\\Location\\DB\\CommonHelper" => "lib/location/db/commonhelper.php",
		"Bitrix\\Sale\\Location\\DB\\Helper" => "lib/location/db/mysql/helper.php",
		"Bitrix\\Sale\\Location\\DB\\BlockInserter" => "lib/location/db/blockinserter.php",

		// admin logic
		"Bitrix\\Sale\\Location\\Admin\\Helper" => "lib/location/admin/helper.php",
		"Bitrix\\Sale\\Location\\Admin\\NameHelper" => "lib/location/admin/namehelper.php",
		"Bitrix\\Sale\\Location\\Admin\\LocationHelper" => "lib/location/admin/locationhelper.php",
		"Bitrix\\Sale\\Location\\Admin\\TypeHelper" => "lib/location/admin/typehelper.php",
		"Bitrix\\Sale\\Location\\Admin\\GroupHelper" => "lib/location/admin/grouphelper.php",
		"Bitrix\\Sale\\Location\\Admin\\DefaultSiteHelper" => "lib/location/admin/defaultsitehelper.php",
		"Bitrix\\Sale\\Location\\Admin\\SiteLocationHelper" => "lib/location/admin/sitelocationhelper.php",
		"Bitrix\\Sale\\Location\\Admin\\ExternalServiceHelper" => "lib/location/admin/externalservicehelper.php",
		"Bitrix\\Sale\\Location\\Admin\\SearchHelper" => "lib/location/admin/searchhelper.php",


		// util
		"Bitrix\\Sale\\Location\\Util\\Process" => "lib/location/util/process.php",
		"Bitrix\\Sale\\Location\\Util\\CSVReader" => "lib/location/util/csvreader.php",
		"Bitrix\\Sale\\Location\\Util\\Assert" => "lib/location/util/assert.php",

		// processes for step-by-step actions
		"Bitrix\\Sale\\Location\\Import\\ImportProcess" => "lib/location/import/importprocess.php",
		"Bitrix\\Sale\\Location\\Search\\ReindexProcess" => "lib/location/search/reindexprocess.php",

		// exceptions
		"\\Bitrix\\Sale\\Location\\Tree\\NodeNotFoundException" => "lib/location/tree/exception.php",
		"\\Bitrix\\Sale\\Location\\Tree\\NodeIncorrectException" => "lib/location/tree/exception.php",
		"\\Bitrix\\Sale\\Location\\Exception" => "lib/location/exception.php",

		// old
		"CSaleProxyAdminResult" => "general/proxyadminresult.php", // for admin
		"CSaleProxyAdminUiResult" => "general/proxyadminresult.php",
		"CSaleProxyResult" => "general/proxyresult.php", // for public
		// other
		"Bitrix\\Sale\\Location\\Migration\\CUpdaterLocationPro" => "lib/location/migration/migrate.php", // class of migrations

		////////////////////////////
		// linked entities
		////////////////////////////

		"Bitrix\\Sale\\Delivery\\DeliveryLocationTable" => "lib/delivery/deliverylocation.php",
		"Bitrix\\Sale\\Delivery\\DeliveryLocationExcludeTable" => "lib/delivery/deliverylocationexclude.php",
		"Bitrix\\Sale\\Tax\\RateLocationTable" => "lib/tax/ratelocation.php",
		////////////////////////////

		"CSaleBasketFilter" => "general/sale_cond.php",
		"CSaleCondCtrl" => "general/sale_cond.php",
		"CSaleCondCtrlComplex" => "general/sale_cond.php",
		"CSaleCondCtrlGroup" => "general/sale_cond.php",
		"CSaleCondCtrlBasketGroup" => "general/sale_cond.php",
		"CSaleCondCtrlBasketFields" => "general/sale_cond.php",
		"CSaleCondCtrlBasketItemConditions" => "general/sale_cond.php",
		"CSaleCondCtrlBasketProperties" => "general/sale_cond.php",
		"CSaleCondCtrlOrderFields" => "general/sale_cond.php",
		"CSaleCondCtrlCommon" => "general/sale_cond.php",
		"CSaleCondTree" => "general/sale_cond.php",
		"CSaleCondCtrlPastOrder" => "general/sale_cond.php",
		"CSaleCondCumulativeCtrl" => "general/sale_cond.php",
		"CSaleCumulativeAction" => "general/sale_act.php",
		"CSaleActionCtrl" => "general/sale_act.php",
		"CSaleActionCtrlComplex" => "general/sale_act.php",
		"CSaleActionCtrlGroup" => "general/sale_act.php",
		"CSaleActionCtrlAction" => "general/sale_act.php",
		"CSaleDiscountActionApply" => "general/sale_act.php",
		"CSaleActionCtrlDelivery" => "general/sale_act.php",
		"CSaleActionGift" => "general/sale_act.php",
		"CSaleActionGiftCtrlGroup" => "general/sale_act.php",
		"CSaleActionCtrlBasketGroup" => "general/sale_act.php",
		"CSaleActionCtrlSubGroup" => "general/sale_act.php",
		"CSaleActionCondCtrlBasketFields" => "general/sale_act.php",
		"CSaleActionTree" => "general/sale_act.php",
		"CSaleDiscountConvert" => "general/discount_convert.php",

		"CSalePdf" => "general/pdf.php",
		"CSaleYMHandler" => "general/ym_handler.php",
		"CSaleYMLocation" => "general/ym_location.php",

		"Bitrix\\Sale\\Delivery\\CalculationResult" => "lib/delivery/calculationresult.php",
		"Bitrix\\Sale\\Delivery\\Services\\Table" => "lib/delivery/services/table.php",
		"Bitrix\\Sale\\Delivery\\Restrictions\\Table" => "lib/delivery/restrictions/table.php",
		"Bitrix\\Sale\\Delivery\\Services\\Manager" => "lib/delivery/services/manager.php",
		"Bitrix\\Sale\\Delivery\\Restrictions\\Base" => "lib/delivery/restrictions/base.php",
		"Bitrix\\Sale\\Delivery\\Restrictions\\Manager" => "lib/delivery/restrictions/manager.php",
		"Bitrix\\Sale\\Delivery\\Services\\Base" => "lib/delivery/services/base.php",
		"Bitrix\\Sale\\Delivery\\Menu" => "lib/delivery/menu.php",
		"Bitrix\\Sale\\Delivery\\Services\\ObjectPool" => "lib/delivery/services/objectpool.php",

		'\Bitrix\Sale\TradingPlatformTable' => 'lib/internals/tradingplatform.php',
		'\Bitrix\Sale\TradingPlatform\Ebay\Policy' => 'lib/tradingplatform/ebay/policy.php',
		'\Bitrix\Sale\TradingPlatform\Helper' => 'lib/tradingplatform/helper.php',
		'\Bitrix\Sale\TradingPlatform\YMarket\YandexMarket' => 'lib/tradingplatform/ymarket/yandexmarket.php',
		'\Bitrix\Sale\TradingPlatform\Platform' => 'lib/tradingplatform/platform.php',
		'\Bitrix\Sale\TradingPlatform\Logger' => 'lib/tradingplatform/logger.php',

		'Bitrix\Sale\Internals\ShipmentExtraServiceTable' => 'lib/internals/shipmentextraservice.php',
		'Bitrix\Sale\Delivery\ExtraServices\Manager' => 'lib/delivery/extra_services/manager.php',
		'Bitrix\Sale\Delivery\ExtraServices\Base' => 'lib/delivery/extra_services/base.php',
		'Bitrix\Sale\Delivery\ExtraServices\Table' => 'lib/delivery/extra_services/table.php',
		'Bitrix\Sale\Delivery\Tracking\Manager' => 'lib/delivery/tracking/manager.php',
		'Bitrix\Sale\Delivery\Tracking\Table' => 'lib/delivery/tracking/table.php',
		'Bitrix\Sale\Delivery\ExternalLocationMap' => 'lib/delivery/externallocationmap.php',

		'Bitrix\Sale\Internals\ServiceRestrictionTable' => 'lib/internals/servicerestriction.php',
		'Bitrix\Sale\Services\Base\RestrictionManager' => 'lib/services/base/restrictionmanager.php',
		'\Bitrix\Sale\Services\Base\SiteRestriction' => 'lib/services/base/siterestriction.php',
		'\Bitrix\Sale\Services\Base\TradeBindingRestriction' => 'lib/services/base/tradebindingrestriction.php',

		'\Bitrix\Sale\Compatible\DiscountCompatibility' => 'lib/compatible/discountcompatibility.php',
		'\Bitrix\Sale\Config\Feature' => 'lib/config/feature.php',
		'\Bitrix\Sale\Discount\Context\BaseContext' => 'lib/discount/context/basecontext.php',
		'\Bitrix\Sale\Discount\Context\Fuser' => 'lib/discount/context/fuser.php',
		'\Bitrix\Sale\Discount\Context\User' => 'lib/discount/context/user.php',
		'\Bitrix\Sale\Discount\Context\UserGroup' => 'lib/discount/context/usergroup.php',
		'\Bitrix\Sale\Discount\Gift\Collection' => 'lib/discount/gift/collection.php',
		'\Bitrix\Sale\Discount\Gift\Gift' => 'lib/discount/gift/gift.php',
		'\Bitrix\Sale\Discount\Gift\Manager' => 'lib/discount/gift/manager.php',
		'\Bitrix\Sale\Discount\Gift\RelatedDataTable' => 'lib/discount/gift/relateddata.php',
		'\Bitrix\Sale\Discount\Index\IndexElementTable' => 'lib/discount/index/indexelement.php',
		'\Bitrix\Sale\Discount\Index\IndexSectionTable' => 'lib/discount/index/indexsection.php',
		'\Bitrix\Sale\Discount\Index\Manager' => 'lib/discount/index/manager.php',
		'\Bitrix\Sale\Discount\Migration\OrderDiscountMigrator' => 'lib/discount/migration/orderdiscountmigrator.php',
		'\Bitrix\Sale\Discount\Prediction\Manager' => 'lib/discount/prediction/manager.php',
		'\Bitrix\Sale\Discount\Preset\ArrayHelper' => 'lib/discount/preset/arrayhelper.php',
		'\Bitrix\Sale\Discount\Preset\BasePreset' => 'lib/discount/preset/basepreset.php',
		'\Bitrix\Sale\Discount\Preset\HtmlHelper' => 'lib/discount/preset/htmlhelper.php',
		'\Bitrix\Sale\Discount\Preset\Manager' => 'lib/discount/preset/manager.php',
		'\Bitrix\Sale\Discount\Preset\SelectProductPreset' => 'lib/discount/preset/selectproductpreset.php',
		'\Bitrix\Sale\Discount\Preset\State' => 'lib/discount/preset/state.php',
		'\Bitrix\Sale\Discount\Result\CompatibleFormat' => 'lib/discount/result/compatibleformat.php',
		'\Bitrix\Sale\Discount\RuntimeCache\DiscountCache' => 'lib/discount/runtimecache/discountcache.php',
		'\Bitrix\Sale\Discount\RuntimeCache\FuserCache' => 'lib/discount/runtimecache/fusercache.php',
		'\Bitrix\Sale\Discount\Actions' => 'lib/discount/actions.php',
		'\Bitrix\Sale\Discount\Analyzer' => 'lib/discount/analyzer.php',
		'\Bitrix\Sale\Discount\CumulativeCalculator' => 'lib/discount/cumulativecalculator.php',
		'\Bitrix\Sale\Discount\Formatter' => 'lib/discount/formatter.php',
		'\Bitrix\Sale\Internals\DiscountTable' => 'lib/internals/discount.php',
		'\Bitrix\Sale\Internals\DiscountCouponTable' => 'lib/internals/discountcoupon.php',
		'\Bitrix\Sale\Internals\DiscountEntitiesTable' => 'lib/internals/discountentities.php',
		'\Bitrix\Sale\Internals\DiscountGroupTable' => 'lib/internals/discountgroup.php',
		'\Bitrix\Sale\Internals\DiscountModuleTable' => 'lib/internals/discountmodule.php',
		'\Bitrix\Sale\Internals\OrderDiscountTable' => 'lib/internals/orderdiscount.php',
		'\Bitrix\Sale\Internals\OrderDiscountDataTable' => 'lib/internals/orderdiscount.php',
		'\Bitrix\Sale\Internals\OrderCouponsTable' => 'lib/internals/orderdiscount.php',
		'\Bitrix\Sale\Internals\OrderModulesTable' => 'lib/internals/orderdiscount.php',
		'\Bitrix\Sale\Internals\OrderRoundTable' => 'lib/internals/orderround.php',
		'\Bitrix\Sale\Internals\OrderRulesTable' => 'lib/internals/orderdiscount.php',
		'\Bitrix\Sale\Internals\OrderRulesDescrTable' => 'lib/internals/orderdiscount.php',
		'\Bitrix\Sale\Internals\AccountNumberGenerator' => 'lib/internals/accountnumber.php',
		'\Bitrix\Sale\Discount' => 'lib/discount.php',
		'\Bitrix\Sale\DiscountBase' => 'lib/discountbase.php',
		'\Bitrix\Sale\DiscountCouponsManager' => 'lib/discountcouponsmanager.php',
		'\Bitrix\Sale\DiscountCouponsManagerBase' => 'lib/discountcouponsmanagerbase.php',
		'\Bitrix\Sale\OrderDiscount' => 'lib/orderdiscount.php',
		'\Bitrix\Sale\OrderDiscountBase' => 'lib/orderdiscountbase.php',
		'\Bitrix\Sale\OrderDiscountManager' => 'lib/orderdiscountmanager.php',

		'\Bitrix\Sale\PaySystem\Logger' => 'lib/paysystem/logger.php',
		'\Bitrix\Sale\PaySystem\RestService' => 'lib/paysystem/restservice.php',
		'\Bitrix\Sale\PaySystem\RestHandler' => 'lib/paysystem/resthandler.php',
		'\Bitrix\Sale\Services\Base\RestClient' => 'lib/services/base/restclient.php',
		'\Bitrix\Sale\PaySystem\Service' => 'lib/paysystem/service.php',
		'\Bitrix\Sale\Internals\PaySystemRestHandlersTable' => 'lib/internals/paysystemresthandlers.php',
		'\Bitrix\Sale\PaySystem\Manager' => 'lib/paysystem/manager.php',
		'\Bitrix\Sale\PaySystem\BaseServiceHandler' => 'lib/paysystem/baseservicehandler.php',
		'\Bitrix\Sale\PaySystem\ServiceHandler' => 'lib/paysystem/servicehandler.php',
		'\Bitrix\Sale\PaySystem\IRefund' => 'lib/paysystem/irefund.php',
		'\Bitrix\Sale\PaySystem\IPdf' => 'lib/paysystem/ipdf.php',
		'\Bitrix\Sale\PaySystem\IRefundExtended' => 'lib/paysystem/irefundextended.php',
		'\Bitrix\Sale\PaySystem\Cert' => 'lib/paysystem/cert.php',
		'\Bitrix\Sale\PaySystem\IPayable' => 'lib/paysystem/ipayable.php',
		'\Bitrix\Sale\PaySystem\ICheckable' => 'lib/paysystem/icheckable.php',
		'\Bitrix\Sale\PaySystem\IPrePayable' => 'lib/paysystem/iprepayable.php',
		'\Bitrix\Sale\PaySystem\CompatibilityHandler' => 'lib/paysystem/compatibilityhandler.php',
		'\Bitrix\Sale\PaySystem\IHold' => 'lib/paysystem/ihold.php',
		'\Bitrix\Sale\PaySystem\IPartialHold' => 'lib/paysystem/ipartialhold.php',
		'\Bitrix\Sale\Internals\PaymentLogTable' => 'lib/internals/paymentlog.php',
		'\Bitrix\Sale\Services\PaySystem\Restrictions\Manager' => 'lib/services/paysystem/restrictions/manager.php',
		'\Bitrix\Sale\Services\Base\Restriction' => 'lib/services/base/restriction.php',
		'\Bitrix\Sale\Services\Base\RestrictionManager' => 'lib/services/base/restrictionmanager.php',
		'\Bitrix\sale\Internals\YandexSettingsTable' => 'lib/internals/yandexsettings.php',

		'\Bitrix\Sale\Services\Company\Manager' => 'lib/services/company/manager.php',
		'\Bitrix\Sale\Internals\CollectionFilterIterator' => 'lib/internals/collectionfilteriterator.php',

		'\Bitrix\Sale\Cashbox\Internals\Pool' => 'lib/cashbox/internals/pool.php',
		'\Bitrix\Sale\Cashbox\Internals\CashboxTable' => 'lib/cashbox/internals/cashbox.php',
		'\Bitrix\Sale\Cashbox\Internals\CashboxCheckTable' => 'lib/cashbox/internals/cashboxcheck.php',
		'\Bitrix\Sale\Cashbox\Internals\CashboxZReportTable' => 'lib/cashbox/internals/cashboxzreport.php',
		'\Bitrix\Sale\Cashbox\Internals\CashboxErrLogTable' => 'lib/cashbox/internals/cashboxerrlog.php',
		'\Bitrix\Sale\Cashbox\Cashbox' => 'lib/cashbox/cashbox.php',
		'\Bitrix\Sale\Cashbox\Manager' => 'lib/cashbox/manager.php',
		'\Bitrix\Sale\Cashbox\IPrintImmediately' => 'lib/cashbox/iprintimmediately.php',
		'\Bitrix\Sale\Cashbox\Restrictions\Manager' => 'lib/cashbox/restrictions/manager.php',

		'\Bitrix\Sale\Notify' => 'lib/notify.php',
		'\Bitrix\Sale\BuyerStatistic'=> '/lib/buyerstatistic.php',
		'\Bitrix\Sale\Internals\BuyerStatisticTable'=> '/lib/internals/buyerstatistic.php',

		'CAdminSaleList' => 'general/admin_lib.php',
		'\Bitrix\Sale\Helpers\Admin\SkuProps' => 'lib/helpers/admin/skuprops.php',
		'\Bitrix\Sale\Helpers\Admin\Product' => 'lib/helpers/admin/product.php',
		'\Bitrix\Sale\Helpers\Order' => 'lib/helpers/order.php',
		'\Bitrix\Sale\Location\Comparator\Replacement' => 'lib/location/comparator/ru/replacement.php',
		'\Bitrix\Sale\Location\Comparator\TmpTable' => 'lib/location/comparator/tmptable.php',
		'\Bitrix\Sale\Location\Comparator' => 'lib/location/comparator.php',
		'\Bitrix\Sale\Location\Comparator\MapResult' => 'lib/location/comparator/mapresult.php',
		'\Bitrix\Sale\Location\Comparator\Mapper' => 'lib/location/comparator/mapper.php',

		'\Bitrix\Sale\Exchange\OneC\DocumentImport' => '/lib/exchange/compatibility/documents.php',

		'\Bitrix\Sale\Exchange\OneC\CollisionOrder' => '/lib/exchange/onec/importcollision.php',
		'\Bitrix\Sale\Exchange\OneC\CollisionShipment' => '/lib/exchange/onec/importcollision.php',
		'\Bitrix\Sale\Exchange\OneC\CollisionPayment' => '/lib/exchange/onec/importcollision.php',
		'\Bitrix\Sale\Exchange\OneC\CollisionProfile' => '/lib/exchange/onec/importcollision.php',
		'\Bitrix\Sale\Exchange\OneC\PaymentCashDocument'=> '/lib/exchange/onec/paymentdocument.php',
		'\Bitrix\Sale\Exchange\OneC\PaymentCashLessDocument'=> '/lib/exchange/onec/paymentdocument.php',
		'\Bitrix\Sale\Exchange\OneC\PaymentCardDocument'=> '/lib/exchange/onec/paymentdocument.php',
		'\Bitrix\Sale\Exchange\OneC\ImportCriterionBase' => '/lib/exchange/onec/importcriterion.php',
		'\Bitrix\Sale\Exchange\OneC\ImportCriterionOneCCml2' => '/lib/exchange/onec/importcriterion.php',
		'\Bitrix\Sale\Exchange\OneC\CriterionOrder' => '/lib/exchange/onec/importcriterion.php',
		'\Bitrix\Sale\Exchange\OneC\CriterionShipment' => '/lib/exchange/onec/importcriterion.php',
		'\Bitrix\Sale\Exchange\OneC\CriterionShipmentInvoice' => '/lib/exchange/onec/importcriterion.php',
		'\Bitrix\Sale\Exchange\OneC\CriterionPayment' => '/lib/exchange/onec/importcriterion.php',
		'\Bitrix\Sale\Exchange\OneC\CriterionProfile' => '/lib/exchange/onec/importcriterion.php',
		'\Bitrix\Sale\Exchange\Entity\OrderImportLoader'=> '/lib/exchange/entity/entityimportloader.php',
		'\Bitrix\Sale\Exchange\Entity\InvoiceImportLoader'=> '/lib/exchange/entity/entityimportloader.php',
		'\Bitrix\Sale\Exchange\Entity\PaymentImportLoader'=> '/lib/exchange/entity/entityimportloader.php',
		'\Bitrix\Sale\Exchange\Entity\ShipmentImportLoader'=> '/lib/exchange/entity/entityimportloader.php',
		'\Bitrix\Sale\Exchange\Entity\UserProfileImportLoader'=> '/lib/exchange/entity/entityimportloader.php',
		'\Bitrix\Sale\Exchange\Entity\PaymentCashLessImport'=> '/lib/exchange/entity/paymentimport.php',
		'\Bitrix\Sale\Exchange\Entity\PaymentCardImport'=> '/lib/exchange/entity/paymentimport.php',
		'\Bitrix\Sale\Exchange\Entity\PaymentCashImport'=> '/lib/exchange/entity/paymentimport.php',

		'\Bitrix\Sale\Location\GeoIp' => '/lib/location/geoip.php',

		'\Bitrix\Sale\Delivery\Requests\Manager' => '/lib/delivery/requests/manager.php',
		'\Bitrix\Sale\Delivery\Requests\Helper' => '/lib/delivery/requests/helper.php',
		'\Bitrix\Sale\Delivery\Requests\HandlerBase' => '/lib/delivery/requests/handlerbase.php',
		'\Bitrix\Sale\Delivery\Requests\RequestTable' => '/lib/delivery/requests/request.php',
		'\Bitrix\Sale\Delivery\Requests\ShipmentTable' => '/lib/delivery/requests/shipment.php',
		'\Bitrix\Sale\Delivery\Requests\Result' => '/lib/delivery/requests/result.php',
		'\Bitrix\Sale\Delivery\Requests\ResultFile' => '/lib/delivery/requests/resultfile.php',

		'\Bitrix\Sale\Delivery\Packing\Packer' => '/lib/delivery/packing/packer.php',

		'\Bitrix\Sale\Recurring' => '/lib/recurring.php',

		'\Bitrix\Sale\Location\Normalizer\Builder' => '/lib/location/normalizer/builder.php',
		'\Bitrix\Sale\Location\Normalizer\IBuilder' => '/lib/location/normalizer/ibuilder.php',
		'\Bitrix\Sale\Location\Normalizer\Normalizer' => '/lib/location/normalizer/normalizer.php',
		'\Bitrix\Sale\Location\Normalizer\INormalizer' => '/lib/location/normalizer/inormalizer.php',
		'\Bitrix\Sale\Location\Normalizer\CommonNormalizer' => '/lib/location/normalizer/commonnormalizer.php',
		'\Bitrix\Sale\Location\Normalizer\NullNormalizer' => '/lib/location/normalizer/nullnormalizer.php',
		'\Bitrix\Sale\Location\Normalizer\SpaceNormalizer' => '/lib/location/normalizer/spacenormalizer.php',
		'\Bitrix\Sale\Location\Normalizer\LanguageNormalizer' => '/lib/location/normalizer/languagenormalizer.php',
		'\Bitrix\Sale\Location\Normalizer\Helper' => '/lib/location/normalizer/helper.php',

		'\Sale\Handlers\Delivery\Additional\RusPost\Reliability\Service' => '/handlers/delivery/additional/ruspost/reliability/service.php'
	]
);

\Bitrix\Main\Loader::registerNamespace(
	'Sale\Handlers\Delivery\YandexTaxi\\',
	\Bitrix\Main\Loader::getDocumentRoot().'/bitrix/modules/sale/handlers/delivery/yandextaxi'
);
\Bitrix\Main\Loader::registerNamespace(
	'Sale\Handlers\Delivery\Rest\\',
	\Bitrix\Main\Loader::getDocumentRoot().'/bitrix/modules/sale/handlers/delivery/rest'
);

class_alias('Bitrix\Sale\TradingPlatform\YMarket\YandexMarket', 'Bitrix\Sale\TradingPlatform\YandexMarket');
class_alias('\Bitrix\Sale\PaySystem\Logger', '\Bitrix\Sale\PaySystem\ErrorLog');
class_alias('\Bitrix\Sale\Internals\OrderTable', '\Bitrix\Sale\OrderTable');
class_alias('\Bitrix\Sale\Internals\FuserTable', '\Bitrix\Sale\FuserTable');
class_alias('\Bitrix\Sale\Internals\Product2ProductTable', '\Bitrix\Sale\Product2ProductTable');
class_alias('\Bitrix\Sale\Internals\StoreProductTable', '\Bitrix\Sale\StoreProductTable');
class_alias('\Bitrix\Sale\Internals\PersonTypeTable', '\Bitrix\Sale\PersonTypeTable');
class_alias('\Bitrix\Sale\Internals\ProductTable', '\Bitrix\Sale\ProductTable');
class_alias('\Bitrix\Sale\Internals\SectionTable', '\Bitrix\Sale\SectionTable');
class_alias('\Bitrix\Sale\Internals\OrderProcessingTable', '\Bitrix\Sale\OrderProcessingTable');
class_alias('\Bitrix\Sale\Internals\GoodsSectionTable', '\Bitrix\Sale\GoodsSectionTable');