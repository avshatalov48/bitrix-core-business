BX.namespace("BX.Sale.Admin.OrderPayment");

BX.Sale.Admin.OrderPayment = function(params)
{
	this.clWindow = null;
	this.pdWindow = null;
	this.rtWindow = null;
	this.psToReturn = params.psToReturn;

	this.index = params.index;
	this.viewForm = !!params.viewForm;
	this.isAvailableChangeStatus = params.isAvailableChangeStatus;

	if (this.isAvailableChangeStatus)
	{
		if (!!params.isPaid)
			this.initPaidPopup();
		else
			this.initNotPaidPopup();
	}

	this.initToggle();
	this.initReloadImg();
	this.initDeletePayment();
	this.initPaymentSum();

	var updater = [];

	updater["PAY_SYSTEM_LIST"] = {
		callback: this.updatePaySystemList,
		context: this
	};

	updater["PRICE_COD"] = {
		callback: this.updatePriceCod,
		context: this
	};

	updater["PAYSYSTEM_ERROR"] = {
		callback: BX.Sale.Admin.OrderEditPage.showDialog,
		context: this
	};

	updater["PAYMENT_COMPANY_ID"] = {
		callback: this.updateCompany,
		context: this
	};


	BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters(updater);

	if (this.viewForm)
	{
		var psUpdateLink = BX('ps_update_'+this.index);

		var orderId = BX('ID');
		if (orderId)
			orderId = orderId.value;

		var paymentId = BX('PAYMENT_ID_'+this.index);
		if (paymentId)
			paymentId = paymentId.value;

		if (psUpdateLink)
		{
			BX.bind(psUpdateLink, 'click', function ()
			{
				var request = {
					'action' : 'updatePaySystemInfo',
					'orderId' : orderId,
					'paymentId' : paymentId,
					'callback' : function (result)
					{
						if (result.ERROR && result.ERROR.length > 0)
						{
							alert(result.ERROR);
						}
						else
						{
							location.reload();
						}
					}
				};

				BX.Sale.Admin.OrderAjaxer.sendRequest(request);
			});
		}
	}
};

BX.Sale.Admin.OrderPayment.prototype.initPaymentSum = function()
{
	var sumField = BX('PAYMENT_SUM_'+this.index);
	if (sumField)
	{
		BX.bind(sumField, 'change', function ()
		{
			BX.Sale.Admin.OrderEditPage.autoPriceChange = false;

			if (BX.Sale.Admin.OrderEditPage.formId != 'order_payment_edit_info_form')
			{
				BX.Sale.Admin.OrderAjaxer.sendRequest(
					BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
				);
			}
		});
	}
};

BX.Sale.Admin.OrderPayment.prototype.updateCompany = function(companyList)
{
	var company = BX('PAYMENT_COMPANY_ID_'+this.index);
	if (company)
		company.innerHTML = companyList;
};

BX.Sale.Admin.OrderPayment.prototype.updatePaySystemList = function(paySystemList)
{
	var selectControl = BX('PAY_SYSTEM_ID_'+this.index);
	if (!selectControl)
		return;

	var selectedPaySystem = selectControl.options[selectControl.selectedIndex].value;
	selectControl.innerHTML = paySystemList;

	for (var i in selectControl.options)
	{
		if (selectControl.options.hasOwnProperty(i) && selectControl.options[i].value == selectedPaySystem)
		{
			selectControl.options[i].selected = true;
			break;
		}
	}

	this.reloadImg();
};

BX.Sale.Admin.OrderPayment.prototype.updatePriceCod = function(priceCod)
{
	var blockPriceCod = BX('PAYMENT_PRICE_COD_' + this.index);
	if (blockPriceCod)
	{
		blockPriceCod.value = priceCod;
		var parent = blockPriceCod.parentNode.parentNode;
		if (priceCod > 0)
			parent.style.display = 'table-row';
		else
			BX.hide(parent);
	}
};

BX.Sale.Admin.OrderPayment.prototype.sendAjaxChangeStatus = function(params)
{
	var formData = BX.ajax.prepareForm(BX(params.form_name));
	var orderId = BX('ID').value;
	var paymentId = BX('PAYMENT_ID_'+this.index).value;

	var request = {
		'method' : params.action,
		'action' : 'updatePaymentStatus',
		'orderId' : orderId,
		'paymentId' : paymentId,
		'data' : formData.data,
		'callback' : BX.proxy(function(result){
			params.callback(result, params)
		}, this)
	};

	if (params.strict && params.strict === true)
	{
		request['strict'] = params.strict;
	}

	BX.Sale.Admin.OrderAjaxer.sendRequest(request);
};

