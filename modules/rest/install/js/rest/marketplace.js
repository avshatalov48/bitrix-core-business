BX.namespace('BX.rest.Marketplace');

BX.rest.Marketplace = (function(){

	var ajaxPath = "/bitrix/tools/rest.php";

	var query = function(action, data, callback)
	{
		data.action = action;
		data.sessid = BX.bitrix_sessid();

		BX.ajax({
			dataType: 'json',
			method: 'POST',
			url: ajaxPath,
			data: data,
			onsuccess: callback,
			onfailure: function(error_type, error)
			{
				callback({error: error_type + (!!error ? ': ' + error : '')});
			}
		});
	};

	return {
		install: function(params)
		{
			params = params || {url:location.href};
			params.IFRAME = location.href.indexOf("IFRAME=Y") > 0;

			var loaded = false;

			var popup = BX.PopupWindowManager.create("BXAppInstallPopup|" + params.url, null, {
				autoHide: false,
				zIndex: 0,
				offsetLeft: 0,
				offsetTop: 0,
				overlay: true,
				draggable: {restrict: true},
//				titleBar: "...",
				closeByEsc: true,
				closeIcon: {right: "12px", top: "10px"},
				buttons: [
					(button = new BX.PopupWindowButton({
						text: BX.message("REST_MP_APP_INSTALL"),
						className: "popup-window-button-accept",
						events: {
							click: function()
							{
								if(!loaded)
								{
									return;
								}

								if (
									BX("mp_tos_license") && !BX("mp_tos_license").checked
								)
								{
									BX("mp_detail_error").innerHTML = BX.message("MARKETPLACE_LICENSE_TOS_ERROR");
									return;
								}

								if (
									BX("mp_detail_license") && !BX("mp_detail_license").checked
									|| BX("mp_detail_confidentiality") && !BX("mp_detail_confidentiality").checked
								)
								{
									BX("mp_detail_error").innerHTML = BX.message("MARKETPLACE_LICENSE_ERROR");
									return;
								}

								if (BX.hasClass(button.buttonNode, "popup-window-button-wait"))
								{
									return;
								}

								var form = document.forms["left-menu-preset-form"];
								BX.addClass(button.buttonNode, "popup-window-button-wait");

								var queryParam = {
									code: params.CODE
								};

								if(!!params.VERSION)
								{
									queryParam.version = params.VERSION;
								}

								if(!!params.CHECK_HASH)
								{
									queryParam.check_hash = params.CHECK_HASH;
									queryParam.install_hash = params.INSTALL_HASH;
								}

								query(
									'install',
									queryParam,
									BX.delegate(function(result)
									{
										if(!!result.error)
										{
											BX('mp_error').innerHTML = result.error
												+ (!!result.error_description
													? '<br /><br />' + result.error_description
													: ''
												);

											BX.show(BX('mp_error'));
										}
										else if(!!result.redirect && params['REDIRECT_PRIORITY'] === true)
										{
											top.location.href = result.redirect;
										}
										else if(!params.IFRAME)
										{
											if(!!result.redirect)
											{
												top.location.href = result.redirect;
											}
											else
											{
												top.location.href = BX.util.remove_url_param(top.location.href, ['install']);
											}
										}
										else
										{
											if(result.installed)
											{
												var eventResult = {};
												top.BX.onCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', [true, eventResult], false);
											}

											if(!!result.open)
											{
												BX.SidePanel.Instance.reload();
												top.BX.rest.AppLayout.openApplication(result.id, {});
											}
											else
											{
												BX.SidePanel.Instance.reload();
											}
										}
									}, this)
								);
							}
						}
					})),

					new BX.PopupWindowButtonLink({
						text: BX.message("REST_MP_APP_INSTALL_CANCEL"),
						className: "popup-window-button-link-cancel",
						events: {
							click: function()
							{
								this.popupWindow.close();
							}
						}
					})
				],
				content: '<div style="width:450px;height:230px; background: url(/bitrix/js/rest/images/loader.gif) no-repeat center;"></div>',
				events: {
					onAfterPopupShow: function()
					{
						return BX.ajax({
							'method': 'POST',
							'processData' : false,
							'url': params.url || location.href,
							'data':  BX.ajax.prepareData({
								install: 1,
								sessid: BX.bitrix_sessid(),
								dataType: 'json'
							}),
							'onsuccess': BX.delegate(function(result) {
								loaded = true;
								var res = BX.parseJSON(result);
								if (BX.type.isPlainObject(res) && res["status"] == "success")
								{
									this.setContent(res["data"]["content"]);
									this.setTitleBar(res["data"]["title"]);
								}
								else
								{
									this.setContent(result);
								}

								BX.defer(this.adjustPosition, this)();
							}, this)
						});
					}
				}
			});

			popup.show();
		},

		uninstallConfirm: function(code)
		{
			var popup = new BX.PopupWindow('mp_delete_confirm_popup', null, {
				content: '<div class="mp_delete_confirm"><div class="mp_delete_confirm_text">' + BX.message('REST_MP_DELETE_CONFIRM') + '</div><div class="mp_delete_confirm_cb"><input type="checkbox" name="delete_data" id="delete_data">&nbsp;<label for="delete_data">' + BX.message('REST_MP_DELETE_CONFIRM_CLEAN') + '</label></div></div>',
				closeByEsc: true,
				closeIcon: {top: '1px', right: '10px'},
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message("REST_MP_APP_DELETE"),
						className: "popup-window-button-decline",
						events: {
							click: function()
							{
								BX.rest.Marketplace.uninstall(
									code,
									BX('delete_data').checked,
									function(result) {
										if(result.error)
										{
											popup.setContent('<div class="mp_delete_confirm"><div class="mp_delete_confirm_text">' + result.error + '</div></div>');
											popup.setButtons([new BX.PopupWindowButtonLink({
												text: BX.message('JS_CORE_WINDOW_CLOSE'),
												className: "popup-window-button-link-cancel",
												events: {
													click: function()
													{
														this.popupWindow.close()
													}
												}
											})]);
											popup.adjustPosition();
										}
										else
										{
											if(!!result.sliderUrl)
											{
												BX.SidePanel.Instance.open(result.sliderUrl);
											}
											else
											{
												popup.close();
												window.location.reload();
											}
										}
									}
								);
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text: BX.message('JS_CORE_WINDOW_CANCEL'),
						className: "popup-window-button-link-cancel",
						events: {
							click: function()
							{
								this.popupWindow.close()
							}
						}
					})
				]
			});

			popup.show();
		},

		uninstall: function(code, clean, callback)
		{
			query('uninstall', {
				code: code,
				clean: clean
			}, function(result)
			{
				var eventResult = {};
				top.BX.onCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', [false, eventResult], false);

				if(!!callback)
				{
					callback(result);
				}
				else
				{
					if (!!result.error)
					{
						BX.UI.Notification.Center.notify({
							content: result.error
						});
					}
					else
					{
						location.reload();
					}
				}
			});
		},

		reinstall: function(id, callback)
		{
			query('reinstall', {
				id: id
			}, function(result)
			{
				if(!!result.error)
				{
					BX.UI.Notification.Center.notify({
						content: result.error
					});
				}
				else if(!!result.redirect)
				{
					BX.reload(result.redirect);
				}
				else
				{
					BX.UI.Notification.Center.notify({
						content: BX.message('REST_MP_APP_REINSTALLED')
					});
				}

				if(!!callback)
				{
					callback();
				}
			});
		},

		buy: function(bindElement, priceList)
		{
			var menu = [];

			for(var i = 0; i < priceList.length; i++)
			{
				menu.push({
					text: priceList[i].TEXT,
					href: priceList[i].LINK,
					target: '_blank',
					className: "menu-popup-no-icon"
				});
			}

			BX.PopupMenu.show("user-menu", bindElement, menu,
			{
				offsetTop: 9,
				offsetLeft: 43,
				angle: true
			});
		},
		buySubscription : function(params) 
		{
			var oPopup = BX.PopupWindowManager.create('marketplace_buy_subscription', null, {
				content: [
'\t\t<div class="rest-marketplace-popup-block">\n' +
'\t\t\t<div class="rest-marketplace-popup-text-block">\n' +
'\t\t\t\t<div class="rest-marketplace-popup-text">' + BX.message("REST_MP_SUBSCRIPTION_TEXT1") + '</div>\n' +
'\t\t\t\t<div class="rest-marketplace-popup-text">' + BX.message("REST_MP_SUBSCRIPTION_TEXT2") + '</div>' +
'\t\t\t\t<div class="rest-marketplace-popup-text">' + BX.message("REST_MP_SUBSCRIPTION_TEXT3") + '</div>\n' +
'\t\t\t</div>\n' +
'\t\t</div>\n'
				].join(),
				titleBar: BX.message("REST_MP_SUBSCRIPTION_TITLE"),
				closeIcon : true,
				closeByEsc : true,
				draggable: true,
				lightShadow: true,
				overlay: true,
				className: 'landing-marketplace-popup-wrapper',
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message("REST_MP_SUBSCRIPTION_BUTTON_TITLE"),
						className: "popup-window-button-accept"
					}),
					new BX.PopupWindowButtonLink({
						text: BX.message("REST_MP_SUBSCRIPTION_BUTTON_TITLE2"),
						className: "popup-window-button-link-cancel"
					})
				]
			}).show();
		},

		setRights: function(appId, siteId)
		{
			BX.Access.Init({
				other: {
					disabled: false,
					disabled_g2: true,
					disabled_cr: true
				},
				groups: {disabled: true},
				socnetgroups: {disabled: true}
			});

			var p = {app_id: appId};
			if(!!siteId)
			{
				p.site_id = siteId;
			}

			query(
				'get_app_rigths',
				p,
				function(result)
				{
					BX.Access.SetSelected(result, "bind");

					BX.Access.ShowForm({
						bind: "bind",
						showSelected: true,
						callback: function(arRights)
						{
							var p = {app_id: appId, rights: arRights};
							if(!!siteId)
							{
								p.site_id = siteId;
							}

							query(
								'set_app_rights',
								p,
								function(result)
								{
								}
							);
						}
					});
				}
			);
		},

		open: function(placementConfig, category)
		{
			if(!category)
			{
				category = 'all';
			}

			var url = BX.message("REST_MARKETPLACE_CATEGORY_URL").replace("#CODE#", category);

			if(!!placementConfig && !!placementConfig.PLACEMENT)
			{
				url = BX.util.add_url_param(url, {placement: placementConfig.PLACEMENT, category: category});
			}
			else
			{
				url = BX.util.add_url_param(url, {category: category});
			}

			var rule = BX.SidePanel.Instance.getUrlRule(url);
			var options = (rule && BX.type.isPlainObject(rule.options)) ? rule.options : {};
			options["cacheable"] = false;
			options["allowChangeHistory"] = false;
			options["requestMethod"] = "post";
			options["requestParams"] = { sessid: BX.bitrix_sessid() };
			BX.SidePanel.Instance.open(url, options);

			var slider = BX.SidePanel.Instance.getTopSlider();
			top.BX.addCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', function(installed, eventResult){
				eventResult.redirect = false;
				//slider.close();
			});
		},

		bindPageAnchors: function(param)
		{
			BX.ready(function()
			{
				BX.SidePanel.Instance.bindAnchors(top.BX.clone({
					rules: [
						{
							condition: [
								"/marketplace/detail/",
								"/bitrix/components/bitrix/rest.marketplace/lazyload.ajax.php"
							],
							options: {
								cacheable: false,
								allowChangeHistory: param.allowChangeHistory
							}
						}
					]
				}));
			});
		}
	};
})();