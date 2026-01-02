import { Type, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { NavigationPanel } from 'ui.navigationpanel';

export class LineViewSelector extends EventEmitter
{
	views = [];
	created = false;
	currentValue = null;
	currentViewMode = null;
	DOM = {};
	target: ?HTMLElement = null;
	navigationPanel: ?NavigationPanel = null;

	constructor(params = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.LineViewSelector');

		if (Type.isArray(params.views))
		{
			this.views = params.views;
		}

		this.target = params.target;

		this.zIndex = params.zIndex || 3200;
		this.popupId = params.id || `view-selector-${Math.round(Math.random() * 10000)}`;
		this.create();

		if (params.currentView)
		{
			this.setValue(params.currentView);
		}
	}

	create()
	{
		if (Type.isDomNode(this.target) && !this.navigationPanel)
		{
			const items = [];
			this.views.forEach((view) => {
				if (view.type === 'base')
				{
					items.push(this.getItem(view));
				}
			});

			this.navigationPanel = new NavigationPanel({
				target: this.target,
				items,
			});

			this.navigationPanel.init();
		}
	}

	getItem(view): Object
	{
		const click = () => {
			this.emit('onChange', {
				name: view.name,
				type: view.type,
				dataset: view.dataset,
			});
		};

		return {
			id: view.name,
			title: view.text,
			active: false,
			events: {
				click,
			},
		};
	}

	setValue(value)
	{
		this.currentValue = this.views.find((view) => {
			return value.name === view.name;
		});

		if (this.currentValue)
		{
			const targetWrap = this.navigationPanel.getItemById(this.currentValue.name);

			if (targetWrap)
			{
				targetWrap.activate();
			}
		}
	}

	setViewMode(value)
	{
		if (value)
		{
			this.currentViewMode = this.views.find((view) => {
				return value === view.name && view.type === 'additional';
			});
		}
	}

	getMenuItems()
	{
		const menuItems = [];
		this.views.forEach((view) => {
			if (view.type === 'base')
			{
				menuItems.push({
					html: `<span>${view.text}</span>${view.hotkey ? `<span class="calendar-item-hotkey">${view.hotkey}</span>` : ''}`,
					className: this.currentValue.name === view.name ? 'menu-popup-item-accept' : ' ',
					onclick: () => {
						this.emit('onChange', {
							name: view.name,
							type: view.type,
							dataset: view.dataset,
						});
						this.menuPopup.close();
					},
				});
			}
		});

		if (menuItems.length < this.views.length)
		{
			menuItems.push({
				html: `<span>${Loc.getMessage('EC_VIEW_MODE_SHOW_BY')}</span>`,
				className: 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label',
			});

			this.views.forEach(function(view)
			{
				if (view.type === 'additional')
				{
					menuItems.push({
						text: view.text,
						className: this.currentViewMode.name === view.name ? 'menu-popup-item-accept' : ' ',
						onclick: function() {
							this.emit('onChange', {
								name: view.name,
								type: view.type,
								dataset: view.dataset,
							});
							this.menuPopup.close();
						}.bind(this),
					});
				}
			}, this);
		}

		return menuItems;
	}
}
