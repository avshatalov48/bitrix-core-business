import { ajax as Ajax, Cache, Dom, Runtime, Tag, Type } from 'main.core';
import { Loader } from 'main.loader';

import ItemCollection from './item-collection';
import ItemNodeComparator from './item-node-comparator';
import Highlighter from '../search/highlighter';
import ItemBadge from './item-badge';
import MatchField from '../search/match-field';

import type Item from './item';
import type Tab from '../dialog/tabs/tab';
import type Dialog from '../dialog/dialog';
import type { ItemOptions } from './item-options';
import type { ItemNodeOptions } from './item-node-options';
import type { ItemBadgeOptions } from './item-badge-options';

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

	children: ItemCollection<ItemNode> = null;
	childItems: WeakMap<Item, ItemNode> = new WeakMap(); // for the fast access

	loaded: boolean = false;
	dynamic: boolean = false;
	dynamicPromise: ?Promise = null;
	loader: Loader = null;
	open: boolean = false;
	autoOpen: boolean = false;
	focused: boolean = false;

	renderMode: RenderMode = RenderMode.PARTIAL;
	title: ?string = null;
	subtitle: ?string = null;
	caption: ?string = null;
	supertitle: ?string = null;
	avatar: ?string = null;
	link: ?string = null;
	linkTitle: ?string = null;
	textColor: ?string = null;
	badges: ItemBadgeOptions[] = null;

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

		this.children = new ItemCollection(comparator);

		this.renderMode = options.renderMode === RenderMode.OVERRIDE ? RenderMode.OVERRIDE : RenderMode.PARTIAL;
		if (this.renderMode === RenderMode.OVERRIDE)
		{
			this.title = '';
			this.subtitle = '';
			this.caption = '';
			this.supertitle = '';
			this.avatar = '';
			this.textColor = '';
			this.link = '';
			this.linkTitle = '';
			this.badges = [];
		}

		this.setTitle(options.title);
		this.setSubtitle(options.subtitle);
		this.setSupertitle(options.supertitle);
		this.setCaption(options.caption);
		this.setAvatar(options.avatar);
		this.setTextColor(options.textColor);
		this.setLink(options.link);
		this.setLinkTitle(options.linkTitle);
		this.setBadges(options.badges);

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

	setTab(tab: Tab): this
	{
		this.tab = tab;

		return this;
	}

	getTab(): Tab
	{
		return this.tab;
	}

	getParentNode(): ?ItemNode
	{
		return this.parentNode;
	}

	setParentNode(parentNode: ItemNode): this
	{
		this.parentNode = parentNode;

		return this;
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

	addChildren(children: ItemOptions[])
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

		while (child.getFirstChild())
		{
			child.removeChild(child.getFirstChild());
		}

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
			this.render();
		}

		return true;
	}

	removeChildren(): void
	{
		while (this.getFirstChild())
		{
			this.removeChild(this.getFirstChild());
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

	getChildren(): ItemCollection
	{
		return this.children;
	}

	hasChildren(): boolean
	{
		return this.children.count() > 0;
	}

	loadChildren()
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
				parentItem: this.getItem(),
				dialog: this.getDialog()
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

	setOpen(open: boolean): this
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

		return this;
	}

	isOpen(): boolean
	{
		return this.open;
	}

	isAutoOpen(): boolean
	{
		return this.autoOpen && this.isDynamic() && !this.isLoaded();
	}

	setAutoOpen(autoOpen: boolean): this
	{
		if (Type.isBoolean(autoOpen))
		{
			this.autoOpen = autoOpen;
		}

		return this;
	}

	setDynamic(dynamic: boolean): this
	{
		if (Type.isBoolean(dynamic))
		{
			this.dynamic = dynamic;
		}

		return this;
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
		this.getLoader().show();
		Dom.addClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	}

	hideLoader(): void
	{
		this.getLoader().hide();
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

		Dom.style(this.getChildrenContainer(), 'height', `${this.getChildrenContainer().scrollHeight}px`);
		Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-open');
		this.setOpen(true);
	}

	collapse(): void
	{
		if (!this.isOpen())
		{
			return;
		}

		Dom.style(this.getChildrenContainer(), 'height', `${this.getChildrenContainer().offsetHeight}px`);

		requestAnimationFrame(() => {
			Dom.removeClass(this.getOuterContainer(), 'ui-selector-item-box-open');
			Dom.style(this.getChildrenContainer(), 'height', null);

			this.setOpen(false);
		});
	}

	render(): void
	{
		if (this.isRoot())
		{
			this.renderRoot();
			return;
		}

		this.getTitleContainer().textContent = Type.isString(this.getTitle()) ? this.getTitle() : '';
		this.getSubtitleContainer().textContent = Type.isString(this.getSubtitle()) ? this.getSubtitle() : '';
		this.getSupertitleContainer().textContent = Type.isString(this.getSupertitle()) ? this.getSupertitle() : '';
		this.getCaptionContainer().textContent = Type.isString(this.getCaption()) ? this.getCaption() : '';

		if (Type.isStringFilled(this.getTextColor()))
		{
			Dom.style(this.getTitleContainer(), 'color', this.getTextColor());
		}
		else
		{
			Dom.style(this.getTitleContainer(), 'color', null);
		}

		if (Type.isStringFilled(this.getAvatar()))
		{
			Dom.style(this.getAvatarContainer(), 'background-image', `url('${this.getAvatar()}')`);
		}
		else
		{
			Dom.style(this.getAvatarContainer(), 'background-image', null);
		}

		if (this.hasChildren() || this.isDynamic())
		{
			Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-has-children');
			if (this.getDepthLevel() >= this.getTab().getItemMaxDepth())
			{
				Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-max-depth');
			}
		}
		else
		{
			Dom.removeClass(
				this.getOuterContainer(),
				['ui-selector-item-box-has-children', 'ui-selector-item-box-max-depth']
			);
		}

		this.highlight();

		if (this.isAutoOpen())
		{
			this.expand();
			this.setAutoOpen(false);
		}

		this.renderChildren();

		this.rendered = true;
	}

	/**
	 * @private
	 */
	renderRoot(): void
	{
		this.renderChildren();
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
	renderChildren(): void
	{
		Dom.clean(this.getChildrenContainer());

		if (this.hasChildren())
		{
			this.getChildren().forEach((child: ItemNode) => {
				child.render();
				Dom.append(child.getOuterContainer(), this.getChildrenContainer());
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

	getTitle(): string
	{
		return this.title !== null ? this.title : this.getItem().getTitle();
	}

	setTitle(title: string): this
	{
		if (Type.isString(title) || title === null)
		{
			this.title = title;
		}

		return this;
	}

	getSubtitle(): string
	{
		return this.subtitle !== null ? this.subtitle : this.getItem().getSubtitle();
	}

	setSubtitle(subtitle: string): this
	{
		if (Type.isString(subtitle) || subtitle === null)
		{
			this.subtitle = subtitle;
		}

		return this;
	}

	getSupertitle(): string
	{
		return this.supertitle !== null ? this.supertitle : this.getItem().getSupertitle();
	}

	setSupertitle(supertitle: string): this
	{
		if (Type.isString(supertitle) || supertitle === null)
		{
			this.supertitle = supertitle;
		}

		return this;
	}

	getAvatar(): ?string
	{
		return this.avatar !== null ? this.avatar : this.getItem().getAvatar();
	}

	setAvatar(avatar: ?string): this
	{
		if (Type.isString(avatar) || avatar === null)
		{
			this.avatar = avatar;
		}

		return this;
	}

	getTextColor(): ?string
	{
		return this.textColor !== null ? this.textColor : this.getItem().getTextColor();
	}

	setTextColor(textColor: ?string): this
	{
		if (Type.isString(textColor) || textColor === null)
		{
			this.textColor = textColor;
		}

		return this;
	}

	getCaption(): string
	{
		return this.caption !== null ? this.caption : this.getItem().getCaption();
	}

	setCaption(caption: string): this
	{
		if (Type.isString(caption) || caption === null)
		{
			this.caption = caption;
		}

		return this;
	}

	getLink(): string
	{
		return this.link !== null ? this.getItem().replaceMacros(this.link) : this.getItem().getLink();
	}

	setLink(link: string): this
	{
		if (Type.isString(link) || link === null)
		{
			this.link = link;
		}

		return this;
	}

	getLinkTitle(): string
	{
		return this.linkTitle !== null ? this.linkTitle : this.getItem().getLinkTitle();
	}

	setLinkTitle(title: string): this
	{
		if (Type.isString(title) || title === null)
		{
			this.linkTitle = title;
		}

		return this;
	}

	getBadges(): ItemBadge[]
	{
		return this.badges !== null ? this.badges : this.getItem().getBadges();
	}

	setBadges(badges: ?ItemBadgeOptions[]): this
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

		return this;
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

			return Tag.render`
				<div class="ui-selector-item-box${className}">
					${this.getContainer()}
					${this.getChildrenContainer()}
				</div>
			`;
		});
	}

	getChildrenContainer(): HTMLElement
	{
		if (this.isRoot() && this.getTab())
		{
			return this.getTab().getItemsContainer();
		}

		return this.cache.remember('children-container', () => {
			return Tag.render`
				<div class="ui-selector-item-children" ontransitionend="${this.handleTransitionEnd.bind(this)}"></div>
			`;
		});
	}

	getContainer(): HTMLElement
	{
		return this.cache.remember('container', () => {

			return Tag.render`
				<div 
					class="ui-selector-item" 
					onclick="${this.handleClick.bind(this)}"
					onmouseenter="${this.handleMouseEnter.bind(this)}"
					onmouseleave="${this.handleMouseLeave.bind(this)}"
				>
					${this.getAvatarContainer()}
					<div class="ui-selector-item-titles">
						${this.getSupertitleContainer()}
						<div class="ui-selector-item-title-box">
							${this.getTitleContainer()}
							${Type.isArrayFilled(this.getBadges()) ? this.getBadgeContainer() : ''}
							${this.getCaptionContainer()}
						</div>
						${this.getSubtitleContainer()}
					</div>
					${Type.isStringFilled(this.getLink()) ? this.getLinkContainer() : ''}
					${this.getIndicatorContainer()}
				</div>
			`;
		});
	}

	getAvatarContainer(): HTMLElement
	{
		return this.cache.remember('avatar', () => {
			 return Tag.render`
				<div class="ui-selector-item-avatar"></div>
			`;
		});
	}

	getTitleContainer(): HTMLElement
	{
		return this.cache.remember('title', () => {
			return Tag.render`
				<div class="ui-selector-item-title"></div>
			`;
		});
	}

	getSubtitleContainer()
	{
		return this.cache.remember('subtitle', () => {
			return Tag.render`
				<div class="ui-selector-item-subtitle"></div>
			`;
		});
	}

	getSupertitleContainer()
	{
		return this.cache.remember('supertitle', () => {
			return Tag.render`
				<div class="ui-selector-item-supertitle"></div>
			`;
		});
	}

	getCaptionContainer()
	{
		return this.cache.remember('caption', () => {
			return Tag.render`
				<div class="ui-selector-item-caption"></div>
			`;
		});
	}

	getIndicatorContainer()
	{
		return this.cache.remember('indicator', () => {
			return Tag.render`
				<div class="ui-selector-item-indicator"></div>
			`;
		});
	}

	getBadgeContainer()
	{
		return this.cache.remember('badge', () => {

			const badges = [];

			this.getBadges().forEach((badge: ItemBadge) => {
				badge.render();
				badges.push(badge.render());
			});

			return Tag.render`
				<div class="ui-selector-item-badges">${badges}</div>
			`;
		});
	}

	getLinkContainer()
	{
		return this.cache.remember('link', () => {
			return Tag.render`
				<a 
					class="ui-selector-item-link"
					href="${this.getLink()}" 
					target="_blank"
					onclick="${this.handleLinkClick.bind(this)}"
				>${this.getLinkTextContainer()}</a>
			`;
		});
	}

	getLinkTextContainer()
	{
		return this.cache.remember('link-text', () => {
			return Tag.render`
				<span class="ui-selector-item-link-text">${this.getLinkTitle()}</span>
			`;
		});
	}

	showLink()
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

	hideLink()
	{
		if (Type.isStringFilled(this.getLink()))
		{
			Dom.removeClass(
				this.getLinkContainer(), ['ui-selector-item-link--show', 'ui-selector-item-link--animate']
			);
		}
	}

	setHighlights(highlights: MatchField[])
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
					Highlighter.mark(this.getItem().getTitle(), matchField.getMatches())
				;
			}
			else if (field.getName() === 'subtitle')
			{
				this.getSubtitleContainer().innerHTML =
					Highlighter.mark(this.getItem().getSubtitle(), matchField.getMatches())
				;
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

	click()
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
				this.getItem().deselect();
				if (this.getDialog().shouldHideOnDeselect())
				{
					this.getDialog().hide();
				}
			}
			else
			{
				this.getItem().select();
				if (this.getDialog().shouldHideOnSelect())
				{
					this.getDialog().hide();
				}
			}
		}

		this.getDialog().focusSearch();
	}

	scrollIntoView()
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

	handleClick(): void
	{
		this.click();
	}

	handleLinkClick(event: MouseEvent): void
	{
		this.getDialog().emit('ItemNode:onLinkClick', { node: this, event });
		event.stopPropagation();
	}

	handleTransitionEnd(event: TransitionEvent): void
	{
		if (event.propertyName === 'height')
		{
			Dom.style(this.getChildrenContainer(), 'height', null);
		}
	}

	handleMouseEnter(): void
	{
		this.focus();
		this.showLink();
	}

	handleMouseLeave(): void
	{
		this.unfocus();
		this.hideLink();
	}
}