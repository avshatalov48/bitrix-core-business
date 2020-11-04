import { Type, Loc } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';

import ItemNode from './item-node';
import SearchIndex from '../search/search-index';
import Entity from '../entity/entity';

import type Dialog from '../dialog/dialog';
import type { ItemOptions } from './item-options';
import type { ItemNodeOptions } from './item-node-options';
import type { ItemBadgeOptions } from './item-badge-options';
import ItemBadge from './item-badge';
import type { TagItemOptions } from '../tag-selector/tag-item-options';

/**
 * @memberof BX.UI.EntitySelector
 * @package ui.entity-selector
 */
export default class Item extends EventEmitter
{
	id: string | number = null;
	entityId: string = null;
	entityType: string = null;

	title: string = '';
	subtitle: ?string = null;
	caption: ?string = null;
	supertitle: ?string = null;
	avatar: ?string = null;
	textColor: ?string = null;
	link: ?string = null;
	linkTitle: ?string = null;
	tagOptions: Map<string, any> = null;
	badges: ItemBadgeOptions[] = null;

	dialog: Dialog = null;
	nodes: Set<ItemNode> = new Set();
	selected: boolean = false;
	searchable: boolean = true;
	saveable: boolean = true;
	deselectable: boolean = true;
	hidden: boolean = false;
	searchIndex: { [key: string]: string[] } = null;
	customData: Map<string, any> = null;

	sort: number = null;
	contextSort: number = null;
	globalSort: number = null;

	constructor(itemOptions: ItemOptions)
	{
		super();
		this.setEventNamespace('BX.UI.EntitySelector.Item');

		const options: ItemOptions = Type.isPlainObject(itemOptions) ? itemOptions : {};
		if (!Type.isStringFilled(options.id) && !Type.isNumber(options.id))
		{
			throw new Error('EntitySelector.Item: "id" parameter is required.');
		}

		if (!Type.isStringFilled(options.entityId))
		{
			throw new Error('EntitySelector.Item: "entityId" parameter is required.');
		}

		this.id = options.id;
		this.entityId = options.entityId;
		this.entityType = Type.isStringFilled(options.entityType) ? options.entityType : 'default';
		this.selected = Type.isBoolean(options.selected) ? options.selected : false;
		this.customData =
			Type.isPlainObject(options.customData) ? new Map(Object.entries(options.customData)) : new Map()
		;

		this.tagOptions =
			Type.isPlainObject(options.tagOptions) ? new Map(Object.entries(options.tagOptions)) : new Map()
		;

		this.setTitle(options.title);
		this.setSubtitle(options.subtitle);
		this.setSupertitle(options.supertitle);
		this.setCaption(options.caption);
		this.setAvatar(options.avatar);
		this.setTextColor(options.textColor);
		this.setLink(options.link);
		this.setLinkTitle(options.linkTitle);
		this.setBadges(options.badges);

		this.setSearchable(options.searchable);
		this.setSaveable(options.saveable);
		this.setDeselectable(options.deselectable);
		this.setHidden(options.hidden);
		this.setContextSort(options.contextSort);
		this.setGlobalSort(options.globalSort);
		this.setSort(options.sort);
	}

	getId(): string | number
	{
		return this.id;
	}

	getEntityId(): string
	{
		return this.entityId;
	}

	getEntity(): ?Entity
	{
		let entity = this.getDialog().getEntity(this.getEntityId());
		if (entity === null)
		{
			entity = new Entity({ id: this.getEntityId() });
			this.getDialog().addEntity(entity);
		}

		return entity;
	}

	getEntityType(): string
	{
		return this.entityType;
	}

	getTitle(): string
	{
		return this.title;
	}

	setTitle(title: string): void
	{
		if (Type.isStringFilled(title))
		{
			this.title = title;

			if (this.isRendered())
			{
				for (const node of this.getNodes())
				{
					node.render();
				}
			}
		}
	}

	getSubtitle(): ?string
	{
		return this.subtitle !== null ? this.subtitle : this.getEntity().getItemOption(this, 'subtitle');
	}

	setSubtitle(subtitle: string): void
	{
		if (Type.isString(subtitle) || subtitle === null)
		{
			this.subtitle = subtitle;

			if (this.isRendered())
			{
				for (const node of this.getNodes())
				{
					node.render();
				}
			}
		}
	}

	getSupertitle(): ?string
	{
		return this.supertitle !== null ? this.supertitle : this.getEntity().getItemOption(this, 'supertitle');
	}

	setSupertitle(supertitle: string): void
	{
		if (Type.isString(supertitle) || supertitle === null)
		{
			this.supertitle = supertitle;

			if (this.isRendered())
			{
				for (const node of this.getNodes())
				{
					node.render();
				}
			}
		}
	}

