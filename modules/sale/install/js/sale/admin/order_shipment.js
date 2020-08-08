BX.namespace("BX.Sale.Admin.OrderShipment");

BX.Sale.Admin.OrderShipment = function(params)
{
	this.index = params.index;
	this.id = params.id;
	this.shipment_statuses = params.shipment_statuses;
	this.isAjax = !!params.isAjax;
	this.srcList = params.src_list;
	this.allowAvailable = !!params.canAllow;
	this.deductAvailable = !!params.canDeduct;
	this.changeStatusAvailable = !!params.canChangeStatus;
	this.discounts = params.discounts || {};
	this.discountsMode = params.discountsMode || "edit";
	this.weightKoeff = params.weightKoeff || 1;
	this.weightUnit = params.weightUnit || '';

	if (this.allowAvailable)
		this.initFieldChangeAllowDelivery();

	if (this.deductAvailable)
		this.initFieldChangeDeducted();

	if (this.changeStatusAvailable)
		this.initFieldChangeStatus();

	if (!!params.active && params.templateType == 'view')
		this.initUpdateTrackingNumber();

	this.initFieldUpdateSum();
	this.initFieldUpdateWeight();

	this.initChangeProfile();
	this.initCustomEvent();
	this.initToggle();
	this.initDeleteShipment();

	if (this.discounts)
		this.setDiscountsList(this.discounts);

	var updater = [];

	if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
	{
		updater["DELIVERY_PRICE_DISCOUNT"] = {
			callback: this.setDeliveryPrice,
			context: this
		};

		updater["DELIVERY_WEIGHT"] = {
			callback: this.setDeliveryWeight,
			context: this
		};
	}

	if (!!params.calculated_price)
		this.setCalculatedPriceDelivery(params.calculated_price);

	if (!!params.calculated_weight)
		this.setCalculatedWeightDelivery(params.calculated_weight);

	updater["DEDUCTED_"+this.id] = {
		callback: this.updateDeductedStatus,
		context: this
	};

	updater["ALLOW_DELIVERY_"+this.id] = {
		callback: this.updateAllowDeliveryStatus,
		context: this
	};

	updater["SHIPMENT_STATUS_LIST_"+this.id] = {
		callback: this.setShipmentStatusList,
		context: this
	};

	updater["SHIPMENT_STATUS_"+this.id] = {
		callback: this.setDeliveryStatus,
		context: this
	};

	if (params.templateType == 'edit')
	{
		updater["BASE_PRICE_DELIVERY"] = {
			callback: this.setDeliveryBasePrice,
			context: this
		};

		updater["CALCULATED_PRICE"] = {
			callback: this.setCalculatedPriceDelivery,
			context: this
		};

		updater["CALCULATED_WEIGHT"] = {
			callback: this.setCalculatedWeightDelivery,
			context: this
		};

		updater["DELIVERY_ERROR"] = {
			callback: BX.Sale.Admin.OrderEditPage.showDialog,
			context: this
		};

		updater["MAP"] = {
			callback: this.updateMap,
			context: this
		};

		updater["PROFILES"] = {
			callback: this.updateProfiles,
			context: this
		};

		updater["EXTRA_SERVICES"] = {
			callback: this.updateExtraService,
			context: this
		};

		updater["DELIVERY_SERVICE_LIST"] = {
			callback: this.updateDeliveryList,
			context: this
		};


		updater["SHIPMENT_COMPANY_ID"] = {
			callback: this.updateCompany,
			context: this
		};

		if (!!BX.Sale.Admin.OrderBuyer && !!BX.Sale.Admin.OrderBuyer.propertyCollection)
		{
			var propLocation = BX.Sale.Admin.OrderBuyer.propertyCollection.getDeliveryLocation();
			if (propLocation)
			{
				propLocation.addEvent("change", function ()
				{
					BX.Sale.Admin.OrderAjaxer.sendRequest(
						BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData(), true
					);
				});
			}
		}
	}

	updater["DISCOUNTS_LIST"] = {
		callback: this.setDiscountsList,
		context: this
	};

	BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters(updater);
};

BX.Sale.Admin.OrderShipment.prototype.updateCompany = function(companyList)
{
	var company = BX('SHIPMENT_COMPANY_ID_'+this.index);
	if (company)
		company.innerHTML = companyList;
};

BX.Sale.Admin.OrderShipment.prototype.updateDeductedStatus = function (flag)
{
	this.setDeducted({status : flag});
};

BX.Sale.Admin.OrderShipment.prototype.updateAllowDeliveryStatus = function (flag)
{
	this.setAllowDelivery({status : flag});
};

