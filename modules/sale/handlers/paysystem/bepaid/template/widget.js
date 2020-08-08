/**
 * Class BX.Sale.BePaid
 */
(function() {
	'use strict';

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.BePaid)
		return;

	BX.Sale.BePaid = {
		init: function(params)
		{
			this.params = params.params;
			this.buttonPayNode = BX(params.buttonPayId);
			this.bindEvents();
		},

		bindEvents: function()
		{
			BX.bind(this.buttonPayNode, 'click', BX.proxy(this.createWidget, this));
		},

		createWidget: function()
		{
			new BeGateway(this.params).createWidget();
		},
	}
})();