BX.Sale.Admin.OrderPayment.prototype.initDeletePayment = function()
{
	var obPaidDelete = BX('SECTION_'+this.index+'_DELETE');
	if (obPaidDelete)
		BX.bind(obPaidDelete, 'click', BX.proxy(this.deletePayment, this));
};

BX.Sale.Admin.OrderPayment.prototype.initToggle = function()
{
	var obj = BX('PS_INFO_'+this.index);
	BX.bind(obj, 'click', this.togglePsInfo);

	obj = BX('SECTION_'+this.index+'_TOGGLE');
	BX.bind(obj, 'click', BX.proxy(this.togglePayment, this));
};

BX.Sale.Admin.OrderPayment.prototype.initReloadImg = function()
{
	this.obj = BX('PAY_SYSTEM_ID_'+this.index);
	BX.bind(this.obj, 'change', BX.proxy(function ()
	{
		if (BX.Sale.Admin.OrderEditPage.formId != 'order_payment_edit_info_form')
		{
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
			);
		}
		else
		{
			var objOrderId = BX('order_id');
			var objPaymentId = BX('payment_id_'+this.index);
			var price = BX('PAYMENT_SUM_'+this.index);

			var request = {
				'action': 'updatePriceCod',
				'orderId': (objOrderId) ? objOrderId.value : 0,
				'paymentId': (objPaymentId) ? objPaymentId.value : 0,
				'paySystemId': this.obj.value,
				'price': (price) ? price.value : 0,
				'callback' : BX.proxy(function(result) {
					if (result.ERROR && result.ERROR.length > 0)
						BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
					else
						this.updatePriceCod(result.PRICE_COD);

				}, this)
			};
			BX.Sale.Admin.OrderAjaxer.sendRequest(request);
		}
		this.reloadImg();
	}, this));
};

BX.Sale.Admin.OrderPayment.prototype.deletePayment = function()
{
	if (confirm(BX.message['PAYMENT_CONFIRM_DELETE']))
	{
		var orderId = (BX('ID')) ? BX('ID').value : 0;
		var paymentId = (BX('PAYMENT_ID_'+this.index)) ? BX('PAYMENT_ID_'+this.index).value : 0;

		if ((orderId > 0) && (paymentId > 0))
		{
			var request = {
				'action': 'deletePayment',
				'orderId': BX('ID').value,
				'paymentId': BX('PAYMENT_ID_'+this.index).value,
				'callback' : BX.proxy(function(result) {
					if (result.ERROR && result.ERROR.length > 0)
					{
						BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
					}
					else
					{
						var div = BX.findParent(BX('payment_container_'+this.index), {'tag': div});
						BX.cleanNode(div);
					}

				}, this)
			};
			BX.Sale.Admin.OrderAjaxer.sendRequest(request);
		}
		else
		{
			var div = BX.findParent(BX('payment_container_'+this.index), {'tag': div});
			BX.cleanNode(div);
		}
	}
};

BX.Sale.Admin.OrderPayment.prototype.togglePsInfo = function()
{
	var sibling = BX.nextSibling(this);
	var bShow = sibling.style.display == 'none';
	if (bShow)
	{
		sibling.style.display = "table";
		BX.html(this, BX.message['PAYMENT_TOGGLE_UP']);
	}
	else
	{
		BX.hide(sibling);
		BX.html(this, BX.message['PAYMENT_TOGGLE_DOWN']);
	}
};

BX.Sale.Admin.OrderPayment.prototype.togglePayment = function()
{
	BX.toggle(BX('SECTION_'+this.index));
	BX.toggle(BX('SECTION_SHORT_'+this.index));

	var obShow = BX('SECTION_'+this.index).style.display == 'none';
	BX.html(BX('SECTION_'+this.index+'_TOGGLE'), BX.message['PAYMENT_TOGGLE_'+((obShow) ? 'DOWN' : 'UP')]);
};

BX.Sale.Admin.OrderPayment.prototype.reloadImg = function()
{
	var logotip = BX('LOGOTIP_'+this.index);
	logotip.style.background = 'url('+logoList[BX(this.obj).value]+')';
};