BX.Sale.Admin.OrderShipment.prototype.initUpdateTrackingNumber = function ()
{
	var oldValue = '';
	var trackingNumberEdit = BX('TRACKING_NUMBER_'+this.index+'_EDIT');
	var trackingNumberView = BX('TRACKING_NUMBER_'+this.index+'_VIEW');
	var pencilEdit = BX('TRACKING_NUMBER_PENCIL_'+this.index);

	if (pencilEdit)
	{
		BX.bind(pencilEdit, 'click', function ()
		{
			BX.toggle(this);
			if (trackingNumberEdit)
			{
				BX.toggle(trackingNumberEdit);
				BX.toggle(trackingNumberView);
				trackingNumberEdit.focus();
			}
		});

		BX.bind(trackingNumberEdit, 'blur', BX.proxy(function()
		{
			BX.toggle(pencilEdit);
			BX.toggle(trackingNumberEdit);
			trackingNumberView.innerHTML = trackingNumberEdit.value;
			BX.toggle(trackingNumberView);

			if (trackingNumberEdit.value != oldValue)
			{
				var request = {
					'action': 'saveTrackingNumber',
					'orderId': BX('ID').value,
					'shipmentId': BX('SHIPMENT_ID_' + this.index).value,
					'trackingNumber': trackingNumberEdit.value
				};

				BX.Sale.Admin.OrderAjaxer.sendRequest(request);
			}
		}, this));

		BX.bind(trackingNumberEdit, 'focus', function()
		{
			oldValue = trackingNumberEdit.value;
		});
	}
};

BX.Sale.Admin.OrderShipment.prototype.updateDeliveryList = function(services)
{
	var serviceControl = BX('DELIVERY_'+this.index);

	if (!serviceControl)
	{
		return;
	}

	var selectedItem = 0;

	if(serviceControl.options[serviceControl.selectedIndex])
	{
		selectedItem = serviceControl.options[serviceControl.selectedIndex].value;
	}

	serviceControl.innerHTML = services;

	for (var i in serviceControl.options)
	{
		if (serviceControl.options[i].value == selectedItem)
		{
			serviceControl.options[i].selected = true;
			break;
		}
	}
};

BX.Sale.Admin.OrderShipment.prototype.setDiscountsList = function(discounts)
{
	this.discounts = discounts;
	var container = BX("sale-order-shipment-discounts-container-"+this.index),
		row = BX("sale-order-shipment-discounts-row-"+this.index),
		display = "none";

	if(container)
	{
		container = BX.cleanNode(container, false);

		if(discounts && discounts.RESULT && discounts.RESULT.DELIVERY && discounts.RESULT.DELIVERY.length > 0)
		{
			container.appendChild(
				this.createDiscountsNode(discounts.RESULT.DELIVERY)
			);

			display = "";
		}
	}

	if(row && row.previousElementSibling)
	{
		row.style.display = display;
		row.previousElementSibling.style.display = display;
	}
};

BX.Sale.Admin.OrderShipment.prototype.createDiscountsNode = function(discounts)
{
	return BX.Sale.Admin.OrderEditPage.createDiscountsNode(
		"",
		"DELIVERY",
		discounts,
		this.discounts,
		this.discountsMode == "edit" ? "EDIT" : "VIEW"
	);
};

BX.Sale.Admin.OrderShipment.prototype.updateProfiles = function(profiles)
{
	var selectedItem = null;
	var blockDeliveryService = BX('BLOCK_DELIVERY_SERVICE_' + this.index);
	var blockProfiles = BX('BLOCK_PROFILES_' + this.index);

	var select = BX('PROFILE_' + this.index);
	if (select)
		selectedItem = select.options[select.selectedIndex].value;

	if (blockProfiles)
		BX.remove(blockProfiles);

	var tr = BX.create('tr', {
		props: {
			id: 'BLOCK_PROFILES_' + this.index
		},
		children: [
			BX.create('td', {
				text: BX.message('SALE_ORDER_SHIPMENT_PROFILE')+':',
				style: {
					'width': '40%'
				},
				props: {
					className: 'adm-detail-content-cell-l'
				}
			}),
			BX.create('td', {
				html: profiles,
				props: {
					id: ' PROFILE_SELECT_' + this.index,
					className: 'adm-detail-content-cell-r'
				}
			})
		]
	});
	blockDeliveryService.parentNode.appendChild(tr);

	select = tr.lastChild.firstChild;

	if (selectedItem)
	{
		for (var i in select.options)
		{
			if (select.options[i].value == selectedItem)
			{
				select.options[i].selected = true;
				break;
			}
		}
	}

	BX.bind(select, 'change', BX.proxy(function() {
		if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
		{
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
			);
			this.updateDeliveryLogotip();
		}
		else
		{
			this.updateDeliveryInfo();
		}
	}, this));
};

BX.Sale.Admin.OrderShipment.prototype.updateExtraService = function(extraService)
{
	var blockExtraService = BX('DELIVERY_INFO_'+this.index);
	blockExtraService.innerHTML = extraService;
};

