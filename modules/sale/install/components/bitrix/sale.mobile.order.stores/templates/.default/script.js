__BitrixSaleMOS = function(params)
{
	//aStoresIds, neededAmount
	for(var key in params)
		this[key] = params[key];
};

__BitrixSaleMOS.prototype.makeStoreInpId = function(storeId)
{
	return "STORE_"+storeId+"_QUANTITY";
};

__BitrixSaleMOS.prototype.checkQuantities = function(qByStoresId)
{
	var summ = 0;

	for (var storeId in qByStoresId)
		summ += qByStoresId[storeId];

	return summ == this.neededAmount;
};

__BitrixSaleMOS.prototype.getQuantities = function()
{
	var value = '',
		input,
		aResult = {};

	for (var i = this.aStoresIds.length - 1; i >= 0; i--)
	{
		input = BX(this.makeStoreInpId(this.aStoresIds[i]));

		if(!input || !input.value)
			continue;

		value = parseFloat(input.value);
		aResult[this.aStoresIds[i]] = isNaN(value) ? 0 : value;
	}

	return aResult;
};

__BitrixSaleMOS.prototype.setQuantities = function(qByStore)
{
	for (var i = this.aStoresIds.length - 1; i >= 0; i--)
	{
		var input = BX(this.makeStoreInpId(this.aStoresIds[i]));

		if(!input || !input.value || !qByStore[this.aStoresIds[i]] || !qByStore[this.aStoresIds[i]]["QUANTITY"])
			continue;


		input.value = qByStore[this.aStoresIds[i]]["QUANTITY"];
	}

	return true;
};

__BitrixSaleMOS.prototype.close = function()
{
	if(app.enableInVersion(8))
		app.closeModalDialog();
	else
		app.closeController({drop: true});
};