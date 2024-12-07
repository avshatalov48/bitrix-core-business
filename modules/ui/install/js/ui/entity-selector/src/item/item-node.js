import { ajax as Ajax, Cache, Dom, Runtime, Tag, Type, Browser, Event } from 'main.core';
import { OrderedArray } from 'main.core.collections';
import { Loader } from 'main.loader';

import ItemNodeComparator from './item-node-comparator';
import Highlighter from '../search/highlighter';
import ItemBadge from './item-badge';
import MatchField from '../search/match-field';
import TextNode from '../common/text-node';
import Animation from '../common/animation';
import Item from './item';
import encodeUrl from '../common/encode-url';

import type Tab from '../dialog/tabs/tab';
import type Dialog from '../dialog/dialog';
import type { ItemOptions } from './item-options';
import type { ItemNodeOptions } from './item-node-options';
import type { ItemBadgeOptions } from './item-badge-options';
import type { TextNodeOptions } from '../common/text-node-options';
import type { CaptionOptions } from './caption-options';
import type { BadgesOptions } from './badges-options';
import type { AvatarOptions } from './avatar-options';

export class RenderMode
{
	static PARTIAL = 'partial';
	static OVERRIDE = 'override';
}

export default class ItemNode
{
	item: Item = null;
	tab: Tab = null;
	cache = new Cache.MemoryCache();
	parentNode: ItemNode = null;

	children: OrderedArray<ItemNode> = null;
	childItems: WeakMap<Item, ItemNode> = new WeakMap(); // for the fast access

	loaded: boolean = false;
	dynamic: boolean = false;
	dynamicPromise: ?Promise = null;
	loader: Loader = null;
	open: boolean = false;
	autoOpen: boolean = false;
	focused: boolean = false;

	renderMode: RenderMode = RenderMode.PARTIAL;
	title: ?TextNode = null;
	subtitle: ?TextNode = null;
	supertitle: ?TextNode = null;
	caption: ?TextNode = null;
	captionOptions: CaptionOptions = {};
	avatar: ?string = null;
	avatarOptions: ?AvatarOptions = null;
	link: ?string = null;
	linkTitle: ?TextNode = null;
	textColor: ?string = null;
	badges: ItemBadgeOptions[] = null;
	badgesOptions: BadgesOptions = {};
	hidden: boolean = false;

	highlights: MatchField[] = [];

	rendered: false;
	renderWithDebounce = Runtime.debounce(this.render, 50, this);

	constructor(item: Item, nodeOptions: ItemNodeOptions)
	{
		const options: ItemNodeOptions = Type.isPlainObject(nodeOptions) ? nodeOptions : {};

		if (Type.isObject(item))
		{
			this.item = item;
		}

		let comparator = null;
		if (Type.isFunction(options.itemOrder))
		{
			comparator = options.itemOrder;
		}
		else if (Type.isPlainObject(options.itemOrder))
		{
			comparator = ItemNodeComparator.makeMultipleComparator(options.itemOrder);
		}

		this.children = new OrderedArray(comparator);

		this.renderMode = options.renderMode === RenderMode.OVERRIDE ? RenderMode.OVERRIDE : RenderMode.PARTIAL;
		if (this.renderMode === RenderMode.OVERRIDE)
		{
			this.setTitle('');
			this.setSubtitle('');
			this.setSupertitle('');
			this.setCaption('');
			this.setLinkTitle('');

			this.avatar = '';
			this.avatarOptions = {
				bgSize: null,
				bgColor: null,
				bgImage: null,
				border: null,
				borderRadius: null,
			};
			this.textColor = '';
			this.link = '';
			this.badges = [];
			this.captionOptions = {
				fitContent: null,
				maxWidth: null,
				justifyContent: null,
			};
			this.badgesOptions = {
				fitContent: null,
				maxWidth: null,
				justifyContent: null,
			};
		}

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

		this.setDynamic(options.dynamic);
		this.setOpen(options.open);
	}

	getItem(): Item
	{
		return this.item;
	}

	isRoot(): boolean
	{
		return this.getParentNode() === null;
	}

	getDialog(): Dialog
	{
		return this.getTab().getDialog();
	}

	setTab(tab: Tab): void
	{
		this.tab = tab;
	}

	getTab(): Tab
	{
		return this.tab;
	}

	getParentNode(): ?ItemNode
	{
		return this.parentNode;
	}

	setParentNode(parentNode: ItemNode): void
	{
		this.parentNode = parentNode;
	}

	getNextSibling(): ?ItemNode
	{
		if (!this.getParentNode())
		{
			return null;
		}

		const siblings = this.getParentNode().getChildren();
		const index = siblings.getIndex(this);

		return siblings.getByIndex(index + 1);
	}