BX.Sale.Admin.OrderShipment.prototype.updateShipmentStatus = function(field, status, params)
{
	var request = {
		'action' : 'updateShipmentStatus',
		'orderId' : BX('ID').value,
		'shipmentId' : BX('SHIPMENT_ID_'+this.index).value,
		'field' : field,
		'status' : status,
		'callback' : BX.proxy(function(result){
			this.callbackUpdateShipmentStatus(result, field, status, params)
		}, this)
	};
	BX.Sale.Admin.OrderAjaxer.sendRequest(request);
};


BX.Sale.Admin.OrderShipment.prototype.callbackUpdateShipmentStatus = function(result, field, status, params)
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
				this.sendStrictUpdateShipmentStatus(field, status, params)
			}, this),
			function () {
				return;
			}
		);
	}
	else
	{
		this[params.callback](params.args);

		if(result.RESULT)
			BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT);

		if (result.WARNING && result.WARNING.length > 0)
		{
			BX.Sale.Admin.OrderEditPage.showDialog(result.WARNING);
		}

		if(typeof result.MARKERS != 'undefined')
		{
			var node = BX('sale-adm-order-problem-block');
			if(node)
				node.innerHTML = result.MARKERS;
		}
	}
};

BX.Sale.Admin.OrderShipment.prototype.sendStrictUpdateShipmentStatus = function(field, status, params)
{
	var request = {
		'action' : 'updateShipmentStatus',
		'orderId' : BX('ID').value,
		'shipmentId' : BX('SHIPMENT_ID_'+this.index).value,
		'field' : field,
		'status' : status,
		'strict': true,
		'callback' : BX.proxy(function(result){
			this.callbackUpdateShipmentStatus(result, field, status, params)
		}, this)
	};
	BX.Sale.Admin.OrderAjaxer.sendRequest(request);
};

BX.Sale.Admin.OrderShipment.prototype.updateMap = function(map)
{
	var data = BX.processHTML(map);
	var div = BX('section_map_'+this.index);

	div.innerHTML = data['HTML'];

	for (var i in data['SCRIPT'])
		BX.evalGlobal(data['SCRIPT'][i]['JS']);

	BX.loadCSS(data['STYLE']);
};

BX.Sale.Admin.OrderShipment.prototype.updateDeliveryLogotip = function()
{
	var obj = BX('DELIVERY_'+this.index);
	var tbody = BX.findParent(obj, {tag : 'tbody'}, true);
	if (tbody.children.length > 1)
		obj = BX('PROFILE_'+this.index);

	var mainLogo = '';
	var shortLogo = '';

	var i = 0;
	if (this.srcList[BX(obj).value])
		i = BX(obj).value;

	mainLogo = this.srcList[i]['MAIN'];
	shortLogo = this.srcList[i]['SHORT'];


	var obMainLogo = BX('delivery_service_logo_' + this.index);
	if (!!obMainLogo)
		obMainLogo.style.background = 'url(' + mainLogo + ')';

	var obShortImg = BX('delivery_service_short_logo_' + this.index);
	if (!!obShortImg)
		obShortImg.style.background = 'url(' + shortLogo + ')';
};

BX.Sale.Admin.OrderShipment.prototype.initChangeProfile = function()
{
	var ob = BX('DELIVERY_'+this.index);

	BX.bind(ob, 'change', BX.proxy(function()
	{
		var blockExtraService = BX('DELIVERY_INFO_'+this.index);
		blockExtraService.innerHTML = '';

		var div = BX('section_map_'+this.index);
		div.innerHTML = '';

		var blockProfiles = BX('BLOCK_PROFILES_'+this.index);
		if (blockProfiles)
			BX.remove(blockProfiles);

		var discounts = BX('sale-order-shipment-discounts-row-' + this.index);
		if (discounts)
		{
			BX.hide(discounts.previousElementSibling);
			BX.hide(discounts);
		}

		var deliveryId = BX(ob).value;
		if (deliveryId > 0)
			this.updateDeliveryInfo();
		else
			this.setDeliveryPrice(0);
	}, this));

	var profile = BX('PROFILE_'+this.index);
	if (profile)
	{
		BX.bind(profile, 'change', BX.proxy(function ()
		{
			var blockExtraService = BX('DELIVERY_INFO_' + this.index);
			blockExtraService.innerHTML = '';

			var div = BX('section_map_' + this.index);
			div.innerHTML = '';

			var discounts = BX('sale-order-shipment-discounts-row-' + this.index);
			if (discounts)
			{
				BX.hide(discounts.previousElementSibling);
				BX.hide(discounts);
			}

			var deliveryId = BX(profile).value;
			if (deliveryId > 0)
				this.updateDeliveryInfo();
			else
				this.setDeliveryPrice(0);
		}, this));
	}

};

