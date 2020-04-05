function getLocation(country_id, region_id, city_id, arParams, site_id)
{
	BX.showWait();
	
	property_id = arParams.CITY_INPUT_NAME;
	
	function getLocationResult(res)
	{
		BX.closeWait();
		
		var obContainer = document.getElementById('LOCATION_' + property_id);
		if (obContainer)
		{
			obContainer.innerHTML = res;
		}
	}

	arParams.COUNTRY = parseInt(country_id);
	arParams.REGION = parseInt(region_id);
	arParams.SITE_ID = site_id;

	var url = '/bitrix/components/bitrix/sale.ajax.locations/templates/.default/ajax.php';
	BX.ajax.post(url, arParams, getLocationResult)
}

function getLocationByZip(zip, propertyId)
{
	BX.showWait();
	
	property_id = propertyId;
	
	function getLocationByZipResult(res)
	{
		BX.closeWait();
		
		var obContainer = document.getElementById('LOCATION_' + property_id);
		if (obContainer)
		{
			obContainer.innerHTML = res;
		}
	}

	var url = '/bitrix/components/bitrix/sale.ajax.locations/templates/.default/ajax.php';
	BX.ajax.post(url, 'ZIPCODE=' + zip.value, getLocationByZipResult)
}