BX.Sale.Admin.OrderPayment.prototype.showReturnWindow = function(action)
{
	var table = BX.create('table', {props : {
		width : '100%',
		className : 'adm-detail-content-table edit-table'
		}
	});

	var tBody = BX.create('tbody');

	if (action == 'return')
	{
		var tr = BX.create('tr', {
			children : [
				BX.create('td', {
					props : { className : 'adm-detail-content-cell-l fwb'},
					text : BX.message['PAYMENT_OPERATION_TITLE']+':'
				})
			]
		});

		var td = BX.create('td', {props : { className : 'adm-detail-content-cell-r'}});
		var select = BX.create('select', {
			props : {
				id: 'PAY_OPERATION_ID_' + this.index,
				className: 'adm-bus-select',
				name: 'PAY_RETURN_OPERATION_ID_' + this.index
			}
		});

		for (var i in this.psToReturn)
		{
			if (!this.psToReturn.hasOwnProperty(i))
				continue;
			var option = BX.create('option', {
				props : {'value' : i},
				text : this.psToReturn[i]
			});

			select.appendChild(option);
		}

		td.appendChild(select);
		tr.appendChild(td);
		tBody.appendChild(tr);

		tr = BX.create('tr', {
			children : [
				BX.create('td', {
					props : { className : 'adm-detail-content-cell-l fwb'},
					text : BX.message['PAYMENT_RETURN_NUM_DOC']+':'
				})
			]
		});
		var input = BX.create('input', {
			props : {
				type : 'text',
				className : 'adm-bus-input',
				name : 'PAY_RETURN_NUM_'+this.index,
				maxlength : 20
			}
		});
		td = BX.create('td', {
			props : { className : 'adm-detail-content-cell-r'},
			children : [input],
			text : BX.message['PAYMENT_OPERATION_TITLE']+':'
		});
		tr.appendChild(td);
		tBody.appendChild(tr);

		tr = BX.create('tr', {
			children : [
				BX.create('td', {
					props : { className : 'adm-detail-content-cell-l fwb'},
					text : BX.message['PAYMENT_RETURN_DATE']+':'
				})
			]
		});

		var obj = this;
		td = BX.create('td', {
			props : {className : 'adm-detail-content-cell-r tal'},
			children : [
				BX.create('div', {
					props :{className : 'adm-input-wrap adm-calendar-second'},
					style : {display: 'inline-block'},
					children : [
						BX.create('input', {
							props : {
								type : 'text',
								className : 'adm-input adm-calendar-to',
								id : 'PAY_RETURN_DATE_'+this.index,
								name : 'PAY_RETURN_DATE_'+this.index,
								size : 15
							}
						}),
						BX.create('span', {
							props : {
								className : 'adm-calendar-icon',
								title : BX.message['PAYMENT_RETURN_DATE_ALT']
							},
							events : {
								click : function()
								{
									BX.calendar({node:this, field:'PAY_RETURN_DATE_'+obj.index, form: '', bTime: false, bHideTime: false});
								}
							}
						})
					]
				})
			]
		});
		tr.appendChild(td);
		tBody.appendChild(tr);
	}

	tr = BX.create('tr', {
		children : [
			BX.create('td', {
				props : {
					className : 'adm-detail-content-cell-l fwb'
				},
				text : BX.message['PAYMENT_RETURN_COMMENT']
			}),
			BX.create('td', {
				props : {className : 'adm-detail-content-cell-r'},
				children : [
					BX.create('textarea', {
						props : {
							className : 'adm-bus-textarea',
							id : 'PAY_RETURN_COMMENT_'+this.index,
							name : 'PAY_RETURN_COMMENT_'+this.index
						}
					})
				]
			})
		]
	});
	tBody.appendChild(tr);
	table.appendChild(tBody);

	if (!this.rtWindow && action == 'return')
	{
		this.rtWindow = new BX.CDialog({
			'content': BX.create('form', {
				props : {
					id : 'payment_return_form_'+this.index,
					name : 'payment_return_form_'+this.index
				},
				children : [table]
			}),
			'title': BX.message['PAYMENT_WINDOW_RETURN_TITLE'],
			'width': 650,
			'height': 250,
			'resizable': false,
			'buttons': [
				new BX.CWindowButton({
					'title' : BX.message['PAYMENT_WINDOW_RETURN_BUTTON_SAVE'],
					'action' : BX.proxy(function()
					{
						var params =
						{
							'index' : this.index,
							'action' : 'return',
							'form_name' : 'payment_return_form_'+this.index,
							'callback' : BX.proxy(function(result) {
								if (result.ERROR && result.ERROR.length > 0)
								{
									BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
								}
								else
								{
									this.changeNotPaidStatus('NO');
									this.initNotPaidPopup();
									BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT);

									if(typeof result.MARKERS != 'undefined')
									{
										var node = BX('sale-adm-order-problem-block');
										if(node)
											node.innerHTML = result.MARKERS;
									}

									if (result.WARNING && result.WARNING.length > 0)
									{
										BX.Sale.Admin.OrderEditPage.showDialog(result.WARNING);
									}
								}
							}, this)
						};
						this.sendAjaxChangeStatus(params);
						BX.WindowManager.Get().Close();
					}, this),
					'className' : 'adm-btn-save'
				}),
				BX.CDialog.btnCancel
			]
		});
	}
	else if(!this.clWindow && action == 'cancel')
	{
		this.clWindow = new BX.CDialog({
			'content':	BX.create('form', {
				props : {
					id : 'payment_cancel_form_'+this.index,
					name : 'payment_cancel_form_'+this.index
				},
				children : [table]
			}),
			'title':  BX.message['PAYMENT_WINDOW_CANCEL_TITLE'],
			'width': 650,
			'height': 100,
			'resizable': false,
			'buttons': [
				new BX.CWindowButton({
					'title' : BX.message['PAYMENT_WINDOW_RETURN_BUTTON_SAVE'],
					'action' : BX.proxy(function()
					{
						var params =
						{
							'index' : this.index,
							'action' : 'cancel',
							'form_name' : 'payment_cancel_form_'+this.index,
							'callback' : BX.proxy(function(result){
								if (result.ERROR && result.ERROR.length > 0)
								{
									BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
								}
								else
								{
									this.changeNotPaidStatus('NO');
									this.initNotPaidPopup();
									BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT);

									if(typeof result.MARKERS != 'undefined')
									{
										var node = BX('sale-adm-order-problem-block');
										if(node)
											node.innerHTML = result.MARKERS;
									}

									if (result.WARNING && result.WARNING.length > 0)
									{
										BX.Sale.Admin.OrderEditPage.showDialog(result.WARNING);
									}
								}
							}, this)
						};
						this.sendAjaxChangeStatus(params);
						BX.WindowManager.Get().Close();
					}, this),
					'className' : 'adm-btn-save'
				}),
				BX.CDialog.btnCancel
			]
		});
	}

	if (action == 'return')
		this.rtWindow.Show();
	else
		this.clWindow.Show();
};