BX.Sale.Admin.OrderShipment.prototype.initFieldChangeDeducted = function()
{
	var obStatusDeducted = BX('STATUS_DEDUCTED_'+this.index);
	var postfix = ['SHORT_'+this.index, this.index];
	for (var i in postfix)
	{
		var btnDeducted = BX('BUTTON_DEDUCTED_' + postfix[i]);
		if (!btnDeducted)
			continue;

		var menu = [];
		if (obStatusDeducted.value == 'N')
		{
			menu.push(
				{
					'TEXT': BX.message('SALE_ORDER_SHIPMENT_DEDUCTED_YES'),
					'ONCLICK': BX.proxy(function ()
					{
						var data = {status : 'Y'};
						if (this.isAjax)
							this.updateShipmentStatus('DEDUCTED', 'Y', {callback: 'setDeducted', args: data});
						else
							this.setDeducted(data);

					}, this)
				}
			);
		}
		else
		{
			menu.push(
				{
					'TEXT': BX.message('SALE_ORDER_SHIPMENT_DEDUCTED_NO'),
					'ONCLICK': BX.proxy(function ()
					{
						var data = {status : 'N'};
						if (this.isAjax)
							this.updateShipmentStatus('DEDUCTED', 'N', {callback : 'setDeducted', args : data});
						else
							this.setDeducted(data);
					}, this)
				}
			);
		}

		var deducted = new BX.COpener(
			{
				'DIV': btnDeducted.parentNode,
				'MENU': menu
			}
		);
	}
};

BX.Sale.Admin.OrderShipment.prototype.setDeducted = function(data)
{
	var fullStatus = (data.status == 'Y') ? 'YES' : 'NO';
	var obStatusDeducted = BX('STATUS_DEDUCTED_'+this.index);
	var postfix = ['SHORT_'+this.index, this.index];
	obStatusDeducted.value = data.status;

	for (var i in postfix)
	{
		var btnDeducted = BX('BUTTON_DEDUCTED_' + postfix[i]);
		if (!btnDeducted)
			continue;
		BX.html(btnDeducted, BX.message('SALE_ORDER_SHIPMENT_DEDUCTED_'+fullStatus));
		if (data.status == 'Y')
			BX.removeClass(btnDeducted, 'notdeducted');
		else
			BX.addClass(btnDeducted, 'notdeducted');
	}
	this.initFieldChangeDeducted();
};

BX.Sale.Admin.OrderShipment.prototype.initFieldChangeStatus = function()
{
	var postfix = ['SHORT_'+this.index, this.index];
	var obStatusShipment = BX('STATUS_SHIPMENT_' + this.index);
	for (var i in postfix)
	{
		var btnShipment = BX('BUTTON_SHIPMENT_' + postfix[i]);

		var menu = [];
		for (var j in this.shipment_statuses)
		{
			if (this.shipment_statuses[j].ID == obStatusShipment.value)
				continue;

			function addMenuStatus(status, event)
			{
				var data = {name : status.NAME, id: status.ID};
				var obj = {
					'TEXT': status.NAME,
					'ONCLICK': function ()
					{
						event.updateShipmentStatus('STATUS_ID', status.ID, {callback : 'setDeliveryStatus', args : data});
					}
				};
				menu.push(obj);
			}
			addMenuStatus(this.shipment_statuses[j], this);
		}

		if(btnShipment)
		{
			if (menu.length > 0)
			{
				var shipment = new BX.COpener(
					{
						'DIV': btnShipment.parentNode,
						'MENU': menu
					}
				);
			}
			else
			{
				var span = BX.create('span', {
						children : [
							BX.create('span', {
								attrs :
								{
									id : btnShipment.getAttribute('id'),
									className : 'not_active'
								},
								text : btnShipment.textContent
							})
						]
					}
				);
				btnShipment.parentNode.parentNode.appendChild(span);
				BX.remove(btnShipment.parentNode);
			}
		}
	}
};

BX.Sale.Admin.OrderShipment.prototype.setDeliveryStatus = function (data)
{

	var obStatusShipment = BX('STATUS_SHIPMENT_' + this.index);
	obStatusShipment.value = data.id;

	var postfix = ['SHORT_'+this.index, this.index];
	for (var k in postfix)
	{
		var btnShipment = BX('BUTTON_SHIPMENT_' + postfix[k]);
		BX.html(btnShipment, data.name);
	}

	this.initFieldChangeStatus();
};

BX.Sale.Admin.OrderShipment.prototype.setDeliveryBasePrice = function(basePrice)
{
	if (BX('BASE_PRICE_DELIVERY_'+this.index))
		BX('BASE_PRICE_DELIVERY_'+this.index).value = basePrice;

	if (BX('BASE_PRICE_DELIVERY_T_'+this.index))
		BX('BASE_PRICE_DELIVERY_T_'+this.index).innerHTML = basePrice;
};

