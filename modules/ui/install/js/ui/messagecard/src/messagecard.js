import {Cache, Dom, Tag, Type} from 'main.core';
import {BaseCard} from './basecard';

import 'ui.design-tokens';
import 'ui.fonts.opensans';
import './css/messagecard.css';

export class MessageCard extends BaseCard
{
	static cache = new Cache.MemoryCache();

	constructor(
		options: {
			id?: string,
			header?: string,
			description?: string,
			icon?: string,
			angle?: boolean,
			closeable?: boolean,
			hideActions?: boolean,
			restoreState?: boolean,
			actionElements: Array<HTMLElement>
		},
	)
	{
		super(options);
		Dom.addClass(this.getLayout(), 'ui-card-message');

		this.onCloseClick = this.onCloseClick.bind(this);

		if (this.options.angle === false)
		{
			Dom.addClass(this.getLayout(), 'ui-card-message-without-angle');
		}

		if (Type.isStringFilled(this.options.icon))
		{
			Dom.append(this.getIcon(), this.getHeader());
		}

		if (!Type.isArray(this.options.actionElements))
		{
			this.options.actionElements = [];
		}

		Dom.append(this.getTitle(), this.getHeader());
		Dom.append(this.getDescription(), this.getBody());

		if (this.options.closeable !== false)
		{
			Dom.append(this.getCloseButton(), this.getLayout());
		}

		if (this.options.hideActions !== true || this.options.more)
		{
			Dom.append(this.getActionsContainer(), this.getLayout());
		}

		if (this.isAllowRestoreState())
		{
			const state = MessageCard.cache.get(this.options.id, {shown: true});
			if (state.shown)
			{
				this.show();
			}
			else
			{
				this.hide();
			}
		}
	}

	isAllowRestoreState(): boolean
	{
		return this.options.restoreState && this.options.id;
	}

	getIcon(): HTMLDivElement
	{
		return this.cache.remember('icon', () => {
			return Tag.render`
				<div class="ui-card-message-icon" style="background-image: url(${this.options.icon})"></div>
			`;
		});
	}

	getTitle(): HTMLDivElement
	{
		return this.cache.remember('title', () => {
			return Tag.render`
				<div class="ui-card-message-title">${this.options.header}</div>
			`;
		});
	}

	getDescription(): HTMLDivElement
	{
		return this.cache.remember('description', () => {
			return Tag.render`
				<div class="ui-card-message-description">${this.options.description}</div>
			`;
		});
	}

	getCloseButton(): HTMLDivElement
	{
		return this.cache.remember('closeButton', () => {
			return Tag.render`
				<div 
					class="ui-card-message-close-button" 
					onclick="${this.onCloseClick}"
				></div>
			`;
		});
	}

	onCloseClick(event: MouseEvent)
	{
		event.preventDefault();
		this.hide();
		this.emit('onClose');
		MessageCard.cache.set(this.options.id, {shown: false});
	}

	getActionsContainer(): HTMLDivElement
	{
		return this.cache.remember('actionsContainer', () => {
			const actionWrapper =  Tag.render`
				<div class="ui-card-message-actions"></div>
			`;

			this.options.actionElements.forEach((element: HTMLElement) => {
				actionWrapper.appendChild(element);
			});

			return actionWrapper;
		});
	}

	onClick()
	{
		this.onClickHandler(this);
		this.emit('onClick');
	}
}