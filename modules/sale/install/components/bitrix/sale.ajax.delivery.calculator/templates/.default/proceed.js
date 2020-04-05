function deliveryCalcProceed(arParams)
{
	var delivery_id = arParams.DELIVERY_ID;
	var getExtraParamsFunc = arParams.EXTRA_PARAMS_CALLBACK;

	function __handlerDeliveryCalcProceed(data)
	{
		var obContainer = document.getElementById('delivery_info_' + delivery_id);
		if (obContainer)
		{
			obContainer.innerHTML = data;
		}

		PCloseWaitMessage('wait_container_' + delivery_id, true);
	}

	PShowWaitMessage('wait_container_' + delivery_id, true);
	
	var url = '/bitrix/components/bitrix/sale.ajax.delivery.calculator/templates/.default/ajax.php';
	
	var TID = CPHttpRequest.InitThread();
	CPHttpRequest.SetAction(TID, __handlerDeliveryCalcProceed);

	if(!getExtraParamsFunc)
	{
		CPHttpRequest.Post(TID, url, arParams);
	}
	else
	{
		eval(getExtraParamsFunc);

		BX.addCustomEvent('onSaleDeliveryGetExtraParams', function(params){
			arParams.EXTRA_PARAMS = params;
			CPHttpRequest.Post(TID, url, arParams);
		});
	}
}