BX.Sale.Admin.OrderPayment.prototype.showWindowPaidPayment = function()
{
	if (!this.pdWindow)
	{
		var statusOnPaid = BX('AUTO_CHANGE_STATUS_ON_PAID');
		var tr = '';
		if (statusOnPaid && statusOnPaid.value == 'N')
		{
			var orderStatus = BX('STATUS_ID');
			var options = BX.findChildren(orderStatus, {'tag' : 'option'});

			tr += '<tr><td class="adm-detail-content-cell-l fwb" width="40%">'+BX.message['PAYMENT_ORDER_STATUS']+':</td>';

			tr += '<td class="adm-detail-content-cell-r tal"><select name="ORDER_STATUS_ID_'+this.index+'" id="ORDER_STATUS_ID_'+this.index+'">';
			for (var i in options)
			{
				if (!options.hasOwnProperty(i))
					continue;
				tr += options[i].outerHTML;
			}
			tr += '</select></td></tr>';

			var thisIndex = this.index;

			BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters({
				STATUS_ID: {
					callback: function(statusId) { BX('ORDER_STATUS_ID_'+thisIndex).value = statusId; },
					context: this
				}
			});
		}

		var content = '<table width="100%" class="adm-detail-content-table edit-table">';
		content += '<tbody>';
		content += '<tr>';
		content += '<td class="adm-detail-content-cell-l fwb" width="40%">'+BX.message['PAYMENT_PAY_VOUCHER_DATE']+':</td>';
		content += '<td class="adm-detail-content-cell-r tal"><div class="adm-input-wrap adm-calendar-second" style="display: inline-block;">';
		content += '<input type="text" class="adm-input adm-calendar-to" id="PAY_VOUCHER_DATE_'+this.index+'" name="PAY_VOUCHER_DATE_'+this.index+'" size="15" value="">';
		content += '<span class="adm-calendar-icon" title="'+BX.message['PAYMENT_RETURN_DATE_ALT']+'" onclick="BX.calendar({node:this, field:\'PAY_VOUCHER_DATE_'+this.index+'\', form: \'\', bTime: false, bHideTime: false});"></span>';
		content += '</div></td></tr><tr>';
		content += '<td class="adm-detail-content-cell-l fwb" width="40%">'+BX.message['PAYMENT_PAY_VOUCHER_NUM']+':</td>';
		content += '<td class="adm-detail-content-cell-r tal"><input type="text" class="adm-bus-input" value="" name="PAY_VOUCHER_NUM_'+this.index+'" id="PAY_VOUCHER_NUM_'+this.index+'" maxlength="20">';
		content += '</td></tr>';
		content += tr;
		content += '</tbody></table>';

		this.pdWindow = new BX.CDialog({
			'content':'<form id="payment_voucher_form_'+this.index+'" name="order_new_table_settings_'+this.index+'">'+content+'</form>',
			'title': BX.message['PAYMENT_WINDOW_VOUCHER_TITLE'],
			'width': 650,
			'height': 200,
			'resizable': false,
			'buttons': [
				new BX.CWindowButton({
					'title' : BX.message['PAYMENT_WINDOW_RETURN_BUTTON_SAVE'],
					'action' : BX.proxy(function()
					{
						var params =
						{
							'index' : this.index,
							'action' : 'save',
							'form_name' : 'payment_voucher_form_'+this.index,
							'callback' : BX.proxy(function(result, params) {
								this.callbackUpdatePaymentStatus(result, 'YES', params);
							}, this)
						};
						this.sendAjaxChangeStatus(params);
						BX.WindowManager.Get().Close();
					}, this),
					'className' : 'adm-btn-save'
				}),
				BX.CDialog.btnCancel
			]
		});
	}
	this.pdWindow.Show();
};


