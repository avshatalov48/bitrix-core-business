__MASaleOrderBarcode = function(params) {

	for(var key in params)
		this[key] = params[key];

	this.INPUT_STYLE_WRONG = 0;
	this.INPUT_STYLE_OK = 1;

	this.productData = {};
	this.barcodesData = {};
	this.barcodesCheckRes = {};

	this.isScanning = false;
};

__MASaleOrderBarcode.prototype.setBarcodeData = function(inputId, barcode)
{
	this.barcodesData[inputId] = barcode;
};

__MASaleOrderBarcode.prototype.setBarcodeCheckRes = function(inputId, checkRes)
{
	this.barcodesCheckRes[inputId] = checkRes;
};

__MASaleOrderBarcode.prototype.setInput = function(inputId, barcode)
{
	if(!inputId || !barcode)
		return;

	var inp = BX(inputId);

	if(!inp)
		return;

	inp.value = barcode;
};

__MASaleOrderBarcode.prototype.getInput = function(inputId)
{
	if(!inputId)
		return false;

	var input = BX(inputId);

	if(!input)
		return false;

	return input.value;
};

__MASaleOrderBarcode.prototype.extractStoreId = function(inputId)
{
	var tmp = inputId.split("_"),
		result = "";

	if(tmp[2])
		result = tmp[2];

	return result;
};

__MASaleOrderBarcode.prototype.makeInputId = function(storeId, barcodeId)
{
	return 'bar_code_'+storeId+'_'+barcodeId;
};


__MASaleOrderBarcode.prototype.check = function(inputId, barcode)
{
	var _this = this;

	this.flagChecking = true;
	this.setBarcodeData(inputId, barcode);

	postData = {
		barcode: barcode,
		sessid: BX.bitrix_sessid(),
		basketItemId: this.basketItemId,
		id: this.orderId,
		storeId: this.extractStoreId(inputId),
		action: "check"
	};

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'json',
		url:       this.ajaxUrl,
		data:      postData,
		onsuccess: function(result)
		{
			if(result && !result.ERROR)
			{
				if(result.RESULT == 'Y')
				{
					_this.setInputStyle(inputId, _this.INPUT_STYLE_OK);
					_this.setBarcodeCheckRes(inputId, 'Y');
				}
				else
				{
					_this.setInputStyle(inputId, _this.INPUT_STYLE_WRONG);
					_this.setBarcodeCheckRes(inputId, 'N');
				}
			}
			else if(result.ERROR)
			{
				app.alert({ text: result.ERROR });
			}
			else
			{
				app.alert({ text: BX.message('SMOB_CHECK_ERROR') });
			}

			BX.onCustomEvent("onBitrixSaleMOBCheckingComplete");
		},
		onfailure: function(){
			app.alert({ text: BX.message('SMOB_CHECK_ERROR') });
			BX.onCustomEvent("onBitrixSaleMOBCheckingComplete");
		}
	});
};

__MASaleOrderBarcode.prototype.setInputStyle = function(inputId, style)
{
	var input = BX(inputId);

	if(!input)
		return;

	if(style == this.INPUT_STYLE_OK)
		input.style.color = "green";
	else if(style == this.INPUT_STYLE_WRONG)
		input.style.color = "red";
	else
		input.style.color = "#8F9396";

};

__MASaleOrderBarcode.prototype.scan = function(inputId)
{
	var _this = this;
	this.isScanning = true;

	app.openBarCodeScanner({
		callback:function(data)
		{
			if (data.text)
			{

				_this.setInput(inputId, data.text);
				_this.check(inputId, data.text);
			}
			else
			{
				app.alert(
					{
						text: BX.message('SMOB_SCAN_ERROR'),
						button:"OK"
				}
				);
			}
		}
	});
};

__MASaleOrderBarcode.prototype.setBarcodes = function(params)
{
	if(this.isScanning)
	{
		this.isScanning = false;
		return;
	}

	this.productData = params;

	var barcodesHtml = '',
		val = '',
		inpId;

	for (var storeId in params.STORES)
	{
		var barcodes = [];

		if(params.STORES[storeId].BARCODE)
			for(var bId in params.STORES[storeId].BARCODE)
				barcodes.push(params.STORES[storeId].BARCODE[bId]);

		if(params.STORES[storeId].QUANTITY > 0)
		{
			var bcCount = 1;

			if(params.BARCODE_MULTI == "Y")
				bcCount = params.STORES[storeId].QUANTITY;

			for (var i = bcCount - 1; i >= 0; i--)
			{
				inpId = this.makeInputId(storeId, i);

				if(barcodes.length > 0)
				{
					val = barcodes.pop();
					this.check(inpId, val);
				}
				else
				{
					val = '';
				}

				barcodesHtml += this.prepareBarcodeHtml(i, params.STORES[storeId], val);
			}
		}
	}

	var barcodesData = BX("smob_data_div");

	if(barcodesData)
		barcodesData.innerHTML = barcodesHtml;
};

__MASaleOrderBarcode.prototype.prepareBarcodeHtml = function(barcodeId, storeData, value)
{
	var result = this.itemTmpl.replace(/##STORE_ID##/g, storeData.STORE_ID);
	result = result.replace(/##STORE_NAME##/g, storeData.STORE_NAME);
	result = result.replace(/##BARCODE_ID##/g, barcodeId);
	result = result.replace(/##VALUE##/g, value);

	return result;
};

__MASaleOrderBarcode.prototype.prepareResult = function()
{
	var stId = '',
		isAllChecksOK = 'Y';

	for(var storeId in this.productData.STORES)
	{
		this.productData["STORES"][storeId]["BARCODE"] = [];
		this.productData["STORES"][storeId]["BARCODE_FOUND"] = [];

		for(var inputId in this.barcodesData)
		{
			stId = this.extractStoreId(inputId);

			if(stId == storeId)
			{
				if(!this.barcodesData[inputId])
					this.barcodesData[inputId] = this.getInput(inputId);

				this.productData["STORES"][storeId]["BARCODE"].push(this.barcodesData[inputId]);
				delete(this.barcodesData[inputId]);

				if(!this.barcodesCheckRes[inputId])
					this.barcodesCheckRes[inputId] = 'N';

				if(isAllChecksOK == 'Y' && this.barcodesCheckRes[inputId] == 'N')
					isAllChecksOK = 'N';

				this.productData["STORES"][storeId]["BARCODE_FOUND"].push(this.barcodesCheckRes[inputId]);
				delete(this.barcodesCheckRes[inputId]);
			}
		}
	}

	this.productData["ALL_CHECKS_RESULT"] = isAllChecksOK;
	return this.productData;
};

__MASaleOrderBarcode.prototype.close = function()
{
	if(app.enableInVersion(8))
		app.closeModalDialog();
	else
		app.closeController({drop: true});
};