BX.Sale.Admin.OrderShipment.prototype.setDeliveryWeight = function(weight)
{
	var weightCell = BX('WEIGHT_DELIVERY_'+this.index);

	if(!weightCell)
	{
		return;
	}

	if(weightCell.tagName === 'INPUT')
	{
		weightCell.value = weight;
	}
	else if(weightCell.tagName === 'TD')
	{
		weightCell.innerHTML = weight + ' ' + this.weightUnit;
	}
};

BX.Sale.Admin.OrderShipment.prototype.setDeliveryPrice = function(price)
{
	var priceCell = BX('PRICE_DELIVERY_'+this.index);

	if(!priceCell)
	{
		return;
	}

	if(priceCell.tagName === 'INPUT')
	{
		priceCell.value = price;
	}
	else if(priceCell.tagName === 'TD')
	{
		priceCell.innerHTML = BX.Sale.Admin.OrderEditPage.currencyFormat(price);
	}
};

BX.Sale.Admin.OrderShipment.prototype.setCalculatedPriceDelivery = function(deliveryPrice)
{
	var customPrice = BX('CUSTOM_PRICE_DELIVERY_'+this.index);
	if (customPrice.value != 'Y' && BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
		return;

	var obDiscountSum = BX('PRICE_DELIVERY_'+this.index);
	if (obDiscountSum)
	{
		var parent = BX.findParent(obDiscountSum, {tag: 'tbody'}, true);
		var child = BX.findChildByClassName(parent, 'row_set_new_delivery_price');
		if (child)
			BX.remove(child);
	}

	BX('CALCULATED_PRICE_'+this.index).value = deliveryPrice;

	var tr = BX.create('tr',
	{
		children : [
			BX.create('td',
			{
				html : BX.message('SALE_ORDER_SHIPMENT_NEW_PRICE_DELIVERY')+': ',
				props : {
					className: 'adm-detail-content-cell-l'
				}
			}),
			BX.create('td',
			{
				children : [
					BX.create('span',
					{
						html : BX.Sale.Admin.OrderEditPage.currencyFormat(deliveryPrice)
					}),
					BX.create('span', {
						text : BX.message('SALE_ORDER_SHIPMENT_APPLY'),
						props : {
							onclick: BX.proxy(function ()
							{
								if (confirm(BX.message('SALE_ORDER_SHIPMENT_CONFIRM_SET_NEW_PRICE')))
								{
									BX('PRICE_DELIVERY_'+this.index).value = deliveryPrice;
									BX('BASE_PRICE_DELIVERY_'+this.index).value = deliveryPrice;

									var child = BX.findChildByClassName(parent, 'row_set_new_delivery_price');
									BX.remove(child);

									customPrice.value = 'N';

									if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
										BX.Sale.Admin.OrderAjaxer.sendRequest(BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData());
								}
							}, this),
							className : 'new_delivery_price_button'
						}
					})
				],
				props : {
					className: 'adm-detail-content-cell-r'
				}
			})
		],
		props : {
			className : 'row_set_new_delivery_price'
		}
	});
	parent.appendChild(tr);
};

BX.Sale.Admin.OrderShipment.prototype.setCalculatedWeightDelivery = function(deliveryWeight)
{
	var customWeight = BX('CUSTOM_WEIGHT_DELIVERY_'+this.index);

	if (customWeight.value !== 'Y' && BX.Sale.Admin.OrderEditPage.formId !== 'order_shipment_edit_info_form')
		return;

	var obCurrentWeight = BX('WEIGHT_DELIVERY_'+this.index);
	if (obCurrentWeight)
	{
		var parent = BX.findParent(obCurrentWeight, {tag: 'tbody'}, true);
		var child = BX.findChildByClassName(parent, 'row_set_new_delivery_weight');

		if (child)
		{
			BX.remove(child);
		}
	}

	BX('CALCULATED_WEIGHT_'+this.index).value = deliveryWeight;

	var tr = BX.create('tr',
		{
			children : [
				BX.create('td',
					{
						html : BX.message('SALE_ORDER_SHIPMENT_NEW_WEIGHT_DELIVERY')+': ',
						props : {
							className: 'adm-detail-content-cell-l'
						}
					}),
				BX.create('td',
					{
						children : [
							BX.create('span',
								{
									text : deliveryWeight + ' ' + this.weightUnit
								}),
							BX.create('span', {
								text : BX.message('SALE_ORDER_SHIPMENT_APPLY'),
								props : {
									onclick: BX.proxy(function ()
									{
										if (confirm(BX.message('SALE_ORDER_SHIPMENT_CONFIRM_SET_NEW_WEIGHT')))
										{
											BX('WEIGHT_DELIVERY_'+this.index).value = deliveryWeight;

											var child = BX.findChildByClassName(parent, 'row_set_new_delivery_weight');
											BX.remove(child);

											customWeight.value = 'N';

											if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
												BX.Sale.Admin.OrderAjaxer.sendRequest(BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData());
										}
									}, this),
									className : 'new_delivery_price_button'
								}
							})
						],
						props : {
							className: 'adm-detail-content-cell-r'
						}
					})
			],
			props : {
				className : 'row_set_new_delivery_weight'
			}
		});
	parent.appendChild(tr);
};

BX.Sale.Admin.OrderShipment.prototype.updateDeliveryInfo = function()
{
	var formData = BX.Sale.Admin.OrderEditPage.getAllFormData();
	var request = {
		'action': 'changeDeliveryService',
		'formData': formData,
		'index' : this.index,
		'callback' : BX.proxy(function (result) {
			if (result.ERROR && result.ERROR.length > 0)
			{
				BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
			}
			else
			{
				BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.SHIPMENT_DATA);
				this.updateDeliveryLogotip();

				if (result.WARNING && result.WARNING.length > 0)
				{
					BX.Sale.Admin.OrderEditPage.showDialog(result.WARNING);
				}
			}
		}, this)
	};
	if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
		BX.Sale.Admin.OrderAjaxer.sendRequest(request, false, true);
	else
		BX.Sale.Admin.OrderAjaxer.sendRequest(request, false, false);
};

