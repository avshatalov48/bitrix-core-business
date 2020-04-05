/* Bitrix JS class for mobile Cloud Inspector */
__BitrixCloudMobMon = function(params) {

	for(var key in params)
		this[key] = params[key];
};

__BitrixCloudMobMon.prototype.deleteSite = function(domain)
{
	var _this = this;

	postData = {
		domain: domain,
		action: "delete",
		sessid: BX.bitrix_sessid()
	};

	app.showPopupLoader({text: BX.message("BCL_MOBILE_MONITORING_SITE_DELETING")+"..."});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'json',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result) {
			app.hidePopupLoader();
			if(result && !result.ERROR)
			{
				app.onCustomEvent("onAfterBCMMSiteDelete", {"domain" : domain});
				BX.onCustomEvent("onAfterBCMMSiteDelete", [{"domain" : domain}]);
			}
			else if(result.ERROR)
			{
				app.alert({ text: result.ERROR });
			}
			else
			{
				app.alert({ text: BX.message('BCL_MOBILE_MONITORING_SITE_DEL_ERROR') });
			}
		},
		onfailure: function(){
			app.alert({ text: BX.message('BCL_MOBILE_MONITORING_SITE_DEL_ERROR') });
		}
	});
};

__BitrixCloudMobMon.prototype.updateSite = function(domain, params)
{
	var _this = this;

	postData = params;
	postData.domain = domain;
	postData.action = "update";
	postData.sessid = BX.bitrix_sessid();

	app.showPopupLoader({text: BX.message("BCL_MOBILE_MONITORING_SITE_SAVING")+"..."});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'json',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result) {
			app.hidePopupLoader();
			if(result && !result.ERROR)
			{
				app.onCustomEvent("onAfterBCMMSiteUpdate", {"domain" : domain});
				BX.onCustomEvent("onAfterBCMMSiteUpdate", [{"domain" : domain}]);
			}
			else if(result.ERROR)
			{
				app.alert({ text: result.ERROR });
			}
			else
			{
				app.alert({ text: BX.message('BCL_MOBILE_MONITORING_SITE_SAVE_ERROR') });
			}
		},
		onfailure: function(){
			app.alert({ text: BX.message('BCL_MOBILE_MONITORING_SITE_SAVE_ERROR') });
		}

	});
};

__BitrixCloudMobMon.prototype.showRefreshing = function()
{
	app.showPopupLoader({text: BX.message("BCL_MOBILE_MONITORING_SITE_REFRESHING")+"..."});
};
