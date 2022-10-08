import 'ui.design-tokens';

import {Cache, Dom, Tag, Text, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';

import './css/style.css';

export type ActionPanelItemOptions = {
	id: string,
	text: string,
	align: 'left' | 'center' | 'right',
	onClick: () => void,
};

export class ActionPanel extends EventEmitter
{
	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Component.ActionPanel');
		this.options = {...options};
		this.cache = new Cache.MemoryCache();

		const {left, center, right} = this.options;

		if (Type.isArray(left))
		{
			left.forEach((item) => this.addItem({...item, align: 'left'}));
		}

		if (Type.isArray(center))
		{
			center.forEach((item) => this.addItem({...item, align: 'center'}));
		}

		if (Type.isArray(right))
		{
			right.forEach((item) => this.addItem({...item, align: 'right'}));
		}

		if (Type.isDomNode(this.options.renderTo))
		{
			Dom.append(this.getLayout(), this.options.renderTo);
		}

		if (Type.isPlainObject(this.options.style))
		{
			Dom.style(this.getLayout(), this.options.style);
		}
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-component-action-panel">
					${this.getLeftContainer()}
					${this.getCenterContainer()}
					${this.getRightContainer()}
				</div>
			`;
		});
	}

	getNode(): HTMLDivElement
	{
		return this.getLayout();
	}

	getLeftContainer(): HTMLDivElement
	{
		return this.cache.remember('leftContainer', () => {
			return Tag.render`
				<div class="landing-ui-component-action-panel-left"></div>
			`;
		});
	}

	getCenterContainer(): HTMLDivElement
	{
		return this.cache.remember('centerContainer', () => {
			return Tag.render`
				<div class="landing-ui-component-action-panel-center"></div>
			`;
		});
	}

	getRightContainer(): HTMLDivElement
	{
		return this.cache.remember('rightContainer', () => {
			return Tag.render`
				<div class="landing-ui-component-action-panel-right"></div>
			`;
		});
	}

	addItem(itemOptions: ActionPanelItemOptions): HTMLDivElement
	{
		const item = Tag.render`
			<div 
				class="landing-ui-component-action-panel-button"
				onclick="${itemOptions.onClick}"
				data-id="${itemOptions.id}"
			>${Text.encode(itemOptions.text)}</div>
		`;

		if (itemOptions.align === 'left')
		{
			Dom.append(item, this.getLeftContainer());
		}

		if (itemOptions.align === 'center')
		{
			Dom.append(item, this.getCenterContainer());
		}

		if (itemOptions.align === 'right')
		{
			Dom.append(item, this.getRightContainer());
		}
	}
}