	getAvatar(): ?string
	{
		return this.avatar !== null ? this.avatar : this.getEntity().getItemOption(this, 'avatar');
	}

	setAvatar(avatar: ?string): void
	{
		if (Type.isString(avatar) || avatar === null)
		{
			this.avatar = avatar;

			if (this.isRendered())
			{
				for (const node of this.getNodes())
				{
					node.render();
				}
			}
		}
	}

	getTextColor(): ?string
	{
		return this.textColor !== null ? this.textColor : this.getEntity().getItemOption(this, 'textColor');
	}

	setTextColor(textColor: ?string): void
	{
		if (Type.isString(textColor) || textColor === null)
		{
			this.textColor = textColor;

			if (this.isRendered())
			{
				for (const node of this.getNodes())
				{
					node.render();
				}
			}
		}
	}

	getCaption(): ?string
	{
		return this.caption !== null ? this.caption : this.getEntity().getItemOption(this, 'caption');
	}

	setCaption(caption: ?string): void
	{
		if (Type.isString(caption) || caption === null)
		{
			this.caption = caption;

			if (this.isRendered())
			{
				for (const node of this.getNodes())
				{
					node.render();
				}
			}
		}
	}

	getLink(): ?string
	{
		const link = this.link !== null ? this.link : this.getEntity().getItemOption(this, 'link');

		return this.replaceMacros(link);
	}

	setLink(link: ?string): void
	{
		if (Type.isString(link) || link === null)
		{
			this.link = link;
		}
	}

	getLinkTitle(): ?string
	{
		if (this.linkTitle !== null)
		{
			return this.linkTitle;
		}

		const linkTitle = this.getEntity().getItemOption(this, 'linkTitle');

		return linkTitle !== null ? linkTitle : Loc.getMessage('UI_SELECTOR_ITEM_LINK_TITLE');
	}

	setLinkTitle(linkTitle: ?string): void
	{
		if (Type.isString(linkTitle) || linkTitle === null)
		{
			this.linkTitle = linkTitle;
		}
	}

	getBadges(): ItemBadge[]
	{
		if (this.badges !== null)
		{
			return this.badges;
		}

		const badges = this.getEntity().getBadges(this);
		if (Type.isArray(badges))
		{
			this.setBadges(badges);
		}
		else
		{
			this.badges = [];
		}

		return this.badges;
	}

	setBadges(badges: ?ItemBadgeOptions[]): void
	{
		if (Type.isArray(badges))
		{
			this.badges = [];
			badges.forEach(badge => {
				this.badges.push(new ItemBadge(badge));
			});
		}
		else if (badges === null)
		{
			this.badges = null;
		}
	}

	/**
	 * @internal
	 */
	setDialog(dialog: Dialog): void
	{
		this.dialog = dialog;
	}

	getDialog(): Dialog
	{
		return this.dialog;
	}

	createNode(nodeOptions: ItemNodeOptions): ItemNode
	{
		const itemNode = new ItemNode(this, nodeOptions);
		this.nodes.add(itemNode);

		return itemNode;
	}

	removeNode(node: ItemNode): void
	{
		this.nodes.delete(node);
	}

	getNodes(): Set<ItemNode>
	{
		return this.nodes;
	}

	select(preselectedMode: boolean = false): void
	{
		if (this.selected)
		{
			return;
		}

		const dialog = this.getDialog();
		const emitEvents = dialog && !preselectedMode;

		if (emitEvents)
		{
			const event = new BaseEvent({ data: { item: this } });
			dialog.emit('Item:onBeforeSelect', event);
			if (event.isDefaultPrevented())
			{
				return;
			}
		}

		this.selected = true;

		if (dialog)
		{
			dialog.handleItemSelect(this, !preselectedMode);
		}

		if (this.isRendered())
		{
			this.getNodes().forEach((node: ItemNode) => {
				node.select();
			});
		}

		if (emitEvents)
		{
			dialog.emit('Item:onSelect', { item: this });
			dialog.saveRecentItem(this);
		}
	}

	deselect(): void
	{
		if (!this.selected || !this.isDeselectable())
		{
			return;
		}

		const dialog = this.getDialog();
		if (dialog)
		{
			const event = new BaseEvent({ data: { item: this } });
			dialog.emit('Item:onBeforeDeselect', event);
			if (event.isDefaultPrevented())
			{
				return;
			}
		}

		this.selected = false;
		if (dialog)
		{
			dialog.handleItemDeselect(this);
		}

		if (this.isRendered())
		{
			this.getNodes().forEach(node => {
				node.deselect();
			});
		}

		if (dialog)
		{
			dialog.emit('Item:onDeselect', { item: this });

			if (dialog.getTagSelector())
			{
				dialog.getTagSelector().removeTag({
					id: this.getId(),
					entityId: this.getEntityId()
				});
			}
		}
	}

