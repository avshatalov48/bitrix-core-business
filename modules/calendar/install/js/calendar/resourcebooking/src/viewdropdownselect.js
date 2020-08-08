import {Type, Loc, Dom, Event, Text, BookingUtil, MenuManager} from "./resourcebooking";

export class ViewDropDownSelect
{
	constructor(params)
	{
		this.id = 'viewform-dropdown-select-' + Math.round(Math.random() * 100000);
		this.DOM = {
			wrap: params.wrap
		};
		this.maxHeight = params.maxHeight;
		this.selectAllMessage = Loc.getMessage('WEBF_RES_SELECT_ALL');
		this.setSettings(params);
	}

	build()
	{
		this.DOM.select = this.DOM.wrap.appendChild(Dom.create("div", {
			attrs: {
				className: "calendar-resbook-webform-block-input calendar-resbook-webform-block-input-dropdown"
			},
			events: {click: this.openPopup.bind(this)}
		}));

		this.setSelectedValues(this.selected);
	}

	setSettings(params)
	{
		this.handleChangesCallback = Type.isFunction(params.handleChangesCallback) ? params.handleChangesCallback : null;
		this.values = params.values;
		this.selected = !Type.isArray(params.selected) ? [params.selected] : params.selected;
		this.multiple = params.multiple;
	}

