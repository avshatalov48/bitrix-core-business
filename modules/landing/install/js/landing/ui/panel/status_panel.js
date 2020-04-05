;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var addClass = BX.Landing.Utils.addClass;

	var statusInterval;
	var lastUpdate = new Date();
	var format = [
		["s" , "sago"],
		["i", "iago"],
		["H", "Hago"],
		["d", "dago"],
		["m100", "mago"],
		["m", "mago"]
	];

	BX.Landing.UI.Panel.StatusPanel = function(options)
	{
		BX.Landing.UI.Panel.BasePanel.apply(this, arguments);
		addClass(this.layout, "landing-ui-panel-status");

		if (!!document.body.querySelector('.landing-edit-mode'))
		{
			document.body.appendChild(this.layout);
		}

		this.runInterval();
		this.updateTime();
	};

	BX.Landing.UI.Panel.StatusPanel.setLastModified = function(timestamp)
	{
		lastUpdate = new Date(timestamp * 1000);
	};

	BX.Landing.UI.Panel.StatusPanel.getInstance = function()
	{
		return (
			BX.Landing.UI.Panel.StatusPanel.instance ||
			(BX.Landing.UI.Panel.StatusPanel.instance = new BX.Landing.UI.Panel.StatusPanel())
		);
	};

	BX.Landing.UI.Panel.StatusPanel.prototype = {
		constructor: BX.Landing.UI.Panel.StatusPanel,
		__proto__: BX.Landing.UI.Panel.BasePanel.prototype,

		runInterval: function()
		{
			clearInterval(statusInterval);
			statusInterval = setInterval(this.updateTime.bind(this), (10 * 1000));
		},

		updateTime: function()
		{
			this.setContent([
				BX.message("LANDING_PAGE_STATUS_UPDATED"),
				BX.date.format(format, lastUpdate.getTime() / 1000, (new Date()).getTime() / 1000)
			].join(" "));
		},

		update: function()
		{
			this.show()
				.then(function() {
					this.runInterval();
					lastUpdate = new Date();
					this.setContent(BX.message("LANDING_PAGE_STATUS_UPDATED_NOW"));
				}.bind(this));
		}
	};
})();