BX.Sale.Admin.OrderShipment.prototype.getDeliveryPrice = function()
{
	var formData = BX.Sale.Admin.OrderEditPage.getAllFormData();
	var request = {
	'action': 'getDefaultDeliveryPrice',
	'formData': formData,
	'callback' : BX.proxy(function (result) {
		if (result.ERROR && result.ERROR.length > 0)
		{
			BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
		}
		else
		{
			BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT);
			if (result.WARNING && result.WARNING.length > 0)
			{
				BX.Sale.Admin.OrderEditPage.showDialog(result.WARNING);
			}
		}
		}, this)
	};

	var refreshForm = (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form');
	BX.Sale.Admin.OrderAjaxer.sendRequest(request, false, refreshForm);
};

BX.Sale.Admin.OrderShipment.prototype.initCustomEvent = function()
{
	BX.addCustomEvent('onDeliveryExtraServiceValueChange', BX.proxy(function (params)
	{
		if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
		{
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
			);
		}
		else
		{
			this.getDeliveryPrice();
		}
	}, this));
};

BX.Sale.Admin.OrderShipment.prototype.initFieldChangeAllowDelivery = function()
{
	var obStatusAllowDelivery = BX('STATUS_ALLOW_DELIVERY_'+this.index);
	var postfix = ['SHORT_'+this.index, this.index];
	for (var i in postfix)
	{
		var btnAllowDelivery = BX('BUTTON_ALLOW_DELIVERY_' + postfix[i]);
		if (!btnAllowDelivery)
			continue;

		var menu = [];

		if (obStatusAllowDelivery.value == 'Y')
		{
			menu.push(
				{
					'TEXT': BX.message('SALE_ORDER_SHIPMENT_ALLOW_DELIVERY_NO'),
					'ONCLICK': BX.proxy(function ()
					{
						var data = {status : 'N'};
						if (this.isAjax)
							this.updateShipmentStatus('ALLOW_DELIVERY', 'N', {callback : 'setAllowDelivery', args : data});
						else
							this.setAllowDelivery(data);
					}, this)
				}
			);
		}
		else
		{
			menu.push(
				{
					'TEXT': BX.message('SALE_ORDER_SHIPMENT_ALLOW_DELIVERY_YES'),
					'ONCLICK': BX.proxy(function ()
					{
						var data = {status : 'Y'};
						if (this.isAjax)
							this.updateShipmentStatus('ALLOW_DELIVERY', 'Y', {callback : 'setAllowDelivery', args : data});
						else
							this.setAllowDelivery(data);

						this.initFieldChangeAllowDelivery();
					}, this)
				}
			);
		}

		var allowDelivery = new BX.COpener(
			{
				'DIV' : btnAllowDelivery.parentNode,
				'MENU': menu
			}
		);
	}
};

BX.Sale.Admin.OrderShipment.prototype.setAllowDelivery = function(data)
{
	var fullStatus = (data.status == 'Y') ? 'YES' : 'NO';
	var postfix = ['SHORT_'+this.index, this.index];

	var obStatusAllowDelivery = BX('STATUS_ALLOW_DELIVERY_'+this.index);
	obStatusAllowDelivery.value = data.status;

	for (var i in postfix)
	{
		var btnDelivery = BX('BUTTON_ALLOW_DELIVERY_' + postfix[i]);
		if (!btnDelivery)
			continue;
		BX.html(btnDelivery, BX.message('SALE_ORDER_SHIPMENT_ALLOW_DELIVERY_'+fullStatus));

		if (data.status == 'Y')
			BX.removeClass(btnDelivery, 'notdelivery');
		else
			BX.addClass(btnDelivery, 'notdelivery');
	}
	this.initFieldChangeAllowDelivery();
};

