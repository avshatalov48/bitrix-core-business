export class SelectInput
{
	constructor(params)
	{
		this.id = params.id || 'bx-select-input-' + Math.round(Math.random() * 1000000);
		this.values = params.values || false;
		this.input = params.input;

		this.defaultValue = params.defaultValue || '';
		this.openTitle = params.openTitle || '';
		this.className = params.className || '';

		this.onChangeCallback = params.onChangeCallback || null;
		this.zIndex = params.zIndex || 1200;
		this.disabled = params.disabled;
		this.minWidth = params.minWidth || 0;

		this.setValue({
			value: params.value,
			valueIndex: params.valueIndex
		});

		this.curInd = false;

		this.bindEventHandlers();
	}

	bindEventHandlers()
	{
		if (this.onChangeCallback)
		{
			BX.bind(this.input, 'change', this.onChangeCallback);
			BX.bind(this.input, 'keyup', this.onChangeCallback);
		}

		if (this.values)
		{
			BX.bind(this.input, 'click', BX.proxy(this.onClick, this));
			BX.bind(this.input, 'focus', BX.proxy(this.onFocus, this));
			BX.bind(this.input, 'blur', BX.proxy(this.onBlur, this));
			BX.bind(this.input, 'keyup', BX.proxy(this.onKeyup, this));
		}
	}

	setValue(params)
	{
		this.currentValue = {value: params.value};
		this.currentValueIndex = params.valueIndex;
		if (this.currentValueIndex !== undefined && this.values[this.currentValueIndex])
		{
			this.input.value = this.values[this.currentValueIndex].label;
		}
	}

	getInputValue()
	{
		return this.input.value;
	}

	showPopup()
	{
		if (this.shown || this.disabled)
			return;

		let
			ind = 0,
			j = 0,
			menuItems = [],
			i, _this = this;

		for (i = 0; i < this.values.length; i++)
		{
			if (this.values[i].delimiter)
			{
				menuItems.push(this.values[i]);
			}
			else
			{
				if (this.currentValue && this.values[i]
					&& this.values[i].value === this.currentValue.value)
				{
					ind = j;
				}

				menuItems.push({
					id: this.values[i].value,
					text: this.values[i].label,
					onclick: this.values[i].callback || (function (value, label)
					{
						return function ()
						{
							_this.input.value = label;
							_this.popupMenu.close();
							_this.onChange();
						}
					})(this.values[i].value, this.values[i].labelRaw || this.values[i].label)
				});
				j++;
			}
		}

		this.popupMenu = BX.PopupMenu.create(
			this.id,
			this.input,
			menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: -1
			}
		);

		this.popupMenu.popupWindow.setWidth(Math.max(this.input.offsetWidth + 2, this.minWidth));

		let menuContainer = this.popupMenu.layout.menuContainer;
		BX.addClass(this.popupMenu.layout.menuContainer, 'calendar-select-popup');
		this.popupMenu.show();

		let menuItem = this.popupMenu.menuItems[ind];

		if (menuItem && menuItem.layout)
		{
			menuContainer.scrollTop = menuItem.layout.item.offsetTop - menuItem.layout.item.offsetHeight;
		}

		BX.addCustomEvent(this.popupMenu.popupWindow, 'onPopupClose', function()
		{
			BX.PopupMenu.destroy(this.id);
			this.shown = false;
			this.popupMenu = null;
		}.bind(this));

		this.input.select();

		this.shown = true;
	}

	closePopup()
	{
		BX.PopupMenu.destroy(this.id);
		this.popupMenu = null;
		this.shown = false;
	}

	onFocus()
	{
		setTimeout(function(){
			if (!this.shown)
			{
				this.showPopup();
			}
		}.bind(this), 200);
	}

	onClick()
	{
		if (this.shown)
		{
			this.closePopup();
		}
		else
		{
			this.showPopup();
		}
	}

	onBlur()
	{
		setTimeout(BX.delegate(this.closePopup, this), 200);
	}

	onKeyup()
	{
		setTimeout(BX.delegate(this.closePopup, this), 50);
	}

	onChange()
	{
		var val = this.input.value;
		BX.onCustomEvent(this, 'onSelectInputChanged', [this, val]);
		if (BX.type.isFunction(this.onChangeCallback))
		{
			this.onChangeCallback({value: val});
		}
	}

	destroy()
	{
		if (this.onChangeCallback)
		{
			BX.unbind(this.input, 'change', this.onChangeCallback);
			BX.unbind(this.input, 'keyup', this.onChangeCallback);
		}

		BX.unbind(this.input, 'click', BX.proxy(this.onClick, this));
		BX.unbind(this.input, 'focus', BX.proxy(this.onFocus, this));
		BX.unbind(this.input, 'blur', BX.proxy(this.onBlur, this));
		BX.unbind(this.input, 'keyup', BX.proxy(this.onKeyup, this));

		if (this.popupMenu)
		{
			this.popupMenu.close();
		}
		BX.PopupMenu.destroy(this.id);
		this.popupMenu = null;
		this.shown = false;
	}
}