BX.Sale.Admin.OrderPayment.prototype.callbackUpdatePaymentStatus = function(result, status, params)
{
	if (result.ERROR && result.ERROR.length > 0)
	{
		BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
	}
	else if (result.NEED_CONFIRM && result.NEED_CONFIRM === true)
	{
		var confirmTitle = false;
		var confirmMessage = false;

		if (result.WARNING && result.WARNING.length > 0)
		{
			confirmMessage = result.WARNING;
		}

		if (result.CONFIRM_TITLE && result.CONFIRM_TITLE.length > 0)
		{
			confirmTitle = result.CONFIRM_TITLE ;
		}

		if (result.CONFIRM_MESSAGE && result.CONFIRM_MESSAGE.length > 0)
		{
			confirmMessage = confirmMessage + "<br/>" + result.CONFIRM_MESSAGE;
		}


		BX.Sale.Admin.OrderEditPage.showConfirmDialog(
			confirmMessage,
			confirmTitle,
			BX.proxy(function(){
				this.sendStrictUpdatePaymentStatus(status, params)
			}, this),
			function () {
				return;
			}
		);
	}
	else
	{
		this.changePaidStatus(status);
		this.initPaidPopup();
		BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT);

		if(typeof result.MARKERS != 'undefined')
		{
			var node = BX('sale-adm-order-problem-block');
			if(node)
				node.innerHTML = result.MARKERS;
		}

		if (result.WARNING && result.WARNING.length > 0)
		{
			BX.Sale.Admin.OrderEditPage.showDialog(result.WARNING);
		}
	}

};

BX.Sale.Admin.OrderPayment.prototype.sendStrictUpdatePaymentStatus = function(status, params)
{
	params['strict'] = true;
	this.sendAjaxChangeStatus(params);
};


BX.Sale.Admin.OrderPayment.prototype.changeNotPaidStatus = function(status)
{
	var btn = BX('BUTTON_PAID_'+this.index);
	var shortBtn = BX('BUTTON_PAID_'+this.index+'_SHORT');

	if (btn)
	{
		BX.html(btn, BX.message('PAYMENT_PAID_'+status));
		BX.addClass(btn, 'notpay');
	}
	if (shortBtn)
	{
		BX.html(shortBtn, BX.message('PAYMENT_PAID_'+status));
		BX.addClass(shortBtn, 'notpay');
	}
};

BX.Sale.Admin.OrderPayment.prototype.changePaidStatus = function(status)
{
	var btn = BX('BUTTON_PAID_'+this.index);
	var shortBtn = BX('BUTTON_PAID_'+this.index+'_SHORT');

	if (btn)
	{
		BX.html(btn, BX.message('PAYMENT_PAID_'+status));
		BX.removeClass(btn, 'notpay');
	}
	if (shortBtn)
	{
		BX.html(shortBtn, BX.message('PAYMENT_PAID_'+status));
		BX.removeClass(shortBtn, 'notpay');
	}
};

BX.Sale.Admin.OrderPayment.prototype.initNotPaidPopup = function()
{
	var indexes = [this.index];
	if (this.viewForm)
		indexes.push(this.index+'_SHORT');

	for (var k in indexes)
	{
		if (!indexes.hasOwnProperty(k))
			continue;

		var menu = [
			{
				'ID': 'PAID',
				'TEXT': BX.message('PAYMENT_PAID_YES'),
				'ONCLICK': BX.proxy(function ()
				{
					if (this.viewForm)
					{
						this.showWindowPaidPayment();
					}
					else
					{
						var paymentPaidObj = BX('PAYMENT_PAID_'+indexes[k]);
						if (paymentPaidObj)
							paymentPaidObj.value = 'Y';

						this.changePaidStatus('YES');
					}
				}, this)
			}
		];

		if (!this.viewForm)
		{
			menu.unshift(

				{
					'ID': 'NOT_PAID',
					'TEXT': BX.message('PAYMENT_PAID_NO'),
					'ONCLICK': BX.proxy(function ()
					{
						this.changeNotPaidStatus('NO');

						var paymentPaidObj = BX('PAYMENT_PAID_'+indexes[k]);
						if (paymentPaidObj)
							paymentPaidObj.value = 'N';
					}, this)
				});
		}

		var act = new BX.COpener({
			DIV: BX('BUTTON_PAID_'+indexes[k]).parentNode,
			MENU: menu
		});
	}
};

