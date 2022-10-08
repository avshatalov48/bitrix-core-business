import {Type, Dom, Event, BookingUtil, MenuManager} from "../resourcebooking";

export class SelectInput extends Event.EventEmitter
{
	constructor(params)
	{
		super(params);

		this.id = params.id || 'bx-select-input-' + Math.round(Math.random() * 1000000);

		if (Type.isFunction(params.getValues))
		{
			this.getValues = params.getValues;
			this.values = this.getValues();
		}
		else
		{
			this.values = params.values || false;
		}

		this.input = params.input;
		this.defaultValue = params.defaultValue || '';
		this.openTitle = params.openTitle || '';
		this.className = params.className || '';
		this.currentValue = params.value;
		this.currentValueIndex = params.valueIndex;
		this.onChangeCallback = Type.isFunction(params.onChangeCallback) ? params.onChangeCallback : null;
		this.onAfterMenuOpen = params.onAfterMenuOpen || null;
		this.zIndex = params.zIndex || 1200;
		this.disabled = params.disabled;
		this.editable = params.editable !== false;
		this.setFirstIfNotFound = !!params.setFirstIfNotFound;

		if (this.onChangeCallback)
		{
			Event.bind(this.input, 'change', this.onChangeCallback);
			Event.bind(this.input, 'keyup', this.onChangeCallback);
		}

		this.curInd = false;

		if (Type.isArray(this.values))
		{
			Event.bind(this.input, 'click', this.onClick.bind(this));

			if (this.editable)
			{
				Event.bind(this.input, 'focus', this.onFocus.bind(this));
				Event.bind(this.input, 'blur', this.onBlur.bind(this));
				Event.bind(this.input, 'keyup', this.onKeyup.bind(this));
			}
			else
			{
				Event.bind(this.input, 'focus', function(){this.input.blur();}.bind(this));
			}

			if (this.currentValueIndex === undefined && this.currentValue !== undefined)
			{
				this.currentValueIndex = -1;
				for (let i = 0; i < this.values.length; i++)
				{
					if (parseInt(this.values[i].value) === parseInt(this.currentValue))
					{
						this.currentValueIndex = i;
						break;
					}
				}

				if (this.currentValueIndex === -1)
				{
					this.currentValueIndex = this.setFirstIfNotFound ? 0 : undefined;
				}
			}
		}

		if (this.currentValueIndex !== undefined && this.values[this.currentValueIndex])
		{
			this.input.value = this.values[this.currentValueIndex].label;
		}
	}

	showPopup()
	{
		if (this.getValues)
		{
			this.values = this.getValues();
		}

		if (this.shown || this.disabled || !this.values.length)
		{
			return;
		}

		let
			ind = 0,
			j = 0,
			menuItems = [],
			i,
			_this = this;

		for (i = 0; i < this.values.length; i++)
		{
			if (this.values[i].delimiter)
			{
				menuItems.push(this.values[i]);
			}
			else
			{
				if ((this.currentValue && this.values[i] && this.values[i].value === this.currentValue.value)
					|| this.input.value === this.values[i].label)
				{
					ind = j;
				}

				menuItems.push({
					id: this.values[i].value + '_' + i,
					text: this.values[i].label,
					onclick: this.values[i].callback || (function (value, label)
					{
						return function ()
						{
							_this.input.value = label;
							_this.popupMenu.close();
							_this.onChange(value, label);
						}
					})(this.values[i].value, this.values[i].labelRaw || this.values[i].label)
				});
				j++;
			}
		}

		this.popupMenu = MenuManager.create(
			this.id,
			this.input,
			menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: 0,
				cacheable: false
			}
		);

		if (!BX.browser.IsFirefox())
		{
			this.popupMenu.popupWindow.setMinWidth(this.input.offsetWidth);
		}

		this.popupMenu.popupWindow.setMaxWidth(300);

		let menuContainer = this.popupMenu.layout.menuContainer;
		Dom.addClass(this.popupMenu.layout.menuContainer, 'calendar-resourcebook-select-popup');
		this.popupMenu.show();

		let menuItem = this.popupMenu.menuItems[ind];
		if (menuItem && menuItem.layout)
		{
			menuContainer.scrollTop = menuItem.layout.item.offsetTop - 2;
		}

		BookingUtil.bindCustomEvent(this.popupMenu.popupWindow, 'onPopupClose', function(){this.shown = false;}.bind(this));

		this.input.select();

		if (Type.isFunction(this.onAfterMenuOpen))
		{
			this.onAfterMenuOpen(ind, this.popupMenu);
		}

		this.shown = true;
	}

	closePopup()
	{
		MenuManager.destroy(this.id);
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
		setTimeout(this.closePopup.bind(this), 200);
	}

	onKeyup()
	{
		setTimeout(this.closePopup.bind(this), 50);
	}

	onChange(value)
	{
		let val = this.input.value;
		this.emit('BX.Calendar.Resourcebooking.SelectInput:changed', new Event.BaseEvent({data: {selectinput: this, value: val, realValue: value}}));
		if (this.onChangeCallback)
		{
			this.onChangeCallback({value: val, realValue: value});
		}
	}

	destroy()
	{
		if (this.onChangeCallback)
		{
			Event.unbind(this.input, 'change', this.onChangeCallback);
			Event.unbind(this.input, 'keyup', this.onChangeCallback);
		}

		Event.unbind(this.input, 'click', this.onClick.bind(this));
		Event.unbind(this.input, 'focus', this.onFocus.bind(this));
		Event.unbind(this.input, 'blur', this.onBlur.bind(this));
		Event.unbind(this.input, 'keyup', this.onKeyup.bind(this));

		if (this.popupMenu)
		{
			this.popupMenu.close();
		}

		MenuManager.destroy(this.id);
		this.shown = false;
	}

	setValue(value)
	{
		this.input.value = value;
		if (Type.isArray(this.values))
		{
			let currentValueIndex = -1;
			for (let i = 0; i < this.values.length; i++)
			{
				if (this.values[i].value === value)
				{
					currentValueIndex = i;
					break;
				}
			}

			if (currentValueIndex !== -1)
			{
				this.input.value = this.values[currentValueIndex].label;
				this.currentValueIndex = currentValueIndex;
			}
		}
	}

	getValue()
	{
		return this.input.value;
	}
}