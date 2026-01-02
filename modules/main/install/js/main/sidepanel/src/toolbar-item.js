import { Type, Cache, Text, Tag, Dom, type JsonObject } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { PopupManager, Popup } from 'main.popup';

import { type ToolbarItemOptions } from './types/toolbar-item-options';

export class ToolbarItem extends EventEmitter
{
	constructor(itemOptions: ToolbarItemOptions)
	{
		super();
		this.setEventNamespace('BX.Main.SidePanel.ToolbarItem');

		const options = Type.isPlainObject(itemOptions) ? itemOptions : {};

		this.id = Type.isStringFilled(options.id) ? options.id : `toolbar-item-${Text.getRandom().toLowerCase()}`;
		this.title = '';
		this.url = '';
		this.entityType = '';
		this.entityId = '0';
		this.entityName = '';

		this.refs = new Cache.MemoryCache();
		this.rendered = false;

		this.setTitle(options.title);
		this.setUrl(options.url);
		this.setEntityType(options.entityType);
		this.setEntityId(options.entityId);
	}

	getId(): string
	{
		return this.id;
	}

	getUrl(): string
	{
		return this.url;
	}

	setUrl(url): void
	{
		if (Type.isStringFilled(url))
		{
			this.url = url;
			if (this.rendered)
			{
				this.getContainer().href = url;
			}
		}
	}

	getTitle(): string
	{
		return this.title;
	}

	setTitle(title): void
	{
		if (Type.isStringFilled(title))
		{
			this.title = title;
			if (this.rendered)
			{
				this.getTitleContainer().textContent = title;
			}
		}
	}

	getEntityType(): string
	{
		return this.entityType;
	}

	setEntityType(entityType): void
	{
		if (Type.isStringFilled(entityType))
		{
			this.entityType = entityType;
		}
	}

	getEntityId(): string | number
	{
		return this.entityId;
	}

	setEntityId(entityId: string | number): void
	{
		if (Type.isNumber(entityId) || Type.isStringFilled(entityId))
		{
			this.entityId = String(entityId);
		}
	}

	getEntityName(): string
	{
		return this.entityName;
	}

	setEntityName(entityName): void
	{
		if (Type.isStringFilled(entityName))
		{
			this.entityName = entityName;
		}
	}

	getContainer(): HTMLElement
	{
		return this.refs.remember('container', () => {
			return Tag.render`
				<div 
					class="side-panel-toolbar-item"
					onmouseenter="${this.handleMouseEnter.bind(this)}"
					onmouseleave="${this.handleMouseLeave.bind(this)}"
				>
					${this.getTitleContainer()}
					<div class="side-panel-toolbar-item-remove-btn" onclick="${this.handleRemoveBtnClick.bind(this)}">
						<div class="ui-icon-set --cross-20" style="--ui-icon-set__icon-size: 100%;"></div>
					</div>
				</div>
			`;
		});
	}

	isRendered(): boolean
	{
		return this.rendered;
	}

	getTitleContainer(): HTMLElement
	{
		return this.refs.remember('title', () => {
			return Tag.render`
				<a 
					class="side-panel-toolbar-item-title"
					href="${encodeURI(this.getUrl())}"
					data-slider-maximize="true"
				>${Text.encode(this.getTitle())}</a>
			`;
		});
	}

	prependTo(node)
	{
		if (Type.isDomNode(node))
		{
			Dom.prepend(this.getContainer(), node);
			this.rendered = true;
		}
	}

	appendTo(node): void
	{
		if (Type.isDomNode(node))
		{
			Dom.append(this.getContainer(), node);
			this.rendered = true;
		}
	}

	insertBefore(node): void
	{
		if (Type.isDomNode(node))
		{
			Dom.insertBefore(this.getContainer(), node);
			this.rendered = true;
		}
	}

	insertAfter(node): void
	{
		if (Type.isDomNode(node))
		{
			Dom.insertAfter(this.getContainer(), node);
			this.rendered = true;
		}
	}

	remove(): void
	{
		Dom.remove(this.getContainer());
		this.rendered = false;
	}

	showTooltip(): void
	{
		const targetNode = this.getContainer();
		const rect = targetNode.getBoundingClientRect();
		const targetNodeWidth = rect.width;
		const popupWidth = Math.min(Math.max(100, this.getTitleContainer().scrollWidth + 20), 300);

		const hint = PopupManager.create({
			id: 'sidepanel-toolbar-item-hint',
			cacheable: false,
			bindElement: rect,
			bindOptions: {
				forceBindPosition: true,
				forceTop: true,
				position: 'top',
			},
			width: popupWidth,
			content: Tag.render`
				<div class="sidepanel-toolbar-item-hint">
					<div class="sidepanel-toolbar-item-hint-title">${Text.encode(this.getEntityName())}</div>
					<div class="sidepanel-toolbar-item-hint-content">${Text.encode(this.getTitle())}</div>
				</div>
			`,
			darkMode: true,
			fixed: true,
			offsetTop: 0,
			events: {
				onShow: (event) => {
					const popup = event.getTarget();
					const offsetLeft = (targetNodeWidth / 2) - (popupWidth / 2);
					const angleShift = Popup.getOption('angleLeftOffset') - Popup.getOption('angleMinTop');

					popup.setAngle({ offset: popupWidth / 2 - angleShift });
					popup.setOffset({ offsetLeft: offsetLeft + Popup.getOption('angleLeftOffset') });
				},
			},
		});

		hint.show();
		hint.adjustPosition();
	}

	hideTooltip()
	{
		const hint: Popup = PopupManager.getPopupById('sidepanel-toolbar-item-hint');
		if (hint)
		{
			hint.close();
		}
	}

	handleMouseEnter(): void
	{
		this.showTooltip();
	}

	handleMouseLeave(): void
	{
		this.hideTooltip();
	}

	handleRemoveBtnClick(event): void
	{
		event.stopPropagation();
		this.emit('onRemove');
	}

	toJSON(): JsonObject
	{
		return {
			title: this.getTitle(),
			url: this.getUrl(),
			entityType: this.getEntityType(),
			entityId: this.getEntityId(),
		};
	}
}