BX.Sale.Admin.OrderPayment.prototype.initPaidPopup = function()
{
	var generalStatusFields = BX.findChildrenByClassName(BX('PAYMENT_BLOCK_STATUS_INFO_'+this.index), 'not_paid', true);
	var returnStatusFields = BX.findChildrenByClassName(BX('PAYMENT_BLOCK_STATUS_INFO_'+this.index), 'return', true);

	var indexes = [this.index];
	if (this.viewForm)
		indexes.push(this.index+'_SHORT');

	var menu = [
		{
			'ID': 'CANCEL',
			'TEXT': BX.message('PAYMENT_PAID_CANCEL'),
			'ONCLICK': BX.proxy(function ()
			{
				if (this.viewForm)
				{
					this.showReturnWindow('cancel');
				}
				else
				{
					var paymentPaid = BX("PAYMENT_PAID_"+indexes[k]);
					if (paymentPaid)
						paymentPaid.value = 'N';

					var isReturn = BX("PAYMENT_IS_RETURN_"+indexes[k]);
					if (isReturn)
						isReturn.value = 'N';

					var obOperation = BX("OPERATION_ID_"+this.index);
					if (obOperation)
						obOperation.disabled = true;

					this.changeNotPaidStatus('NO');

					for (var i in generalStatusFields)
					{
						if (!generalStatusFields.hasOwnProperty(i))
							continue;
						BX.style(generalStatusFields[i], 'display', 'table-row');
					}
					for (var i in returnStatusFields)
					{
						if (!returnStatusFields.hasOwnProperty(i))
							continue;
						BX.style(returnStatusFields[i], 'display', 'none');
					}
				}
			}, this)
		}
	];

	if (Object.keys(this.psToReturn).length > 0)
	{
		menu.push(
			{
				'ID': 'RETURN',
				'TEXT': BX.message('PAYMENT_PAID_RETURN'),
				'ONCLICK': BX.proxy(function ()
				{
					if (this.viewForm)
					{
						this.showReturnWindow('return');
					}
					else
					{
						if (BX("PAYMENT_PAID_" + indexes[k]))
							BX("PAYMENT_PAID_" + indexes[k]).value = 'N';

						var obOperation = BX("OPERATION_ID_" + this.index);
						if (obOperation)
							obOperation.disabled = false;

						var isReturn = BX("PAYMENT_IS_RETURN_" + indexes[k]);
						if (isReturn)
							isReturn.value = 'Y';

						this.changeNotPaidStatus('NO');

						for (var i in generalStatusFields)
						{
							if (!generalStatusFields.hasOwnProperty(i))
								continue;
							BX.style(generalStatusFields[i], 'display', 'table-row');
						}
						for (var i in returnStatusFields)
						{
							if (!returnStatusFields.hasOwnProperty(i))
								continue;
							BX.style(returnStatusFields[i], 'display', 'table-row');
						}

						BX.bind(BX('OPERATION_ID_' + this.index), 'change', function ()
						{
							var tr = BX.findParent(this, {tag: 'tr'});
							if (tr)
							{
								var style = (this.value != 'Y') ? 'none' : 'table-row';
								BX.style(tr.nextElementSibling, 'display', style);
							}
						});
					}
				}, this)
			}
		);
	}

	if (!this.viewForm)
	{
		menu.unshift(
			{
				'ID': 'PAID',
				'TEXT': BX.message('PAYMENT_PAID_YES'),
				'ONCLICK': BX.proxy(function ()
				{
					if (this.viewForm)
					{
						this.showWindowPaidPayment();
					}
					else
					{
						var paymentPaid = BX("PAYMENT_PAID_"+indexes[k]);
						if (paymentPaid)
							paymentPaid.value = 'Y';

						this.changePaidStatus('YES');

						var obOperation = BX("OPERATION_ID_"+this.index);
						if (obOperation)
							obOperation.options[obOperation.selectedIndex].value = '';

						for (var i in generalStatusFields)
						{
							if (!generalStatusFields.hasOwnProperty(i))
								continue;
							BX.style(generalStatusFields[i], 'display', 'none');
						}
						for (var i in returnStatusFields)
						{
							if (!returnStatusFields.hasOwnProperty(i))
								continue;
							BX.style(returnStatusFields[i], 'display', 'none');
						}
					}
				}, this)
			}
		);
	}

	for (var k in indexes)
	{
		if (!indexes.hasOwnProperty(k))
			continue;
		var act = new BX.COpener({
			DIV: BX('BUTTON_PAID_'+indexes[k]).parentNode,
			MENU: menu
		});
	}
};