BX.Sale.Admin.OrderShipment.prototype.setShipmentStatusList = function(data)
{
	this.shipment_statuses = data;
	this.initFieldChangeStatus();
};

BX.Sale.Admin.OrderShipment.prototype.initFieldUpdateSum = function()
{
	var obSum = BX('PRICE_DELIVERY_'+this.index);
	var customPrice = BX('CUSTOM_PRICE_DELIVERY_'+this.index);
	BX.bind(obSum, 'change', BX.proxy(function()
	{
		customPrice.value = 'Y';
		if (BX.Sale.Admin.OrderEditPage.formId != 'order_shipment_edit_info_form')
		{
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
			);
		}
		else
		{
			var discounts = BX('sale-order-shipment-discounts-row-' + this.index);
			if (discounts)
			{
				BX.hide(discounts.previousElementSibling);
				BX.hide(discounts);
			}

			BX('CUSTOM_PRICE_DELIVERY_' + this.index).value = 'Y';
			BX('BASE_PRICE_DELIVERY_' + this.index).value = obSum.value;
		}
	}, this));
};

BX.Sale.Admin.OrderShipment.prototype.initFieldUpdateWeight = function()
{
	var obWeight = BX('WEIGHT_DELIVERY_'+this.index);
	var customWeight = BX('CUSTOM_WEIGHT_DELIVERY_'+this.index);
	BX.bind(obWeight, 'change', BX.proxy(function()
	{
		customWeight.value = 'Y';

		if (BX.Sale.Admin.OrderEditPage.formId !== 'order_shipment_edit_info_form')
		{
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
			);
		}
		else
		{
			BX('CUSTOM_WEIGHT_DELIVERY_' + this.index).value = 'Y';
		}
	}, this));
};

BX.Sale.Admin.OrderShipment.prototype.initToggle = function()
{
	var fullView = BX('SHIPMENT_SECTION_'+this.index);
	var shortView = BX('SHIPMENT_SECTION_SHORT_'+this.index);

	var btnToggleView = BX('SHIPMENT_SECTION_'+this.index+'_TOGGLE');
	BX.bind(btnToggleView, 'click', BX.proxy(function() {
		btnToggleView.innerHTML = (shortView.style.display != 'none') ? BX.message('SALE_ORDER_SHIPMENT_BLOCK_SHIPMENT_TOGGLE') : BX.message('SALE_ORDER_SHIPMENT_BLOCK_SHIPMENT_TOGGLE_UP');
		BX.toggle(fullView);
		BX.toggle(shortView);
	}, this));
};


BX.Sale.Admin.OrderShipment.prototype.initDeleteShipment = function()
{
	var btnShipmentSectionDelete = BX('SHIPMENT_SECTION_'+this.index+'_DELETE');
	BX.bind(btnShipmentSectionDelete, 'click', BX.proxy(function() {
		if (confirm(BX.message('SALE_ORDER_SHIPMENT_CONFIRM_DELETE_SHIPMENT')))
			{
				var orderId = (BX('ID')) ? BX('ID').value : 0;
				var shipmentId = (BX('SHIPMENT_ID_'+this.index)) ? BX('SHIPMENT_ID_'+this.index).value : 0;

				if ((orderId > 0) && (shipmentId > 0))
				{
					var request = {
						'action': 'deleteShipment',
						'order_id': orderId,
						'shipment_id': shipmentId,
						'callback' : BX.proxy(function (result) {
							if (result.ERROR && result.ERROR.length > 0)
							{
								BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
							}
							else
							{
								BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT);
								BX.cleanNode(BX('shipment_container_' + this.index));
								if (result.WARNING && result.WARNING.length > 0)
								{
									BX.Sale.Admin.OrderEditPage.showDialog(result.WARNING);
								}
							}
						}, this)
					};
					BX.Sale.Admin.OrderAjaxer.sendRequest(request);
				}
			}
	}, this));
};

