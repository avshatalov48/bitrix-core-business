import { Type, Loc, Text } from 'main.core';
import { BaseEvent } from 'main.core.events';

import ItemNode from './item-node';
import SearchIndex from '../search/search-index';
import Entity from '../entity/entity';
import ItemBadge from './item-badge';
import TextNode from '../common/text-node';
import TypeUtils from '../common/type-utils';

import type Dialog from '../dialog/dialog';
import type { ItemOptions } from './item-options';
import type { ItemNodeOptions } from './item-node-options';
import type { ItemBadgeOptions } from './item-badge-options';
import type { TagItemOptions } from '../tag-selector/tag-item-options';
import type { TextNodeOptions } from '../common/text-node-options';
import type { CaptionOptions } from './caption-options';
import type { BadgesOptions } from './badges-options';
import type { AvatarOptions } from './avatar-options';

/**
 * @memberof BX.UI.EntitySelector
 * @package ui.entity-selector
 */
export default class Item
{
	id: string | number = null;
	entityId: string = null;
	entityType: string = null;

	title: ?TextNode = null;
	subtitle: ?TextNode = null;
	supertitle: ?TextNode = null;
	caption: ?TextNode = null;
	captionOptions: CaptionOptions = {};
	avatar: ?string = null;
	avatarOptions: ?AvatarOptions = null;
	textColor: ?string = null;
	link: ?string = null;
	linkTitle: ?TextNode = null;
	tagOptions: Map<string, any> = null;
	badges: ItemBadgeOptions[] = null;
	badgesOptions: BadgesOptions = {};

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
		this.entityId = options.entityId.toLowerCase();
		this.entityType = Type.isStringFilled(options.entityType) ? options.entityType : 'default';
		this.selected = Type.isBoolean(options.selected) ? options.selected : false;

		this.customData = TypeUtils.createMapFromOptions(options.customData);
		this.tagOptions = TypeUtils.createMapFromOptions(options.tagOptions);

		this.setTitle(options.title);
		this.setSubtitle(options.subtitle);
		this.setSupertitle(options.supertitle);
		this.setCaption(options.caption);
		this.setCaptionOptions(options.captionOptions);
		this.setAvatar(options.avatar);
		this.setAvatarOptions(options.avatarOptions);
		this.setTextColor(options.textColor);
		this.setLink(options.link);
		this.setLinkTitle(options.linkTitle);
		this.setBadges(options.badges);
		this.setBadgesOptions(options.badgesOptions);

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

	getEntity(): Entity
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
		const titleNode = this.getTitleNode();

