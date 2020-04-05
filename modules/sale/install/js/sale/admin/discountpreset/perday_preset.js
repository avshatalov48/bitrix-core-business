BX.namespace("BX.Sale.Admin.DiscountPreset");

BX.Sale.Admin.DiscountPreset.PerDay = (function(){

	var PerDay = function (parameters){
		BX.Sale.Admin.DiscountPreset.SelectProduct.call(this, parameters);
	};

	PerDay.prototype = Object.create(BX.Sale.Admin.DiscountPreset.SelectProduct.prototype);

	return PerDay;
})();
