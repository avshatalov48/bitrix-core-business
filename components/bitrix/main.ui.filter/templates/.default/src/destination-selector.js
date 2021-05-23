;(function() {
	'use strict';

	BX.namespace('BX.Filter');

	BX.Filter.DestinationSelectorManager = {

		fields: [],
		controls: {},

		onSelect: function(isNumeric, prefix, params)
		{
			if (
				!BX.type.isNotEmptyObject(params)
				|| !BX.type.isNotEmptyObject(params.item)
				|| !BX.type.isNotEmptyString(params.selectorId)
			)
			{
				return;
			}

			var
				selectorId = params.selectorId,
				item = params.item;

			var control = BX.Filter.DestinationSelectorManager.controls[selectorId];
			if (control)
			{
				var value = item.id;

				if (
					BX.type.isNotEmptyString(isNumeric)
					&& isNumeric == 'Y'
					&& BX.type.isNotEmptyString(prefix)
				)
				{
					var re = new RegExp('^' + prefix + '(\\d+)$');
					var found = value.match(re);
					if (BX.type.isArray(found))
					{
						value = found[1];
					}
				}
				else
				{
					var eventResult = {};
					BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:convert', [ {
						selectorId: selectorId,
						value: value
					}, eventResult ]);

					if (BX.type.isNotEmptyString(eventResult.value))
					{
						value = eventResult.value;
					}
				}

				control.setData(BX.util.htmlspecialcharsback(item.name), value);
				control.getLabelNode().value = '';
				control.getLabelNode().blur();
			}
		},

		onDialogOpen: function(params)
		{
			if (
				typeof params == 'undefined'
				|| !BX.type.isNotEmptyString(params.selectorId)
			)
			{
				return;
			}

			var selectorId = params.selectorId;

			var item = BX.Filter.DestinationSelector.items[selectorId];
			if(item)
			{
				item.onDialogOpen();
			}
		},

		onDialogClose: function(params)
		{
			if (
				typeof params == 'undefined'
				|| !BX.type.isNotEmptyString(params.selectorId)
			)
			{
				return;
			}

			var selectorId = params.selectorId;

			var item = BX.Filter.DestinationSelector.items[selectorId];
			if(item)
			{
				item.onDialogClose();
			}
		}
	};

	BX.Filter.DestinationSelector = function ()
	{
		this.id = "";
		this.filterId = "";
		this.settings = {};
		this.fieldId = "";
		this.control = null;
		this.inited = null;
	};

	BX.Filter.DestinationSelector.items = {};

	BX.Filter.DestinationSelector.create = function(id, settings)
	{
		if (typeof this.items[id] != 'undefined')
		{
			return this.items[id];
		}

		var self = new BX.Filter.DestinationSelector(id, settings);
		self.initialize(id, settings);
		this.items[id] = self;
		BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:create', [ id ]);
		return self;
	};

	BX.Filter.DestinationSelector.prototype.getSetting = function(name, defaultval)
	{
		return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultval;
	};

	BX.Filter.DestinationSelector.prototype.getSearchInput = function()
	{
		return this.control ? this.control.getLabelNode() : null;
	};

	BX.Filter.DestinationSelector.prototype.initialize = function(id, settings)
	{
		this.id = id;
		this.settings = settings ? settings : {};
		this.fieldId = this.getSetting("fieldId", "");
		this.filterId = this.getSetting("filterId", "");
		this.inited = false;
		this.opened = null;

		var initialValue = this.getSetting("initialValue", false);
		if (!!initialValue)
		{
			var initialSettings = {};
			initialSettings[this.fieldId] = initialValue.itemId;
			initialSettings[this.fieldId + '_label'] = initialValue.itemName;

			BX.Main.filterManager.getById(this.filterId).getApi().setFields(initialSettings);
		}
		BX.addCustomEvent(window, "BX.Main.Filter:customEntityFocus", BX.delegate(this.onCustomEntitySelectorOpen, this));
		BX.addCustomEvent(window, "BX.Main.Filter:customEntityBlur", BX.delegate(this.onCustomEntitySelectorClose, this));
		BX.addCustomEvent(window, "BX.Main.Filter:onGetStopBlur", BX.delegate(this.onGetStopBlur, this));
		BX.addCustomEvent(window, "BX.Main.SelectorV2:beforeInitDialog", BX.delegate(this.onBeforeInitDialog, this));
		BX.addCustomEvent(window, "BX.Main.Filter:customEntityRemove", BX.delegate(this.onCustomEntityRemove, this));
	};

	BX.Filter.DestinationSelector.prototype.open = function()
	{
		var name = this.id;

		if (!this.inited)
		{
			var input = this.getSearchInput();
			input.id = input.name;

			BX.addCustomEvent(window, "BX.Main.SelectorV2:afterInitDialog", BX.delegate(function(params) {
				if (
					typeof params.id != 'undefined'
					|| params.id != this.id
				)
				{
					return;
				}

				this.opened = true;
			}, this));

			BX.addCustomEvent(window, "BX.UI.SelectorManager:onCreate", BX.delegate(function(selectorId) {
				if (
					!BX.type.isNotEmptyString(selectorId)
					|| selectorId != this.id
				)
				{
					return;
				}

				BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:setSelected', [ {
					selectorId: selectorId,
					current: this.control.getCurrentValues()
				} ]);

			}, this));

			BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:openInit', [ {
				id: this.id,
				inputId: input.id,
				containerId: input.id
			} ]);
		}
		else
		{
			var currentValue = {};
			currentValue[this.currentUser.entityId] = "users";

			BX.onCustomEvent(window, 'BX.Filter.DestinationSelector:open', [ {
				id: this.id,
				bindNode: this.control.getField(),
				value: currentValue
			} ]);

			this.opened = true;
		}
	};

	BX.Filter.DestinationSelector.prototype.close = function()
	{
		if(typeof(BX.Main.selectorManagerV2.controls[this.id]) !== "undefined")
		{
			BX.Main.selectorManagerV2.controls[this.id].closeDialog();
		}
	};

	BX.Filter.DestinationSelector.prototype.onCustomEntitySelectorOpen = function(control)
	{
		var fieldId = control.getId();

		if(this.fieldId !== fieldId)
		{
			this.control = null;
		}
		else
		{
			this.control = control;

			if(this.control)
			{
				var current = this.control.getCurrentValues();
				this.currentUser = {
					entityId: current["value"]
				};
			}

			BX.Filter.DestinationSelectorManager.controls[this.id] = this.control;

			if (!this.opened)
			{
				this.open();
			}
			else
			{
				this.close();
			}
		}
	};

	BX.Filter.DestinationSelector.prototype.onCustomEntitySelectorClose = function(control)
	{
		if(
			this.fieldId === control.getId()
			&& this.inited === true
			&& this.opened === true
		)
		{
			this.control = null;
			window.setTimeout(BX.delegate(this.close, this), 0);
		}
	};

	BX.Filter.DestinationSelector.prototype.onGetStopBlur = function(event, result)
	{
		if (BX.findParent(event.target, { className: 'bx-lm-box'}))
		{
			result.stopBlur = true;
		}
	};

	BX.Filter.DestinationSelector.prototype.onCustomEntityRemove = function(control)
	{
		if(this.fieldId === control.getId())
		{
			var instance = BX.UI.SelectorManager.instances[control.getId()];
			if (
				instance
				&& typeof control.hiddenInput != 'undefined'
				&& typeof control.hiddenInput.value != 'undefined'
				&& BX.type.isNotEmptyObject(instance.itemsSelected)
				&& typeof instance.itemsSelected[control.hiddenInput.value] != 'undefined'
			)
			{
				delete instance.itemsSelected[control.hiddenInput.value];
			}
		}
	};

	BX.Filter.DestinationSelector.prototype.onBeforeInitDialog = function(params)
	{
		if (
			typeof params.id == 'undefined'
			|| params.id != this.id
		)
		{
			return;
		}

		this.inited = true;

		if (!this.control)
		{
			params.blockInit = true;
		}
	};

	BX.Filter.DestinationSelector.prototype.onDialogOpen = function()
	{
		this.opened = true;
	};

	BX.Filter.DestinationSelector.prototype.onDialogClose = function()
	{
		this.opened = false;
	};

})();