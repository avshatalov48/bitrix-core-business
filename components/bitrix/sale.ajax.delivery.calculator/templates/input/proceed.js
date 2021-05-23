function deliveryCalcProceed(arParams)
{
	var input_name = arParams.INPUT_NAME;
	
	function __handlerDeliveryCalcProceed(data)
	{
		var obContainer = document.getElementById('delivery_info_' + input_name);
		if (obContainer)
		{
			obContainer.innerHTML = data;
		}

		PCloseWaitMessage('wait_container_' + input_name, true);
	}

	PShowWaitMessage('wait_container_' + input_name, true);
	
	var url = '/bitrix/components/bitrix/sale.ajax.delivery.calculator/templates/input/ajax.php'
	
	var TID = CPHttpRequest.InitThread();
	CPHttpRequest.SetAction(TID, __handlerDeliveryCalcProceed);
	CPHttpRequest.Post(TID, url, arParams);
}