(function(window){
	// Inspired by BitrixSGFilterDestinationSelectorManager
	var CalendarFilterUserSelectorManager = {
	controls: {},

	onSelect: function(item, type, search, bUndeleted, name, state)
	{
		BX.SocNetLogDestination.obItemsSelected[name] = {};
		BX.SocNetLogDestination.obItemsSelected[name][item.id] = type;

		var control = CalendarFilterUserSelectorManager.controls[name];
		if(control)
		{
			control.setData(BX.util.htmlspecialcharsback(item.name), item.id);
			control.getLabelNode().value = '';
			control.getLabelNode().blur();

			if (BX.SocNetLogDestination.popupWindow != null)
			{
				BX.SocNetLogDestination.popupWindow.close();
			}
			if (BX.SocNetLogDestination.popupSearchWindow != null)
			{
				BX.SocNetLogDestination.popupSearchWindow.close();
			}
		}
	}
};

// Inspired by BitrixSGFilterDestinationSelector
var CalendarFilterUserSelector = function ()
{
	this.id = "";
	this.filterId = "";
	this.settings = {};
	this.fieldId = "";
	this.control = null;
	this.inited = null;
};

	CalendarFilterUserSelector.create = function(id, settings)
	{
		var self = new CalendarFilterUserSelector(id, settings);
		self.initialize(id, settings);
		BX.onCustomEvent(window, 'BX.SonetGroupList.Filter:create', [ id ]);
		return self;
	};

	CalendarFilterUserSelector.prototype.getSetting = function(name, defaultval)
	{
		return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultval;
	};

	CalendarFilterUserSelector.prototype.getSearchInput = function()
	{
		return this.control ? this.control.getLabelNode() : null;
	};

	CalendarFilterUserSelector.prototype.initialize = function(id, settings)
	{
		this.id = id;
		this.settings = settings ? settings : {};
		this.fieldId = this.getSetting("fieldId", "");
		this.filterId = this.getSetting("filterId", "");
		this.inited = false;
		this.opened = null;
		this.closed = null;

		var initialValue = this.getSetting("initialValue",false);
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
		BX.addCustomEvent(window, "BX.Main.Selector:beforeInitDialog", BX.delegate(this.onBeforeInitDialog, this));
		BX.addCustomEvent(window, "BX.SocNetLogDestination:onBeforeSwitchTabFocus", BX.delegate(this.onBeforeSwitchTabFocus, this));
		BX.addCustomEvent(window, "BX.SocNetLogDestination:onBeforeSelectItemFocus", BX.delegate(this.onBeforeSelectItemFocus, this));
		BX.addCustomEvent(window, "BX.Main.Filter:customEntityRemove", BX.delegate(this.onCustomEntityRemove, this));
	};

	CalendarFilterUserSelector.prototype.open = function()
	{
		var name = this.id;

		if (!this.inited)
		{
			var input = this.getSearchInput();
			input.id = input.name;

			BX.addCustomEvent(window, "BX.Main.Selector:afterInitDialog", BX.delegate(function(params) {
				if (
					typeof params.id != 'undefined'
					|| params.id != this.id
				)
				{
					return;
				}

				this.opened = true;
				this.closed = false;
			}, this));

			BX.onCustomEvent(window, 'BX.SonetGroupList.Filter:openInit', [ {
				id: this.id,
				inputId: input.id,
				containerId: input.id
			} ]);

			this.inited = true;
		}
		else
		{
			BX.onCustomEvent(window, 'BX.SonetGroupList.Filter:open', [ {
				id: this.id,
				bindNode: this.control.getField()
			} ]);

			this.opened = true;
			this.closed = false;
		}
	};

	CalendarFilterUserSelector.prototype.close = function()
	{
		BX.SocNetLogDestination.closeDialog();
		this.opened = false;
		this.closed = true;
	};

	CalendarFilterUserSelector.prototype.onCustomEntitySelectorOpen = function(control)
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
				this.currentUser = { "entityId": current["value"] };
			}

			CalendarFilterUserSelectorManager.controls[this.id] = this.control;

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

	CalendarFilterUserSelector.prototype.onCustomEntitySelectorClose = function(control)
	{
		if(
			this.fieldId === control.getId()
			&& this.inited === true
		)
		{
			this.control = null;
			this.close();
		}
	};

	CalendarFilterUserSelector.prototype.onGetStopBlur = function(event, result)
	{
		if (BX.findParent(event.target, { className: 'bx-lm-box'}))
		{
			result.stopBlur = true;
		}
	};

	CalendarFilterUserSelector.prototype.onCustomEntityRemove = function(control)
	{
		if(this.fieldId === control.getId())
		{
			if (
				typeof control.hiddenInput != 'undefined'
				&& typeof control.hiddenInput.value != 'undefined'
				&& typeof BX.SocNetLogDestination.obItemsSelected[this.id] != 'undefined'
				&& typeof BX.SocNetLogDestination.obItemsSelected[this.id][control.hiddenInput.value] != 'undefined'
			)
			{
				delete BX.SocNetLogDestination.obItemsSelected[this.id][control.hiddenInput.value];
			}
		}
	};

	CalendarFilterUserSelector.prototype.onBeforeSwitchTabFocus = function(ob)
	{
		if(this.id === ob.id)
		{
			ob.blockFocus = true;
		}
	};

	CalendarFilterUserSelector.prototype.onBeforeSelectItemFocus = function(ob)
	{
		if(this.id === ob.id)
		{
			ob.blockFocus = true;
		}
	};

	CalendarFilterUserSelector.prototype.onBeforeInitDialog = function(params)
	{
		if (
			typeof params.id == 'undefined'
			|| params.id != this.id
		)
		{
			return;
		}

		if (this.closed)
		{
			params.blockInit = true;
		}
	};

	window.CalendarFilterUserSelectorManager = CalendarFilterUserSelectorManager;
	window.CalendarFilterUserSelector = CalendarFilterUserSelector;

}(window));