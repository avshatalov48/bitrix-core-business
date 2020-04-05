BX.namespace("BX.Sale.Handler.Delivery.Additional");

BX.Sale.Handler.Delivery.Additional =
{
	ajaxUrl: "/bitrix/tools/sale/delivery_additional.php",
	interruptFlag: false,
	requestFlag: false,

	onRusPostShippingPointsSelect: function(buttonNode, roNameId)
	{
		if(buttonNode && buttonNode.form
			&& buttonNode.form.elements.ID
			&& buttonNode.form.elements['CONFIG[MAIN][SHIPPING_POINT][VALUE]']
		)
		{
			BX.Sale.Handler.Delivery.Additional.showRusPostShippingPointsDialog(
				buttonNode.form.elements.ID.value,
				buttonNode.form.elements['CONFIG[MAIN][SHIPPING_POINT][VALUE]'].value,
				buttonNode.form,
				roNameId
			);
		}
	},

	showRusPostShippingPointsDialog: function(deliveryId, spSelected, form, roNameId)
	{	var self = this;
		var popup = new BX.CDialog({
			content_url: this.ajaxUrl,
			content_post: 'action=get_ruspost_shipping_points_list&deliveryId='+deliveryId+'&spSelected='+spSelected+'&sessid='+BX.bitrix_sessid(),
			width: 500,
			height: 51,
			draggable: true,
			resizable: false,
			title: BX.message('SALE_DLVRS_ADD_SP_CHOOSE_TITLE'),
			buttons: [
				{
					title: BX.message('SALE_DLVRS_ADD_SP_SAVE'),
					id: 'save-butt',
					action: function ()
					{
						var selector = BX('sale-delivery-ruspost-shipment-points');

						if(selector && selector.options && selector.options.length)
						{
							for(var i = selector.options.length - 1; i >= 0; i--)
							{
								if(selector.options[i].selected === true)
								{
									self.setShippingPoint(
										selector.options[i].value,
										selector.options[i].text,
										form,
										roNameId
									);
									break;
								}
							}
						}

						this.parentWindow.Close();
					}
				},
				BX.CDialog.btnCancel
			]});

		popup.Show();
	},

	setShippingPoint: function(zip, address, form, roNameId)
	{
		if(form.elements['CONFIG[MAIN][SHIPPING_POINT][NAME]'])
		{
			form.elements['CONFIG[MAIN][SHIPPING_POINT][NAME]'].value = address;
		}

		if(form.elements['CONFIG[MAIN][SHIPPING_POINT][VALUE]'])
		{
			form.elements['CONFIG[MAIN][SHIPPING_POINT][VALUE]'].value = zip;
		}

		var roNameNode = BX(roNameId);

		if(roNameNode)
		{
			roNameNode.innerHTML = address;
		}
	},

	sendRequest: function(request)
	{
		if(!request)
			return;

		var postData = request,
			callback = request.callback ? request.callback : null,
			_this = this;

		if(postData.callback)
			delete postData.callback;

		postData.sessid = BX.bitrix_sessid();
		this.requestFlag = true;

		BX.ajax({
			timeout:    120,
			method:     'POST',
			dataType:   'json',
			url:        this.ajaxUrl,
			data:       postData,

			onsuccess: function(result)
			{
				_this.requestFlag = false;

				if(_this.interruptFlag)
				{
					_this.closeProgressDialog();
					return;
				}

				if(result)
				{
					if(callback && typeof callback == "function")
						callback.call(null, result);
				}
				else
				{
					_this.pb.showError(BX.message('SALE_DLVRS_ADD_LOC_COMP_AJAX_ERROR'));
				}

				if(result && result.ERROR)
				{
					_this.pb.showError(result.ERROR);
				}

			},

			onfailure: function(status)
			{
				_this.requestFlag = false;
				_this.pb.showError("ajax onfailure");
				_this.pb.showError("status: "+ status);

				if(_this.interruptFlag)
					_this.closeProgressDialog();
			}
		});
	},

	startLocationsCompare: function()
	{
		this.showProgressDialog();

		this.sendRequest({
			action: 'locations_compare',
			callback: BX.Sale.Handler.Delivery.Additional.processLocationsCompareAnswer
		});
	},

	processLocationsCompareAnswer: function(answer)
	{
		if(!answer || !answer.stage || !answer.action)
		{
			BX.Sale.Handler.Delivery.Additional.pb.showError(BX.message('SALE_DLVRS_ADD_LOC_COMP_AJAX_ERROR'));
			return;
		}

		if(answer.message)
			BX.Sale.Handler.Delivery.Additional.pb.showMessage(answer.message);

		if(answer.progress)
			BX.Sale.Handler.Delivery.Additional.pb.Update(answer.progress);

		if(answer.error)
		{
			BX.Sale.Handler.Delivery.Additional.pb.showError(answer.error);
			return;
		}

		if(answer.stage && answer.stage == 'finish')
		{
			BX('progress_cancel').value = BX.message('SALE_DLVRS_ADD_LOC_COMP_CLOSE');
			return;
		}

		BX.Sale.Handler.Delivery.Additional.sendRequest({
			action: answer.action,
			stage: answer.stage,
			step: answer.step ?  answer.step : '',
			progress: answer.progress ?  answer.progress : 0,
			callback: BX.Sale.Handler.Delivery.Additional.processLocationsCompareAnswer
		});
	},

	closeProgressDialog: function()
	{

		if(!this.interruptFlag)
			this.interruptFlag = true;

		if(this.requestFlag)
			return;

		BX.WindowManager.Get().Close();

		if(this.interruptFlag)
			this.interruptFlag = false;
	},

	showProgressDialog: function()
	{
		var popup = new BX.CDialog({
			content: BX.Sale.Handler.Delivery.Additional.pb.getNode(),
			width: 530,
			height: 200,
			draggable: true,
			resizable: true,
			title: BX.message('SALE_DLVRS_ADD_LOC_COMP_TITLE'),
			buttons: [
				{
					title: BX.message('JS_CORE_WINDOW_CANCEL'),
					id: 'progress_cancel',
					name: 'progress_cancel',
					action: function () {
						window.location.reload();
						BX.Sale.Handler.Delivery.Additional.closeProgressDialog();
					}
				}
			]
		});

		BX.Sale.Handler.Delivery.Additional.pb.Init();

		popup.adjustSizeEx();
		popup.Show();

		BX.Sale.Handler.Delivery.Additional.pb.showError('');
		BX.Sale.Handler.Delivery.Additional.pb.showMessage(BX.message('SALE_DLVRS_ADD_LOC_COMP_PREPARE'));
	},

	pb: {
		width:0,
		obContainer: false,
		obIndicator: false,
		obIndicator2: false,

		Init: function()
		{
			this.obContainer = BX('instal-load-block');
			this.obIndicator = BX('instal-progress-bar-inner-text');
			this.obIndicator2 = BX('instal-progress-bar-span');
			this.obIndicator3 = BX('instal-progress-bar-inner');

			this.obContainer.style.display = '';
			this.width = this.obContainer.clientWidth || this.obContainer.offsetWidth;
		},

		Update: function(percent)
		{
			this.obIndicator.innerHTML = this.obIndicator3.style.width = percent+'%';
			this.obIndicator2.innerHTML = percent+'%';
		},

		showError: function(errorMsg)
		{
			var errDiv = BX("instal-load-error");
			errDiv.innerHTML = errorMsg;
			errDiv.style.display = !!errorMsg ? '' : 'none';
			BX.WindowManager.Get().adjustSizeEx();
		},

		showMessage: function(message, savePrevMsg)
		{
			var msgDiv = BX("instal-load-label"),
					oldMessages = msgDiv.innerHTML;

			msgDiv.innerHTML = (savePrevMsg ? oldMessages +"<br>" : '')+message;
			msgDiv.style.display = !!message ? '' : 'none';
			BX.WindowManager.Get().adjustSizeEx();
		},

		getNode: function()
		{
			var node = BX('instal-load-block');

			if(!node)
			{
				node = BX.create('div', {
					props: {
						className: 'instal-load-block',
						id: 'instal-load-block'
					},
					children:[
						BX.create('div',{
							props: {
								className: 'instal-load-label',
								id: 'instal-load-label'
							}
						}),
						BX.create('div',{
							props: {
								className: 'instal-load-error',
								id: 'instal-load-error'
							}
						}),
						BX.create('div',{
							props: {
								className: 'instal-progress-bar-outer',
								id: 'instal-progress-bar-outer'
							},
							style: {width: '500px'},
							children: [
								BX.create('div',{
									props: {
										className: 'instal-progress-bar-alignment'
									},
									children: [
										BX.create('div',{
											props: {
												className: 'instal-progress-bar-inner',
												id: 'instal-progress-bar-inner'
											},
											style: {width: '0%'},
											children:[
												BX.create('div',{
													props: {
														className: 'instal-progress-bar-inner-text',
														id: 'instal-progress-bar-inner-text'
													},
													style: {width: '500px'},
													html: '0%'
												})
											]
										}),
										BX.create('div',{
											props: {
												className: 'instal-progress-bar-span',
												id: 'instal-progress-bar-span'
											},
											html: '0%'
										})
									]
								})
							]
						})
					]
				});
			}

			return node;
		}
	}
};

