/**
 * Class BX.Sale.Options
 */
;(function(window) {

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.Options) return;

	BX.Sale.Options = {
		lang: null,
		reqTimeIntervalId: null,
		reqDelay: 1000, // ms
		countryPrf: "sales_zone_countries_",
		regionPrf: "sales_zone_regions_",
		cityPrf: "sales_zone_cities_",

		onCountrySelect: function(event)
		{
			if(BX.Sale.Options.reqTimeIntervalId)
				window.clearInterval(BX.Sale.Options.reqTimeIntervalId);

			var event = event || window.event;
			var target = event.target || event.srcElement;
			var siteId = target.id.replace(BX.Sale.Options.countryPrf, "");

			var countries = BX.Sale.Options.getValuesFromMultiselect(target);

			BX.Sale.Options.reqTimeIntervalId = window.setInterval(function(){
					ShowWaitWindow();
					BX.selectUtils.deleteAllOptions(BX(BX.Sale.Options.regionPrf+siteId));
					BX.selectUtils.deleteAllOptions(BX(BX.Sale.Options.cityPrf+siteId));
					BX.Sale.Options.getLocationRegions(countries, siteId)
				},	BX.Sale.Options.reqDelay
			);
		},

		setLocationRegions: function(regions, siteId)
		{
			if(!siteId)
				return false;

			var locSelRegions = BX(BX.Sale.Options.regionPrf+siteId);

			if(!locSelRegions)
				return false;

			BX.selectUtils.addNewOption(locSelRegions, "", BX.message("SMO_LOCATION_ALL"), false, false);
			BX.selectUtils.addNewOption(locSelRegions, "NULL", BX.message("SMO_LOCATION_NO_REGION"), false, false);

			BX.selectUtils.addNewOption(BX(BX.Sale.Options.cityPrf+siteId), "", BX.message("SMO_LOCATION_ALL"), false, false);

			for(var i in regions)
				if(regions.hasOwnProperty(i))
					BX.selectUtils.addNewOption(locSelRegions, i, regions[i], false, false);
		},

		getLocationRegions: function(countryIds, siteId)
		{
			window.clearInterval(BX.Sale.Options.reqTimeIntervalId);
			var url = "/bitrix/services/ajax/sale/location/index.php";

			var postData = {
				action: "getRegionList",
				countryIds: countryIds,
				lang: BX.Sale.Options.lang,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout:   30,
				method:   'POST',
				dataType: 'json',
				url:       url,
				data:      postData,

				onsuccess: function(result)
				{
					CloseWaitWindow();
					if(result && result.DATA && !result.ERROR)
						BX.Sale.Options.setLocationRegions(result.DATA, siteId);
					else if(result && result.ERROR)
						alert(BX.message("SMO_LOCATION_JS_GET_DATA_ERROR") + ": " + result.ERROR);
					else
						alert(BX.message("SMO_LOCATION_JS_GET_DATA_ERROR"));
				},

				onfailure: function()
				{
					CloseWaitWindow();
					alert(BX.message("SMO_LOCATION_JS_GET_DATA_ERROR"));
				}
			});
		},

		getValuesFromMultiselect: function(selectDomNode)
		{
			var result = [];

			for(var i = 0; i <  selectDomNode.options.length; i++)
				if(selectDomNode.options[i].selected == true)
					result.push(selectDomNode.options[i].value);

			return result;
		},

		onRegionSelect: function(event)
		{
			if(BX.Sale.Options.reqTimeIntervalId)
				window.clearInterval(BX.Sale.Options.reqTimeIntervalId);


			var event = event || window.event;
			var target = event.target || event.srcElement;
			var siteId = target.id.replace(BX.Sale.Options.regionPrf, "");

			var regions = BX.Sale.Options.getValuesFromMultiselect(target);
			var countries = BX.Sale.Options.getValuesFromMultiselect(BX(BX.Sale.Options.countryPrf+siteId));

			BX.Sale.Options.reqTimeIntervalId = window.setInterval(function(){
					ShowWaitWindow();
					BX.selectUtils.deleteAllOptions(BX(BX.Sale.Options.cityPrf+siteId));
					BX.Sale.Options.getLocationCities(countries, regions, siteId)
				}, BX.Sale.Options.reqDelay
			);
		},

		getLocationCities: function(countryIds, regionIds, siteId)
		{
			window.clearInterval(BX.Sale.Options.reqTimeIntervalId);
			var url = "/bitrix/services/ajax/sale/location/index.php";

			var postData = {
				action: "getCityList",
				regionIds: regionIds,
				countryIds: countryIds,
				lang: BX.Sale.Options.lang,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout:   30,
				method:   'POST',
				dataType: 'json',
				url:       url,
				data:      postData,

				onsuccess: function(result)
				{
					CloseWaitWindow();
					if(result && result.DATA && !result.ERROR)
						BX.Sale.Options.setLocationCities(result.DATA, siteId);
					else if(result && result.ERROR)
						alert(BX.message("SMO_LOCATION_JS_GET_DATA_ERROR") + ": " + result.ERROR);
					else
						alert(BX.message("SMO_LOCATION_JS_GET_DATA_ERROR"));
				},

				onfailure: function()
				{
					CloseWaitWindow();
					alert(BX.message("SMO_LOCATION_JS_GET_DATA_ERROR"));
				}
			});
		},

		setLocationCities: function(cities, siteId)
		{
			if(!siteId)
				return false;

			var locSelCities = BX(BX.Sale.Options.cityPrf+siteId);

			if(!locSelCities)
				return false;

			BX.selectUtils.deleteAllOptions(locSelCities);

			BX.selectUtils.addNewOption(locSelCities, "", BX.message("SMO_LOCATION_ALL"), false, false);

			for(var i in cities)
				if(cities.hasOwnProperty(i))
					BX.selectUtils.addNewOption(locSelCities, i, cities[i], false, false);
		}
	};

})(window);