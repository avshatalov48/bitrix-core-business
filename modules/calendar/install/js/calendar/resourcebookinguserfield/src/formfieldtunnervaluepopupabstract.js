import {BookingUtil, Dom} from "calendar.resourcebooking";

export class FormFieldTunnerValuePopupAbstract
{
	constructor(params)
	{
		this.id = 'resourcebooking-settings-value-popup-' + Math.round(Math.random() * 100000);
		this.selectedValues = [];
		this.DOM = {
			outerWrap: params.wrap
		};
	}

	build()
	{
		this.DOM.innerWrap = this.DOM.outerWrap.appendChild(Dom.create("div", {props: {className: 'calendar-resbook-webform-settings-popup-select-result'}}));

		this.DOM.valueLink = this.DOM.innerWrap.appendChild(Dom.create("span", {
			props: {className: 'calendar-resbook-webform-settings-popup-select-value'},
			text: this.getCurrentValueState(),
			events: {
				click: this.showPopup.bind(this),
				mouseover: this.showHoverPopup.bind(this),
				mouseout: this.hideHoverPopup.bind(this)
			}
		}));
	}

	showPopup()
	{
		if (this.popup && this.popup.isShown())
		{
			return this.popup.close();
		}

		this.popup = new BX.PopupWindow(
			this.id,
			this.DOM.valueLink,
			{
				autoHide: true,
				loseByEsc: true,
				offsetTop: 0,
				offsetLeft: 0,
				width: this.getPopupWidth(),
				lightShadow: true,
				content: this.getPopupContent()
			});
		this.popup.setAngle({offset: 60, position: 'top'});
		this.popup.show(true);

		BX.unbind(this.DOM.innerWrap, 'click', BX.proxy(this.handlePopupClick, this));
		BX.bind(this.DOM.innerWrap, 'click', BX.proxy(this.handlePopupClick, this));

		BX.addCustomEvent(this.popup, 'onPopupClose', BX.delegate(function ()
		{
			this.handlePopupCloose();
			this.popup.destroy(this.id);
			this.popup = null;
		}, this));
	}

	closePopup()
	{
		if (this.isPopupShown())
		{
			this.popup.close();
		}
	}

	isPopupShown()
	{
		return this.popup && this.popup.popupWindow &&
			this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() &&
			this.popup.popupWindow.popupContainer &&
			BX.isNodeInDom(this.popup.popupWindow.popupContainer)
	}

	showHoverPopup()
	{
	}

	hideHoverPopup()
	{
	}

	handlePopupCloose()
	{
	}

	getCurrentValueState()
	{
		return BX.message('WEBF_RES_NO_VALUE');
	}

	getPopupContent()
	{
		this.DOM.innerWrap = Dom.create("div", {props: {className: ''}});

		this.DOM.innerWrap.style.minWidth = '500px';
		this.DOM.innerWrap.style.minHeight = '30px';
		return this.DOM.innerWrap;
	}

	getPopupWidth()
	{
		return null;
	}

	handlePopupClick(e)
	{
		var target = e.target || e.srcElement;
		if (target.hasAttribute('data-bx-resbook-control-node') || BX.findParent(target, {attribute: 'data-bx-resbook-control-node'}, this.DOM.innerWrap))
		{
			this.handleControlChanges();
		}
	}

	handleControlChanges()
	{
		setTimeout(BX.delegate(function(){BX.onCustomEvent('ResourceBooking.webformSettings:onChanged');}, this), 50);
	}

	showPopupLoader()
	{
		if (this.DOM.innerWrap)
		{
			this.hidePopupLoader();
			this.DOM.popupLoader = this.DOM.innerWrap.appendChild(BookingUtil.getLoader(50));
		}
	}

