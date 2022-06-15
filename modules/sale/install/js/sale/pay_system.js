/**
 * Class BX.Sale.PaySystem
 */
(function(window) {

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.PaySystem) return;

	BX.Sale.PaySystem = {

		ajaxUrl: "sale_pay_system_ajax.php",

		setLHEClass: function (lheDivId)
		{
			BX.ready(
				function ()
				{
					var lheDivObj = BX(lheDivId);

					if (lheDivObj)
						BX.addClass(lheDivObj, 'adm-sale-lhe-frame-dlvrs-dscr');
				});
		},

		getRestrictionParamsHtml: function (params)
		{
			if (!params.class)
				return;

			params.params = params.params || {};
			params.restrictionId = params.restrictionId || 0;
			params.sort = params.sort || 100;

			ShowWaitWindow();

			var postData = {
				action: "get_restriction_params_html",
				className: params.class,
				params: params.params,
				paySystemId: params.paySystemId,
				sort: params.sort,
				lang: params.lang,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result && result.RESTRICTION_HTML && !result.ERROR)
					{
						var data = BX.processHTML(result.RESTRICTION_HTML);
						BX.Sale.PaySystem.showRestrictionParamsDialog(data['HTML'], params);
						window["paySystemGetRestrictionHtmlScriptsLoadingStarted"] = false;

						//process scripts
						var scrs = function (loadScripts)
						{
							if (!loadScripts)
								BX.removeCustomEvent('paySystemGetRestrictionHtmlScriptsReady', scrs);

							for (var i in data['SCRIPT'])
							{
								BX.evalGlobal(data['SCRIPT'][i]['JS']);
								delete(data['SCRIPT'][i]);

								//It can be nesessary  at first to load some JS for restriction form
								if (loadScripts && window["paySystemGetRestrictionHtmlScriptsLoadingStarted"])
									return;
							}
						};

						BX.addCustomEvent('paySystemGetRestrictionHtmlScriptsReady', scrs);
						scrs(true);
						BX.loadCSS(data['STYLE']);
					}
					else if (result && result.ERROR)
					{
						BX.debug("Error receiving restriction params html: " + result.ERROR);
					}
					else
					{
						BX.debug("Error receiving restriction params html!");
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
					BX.debug("Error adding restriction!");
				}
			});
		},

		showRestrictionParamsDialog: function (content, rstrParams)
		{
			var width = (rstrParams.class == '\\Bitrix\\Sale\\PaySystem\\Restrictions\\ByLocation' ? 1030 : 420),
				dialog = new BX.CDialog({
					'content': '<form id="sale-paysystem-restriction-edit-form">' +
					content +
					'</form>',
					'title': BX.message("SALE_RDL_RESTRICTION") + " " + rstrParams.title,
					'width': width,
					'height': 500,
					'resizable': true
				});

			dialog.ClearButtons();
			dialog.SetButtons([
				{
					'title': BX.message("SALE_RDL_SAVE"),
					'action': function ()
					{

						var form = BX("sale-paysystem-restriction-edit-form"),
							prepared = BX.ajax.prepareForm(form),
							values = !!prepared && prepared.data ? prepared.data : {};

						BX.Sale.PaySystem.saveRestriction(rstrParams, values);
						this.parentWindow.Close();
					}
				},
				BX.CDialog.prototype.btnCancel
			]);

			BX.addCustomEvent(dialog, 'onWindowClose', function (dialog)
			{
				dialog.DIV.parentNode.removeChild(dialog.DIV);
			});

			dialog.Show();
			dialog.adjustSizeEx();
		},

		saveRestriction: function (rstrParams, values)
		{
			ShowWaitWindow();

			var params = values.RESTRICTION || {},
				postData = {
					action: "save_restriction",
					params: params,
					sort: values.SORT,
					className: rstrParams.class,
					paySystemId: rstrParams.paySystemId,
					restrictionId: rstrParams.restrictionId,
					sessid: BX.bitrix_sessid(),
					lang: BX.message('LANGUAGE_ID')
				};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result)
					{
						if (result.HTML)
						{
							BX.Sale.PaySystem.insertAjaxRestrictionHtml(result.HTML);
						}
						
						if (result.ERROR)
						{
							alert(result.ERROR);
						}
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
				}
			});
		},

		deleteRestriction: function (restrictionId, paySystemId)
		{
			if (!restrictionId)
				return;

			ShowWaitWindow();

			var postData = {
				action: "delete_restriction",
				restrictionId: restrictionId,
				paySystemId: paySystemId,
				sessid: BX.bitrix_sessid(),
				lang: BX.message('LANGUAGE_ID')
			};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result && !result.ERROR)
					{
						if (result.HTML)
							BX.Sale.PaySystem.insertAjaxRestrictionHtml(result.HTML);

						if (result.ERROR)
							BX.debug("Error deleting restriction: " + result.ERROR);
					}
					else
					{
						BX.debug("Error deleting restriction!");
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
					BX.debug("Error refreshing restriction!");
				}
			});
		},

		insertAjaxRestrictionHtml: function (html)
		{
			var data = BX.processHTML(html),
				container = BX("sale-paysystem-restriction-container");

			if (!container)
				return;

			BX.loadCSS(data['STYLE']);

			container.innerHTML = data['HTML'];

			for (var i in data['SCRIPT'])
				BX.evalGlobal(data['SCRIPT'][i]['JS']);
		},

		getHandlerOptions: function (link)
		{
			var handlerType = link.value, psMode;

			if (handlerType === '')
				return;

			if (BX('PS_MODE'))
			{
				psMode = BX('PS_MODE').value;
			}

			ShowWaitWindow();
			var postData = {
				action: "getHandlerDescription",
				handler: handlerType,
				paySystemId: BX('ID').value,
				sessid: BX.bitrix_sessid(),
				lang: BX.message('LANGUAGE_ID')
			};

			if (psMode !== undefined)
			{
				postData.PS_MODE = psMode;
			}

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result && !result.ERROR)
					{
						if (result.BUS_VAL)
						{
							var data = BX.processHTML(result.BUS_VAL);
							var busValSettings = BX('paysystem-business-value-settings');

							if (!busValSettings)
								return;

							BX.loadCSS(data['STYLE']);
							busValSettings.innerHTML = data['HTML'];

							for (var i in data['SCRIPT'])
								BX.evalGlobal(data['SCRIPT'][i]['JS']);
						}

						var tariffSettings = BX('pay_system_tariff');
						if (result.TARIF)
						{
							data = BX.processHTML(result.TARIF);
							if (!tariffSettings)
								return;

							BX.loadCSS(data['STYLE']);
							tariffSettings.innerHTML = data['HTML'];

							for (i in data['SCRIPT'])
								BX.evalGlobal(data['SCRIPT'][i]['JS']);

							BX.Sale.PaySystem.initTariffLoad();
						}
						else
						{
							tariffSettings.innerHTML = '';
						}

						var psMode = BX('pay_system_ps_mode');
						var isOrderHandler = BX('ACTION_FILE').value === 'orderdocument';
						if (result.PAYMENT_MODE || isOrderHandler)
						{
							var tr = BX.create('tr', {props : {'width': '40%'}});
							tr.setAttribute('valign', 'top');

							var tdTitle = BX.create('td', {props : {'width': '40%'}});
							BX.addClass(tdTitle, 'adm-detail-content-cell-l');
							tdTitle.innerHTML = result.PAYMENT_MODE_TITLE+':';

							var tdContent = BX.create('td', {props : {'width': '60%'}});
							BX.addClass(tdContent, 'adm-detail-content-cell-r');
							if (result.PAYMENT_MODE)
							{
								tdContent.innerHTML = result.PAYMENT_MODE;
							}

							if (isOrderHandler)
							{
								var span = BX.create(
									'span',
									{
										text: BX.message('SALE_TEMPLATE_DOCUMENT_ADD'),
										attrs: {
											class: 'bx-button-add-template',
											style: 'margin-left: 5px'
										}
									}
								);
								BX.bind(span, 'click', function () {
									BX.SidePanel.Instance.open(result.ORDER_DOC_ADD_LINK, {width: 930, events: {onCloseComplete: function() {BX.Sale.PaySystem.getHandlerOptions(BX("ACTION_FILE"));}}});
								});

								tdContent.appendChild(span);
							}

							tr.appendChild(tdTitle);
							tr.appendChild(tdContent);

							psMode.innerHTML = '';
							psMode.appendChild(tr);
						}
						else
						{
							psMode.innerHTML = '';
						}

						var psDesc = BX('pay_system_ps_description');
						if (psDesc)
							psDesc.innerHTML = '';

						if (result.DESCRIPTION)
						{
							var tBody = BX.create('tr', {
								children :[
									BX.create('td', {props : {'width': '40%', className : 'adm-detail-content-cell-l'}}),
									BX.create('td', {props : {'width': '60%', className : 'adm-detail-content-cell-r'}, html : result.DESCRIPTION})
								]
							});
							psDesc.appendChild(tBody);
						}

						if (result.NAME !== undefined)
							BX('NAME').value = result.NAME;

						if (result.PSA_NAME !== undefined)
							BX('PSA_NAME').value = result.PSA_NAME;

						if (result.SORT)
							BX('SORT').value = result.SORT;

						var id = BX('ID').value;
						var logo = BX('LOGOTIP');
						var parent = BX.findParent(logo, {tag : 'div'});
						var img = BX.findChild(parent.parentNode, {tag : 'img'});

						if (result.LOGOTIP)
						{
							if (result.LOGOTIP.NAME)
								logo.previousElementSibling.innerHTML = result.LOGOTIP.NAME;

							if (img)
							{
								if (result.LOGOTIP.PATH)
									img.src = result.LOGOTIP.PATH;
							}
							else
							{
								img = BX.create('img', {
									attrs: {
										'src': result.LOGOTIP.PATH,
									}
								});
								img.style.maxHeight = "55px";
								BX.insertAfter(img, parent);
								BX.insertAfter(BX.create('br'), parent);
							}
						}
						else if (id <= 0)
						{
							if (img)
								BX.remove(img);

							logo.previousElementSibling.innerHTML = BX.message('JSADM_FILE');
						}

						this.updateVerificationBlock(result.DOMAIN_VERIFICATION);
					}
					else
					{
						BX.debug(result.ERROR);
					}
				}.bind(this),

				onfailure: function ()
				{
					CloseWaitWindow();

					var psDesc = BX('pay_system_ps_description');
					if (psDesc)
						psDesc.innerHTML = '';

					var psMode = BX('pay_system_ps_mode');
					if (psMode)
						psMode.innerHTML = '';

					BX.debug("Error");
				}
			});
		},

		toggleNextSiblings : function(obj, siblNumber, hide)
		{
			if (!obj.nextElementSibling)
				return false;

			var nextObj = obj.nextElementSibling;

			for (var i=0; i < siblNumber; i++)
			{
				if(nextObj.style.display == 'none' && !hide)
					nextObj.style.display = '';
				else
					nextObj.style.display = 'none';

				if(nextObj.nextElementSibling)
					nextObj = nextObj.nextElementSibling;
				else
					break;
			}

			return true;
		},

		deleteObjectAndNextSiblings : function (obj, siblNumber, parentsCount)
		{
			if (!obj)
				return false;

			var i;
			var firstObj = obj;

			if (parentsCount && parentsCount > 0)
			{
				for (i = 0; i < parentsCount; i++)
				{
					if(firstObj.parentNode)
						firstObj = firstObj.parentNode;
					else
						return false;
				}
			}

			var newNextObj = false;
			var nextObj = firstObj;

			for (i = 0; i <= siblNumber; i++)
			{
				if (nextObj.nextElementSibling)
					newNextObj = nextObj.nextElementSibling;

				nextObj.parentNode.removeChild(nextObj);

				if (newNextObj)
					nextObj = newNextObj;
				else
					break;
			}

			return true;
		},

		initTariffLoad : function ()
		{
			var i;
			var tabControlLayout = BX("tabControl_layout");

			if (tabControlLayout)
			{
				var rowsToHide = window.parent.BX.findChildren(tabControlLayout, {'tag': 'tr', 'class': 'ps-admin-hide'}, true);

				for (i in rowsToHide)
					BX.Sale.PaySystem.toggleNextSiblings(rowsToHide[i], 4, true);
			}
			window.parent.BX.onCustomEvent('onAdminTabsChange');
		},

		updateVerificationBlock: function(verificationData)
		{
			var validationDomainNode = BX('pay_system_validation_domain');
			validationDomainNode.style.display = (verificationData.NEED_VERIFICATION) ? "" : "none";

			if (verificationData.NEED_VERIFICATION)
			{
				var domainVerificationLinkNode = BX('domain-verification-link');
				domainVerificationLinkNode.setAttribute('onclick', 'BX.Sale.PaySystem.openVerificationForm(\'' + verificationData.FORM_LINK + '\')');
			}
		},

		openVerificationForm: function(url)
		{
			BX.SidePanel.Instance.open(url, {
				width: 750
			});
		},

		addRestrictionProductSection: function(id, name, nodeId)
		{
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
					id: nodeId+ '-' + id,
					className: 'adm-s-product-category-restriction-delcat'
				},
				children: [
					BX.create('td', {
						children: [
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
						children: [
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
						props: {
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
	}
})(window);