	getPreviousSibling(): ?ItemNode
	{
		if (!this.getParentNode())
		{
			return null;
		}

		const siblings = this.getParentNode().getChildren();
		const index = siblings.getIndex(this);

		return siblings.getByIndex(index - 1);
	}

	addChildren(children: ItemOptions[]): void
	{
		if (!Type.isArray(children))
		{
			return;
		}

		children.forEach((childOptions: ItemOptions) => {
			delete childOptions.tabs;
			const childItem = this.getDialog().addItem(childOptions);

			const childNode = this.addItem(childItem, childOptions.nodeOptions);
			childNode.addChildren(childOptions.children);
		});
	}

	addChild(child: ItemNode): ItemNode
	{
		if (!(child instanceof ItemNode))
		{
			throw new Error('EntitySelector.ItemNode: an item must be an instance of EntitySelector.ItemNode.');
		}

		if (this.isChildOf(child) || child === this)
		{
			throw new Error('EntitySelector.ItemNode: a child item cannot be a parent of current item.');
		}

		if (this.getChildren().has(child) || this.childItems.has(child.getItem()))
		{
			return null;
		}

		this.getChildren().add(child);
		this.childItems.set(child.getItem(), child);

		child.setTab(this.getTab());
		child.setParentNode(this);

		if (this.isRendered())
		{
			this.renderWithDebounce();
		}

		return child;
	}

	getDepthLevel(): number
	{
		return this.isRoot() ? 0 : this.getParentNode().getDepthLevel() + 1;
	}

	addItem(item: Item, nodeOptions: ItemNodeOptions): ItemNode
	{
		let itemNode = this.childItems.get(item);
		if (!itemNode)
		{
			itemNode = item.createNode(nodeOptions);
			this.addChild(itemNode);
		}

		return itemNode;
	}

	addItems(items: Item[] | Array<[Item, ItemNodeOptions]>): void
	{
		if (Type.isArray(items))
		{
			this.disableRender();

			items.forEach((item: Item | [Item, ItemNodeOptions]) => {
				if (Type.isArray(item) && item.length === 2)
				{
					this.addItem(item[0], item[1]);
				}
				else if (item instanceof Item)
				{
					this.addItem(item);
				}
			});

			this.enableRender();

			if (this.isRendered())
			{
				this.renderWithDebounce();
			}
		}
	}

	hasItem(item: Item): boolean
	{
		return this.childItems.has(item);
	}

	removeChild(child: ItemNode): boolean
	{
		if (!this.getChildren().has(child))
		{
			return false;
		}

		child.removeChildren();

		if (child.isFocused())
		{
			child.unfocus();
		}

		child.setParentNode(null);
		child.getItem().removeNode(child);

		this.getChildren().delete(child);
		this.childItems.delete(child.getItem());

		if (this.isRendered())
		{
			Dom.remove(child.getOuterContainer());
		}

		return true;
	}

	removeChildren(): void
	{
		if (!this.hasChildren())
		{
			return;
		}

		this.getChildren().forEach((node: ItemNode) => {

			node.removeChildren();

			if (node.isFocused())
			{
				node.unfocus();
			}

			node.setParentNode(null);
			node.getItem().removeNode(node);
		});

		this.getChildren().clear();
		this.childItems = new WeakMap();

		if (this.isRendered())
		{
			if (Browser.isIE())
			{
				Dom.clean(this.getChildrenContainer());
			}
			else
			{
				this.getChildrenContainer().textContent = '';
			}
		}
	}

	hasChild(child: ItemNode): boolean
	{
		return this.getChildren().has(child);
	}

	isChildOf(parent: ItemNode): boolean
	{
		let parentNode = this.getParentNode();
		while (parentNode !== null)
		{
			if (parentNode === parent)
			{
				return true;
			}

			parentNode = parentNode.getParentNode();
		}

		return false;
	}

	getFirstChild(): ?ItemNode
	{
		return this.children.getFirst();
	}

	getLastChild(): ?ItemNode
	{
		return this.children.getLast();
	}

	getChildren(): OrderedArray<ItemNode>
	{
		return this.children;
	}

	hasChildren(): boolean
	{
		return this.children.count() > 0;
	}

	loadChildren(): Promise
	{
		if (!this.isDynamic())
		{
			throw new Error('EntitySelector.ItemNode.loadChildren: an item node is not dynamic.');
		}

		if (this.dynamicPromise)
		{
			return this.dynamicPromise;
		}

		this.dynamicPromise = Ajax.runAction('ui.entityselector.getChildren', {
			json: {
				parentItem: this.getItem().getAjaxJson(),
				dialog: this.getDialog().getAjaxJson()
			},
			getParameters: {
				context: this.getDialog().getContext()
			}
		});

		this.dynamicPromise.then((response) => {
			if (response && response.data && Type.isPlainObject(response.data.dialog))
			{
				this.addChildren(response.data.dialog.items);
				this.render();
			}
			this.loaded = true;
		});

		this.dynamicPromise.catch((error) => {
			this.loaded = false;
			this.dynamicPromise = null;
			console.error(error);
		});

		return this.dynamicPromise;
	}

