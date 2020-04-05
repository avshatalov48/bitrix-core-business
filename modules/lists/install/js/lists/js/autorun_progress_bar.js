BX.namespace("BX.Lists");
if(typeof(BX.Lists.AutoRunProcessState) == "undefined")
{
	BX.Lists.AutoRunProcessState =
		{
			intermediate: 0,
			running: 1,
			completed: 2,
			stoped: 3,
			error: 4
		};
}

if (typeof(BX.Lists.AutorunProcessManager) != "undefined") {
} else {
	BX.Lists.AutorunProcessManager = function () {
		this._id = "";
		this._settings = {};
		this._serviceUrl = "";
		this._dataForAjax = null;

		this._container = null;
		this._panel = null;
		this._hasLayout = false;

		this._state = BX.Lists.AutoRunProcessState.intermediate;
		this._processedItemCount = 0;
		this._totalItemCount = 0;

		this._error = "";
	};
	BX.Lists.AutorunProcessManager.prototype =
		{
			initialize: function (id, settings) {
				this._id = BX.type.isNotEmptyString(id) ? id : "lists_lrp_mgr_" + Math.random().toString().substring(2);
				this._settings = settings ? settings : {};
				this._serviceUrl = this.getSetting("serviceUrl", "");
				if (!BX.type.isNotEmptyString(this._serviceUrl)) {
					throw "AutorunProcessManager. Could not find 'serviceUrl' parameter in settings.";
				}
				this._ajaxAction = this.getSetting("ajaxAction", "");
				if (!BX.type.isNotEmptyString(this._ajaxAction)) {
					throw "AutorunProcessManager. Could not find 'ajaxAction' parameter in settings.";
				}
				this._dataForAjax = this.getSetting("dataForAjax", "");
				this._container = BX(this.getSetting("container"));
				if (!BX.type.isElementNode(this._container)) {
					throw "AutorunProcessManager: Could not find container.";
				}
				if (!!this.getSetting("enableLayout", false)) {
					this.layout();
				}
			},
			getId: function () {
				return this._id;
			},
			getSetting: function (name, defaultval) {
				return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultval;
			},
			getMessage: function (name) {
				var m = BX.Lists.AutorunProcessManager.messages;
				return m.hasOwnProperty(name) ? m[name] : name;
			},
			isHidden: function () {
				return !this._hasLayout || this._panel.isHidden();
			},
			show: function () {
				if (this._hasLayout) {
					this._panel.show();
				}
			},
			hide: function () {
				if (this._hasLayout) {
					this._panel.hide();
				}
			},
			layout: function () {
				if (this._hasLayout) {
					return;
				}

				if (!this._panel) {
					this._panel = BX.Lists.AutorunProcessPanel.create(
						this._id,
						{
							manager: this,
							container: this._container,
							title: this.getMessage("title"),
							stateTemplate: this.getMessage("stateTemplate")
						}
					);
				}
				this._panel.layout();
				this._hasLayout = true;
			},
			clearLayout: function () {
				if (!this._hasLayout) {
					return;
				}

				this._panel.clearLayout();
				this._hasLayout = false;
			},
			refresh: function () {
				if (!this._hasLayout) {
					this.layout();
				}

				if (this._panel.isHidden()) {
					this._panel.show();
				}
				this._panel.onManagerStateChange();
			},
			getState: function () {
				return this._state;
			},
			getProcessedItemCount: function () {
				return this._processedItemCount;
			},
			getTotalItemCount: function () {
				return this._totalItemCount;
			},
			getError: function () {
				return this._error;
			},
			run: function () {
				this.startRequest();
			},
			runAfter: function (timeout) {
				window.setTimeout(BX.delegate(this.run, this), timeout);
			},
			startRequest: function () {
				if (this._requestIsRunning) {
					return;
				}
				this._requestIsRunning = true;

				this._state = BX.Lists.AutoRunProcessState.running;

				var data = {};
				if (this._dataForAjax)
				{
					data = this._dataForAjax;
				}

				BX.Lists.ajax(
					{
						url: BX.Lists.addToLinkParam(this._serviceUrl, 'action', this._ajaxAction),
						method: "POST",
						dataType: "json",
						data: data,
						onsuccess: BX.delegate(this.onRequestSuccsess, this),
						onfailure: BX.delegate(this.onRequestFailure, this)
					}
				);
			},
			onRequestSuccsess: function (result) {
				this._requestIsRunning = false;

				var status = BX.type.isNotEmptyString(result["status"]) ? result["status"] : "";
				if (status === "error") {
					this._state = BX.Lists.AutoRunProcessState.error;
				}
				else if (status === "completed") {
					this._state = BX.Lists.AutoRunProcessState.completed;
				}

				if (this._state === BX.Lists.AutoRunProcessState.error) {
					this._error = BX.type.isNotEmptyString(result["error"]) ? result["error"] : this.getMessage("requestError");
				}
				else {
					this._processedItemCount = result["processedItems"] ? parseInt(result["processedItems"]) : 0;
					this._totalItemCount = result["totalItems"] ? parseInt(result["totalItems"]) : 0;
				}

				if (this._state === BX.Lists.AutoRunProcessState.completed) {
					this.hide();
				}
				else {
					this.refresh();
					if (this._state === BX.Lists.AutoRunProcessState.running) {
						window.setTimeout(BX.delegate(this.startRequest, this), 4000);
					}
				}

				BX.onCustomEvent(this, 'ON_AUTORUN_PROCESS_STATE_CHANGE', [this]);
			},
			onRequestFailure: function (result) {
				this._requestIsRunning = false;

				this._state = BX.Lists.AutoRunProcessState.error;
				this._error = this.getMessage("requestError");

				this.refresh();
				BX.onCustomEvent(this, 'ON_AUTORUN_PROCESS_STATE_CHANGE', [this]);
			}
		};
	if (typeof(BX.Lists.AutorunProcessManager.messages) == "undefined") {
		BX.Lists.AutorunProcessManager.messages = {};
	}
	BX.Lists.AutorunProcessManager.items = {};
	BX.Lists.AutorunProcessManager.create = function (id, settings) {
		var self = new BX.Lists.AutorunProcessManager();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}

if(typeof(BX.Lists.AutorunProcessPanel) == "undefined")
{
	BX.Lists.AutorunProcessPanel = function()
	{
		this._id = "";
		this._settings = {};

		this._manager = null;
		this._container = null;
		this._wrapper = null;
		this._stateNode = null;
		this._progressNode = null;
		this._hasLayout = false;
		this._isHidden = false;
	};
	BX.Lists.AutorunProcessPanel.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = id;
				this._settings = settings ? settings : {};

				this._container = BX(this.getSetting("container"));
				if(!BX.type.isElementNode(this._container))
				{
					throw "AutorunProcessPanel: Could not find container.";
				}

				this._manager = this.getSetting("manager");
				if(!this._manager)
				{
					throw "AutorunProcessPanel: Could not find manager.";
				}

				this._isHidden = this.getSetting("isHidden", false);
			},
			getId: function()
			{
				return this._id;
			},
			getSetting: function (name, defaultval)
			{
				return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultval;
			},
			layout: function()
			{
				if(this._hasLayout)
				{
					return;
				}

				this._wrapper = BX.create("DIV", { attrs: { className: "lists-view-progress" } });
				BX.addClass(this._wrapper, this._isHidden ? "lists-view-progress-hide" : "lists-view-progress-show");

				this._container.appendChild(this._wrapper);

				this._wrapper.appendChild(
					BX.create("DIV",
						{
							attrs: { className: "lists-view-progress-info" },
							text: this.getSetting("title", "Please wait...")
						}
					)
				);

				this._progressNode = BX.create("DIV", { attrs: { className: "lists-view-progress-bar-line" } });
				this._stateNode = BX.create("DIV", { attrs: { className: "lists-view-progress-steps" } });
				this._wrapper.appendChild(
					BX.create("DIV",
						{
							attrs: { className: "lists-view-progress-inner" },
							children:
								[
									BX.create("DIV",
										{
											attrs: { className: "lists-view-progress-bar" },
											children: [ this._progressNode ]
										}
									),
									this._stateNode
								]
						}
					)
				);

				this._hasLayout = true;
			},
			isHidden: function()
			{
				return this._isHidden;
			},
			show: function()
			{
				if(!this._isHidden)
				{
					return;
				}

				if(!this._hasLayout)
				{
					return;
				}

				BX.removeClass(this._wrapper, "lists-view-progress-hide");
				BX.addClass(this._wrapper, "lists-view-progress-show");

				this._isHidden = false;
			},
			hide: function()
			{
				if(this._isHidden)
				{
					return;
				}

				if(!this._hasLayout)
				{
					return;
				}

				BX.removeClass(this._wrapper, "lists-view-progress-show");
				BX.addClass(this._wrapper, "lists-view-progress-hide");

				this._isHidden = true;
			},
			clearLayout: function()
			{
				if(!this._hasLayout)
				{
					return;
				}

				BX.remove(this._wrapper);
				this._wrapper = this._stateNode = null;

				this._hasLayout = false;
			},
			onManagerStateChange: function()
			{
				if(!this._hasLayout)
				{
					return;
				}
				var state = this._manager.getState();
				if(state !== BX.Lists.AutoRunProcessState.error)
				{
					var processed = this._manager.getProcessedItemCount();
					var total = this._manager.getTotalItemCount();

					var progress = 0;
					if(total !== 0)
					{
						progress = Math.floor((processed / total) * 100);
						var offset = progress % 5;
						if(offset !== 0)
						{
							progress -= offset;
						}
					}
					this._stateNode.innerHTML = (processed > 0 && total > 0)
						? this.getSetting("stateTemplate", "#processed# from #total#").
						replace('#processed#', processed).replace('#total#', total) : "";
					this._progressNode.className = "lists-view-progress-bar-line";
					if(progress > 0)
					{
						this._progressNode.className += " lists-view-progress-line-" + progress.toString();
					}
				}
			}
		};
	BX.Lists.AutorunProcessPanel.items = {};
	BX.Lists.AutorunProcessPanel.isExists = function(id)
	{
		return this.items.hasOwnProperty(id);
	};

	BX.Lists.AutorunProcessPanel.create = function(id, settings)
	{
		var self = new BX.Lists.AutorunProcessPanel();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	}
}
