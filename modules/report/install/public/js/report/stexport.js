BX.namespace("BX.Report");

//region Step-by-step export manager
if(typeof BX.Report.StExportManager === "undefined")
{
	BX.Report.StExportManager = function()
	{
		this._id = "";
		this._settings = {};
		this._processDialog = null;
		this._siteId = "";
		this._entityType = "";
		this._sToken = "";
		this._cToken = "";
		this._token = "";
		this._serviceUrl = "";
		this._initialOptions = {};
	};

	BX.Report.StExportManager.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._siteId = this.getSetting("siteId", "");
			if (!BX.type.isNotEmptyString(this._siteId))
				throw "BX.Report.StExportManager: parameter 'siteId' is not found.";
			this._entityType = this.getSetting("entityType", "");
			if (!BX.type.isNotEmptyString(this._entityType))
				throw "BX.Report.StExportManager: parameter 'entityType' is not found.";
			this._sToken = this.getSetting("sToken", "");
			if (!BX.type.isNotEmptyString(this._sToken))
				throw "BX.Report.StExportManager: parameter 'sToken' is not found.";
			this._serviceUrl = this.getSetting("serviceUrl", "");
			if (!BX.type.isNotEmptyString(this._serviceUrl))
				throw "BX.Report.StExportManager: parameter 'serviceUrl' is not found.";
			this._initialOptions = this.getSetting("initialOptions", {});
		},
		getId: function()
		{
			return this._id;
		},
		getSetting: function(name, defaultval)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultval;
		},
		startExport: function (exportType) {
			if (!BX.type.isNotEmptyString(exportType))
				throw "BX.Report.StExportManager: parameter 'exportType' has invalid value.";

			this._cToken = "c" + Date.now();
			this._token = this._sToken + this._cToken;
			var params = {
				"SITE_ID": this._siteId,
				"PROCESS_TOKEN": this._token,
				"ENTITY_TYPE_NAME": this._entityType,
				"EXPORT_TYPE": exportType,
				"COMPONENT_PARAMS": this.getSetting("componentParams", {})
			};
			var exportTypeMsgSuffix = exportType.charAt(0).toUpperCase() + exportType.slice(1);
			this._processDialog = BX.Report.LongRunningProcessDialog.create(
				this._id + "_LrpDlg",
				{
					serviceUrl: this._serviceUrl,
					action: "STEXPORT",
					params: params,
					initialOptions: this._initialOptions,
					title: this.getMessage("stExport" + exportTypeMsgSuffix + "DlgTitle"),
					summary: this.getMessage("stExport" + exportTypeMsgSuffix + "DlgSummary"),
					isSummaryHtml: false
				}
			);
			this._processDialog.show();
		},
		destroy: function ()
		{
			this._id = "";
			this._settings = {};
			this._processDialog = null;
			this._siteId = "";
			this._entityType = "";
			this._sToken = "";
			this._cToken = "";
			this._token = "";
			this._serviceUrl = "";
			this._initialOptions = {};
		}
	};

	BX.Report.StExportManager.prototype.getMessage = function(name)
	{
		var message = name;
		var messages = this.getSetting("messages", null);
		if (messages !== null && typeof(messages) === "object" && messages.hasOwnProperty(name))
		{
			message =  messages[name];
		}
		else
		{
			messages = BX.Report.StExportManager.messages;
			if (messages !== null && typeof(messages) === "object" && messages.hasOwnProperty(name))
			{
				message =  messages[name];
			}
		}
		return message;
	};

	if(typeof(BX.Report.StExportManager.messages) === "undefined")
	{
		BX.Report.StExportManager.messages = {};
	}

	if(typeof(BX.Report.StExportManager.items) === "undefined")
	{
		BX.Report.StExportManager.items = {};
	}

	BX.Report.StExportManager.create = function(id, settings)
	{
		var self = new BX.Report.StExportManager();
		self.initialize(id, settings);
		BX.Report.StExportManager.items[id] = self;
		return self;
	};

	BX.Report.StExportManager.delete = function(id)
	{
		if (BX.Report.StExportManager.items.hasOwnProperty(id))
		{
			BX.Report.StExportManager.items[id].destroy();
			delete BX.Report.StExportManager.items[id];
		}
	};
}
//endregion Step-by-step export manager