	isSelected(): boolean
	{
		return this.selected;
	}

	setSearchable(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.searchable = flag;
		}
	}

	isSearchable(): boolean
	{
		return this.searchable;
	}

	setSaveable(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.saveable = flag;
		}
	}

	isSaveable(): boolean
	{
		return this.saveable;
	}

	setDeselectable(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.deselectable = flag;

			if (this.getDialog() && this.getDialog().getTagSelector())
			{
				const tag = this.getDialog().getTagSelector().getTag({
					id: this.getId(),
					entityId: this.getEntityId()
				});

				if (tag)
				{
					tag.setDeselectable(flag);
				}
			}
		}
	}

	isDeselectable(): boolean
	{
		return this.deselectable;
	}

	setHidden(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.hidden = flag;
		}
	}

	isHidden(): boolean
	{
		return this.hidden;
	}

	setContextSort(sort: ?number): void
	{
		if (Type.isNumber(sort) || sort === null)
		{
			this.contextSort = sort;
		}
	}

	getContextSort(): ?number
	{
		return this.contextSort;
	}

	setGlobalSort(sort: ?number): void
	{
		if (Type.isNumber(sort) || sort === null)
		{
			this.globalSort = sort;
		}
	}

	getGlobalSort(): ?number
	{
		return this.globalSort;
	}

	setSort(sort: ?number): void
	{
		if (Type.isNumber(sort) || sort === null)
		{
			this.sort = sort;
		}
	}

	getSort(): ?number
	{
		return this.sort;
	}

	getSearchIndex(): SearchIndex
	{
		if (this.searchIndex === null)
		{
			this.searchIndex = SearchIndex.create(this);
		}

		return this.searchIndex;
	}

	resetSearchIndex(): void
	{
		this.searchIndex = null;
	}

	getCustomData(): Map<string, any>
	{
		return this.customData;
	}

	isRendered(): boolean
	{
		return this.getDialog() && this.getDialog().isRendered();
	}

	getTagOptions(): Map<string, any>
	{
		return this.tagOptions;
	}

	getTagOption(option: string, useEntityOptions: boolean = true): any
	{
		const value = this.getTagOptions().get(option);

		if (!Type.isUndefined(value))
		{
			return value;
		}
		else if (useEntityOptions !== false)
		{
			return this.getEntity().getTagOption(this, option);
		}

		return null;
	}

	getTagGlobalOption(propName: string): any
	{
		let value = null;

		if (this.getTagOption(propName, false) !== null)
		{
			value = this.getTagOption(propName);
		}
		else if (this[propName] !== null)
		{
			value = this[propName];
		}
		else if (this.getEntity().getTagOption(this, propName) !== null)
		{
			value = this.getEntity().getTagOption(this, propName);
		}
		else
		{
			value = this.getEntity().getItemOption(this, propName);
		}

		return value;
	}

	getTagAvatar(): ?string
	{
		return this.getTagGlobalOption('avatar');
	}

	getTagLink(): ?string
	{
		return this.replaceMacros(this.getTagGlobalOption('link'));
	}

	replaceMacros(str: string): string
	{
		if (!Type.isStringFilled(str))
		{
			return str;
		}

		return (
			str
				.replace(/#id#/i, this.getId())
				.replace(/#element_id#/i, this.getId())
		);
	}

	createTag(): TagItemOptions
	{
		return {
			id: this.getId(),
			entityId: this.getEntityId(),
			title: this.getTagOption('title', false) || this.getTitle(),
			deselectable: this.isDeselectable(),
			customData: this.getCustomData(),

			avatar: this.getTagAvatar(),
			link: this.getTagLink(),
			maxWidth: this.getTagOption('maxWidth'),
			textColor: this.getTagOption('textColor'),
			bgColor: this.getTagOption('bgColor'),
			fontWeight: this.getTagOption('fontWeight'),
		};
	}

	toJSON()
	{
		return {
			id: this.getId(),
			entityId: this.getEntityId(),
			entityType: this.getEntityType(),
			selected: this.isSelected(),
			deselectable: this.isDeselectable(),
			hidden: this.isHidden(),
			title: this.getTitle(),
			link: this.getLink(),
			linkTitle: this.getLinkTitle(),
			subtitle: this.getSubtitle(),
			supertitle: this.getSupertitle(),
			caption: this.getCaption(),
			avatar: this.getAvatar(),
			customData: this.getCustomData(),
		};
	}
}