	hidePopupLoader()
	{
		Dom.remove(this.DOM.popupLoader);
	}
}

	export class FormFieldTunnerMultipleChecknoxPopupAbstract extends FormFieldTunnerValuePopupAbstract{
	constructor(params)
	{
		super(params);
		this.id = 'resourcebooking-settings-multiple-checknox-' + Math.round(Math.random() * 100000);
	}

	showPopup()
	{
		if (this.isPopupShown())
		{
			return this.closePopup();
		}

		var menuItems = [];

		this.values.forEach(function(item)
		{
			menuItems.push({
				id: item.id,
				text: BX.util.htmlspecialchars(item.title),
				dataset: item.dataset,
				onclick: BX.proxy(this.menuItemClick, this)
			});
		}, this);

		if (menuItems.length > 1)
		{
			this.selectAllMessage = this.selectAllMessage || 'select all';
			menuItems.push({
				text: this.selectAllMessage,
				onclick: BX.proxy(this.selectAllItemClick, this)
			});
		}

		this.popup = BX.PopupMenu.create(
			this.id,
			this.DOM.valueLink,
			menuItems,
			{
				className: 'popup-window-resource-select',
				closeByEsc : true,
				autoHide : false,
				offsetTop: 0,
				offsetLeft: 0
			}
		);

		this.popup.popupWindow.setAngle({offset: 60, position: 'top'});
		this.popup.show(true);
		this.popupContainer = this.popup.popupWindow.popupContainer;

		BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', BX.proxy(function()
		{
			this.handlePopupCloose();
			BX.PopupMenu.destroy(this.id);
			this.popup = null;
		}, this));

		this.popup.menuItems.forEach(function(menuItem)
		{
			var checked;
			if (menuItem.dataset && menuItem.dataset.id)
			{
				checked = this.selectedValues.find(function(itemId){return itemId === menuItem.id});

				menuItem.layout.item.className = 'menu-popup-item';
				menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
					'<div class="menu-popup-item-resource">' +
					'<input class="menu-popup-item-resource-checkbox" type="checkbox"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
					'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' +
					'</div>' +
					'</div>';
			}
			else
			{
				this.selectAllChecked = !this.values.find(function(value){
					return !this.selectedValues.find(function(itemId){return itemId === value.id});
				},this);

				menuItem.layout.item.className = 'menu-popup-item menu-popup-item-resource-all';
				menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
					'<div class="menu-popup-item-resource">' +
					'<input class="menu-popup-item-resource-checkbox menu-popup-item-all-resources-checkbox" type="checkbox"' + (this.selectAllChecked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
					'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' +
					'</div>' +
					'</div>';
			}
		}, this);

		setTimeout(BX.delegate(function(){
			BX.bind(document, 'click', BX.proxy(this.handleClick, this));
		}, this), 50);
	}

	menuItemClick(e, menuItem)
	{
		var
			selectAllcheckbox,
			target = e.target || e.srcElement,
			checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox'),
			foundValue = this.values.find(function(value){return value.id === menuItem.id;});

		if (foundValue)
		{
			if (target && (Dom.hasClass(target, "menu-popup-item") || Dom.hasClass(target, "menu-popup-item-resource-checkbox") || Dom.hasClass(target, "menu-popup-item-inner") ))
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
			}
			this.handleControlChanges();
		}
	}

	selectItem(value)
	{
		if (!BX.util.in_array(value.id, this.selectedValues))
		{
			this.selectedValues.push(value.id);
		}
	}
	deselectItem(value)
	{
		var index = BX.util.array_search(value.id, this.selectedValues);
		if (index >= 0)
		{
			this.selectedValues = BX.util.deleteFromArray(this.selectedValues, index);
		}
	}

	selectAllItemClick(e, menuItem)
	{
		var target = e.target || e.srcElement;
		if (target && (Dom.hasClass(target, "menu-popup-item") || Dom.hasClass(target, "menu-popup-item-resource-checkbox")))
		{
			var checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');

			if (Dom.hasClass(target, "menu-popup-item"))
			{
				checkbox.checked = !checkbox.checked;
			}

			var i, checkboxes = this.popupContainer.querySelectorAll('input.menu-popup-item-resource-checkbox');
			this.selectAllChecked = checkbox.checked;

			for (i = 0; i < checkboxes.length; i++)
			{
				checkboxes[i].checked = this.selectAllChecked;
			}
			this.selectedValues = [];
			if (this.selectAllChecked)
			{
				this.values.forEach(function(value){this.selectedValues.push(value.id);}, this);
			}
			this.handleControlChanges();
		}
	}

	handleClick(e)
	{
		var target = e.target || e.srcElement;
		if (this.isPopupShown() && !BX.isParentForNode(this.popupContainer, target))
		{
			this.closePopup({animation: true});
		}

		this.handleControlChanges();
	}

	closePopup()
	{
		if (this.isPopupShown())
		{
			this.popup.close();
			this.popupContainer.style.maxHeight = '';
			BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
		}
	}

	getSelectedValues()
	{
		return this.selectedValues;
	}
}