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

	var queryInstall = function(params)
	{
		var queryParam = {
			code: params.CODE
		};

		if (!!params.VERSION)
		{
			queryParam.version = params.VERSION;
		}

		if (!!params.CHECK_HASH)
		{
			queryParam.check_hash = params.CHECK_HASH;
			queryParam.install_hash = params.INSTALL_HASH;
		}

		if (!!params.FROM)
		{
			queryParam.from = params.FROM;
		}

		query(
			'install',
			queryParam,
			BX.delegate(
				function (result)
				{
					var isDoNothing = (
						params.hasOwnProperty('DO_NOTHING')
						&& (params['DO_NOTHING'] === 'Y' || params['DO_NOTHING'] === true)
					);
					if (!!result.error)
					{
						if (!!result.helperCode && result.helperCode !== '')
						{
							top.BX.UI.InfoHelper.show(result.helperCode);
						}
						var errorDom = BX('mp_error');
						var errorMessage = result.error + (
							!!result.error_description
								? '<br />' + result.error_description
								: ''
						);
						if (errorDom)
						{
							errorDom.innerHTML = errorMessage;
							BX.show(errorDom);
						}
						else
						{
							BX.UI.Notification.Center.notify(
								{
									content: errorMessage
								}
							);
						}
					}
					else if (!isDoNothing && !!result.redirect && params['REDIRECT_PRIORITY'] === true)
					{
						top.location.href = result.redirect;
					}
					else if (!isDoNothing && !params.IFRAME)
					{
						if (!!result.redirect)
						{
							top.location.href = result.redirect;
						}
						else
						{
							top.location.href = BX.util.remove_url_param(
								top.location.href,
								[
									'install',
								]
							);
						}
					}
					else
					{
						if (result.installed)
						{
							var eventResult = {};
							top.BX.onCustomEvent(
								top,
								'Rest:AppLayout:ApplicationInstall',
								[
									true,
									eventResult,
								],
								false
							);
						}

						if (!isDoNothing)
						{
							if (!!result.open)
							{
								BX.SidePanel.Instance.reload();
								top.BX.rest.AppLayout.openApplication(
									result.id,
									{}
								);
							}
							else
							{
								BX.SidePanel.Instance.reload();
							}
						}
					}
				},
				this
			)
		);
	};

	return {
		install: function(params)
		{
			if (!!params.SILENT_INSTALL && params.SILENT_INSTALL === 'Y')
			{
				queryInstall(params);
			}
			else
			{
				this.installPopup(params);
			}
		},

		installPopup: function(params)
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
									BX("mp_detail_error").innerHTML = BX.message("MARKETPLACE_LICENSE_TOS_ERROR_2");
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

								BX.addClass(button.buttonNode, "popup-window-button-wait");

								queryInstall(params);
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
						var url = params.url || location.href;
						if (url.indexOf("?") > 0)
						{
							url += '&label=startInstall'
						}
						else
						{
							url += '?label=startInstall'
						}

						return BX.ajax({
							'method': 'POST',
							'processData' : false,
							'url': url,
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

		uninstallConfirm: function(code, analyticsFrom)
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
									},
									analyticsFrom
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

		uninstall: function(code, clean, callback, analyticsFrom)
		{
			query(
				'uninstall',
				{
					code: code,
					clean: clean,
					from: analyticsFrom
				},
				function (result)
				{
					var eventResult = {};
					top.BX.onCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', [false, eventResult], false);

					if (!!callback)
					{
						callback(result);
					}
					else
					{
						if (!!result.error)
						{
							BX.UI.Notification.Center.notify(
								{
									content: result.error
								}
							);
						}
						else
						{
							location.reload();
						}
					}
				}
			);
		},

		reinstall: function(id, callback)
		{
			query('reinstall', {
				id: id
			}, function(result)
			{
				if(!!result.error)
				{

					if (!!result.helperCode && result.helperCode !== '')
					{
						top.BX.UI.InfoHelper.show(result.helperCode);
					}
					else
					{
						BX.UI.Notification.Center.notify({
							content: result.error
						});
					}
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
		buySubscription: function(params)
		{
			var btn = [];
			var canBuySubscription = BX.message("CAN_BUY_SUBSCRIPTION");
			var canActivateDemoSubscription = BX.message("CAN_ACTIVATE_DEMO_SUBSCRIPTION");

			if (!!canBuySubscription && canBuySubscription === 'Y')
			{
				btn.push(
					new BX.PopupWindowButton({
						text: BX.message("REST_MP_SUBSCRIPTION_BUTTON_TITLE"),
						className: "popup-window-button-accept",
						events: {
							click: this.openBuySubscription
						}
					})
				);
			}

			if (!!canActivateDemoSubscription && canActivateDemoSubscription === "Y")
			{
				btn.push(
					new BX.PopupWindowButtonLink({
						text: BX.message("REST_MP_SUBSCRIPTION_BUTTON_TITLE2"),
						className: "popup-window-button-link-cancel",
						events: {
							click: function()
							{
								this.openDemoSubscription();
							}.bind(this)
						}
					})
				);
			}

			var oPopup = BX.PopupWindowManager.create('marketplace_buy_subscription', null, {
				content: BX.create(
					'div',
					{
						props: {
							className: 'rest-marketplace-popup-block'
						},
						children: [
							BX.create(
								'div',
								{
									props: {
										className: 'rest-marketplace-popup-text-block'
									},
									children: [
										BX.create(
											'div',
											{
												props: {
													className: 'rest-marketplace-popup-text'
												},
												text: BX.message("REST_MP_SUBSCRIPTION_TEXT_1")
											}
										),
										BX.create(
											'div',
											{
												props: {
													className: 'rest-marketplace-popup-text'
												},
												text: BX.message("REST_MP_SUBSCRIPTION_TEXT_2")
											}
										),
										BX.create(
											'div',
											{
												props: {
													className: 'rest-marketplace-popup-text'
												},
												children: [
													BX.create(
														'div',
														{
															props: {
																className: 'rest-marketplace-popup-text'
															},
															html: BX.message("REST_MP_SUBSCRIPTION_TEXT_3").replace(
																'#ONCLICK#',
																'BX.rest.Marketplace.open(null,\'subscription\')'
															)
														}
													),
													BX.create(
														'ul',
														{
															children: [
																BX.create(
																	'li',
																	{
																		text: BX.message("REST_MP_SUBSCRIPTION_TEXT_3_LI_1")
																	}
																),
																BX.create(
																	'li',
																	{
																		text: BX.message("REST_MP_SUBSCRIPTION_TEXT_3_LI_2")
																	}
																),
																BX.create(
																	'li',
																	{
																		text: BX.message("REST_MP_SUBSCRIPTION_TEXT_3_LI_3")
																	}
																),
																BX.create(
																	'li',
																	{
																		text: BX.message("REST_MP_SUBSCRIPTION_TEXT_3_LI_4")
																	}
																),
																BX.create(
																	'li',
																	{
																		text: BX.message("REST_MP_SUBSCRIPTION_TEXT_3_LI_5")
																	}
																),
																BX.create(
																	'li',
																	{
																		text: BX.message("REST_MP_SUBSCRIPTION_TEXT_3_LI_6")
																	}
																),
															]
														}
													),
												]
											}
										),
										BX.create(
											'div',
											{
												props: {
													className: 'rest-marketplace-popup-text'
												},
												html: BX.message("REST_MP_SUBSCRIPTION_TEXT_4").replace(
													'#ONCLICK#',
													'top.BX.Helper.show(\'redirect=detail&code=12154172\');'
												)
											}
										),
										BX.create(
											'div',
											{
												props: {
													className: 'rest-marketplace-popup-text'
												},
												text: BX.message("REST_MP_SUBSCRIPTION_TEXT_5")
											}
										),
									]
								}
							),
						]
					}
				),
				titleBar: BX.message("REST_MP_SUBSCRIPTION_TITLE"),
				closeIcon : true,
				closeByEsc : true,
				draggable: true,
				lightShadow: true,
				overlay: true,
				className: 'landing-marketplace-popup-wrapper',
				buttons: btn
			}).show();
		},

		openBuySubscription: function()
		{
			var url = BX.message('REST_BUY_SUBSCRIPTION_URL');
			if (url !== '')
			{
				top.window.open(url, '_blank');
			}
			else
			{
				BX.UI.Notification.Center.notify(
					{
						content: BX.message('REST_MP_SUBSCRIPTION_ERROR_OPEN_BUY_URL')
					}
				);
			}
		},

		openDemoSubscription: function(callback)
		{
			var btnConfirm = new BX.UI.Button({
				color: BX.UI.Button.Color.SUCCESS,
				state: BX.UI.Button.State.DISABLED,
				text: BX.message('REST_MP_SUBSCRIPTION_BUTTON_DEMO_ACTIVE'),
				className: "rest-marketplace-popup-activate-subscription-btn",
				onclick: BX.delegate(
					function()
					{
						if (BX('mp_demo_subscription_license').checked)
						{
							btnConfirm.setState(
								BX.UI.Button.State.WAITING
							);
							query(
								'activate_demo',
								{},
								function(result)
								{
									if (!!result.error)
									{
										BX.UI.Notification.Center.notify(
											{
												content: result.error
											}
										);
										btnConfirm.setState(
											BX('mp_demo_subscription_license').checked ? BX.UI.Button.State.ACTIVE : BX.UI.Button.State.DISABLED
										);
									}
									else
									{
										if (BX.type.isFunction(callback))
										{
											callback(result);
										}
										else
										{
											var slider = BX.SidePanel.Instance.getTopSlider();
											if (!!slider)
											{
												slider.reload();
											}
											else
											{
												window.location.reload();
											}
										}
									}
								}
							)
						}
					},
					this
				)
			});
			var popupDemo = BX.PopupWindowManager.create('marketplace_demo_subscription', null, {
				content: BX.create(
					'div',
					{
						props: {
							className: 'rest-marketplace-popup-block'
						},
						children:
						[
							BX.create(
								'div',
								{
									props: {
										className: 'rest-marketplace-popup-text-block'
									},
									children: [
										BX.create(
											'div',
											{
												props: {
													className: 'rest-marketplace-popup-text'
												},
												text: BX.message("REST_MP_SUBSCRIPTION_DEMO_TITLE")
											}
										),
										BX.create(
											'div',
											{
												props: {
													className: 'rest-marketplace-popup-text'
												},
												html: BX.message("REST_MP_SUBSCRIPTION_DEMO_TEXT_1").replace(
													'#ONCLICK#',
													'BX.rest.Marketplace.open(null,\'subscription\')'
												)
											}
										),
										BX.create(
											'div',
											{
												props: {
													className: 'rest-marketplace-popup-text'
												},
												text: BX.message("REST_MP_SUBSCRIPTION_DEMO_TEXT_2")
											}
										),
										BX.create(
											'div',
											{
												style: {
													'margin-bottom': '8px',
													'margin-top': '15px'
												},
												children: [
													BX.create(
														'input',
														{
															attrs: {
																id: "mp_demo_subscription_license",
																type: "checkbox",
																name: 'ACCEPT_SUBSCRIPTION_LICENSE',
																value: 'Y'
															},
															events: {
																change: function (event)
																{
																	btnConfirm.setState(
																		event.target.checked ? BX.UI.Button.State.ACTIVE : BX.UI.Button.State.DISABLED
																	);
																}
															}
														}
													),
													BX.create(
														'label',
														{
															attrs: {
																for: "mp_demo_subscription_license",
															},
															html: BX.message("REST_MP_SUBSCRIPTION_DEMO_EULA_TITLE").replace('#LINK#', BX.message("REST_MP_SUBSCRIPTION_DEMO_EULA_LINK"))
														}
													)
												]
											}
										)
									]
								}
							)
						]
					}
				),
				titleBar: BX.message("REST_MP_SUBSCRIPTION_TITLE"),
				closeIcon : true,
				closeByEsc : true,
				draggable: true,
				lightShadow: true,
				overlay: true,
				className: 'landing-marketplace-popup-wrapper',
				buttons: [
					btnConfirm,
					new BX.PopupWindowButtonLink({
						text: BX.message("JS_CORE_WINDOW_CANCEL"),
						className: "popup-window-button-link-cancel",
						events: {
							click: function()
							{
								this.popupWindow.close();
							}
						}
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