	setOpen(open: boolean): void
	{
		if (Type.isBoolean(open))
		{
			if (open && this.isDynamic() && !this.isLoaded())
			{
				this.setAutoOpen(true);
			}
			else
			{
				this.open = open;
			}
		}
	}

	isOpen(): boolean
	{
		return this.open;
	}

	isAutoOpen(): boolean
	{
		return this.autoOpen && this.isDynamic() && !this.isLoaded();
	}

	setAutoOpen(autoOpen: boolean): void
	{
		if (Type.isBoolean(autoOpen))
		{
			this.autoOpen = autoOpen;
		}
	}

	setDynamic(dynamic: boolean): void
	{
		if (Type.isBoolean(dynamic))
		{
			this.dynamic = dynamic;
		}
	}

	isDynamic(): boolean
	{
		return this.dynamic;
	}

	isLoaded(): boolean
	{
		return this.loaded;
	}

	getLoader(): Loader
	{
		if (this.loader === null)
		{
			this.loader = new Loader({
				target: this.getIndicatorContainer(),
				size: 30
			});
		}

		return this.loader;
	}

	showLoader(): void
	{
		void this.getLoader().show();
		Dom.addClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	}

	hideLoader(): void
	{
		void this.getLoader().hide();
		Dom.removeClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	}

	destroyLoader(): void
	{
		this.getLoader().destroy();
		this.loader = null;
		Dom.removeClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	}

	expand(): void
	{
		if (this.isOpen() || (!this.hasChildren() && !this.isDynamic()))
		{
			return;
		}

		if (this.isDynamic() && !this.isLoaded())
		{
			this.loadChildren().then(() => {
				this.destroyLoader();
				this.expand();
			});

			this.showLoader();

			return;
		}

		Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-open');
		Dom.style(this.getChildrenContainer(), 'height', '0px');
		Dom.style(this.getChildrenContainer(), 'opacity', 0);

		requestAnimationFrame(() => {
			requestAnimationFrame(() => {
				Dom.style(this.getChildrenContainer(), 'height', `${this.getChildrenContainer().scrollHeight}px`);
				Dom.style(this.getChildrenContainer(), 'opacity', 1);

				Animation.handleTransitionEnd(this.getChildrenContainer(), 'height').then(() => {
					Dom.style(this.getChildrenContainer(), 'height', null);
					Dom.style(this.getChildrenContainer(), 'opacity', null);
					Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-open');
					this.setOpen(true);
				});
			});
		});
	}

	collapse(): void
	{
		if (!this.isOpen())
		{
			return;
		}

		Dom.style(this.getChildrenContainer(), 'height', `${this.getChildrenContainer().offsetHeight}px`);

		requestAnimationFrame(() => {
			requestAnimationFrame(() => {
				Dom.style(this.getChildrenContainer(), 'height', '0px');
				Dom.style(this.getChildrenContainer(), 'opacity', 0);

				Animation.handleTransitionEnd(this.getChildrenContainer(), 'height').then(() => {
					Dom.style(this.getChildrenContainer(), 'height', null);
					Dom.style(this.getChildrenContainer(), 'opacity', null);
					Dom.removeClass(this.getOuterContainer(), 'ui-selector-item-box-open');
					this.setOpen(false);
				});
			});
		});
	}

