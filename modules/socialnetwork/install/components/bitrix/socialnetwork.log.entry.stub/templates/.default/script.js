(function(){
	var BX = window.BX;
	if (BX.SocialnetworkLogEntryStub)
	{
		return;
	}

	BX.SocialnetworkLogEntryStub = function() {
		this.id = "";
		this.serviceUrl = "";
		this.redirectUrl = "";
		this.host = "";
	};
	BX.SocialnetworkLogEntryStub.items = {};
	BX.SocialnetworkLogEntryStub.create = function(id, settings)
	{
		var self = new BX.SocialnetworkLogEntryStub();
		self.initialize(id, settings);
		this.items[self.id] = self;
		return self;
	};

	BX.SocialnetworkLogEntryStub.prototype = {

		initialize: function (id, settings)
		{
			this.id = id;
			this.redirectUrl = (BX.type.isNotEmptyString(settings.redirectUrl) ? settings.redirectUrl : "");
			this.serviceUrl = (BX.type.isNotEmptyString(settings.serviceUrl) ? settings.serviceUrl : "");
			this.host = (BX.type.isNotEmptyString(settings.host) ? settings.host : "");
		},

		execute: function()
		{
			BX.ajax({
				method: "POST",
				dataType: "html",
				url: this.serviceUrl,
				data: {
					host: this.host,
					action: 'tariff',
				},
				onsuccess: function() {
					document.location.href = this.redirectUrl;
				}.bind(this)
			});
		}
	};
})();

