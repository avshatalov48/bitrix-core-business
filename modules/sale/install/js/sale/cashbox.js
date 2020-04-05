/**
 * Class BX.Sale.Cashbox
 */
(function(window) {

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.Cashbox)
		return;

	BX.Sale.Cashbox = {

		ajaxUrl: "sale_cashbox_ajax.php",

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
				cashboxId: params.cashboxId,
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
						BX.Sale.Cashbox.showRestrictionParamsDialog(data['HTML'], params);
						window["cashboxGetRestrictionHtmlScriptsLoadingStarted"] = false;

						//process scripts
						var scrs = function (loadScripts)
						{
							if (!loadScripts)
								BX.removeCustomEvent('cashboxGetRestrictionHtmlScriptsReady', scrs);

							for (var i in data['SCRIPT'])
							{
								BX.evalGlobal(data['SCRIPT'][i]['JS']);
								delete(data['SCRIPT'][i]);

								//It can be nesessary  at first to load some JS for restriction form
								if (loadScripts && window["cashboxGetRestrictionHtmlScriptsLoadingStarted"])
									return;
							}
						};

						BX.addCustomEvent('cashboxGetRestrictionHtmlScriptsReady', scrs);
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
			var width = 460,
				dialog = new BX.CDialog({
					'content': '<form id="sale-cashbox-restriction-edit-form">' +
					content +
					'</form>',
					'title': BX.message("SALE_RDL_RESTRICTION") + ": " + rstrParams.title,
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

						var form = BX("sale-cashbox-restriction-edit-form"),
							prepared = BX.ajax.prepareForm(form),
							values = !!prepared && prepared.data ? prepared.data : {};

						BX.Sale.Cashbox.saveRestriction(rstrParams, values);
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
					cashboxId: rstrParams.cashboxId,
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

					if (result && !result.ERROR)
					{
						if (result.HTML)
							BX.Sale.Cashbox.insertAjaxRestrictionHtml(result.HTML);
					}
					else
					{
						alert(result.ERROR);
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
				}
			});
		},

		deleteRestriction: function (restrictionId, cashboxId)
		{
			if (!restrictionId)
				return;

			ShowWaitWindow();

			var postData = {
				action: "delete_restriction",
				restrictionId: restrictionId,
				cashboxId: cashboxId,
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
							BX.Sale.Cashbox.insertAjaxRestrictionHtml(result.HTML);

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
				container = BX("sale-cashbox-restriction-container");

			if (!container)
				return;

			BX.loadCSS(data['STYLE']);

			container.innerHTML = data['HTML'];

			for (var i in data['SCRIPT'])
				BX.evalGlobal(data['SCRIPT'][i]['JS']);
		},

		generateConnectionLink: function()
		{

			var data = {
				'action': 'generate_link',
				'sessid': BX.bitrix_sessid()
			};

			BX.showWait();
			BX.ajax({
				data: data,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				onsuccess: BX.delegate(function(result)
				{
						BX.closeWait();
						if(result)
						{
							if(!result.ERROR)
							{
								text =
									'<div style="margin-bottom: 50px;">' +
										'<ul class="adm-cashbox-list2 adm-cashbox-inner">' +
											'<li style="margin-bottom: 20px;">' + BX.message('SALE_CASHBOX_WINDOW_STEP_1') + '<br> <b id="generated-link">' + result.LINK + '</b></li>' +
											'<li>' + BX.message('SALE_CASHBOX_WINDOW_STEP_2') + '</li>' +
										'</ul>' +
									'</div>';
								var dlg = new BX.CAdminDialog({
									'content': text,
									'title': BX.message('SALE_CASHBOX_WINDOW_TITLE'),
									'resizable': false,
									'draggable': false,
									'height': '145',
									'width': '516',
									'buttons': [
										{
											title: top.BX.message('SALE_CASHBOX_COPY'),
											id: 'copyCheckBtn',
											name: 'copybtn',
											className: top.BX.browser.IsIE() && top.BX.browser.IsDoctype() && !top.BX.browser.IsIE10() ? '' : 'adm-btn-save'
										},
										BX.CAdminDialog.btnCancel
									]
								});
								dlg.Show();
								var copy = BX('copyCheckBtn');
								if (copy)
									BX.clipboard.bindCopyClick(copy, {text : result.LINK});
							}
							else
							{
								BX.debug(result.ERROR);
							}
						}
					}, this
				),
				onfailure: function() {BX.debug('onfailure: generateConnectionLink');}
			});
		},

		connectToKKM: function (event)
		{
			BX.ajax({
				data: {
					'action': 'generate_link',
					'sessid': BX.bitrix_sessid()
				},
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				onsuccess: BX.delegate(function(result)
				{
					BX.closeWait();
					if(result)
					{
						if(!result.ERROR)
						{
							var parent = event.parentNode;
							BX.hide(parent);
							var container = BX('container-instruction');
							container.style.display = 'block';
							BX('cashbox-url').innerHTML = result.LINK;
						}
						else
						{
							BX.debug(result.ERROR);
						}
					}
				}, this
				),
				onfailure: function() {BX.debug('onfailure: generateConnectionLink');}
			});
		},

		reloadSettings: function()
		{
			var kkmId = BX('KKM_ID');
			kkmId = (kkmId) ? kkmId.value : '';
			
			BX.ajax({
				data: {
					'action': 'reload_settings',
					'kkmId': kkmId,
					'handler': BX('HANDLER').value || '',
					'sessid': BX.bitrix_sessid()
				},
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				onsuccess: BX.delegate(function(result)
					{
						BX.closeWait();
						if (result && result.hasOwnProperty('HTML'))
							BX('sale-cashbox-settings-container').innerHTML = result.HTML;

						if (result && result.hasOwnProperty('MODEL_HTML'))
						{
							BX('sale-cashbox-models-container').innerHTML = result.MODEL_HTML;
						}
						else
						{
							BX('sale-cashbox-models-container').innerHTML = '';
						}

						if (result.hasOwnProperty('GENERAL_REQUIRED_FIELDS'))
						{
							var generalBlock = BX('edit1_edit_table');
							var tr = BX.findChildren(generalBlock, {tag : 'tr'}, true);
							for (var i in tr)
							{
								if (tr.hasOwnProperty(i))
								{
									var span = BX.findChild(tr[i], {tag : 'span'} ,true);
									if (span)
									{
										var className = span.className;
										if (className && className.indexOf('adm-required-field') > -1)
											span.className = '';
									}
									
									var id = tr[i].getAttribute('id');
									if (id)
										id = id.slice(3);

									if (result.GENERAL_REQUIRED_FIELDS.hasOwnProperty(id))
									{
										if (span)
										{
											span.className = 'adm-required-field';
										}
										else
										{
											tr[i].firstElementChild.innerHTML = '<span class="adm-required-field">'+tr[i].firstElementChild .innerHTML+'</span>';
										}
									}
								}
							}
						}
					}, this
				),
				onfailure: function() {BX.debug('onfailure: reloadSettings');}
			});
		},
		
		reloadOfdSettings: function()
		{
			BX.ajax({
				data: {
					'action': 'reload_ofd_settings',
					'handler': BX('OFD').value || '',
					'sessid': BX.bitrix_sessid()
				},
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				onsuccess: BX.delegate(function(result)
					{
						BX.closeWait();
						if (result && result.hasOwnProperty('HTML'))
							BX('sale-cashbox-ofd-settings-container').innerHTML = result.HTML;
					}, this
				),
				onfailure: function() {BX.debug('onfailure: reloadOfdSettings');}
			});
		},


		showCreateCheckWindow: function()
		{
			var form = BX.create('form', { props : {name : 'check_form_add', id : 'check_form_add'}});
			var div = BX.create('div', {attrs : {className : 'adm-info-message'},  text : BX.message('CASHBOX_ADD_CHECK_TITLE')});
			form.appendChild(div);

			var table = BX.create(
				'table', {
					props : {id : 'check_add_control_table'},
					children : [
						BX.create('tr', {children: [
							BX.create('td', {text : BX.message('CASHBOX_ADD_CHECK_INPUT_ORDER')+':'}),
							BX.create('td', {children : [
								BX.create('input', {
									props : {
										name : 'ORDER_ID',
										onchange : BX.delegate(function () { this.onChangeInputOrderId(); }, this)
									},
									attrs : {
										className : 'sale-discount-bus-select',
										value : '',
										type : 'text'
									}
								})
							]})
						]}),
						BX.create('tr', {props : {id : 'check_entities_body'}}),
						BX.create('tbody', {props : {id : 'check_info_block'}})
					]
				}
			);
			form.appendChild(table);

			var dlg = new BX.CAdminDialog({
				'title': BX.message('CASHBOX_CREATE_WINDOW_TITLE'),
				'content': form,
				'resizable': false,
				'draggable': true,
				'height': '300',
				'width': '516',
				'buttons': [
					{
						title: BX.message('JS_CORE_WINDOW_SAVE'),
						className: top.BX.browser.IsIE() && top.BX.browser.IsDoctype() && !top.BX.browser.IsIE10() ? '' : 'adm-btn-save',
						action : BX.delegate(
							function()
							{
								var form = BX('check_form_add');
								var sendData = {
									sessid : BX.bitrix_sessid(),
									formData : BX.ajax.prepareForm(form),
									action: 'add_check'
								};
								BX.ajax(
									{
										method: 'post',
										dataType: 'json',
										url: '/bitrix/admin/sale_cashbox_ajax.php',
										data: sendData,
										onsuccess: function (result)
										{
											if (result.ERROR && result.ERROR.length > 0)
											{
												alert(result.ERROR);
											}
											else
											{
												dlg.Close();
												location.reload();
											}
										},
										onfailure: function() {BX.debug('Select params error');}
									}
								);
			
							},
							this
						)
					},
					{
						title: top.BX.message('JS_CORE_WINDOW_CANCEL'),
						id: 'cancelCheckBtn',
						name: 'cancel'
					}
				]
			});

			BX.bind(BX("cancelCheckBtn"), 'click', BX.delegate(
				function()
				{
					dlg.Close();
					dlg.DIV.parentNode.removeChild(dlg.DIV);
				}
			),this );

			dlg.Show();
		},


		onChangeInputOrderId : function ()
		{
			var form = BX('check_form_add');
			var sendData = {
				sessid : BX.bitrix_sessid(),
				formData: BX.ajax.prepareForm(form),
				action: 'get_order_entities'
			};

			BX.ajax({
				method: 'post',
				dataType: 'json',
				url: '/bitrix/admin/sale_cashbox_ajax.php',
				data: sendData,
				onsuccess: BX.delegate(function (result)
				{
					if (result.ERROR && result.ERROR.length > 0)
					{
						alert(result.ERROR);
					}
					else
					{
						var group, option, i;

						var select = BX.create('select', {
							props : {
								id : 'ENTITY_CODE',
								name : 'ENTITY_CODE',
								onchange : BX.delegate(function() {
									var form = BX('check_form_add');
									
									var sendData = {
										sessid : BX.bitrix_sessid(),
										formData: BX.ajax.prepareForm(form),
										action: 'get_data_for_check'
									};

									BX.ajax({
										method: 'post',
										dataType: 'json',
										url: '/bitrix/admin/sale_cashbox_ajax.php',
										data: sendData,
										onsuccess: BX.delegate(function (result)
										{
											if (result.ERROR && result.ERROR.length > 0)
											{
												alert(result.ERROR);
											}
											else
											{
												var tbody = BX('check_info_block');
												tbody.innerHTML = '';

												var elements = this.constructCheckInfoBlock(result);
												for (i in elements)
												{
													if (elements.hasOwnProperty(i))
														tbody.appendChild(elements[i]);
												}
											}
										}, this),
										onfailure: function() {BX.debug('Select params error');}
									});
								},this)
							}
						});
						if (result.PAYMENTS)
						{
							group = BX.create('optgroup', {
								attrs : {
									label : BX.message('CASHBOX_ADD_CHECK_OPTGROUP_PAYMENTS')
								}
							});
							for (i in result.PAYMENTS)
							{
								if (result.PAYMENTS.hasOwnProperty(i))
								{
									option = BX.create('option', {
										text : BX.message('CASHBOX_ADD_CHECK_PAYMENT')+' '+result.PAYMENTS[i].ID,
										attrs : { value : result.PAYMENTS[i].CODE }
									});

									group.appendChild(option);
								}
							}

							select.appendChild(group);
						}

						if (result.SHIPMENTS)
						{
							group = BX.create('optgroup', {
								attrs : {
									label : BX.message('CASHBOX_ADD_CHECK_OPTGROUP_SHIPMENTS')
								}
							});
							for (i in result.SHIPMENTS)
							{
								if (result.SHIPMENTS.hasOwnProperty(i))
								{
									option = BX.create('option', {
										text : BX.message('CASHBOX_ADD_CHECK_SHIPMENT')+' '+result.SHIPMENTS[i].ID,
										attrs : { value : result.SHIPMENTS[i].CODE }
									});
									group.appendChild(option);
								}
							}
							select.appendChild(group);
						}
						
						var tr = BX('check_info_block');
						tr.innerHTML = '';
						
						tr = BX('check_entities_body');
						tr.innerHTML = '';

						var td = BX.create('td', { text: BX.message('CASHBOX_ADD_CHECK_ENTITIES')+': '});
						tr.appendChild(td);
						td = BX.create('td', {children : [select]});
						tr.appendChild(td);
						
						select.onchange();
					}
				},this),
				onfailure: function() {BX.debug('Select params error');}
			});
		},
		
		constructCheckInfoBlock : function (data)
		{
			var i, element;
			
			var result = [];
			
			select = BX.create('select', {
				attrs : {id : 'CHECK_TYPE'},
				props : {
					name : 'CHECK_TYPE',
					onchange : BX.delegate(function ()
					{
						var td = BX('related_entity_list');
						td.innerHTML = '';
						BX.hide(td.parentNode);

						var entityCode = BX('ENTITY_CODE').value;
						var sendData = {
							sessid : BX.bitrix_sessid(),
							entityCode: entityCode,
							checkType: BX('CHECK_TYPE').value,
							action: 'get_related_entities'
						};

						BX.ajax({
							method: 'post',
							dataType: 'json',
							url: '/bitrix/admin/sale_cashbox_ajax.php',
							data: sendData,
							onsuccess: BX.delegate(function (result)
							{
								if (result.ERROR && result.ERROR.length > 0)
								{
									alert(result.ERROR);
								}
								else
								{
									var td = BX('related_entity_list');

									if (result.PAYMENTS)
									{
										for (i in result.PAYMENTS)
										{
											if (!result.PAYMENTS.hasOwnProperty(i))
												continue;
						
											element = this.createSinglePayment(result.PAYMENTS[i]);
											td.appendChild(element);
										}
									}

									if (result.SHIPMENTS)
									{
										for (i in result.SHIPMENTS)
										{
											if (!result.SHIPMENTS.hasOwnProperty(i))
												continue;
						
											element = this.createSingleShipment(result.SHIPMENTS[i], result.FFD_105_ENABLED);
											td.appendChild(element);
										}
									}
									
									var tdTitle = td.parentNode;
									if (!result.PAYMENTS && !result.SHIPMENTS)
									{
										BX.hide(tdTitle);
									}
									else
									{
										BX.show(tdTitle, 'table-row');
									}
								}
							}, this),
							onfailure: function() {BX.debug('Select params error');}
						});
					}, this)
				}
			});
			for (i in data.CHECK_TYPES)
			{
				if (!data.CHECK_TYPES.hasOwnProperty(i))
					continue;
				
				option = BX.create('option', {
					text : data.CHECK_TYPES[i].NAME,
					attrs : {
						value : data.CHECK_TYPES[i].ID
					}
				});

				select.appendChild(option);
			}
			
			var tr = BX.create('tr', {
				children : [
					BX.create('td', {text : BX.message('CASHBOX_ADD_CHECK_TYPE_CHECKS')+': '}),
					BX.create('td', {children : [select]})
				]
			});

			result.push(tr);

			var td = BX.create('td', {attrs : {id : 'related_entity_list'}});
			if (data.PAYMENTS)
			{
				for (i in data.PAYMENTS)
				{
					if (!data.PAYMENTS.hasOwnProperty(i))
						continue;

					element = this.createSinglePayment(data.PAYMENTS[i]);
					td.appendChild(element);
				}
			}
			if (data.SHIPMENTS)
			{
				for (i in data.SHIPMENTS)
				{
					if (!data.SHIPMENTS.hasOwnProperty(i))
						continue;

					element = this.createSingleShipment(data.SHIPMENTS[i], data.FFD_105_ENABLED);
					td.appendChild(element);
				}
			}
			
			tr = BX.create('tr', {
				children : [
					BX.create('td', {text : BX.message('CASHBOX_ADD_CHECK_ADDITIONAL_ENTITIES')+': '}),
					td
				]
			});

			result.push(tr);
			
			return result;
		},

		createSinglePayment : function (payment)
		{
			var checkbox = BX.create('input', {
				props : {
					id : 'payment_'+payment.ID,
					name : 'PAYMENTS['+payment.ID+'][ID]',
					onclick : function ()
					{
						var label = this.nextElementSibling;
						if (this.checked)
							label.style.color = '';
						else
							label.style.color = '#D2D1D1';
						
						var select = label.nextElementSibling;
						if (select)
							select.disabled = !this.checked;
					}
				},
				attrs : {
					type : 'checkbox',
					value : payment.ID
				}
			});

			var div = BX.create('div', {children : [
				checkbox,
				BX.create('label', {
					text : BX.message('CASHBOX_ADD_CHECK_PAYMENT')+' '+payment.ID+': '+payment.NAME+' ',
					attrs : {
						style : 'color: #D2D1D1',
						for : 'payment_'+payment.ID
					}
				})
			]});

			if (payment.PAYMENT_TYPES)
			{
				var select = BX.create('select', {
					props: {
						name: 'PAYMENTS[' + payment.ID + '][TYPE]',
						id: 'payment_' + payment.ID
					},
					attrs: {disabled: 'disabled'}
				});

				for (j in payment.PAYMENT_TYPES)
				{
					if (!payment.PAYMENT_TYPES.hasOwnProperty(j))
						continue;
					
					var option = BX.create('option', {
						text: payment.PAYMENT_TYPES[j].NAME,
						attrs: {
							value: payment.PAYMENT_TYPES[j].CODE
						}
					});
					select.appendChild(option);
				}

				div.appendChild(select);
			}
			
			return div;
		},
		
		createSingleShipment: function(shipment, isFfd105Enable)
		{
			var checkbox = BX.create('input', {
				props : {
					id : 'shipment_'+shipment.ID,
					name : 'SHIPMENTS['+shipment.ID+'][ID]',
					onclick : function ()
					{
						var label = this.nextElementSibling;
						if (this.checked)
							label.style.color = '';
						else
							label.style.color = '#D2D1D1';
			
						if (!isFfd105Enable)
						{
							var td = this.parentNode.parentNode;
							var inputs = BX.findChildren(td, {tag: 'input'}, true);
							for (var i in inputs)
							{
								if (inputs.hasOwnProperty(i))
								{
									if (inputs[i].id === this.id)
										continue;
									
									inputs[i].disabled = this.checked;
								}
							}
						}
					}
				},
				attrs : {
					type : 'checkbox',
					value : shipment.ID
				}
			});

			var div = BX.create('div', {children : [
				checkbox,
				BX.create('label', {
					text : BX.message('CASHBOX_ADD_CHECK_SHIPMENT')+' '+shipment.ID+': '+shipment.NAME,
					attrs : {
						style : 'color: #D2D1D1',
						for : 'shipment_'+shipment.ID
					}
				})
			]});
			
			return div;
		}
	}
})(window);
