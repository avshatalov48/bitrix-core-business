/**
 * Class BX.Sale.Delivery
 */
;(function(window) {

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.Delivery) return;

	BX.Sale.Delivery = {

		ajaxUrl: "sale_delivery_ajax.php",

		showGroupsDialog: function(nextUrl, currentGroup)
		{
			var storeForm = new BX.CDialog({
				'title': BX.message("SALE_DSE_CHOOSE_GROUP_TITLE"),
				'head': BX.message("SALE_DSE_CHOOSE_GROUP_HEAD"),
				'content_url': this.ajaxUrl,
				'content_post': 'currentGroup='+currentGroup+"&action=get_group_dialog_content&selectedGroupId="+currentGroup+"&sessid="+BX.bitrix_sessid(),
				'width': 350,
				'height':300,
				'resizable':false,
				'draggable':false
			});

			var button = [
				{
					title: BX.message("SALE_DSE_CHOOSE_GROUP_SAVE"),
					id: 'GROUP_SAVE',
					'action': function ()
					{
						var href = BX.util.remove_url_param(nextUrl, "PARENT_ID"),
							parentId = BX.Sale.Delivery.getGroupFromDialog();

						if(parentId)
							href = BX.util.add_url_param(href, {PARENT_ID: parentId});

						BX.showWait();
						window.location.href = href;
					}
				},
				BX.CDialog.btnCancel
			];
			storeForm.ClearButtons();
			storeForm.SetButtons(button);
			storeForm.Show();
		},

		getGroupFromDialog: function()
		{
			return BX('sale_delivery_group_choose').value;
		},

		setLHEClass: function (lheDivId)
		{
			BX.ready(
				function(){
					var lheDivObj = BX(lheDivId);

					if(lheDivObj)
						BX.addClass(lheDivObj, 'adm-sale-lhe-frame-dlvrs-dscr');
				});
		},

		createFlagFieldChanged: function(fieldName, domNode)
		{
			if(domNode.parentNode.lastChild.name && domNode.parentNode.lastChild.name == "CHANGED_FIELDS[]")
				return;

			domNode.parentNode.appendChild(
				BX.create('input',{
					props:{
						type: 'hidden',
						name: "CHANGED_FIELDS[]",
						value: fieldName
					}
				})
			);
		},

		toggleStores: function()
		{
			var storesRow = BX("sale-admin-delivery-stores");

			if(!storesRow)
				return;

			if(storesRow.style.display == "none")
				storesRow.style.display = "";
			else
				storesRow.style.display = "none";
		},

		createGroup: function()
		{
			var dialog = new BX.CDialog({
				'content': BX.message("SALE_DSE_GROUP_NAME")+': <input type="text" id="sale_delivery_group_add">',
				'title': BX.message("SALE_DSE_GROUP_CREATE_G"),
				'width': 350,
				'height': 55
			});

			dialog.ClearButtons();
			dialog.SetButtons([
				{
					'title': BX.message("SALE_DSE_GROUP_CREATE"),
					'action': function() {

						var select = BX("sale_delivery_group_choose"),
							input = BX("sale_delivery_group_add"),
							option = BX.create("option"),
							groupName = BX("GROUP_NAME");

						groupName.value = input.value;
						option.text = input.value;
						option.value = "new";
						select.add(option);
						select.value = "new";
						this.parentWindow.Close();
					}
				},
				BX.CDialog.prototype.btnCancel
			]);

			dialog.Show();
		},

		getRestrictionParamsHtml: function(params)
		{
			if(!params.class)
				return;

			params.params = params.params || {};
			params.restrictionId = params.restrictionId || 0;
			params.sort = params.sort || 100;
			params.publicMode = params.publicMode || 'N';

			ShowWaitWindow();

			var postData = {
				action: "get_restriction_params_html",
				className: params.class,
				params: params.params,
				deliveryId: params.deliveryId,
				sort: params.sort,
				lang: params.lang,
				publicMode: params.publicMode,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout:    30,
				method:     'POST',
				dataType:   'json',
				url:        this.ajaxUrl,
				data:       postData,

				onsuccess: function(result)
				{
					CloseWaitWindow();

					if(result && result.RESTRICTION_HTML && !result.ERROR)
					{
						var data = BX.processHTML(result.RESTRICTION_HTML);
						BX.Sale.Delivery.showRestrictionParamsDialog(data['HTML'], params);
						window["deliveryGetRestrictionHtmlScriptsLoadingStarted"] = false;

						//process scripts
						var scrs = function(loadScripts)
						{
							if(!loadScripts)
								BX.removeCustomEvent('deliveryGetRestrictionHtmlScriptsReady', scrs);

							for (var i in data['SCRIPT'])
							{
								if(!data['SCRIPT'].hasOwnProperty(i))
									continue;

								BX.evalGlobal(data['SCRIPT'][i]['JS']);
								delete(data['SCRIPT'][i]);

								//It can be nesessary  at first to load some JS for restriction form
								if(loadScripts && window["deliveryGetRestrictionHtmlScriptsLoadingStarted"])
									return;
							}
						};

						BX.addCustomEvent('deliveryGetRestrictionHtmlScriptsReady', scrs);
						scrs(true);
						BX.loadCSS(data['STYLE']);
					}
					else if(result && result.ERROR)
					{
						BX.debug("Error receiving restriction params html: " + result.ERROR);
					}
					else
					{
						BX.debug("Error receiving restriction params html!");
					}
				},

				onfailure: function()
				{
					CloseWaitWindow();
					BX.debug("Error adding restriction!");
				}
			});
		},

		showRestrictionParamsDialog: function(content, rstrParams)
		{
			if(rstrParams.class == '\\Bitrix\\Sale\\Delivery\\Restrictions\\ByLocation'
				|| rstrParams.class == '\\Bitrix\\Sale\\Delivery\\Restrictions\\ExcludeLocation')
			{
				var width = 1030;
			}
			else
			{
				width = 600;
			}

			var	dialog = new BX.CDialog({
					'content': '<form id="sale-delivery-restriction-edit-form">'+
						content+
						'</form>',
					'title': BX.message("SALE_RDL_RESTRICTION")+" "+rstrParams.title,
					'width': width,
					'height': 600,
					'resizable': true
				});

			dialog.ClearButtons();
			dialog.SetButtons([
				{
					'title': BX.message("SALE_RDL_SAVE"),
					'action': function() {

							var form = BX("sale-delivery-restriction-edit-form"),
								prepared = BX.ajax.prepareForm(form),
								values =  !!prepared && prepared.data ? prepared.data : {};

						BX.Sale.Delivery.saveRestriction(rstrParams, values);
						this.parentWindow.Close();
					}
				},
				BX.CDialog.prototype.btnCancel
			]);

			BX.addCustomEvent(dialog, 'onWindowClose', function(dialog) {
				dialog.DIV.parentNode.removeChild(dialog.DIV);
			});

			dialog.Show();
			dialog.adjustSizeEx();
		},

		saveRestriction: function(rstrParams, values)
		{
			if(!rstrParams.class || !rstrParams.deliveryId)
				return;

			ShowWaitWindow();

			var params = values.RESTRICTION || {},
				postData = {
					action: "save_restriction",
					params: params,
					sort: values.SORT,
					className: rstrParams.class,
					deliveryId: rstrParams.deliveryId,
					restrictionId: rstrParams.restrictionId,
					sessid: BX.bitrix_sessid(),
					lang: BX.message('LANGUAGE_ID')
				};

			BX.ajax({
				timeout:    30,
				method:     'POST',
				dataType:   'json',
				url:        this.ajaxUrl,
				data:       postData,

				onsuccess: function(result)
				{
					CloseWaitWindow();

					if(result && !result.ERROR)
					{
						if(result.HTML)
							BX.Sale.Delivery.insertAjaxRestrictionHtml(result.HTML);

						if(result.ERROR)
							BX.debug("Error saving restriction: " + result.ERROR);
					}
					else
					{
						BX.debug("Error saving restriction!");
					}
				},

				onfailure: function()
				{
					CloseWaitWindow();
					BX.debug("Error refreshing restriction!");
				}
			});
		},

		deleteRestriction: function(restrictionId, deliveryId)
		{
			if(!restrictionId)
				return;

			ShowWaitWindow();

			var postData = {
				action: "delete_restriction",
				restrictionId: restrictionId,
				deliveryId: deliveryId,
				sessid: BX.bitrix_sessid(),
				lang: BX.message('LANGUAGE_ID')
			};

			BX.ajax({
				timeout:    30,
				method:     'POST',
				dataType:   'json',
				url:        this.ajaxUrl,
				data:       postData,

				onsuccess: function(result)
				{
					CloseWaitWindow();

					if(result && !result.ERROR)
					{
						if(result.HTML)
							BX.Sale.Delivery.insertAjaxRestrictionHtml(result.HTML);

						if(result.ERROR)
							BX.debug("Error deleting restriction: " + result.ERROR);
					}
					else
					{
						BX.debug("Error deleting restriction!");
					}
				},

				onfailure: function()
				{
					CloseWaitWindow();
					BX.debug("Error refreshing restriction!");
				}
			});
		},

		insertAjaxRestrictionHtml: function(html)
		{
			var data = BX.processHTML(html),
				container = BX("sale-delivery-restriction-container");

			if(!container)
				return;

			BX.loadCSS(data['STYLE']);

			container.innerHTML = data['HTML'];

			for (var i in data['SCRIPT'])
				if(data['SCRIPT'].hasOwnProperty(i))
					BX.evalGlobal(data['SCRIPT'][i]['JS']);
		},

		resetRusPostSettings: function()
		{
			window.location.href.search('RESET_HANDLER_SETTINGS') != -1 ? window.location.reload(true) : window.location.href += '&RESET_HANDLER_SETTINGS=Y';
		},

		resetRusPostTarifSettings: function()
		{
			window.location.href.search('RESET_TARIF_SETTINGS') != -1 ? window.location.reload(true) : window.location.href += '&RESET_TARIF_SETTINGS=Y';
		},

		addRestrictionProductSection: function(id, name, nodeId)
		{
			var deprecatedSupport = !(typeof nodeId === 'string');
			if (deprecatedSupport)
			{
				nodeId = 'sale-admin-delivery-restriction-cat';
			}

			name = BX.util.htmlspecialcharsback(name);
			name = name.replace(/&#039;/g, "'").replace(/&nbsp;/g, ' ');

			var alreadyExist = BX(nodeId + '-' + id);

			if (alreadyExist)
			{
				return;
			}

			var self = this;

			var category = BX.create('tr', {
				props: {
					id: nodeId + '-' + id,
					className: 'adm-s-product-category-restriction-delcat'
				},
				children:[
					BX.create('td', {
						children:[
							BX.create('span', {
								html: " - "+ BX.util.htmlspecialchars(name)
							}),
							BX.create('input', {
								props: {
									type: 'hidden',
									name: 'RESTRICTION[CATEGORIES][]',
									value: id
								}
							})
						]
					}),
					BX.create('td', {
						props: {
							align: 'right'
						},
						children: [
							BX.create('text', {
								html: '&nbsp;'
							}),
							BX.create('a', {
								props: {
									href: 'javascript:void(0);',
									className: 'adm-s-bus-morelinkqhsw'
								},
								text: BX.message('SALE_PRODUCT_CATEGORY_INP_DELETE'),
								events: {
									click: function() {
										self.deleteRestrictionProductSection(id, nodeId);
									}
								}
							})
						]
					})
				]
			});

			BX(nodeId + '-content').appendChild(category);
		},

		deleteRestrictionProductSection: function(id, nodeId)
		{
			var deprecatedSupport = !(typeof nodeId === 'string');
			if (deprecatedSupport)
			{
				nodeId = 'sale-admin-delivery-restriction-cat';
			}

			var node = BX(nodeId + '-' + id);
			if (node)
			{
				node.parentNode.removeChild(node);
			}
		},

		addRestrictionByConcreteProduct: function (nodeId, id, name)
		{
			name = BX.util.htmlspecialcharsback(name);
			name = name.replace(/&#039;/g, "'").replace(/&nbsp;/g, ' ');

			var alreadyExist = BX(nodeId + '-' + id);

			if (alreadyExist)
			{
				return;
			}

			var self = this;

			var product = BX.create('tr', {
				props: {
					id: nodeId + '-' + id,
					className: 'adm-s-concrete-product-restriction-delprod'
				},
				children: [
					BX.create('td', {
						children:[
							BX.create('span', {
								text: " - "+ BX.util.htmlspecialchars(name)
							}),
							BX.create('input', {
								props: {
									type: 'hidden',
									name: 'RESTRICTION[PRODUCTS][]',
									value: id
								}
							})
						]
					}),
					BX.create('td', {
						props:{
							align: 'right'
						},
						children: [
							BX.create('text', {html: '&nbsp;'}),
							BX.create('a', {
								props: {
									href: 'javascript:void(0);',
									className: 'adm-s-bus-morelinkqhsw'
								},
								text: BX.message('SALE_CONCRETE_PRODUCT_INP_DELETE'),
								events: {
									click: function() {
										self.deleteRestrictionByConcreteProduct(nodeId, id);
									}
								}
							})
						]
					})
				]
			});

			BX(nodeId + '-content').appendChild(product);
		},

		deleteRestrictionByConcreteProduct: function (nodeId, id)
		{
			var node = BX(nodeId + '-' + id);

			if (node)
			{
				node.parentNode.removeChild(node);
			}
		},
	};

})(window);
