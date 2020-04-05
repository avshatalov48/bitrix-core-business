BX.namespace("BX.Sale.Admin.OrderAjaxer");

BX.Sale.Admin.OrderAjaxer =
{
	ajaxUrl: "/bitrix/admin/sale_order_ajax.php",

	sendRequest: function(request, noBlockForm, refreshFormData)
	{
		if(!request)
			return;

		if(!noBlockForm)
			BX.Sale.Admin.OrderEditPage.blockForm();

		var postData = request,
			callback = request.callback ? request.callback : null;

		if(postData.callback)
			delete postData.callback;

		if(refreshFormData)
			postData = this.refreshOrderData.modifyParams(postData);

		postData.sessid = BX.bitrix_sessid();

		var ajaxParams = {
			timeout:    60,
			method:     'POST',
			dataType:   'json',
			url:        this.ajaxUrl,
			data:       postData,

			onsuccess: function(result)
			{
				if(result)
				{
					if(result.NEED_CONFIRM && result.NEED_CONFIRM === 'Y')
					{
						BX.Sale.Admin.OrderEditPage.showConfirmDialog(
							result.CONFIRM.TEXT,
							result.CONFIRM.TITLE,
							function()
							{
								postData.confirmed = 'Y';
								ajaxParams.data = postData;
								BX.ajax(ajaxParams);
							}
						);
					}
					else
					{
						if(callback && typeof callback === "function")
							callback.call(null, result);
					}
				}
				else
				{
					BX.debug("Admin order ajaxer error! Can't receive the result.");
				}

				if(result && result.ERROR)
				{
					BX.debug("Admin order ajaxer recieved error: " + result.ERROR);
				}
				
				if(result && result.WARNING)
				{
					BX.debug("Admin order ajaxer recieved warning: " + result.WARNING);
				}

				if(refreshFormData)
					BX.Sale.Admin.OrderAjaxer.refreshOrderData.callback(result);

				if(!noBlockForm)
					BX.Sale.Admin.OrderEditPage.unBlockForm();
			},

			onfailure: function(status)
			{
				if(!noBlockForm)
					BX.Sale.Admin.OrderEditPage.unBlockForm();

				if(refreshFormData)
					BX.Sale.Admin.OrderAjaxer.refreshOrderData.setFlag(false);

				BX.debug("ajax onfailure");
				BX.debug("status: "+ status);
			}
		};

		BX.ajax(ajaxParams);
	},

	refreshOrderData: {
		flag: false,
		callback: function(result)
		{
			if(result)
			{
				if(result.ERROR)
				{
					BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
					/*
					 * If for example where is no requested quantity of product.
					 * We will receive an error.
					 * So we must return the old value of product witch was before the request with wrong quantity was send
					 * to avoid unpredictable side effects.
					 */
					BX.Sale.Admin.OrderEditPage.rollBack();
				}
				else if(result.ORDER_DATA)
				{
					BX.Sale.Admin.OrderEditPage.resetRollbackMethods();
					BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.ORDER_DATA);
				}
			}
			else
				BX.debug("Error receiving order data!");

			BX.Sale.Admin.OrderAjaxer.refreshOrderData.setFlag(false);
		},

		modifyParams: function(params)
		{
			BX.Sale.Admin.OrderAjaxer.refreshOrderData.setFlag(true);
			params.formData = BX.Sale.Admin.OrderEditPage.getAllFormData();
			params.refreshOrderData = "Y";
			return params;
		},

		setFlag: function(value)
		{
			BX.Sale.Admin.OrderAjaxer.refreshOrderData.flag = !!value;
		},

		getFlag: function()
		{
			return BX.Sale.Admin.OrderAjaxer.refreshOrderData.flag;
		}
	}
};
