import {Dom, Event} from "main.core";

export class FormFieldTunnerPopupAbstract {
	constructor(params)
	{
		this.id = 'resourcebooking-settings-popup-' + Math.round(Math.random() * 100000);
		this.menuItems = [];
		this.DOM = {
			outerWrap: params.wrap
		};
		this.handleClickFunc = this.handleClick.bind(this);
	}

	build()
	{
		this.DOM.innerWrap = this.DOM.outerWrap.appendChild(Dom.create("div", {props:{className:'calendar-resbook-webform-settings-popup-select'}}));

		this.DOM.currentStateLink = this.DOM.innerWrap.appendChild(
			Dom.create("span",
				{
					props : { className : 'calendar-resbook-webform-settings-popup-select-value'},
					text: this.getCurrentModeState(),
					events: {click: this.showPopup.bind(this)}
				}
			)
		);
	}

	showPopup()
	{
		if (this.isPopupShown() || this.disabled)
		{
			return this.closePopup();
		}

		this.menuItems = this.getMenuItems();

		Event.unbind(document, 'click', this.handleClickFunc);
		this.popup = BX.PopupMenu.create(
			this.id,
			this.DOM.currentStateLink,
			this.menuItems,
			{
				className: 'popup-window-resource-select',
				closeByEsc : true,
				autoHide : false,
				offsetTop: 0,
				offsetLeft: 0,
				cacheable: false
			}
		);

		this.popup.popupWindow.setAngle({offset: 30, position: 'top'});
		this.popup.show(true);
		this.popupContainer = this.popup.popupWindow.popupContainer;

		//this.popupContainer.style.width = parseInt(this.DOM.blocksWrap.offsetWidth) + 'px';

		// BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', function()
		// {
		// 	BX.PopupMenu.destroy(this.id);
		// 	this.popup = null;
		// }.bind(this));

		this.popup.menuItems.forEach(function(menuItem)
		{
			let inputType = false, className, checked, inputNameStr = '';
			if (menuItem.dataset && menuItem.dataset.type)
			{
				checked = menuItem.dataset.checked;

				let menuItemClassName = 'menu-popup-item';
				if (menuItem.dataset.type === 'radio')
				{
					inputType = 'radio';
					className = 'menu-popup-item-resource-radio';
					if (menuItem.dataset.inputName)
					{
						inputNameStr = ' name="' + menuItem.dataset.inputName + '" ';
					}
				}
				else if (menuItem.dataset.type === 'checkbox')
				{
					inputType = 'checkbox';
					className = 'menu-popup-item-resource-checkbox';
				}

				let innerHtml = '<div class="menu-popup-item-inner">';
				if (menuItem.dataset.type === 'submenu-list')
				{
					menuItemClassName += ' menu-popup-item-submenu';
					innerHtml += '<div class="menu-popup-item-resource menu-popup-item-resource-wide">' +
						'<span class="menu-popup-item-text">' +
						'<span>' + menuItem.text + '</span>' +
						'<span class="menu-popup-item-resource-subvalue">' + (menuItem.dataset.textValue || menuItem.dataset.value) + '</span>' +
						'</span>' +
						'</div>';
				}
				else if (inputType)
				{
					innerHtml += '<div class="menu-popup-item-resource">';
					if (inputType)
					{
						innerHtml += '<input class="' + className + '" type="' + inputType + '"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '" ' + inputNameStr + '>' +
							'<label class="menu-popup-item-text"  for="' + menuItem.id + '">' + menuItem.text + '</label>';
					}
					innerHtml += '</div>';
				}

				innerHtml += '</div>';

				menuItem.layout.item.className = menuItemClassName;
				menuItem.layout.item.innerHTML = innerHtml;
			}
		}, this);

		setTimeout(()=>{Event.bind(document, 'click', this.handleClickFunc);}, 50);
	}

	closePopup()
	{
		if (this.isPopupShown())
		{
			this.popup.close();
			this.popupContainer.style.maxHeight = '';
		}
	}

	isPopupShown()
	{
		return this.popup && this.popup.popupWindow &&
			this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() &&
			this.popup.popupWindow.popupContainer &&
			BX.isNodeInDom(this.popup.popupWindow.popupContainer)
	}

	getCurrentModeState()
	{
		return '';
	}

	getMenuItems()
	{
		return [];
	}

	getPopupContent()
	{
		this.DOM.innerWrap = Dom.create("div", {props : {className : ''}});
		return this.DOM.innerWrap;
	}

	handlePopupClick(e)
	{
		let target = e.target || e.srcElement;
		if (target.hasAttribute('data-bx-resbook-control-node') ||
			BX.findParent(target, {attribute: 'data-bx-resbook-control-node'}, this.DOM.innerWrap)
		)
		{
			this.handleControlChanges();
		}
	}

	handleControlChanges()
	{
		if (this.changesTimeout)
		{
			this.changesTimeout = clearTimeout(this.changesTimeout);
		}
		this.changesTimeout = setTimeout(BX.delegate(function(){BX.onCustomEvent('ResourceBooking.webformSettings:onChanged');}, this), 50);
	}

	menuItemClick(e, menuItem)
	{

	}

	handleClick(e)
	{
		let target = e.target || e.srcElement;
		if (this.isPopupShown() && !BX.isParentForNode(this.popupContainer, target))
		{
			return this.closePopup({animation: true});
		}
	}

	setDisabled()
	{
		this.disabled = true;
		if (this.isPopupShown())
		{
			this.closePopup();
		}
		Dom.addClass(this.DOM.innerWrap, 'disabled');
	}

	setEnabled()
	{
		this.disabled = false;
		Dom.removeClass(this.DOM.innerWrap, 'disabled');
	}
}