	openPopup()
	{
		if (this.isPopupShown())
		{
			return this.closePopup();
		}

		let menuItems = [];
		this.values.forEach(function(item)
		{
			let className = 'menu-popup-no-icon';
			if (Type.isArray(this.selected) && this.selected.includes(parseInt(item.id)))
			{
				className += ' menu-item-selected';
			}

			menuItems.push({
				id: item.id,
				className: className,
				text: Text.encode(item.title),
				onclick: this.menuItemClick.bind(this)
			});
		}, this);

		if (this.multiple && menuItems.length <= 1)
		{
			this.multiple = false;
		}

		if (this.multiple)
		{
			menuItems.push({
				id: 'select-all',
				text: this.selectAllMessage,
				onclick: this.selectAllItemClick.bind(this)
			});
		}

		this.popup = MenuManager.create(
			this.id,
			this.DOM.select,
			menuItems,
			{
				className: 'calendar-resbook-form-popup' + (this.multiple ? ' popup-window-resource-select' : ''),
				closeByEsc : true,
				autoHide : !this.multiple,
				offsetTop: 0,
				offsetLeft: 0,
				cacheable: false
			}
		);

		this.popup.show(true);
		this.popupContainer = this.popup.popupWindow.popupContainer;
		this.popupContainer.style.width = parseInt(this.DOM.select.offsetWidth) + 'px';

		if (this.multiple)
		{
			this.popup.menuItems.forEach(function(menuItem)
			{
				let checked;
				if (menuItem.id === 'select-all')
				{
					this.selectAllChecked = !this.values.find(function(value){
						return !this.selected.find(function(itemId){return itemId === value.id});
					},this);

					menuItem.layout.item.className = 'menu-popup-item menu-popup-item-resource-all';
					menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
						'<div class="menu-popup-item-resource">' +
						'<input class="menu-popup-item-resource-checkbox menu-popup-item-all-resources-checkbox" type="checkbox"' + (this.selectAllChecked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
						'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' +
						'</div>' +
						'</div>';
				}
				else
				{
					checked = this.selected.find(function(itemId){return itemId === menuItem.id});

					menuItem.layout.item.className = 'menu-popup-item';
					menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
						'<div class="menu-popup-item-resource">' +
						'<input class="menu-popup-item-resource-checkbox" type="checkbox"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
						'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' +
						'</div>' +
						'</div>';
				}
			}, this);

			Event.unbind(document, 'click', this.handleClick.bind(this));
			setTimeout(function(){
				Event.bind(document, 'click', this.handleClick.bind(this));
			}.bind(this), 50);
		}
	}

	closePopup()
	{
		if (this.isPopupShown())
		{
			this.popup.close();
			if (this.multiple)
			{
				Event.unbind(document, 'click', this.handleClick.bind(this));
			}
		}
	}

	isPopupShown()
	{
		return this.popup && this.popup.popupWindow &&
			this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() &&
			this.popup.popupWindow.popupContainer &&
			Dom.isShown(this.popup.popupWindow.popupContainer);
	}

	menuItemClick(e, menuItem)
	{
		let
			selectAllcheckbox,
			target = e.target || e.srcElement,
			foundValue, checkbox;


		if (this.multiple)
		{
			foundValue = this.values.find(function(value){return value.id == menuItem.id;});
			checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');

			if (foundValue && target && (Dom.hasClass(target, "menu-popup-item") || Dom.hasClass(target, "menu-popup-item-resource-checkbox") || Dom.hasClass(target, "menu-popup-item-inner")))
			{
				if (!Dom.hasClass(target, "menu-popup-item-resource-checkbox"))
				{
					checkbox.checked = !checkbox.checked;
				}

				if (checkbox.checked)
				{
					this.selectItem(foundValue);
				}
				else
				{
					this.deselectItem(foundValue);
					selectAllcheckbox = this.popupContainer.querySelector('.menu-popup-item-all-resources-checkbox');
					this.selectAllChecked = false;
					if (selectAllcheckbox)
					{
						selectAllcheckbox.checked = false;
					}
				}
				this.setSelectedValues(this.selected);
				this.handleControlChanges();
			}
		}
		else
		{
			this.setSelectedValues([menuItem.id]);
			this.handleControlChanges();
			this.closePopup();
		}
	}

	selectItem(value)
	{
		if (!this.selected.includes(value.id))
		{
			this.selected.push(value.id);
		}
	}

	deselectItem(value)
	{
		let index = this.selected.indexOf(parseInt(value.id));
		if (index >= 0)
		{
			this.selected = this.selected.slice(0, index).concat(this.selected.slice(index + 1));
		}
	}

	selectAllItemClick(e, menuItem)
	{
		let target = e.target || e.srcElement;
		if (target && (Dom.hasClass(target, "menu-popup-item") || Dom.hasClass(target, "menu-popup-item-resource-checkbox")))
		{
			let checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');

			if (Dom.hasClass(target, "menu-popup-item"))
			{
				checkbox.checked = !checkbox.checked;
			}

			let i, checkboxes = this.popupContainer.querySelectorAll('input.menu-popup-item-resource-checkbox');
			this.selectAllChecked = checkbox.checked;

			for (i = 0; i < checkboxes.length; i++)
			{
				checkboxes[i].checked = this.selectAllChecked;
			}
			this.selected = [];
			if (this.selectAllChecked)
			{
				this.values.forEach(function(value){this.selected.push(value.id);}, this);
			}
			this.setSelectedValues(this.selected);
			this.handleControlChanges();
		}
	}

	handleClick(e)
	{
		if (this.isPopupShown() && !this.popupContainer.contains(e.target || e.srcElement))
		{
			this.closePopup({animation: true});
		}
		this.handleControlChanges();
	}

	getSelectedValues()
	{
		return this.selected;
	}

	setSelectedValues(values)
	{
		let i,
			foundValue,
			textValues = [],
			selectedValues = [];

		for (i = 0; i < values.length; i++)
		{
			foundValue = this.values.find(function(value){return value.id === values[i];});
			if (foundValue)
			{
				textValues.push(foundValue.title);
				selectedValues.push(foundValue.id);
			}
		}

		this.selected = selectedValues;
		Dom.adjust(this.DOM.select, {text: textValues.length ? textValues.join(', ') : Loc.getMessage('USER_TYPE_RESOURCE_LIST_PLACEHOLDER')});
	}

	handleControlChanges()
	{
		if (this.handleChangesCallback)
		{
			this.handleChangesCallback(this.getSelectedValues());
		}
	}
}
