import {Type, Dom, Event, Tag, Loc} from 'main.core';
//import {ViewSelector} from './viewselector';
import {EventEmitter} from 'main.core.events';

export class LineViewSelector extends  EventEmitter
{
	views = [];
	created = false;
	currentValue = null;
	currentViewMode = null;
	DOM = {};

	constructor(params = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.LineViewSelector');

		if (Type.isArray(params.views))
		{
			this.views = params.views;
		}

		this.viewsMap = new WeakMap();

		this.zIndex = params.zIndex || 3200;
		this.popupId = params.id || 'view-selector-' + Math.round(Math.random() * 10000);
		this.create();

		if (params.currentView)
		{
			this.setValue(params.currentView);
		}
	}

	create()
	{
		this.DOM.wrap = Tag.render`<div class="calendar-view-switcher-list"></div>`;

		this.views.forEach((view) =>
		{
			if (view.type === 'base')
			{
				this.viewsMap.set(view, {
					wrap: this.DOM.wrap.appendChild(Tag.render`<span 
						class="calendar-view-switcher-list-item"
						onclick="${()=>{
							this.emit('onChange', {
								name: view.name,
								type: view.type,
								dataset: view.dataset
							});
					}}"
					>${view.text}</span>`)
				});
			}
		});

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
			let viewData = this.viewsMap.get(this.currentValue);
			let currentActiveWrap = this.DOM.wrap.querySelector('.calendar-view-switcher-list-item-active');
			if (Type.isDomNode(currentActiveWrap))
			{
				Dom.removeClass(currentActiveWrap, 'calendar-view-switcher-list-item-active');
			}

			if (Type.isDomNode(viewData.wrap))
			{
				Dom.addClass(viewData.wrap, 'calendar-view-switcher-list-item-active');
			}
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

			// if (this.currentViewMode)
			// {
			// 	Dom.adjust(this.DOM.viewModeTextInner, {text: '(' + this.currentViewMode.text + ')'});
			// }
			//this.DOM.viewModeTextInner.style.display = this.currentViewMode ? '' : 'block';
		}
	}

	getMenuItems()
	{
		let menuItems = [];
		this.views.forEach((view) =>
		{
			if (view.type === 'base')
			{
				menuItems.push({
					html: '<span>' + view.text + '</span>' + (view.hotkey ? '<span class="calendar-item-hotkey">' + view.hotkey + '</span>' : ''),
					className: this.currentValue.name === view.name ? 'menu-popup-item-accept' : ' ',
					onclick: () => {
						this.emit('onChange', {
							name: view.name,
							type: view.type,
							dataset: view.dataset
						});
						this.menuPopup.close();
					}
				});
			}
		});

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

	// showPopup()
	// {
	// 	this.closePopup();
	//
	// 	this.menuPopup = MenuManager.create(
	// 		this.popupId,
	// 		this.DOM.selectorText,
	// 		this.getMenuItems(),
	// 		{
	// 			className: "calendar-view-switcher-popup",
	// 			closeByEsc : true,
	// 			autoHide : true,
	// 			zIndex: this.zIndex,
	// 			offsetTop: -3,
	// 			offsetLeft: this.DOM.selectorText.offsetWidth - 6,
	// 			angle: true,
	// 			cacheable: false
	// 		}
	// 	);
	//
	// 	this.menuPopup.show();
	// }
	//
	// closePopup()
	// {
	// 	if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
	// 	{
	// 		return this.menuPopup.close();
	// 	}
	// }
}



