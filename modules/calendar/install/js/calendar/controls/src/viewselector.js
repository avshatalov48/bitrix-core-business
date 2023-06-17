import {Type, Dom, Event, Tag, Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {MenuManager} from 'main.popup';

export class ViewSelector extends EventEmitter
{
	views = [];
	created = false;
	currentValue = null;
	currentViewMode = null;
	DOM = {};

	constructor(params = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.ViewSelector');

		if (Type.isArray(params.views))
		{
			this.views = params.views;
		}

		this.zIndex = params.zIndex || 3200;
		this.popupId = params.id || 'view-selector-' + Math.round(Math.random() * 10000);
		this.create();

		if (params.currentView)
		{
			this.setValue(params.currentView);
		}

		if (params.currentViewMode)
		{
			this.setViewMode(params.currentViewMode);
		}
	}

	create()
	{
		this.DOM.wrap = Tag.render`<div class="calendar-view-switcher-selector"></div>`;
		this.DOM.selectorText = Tag.render`<div class="calendar-view-switcher-text"></div>`;
		this.DOM.selectorTextInner = this.DOM.selectorText.appendChild(Tag.render`<div class="calendar-view-switcher-text-inner"></div>`);
		this.DOM.wrap.appendChild(this.DOM.selectorText);
		this.DOM.wrap.appendChild(Tag.render`<div class="calendar-view-switcher-dropdown"></div>`);
		Event.bind(this.DOM.wrap, 'click', this.showPopup.bind(this));
		this.DOM.viewModeTextInner = this.DOM.selectorText.appendChild(Tag.render`<div class="calendar-view-switcher-text-mode-inner" style="display: none;"></div>`);

		this.created = true;
	}

	getOuterWrap()
	{
		if (!this.created)
		{
			this.create();
		}

		return this.DOM.wrap;
	}

	setValue(value)
	{
		this.currentValue = this.views.find(function(view)
		{
			return value.name === view.name;
		}, this);

		if (this.currentValue)
		{
			Dom.adjust(this.DOM.selectorTextInner, {text: this.currentValue.text});
		}
	}

	setViewMode(value)
	{
		if (value)
		{
			this.currentViewMode = this.views.find(function(view)
			{
				return value === view.name && view.type === 'additional';
			}, this);

			if (this.currentViewMode)
			{
				Dom.adjust(this.DOM.viewModeTextInner, {text: '(' + this.currentViewMode.text + ')'});
			}
			this.DOM.viewModeTextInner.style.display = this.currentViewMode ? '' : 'block';
		}
	}

	getMenuItems()
	{
		let menuItems = [];
		this.views.forEach(function(view)
		{
			if (view.type === 'base')
			{
				menuItems.push({
					html: '<span>' + view.text + '</span>' + (view.hotkey ? '<span class="calendar-item-hotkey">' + view.hotkey + '</span>' : ''),
					//text: view.text,
					className: this.currentValue.name === view.name ? 'menu-popup-item-accept' : ' ',
					onclick: function(){
						this.emit('onChange', {
							name: view.name,
							type: view.type,
							dataset: view.dataset
						});
						this.menuPopup.close();
					}.bind(this)
				});
			}
		}, this);

		if (menuItems.length < this.views.length)
		{
			menuItems.push({
				html: '<span>' + Loc.getMessage('EC_VIEW_MODE_SHOW_BY') + '</span>',
				className: 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label'
			});

			this.views.forEach(function(view)
			{
				if (view.type === 'additional')
				{
					menuItems.push({
						text: view.text,
						className: this.currentViewMode.name === view.name ? 'menu-popup-item-accept' : ' ',
						onclick: function(){
							this.emit('onChange', {
								name: view.name,
								type: view.type,
								dataset: view.dataset
							});
							this.menuPopup.close();
						}.bind(this)
					});
				}
			}, this);
		}

		return menuItems;
	}

	showPopup()
	{
		if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
		{
			return this.menuPopup.close();
		}

		this.menuPopup = MenuManager.create(
			this.popupId,
			this.DOM.selectorText,
			this.getMenuItems(),
			{
				className: "calendar-view-switcher-popup",
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: -3,
				offsetLeft: this.DOM.selectorText.offsetWidth - 6,
				angle: true,
				cacheable: false
			}
		);

		this.menuPopup.show();
	}

	closePopup()
	{
		if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
		{
			this.menuPopup.close();
		}
	}

	show()
	{
		this.DOM.wrap.style.display = '';
	}

	hide()
	{
		this.DOM.wrap.style.display = 'none';
	}
}



