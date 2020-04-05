function appDeliveryCalcProceed(arParams)
{
	var delivery_id = arParams.DELIVERY;
	var profile_id = arParams.PROFILE;
	
	function __handlerDeliveryCalcProceed(data)
	{
		var obContainer = document.getElementById('delivery_info_' + delivery_id + '_' + profile_id);
		if (obContainer)
		{
			obContainer.innerHTML = data;
		}

		PCloseWaitMessage('wait_container_' + delivery_id + '_' + profile_id, true);
	}

	PShowWaitMessage('wait_container_' + delivery_id + '_' + profile_id, true);
	
	var url = '/bitrix/components/bitrix/eshopapp.ajax.delivery.calculator/templates/.default/ajax.php';
	
	var TID = CPHttpRequest.InitThread();
	CPHttpRequest.SetAction(TID, __handlerDeliveryCalcProceed);
	CPHttpRequest.Post(TID, url, arParams);
}