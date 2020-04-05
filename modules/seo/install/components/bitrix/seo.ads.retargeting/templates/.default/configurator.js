if (typeof(CrmAdsRetargeting) === "undefined")
{
	CrmAdsRetargeting = function (params)
	{
		this.containerId = params.containerId || 'crm-robot-ads-container-' + params.provider.TYPE;
		this.provider = params.provider;
		this.context = params.context;
		this.onRequest = params.onRequest;
		this.componentName = params.componentName;
		this.signedParameters = params.signedParameters;
		this.mess = params.mess;
		this.multiClients = !!params.multiClients;

		if (params.destroyEventName)
		{
			BX.addCustomEvent(window, params.destroyEventName, BX.proxy(function () {
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

		this.hasAudiences = false;
		this.loaded = [];
		if (this.multiClients && !this.clientId && !this.provider.PROFILE)
		{ // use first client by default
			for (var i=0; i<this.provider.CLIENTS.length; i++)
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
		cleanInstances: function ()
		{
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
		unbindAll: function ()
		{
			BX.removeCustomEvent(
				window,
				'seo-client-auth-result',
				BX.proxy(this.onSeoAuth, this)
			);
		},
		init: function ()
		{
			this.cleanInstances();
			this.instances.push(this);

			this.containerNode = BX(this.containerId);
			if (!this.containerNode)
			{
				this.containerNode = BX.create('div');
				this.containerNode.id = this.containerId;
			}

			this.insertTemplateIntoNode('settings', this.containerNode);

			this.uiNodes = {
				'avatar': this.containerNode.querySelector('[data-bx-ads-auth-avatar]'),
				'name': this.containerNode.querySelector('[data-bx-ads-auth-name]'),
				'link': this.containerNode.querySelector('[data-bx-ads-auth-link]'),
				'logout': this.containerNode.querySelector('[data-bx-ads-auth-logout]'),
				'clientBlock': this.containerNode.querySelector('[data-bx-ads-client]'),
				'clientInput': this.containerNode.querySelector('[data-bx-ads-client-input]'),
				'account': this.containerNode.querySelector('[data-bx-ads-account]'),
				'accountLoader': this.containerNode.querySelector('[data-bx-ads-account-loader]'),
				'audience': [],
				'errorNotFound': this.containerNode.querySelector('[data-bx-ads-audience-not-found]'),
				'refreshButton': this.containerNode.querySelector('[data-bx-ads-refresh-btn]'),
				'createLinks': BX.convert.nodeListToArray(
					this.containerNode.querySelectorAll('[data-bx-ads-audience-create-link]')
				),
				'autoRemover' : {
					'node': this.containerNode.querySelector('[data-bx-ads-audience-auto-remove]'),
					'checker': this.containerNode.querySelector('[data-bx-ads-audience-auto-remove-checker]'),
					'select': this.containerNode.querySelector('[data-bx-ads-audience-auto-remove-select]')
				},
				'addClientBtn': this.containerNode.querySelector('[data-bx-ads-client-add-btn]'),
				'addAudienceBtn': this.containerNode.querySelector('[data-bx-ads-audience-add]'),
				'regionInput':  this.containerNode.querySelector('[data-bx-ads-region]'),
				'regionLoader':  this.containerNode.querySelector('[data-bx-ads-region-loader]')
			};

			var attrAudience = 'data-bx-ads-audience';
			var attrAudienceLoader = 'data-bx-ads-audience-loader';
			var attrAudienceChecker = 'data-bx-ads-audience-checker';
			var audiences = this.containerNode.querySelectorAll('[' + attrAudience + ']');
			audiences = BX.convert.nodeListToArray(audiences);
			audiences.forEach(function (audienceNode){
				var audienceType = audienceNode.getAttribute(attrAudience);
				var checkerNode = this.containerNode.querySelector('[' + attrAudienceChecker + '="' + audienceType + '"]');
				var audienceLoaderSelector = '[' + attrAudienceLoader + (!audienceType ? '' : '="' + audienceType + '"') + ']';
				this.uiNodes.audience.push({
					type: audienceType,
					node: audienceNode,
					loader: this.containerNode.querySelector(audienceLoaderSelector),
					checker: checkerNode
				});

				BX.bind(checkerNode, 'change', function ()
				{
					audienceNode.disabled = audienceNode.options.length > 0
						?
						!this.checked
						:
						true;
				});

			}, this);


			this.uiNodes.createLinks.forEach(function (createLink) {
				BX.bind(createLink, 'click', BX.proxy(function () {
					if (!this.hasAudiences) this.showBlockRefresh();
				}, this));
			}, this);
			BX.bind(this.uiNodes.refreshButton, 'click', BX.proxy(function () {
				this.getProvider();
			}, this));


			if (this.uiNodes.autoRemover.checker)
			{
				BX.bind(this.uiNodes.autoRemover.checker, 'click', BX.proxy(function () {
					var autoRemover = this.uiNodes.autoRemover;
					autoRemover.select.disabled = !autoRemover.checker.checked;
				}, this));
			}

			this.loader.init(this);
			BX.bind(this.uiNodes.logout, 'click', BX.proxy(function () {
				this.logout(this.clientId);
			}, this));

			BX.bind(this.uiNodes.addAudienceBtn, 'click', BX.proxy(function () {
				this.addAudience(this.uiNodes.account.value);
			}, this));

			BX.bind(this.uiNodes.addClientBtn, 'click', BX.proxy(function () {
				BX.util.popup(_this.provider.AUTH_URL, 800, 600);
			}, this));

			this.listenSeoAuth();
			if (this.multiClients)
			{
				if (this.clientSelector) {
					this.clientSelector.destroy();
				}
				var _this = this;
				this.clientSelector = new BX.Seo.Ads.ClientSelector(this.uiNodes.clientBlock, {
					selected: this.provider.PROFILE,
					items: this.provider.CLIENTS,
					canAddItems: true,
					events: {
						onNewItem: function() {
							BX.util.popup(_this.provider.AUTH_URL, 800, 600);
						},
						onSelectItem: function(item) {
							_this.setProfile(item);
						},
						onRemoveItem: function(item) {
							_this.logout(item.CLIENT_ID);
						}
					}
				});
			}

			BX.UI.Hint.init(this.containerNode);
		},
		showBlockByAuth: function ()
		{
			if (this.provider.HAS_AUTH)
			{
				this.showBlockMain();
			}
			else
			{
				this.showBlockLogin();
			}
		},
		listenSeoAuth: function ()
		{
			BX.addCustomEvent(
				window,
				'seo-client-auth-result',
				BX.proxy(this.onSeoAuth, this)
			);
		},
		onSeoAuth: function (eventData)
		{
			eventData.reload = false;
			this.getProvider(eventData.clientId);
		},
		logout: function (clientId)
		{
			this.showBlock('loading');
			this.request('logout', {logoutClientId: clientId}, BX.delegate(function (provider) {
				this.provider = provider;
				if (this.clientSelector)
				{
					this.clientSelector.setSelected(this.provider.PROFILE);
					this.clientSelector.setItems(this.provider.CLIENTS);
				}
				this.showBlockByAuth();
			}, this));
		},
		addAudience: function (accountId)
		{
			var audienceName = this.entityTitleNode ? this.entityTitleNode.value : '';
			this.showNewAudiencePopup(accountId, audienceName);
		},
		getProvider: function (clientId)
		{
			this.showBlock('loading');
			this.request('getProvider', {}, BX.delegate(function (provider) {
				this.provider = provider;
				if (this.clientSelector)
				{
					if (!this.provider.PROFILE || (clientId && clientId != this.provider.PROFILE.CLIENT_ID)) {
						// set PROFILE equal to clientId or first record from CLIENTS:
						for (var i=0; i<this.provider.CLIENTS.length; i++)
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
		showBlock: function (blockCodes)
		{
			blockCodes = BX.type.isArray(blockCodes) ? blockCodes : [blockCodes];
			var attributeBlock = 'data-bx-ads-block';
			var blockNodes = this.containerNode.querySelectorAll('[' + attributeBlock + ']');
			blockNodes = BX.convert.nodeListToArray(blockNodes);
			blockNodes.forEach(function (blockNode) {
				var code = blockNode.getAttribute(attributeBlock);
				var isShow = BX.util.in_array(code, blockCodes);
				blockNode.style.display = isShow ? 'block' : 'none';
			}, this);
		},
		showBlockRefresh: function ()
		{
			this.showBlock(['auth', 'refresh']);
		},
		showBlockLogin: function ()
		{
			this.showBlock('login');

			var btn = BX('seo-ads-login-btn');
			if (btn && this.provider && this.provider.AUTH_URL)
			{
				btn.setAttribute(
					'onclick',
					'BX.util.popup(\'' + this.provider.AUTH_URL + '\', 800, 600);'
				);
			}
			if (this.uiNodes.clientInput) {
				this.uiNodes.clientInput.value = "";
			}
		},
		showBlockMain: function ()
		{
			if (this.uiNodes.avatar)
			{
				this.uiNodes.avatar.style['background-image'] = 'url(' + this.provider.PROFILE.PICTURE + ')';
			}
			if (this.uiNodes.name)
			{
				this.uiNodes.name.innerText = this.provider.PROFILE.NAME;
			}
			if (this.uiNodes.link)
			{
				if (this.provider.PROFILE.LINK)
				{
					this.uiNodes.link.setAttribute('href', this.provider.PROFILE.LINK);
				}
				else
				{
					this.uiNodes.link.removeAttribute('href');
				}
			}
			if (this.uiNodes.clientInput) {
				this.uiNodes.clientInput.value =
					this.provider.PROFILE && this.provider.PROFILE.CLIENT_ID ?
						this.provider.PROFILE.CLIENT_ID :
						"";
			}

			this.showBlock(['auth', 'main']);

			this.loadSettings();
		},
		insertTemplateIntoNode: function (templateCode, parentNode, isAppend)
		{
			isAppend = isAppend || false;
			var defaultTemplateId = 'template-crm-ads-dlg-' + templateCode;
			var templateId = defaultTemplateId + '-' + this.provider.TYPE;
			var templateNode = BX(templateId);
			if (!templateNode)
			{
				templateNode = BX(defaultTemplateId);
			}

			var temporaryContainerNode = BX.create('div');
			temporaryContainerNode.innerHTML = templateNode.innerHTML;

			if (!isAppend)
			{
				parentNode.innerHTML = '';
			}

			var childList = BX.convert.nodeListToArray(temporaryContainerNode.children);
			childList.forEach(function (child) {
				parentNode.appendChild(child);
			});
		},
		onResponse: function (response, callback)
		{
			if (!response.error)
			{
				callback.apply(this, [response.data]);
			}
		},
		request: function (action, requestData, callback) {
			requestData.action = action;
			requestData.type = this.provider.TYPE;
			requestData.clientId = this.clientId;

			if (this.onRequest)
			{
				this.onRequest.apply(this, [requestData, BX.delegate(function (response) {
					this.onResponse(response, callback);
				}, this)]);
			}
			else
			{
				this.sendActionRequest(action, requestData, function(response){
					this.onResponse(response, callback);
				});
			}
		},
		sendActionRequest: function (action, data, callbackSuccess, callbackFailure)
		{
			callbackSuccess = callbackSuccess || null;
			callbackFailure = callbackFailure || BX.proxy(this.showErrorPopup, this);
			data = data || {};

			var self = this;
			BX.ajax.runComponentAction(this.componentName, action, {
				'mode': 'class',
				'signedParameters': this.signedParameters,
				'data': data
			}).then(
				function (response)
				{
					var data = response.data || {};
					if(data.error)
					{
						callbackFailure.apply(self, [data]);
					}
					else if(callbackSuccess)
					{
						callbackSuccess.apply(self, [data]);
					}
				},
				function()
				{
					var data = {'error': true, 'text': ''};
					callbackFailure.apply(self, [data]);
				}
			);
		},
		showErrorPopup: function (data)
		{
			data = data || {};
			var text = data.text || this.mess.errorAction;
			var popup = BX.PopupWindowManager.create(
				'crm_ads_rtg_error',
				null,
				{
					autoHide: true,
					lightShadow: true,
					closeByEsc: true,
					overlay: {backgroundColor: 'black', opacity: 500},
					events: {
						'onPopupClose': this.onErrorPopupClose.bind(this)
					}
				}
			);
			popup.setButtons([
				new BX.PopupWindowButton({
					text: this.mess.dlgBtnClose,
					events: {click: function(){this.popupWindow.close();}}
				})
			]);
			popup.setContent('<span class="crm-ads-rtg-warning-popup-alert">' + text + '</span>');
			popup.show();
		},
		showNewAudiencePopup: function(accountId, audienceName)
		{
			var popup = BX.PopupWindowManager.create(
				'crm_ads_rtg_new_audience',
				null,
				{
					width: 500,
					autoHide: true,
					lightShadow: true,
					closeByEsc: true
				}
			);
			var input = BX.create('input', {
				attrs : {
					type: 'text',
					className : "crm-ads-rtg-input-input",
					value: audienceName
				}
			});
			var content =
				BX.create('div', {
					attrs : {
						className : "crm-ads-rtg-input"
					},
					children: [
						BX.create('div', {
							attrs : {
								className : "crm-ads-rtg-input-label",
							},
							html: this.mess.newAudienceNameLabel
						}),
						input
					]
				});
			popup.setContent(content);

			var _this = this;
			popup.setButtons([
				new BX.UI.Button({
					color: BX.UI.Button.Color.LINK,
					text: this.mess.dlgBtnCancel,
					events: {click: function(){popup.close();}}
				}),
				new BX.UI.Button({
					color: BX.UI.Button.Color.SUCCESS,
					text: this.mess.dlgBtnCreate,
					events: {click: function() {
						var name = input.value;
						popup.close();
						_this.hideAddAudienceButton();
						_this.loader.forAudience(true);
						_this.request('addAudience', {accountId: accountId, name: name}, BX.delegate(function(data) {
								_this.audienceId = data.id;
								_this.loadSettingsAudiences(accountId);
						}, _this));
					}}
				})
			]);
			popup.show();
		},
		onErrorPopupClose: function()
		{
			if (this.clientSelector) {
				this.clientSelector.enable();
				this.loader.forAccount(false);
				this.loader.forAudience(false);
				this.showAddAudienceButton();
			}
		},
		setProfile: function(item)
		{
			this.clientId = item && item.CLIENT_ID ? item.CLIENT_ID : null;
			this.provider.PROFILE = item;
			this.accountId = null;
			this.audienceId = null;
			if (this.containerNode) {
				this.showBlockMain();
			}
		},
		loader: {
			init: function (caller) {
				this.caller = caller;
			},
			change: function (loaderNode, inputNode, isShow) {
				loaderNode.style.display = isShow ? '' : 'none';
				if (inputNode)
				{
					inputNode.disabled = (!inputNode.options.length == 0 || isShow) ? false : true;
				}
			},
			forAccount: function (isShow) {
				this.change(this.caller.uiNodes.accountLoader, this.caller.uiNodes.account, isShow);
			},
			forAudience: function (isShow) {
				this.caller.uiNodes.audience.forEach(function (audience) {
					this.change(audience.loader, audience.node, isShow);
				}, this);

				if (this.caller.uiNodes.autoRemover.node)
				{
					this.caller.uiNodes.autoRemover.node.style.display = isShow ? 'none' : '';
				}
			},
			forRegion: function (isShow) {
				this.change(this.caller.uiNodes.regionLoader, this.caller.uiNodes.regionInput, isShow);
			},
		},
		loadSettings: function()
		{
			var type = this.provider.TYPE;
			var isSupportAccount = this.provider.IS_SUPPORT_ACCOUNT;
			var isSupportLookalikeAudience = this.audienceLookalikeMode && this.provider.IS_SUPPORT_LOOKALIKE_AUDIENCE;

			if (!this.provider.PROFILE) {
				return;
			}
			var typeLoaded = BX.util.in_array(type, this.loaded);
			if(!typeLoaded) {
				this.loaded.push(type);
			}

			if (isSupportLookalikeAudience && this.uiNodes.regionInput)
			{
				this.loadRegionsList();
			}

			if (this.uiNodes.account && isSupportAccount)
			{
				if(!typeLoaded) {
					var queryAudiences = function () {
						this.loadSettingsAudiences(this.uiNodes.account.value);
					};
					BX.bind(
						this.uiNodes.account,
						'change',
						queryAudiences.bind(this)
					);
				}
				this.loadSettingsAccounts();
			}
			else
			{
				this.loadSettingsAudiences(null);
			}
		},
		loadSettingsAccounts: function()
		{
			this.hideAddAudienceButton();
			this.loader.forAccount(true);
			for (var i=0; i < this.uiNodes.audience.length; i++) {
				this.uiNodes.audience[i].node.disabled = true;
				this.fillDropDownControl(this.uiNodes.audience[i].node, []);
			}
			if (this.clientSelector) {
				this.clientSelector.disable();
			}
			this.request('getAccounts', {}, BX.delegate(function(data){
				if (this.clientSelector) {
					this.clientSelector.enable();
				}
				var dropDownData = data.map(function (accountData) {
					return {
						caption: accountData.name,
						value: accountData.id,
						selected: accountData.id == this.accountId
					};
				}, this);

				this.fillDropDownControl(this.uiNodes.account, dropDownData);
				this.loader.forAccount(false);
				if (dropDownData.length > 0)
				{
					var updateWorker = function () {
						BX.fireEvent(this.uiNodes.account, 'change');
					};
					setTimeout(updateWorker.bind(this), 150);

				}
				else
				{
					this.ShowErrorEmptyAudiences();
				}

			}, this));
		},
		loadSettingsAudiences: function(accountId)
		{
			var isSupportLookalikeAudience = this.audienceLookalikeMode && this.provider.IS_SUPPORT_LOOKALIKE_AUDIENCE;
			if (isSupportLookalikeAudience)
				return;

			this.hideAddAudienceButton();
			var requestData = {
				'accountId': accountId || null
			};
			this.loader.forAudience(true);
			if (this.clientSelector) {
				this.clientSelector.disable();
			}
			this.request('getAudiences', requestData, BX.delegate(function(data){
				if (this.clientSelector) {
					this.clientSelector.enable();
				}
				var hasAudiences = false;
				var dropDownData = [];
				var dropDownDataByType = {};
				var isSupportMultiTypeContacts = this.provider.IS_SUPPORT_MULTI_TYPE_CONTACTS;

				this.hasAudiences = false;
				data.forEach(function (audienceData) {
					var dropDownItem = {
						caption: audienceData.name,
						value: audienceData.id,
						selected: audienceData.id == this.audienceId
					};

					dropDownData.push(dropDownItem);

					var dropDownItemCloned = BX.clone(dropDownItem);
					dropDownDataByType = dropDownDataByType || {};
					audienceData.supportedContactTypes = audienceData.supportedContactTypes || [];
					audienceData.supportedContactTypes.forEach(function (contactType) {
						dropDownDataByType[contactType] = dropDownDataByType[contactType] || [];
						if (this.audienceId)
						{
							dropDownItemCloned.selected = dropDownItemCloned.value == this.audienceId[contactType];
						}
						dropDownDataByType[contactType].push(dropDownItemCloned);
					}, this);
				}, this);

				if (!isSupportMultiTypeContacts)
				{
					var hasSelectedItemsOnce = false;
					for (var contactType in dropDownDataByType)
					{
						var audiences = this.uiNodes.audience.filter(function (audience) { return audience.type == contactType; });
						if (!audiences[0])
						{
							continue;
						}

						this.fillDropDownControl(audiences[0].node, dropDownDataByType[contactType]);

						var hasItems = dropDownDataByType[contactType].length > 0;
						var hasSelectedItems = dropDownDataByType[contactType].filter(function(item){return item.selected}).length > 0;

						audiences[0].checker.checked = hasSelectedItems;
						audiences[0].node.disabled = !hasSelectedItems;
						//BX.fireEvent(audiences[0].checker, 'change');

						if (hasSelectedItems) hasSelectedItemsOnce = true;
						if (hasItems) hasAudiences = true;
					}

					if (!hasSelectedItemsOnce)
					{
						this.uiNodes.audience.forEach(function (audience) {
							audience.checker.checked = true;
							//BX.fireEvent(audience.checker, 'change');
						});
					}
				}
				else
				{
					var audienceNode = this.uiNodes.audience[0].node;
					this.fillDropDownControl(
						audienceNode,
						dropDownData
					);

					if (audienceNode.options.length > 0) hasAudiences = true;
				}

				this.hasAudiences = hasAudiences;
				this.ShowErrorEmptyAudiences();
				this.loader.forAudience(false);

				this.showAddAudienceButton();

			}, this));
		},
		loadRegionsList: function()
		{
			this.loader.forRegion(true);
			this.fillDropDownControl(this.uiNodes.regionInput, []);
			if (this.clientSelector) {
				this.clientSelector.disable();
			}
			this.request('getRegions', {}, BX.delegate(function(data){
				if (this.clientSelector) {
					this.clientSelector.enable();
				}
				var dropDownData = data.map(function (regionData) {
					return {
						caption: regionData.name,
						value: regionData.id,
						selected: this.audienceRegion ? (regionData.id == this.audienceRegion) : regionData.isDefault
					};
				}, this);

				this.fillDropDownControl(this.uiNodes.regionInput, dropDownData);
				this.loader.forRegion(false);
			}, this));
		},
		ShowErrorEmptyAudiences: function()
		{
			if (this.uiNodes.errorNotFound)
			{
				this.uiNodes.errorNotFound.style.display = this.hasAudiences ? 'none' : '';
			}
		},
		hideAddAudienceButton: function()
		{
			if (this.uiNodes.addAudienceBtn)
				BX.hide(this.uiNodes.addAudienceBtn);
		},
		showAddAudienceButton: function()
		{
			if (this.uiNodes.addAudienceBtn)
				BX.show(this.uiNodes.addAudienceBtn);
		},
		fillDropDownControl: function(node, items)
		{
			items = items || [];
			node.innerHTML = '';

			items.forEach(function(item){
				if(!item || !item.caption)
				{
					return;
				}

				var option = document.createElement('option');
				option.value = item.value;
				option.selected = !!item.selected;
				option.innerText = item.caption;
				node.appendChild(option);
			});
		}
	};
}