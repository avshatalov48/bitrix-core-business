if (typeof (CrmAdsRetargeting) === "undefined")
{
	CrmAdsRetargeting = function(params) {
		this.containerId = params.containerId || "crm-robot-ads-container-" + params.provider.TYPE;
		this.provider = params.provider;
		this.context = params.context;
		this.onRequest = params.onRequest;
		this.componentName = params.componentName;
		this.signedParameters = params.signedParameters;
		this.mess = params.mess;
		this.messageCode = params.messageCode;
		this.multiClients = !!params.multiClients;

		if (params.destroyEventName)
		{
			BX.addCustomEvent(window, params.destroyEventName, BX.proxy(function() {
				this.unbindAll();
				this.cleanInstances();
			}, this));
		}

		this.clientId = params.clientId;
		this.accountId = params.accountId;
		this.audienceId = params.audienceId;
		this.audienceRegion = params.audienceRegion;
		this.autoRemoveDayNumber = params.autoRemoveDayNumber;
		this.audienceLookalikeMode = params.audienceLookalikeMode;
		this.isSupportCreateLookalikeFromSegments = params.isSupportCreateLookalikeFromSegments;

		this.hasAudiences = false;
		this.loaded = [];
		if (this.multiClients && !this.clientId && !this.provider.PROFILE)
		{ // use first client by default
			for (var i = 0; i < this.provider.CLIENTS.length; i++)
			{
				this.setProfile(this.provider.CLIENTS[i]);
				break;
			}
		}
		this.init();
		this.showBlockByAuth();

		this.entityTitleNode = params.titleNodeSelector ? document.querySelector(params.titleNodeSelector) : null;
	};
	CrmAdsRetargeting.prototype = {
		instances: [],
		cleanInstances: function() {
			for (var i = 0, len = this.instances.length; i < len; i++)
			{
				if (!this.instances[i])
				{
					continue;
				}

				this.instances[i].unbindAll();
				delete this.instances[i];
				this.instances[i] = null;
			}
		},
		unbindAll: function() {
			BX.removeCustomEvent(
				window,
				"seo-client-auth-result",
				BX.proxy(this.onSeoAuth, this),
			);
		},
		init: function() {
			this.cleanInstances();
			this.instances.push(this);

			this.containerNode = BX(this.containerId);
			if (!this.containerNode)
			{
				this.containerNode = BX.create("div");
				this.containerNode.id = this.containerId;
			}

			this.insertTemplateIntoNode("settings", this.containerNode);

			this.uiNodes = {
				"avatar": this.containerNode.querySelector("[data-bx-ads-auth-avatar]"),
				"name": this.containerNode.querySelector("[data-bx-ads-auth-name]"),
				"link": this.containerNode.querySelector("[data-bx-ads-auth-link]"),
				"logout": this.containerNode.querySelector("[data-bx-ads-auth-logout]"),
				"clientBlock": this.containerNode.querySelector("[data-bx-ads-client]"),
				"clientInput": this.containerNode.querySelector("[data-bx-ads-client-input]"),
				"account": this.containerNode.querySelector("[data-bx-ads-account]"),
				"accountLoader": this.containerNode.querySelector("[data-bx-ads-account-loader]"),
				"audience": [],
				"errorNotFound": this.containerNode.querySelector("[data-bx-ads-audience-not-found]"),
				"refreshButton": this.containerNode.querySelector("[data-bx-ads-refresh-btn]"),
				"createLinks": BX.convert.nodeListToArray(
					this.containerNode.querySelectorAll("[data-bx-ads-audience-create-link]"),
				),
				"autoRemover": {
					"node": this.containerNode.querySelector("[data-bx-ads-audience-auto-remove]"),
					"checker": this.containerNode.querySelector("[data-bx-ads-audience-auto-remove-checker]"),
					"select": this.containerNode.querySelector("[data-bx-ads-audience-auto-remove-select]"),
				},
				"addClientBtn": this.containerNode.querySelector("[data-bx-ads-client-add-btn]"),
				"addAudienceBtn": this.containerNode.querySelector("[data-bx-ads-audience-add]"),
				"regionInput": this.containerNode.querySelector("[data-bx-ads-region]"),
				"regionLoader": this.containerNode.querySelector("[data-bx-ads-region-loader]"),
				"audienceSelectorBtn": this.containerNode.querySelector("#audience-selector-btn"),
				"audienceSelectorValue": this.containerNode.querySelector("#audience-selector-value"),
			};

			const audienceSelectorBtnMinWidth = 200;
			this.audienceSelectorBtn = new BX.UI.Button({
				color: BX.UI.Button.Color.LIGHT_BORDER,
				tag: BX.UI.ButtonTag.DIV,
				onclick: this.onAudienceSelectorBtnClick.bind(this),
				maxWidth: 420,
			});

			this.audienceSelectorBtn.renderTo(this.uiNodes.audienceSelectorBtn);
			BX.Dom.style(this.audienceSelectorBtn.getContainer(), { "min-width": `${audienceSelectorBtnMinWidth}px` });

			this.audienceMenu = this.generateAudienceMenu();

			this.uiNodes.createLinks.forEach(function(createLink) {
				BX.bind(createLink, "click", BX.proxy(function() {
					if (!this.hasAudiences)
					{
						this.showBlockRefresh();
					}
				}, this));
			}, this);
			BX.bind(this.uiNodes.refreshButton, "click", BX.proxy(function() {
				this.getProvider();
			}, this));

			if (this.uiNodes.autoRemover.checker)
			{
				BX.bind(this.uiNodes.autoRemover.checker, "click", BX.proxy(function() {
					var autoRemover = this.uiNodes.autoRemover;
					autoRemover.select.disabled = !autoRemover.checker.checked;
				}, this));
			}

			this.loader.init(this);
			BX.bind(this.uiNodes.logout, "click", BX.proxy(function() {
				this.logout(this.clientId);
			}, this));

			BX.bind(this.uiNodes.addAudienceBtn, "click", BX.proxy(function() {
				this.addAudience(this.uiNodes.account.value);
			}, this));

			BX.bind(this.uiNodes.addClientBtn, "click", BX.proxy(function() {
				BX.Seo.Ads.LoginFactory.getLoginObject(this.provider).login();
			}, this));

			this.listenSeoAuth();
			if (this.multiClients)
			{
				if (this.clientSelector)
				{
					this.clientSelector.destroy();
				}
				var _this = this;
				this.clientSelector = new BX.Seo.Ads.ClientSelector(this.uiNodes.clientBlock, {
					selected: this.provider.PROFILE,
					items: this.provider.CLIENTS,
					canAddItems: true,
					events: {
						onNewItem: function() {
							BX.Seo.Ads.LoginFactory.getLoginObject(_this.provider).login();
						},
						onSelectItem: function(item) {
							_this.setProfile(item);
						},
						onRemoveItem: function(item) {
							_this.logout(item.CLIENT_ID);
						},
					},
				});
			}

			BX.UI.Hint.init(this.containerNode);
		},
		showBlockByAuth: function() {
			if (this.provider.HAS_AUTH)
			{
				this.showBlockMain();
			}
			else
			{
				this.showBlockLogin();
			}
		},
		listenSeoAuth: function() {
			BX.addCustomEvent(
				window,
				"seo-client-auth-result",
				BX.proxy(this.onSeoAuth, this),
			);
		},
		onSeoAuth: function(eventData) {
			eventData.reload = false;
			this.getProvider(eventData.clientId);
		},
		logout: function(clientId) {
			var analyticsLabel =
				!(this.provider.TYPE === "facebook" || this.provider.TYPE === "instagram")
				? {}
				: {
						connect: "FBE",
						action: "disconnect",
						type: "disconnect",
					}
			;
			this.showBlock("loading");
			this.request(
				"logout",
				{ logoutClientId: clientId },
				BX.delegate(
					function(provider) {
						this.provider = provider;
						if (this.clientSelector)
						{
							this.clientSelector.setSelected(this.provider.PROFILE);
							this.clientSelector.setItems(this.provider.CLIENTS);
						}
						this.showBlockByAuth();
					},
					this,
				),
				analyticsLabel,
			);
		},
		addAudience: function(accountId) {
			var audienceName = this.entityTitleNode ? this.entityTitleNode.value : "";
			this.showNewAudiencePopup(accountId, audienceName);
		},
		getProvider: function(clientId) {
			this.showBlock("loading");
			this.request("getProvider", {}, BX.delegate(function(provider) {
				this.provider = provider;
				if (this.clientSelector)
				{
					if (!this.provider.PROFILE || (clientId && clientId != this.provider.PROFILE.CLIENT_ID))
					{
						// set PROFILE equal to clientId or first record from CLIENTS:
						for (var i = 0; i < this.provider.CLIENTS.length; i++)
						{
							var client = this.provider.CLIENTS[i];
							if (!clientId || clientId == client.CLIENT_ID)
							{
								this.setProfile(client);
								break;
							}
						}
					}
					this.clientSelector.setSelected(this.provider.PROFILE);
					this.clientSelector.setItems(this.provider.CLIENTS);
				}
				this.showBlockByAuth();
			}, this));
		},
		showBlock: function(blockCodes) {
			blockCodes = BX.type.isArray(blockCodes) ? blockCodes : [blockCodes];
			var attributeBlock = "data-bx-ads-block";
			var blockNodes = this.containerNode.querySelectorAll("[" + attributeBlock + "]");
			blockNodes = BX.convert.nodeListToArray(blockNodes);
			blockNodes.forEach(function(blockNode) {
				var code = blockNode.getAttribute(attributeBlock);
				var isShow = BX.util.in_array(code, blockCodes);
				blockNode.style.display = isShow ? "block" : "none";
			}, this);
		},
		showBlockRefresh: function() {
			this.showBlock(["auth", "refresh"]);
		},
		showBlockLogin: function() {
			this.showBlock("login");

			var btn = BX("seo-ads-login-btn");
			if (btn && this.provider && this.provider.AUTH_URL)
			{
				BX.bind(btn, "click", BX.proxy(function() {
					BX.Seo.Ads.LoginFactory.getLoginObject(this.provider).login();
				}, this));
			}
			if (this.uiNodes.clientInput)
			{
				this.uiNodes.clientInput.value = "";
			}
		},
		showBlockMain: function() {
			if (this.uiNodes.avatar)
			{
				this.uiNodes.avatar.style["background-image"] = "url(" + this.provider.PROFILE.PICTURE + ")";
			}
			if (this.uiNodes.name)
			{
				this.uiNodes.name.innerText = this.provider.PROFILE.NAME;
			}
			if (this.uiNodes.link)
			{
				if (this.provider.PROFILE.LINK)
				{
					this.uiNodes.link.setAttribute("href", this.provider.PROFILE.LINK);
				}
				else
				{
					this.uiNodes.link.removeAttribute("href");
				}
			}
			if (this.uiNodes.clientInput)
			{
				this.uiNodes.clientInput.value =
					this.provider.PROFILE && this.provider.PROFILE.CLIENT_ID ?
					this.provider.PROFILE.CLIENT_ID :
					"";
			}

			this.showBlock(["auth", "main"]);

			this.loadSettings();
		},
		insertTemplateIntoNode: function(templateCode, parentNode, isAppend) {
			isAppend = isAppend || false;
			var defaultTemplateId = "template-crm-ads-dlg-" + templateCode;
			var templateId = defaultTemplateId + "-" + this.provider.TYPE;
			var templateNode = BX(templateId);
			if (!templateNode)
			{
				templateNode = BX(defaultTemplateId);
			}

			var temporaryContainerNode = BX.create("div");
			temporaryContainerNode.innerHTML = templateNode.innerHTML;

			if (!isAppend)
			{
				parentNode.innerHTML = "";
			}

			var childList = BX.convert.nodeListToArray(temporaryContainerNode.children);
			childList.forEach(function(child) {
				parentNode.appendChild(child);
			});
		},
		onResponse: function(response, callback) {
			if (!response.error)
			{
				callback.apply(this, [response.data]);
			}
		},
		request: function(action, requestData, callback, analytics) {
			requestData.action = action;
			requestData.type = this.provider.TYPE;
			requestData.clientId = this.clientId;
			analytics = analytics || {};

			if (this.onRequest)
			{
				this.onRequest.apply(
					this,
					[requestData, BX.delegate(function(response) {
						this.onResponse(response, callback);
					}, this)],
				);
			}
			else
			{
				this.sendActionRequest(
					action,
					requestData,
					function(response) {
						this.onResponse(response, callback);
					},
					null,
					analytics,
				);
			}
		},
		sendActionRequest: function(action, data, callbackSuccess, callbackFailure, analytics) {
			callbackSuccess = callbackSuccess || null;
			callbackFailure = callbackFailure || BX.proxy(this.showErrorPopup, this);
			data = data || {};

			var self = this;
			BX.ajax.runComponentAction(this.componentName, action, {
				"mode": "class",
				"signedParameters": this.signedParameters,
				"data": data,
				"analyticsLabel": analytics,
			}).then(
				function(response) {
					var data = response.data || {};
					if (data.error)
					{
						callbackFailure.apply(self, [data]);
					}
					else if (callbackSuccess)
					{
						callbackSuccess.apply(self, [data]);
					}
				},
				function() {
					var data = { "error": true, "text": "" };
					callbackFailure.apply(self, [data]);
				},
			);
		},
		showErrorPopup: function(data) {
			data = data || {};
			var text = data.text || this.mess.errorAction;
			var popup = BX.PopupWindowManager.create(
				"crm_ads_rtg_error",
				null,
				{
					autoHide: true,
					lightShadow: true,
					closeByEsc: true,
					overlay: { backgroundColor: "black", opacity: 500 },
					events: {
						"onPopupClose": this.onErrorPopupClose.bind(this),
					},
				},
			);
			popup.setButtons([
				new BX.PopupWindowButton({
					text: this.mess.dlgBtnClose,
					events: {
						click: function() {
							this.popupWindow.close();
						},
					},
				}),
			]);
			popup.setContent("<span class=\"crm-ads-rtg-warning-popup-alert\">" + text + "</span>");
			popup.show();
		},
		showNewAudiencePopup: function(accountId, audienceName) {
			var popup = BX.PopupWindowManager.create(
				"crm_ads_rtg_new_audience",
				null,
				{
					width: 500,
					autoHide: false,
					lightShadow: true,
					closeByEsc: true,
				},
			);
			var input = BX.create("input", {
				attrs: {
					type: "text",
					className: "crm-ads-rtg-input-input",
					value: audienceName,
				},
			});
			var content =
				BX.create("div", {
					attrs: {
						className: "crm-ads-rtg-input",
					},
					children: [
						BX.create("div", {
							attrs: {
								className: "crm-ads-rtg-input-label",
							},
							html: this.mess.newAudienceNameLabel,
						}),
						input,
					],
				});
			popup.setContent(content);

			var _this = this;
			popup.setButtons([
				new BX.UI.Button({
					color: BX.UI.Button.Color.LINK,
					text: this.mess.dlgBtnCancel,
					events: {
						click: function() {
							popup.close();
						},
					},
				}),
				new BX.UI.Button({
					color: BX.UI.Button.Color.SUCCESS,
					text: this.mess.dlgBtnCreate,
					events: {
						click: function() {
							var name = input.value;
							popup.close();
							_this.hideAddAudienceButton();
							_this.request("addAudience", {
								accountId: accountId,
								name: name,
							}, BX.delegate(function(data) {
								_this.audienceId = data.id;
								_this.loadSettingsAudiences(accountId);
								if (_this.audienceId)
								{
									this.setSubmitAudienceData(_this.audienceId);
								}
							}, _this));
						},
					},
				}),
			]);
			popup.show();
		},
		onErrorPopupClose: function() {
			if (this.clientSelector)
			{
				this.clientSelector.enable();
				this.loader.forAccount(false);
				this.showAudienceLoadingState(false);
				this.showAddAudienceButton();
			}
		},
		setProfile: function(item) {
			this.clientId = item && item.CLIENT_ID ? item.CLIENT_ID : null;
			this.provider.PROFILE = item;
			this.accountId = null;
			this.audienceId = null;
			if (this.containerNode)
			{
				this.showBlockMain();
			}
		},
		loader: {
			init: function(caller) {
				this.caller = caller;
			},
			change: function(loaderNode, inputNode, isShow) {
				loaderNode.style.display = isShow ? "" : "none";
				if (inputNode)
				{
					inputNode.disabled = (!inputNode.options.length == 0 || isShow) ? false : true;
				}
			},
			forAccount: function(isShow) {
				this.change(this.caller.uiNodes.accountLoader, this.caller.uiNodes.account, isShow);
			},
			forAudience: function(isShow) {
				this.caller.uiNodes.audience.forEach(function(audience) {
					this.change(audience.loader, audience.node, isShow);
				}, this);

				if (this.caller.uiNodes.autoRemover.node)
				{
					this.caller.uiNodes.autoRemover.node.style.display = isShow ? "none" : "";
				}
			},
			forRegion: function(isShow) {
				this.change(this.caller.uiNodes.regionLoader, this.caller.uiNodes.regionInput, isShow);
			},
		},
		loadSettings: function() {
			var type = this.provider.TYPE;
			var isSupportAccount = this.provider.IS_SUPPORT_ACCOUNT;
			var isSupportLookalikeAudience = this.audienceLookalikeMode && this.provider.IS_SUPPORT_LOOKALIKE_AUDIENCE;

			if (!this.provider.PROFILE)
			{
				return;
			}
			var typeLoaded = BX.util.in_array(type, this.loaded);
			if (!typeLoaded)
			{
				this.loaded.push(type);
			}

			if (isSupportLookalikeAudience && this.uiNodes.regionInput)
			{
				this.loadRegionsList();
			}

			if (this.uiNodes.account && isSupportAccount)
			{
				if (!typeLoaded)
				{
					var queryAudiences = function() {
						this.loadSettingsAudiences(this.uiNodes.account.value);
					};
					BX.bind(
						this.uiNodes.account,
						"change",
						queryAudiences.bind(this),
					);
				}
				this.loadSettingsAccounts();
			}
			else
			{
				this.loadSettingsAudiences(null);
			}
		},
		loadSettingsAccounts: function() {
			this.hideAddAudienceButton();
			if (this.clientSelector)
			{
				this.clientSelector.disable();
			}
			this.request("getAccounts", {}, BX.delegate(function(data) {
				if (this.clientSelector)
				{
					this.clientSelector.enable();
				}
				var dropDownData = data.map(function(accountData) {
					return {
						caption: accountData.name,
						value: accountData.id,
						selected: accountData.id == this.accountId,
					};
				}, this);

				this.fillDropDownControl(this.uiNodes.account, dropDownData);
				this.loader.forAccount(false);
				if (dropDownData.length > 0)
				{
					var updateWorker = function() {
						BX.fireEvent(this.uiNodes.account, "change");
					};
					setTimeout(updateWorker.bind(this), 150);
				}
				else
				{
					this.setClassForAddAudienceButton();
					this.ShowErrorEmptyAudiences();
				}

			}, this));
		},
		loadSettingsAudiences: function(accountId) {
			var isSupportLookalikeAudience = this.isSupportLookalikeAudience();
			if (isSupportLookalikeAudience && this.isSupportCreateLookalikeFromSegments)
			{
				return;
			}

			this.showAudienceLoadingState(true);
			this.hideAddAudienceButton();

			var requestData = {
				"accountId": accountId || null,
				"messageCode": this.messageCode,
			};
			if (this.clientSelector)
			{
				this.clientSelector.disable();
			}
			this.request("getAudiencesWithNormalizedStatus", requestData, BX.delegate(function(data) {
				if (this.clientSelector)
				{
					this.clientSelector.enable();
				}

				this.showAddAudienceButton();

				this.clearAudienceMenu();
				this.fillAudienceMenuFromResponseData(data);
				this.updateAudienceSelectorBtnByMenu(this.audienceMenu);

				this.hasAudiences = this.audienceMenu.getMenuItems().length > 0;
				this.setClassForAddAudienceButton();

				this.showAudienceLoadingState(false);
			}, this));
		},
		showAudienceLoadingState: function(isShow) {
			this.audienceSelectorBtn.setWaiting(isShow);
			this.audienceSelectorBtn.setDropdown(!isShow);
		},
		setAudienceSelectorBtnText: function(text) {
			this.audienceSelectorBtn.setText(text);
		},
		updateAudienceSelectorBtnByMenu: function(menu) {
			const menuAudiences = menu.getMenuItems();

			const selectedAudienceId = String(this.audienceId);
			let selectedAudienceData = menuAudiences.find((audienceData) =>
				String(audienceData.id) === selectedAudienceId
			);

			if (selectedAudienceData !== undefined)
			{
				this.setAudienceSelectorBtnText(selectedAudienceData.text);
			}
			else
			{
				this.setAudienceSelectorBtnText(this.mess.chooseAudience);
			}
		},
		onAudienceSelectorBtnClick: function() {
			this.audienceMenu.toggle();
		},
		setSubmitAudienceData: function(audienceId) {
			this.uiNodes.audienceSelectorValue.value = String(audienceId);
		},
		fillAudienceMenuFromResponseData: function(data)
		{
			if (data.length === 0)
			{
				return;
			}

			const audienceDataWithoutPlaceholder = this.removePlaceHolderAudienceFromResponseData(data);

			const audienceGroups = [];
			audienceDataWithoutPlaceholder.forEach((audienceData) => {
				const findAudienceGroupByData = (audienceData) => {
					let group = audienceGroups.find(
						(item) =>
							item.groupInfo.status === audienceData.status
							&& item.groupInfo.normalizedStatus === audienceData.normalizedStatus,
					);
					if (group !== undefined)
					{
						return group;
					}

					group = audienceGroups.find(
						(item) => (item.groupInfo.normalizedStatus === audienceData.normalizedStatus)
							&& ["READY", "PROCESSING"].includes(audienceData.normalizedStatus),
					);

					return group;
				};

				const group = findAudienceGroupByData(audienceData);
				if (group === undefined)
				{
					audienceGroups.push({
						groupInfo: {
							status: audienceData.status,
							normalizedStatus: audienceData.normalizedStatus,
							normalizedStatusMessage: audienceData.normalizedStatusMessage,
							isEnabled: audienceData.isEnabled,
						},
						items: [audienceData],
					});
				}
				else
				{
					group.items.push(audienceData);
				}
			});

			const orderedAudienceGroups = audienceGroups.sort((audience1, audience2) => {
				if (audience1.groupInfo.normalizedStatus === "READY")
				{
					return -1;
				}
				if (audience2.groupInfo.normalizedStatus === "READY")
				{
					return 1;
				}

				if (audience1.groupInfo.isEnabled || audience2.groupInfo.isEnabled)
				{
					return audience2.groupInfo.isEnabled - audience1.groupInfo.isEnabled;
				}

				if (audience1.groupInfo.normalizedStatus === "PROCESSING")
				{
					return -1;
				}
				if (audience2.groupInfo.normalizedStatus === "PROCESSING")
				{
					return 1;
				}

				return 0;
			});

			orderedAudienceGroups.forEach((audienceGroup) => {
				this.audienceMenu.addMenuItem({
					delimiter: true,
					text: audienceGroup.groupInfo.normalizedStatusMessage,
				});

				audienceGroup.items.forEach((audience) => this.audienceMenu.addMenuItem({
					id: String(audience.id),
					text: audience.name,
					value: audience.id,
					disabled: !audienceGroup.groupInfo.isEnabled,
					onclick: this.getClickAudienceMenuItemEventHandler(audience),
				}));
			});
		},
		removePlaceHolderAudienceFromResponseData: function(data) {
			return data.filter((audience) => Number(audience.id) !== -1);
		},
		generateAudienceMenu: function() {
			return new BX.Main.Menu({
				maxHeight: 340,
				bindOptions: {
					forceTop: true,
				},
				bindElement: this.uiNodes.audienceSelectorBtn,
			});
		},
		clearAudienceMenu: function() {
			this.audienceMenu = this.generateAudienceMenu();
		},
		getClickAudienceMenuItemEventHandler(menuItemOptions)
		{
			return () => {
				this.audienceSelectorBtn.setText(menuItemOptions.name);
				this.audienceMenu.close();
				this.setSubmitAudienceData(menuItemOptions.id);
			};
		},
		loadRegionsList: function() {
			this.loader.forRegion(true);
			this.fillDropDownControl(this.uiNodes.regionInput, []);
			if (this.clientSelector)
			{
				this.clientSelector.disable();
			}
			this.request("getRegions", {}, BX.delegate(function(data) {
				if (this.clientSelector)
				{
					this.clientSelector.enable();
				}
				var dropDownData = data.map(function(regionData) {
					return {
						caption: regionData.name,
						value: regionData.id,
						selected: this.audienceRegion ? (regionData.id == this.audienceRegion) : regionData.isDefault,
					};
				}, this);

				this.fillDropDownControl(this.uiNodes.regionInput, dropDownData);
				this.loader.forRegion(false);
			}, this));
		},
		ShowErrorEmptyAudiences: function() {
			if (this.uiNodes.errorNotFound)
			{
				this.uiNodes.errorNotFound.style.display = this.hasAudiences ? "none" : "";
			}
		},
		setClassForAddAudienceButton: function() {
			if (this.uiNodes.addAudienceBtn && !this.hasAudiences)
			{
				this.uiNodes.addAudienceBtn.classList.remove('ui-btn-link');
				this.uiNodes.addAudienceBtn.classList.add('ui-btn-success');
			}
			else
			{
				this.uiNodes.addAudienceBtn.classList.remove('ui-btn-success');
				this.uiNodes.addAudienceBtn.classList.add('ui-btn-link');
			}
		},
		hideAddAudienceButton: function() {
			if (this.uiNodes.addAudienceBtn)
			{
				BX.hide(this.uiNodes.addAudienceBtn);
			}
		},
		showAddAudienceButton: function() {
			if (this.uiNodes.addAudienceBtn)
			{
				BX.show(this.uiNodes.addAudienceBtn);
			}
		},
		fillDropDownControl: function(node, items) {
			items = items || [];
			node.innerHTML = "";

			items.forEach(function(item) {
				if (!item || !item.caption)
				{
					return;
				}

				var option = document.createElement("option");
				option.value = item.value;
				option.selected = !!item.selected;
				option.disabled = !!item.disabled;
				option.innerText = item.caption;
				node.appendChild(option);
			});
		},
		isSupportLookalikeAudience: function() {
			return this.audienceLookalikeMode && this.provider.IS_SUPPORT_LOOKALIKE_AUDIENCE;
		},
	};
}