BX.Sale.Admin.OrderShipment.prototype.showCreateCheckWindow = function(shipmentId)
{
	ShowWaitWindow();
	var request = {
		'action': 'addCheckShipment',
		'shipmentId': shipmentId,
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
					'title': BX.message('SALE_ORDER_SHIPMENT_CASHBOX_CHECK_ADD_WINDOW_TITLE'),
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
					var disabled = option.indexOf('credit') !== -1;

					var parent = BX.findParent(this, {tag : 'tr'});
					var tr = parent.nextElementSibling;
					var checkboxList = BX.findChildren(tr, {tag : 'input'}, true);
					for (var i in checkboxList)
					{
						if (checkboxList.hasOwnProperty(i))
						{
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
						var form = BX('check_shipment');
						
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
									BX('SHIPMENT_CHECK_LIST_ID_' + shipmentId).innerHTML = saveResult.CHECK_LIST_HTML;
									if (BX('SHIPMENT_CHECK_LIST_ID_SHORT_VIEW' + shipmentId) !== undefined && BX('SHIPMENT_CHECK_LIST_ID_SHORT_VIEW' + shipmentId) !== null)
									{
										BX('SHIPMENT_CHECK_LIST_ID_SHORT_VIEW' + shipmentId).innerHTML = saveResult.CHECK_LIST_HTML;
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



BX.Sale.Admin.OrderShipment.prototype.onCheckEntityChoose = function (currentElement)
{
	var checked = currentElement.checked;
	
	var paymentType = BX(currentElement.id+"_type");
	if (paymentType)
		paymentType.disabled = !checked;
};

BX.Sale.Admin.OrderShipment.prototype.sendQueryCheckStatus = function(checkId)
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
			
			var shipmentId = result.SHIPMENT_ID;
			BX('SHIPMENT_CHECK_LIST_ID_' + shipmentId).innerHTML = result.CHECK_LIST_HTML;
			if (BX('SHIPMENT_CHECK_LIST_ID_SHORT_VIEW' + shipmentId) !== undefined && BX('SHIPMENT_CHECK_LIST_ID_SHORT_VIEW' + shipmentId) !== null)
			{
				BX('SHIPMENT_CHECK_LIST_ID_SHORT_VIEW' + shipmentId).innerHTML = result.CHECK_LIST_HTML;
			}

			CloseWaitWindow();
		}, this)
	};

	BX.Sale.Admin.OrderAjaxer.sendRequest(request, true);
};

BX.namespace("BX.Sale.Admin.GeneralShipment");

BX.Sale.Admin.GeneralShipment =
{
	getIds : function()
	{
		BX.Sale.Admin.OrderAjaxer.sendRequest(
			BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
		);
	},

	createNewShipment : function(event, data)
	{
        data = data ? data : {};
        addParams = BX.prop.getObject(data, 'addParams', {});

		var orderId = BX('ID').value;
        url = '/bitrix/admin/sale_order_shipment_edit.php?lang='+BX.Sale.Admin.OrderEditPage.languageId+'&order_id='+orderId+'&backurl='+encodeURIComponent(window.location.pathname+window.location.search);
		if (addParams)
            url = BX.util.add_url_param(url, addParams);

		window.location = url;
	},
	
	findProductByBarcode : function(_this)
	{
		BX.hide(_this.parentNode);
		BX.show(_this.parentNode.nextElementSibling);
	},

	refreshTrackingStatus : function(shipmentIndex, shipmentId, refreshTrackNumber)
	{
		var trackingNumber = '';

		if(refreshTrackNumber)
		{
			var form = BX('order_shipment_edit_info_form');

			if(form)
			{
				var tnInput = form.elements['SHIPMENT['+shipmentIndex+'][TRACKING_NUMBER]'];

				if(tnInput && tnInput.value)
					trackingNumber = tnInput.value;
			}
		}
		else
		{
			var tnSpan = BX('TRACKING_NUMBER_'+shipmentIndex+'_VIEW');

			if(tnSpan)
				trackingNumber = tnSpan.innerHTML;
		}

		if(!trackingNumber)
		{
			alert(BX.message('SALE_ORDER_SHIPMENT_TRACKING_S_EMPTY'));
			return;
		}

		var params = {
			action: "refreshTrackingStatus",
			shipmentId: shipmentId,
			trackingNumber: trackingNumber,
			callback: function(result)
			{
				if(result && !result.ERROR)
				{
					if(result.TRACKING_STATUS)
					{
						var status = BX("sale-order-shipment-tracking-status-"+shipmentIndex);

						if(status)
							status.innerHTML = result.TRACKING_STATUS;
					}

					if(result.TRACKING_DESCRIPTION)
					{
						var description = BX("sale-order-shipment-tracking-description-"+shipmentIndex);

						if(description)
							description.innerHTML = result.TRACKING_DESCRIPTION;
					}

					if(result.TRACKING_LAST_CHANGE)
					{
						var lastUpdate = BX("sale-order-shipment-tracking-last-change-"+shipmentIndex);

						if(lastUpdate)
							lastUpdate.innerHTML = result.TRACKING_LAST_CHANGE;
					}

					if (result.WARNING && result.WARNING.length > 0)
					{
						BX.Sale.Admin.OrderEditPage.showDialog(result.WARNING);
					}
				}

				else if(result && result.ERROR)
				{
					BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
				}
				else
				{
					BX.debug("Error refreshing tracking status!");
				}
			}
		};

		BX.Sale.Admin.OrderAjaxer.sendRequest(params);
	}
}
;