		return titleNode !== null && !titleNode.isNullable() ? titleNode.getText() : '';
	}

	getTitleNode(): ?TextNode
	{
		return this.title;
	}

	setTitle(title: ?string | TextNodeOptions): void
	{
		if (Type.isStringFilled(title) || Type.isPlainObject(title) || title === null)
		{
			this.title = title === null ? null : new TextNode(title);

			this.resetSearchIndex();
			this.#renderNodes();
		}
	}

	getSubtitle(): ?string
	{
		const subtitleNode = this.getSubtitleNode();

		return subtitleNode !== null ? subtitleNode.getText() : null;
	}

	getSubtitleNode(): ?TextNode
	{
		return this.subtitle !== null ? this.subtitle : this.getEntityTextNode('subtitle');
	}

	setSubtitle(subtitle: ?string | TextNodeOptions): void
	{
		if (Type.isString(subtitle) || Type.isPlainObject(subtitle) || subtitle === null)
		{
			this.subtitle = subtitle === null ? null : new TextNode(subtitle);

			this.resetSearchIndex();
			this.#renderNodes();
		}
	}

	getSupertitle(): ?string
	{
		const supertitleNode = this.getSupertitleNode();

		return supertitleNode !== null ? supertitleNode.getText() : null;
	}

	getSupertitleNode(): ?TextNode
	{
		return this.supertitle !== null ? this.supertitle : this.getEntityTextNode('supertitle');
	}

	setSupertitle(supertitle: ?string | TextNodeOptions): void
	{
		if (Type.isString(supertitle) || Type.isPlainObject(supertitle) || supertitle === null)
		{
			this.supertitle = supertitle === null ? null : new TextNode(supertitle);

			this.resetSearchIndex();
			this.#renderNodes();
		}
	}

	getCaption(): ?string
	{
		const captionNode = this.getCaptionNode();

		return captionNode !== null ? captionNode.getText() : null;
	}

	getCaptionNode(): ?TextNode
	{
		return this.caption !== null ? this.caption : this.getEntityTextNode('caption');
	}

	setCaption(caption: ?string | TextNodeOptions): void
	{
		if (Type.isString(caption) || Type.isPlainObject(caption) || caption === null)
		{
			this.caption = caption === null ? null : new TextNode(caption);

			this.resetSearchIndex();
			this.#renderNodes();
		}
	}

	getCaptionOption(option: string): string | boolean | number | null
	{
		if (!Type.isUndefined(this.captionOptions[option]))
		{
			return this.captionOptions[option];
		}

		const captionOptions = this.getEntityItemOption('captionOptions');
		if (Type.isPlainObject(captionOptions) && !Type.isUndefined(captionOptions[option]))
		{
			return captionOptions[option];
		}

		return null;
	}

	setCaptionOption(option: string, value: string | boolean | number | null): void
	{
		if (Type.isStringFilled(option) && !Type.isUndefined(value))
		{
			this.captionOptions[option] = value;
			this.#renderNodes();
		}
	}

	setCaptionOptions(options: {[key: string]: any }): void
	{
		if (Type.isPlainObject(options))
		{
			Object.keys(options).forEach((option: string) => {
				this.setCaptionOption(option, options[option]);
			});
		}
	}

	getAvatar(): ?string
	{
		return this.avatar !== null ? this.avatar : this.getEntityItemOption('avatar');
	}

	setAvatar(avatar: ?string): void
	{
		if (Type.isString(avatar) || avatar === null)
		{
			this.avatar = avatar;
			this.#renderNodes();
		}
	}

	getAvatarOption(option: $Keys<AvatarOptions>): string | boolean | number | null
	{
		if (this.avatarOptions !== null && !Type.isUndefined(this.avatarOptions[option]))
		{
			return this.avatarOptions[option];
		}

		const avatarOptions = this.getEntityItemOption('avatarOptions');
		if (Type.isPlainObject(avatarOptions) && !Type.isUndefined(avatarOptions[option]))
		{
			return avatarOptions[option];
		}

		return null;
	}

	setAvatarOption(option: $Keys<AvatarOptions>, value: string | boolean | number | null): void
	{
		if (Type.isStringFilled(option) && !Type.isUndefined(value))
		{
			if (this.avatarOptions === null)
			{
				this.avatarOptions = {};
			}

			this.avatarOptions[option] = value;
			this.#renderNodes();
		}
	}

	setAvatarOptions(options: AvatarOptions): void
	{
		if (Type.isPlainObject(options))
		{
			Object.keys(options).forEach((option: string) => {
				this.setAvatarOption(option, options[option]);
			});
		}
	}

	getTextColor(): ?string
	{
		return this.textColor !== null ? this.textColor : this.getEntityItemOption('textColor');
	}

	setTextColor(textColor: ?string): void
	{
		if (Type.isString(textColor) || textColor === null)
		{
			this.textColor = textColor;
			this.#renderNodes();
		}
	}

	getLink(): ?string
	{
		const link = this.link !== null ? this.link : this.getEntityItemOption('link');

		return this.replaceMacros(link);
	}

	setLink(link: ?string): void
	{
		if (Type.isString(link) || link === null)
		{
			this.link = link;
			this.#renderNodes();
		}
	}

	getLinkTitle(): ?string
	{
		const linkTitleNode = this.getLinkTitleNode();

		return linkTitleNode !== null ? linkTitleNode.getText() : Loc.getMessage('UI_SELECTOR_ITEM_LINK_TITLE');
	}

	getLinkTitleNode(): ?TextNode
	{
		return this.linkTitle !== null ? this.linkTitle : this.getEntityTextNode('linkTitle');
	}

	setLinkTitle(linkTitle: ?string | TextNodeOptions): void
	{
		if (Type.isString(linkTitle) || Type.isPlainObject(linkTitle) || linkTitle === null)
		{
			this.linkTitle = linkTitle === null ? null : new TextNode(linkTitle);
			this.#renderNodes();
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

			this.#renderNodes();
		}
		else if (badges === null)
		{
			this.badges = null;
			this.#renderNodes();
		}
	}

	getBadgesOption(option: string): string | boolean | number | null
	{
		if (!Type.isUndefined(this.badgesOptions[option]))
		{
			return this.badgesOptions[option];
		}

		const badgesOptions = this.getEntityItemOption('badgesOptions');
		if (Type.isPlainObject(badgesOptions) && !Type.isUndefined(badgesOptions[option]))
		{
			return badgesOptions[option];
		}

		return null;
	}

	setBadgesOption(option: string, value: string | boolean | number | null): void
	{
		if (Type.isStringFilled(option) && !Type.isUndefined(value))
		{
			this.badgesOptions[option] = value;
			this.#renderNodes();
		}
	}

	setBadgesOptions(options: {[key: string]: any }): void
	{
		if (Type.isPlainObject(options))
		{
			Object.keys(options).forEach((option: string) => {
				this.setBadgesOption(option, options[option]);
			});
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
		if (!this.selected)
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

		if (this.isRendered())
		{
			this.getNodes().forEach(node => {
				node.deselect();
			});
		}

		if (dialog)
		{
			dialog.handleItemDeselect(this);
			dialog.emit('Item:onDeselect', { item: this });
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

			if (this.isRendered())
			{
				this.getNodes().forEach((node: ItemNode) => {
					node.setHidden(flag);
				});
			}
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

	#renderNodes(): void
	{
		if (this.isRendered())
		{
			this.getNodes().forEach((node: ItemNode) => {
				node.render();
			});
		}
	}

	getEntityItemOption(option): any
	{
		return this.getEntity().getItemOption(option, this.getEntityType());
	}

	getEntityTagOption(option): any
	{
		return this.getEntity().getTagOption(option, this.getEntityType());
	}

	getEntityTextNode(option): any
	{
		return this.getEntity().getOptionTextNode(option, this.getEntityType());
	}

	getTagOptions(): Map<string, any>
	{
		return this.tagOptions;
	}

	getTagOption(option: string): any
	{
		const value = this.getTagOptions().get(option);

		if (!Type.isUndefined(value))
		{
			return value;
		}

		return null;
	}

	getTagGlobalOption(option: string, useItemOptions: boolean = false): any
	{
		if (!Type.isStringFilled(option))
		{
			return null;
		}

		let value = this.getTagOption(option);

		if (value === null && useItemOptions === true && this[option] !== null)
		{
			value = this[option];
		}

		if (value === null && this.getDialog().getTagSelector())
		{
			const fn = `getTag${Text.toPascalCase(option)}`;
			if (Type.isFunction(this.getDialog().getTagSelector()[fn]))
			{
				value = this.getDialog().getTagSelector()[fn]();
			}
		}

		if (value === null)
		{
			value = this.getEntityTagOption(option);
		}

		if (value === null && useItemOptions === true)
		{
			value = this.getEntityItemOption(option);
		}

		return value;
	}

	getTagBgColor(): ?string
	{
		return this.getTagGlobalOption('bgColor');
	}

	getTagTextColor(): ?string
	{
		return this.getTagGlobalOption('textColor');
	}

	getTagMaxWidth(): ?number
	{
		return this.getTagGlobalOption('maxWidth');
	}

	getTagFontWeight(): ?string
	{
		return this.getTagGlobalOption('fontWeight');
	}

	getTagAvatar(): ?string
	{
		return this.getTagGlobalOption('avatar', true);
	}

	getTagAvatarOptions(): ?AvatarOptions
	{
		return this.getTagGlobalOption('avatarOptions', true);
	}

	getTagLink(): ?string
	{
		return this.replaceMacros(this.getTagGlobalOption('link', true));
	}

	/**
	 * @internal
	 */
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

	/**
	 * @internal
	 */
	createTag(): TagItemOptions
	{
		return {
			id: this.getId(),
			entityId: this.getEntityId(),
			entityType: this.getEntityType(),
			title: this.getTagOption('title') || (this.getTitleNode() && this.getTitleNode().toJSON()) || '',
			deselectable: this.isDeselectable(),
			avatar: this.getTagAvatar(),
			avatarOptions: this.getTagAvatarOptions(),
			link: this.getTagLink(),
			maxWidth: this.getTagMaxWidth(),
			textColor: this.getTagTextColor(),
			bgColor: this.getTagBgColor(),
			fontWeight: this.getTagFontWeight(),
			onclick: this.getTagOption('onclick'),
		};
	}

	getAjaxJson(): { [key: string]: any }
	{
		return this.toJSON();
	}

	toJSON(): { [key: string]: any }
	{
		return {
			id: this.getId(),
			entityId: this.getEntityId(),
			entityType: this.getEntityType(),
			selected: this.isSelected(),
			deselectable: this.isDeselectable(),
			searchable: this.isSearchable(),
			saveable: this.isSaveable(),
			hidden: this.isHidden(),
			title: this.getTitleNode(),
			link: this.getLink(),
			linkTitle: this.getLinkTitleNode(),
			subtitle: this.getSubtitleNode(),
			supertitle: this.getSupertitleNode(),
			caption: this.getCaptionNode(),
			avatar: this.getAvatar(),
			textColor: this.getTextColor(),
			sort: this.getSort(),
			contextSort: this.getContextSort(),
			globalSort: this.getGlobalSort(),
			customData: TypeUtils.convertMapToObject(this.getCustomData()),
			tagOptions: TypeUtils.convertMapToObject(this.getTagOptions()),
			badges: this.getBadges()
		};
	}
}
