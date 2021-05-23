<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CJSCore::Init('ajax');

if($arResult["ACTION"] == "get_dialog")
{
	switch ($arResult["DIALOG_NAME"])
	{
		case "fields":
			require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/dialogs/fields.php');
		return;

	}
}
elseif ($arResult["ACTION"] == "filter_edit")
{
		$APPLICATION->IncludeComponent(
			'bitrix:mobileapp.filter',
			'.default',
			$arResult["FILTER_PARAMS"],
			false
		);
	return;
}

if((!is_array($arResult["ORDERS"]) || empty($arResult["ORDERS"])) && !$arResult['RETURN_AS_ARRAY'])
{
		echo '<div style="text-align: center; width=100%">'.GetMessage('SMOL_NO_ORDERS2').'</div>';
}
else
{
	require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/order_tmpl.php');

	$ordersHtml = '
	<div class="order_itemlist_component" id="orders-list">';
	$arOrders = array();
	$lastOrderId = 0;
	foreach ($arResult["ORDERS"] as $order)
	{
		$orderItemHtml = '';
		$lastOrderId = $order["ID"];
		$orderItemHtml .= '<div id="order-'.$order["ID"].'" class="order_itemlist_item_container';

		if($order["STATUS_ID"] == 'F')
		{
			$orderItemHtml .= ' order_completed">';
			$orderItemHtml .= CSaleMobileOrderUtils::getPreparedTemplate($orderTemplateCompleted, $order);
		}
		else
		{
			$content = '';

			foreach ($arResult['VISIBLE_FIELDS'] as $fName)
			{
				switch($fName)
				{
					case "USER_ID":
						$content .= '<div class="order_itemlist_item_customer"><span class="rv_icon"> </span>##ADD_FIO##</div>'.PHP_EOL;
					break;

					case "DELIVERY":
						$content .= '<div class="order_itemlist_item_delivery ##TMPL_DELIVERY_ALLOWED##">'.
									'<span class="rv_icon"> </span>##ADD_DELIVERY_NAME## / ##ADD_ALLOW_DELIVERY_PHRASE##</div>'.PHP_EOL;
					break;

					case "PAYED":
						$content .= '<div class="order_itemlist_item_pay ##TMPL_PAYED##"><span class="rv_icon">'.
									'</span>##ADD_PAY_SYSTEM_NAME## ##ADD_ALLOW_PAYED_PHRASE##</div>'.PHP_EOL;
					break;

					default:
						$content .= '<p class="order_itemlist_item_default">'.
									$arResult['FIELDS'][$fName].': ##'.$fName.'##</p>'.PHP_EOL;
				}
			}

			$orderTemplate = str_replace('##CONTENT##', $content, $orderTemplate);
			$orderItemHtml .= ' order_'.$order["ADD_ORDER_STEP"].'">';
			$orderItemHtml .= CSaleMobileOrderUtils::getPreparedTemplate($orderTemplate, $order);
		}

		$orderItemHtml .= '</div>';

		if($arResult['RETURN_AS_ARRAY'])
			$arOrders[$order["ID"]] = $orderItemHtml;
		else
			$ordersHtml .= $orderItemHtml;
	}

	if($arResult['RETURN_AS_ARRAY'])
	{
		$arOrders = $APPLICATION->ConvertCharsetArray($arOrders, SITE_CHARSET, 'utf-8');
		$arJsonRes = array('orders' => $arOrders);

		if($arResult['BOTTOM_REACHED'])
			$arJsonRes['bottomReached'] = true;

		$arJsonRes = json_encode($arJsonRes);
		die($arJsonRes);
	}
	else
	{
		echo $ordersHtml.'</div>';
	}
}

$pageTitle = GetMessage("SMOL_ALL_ORDERS");

if(isset($_REQUEST['filter_name']))
{
	switch($_REQUEST['filter_name'])
	{
		case 'waiting_for_pay':
			$pageTitle = GetMessage('SMOL_WAITING_FOR_PAY');
		break;

		case 'waiting_for_delivery':
			$pageTitle = GetMessage('SMOL_WAITING_FOR_DELIVERY');
		break;

		case 'custom':
			$pageTitle = GetMessage('SMOL_FILTER_USER');;
		break;

	}
}

