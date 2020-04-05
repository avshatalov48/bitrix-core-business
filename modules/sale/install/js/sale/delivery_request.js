/**
 * Class BX.Sale.Delivery.Request
 */
BX.namespace("BX.Sale.Delivery.Request");

(function(window) {

	BX.Sale.Delivery.Request = {

		ajaxUrl: "/bitrix/admin/sale_delivery_request_ajax.php",
		timeout: 120,

		showShipmentContent: function(requestId, shipmentId)
		{
			var storeForm = new BX.CDialog({
				'title': BX.message('SALE_DELIVERY_REQ_DIALOG_CONTENT')+" \""+shipmentId+"\"",
				'content_url': this.ajaxUrl,
				'content_post': 'requestId='+requestId+'&shipmentId='+shipmentId+"&action=getShipmentContent&lang="+BX.message('LANGUAGE_ID')+"&sessid="+BX.bitrix_sessid(),
				'width': 500,
				'height':550
			});

			var button = [
				{
					title: BX.message('SALE_DELIVERY_REQ_DIALOG_CLOSE'),
					id: 'close',
					name: 'close',
					action: function () {
						this.parentWindow.Close();
					}
				}
			];

			storeForm.ClearButtons();
			storeForm.SetButtons(button);
			storeForm.Show();
		},

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
						if(result.RESULT != 'ERROR')
						{
							if(result.FILE_NAME && result.FILE_PATH)
							{
								window.location.href = BX.Sale.Delivery.Request.ajaxUrl+'?action=downloadFile&fileName='+encodeURI(result.FILE_NAME)+'&filePath='+encodeURI(result.FILE_PATH)+'&sessid='+BX.bitrix_sessid();
							}
							else if(result.DAILOG_PARAMS)
							{
								BX.Sale.Delivery.Request.showRequestDialog(result.DAILOG_PARAMS, refreshPage);
							}
						}
						else
						{
							var message = BX.message('SALE_DELIVERY_REQ_ERROR')+"\n";

							if(result.ERRORS)
								for(var i in result.ERRORS)
									if(result.ERRORS.hasOwnProperty(i))
										message += result.ERRORS[i]+"\n";

							alert(message);
						}

						if(result.WARNINGS && result.WARNINGS.length > 0)
						{
							message += "\n"+BX.message('SALE_DELIVERY_REQ_WARNING')+"\n";

							for(i in result.WARNINGS)
								if(result.WARNINGS.hasOwnProperty(i))
									message += result.WARNINGS[i]+"\n";
						}

						if(result.MESSAGES && result.MESSAGES.length > 0)
							for(i in result.MESSAGES)
								if(result.MESSAGES.hasOwnProperty(i))
									message += result.MESSAGES[i]+"\n";
					}
					else
					{
						alert(BX.message('SALE_DELIVERY_REQ_ERROR_RECEIVING'));
					}
				},

				onfailure: function()
				{
					CloseWaitWindow();
					alert(BX.message('SALE_DELIVERY_REQ_ERROR_PROCESSING'));
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
					title: BX.message('SALE_DELIVERY_REQ_DIALOG_CLOSE'),
					id: 'close',
					name: 'close',
					action: function () {

						if(refreshPage)
							window.location.reload(true);

						this.parentWindow.Close();
					}
				}
			];

			if(params.IS_FINAL != true)
			{
				buttons.push(
					{
						title: BX.message('SALE_DELIVERY_REQ_DIALOG_NEXT'),
						id: 'next',
						name: 'next',
						action: function () {
							var form = BX('dailogContentForm'),
								formElemets = {},
								arrReg = /\[\]$/g;

							for(var i = 0, l = form.elements.length; i < l; i++)
							{
								var elName = form.elements[i].name;

								if(elName.search(arrReg) == -1)
								{
									formElemets[elName] = form.elements[i].value;
								}
								else
								{
									elName = elName.replace(arrReg, '');

									if(typeof formElemets[elName] == 'undefined')
										formElemets[elName] = [];

									formElemets[elName].push(form.elements[i].value);
								}
							}

							BX.Sale.Delivery.Request.processRequest(formElemets, refreshPage);
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
	}
})(window);