BX.Sale.Admin.OrderPayment.prototype.setPrice = function(price)
{
	var obPrice = BX("PAYMENT_SUM_1");

	if (obPrice && BX.Sale.Admin.OrderEditPage.autoPriceChange)
		obPrice.value = parseFloat(price).toFixed(2);

};

BX.Sale.Admin.OrderPayment.prototype.getCreateOrderFieldsUpdaters = function()
{
	return {
		"PRICE": BX.Sale.Admin.OrderPayment.prototype.setPrice
	};
};

BX.Sale.Admin.OrderPayment.prototype.showCreateCheckWindow = function(paymentId)
{
	ShowWaitWindow();
	var request = {
		'action': 'addCheckPayment',
		'paymentId': paymentId,
		'callback' : BX.proxy(function(result)
		{
			CloseWaitWindow();
			if (result.ERROR && result.ERROR.length > 0)
			{
				BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
			}
			else
			{
				var text = result.HTML;

				var dlg = new BX.CAdminDialog({
					'content': text,
					'title': BX.message('PAYMENT_CASHBOX_CHECK_ADD_WINDOW_TITLE'),
					'resizable': false,
					'draggable': false,
					'height': '100',
					'width': '516',
					'buttons': [
						{
							title: window.BX.message('JS_CORE_WINDOW_SAVE'),
							id: 'saveCheckBtn',
							name: 'savebtn',
							className: window.BX.browser.IsIE() && window.BX.browser.IsDoctype() && !window.BX.browser.IsIE10() ? '' : 'adm-btn-save'
						},
						{
							title: window.BX.message('JS_CORE_WINDOW_CANCEL'),
							id: 'cancelCheckBtn',
							name: 'cancel'
						}
					]
				});
				dlg.Show();

				BX.bind(BX('checkTypeSelect'), 'change', function ()
				{
					var option = this.value;
					var disabled = option.indexOf('advance') !== -1 || option.indexOf('creditpayment') !== -1;

					var parent = BX.findParent(this, {tag : 'tr'});
					var tr = parent.nextElementSibling;
					var checkboxList = BX.findChildren(tr, {tag : 'input'}, true);
					for (var i in checkboxList)
					{
						if (checkboxList.hasOwnProperty(i))
						{
							if (option.indexOf('prepayment') !== -1)
							{
								disabled = (checkboxList[i].name.indexOf('PAYMENT') !== -1);
							}

							var sibling = checkboxList[i].nextElementSibling;
							if (disabled)
							{
								BX.addClass(sibling, "bx-admin-service-restricted");
							}
							else
							{
								BX.removeClass(sibling, "bx-admin-service-restricted");
							}

							if (checkboxList[i].checked)
								checkboxList[i].click();
							checkboxList[i].disabled = disabled;
						}
					}
				});

				BX.bind(BX("cancelCheckBtn"), 'click', BX.delegate(
					function()
					{
						dlg.Close();
						dlg.DIV.parentNode.removeChild(dlg.DIV);
					}
				),this );

				BX.bind(BX("saveCheckBtn"), 'click', BX.delegate(
					function()
					{
						ShowWaitWindow();
						var form = BX('check_payment');

						var subRequest = {
							formData : BX.ajax.prepareForm(form),
							action: 'saveCheck',
							sessid: BX.bitrix_sessid()
						};

						BX.ajax(
						{
							method: 'post',
							dataType: 'json',
							url: '/bitrix/admin/sale_order_ajax.php',
							data: subRequest,
							onsuccess: function(saveResult)
							{
								CloseWaitWindow();
								if (saveResult.ERROR && saveResult.ERROR.length > 0)
								{
									BX.Sale.Admin.OrderEditPage.showDialog(saveResult.ERROR);
								}
								else 
								{
									BX('PAYMENT_CHECK_LIST_ID_' + paymentId).innerHTML = saveResult.CHECK_LIST_HTML;
									if (BX('PAYMENT_CHECK_LIST_ID_SHORT_VIEW' + paymentId) !== undefined && BX('PAYMENT_CHECK_LIST_ID_SHORT_VIEW' + paymentId) !== null)
									{
										BX('PAYMENT_CHECK_LIST_ID_SHORT_VIEW' + paymentId).innerHTML = saveResult.CHECK_LIST_HTML;
									}

									dlg.Close();
									dlg.DIV.parentNode.removeChild(dlg.DIV);
								}
							},
							onfailure: function(data)
							{
								CloseWaitWindow();
							}
						});
					}
				),this);
			}
		}, this)
	};

	BX.Sale.Admin.OrderAjaxer.sendRequest(request, true);
};

