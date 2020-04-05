BX.namespace("BX.UI");

BX.UI.InfoHelper =
{
	frameUrlTemplate : '',
	frameNode : null,
	popupLoader : null,
	frameUrl: "",

	init : function(params)
	{
		this.frameUrlTemplate = params.frameUrlTemplate || '';

		BX.bind(window, 'message', BX.proxy(function(event)
		{
			if (!!event.origin && event.origin.indexOf('bitrix') === -1)
			{
				return;
			}

			if (!event.data || typeof(event.data) !== "object")
			{
				return;
			}

			if (event.data.action === "ClosePage")
			{
				this.close();
			}

			if (event.data.action === "openPage")
			{
				window.location.href = this.frameUrl;
			}

			if (event.data.action === "openPageInNewTab")
			{
				window.open(this.frameUrl,'_blank');
			}

		}, this));
	},

	show: function(code)
	{
		if (this.isOpen())
		{
			return;
		}

		if (!code)
		{
			return;
		}

		this.frameUrl = this.frameUrlTemplate.replace(/code/, code);

		if (this.getFrame().src !== this.frameUrl)
		{
			this.getFrame().src = this.frameUrl;
		}

		BX.SidePanel.Instance.open(this.getSliderId(), {
			contentCallback: function(slider) {
				var promise = new BX.Promise();
				promise.fulfill(this.getContent());
				return promise;
			}.bind(this),
			width: 700,
			loader: 'default-loader',
			cacheable: false,
			data: { rightBoundary: 0 },
			events: {
				onCloseComplete: function() {
					BX.UI.InfoHelper.close();
				},
				onLoad: function () {
					BX.UI.InfoHelper.showFrame();
				},
				onClose: function () {
					BX.UI.InfoHelper.frameNode.contentWindow.postMessage({action: 'onCloseWidget'}, '*');
				}
			}
		});
	},

	close: function()
	{
		var slider = this.getSlider();
		if (slider)
		{
			slider.close();
		}
	},

	getContent: function()
	{
		if (this.content)
		{
			return this.content;
		}

		this.content = BX.create('div', {
			attrs: {
				className: 'info-helper-container',
				id: "info-helper-container"
			},
			children: [
				this.getLoader(),
				this.getFrame()
			]
		});
		return this.content;
	},

	getFrame: function()
	{
		if (this.frameNode)
		{
			return this.frameNode;
		}

		this.frameNode = BX.create('iframe', {
			attrs: {
				className: 'info-helper-panel-iframe',
				src: "about:blank"
			}
		});

		return this.frameNode;
	},

	showFrame: function()
	{
		setTimeout(function(){
			this.getFrame().classList.add("info-helper-panel-iframe-show");
		}.bind(this), 600);
	},

	getLoader: function()
	{
		if (this.popupLoader)
		{
			return this.popupLoader;
		}

		var loader = new BX.Loader({
			target: BX("info-helper-container"),
			size: 100
		});

		loader.show();
		this.popupLoader = loader.data.container;

		return this.popupLoader;
	},

	getSliderId: function()
	{
		return "ui:info_helper";
	},

	getSlider: function()
	{
		return BX.SidePanel.Instance.getSlider(this.getSliderId());
	},

	isOpen: function()
	{
		return this.getSlider() && this.getSlider().isOpen();
	}
};