?>
<script type="text/javascript">

	app.setPageTitle({title: "<?=$pageTitle?>"});

	var ordersListParams = {
							lastOrder: '<?=$lastOrderId?>',
							filter: <?=CUtil::PhpToJsObject($arResult["FILTER"])?>,
							ajaxUrl: '<?=$arResult['AJAX_URL']?>',
							dialogUrl: "<?=$arResult['CURRENT_PAGE']?>",
							orderDetailPath: "<?=$arResult['ORDER_DETAIL_PATH']?>"
						};

	var ordersList = new __MASaleOrdersList(ordersListParams);
	var checkTimeout = <?=SALE_ORDERS_LIST_CHECK_TIMEOUT?>;

	//order updated from application
	BX.addCustomEvent('onAfterOrderChange', function (params){ ordersList.onOrderUpdate(params.id);});
	BX.addCustomEvent('onAfterFilterApply', function (){ document.location.search = "?filtered=Y&filter_name=custom";});
	BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", function(params) { ordersList.getUpdatedOrders(); });
	BX.addCustomEvent('onAfterOrdersListVisibleFieldsChange', function (params){ document.location.reload();});
	BX.addCustomEvent("onPull", function(data) {
		if(data.module_id == 'sale')
		{
			ordersList.setUpdateTime();

			if(data.command == 'orderDelete')
				ordersList.deleteOrder(data.params.id);
			else if(data.command == 'orderAdd')
				ordersList.onOrderAdd(data.params.id);
			else if(data.command == 'orderUpdate')
				ordersList.onOrderUpdate(data.params.id);
		}
	});

	<?if(isset($_GET["on_open_push"])):?>
		BX.ready( function(){ app.onCustomEvent('onOpenPushReadyForParams', {head: 'sl'}); });
	<?endif;?>

	BX.addCustomEvent('onOpenPushReadyToSendParams', function(params) {
		app.onCustomEvent('onOpenPushReadyForParams', {head: 'sl'});
	});

	BX.addCustomEvent('onOpenPushParams', function(params) {
		if(params.params)
		{
			var aPushParams = (params.params+'').split("_");
			var ppHead = aPushParams[0];

			if(ppHead && ppHead == "sl" && aPushParams[1])
				app.loadPageBlank({ url: "<?=$arResult['ORDER_DETAIL_PATH'].'?id='?>"+aPushParams[1]});
		}
	});

	setInterval(function() {
		var d = new Date();
		if(d - ordersList.getUpdateTime() >= checkTimeout)
			ordersList.getUpdatedOrders();
		},
		checkTimeout
	);

	window.onscroll = function ()
	{
		var preloadCoefficient = <?=SALE_ORDERS_LIST_PRELOAD_START?>;
		var clientHeight = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
		var documentHeight = document.documentElement.scrollHeight ? document.documentElement.scrollHeight : document.body.scrollHeight;
		var scrollTop = window.pageYOffset ? window.pageYOffset : (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);

		//if we nearly last (bottom) order
		if((documentHeight - clientHeight*(1+preloadCoefficient)) <= scrollTop)
		{
			ordersList.getBottomOrders();
		}
	}

	listMenuItems = {
		items: [
			{
				name: "<?=GetMessage("SMOL_ALL_ORDERS");?>",
				action: function(){window.location.replace("<?=$arResult["CURRENT_PAGE"]?>")},
				icon: "<?=(isset($_REQUEST['filter_name'])? 'default' : 'check')?>"
			},
			{
				name: "<?=GetMessage("SMOL_WAITING_FOR_PAY");?>",
				action: function(){window.location.replace("<?=$arResult["CURRENT_PAGE"]?>"+
									"?filtered=Y&filter_name=waiting_for_pay")},
				icon: "<?=(isset($_REQUEST['filter_name']) && $_REQUEST['filter_name'] == 'waiting_for_pay' ? 'check' : 'filter')?>"
			},
			{
				name: "<?=GetMessage("SMOL_WAITING_FOR_DELIVERY");?>",
				action: function(){window.location.replace("<?=$arResult["CURRENT_PAGE"]?>"+
									"?filtered=Y&filter_name=waiting_for_delivery")},
				icon: "<?=(isset($_REQUEST['filter_name']) && $_REQUEST['filter_name'] == 'waiting_for_delivery' ? 'check' : 'filter')?>"
			},
			{
				name: "<?=GetMessage("SMOL_FILTER_USER");?>",
				action: function() { document.location.search = "?filtered=Y&filter_name=custom"; },
				icon: "<?=(isset($_REQUEST['filter_name']) && $_REQUEST['filter_name'] == 'custom' ? 'check' : 'filter')?>"
			},
			{
				name: "<?=GetMessage("SMOL_FILTER_TUNE");?>",
				url: "<?=$arResult['CURRENT_PAGE']?>?action=filter_edit",
				icon: "settings"
			},
			{
				name: "<?=GetMessage("SMOL_VISIBLE_FIELDS");?>",
				action: function() { ordersList.dialogShow("fields"); },
				icon: "settings"
			}
		]
	};

	app.menuCreate(listMenuItems);

	app.addButtons({
		menuButton:
		{
			type:     'context-menu',
			style:    'custom',
			callback: function()
			{
				app.menuShow();
			}
		},
	});

</script>