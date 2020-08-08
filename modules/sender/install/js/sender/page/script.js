;(function (window)
{
	BX.namespace('BX.Sender');
	if (BX.Sender.Page)
	{
		return;
	}

	function Page()
	{
	}
	Page.prototype.initButtonPanel = function ()
	{
		var prefix = 'sender-ui-button-panel-';
		var saveButton = BX(prefix + 'save');
		var cancelButton = BX(prefix + 'cancel');

		BX.bind(saveButton, 'click', function () {
			BX.addClass(saveButton, 'ui-btn-wait');
			setTimeout(function () {
				saveButton.disabled = true;
			}, 100);
		});

		if (this.slider.isInSlider())
		{
			var self = this;
			BX.bind(cancelButton, 'click', function (e) {
				self.slider.close();
				e.preventDefault();
				e.stopPropagation();
			});
		}
	};
	Page.prototype.initButtons = function ()
	{
		var buttonAdd = BX('SENDER_BUTTON_ADD');
		if (buttonAdd)
		{
			this.slider.bindOpen(buttonAdd);
		}

		this.initButtonPanel();
	};
	Page.prototype.reloadGrid = function (id)
	{
		if (!BX.Main || !BX.Main.gridManager)
		{
			return;
		}

		if (!id && BX.Main.gridManager.data)
		{
			var grids = BX.Main.gridManager.data;
			id = grids.length > 0 ? grids[0].id : null;
		}

		if(!id)
		{
			return;
		}

		var grid = BX.Main.gridManager.getById(id);
		if (!grid || !BX.height(grid.instance.getTable()))
		{
			return;
		}
		grid.instance.reload();
	};
	Page.prototype.changeGridLoaderShowing = function (id, isShow)
	{
		var grid = BX.Main.gridManager.getById(id);
		if (!grid || !grid.instance)
		{
			return;
		}

		isShow ? grid.instance.tableFade() : grid.instance.tableUnfade();
	};
	Page.prototype.open = function (uri, callback, parameters)
	{
		this.slider.open(uri, callback, parameters);
	};
	Page.prototype.slider = {

		init: function (params)
		{
			if (!this.isSupported())
			{
				return;
			}
			if (
				typeof BX.Bitrix24 !== "undefined" &&
				typeof BX.Bitrix24.PageSlider !== "undefined"
			)
			{
				BX.Bitrix24.PageSlider.bindAnchors({
					rules: [
						{
							condition: params.condition,
							loader: params.loader,
							stopParameters: [],
							options: params.options
						}
					],
				});
			}
		},
		getSlider: function ()
		{
			if (!this.isSupported())
			{
				return null;
			}

			return BX.SidePanel.Instance;
		},
		isInSlider: function ()
		{
			return (top !== window && top.BX && this.isSupported());
		},
		isSupported: function ()
		{
			return ((BX.SidePanel && BX.SidePanel.Instance) || (this.isInSlider()));
		},
		bindOpen: function (element)
		{
			if (!this.isSupported())
			{
				return;
			}

			BX.bind(element, 'click', this.openHref.bind(this, element));
		},
		openHref: function (a, e)
		{
			if (!this.isSupported())
			{
				return;
			}

			e.preventDefault();
			e.stopPropagation();

			var href = a.getAttribute('href');
			if (!href)
			{
				return;
			}

			this.open(href);
		},
		open: function (uri, reloadAfterClosing, parameters)
		{
			if (!this.isSupported())
			{
				window.location.href = uri;
				return;
			}

			parameters = parameters || {};
			if (!BX.type.isBoolean(parameters.cacheable))
			{
				parameters.cacheable = false;
			}

			this.getSlider().open(uri, parameters);
			if (reloadAfterClosing)
			{
				if (!this.getSlider().iframe)
				{
					return;
				}

				BX.addCustomEvent(
					this.getSlider().iframe.contentWindow,
					"BX.Bitrix24.PageSlider:onClose",
					function ()
					{
						if (BX.type.isBoolean(reloadAfterClosing))
						{
							window.location.reload();
						}
						else if (BX.type.isFunction(reloadAfterClosing))
						{
							reloadAfterClosing();
						}
					}
				);
			}
			else
			{
				BX.addCustomEvent(
					BX.SidePanel.Instance.getTopSlider(),
					"SidePanel.Slider:onReload",
					function () {
						BX.Sender.Page.reloadGrid();
					}
				);
			}
		},
		close: function ()
		{
			if (!this.isSupported())
			{
				return null;
			}

			this.getSlider().close();
		}
	};

	BX.Sender.Page = new Page();
	BX.Sender.Page.slider.init({
		condition: ["/marketing/config/role/"],
		options: {
			cacheable: false,
			events: {
				onOpen: function () {
					var manager = BX.Main.interfaceButtonsManager;
					for (var menuId in manager.data)
					{
						manager.data[menuId].closeSubmenu();
					}
				}
			}
		}
	});


})(window);