	render(appendChildren = false): void
	{
		if (this.isRoot())
		{
			this.renderRoot(appendChildren);
			return;
		}

		const titleNode = this.getTitleNode();
		if (titleNode)
		{
			titleNode.renderTo(this.getTitleContainer());
		}
		else
		{
			this.getTitleContainer().textContent = '';
		}

		const supertitleNode = this.getSupertitleNode();
		if (supertitleNode)
		{
			supertitleNode.renderTo(this.getSupertitleContainer());
		}
		else
		{
			this.getSupertitleContainer().textContent = '';
		}

		const subtitleNode = this.getSubtitleNode();
		if (subtitleNode)
		{
			subtitleNode.renderTo(this.getSubtitleContainer());
		}
		else
		{
			this.getSubtitleContainer().textContent = '';
		}

		const captionNode = this.getCaptionNode();
		if (captionNode)
		{
			captionNode.renderTo(this.getCaptionContainer());
		}
		else
		{
			this.getCaptionContainer().textContent = '';
		}

		const captionFitContent = this.getCaptionOption('fitContent');
		if (Type.isBoolean(captionFitContent))
		{
			Dom.style(this.getCaptionContainer(), 'flex-shrink', captionFitContent ? 0 : null);
		}

		const captionJustifyContent = this.getCaptionOption('justifyContent');
		if (Type.isStringFilled(captionJustifyContent) || captionJustifyContent === null)
		{
			Dom.style(
				this.getCaptionContainer(),
				{
					flexGrow: captionJustifyContent ? '1' : null,
					textAlign: captionJustifyContent || null,
				},
			);
		}

		const captionMaxWidth = this.getCaptionOption('maxWidth');
		if (Type.isString(captionMaxWidth) || Type.isNumber(captionMaxWidth))
		{
			Dom.style(
				this.getCaptionContainer(),
				'max-width',
				Type.isNumber(captionMaxWidth) ? `${captionMaxWidth}px` : captionMaxWidth
			);
		}

		if (Type.isStringFilled(this.getTextColor()))
		{
			this.getTitleContainer().style.color = this.getTextColor();
		}
		else
		{
			this.getTitleContainer().style.removeProperty('color');
		}

		const avatar = this.getAvatar();
		if (Type.isStringFilled(avatar))
		{
			this.getAvatarContainer().style.backgroundImage = `url('${encodeUrl(avatar)}')`;
		}
		else
		{
			const bgImage = this.getAvatarOption('bgImage');
			if (Type.isStringFilled(bgImage))
			{
				this.getAvatarContainer().style.backgroundImage = bgImage;
			}
			else
			{
				this.getAvatarContainer().style.removeProperty('background-image');
			}
		}

		const bgColor = this.getAvatarOption('bgColor');
		if (Type.isStringFilled(bgColor))
		{
			this.getAvatarContainer().style.backgroundColor = bgColor;
		}
		else
		{
			this.getAvatarContainer().style.removeProperty('background-color');
		}

		const bgSize = this.getAvatarOption('bgSize');
		if (Type.isStringFilled(bgSize))
		{
			this.getAvatarContainer().style.backgroundSize = bgSize;
		}
		else
		{
			this.getAvatarContainer().style.removeProperty('background-size');
		}

		const border = this.getAvatarOption('border');
		if (Type.isStringFilled(border))
		{
			this.getAvatarContainer().style.border = border;
		}
		else
		{
			this.getAvatarContainer().style.removeProperty('border');
		}

		const borderRadius = this.getAvatarOption('borderRadius');
		if (Type.isStringFilled(borderRadius))
		{
			this.getAvatarContainer().style.borderRadius = borderRadius;
		}
		else
		{
			this.getAvatarContainer().style.removeProperty('border-radius');
		}

		Dom.clean(this.getBadgeContainer());
		this.getBadges().forEach((badge: ItemBadge) => {
			badge.renderTo(this.getBadgeContainer());
		});

		const badgesFitContent = this.getBadgesOption('fitContent');
		if (Type.isBoolean(badgesFitContent))
		{
			Dom.style(this.getBadgeContainer(), 'flex-shrink', badgesFitContent ? 0 : null);
		}

		const badgesJustifyContent = this.getBadgesOption('justifyContent');
		if (Type.isStringFilled(badgesJustifyContent) || badgesJustifyContent === null)
		{
			Dom.style(
				this.getBadgeContainer(),
				{
					flexGrow: badgesJustifyContent ? '1' : null,
					justifyContent: badgesJustifyContent || null,
				},
			);
		}

		const badgesMaxWidth = this.getBadgesOption('maxWidth');
		if (Type.isString(badgesMaxWidth) || Type.isNumber(badgesMaxWidth))
		{
			Dom.style(
				this.getBadgeContainer(),
				'max-width',
				Type.isNumber(badgesMaxWidth) ? `${badgesMaxWidth}px` : badgesMaxWidth
			);
		}

		const linkTitleNode = this.getLinkTitleNode();
		if (linkTitleNode)
		{
			linkTitleNode.renderTo(this.getLinkTextContainer());
		}
		else
		{
			this.getLinkTextContainer().textContent = '';
		}

		if (this.hasChildren() || this.isDynamic())
		{
			Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-has-children');
			if (this.getDepthLevel() >= this.getTab().getItemMaxDepth())
			{
				Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-max-depth');
			}
		}
		else if (this.getOuterContainer().classList.contains('ui-selector-item-box-has-children'))
		{
			Dom.removeClass(
				this.getOuterContainer(),
				['ui-selector-item-box-has-children', 'ui-selector-item-box-max-depth']
			);
		}

		if (this.hasChildren())
		{
			const hasVisibleChild = this.getChildren().getAll().some((child: ItemNode) => {
				return child.isHidden() !== true;
			});

			if (!hasVisibleChild)
			{
				this.#setHidden(true);
			}
		}

		this.toggleVisibility();
		this.highlight();
		this.renderChildren(appendChildren);

		if (this.isAutoOpen())
		{
			this.setAutoOpen(false);

			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					this.expand();
				});
			});
		}

		this.rendered = true;
	}

	/**
	 * @private
	 */
	renderRoot(appendChildren = false): void
	{
		this.renderChildren(appendChildren);
		this.rendered = true;

		const stub = this.getTab().getStub();
		if (stub && stub.isAutoShow() && (this.getDialog().isLoaded() || !this.getDialog().hasDynamicLoad()))
		{
			if (this.hasChildren())
			{
				stub.hide();
			}
			else
			{
				stub.show();
			}
		}
	}

	/**
	 * @private
	 */
	renderChildren(appendChildren = false): void
	{
		if (!appendChildren)
		{
			if (Browser.isIE())
			{
				Dom.clean(this.getChildrenContainer());
			}
			else
			{
				this.getChildrenContainer().textContent = '';
			}
		}

		if (this.hasChildren())
		{
			let previousSibling: ItemNode = null;
			this.getChildren().forEach((child: ItemNode) => {
				child.render(appendChildren);
				const container = child.getOuterContainer();

				if (!appendChildren)
				{
					Dom.append(container, this.getChildrenContainer());
				}
				if (!container.parentNode)
				{
					if (previousSibling !== null)
					{
						Dom.insertAfter(container, previousSibling.getOuterContainer());
					}
					else
					{
						Dom.append(container, this.getChildrenContainer());
					}
				}

				previousSibling = child;
			});
		}
	}

	isRendered(): boolean
	{
		return this.rendered && this.getDialog() && this.getDialog().isRendered();
	}

	enableRender(): void
	{
		this.rendered = true;
	}

	disableRender(): void
	{
		this.rendered = false;
	}

	getRenderMode(): RenderMode
	{
		return this.renderMode;
	}

	isHidden(): boolean
	{
		return this.hidden === true || this.getItem().isHidden() === true;
	}

	setHidden(flag: boolean): void
	{
		if (!Type.isBoolean(flag) || this.isRoot())
		{
			return;
		}

		this.#setHidden(flag);

		if (this.isRendered())
		{
			this.toggleVisibility();

			let parentNode = this.getParentNode();
			const isHidden = this.isHidden();
			while (parentNode.isRoot() === false)
			{
				if (isHidden)
				{
					const hasVisibleChild = parentNode.getChildren().getAll().some((child: ItemNode) => {
						return child.isHidden() !== true;
					});

					if (!hasVisibleChild)
					{
						parentNode.#setHidden(true);
					}

					parentNode.toggleVisibility();
				}
				else
				{
					parentNode.#setHidden(false);
					parentNode.toggleVisibility();
					if (parentNode.isHidden())
					{
						break;
					}
				}

				parentNode = parentNode.getParentNode();
			}
		}
	}

	#setHidden(flag: boolean): void
	{
		if (Type.isBoolean(flag) && !this.isRoot())
		{
			this.hidden = flag;
		}
	}

	toggleVisibility(): boolean
	{
		if (this.isHidden())
		{
			Dom.addClass(this.getOuterContainer(), '--hidden');
		}
		else if (this.getOuterContainer().classList.contains('--hidden'))
		{
			Dom.removeClass(this.getOuterContainer(), '--hidden');
		}
	}

	getTitle(): string
	{
		const titleNode = this.getTitleNode();

		return titleNode !== null ? titleNode.getText() : null;
	}

	getTitleNode(): ?TextNode
	{
		return this.title !== null ? this.title: this.getItem().getTitleNode();
	}

	setTitle(title: string | TextNodeOptions): void
	{
		if (Type.isString(title) || Type.isPlainObject(title))
		{
			this.title = new TextNode(title);
		}
		else if (title === null)
		{
			this.title = null;
		}
	}

	getSubtitle(): ?string
	{
		const subtitleNode = this.getSubtitleNode();

		return subtitleNode !== null ? subtitleNode.getText() : null;
	}

	getSubtitleNode(): ?TextNode
	{
		return this.subtitle !== null ? this.subtitle: this.getItem().getSubtitleNode();
	}

	setSubtitle(subtitle: string | TextNodeOptions): void
	{
		if (Type.isString(subtitle) || Type.isPlainObject(subtitle))
		{
			this.subtitle = new TextNode(subtitle);
		}
		else if (subtitle === null)
		{
			this.subtitle = null;
		}
	}

	getSupertitle(): ?string
	{
		const supertitleNode = this.getSupertitleNode();

		return supertitleNode !== null ? supertitleNode.getText() : null;
	}

	getSupertitleNode(): ?TextNode
	{
		return this.supertitle !== null ? this.supertitle: this.getItem().getSupertitleNode();
	}

	setSupertitle(supertitle: string | TextNodeOptions): void
	{
		if (Type.isString(supertitle) || Type.isPlainObject(supertitle))
		{
			this.supertitle = new TextNode(supertitle);
		}
		else if (supertitle === null)
		{
			this.supertitle = null;
		}
	}

	getCaption(): ?string
	{
		const caption = this.getCaptionNode();

		return caption !== null ? caption.getText() : null;
	}

	getCaptionNode(): ?TextNode
	{
		return this.caption !== null ? this.caption: this.getItem().getCaptionNode();
	}

	setCaption(caption: string | TextNodeOptions): void
	{
		if (Type.isString(caption) || Type.isPlainObject(caption))
		{
			this.caption = new TextNode(caption);
		}
		else if (caption === null)
		{
			this.caption = null;
		}
	}

	getCaptionOption(option: string): string | boolean | number | null
	{
		if (!Type.isUndefined(this.captionOptions[option]))
		{
			return this.captionOptions[option];
		}

		return this.getItem().getCaptionOption(option);
	}

	setCaptionOption(option: string, value: string | boolean | number | null): void
	{
		if (Type.isStringFilled(option) && !Type.isUndefined(value))
		{
			this.captionOptions[option] = value;
		}
	}

	setCaptionOptions(options: {[key: string]: any } | null): void
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
		return this.avatar !== null ? this.avatar : this.getItem().getAvatar();
	}

	setAvatar(avatar: ?string): void
	{
		if (Type.isString(avatar) || avatar === null)
		{
			this.avatar = avatar;
		}
	}

	getAvatarOption(option: $Keys<AvatarOptions>): string | boolean | number | null
	{
		return (
			this.avatarOptions === null || Type.isUndefined(this.avatarOptions[option])
				? this.getItem().getAvatarOption(option)
				: this.avatarOptions[option]
		);
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
		}
	}

	setAvatarOptions(avatarOptions: AvatarOptions): void
	{
		if (Type.isPlainObject(avatarOptions))
		{
			Object.keys(avatarOptions).forEach((option: string) => {
				this.setAvatarOption(option, avatarOptions[option]);
			});
		}
	}

	getTextColor(): ?string
	{
		return this.textColor !== null ? this.textColor : this.getItem().getTextColor();
	}

	setTextColor(textColor: ?string): void
	{
		if (Type.isString(textColor) || textColor === null)
		{
			this.textColor = textColor;
		}
	}

	getLink(): ?string
	{
		return this.link !== null ? this.getItem().replaceMacros(this.link) : this.getItem().getLink();
	}

	setLink(link: string): void
	{
		if (Type.isString(link) || link === null)
		{
			this.link = link;
		}
	}

	getLinkTitle(): ?string
	{
		const linkTitle = this.getLinkTitleNode();

		return linkTitle !== null ? linkTitle.getText() : null;
	}

	getLinkTitleNode(): ?TextNode
	{
		return this.linkTitle !== null ? this.linkTitle: this.getItem().getLinkTitleNode();
	}

	setLinkTitle(title: string | TextNodeOptions): void
	{
		if (Type.isString(title) || Type.isPlainObject(title))
		{
			this.linkTitle = new TextNode(title);
		}
		else if (title === null)
		{
			this.linkTitle = null;
		}
	}

	getBadges(): ItemBadge[]
	{
		return this.badges !== null ? this.badges : this.getItem().getBadges();
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

	getBadgesOption(option: string): string | boolean | number | null
	{
		if (!Type.isUndefined(this.badgesOptions[option]))
		{
			return this.badgesOptions[option];
		}

		return this.getItem().getBadgesOption(option);
	}

	setBadgesOption(option: string, value: string | boolean | number | null): void
	{
		if (Type.isStringFilled(option) && !Type.isUndefined(value))
		{
			this.badgesOptions[option] = value;
		}
	}

	setBadgesOptions(options: {[key: string]: any } | null): void
	{
		if (Type.isPlainObject(options))
		{
			Object.keys(options).forEach((option: string) => {
				this.setBadgesOption(option, options[option]);
			});
		}
	}

	getOuterContainer(): HTMLElement
	{
		return this.cache.remember('outer-container', () => {

			let className = '';

			if (this.hasChildren() || this.isDynamic())
			{
				className += ' ui-selector-item-box-has-children';
				if (this.getDepthLevel() >= this.getTab().getItemMaxDepth())
				{
					className += ' ui-selector-item-box-max-depth';
				}
			}
			else if (this.getItem().isSelected())
			{
				className += ' ui-selector-item-box-selected';
			}

			if (this.isOpen())
			{
				className += ' ui-selector-item-box-open';
			}

			const div = document.createElement('div');
			div.className = `ui-selector-item-box${className}`;
			div.appendChild(this.getContainer());
			div.appendChild(this.getChildrenContainer());

			return div;
		});
	}

	getChildrenContainer(): HTMLElement
	{
		if (this.isRoot() && this.getTab())
		{
			return this.getTab().getItemsContainer();
		}

		return this.cache.remember('children-container', () => {

			const div = document.createElement('div');
			div.className = 'ui-selector-item-children';

			return div;
		});
	}

	getContainer(): HTMLElement
	{
		return this.cache.remember('container', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item';

			Event.bind(div, 'click', this.handleClick.bind(this))
			Event.bind(div, 'mouseenter', this.handleMouseEnter.bind(this))
			Event.bind(div, 'mouseleave', this.handleMouseLeave.bind(this))

			div.appendChild(this.getAvatarContainer());
			div.appendChild(this.getTitlesContainer());
			div.appendChild(this.getIndicatorContainer());

			if (Type.isStringFilled(this.getLink()))
			{
				div.appendChild(this.getLinkContainer());
			}

			return div;
		});
	}

	getAvatarContainer(): HTMLElement
	{
		return this.cache.remember('avatar', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item-avatar';

			return div;
		});
	}

	getTitlesContainer(): HTMLElement
	{
		return this.cache.remember('titles', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item-titles';

			div.appendChild(this.getSupertitleContainer());
			div.appendChild(this.getTitleBoxContainer());
			div.appendChild(this.getSubtitleContainer());

			return div;
		});
	}

	getTitleBoxContainer(): HTMLElement
	{
		return this.cache.remember('title-box', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item-title-box';

			div.appendChild(this.getTitleContainer());
			div.appendChild(this.getBadgeContainer());
			div.appendChild(this.getCaptionContainer());

			return div;
		});
	}

	getTitleContainer(): HTMLElement
	{
		return this.cache.remember('title', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item-title';

			return div;
		});
	}

	getSubtitleContainer(): HTMLElement
	{
		return this.cache.remember('subtitle', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item-subtitle';

			return div;
		});
	}

	getSupertitleContainer(): HTMLElement
	{
		return this.cache.remember('supertitle', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item-supertitle';

			return div;
		});
	}

	getCaptionContainer(): HTMLElement
	{
		return this.cache.remember('caption', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item-caption';

			return div;
		});
	}

	getIndicatorContainer(): HTMLElement
	{
		return this.cache.remember('indicator', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item-indicator';

			return div;
		});
	}

	getBadgeContainer(): HTMLElement
	{
		return this.cache.remember('badge', () => {
			const div = document.createElement('div');
			div.className = 'ui-selector-item-badges';

			return div;
		});
	}

	getLinkContainer(): HTMLElement
	{
		return this.cache.remember('link', () => {
			const anchor: HTMLAnchorElement = document.createElement('a');
			anchor.className = 'ui-selector-item-link';
			anchor.href = this.getLink();
			anchor.target = '_blank';
			anchor.title = '';

			Event.bind(anchor, 'click', this.handleLinkClick.bind(this));
			anchor.appendChild(this.getLinkTextContainer());

			return anchor;
		});
	}

	getLinkTextContainer(): HTMLElement
	{
		return this.cache.remember('link-text', () => {
			const span = document.createElement('span');
			span.className = 'ui-selector-item-link-text';

			return span;
		});
	}

	showLink(): void
	{
		if (Type.isStringFilled(this.getLink()))
		{
			Dom.addClass(this.getLinkContainer(), 'ui-selector-item-link--show');
			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					Dom.addClass(this.getLinkContainer(), 'ui-selector-item-link--animate');
				});
			});

		}
	}

	hideLink(): void
	{
		if (Type.isStringFilled(this.getLink()))
		{
			Dom.removeClass(
				this.getLinkContainer(), ['ui-selector-item-link--show', 'ui-selector-item-link--animate']
			);
		}
	}

	setHighlights(highlights: MatchField[]): void
	{
		this.highlights = highlights;
	}

	getHighlights(): MatchField[]
	{
		return this.highlights;
	}

	highlight(): void
	{
		this.getHighlights().forEach(matchField => {
			const field = matchField.getField();
			const fieldName = field.getName();

			if (field.isCustom())
			{
				const text = this.getItem().getCustomData().get(fieldName);
				this.getSubtitleContainer().innerHTML = Highlighter.mark(text, matchField.getMatches());
			}
			else if (field.getName() === 'title')
			{
				this.getTitleContainer().innerHTML =
					Highlighter.mark(this.getItem().getTitleNode(), matchField.getMatches())
				;
			}
			else if (field.getName() === 'subtitle')
			{
				this.getSubtitleContainer().innerHTML =
					Highlighter.mark(this.getItem().getSubtitleNode(), matchField.getMatches())
				;
			}
			else if (field.getName() === 'supertitle')
			{
				this.getSupertitleContainer().innerHTML =
					Highlighter.mark(this.getItem().getSupertitleNode(), matchField.getMatches())
				;
			}
			else if (field.getName() === 'caption')
			{
				this.getCaptionContainer().innerHTML = (
					Highlighter.mark(this.getItem().getCaptionNode(), matchField.getMatches())
				);
			}
		});
	}

	select(): void
	{
		if (this.hasChildren() || this.isDynamic())
		{
			return;
		}

		Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-selected');
	}

	deselect(): void
	{
		if (this.hasChildren() || this.isDynamic())
		{
			return;
		}

		Dom.removeClass(this.getOuterContainer(), 'ui-selector-item-box-selected');
	}

	focus(): void
	{
		if (this.isFocused())
		{
			return;
		}

		this.focused = true;
		Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-focused');

		this.getDialog().emit('ItemNode:onFocus', { node: this });
	}

	unfocus(): void
	{
		if (!this.isFocused())
		{
			return;
		}

		this.focused = false;
		Dom.removeClass(this.getOuterContainer(), 'ui-selector-item-box-focused');

		this.getDialog().emit('ItemNode:onUnfocus', { node: this });
	}

	isFocused(): boolean
	{
		return this.focused;
	}

	click(): void
	{
		if (this.hasChildren() || this.isDynamic())
		{
			if (this.isOpen())
			{
				this.collapse();
			}
			else
			{
				this.expand();
			}
		}
		else
		{
			if (this.getItem().isSelected())
			{
				if (this.getItem().isDeselectable())
				{
					this.getItem().deselect();
				}

				if (this.getDialog().shouldHideOnDeselect())
				{
					this.getDialog().hide();
				}
			}
			else
			{
				this.getItem().select();

				if (this.getDialog().shouldClearSearchOnSelect())
				{
					this.getDialog().clearSearch();
				}

				if (this.getDialog().shouldHideOnSelect())
				{
					this.getDialog().hide();
				}
			}
		}

		this.getDialog().focusSearch();
	}

	scrollIntoView(): void
	{
		const tabContainer = this.getTab().getContainer();
		const nodeContainer = this.getContainer();

		const tabRect = Dom.getPosition(tabContainer);
		const nodeRect = Dom.getPosition(nodeContainer);
		const margin = 9; // 'ui-selector-items' padding - 'ui-selector-item' margin = 10 - 1

		if (nodeRect.top < tabRect.top) // scroll up
		{
			tabContainer.scrollTop -= (tabRect.top - nodeRect.top + margin);
		}
		else if (nodeRect.bottom > tabRect.bottom) // scroll down
		{
			tabContainer.scrollTop += nodeRect.bottom - tabRect.bottom + margin;
		}
	}

	#makeEllipsisTitle(): void
	{
		if (this.constructor.#isEllipsisActive(this.getTitleContainer()))
		{
			this.getContainer().setAttribute(
				'title',
				this.constructor.#sanitizeTitle(this.getTitleContainer().textContent)
			);
		}
		else
		{
			Dom.attr(this.getContainer(), 'title', null);
		}

		const containers = [
			this.getSupertitleContainer(),
			this.getSubtitleContainer(),
			this.getCaptionContainer(),
			...this.getBadges().map((badge: ItemBadge) => badge.getContainer(this.getBadgeContainer()))
		];

		containers.forEach(container => {
			if (this.constructor.#isEllipsisActive(container))
			{
				container.setAttribute('title', this.constructor.#sanitizeTitle(container.textContent));
			}
			else
			{
				Dom.attr(container, 'title', null);
			}
		});
	}

	static #isEllipsisActive(element: HTMLElement): boolean
	{
		return element.offsetWidth < element.scrollWidth;
	}

	static #sanitizeTitle(text: string)
	{
		return text.replace(/[\t ]+/gm, ' ').replace(/\n+/gm, '\n').trim();
	}

	handleClick(): void
	{
		this.click();
	}

	handleLinkClick(event: MouseEvent): void
	{
		this.getDialog().emit('ItemNode:onLinkClick', { node: this, event });
		event.stopPropagation();
	}

	handleMouseEnter(): void
	{
		this.focus();
		this.showLink();
		this.#makeEllipsisTitle();
	}

	handleMouseLeave(): void
	{
		this.unfocus();
		this.hideLink();
	}
}