BX.Sale.Admin.OrderPayment.prototype.onCheckEntityChoose = function (currentElement, multiSelect)
{
	var checked = currentElement.checked;

	var paymentType = BX(currentElement.id+"_type");
	if (paymentType)
		paymentType.disabled = !checked;
	
	if (!multiSelect)
	{
		var parent = BX.findParent(currentElement, {tag : 'table'});
		var inputs = BX.findChildren(parent, {tag : 'input'}, true);
		for (var i in inputs)
		{
			if (inputs.hasOwnProperty(i))
			{
				if (inputs[i].id === currentElement.id)
					continue;
				
				inputs[i].disabled = checked;
			}
		}
	}
};

BX.Sale.Admin.OrderPayment.prototype.sendQueryCheckStatus = function(checkId)
{
	ShowWaitWindow();
	var request = {
		'action': 'sendQueryCheckStatus',
		'checkId': checkId,
		'callback' : BX.proxy(function(result)
		{
			if (result.ERROR && result.ERROR.length > 0)
			{
				BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
			}

			var paymentId = result.PAYMENT_ID;
			BX('PAYMENT_CHECK_LIST_ID_' + paymentId).innerHTML = result.CHECK_LIST_HTML;
			if (BX('PAYMENT_CHECK_LIST_ID_SHORT_VIEW' + paymentId) !== undefined && BX('PAYMENT_CHECK_LIST_ID_SHORT_VIEW' + paymentId) !== null)
			{
				BX('PAYMENT_CHECK_LIST_ID_SHORT_VIEW' + paymentId).innerHTML = result.CHECK_LIST_HTML;
			}

			CloseWaitWindow();
		}, this)
	};

	BX.Sale.Admin.OrderAjaxer.sendRequest(request, true);
};

BX.namespace("BX.Sale.Admin.GeneralPayment");

BX.Sale.Admin.GeneralPayment = {

	addNewPayment : function(event, data)
	{
        data = data ? data : {};
        addParams = BX.prop.getObject(data, 'addParams', {});
        formType = BX.prop.getString(data, 'formType', '');

		var obOrderId = BX('ID');

		if (formType == 'edit')
		{
			var parent = BX.findParent(event, {tag : 'div'});
			var blockNewPaymentElements = BX.findChildrenByClassName(parent, 'adm-bus-pay', true);
			var formData = BX.Sale.Admin.OrderEditPage.getAllFormData();

			var request = {
				'method': 'POST',
				'action': 'createNewPayment',
				'index': parseInt(blockNewPaymentElements.length)+1,
				'formData': formData,
				'callback': function (result)
				{
					var data = BX.processHTML(result.PAYMENT);
					var div = BX.create('div');
					div.innerHTML = data['HTML'];
					parent.insertBefore(div, event);
					BX.evalGlobal(data['SCRIPT'][0]['JS']);
					BX.Sale.Admin.OrderEditPage.unRegisterFieldUpdater("PRICE", BX.Sale.Admin.OrderPayment.prototype.setPrice);
				}
			};

            if (addParams)
                request = BX.merge(request, addParams);

			BX.Sale.Admin.OrderAjaxer.sendRequest(request);
		}
		else
		{
            url = 'sale_order_payment_edit.php?lang='+BX.Sale.Admin.OrderEditPage.languageId+'&order_id='+obOrderId.value+'&backurl='+encodeURIComponent(window.location.pathname+window.location.search);
			if (addParams)
                url = BX.util.add_url_param(url, addParams);

			window.location=url;
		}
	},
	useCurrentBudget : function(event)
	{
		var innerBudget = BX('PAYMENT_INNER_BUDGET_ID');
		var select = BX('PAY_SYSTEM_ID_1');
		select.value = innerBudget.value;

		var logo = BX('LOGOTIP_1');
		logo.src = logoList[innerBudget.value];

		var obPayable = BX('sale-order-financeinfo-payable');
		var obUserBudget = BX('sale-order-financeinfo-user-budget-input');

		var payable = parseFloat(obPayable.value);
		var userBudget = parseFloat(obUserBudget.value);
		var price = payable;
		if (userBudget < payable)
			price = userBudget;

		BX.Sale.Admin.OrderEditPage.autoPriceChange = true;
		BX.Sale.Admin.OrderPayment.prototype.setPrice(price);
		BX.Sale.Admin.OrderEditPage.autoPriceChange = false;

		BX.Sale.Admin.OrderEditPage.showDialog(BX.message['PAYMENT_USE_INNER_BUDGET']);
		BX.hide(event);
	}
};
