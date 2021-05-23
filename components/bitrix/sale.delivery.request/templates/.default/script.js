BX.namespace("BX.Sale.Delivery.Request.Component");

BX.Sale.Delivery.Request.Component = {

	ajaxUrl: '',
	timeout: 120,

	processRequest: function(params, refreshPage)
	{
		ShowWaitWindow();

		var postData = params;
		postData['sessid'] = BX.bitrix_sessid();
		postData['lang'] = BX.message('LANGUAGE_ID');

		BX.ajax({
			timeout:    this.timeout,
			method:     'POST',
			dataType:   'json',
			url:        this.ajaxUrl,
			data:       postData,

			onsuccess: function(result)
			{
				CloseWaitWindow();

				if(result)
				{
					if(result.RESULT !== 'ERROR')
					{
						if(result.FILE_NAME && result.FILE_PATH)
						{
							window.location.href = BX.Sale.Delivery.Request.Component.ajaxUrl+'?action=downloadFile&fileName='+encodeURI(result.FILE_NAME)+'&filePath='+encodeURI(result.FILE_PATH)+'&sessid='+BX.bitrix_sessid();
						}
						else if(result.DAILOG_PARAMS)
						{
							BX.Sale.Delivery.Request.Component.showRequestDialog(result.DAILOG_PARAMS, refreshPage);
						}

						if(result.DELIVERY_BLOCK_HTML && result.DELIVERY_ID)
						{
							var block = BX('delivery-request-for-'+result.DELIVERY_ID);

							if(block)
								block.innerHTML = result.DELIVERY_BLOCK_HTML;
						}
					}
					else
					{
						var message = BX.message('SALE_CSDRTJ_ERROR')+"\n";

						if(result.ERRORS)
							for(var i in result.ERRORS)
								if(result.ERRORS.hasOwnProperty(i))
									message += result.ERRORS[i]+"\n";

						alert(message);
					}
				}
				else
				{
					alert(BX.message('SALE_CSDRTJ_RESPONSE_ERROR'));
				}
			},

			onfailure: function()
			{
				CloseWaitWindow();
				alert(BX.message('SALE_CSDRTJ_RESPONSE_PROCESSING_ERROR'));
			}
		});
	},

	showRequestDialog: function(params, refreshPage)
	{
		var requestDialog = new BX.CDialog({
			'title': params.TITLE,
			'content': '<form id="dailogContentForm">'+params.CONTENT+'</form>',
			'width': 500,
			'height':550
		});

		var buttons = [
			{
				title: BX.message('SALE_CSDRTJ_DIALOG_CLOSE'),
				id: 'close',
				name: 'close',
				action: function () {

					if(refreshPage)
						window.location.reload(true);

					this.parentWindow.Close();
				}
			}
		];

		if(params.IS_FINAL !== true)
		{
			buttons.push(
				{
					title: BX.message('SALE_CSDRTJ_DIALOG_NEXT'),
					id: 'next',
					name: 'next',
					action: function () {
						var form = BX('dailogContentForm'),
							formElemets = {},
							arrReg = /\[\]$/g;

						for(var i = 0, l = form.elements.length; i < l; i++)
						{
							var elName = form.elements[i].name;

							if(elName.search(arrReg) === -1)
							{
								formElemets[elName] = form.elements[i].value;
							}
							else
							{
								elName = elName.replace(arrReg, '');

								if(typeof formElemets[elName] === 'undefined')
									formElemets[elName] = [];

								formElemets[elName].push(form.elements[i].value);
							}
						}

						BX.Sale.Delivery.Request.Component.processRequest(formElemets, refreshPage);
						this.parentWindow.Close();
					}
				}
			);
		}

		BX.addCustomEvent(requestDialog, 'onWindowClose', function(requestDialog) {
			requestDialog.DIV.parentNode.removeChild(requestDialog.DIV);
			BX.proxy(function(){this.Register();}, this);
		});

		requestDialog.ClearButtons();
		requestDialog.SetButtons(buttons);
		requestDialog.Show();
		requestDialog.adjustSizeEx();
	}
};