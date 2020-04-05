BX.namespace("BX.Sale.Location.Map");

BX.Sale.Location.Map =
{
	ajaxUrl: "",
	interruptFlag: false,
	requestFlag: false,
	serviceLocationClass: "",

	sendRequest: function(request, callback)
	{
		if(!request)
			return;

		var postData = request,
			_this = this;

		postData.sessid = BX.bitrix_sessid();
		this.requestFlag = true;

		BX.ajax({
			timeout:    300,
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

	startLocationsCompare: function(needToDeleteExist)
	{
		this.showProgressDialog();

		this.sendRequest({
				action: 'locations_map',
				class: BX.Sale.Location.Map.serviceLocationClass,
				needToDeleteExist: needToDeleteExist || false
			},
			BX.Sale.Location.Map.processLocationsCompareAnswer
		);
	},

	processLocationsCompareAnswer: function(answer)
	{
		if(!answer || !answer.stage || !answer.action)
		{
			BX.Sale.Location.Map.pb.showError(BX.message('SALE_DLVRS_ADD_LOC_COMP_AJAX_ERROR'));
			return;
		}

		if(answer.message)
			BX.Sale.Location.Map.pb.showMessage(answer.message);

		if(answer.progress)
			BX.Sale.Location.Map.pb.Update(answer.progress);

		if(answer.error)
		{
			BX.Sale.Location.Map.pb.showError(answer.error);
			return;
		}

		if(answer.stage && answer.stage == 'finish')
		{
			BX('progress_cancel').value = BX.message('SALE_LOCATION_MAP_CLOSE');
			return;
		}

		BX.Sale.Location.Map.sendRequest({
				action: answer.action,
				stage: answer.stage,
				step: answer.step ?  answer.step : '',
				progress: answer.progress ?  answer.progress : 0,
				class: BX.Sale.Location.Map.serviceLocationClass
			},
			BX.Sale.Location.Map.processLocationsCompareAnswer
		);
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
			content: BX.Sale.Location.Map.pb.getNode(),
			width: 530,
			height: 200,
			draggable: true,
			resizable: true,
			title: BX.message('SALE_LOCATION_MAP_LOC_MAPPING'),
			buttons: [
				{
					title: BX.message('SALE_LOCATION_MAP_CANCEL'),
					id: 'progress_cancel',
					name: 'progress_cancel',
					action: function () {
						window.location.reload();
						BX.Sale.Location.Map.closeProgressDialog();
					}
				}
			]
		});

		BX.Sale.Location.Map.pb.Init();

		popup.adjustSizeEx();
		popup.Show();

		BX.Sale.Location.Map.pb.showError('');
		BX.Sale.Location.Map.pb.showMessage(BX.message('SALE_LOCATION_MAP_PREPARING'));
	},

	pb: {
		width:0,
		obContainer: false,
		obIndicator: false,
		obIndicator2: false,

		Init: function()
		{
			this.obContainer = BX('install-load-block');
			this.obIndicator = BX('install-progress-bar-inner-text');
			this.obIndicator2 = BX('install-progress-bar-span');
			this.obIndicator3 = BX('install-progress-bar-inner');

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
			var errDiv = BX("install-load-error");
			errDiv.innerHTML = errorMsg;
			errDiv.style.display = !!errorMsg ? '' : 'none';
			BX.WindowManager.Get().adjustSizeEx();
		},

		showMessage: function(message, savePrevMsg)
		{
			var msgDiv = BX("install-load-label"),
					oldMessages = msgDiv.innerHTML;

			msgDiv.innerHTML = (savePrevMsg ? oldMessages +"<br>" : '')+message;
			msgDiv.style.display = !!message ? '' : 'none';
			BX.WindowManager.Get().adjustSizeEx();
		},

		getNode: function()
		{
			var node = BX('install-load-block');

			if(!node)
			{
				node = BX.create('div', {
					props: {
						className: 'install-load-block',
						id: 'install-load-block'
					},
					children:[
						BX.create('div',{
							props: {
								className: 'install-load-label',
								id: 'install-load-label'
							}
						}),
						BX.create('div',{
							props: {
								className: 'install-load-error',
								id: 'install-load-error'
							}
						}),
						BX.create('div',{
							props: {
								className: 'install-progress-bar-outer',
								id: 'install-progress-bar-outer'
							},
							style: {width: '500px'},
							children: [
								BX.create('div',{
									props: {
										className: 'install-progress-bar-alignment'
									},
									children: [
										BX.create('div',{
											props: {
												className: 'install-progress-bar-inner',
												id: 'install-progress-bar-inner'
											},
											style: {width: '0%'},
											children:[
												BX.create('div',{
													props: {
														className: 'install-progress-bar-inner-text',
														id: 'install-progress-bar-inner-text'
													},
													style: {width: '500px'},
													html: '0%'
												})
											]
										}),
										BX.create('div',{
											props: {
												className: 'install-progress-bar-span',
												id: 'install-progress-bar-span'
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

