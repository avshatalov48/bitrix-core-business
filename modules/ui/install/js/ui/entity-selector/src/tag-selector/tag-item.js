import { Cache, Tag, Type, Dom } from 'main.core';
import Entity from '../entity/entity';
import TextNode from '../common/text-node';
import Animation from '../common/animation';
import TypeUtils from '../common/type-utils';

import type TagSelector from './tag-selector';
import type { TagItemOptions } from './tag-item-options';
import type { TextNodeOptions } from '../common/text-node-options';
import type { AvatarOptions } from '../item/avatar-options';

export default class TagItem
{
	id: string | number = null;
	entityId: string = null;
	entityType: string = null;
	title: ?TextNode = null;

	avatar: ?string = null;
	avatarOptions: ?AvatarOptions = null;
	maxWidth: ?number = null;
	textColor: ?string = null;
	bgColor: ?string = null;
	fontWeight: ?string = null;

	link: ?string = null;
	onclick: ?Function = null;

	deselectable: ?boolean = null;
	customData: Map<string, any> = null;

	cache = new Cache.MemoryCache();
	selector: TagSelector = null;
	rendered: ?boolean = false;

	constructor(itemOptions: TagItemOptions)
	{
		const options = Type.isPlainObject(itemOptions) ? itemOptions : {};
		if (!Type.isStringFilled(options.id) && !Type.isNumber(options.id))
		{
			throw new Error('TagSelector.TagItem: "id" parameter is required.');
		}

		if (!Type.isStringFilled(options.entityId))
		{
			throw new Error('TagSelector.TagItem: "entityId" parameter is required.');
		}

		this.id = options.id;
		this.entityId = options.entityId.toLowerCase();
		this.entityType = Type.isStringFilled(options.entityType) ? options.entityType : 'default';
		this.customData = TypeUtils.createMapFromOptions(options.customData);

		this.onclick = Type.isFunction(options.onclick) ? options.onclick : null;
		this.link = Type.isStringFilled(options.link) ? options.link : null;

		this.setTitle(options.title);
		this.setDeselectable(options.deselectable);

		this.setAvatar(options.avatar);
		this.setAvatarOptions(options.avatarOptions);
		this.setMaxWidth(options.maxWidth);
		this.setTextColor(options.textColor);
		this.setBgColor(options.bgColor);
		this.setFontWeight(options.fontWeight);
	}

	getId(): string | number
	{
		return this.id;
	}

	getEntityId(): string
	{
		return this.entityId;
	}

	getEntityType(): string
	{
		return this.entityType;
	}

	getSelector(): TagSelector
	{
		return this.selector;
	}

	setSelector(selector: TagSelector)
	{
		this.selector = selector;
	}

	getTitle(): string
	{
		return this.getTitleNode() && !this.getTitleNode().isNullable() ? this.getTitleNode().getText() : '';
	}

	getTitleNode(): ?TextNode
	{
		return this.title;
	}

	setTitle(title: string | TextNodeOptions): void
	{
		if (Type.isStringFilled(title) || Type.isPlainObject(title) || title === null)
		{
			this.title = title === null ? null : new TextNode(title);
		}
	}

	getAvatar(): ?string
	{
		if (this.avatar !== null)
		{
			return this.avatar;
		}
		else if (this.getSelector().getTagAvatar() !== null)
		{
			return this.getSelector().getTagAvatar();
		}
		else if (this.getEntityTagOption('avatar') !== null)
		{
			return this.getEntityTagOption('avatar');
		}

		return this.getEntityItemOption('avatar');
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
		if (this.avatarOptions !== null && !Type.isUndefined(this.avatarOptions[option]))
		{
			return this.avatarOptions[option];
		}

		const selectorAvatarOption = this.getSelector().getTagAvatarOption(option);
		if (selectorAvatarOption !== null)
		{
			return selectorAvatarOption[option];
		}

		const entityTagAvatarOptions = this.getEntityTagOption('avatarOptions');
		if (Type.isPlainObject(entityTagAvatarOptions) && !Type.isUndefined(entityTagAvatarOptions[option]))
		{
			return entityTagAvatarOptions[option];
		}

		const entityItemAvatarOptions = this.getEntityItemOption('avatarOptions');
		if (Type.isPlainObject(entityItemAvatarOptions) && !Type.isUndefined(entityItemAvatarOptions[option]))
		{
			return entityItemAvatarOptions[option];
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
		if (this.textColor !== null)
		{
			return this.textColor;
		}
		else if (this.getSelector().getTagTextColor() !== null)
		{
			return this.getSelector().getTagTextColor();
		}

		return this.getEntityTagOption('textColor');
	}

	setTextColor(textColor: ?string): void
	{
		if (Type.isString(textColor) || textColor === null)
		{
			this.textColor = textColor;
		}
	}

	getBgColor(): ?string
	{
		if (this.bgColor !== null)
		{
			return this.bgColor;
		}
		else if (this.getSelector().getTagBgColor() !== null)
		{
			return this.getSelector().getTagBgColor();
		}

		return this.getEntityTagOption('bgColor');
	}

	setBgColor(bgColor: ?string): void
	{
		if (Type.isString(bgColor) || bgColor === null)
		{
			this.bgColor = bgColor;
		}
	}

	getFontWeight(): ?string
	{
		if (this.fontWeight !== null)
		{
			return this.fontWeight;
		}
		else if (this.getSelector().getTagFontWeight() !== null)
		{
			return this.getSelector().getTagFontWeight();
		}

		return this.getEntityTagOption('fontWeight');
	}

	setFontWeight(fontWeight: ?string): void
	{
		if (Type.isString(fontWeight) || fontWeight === null)
		{
			this.fontWeight = fontWeight;
		}
	}

	getMaxWidth(): ?number
	{
		if (this.maxWidth !== null)
		{
			return this.maxWidth;
		}
		else if (this.getSelector().getTagMaxWidth() !== null)
		{
			return this.getSelector().getTagMaxWidth();
		}

		return this.getEntityTagOption('maxWidth');
	}

	setMaxWidth(width: ?number): void
	{
		if ((Type.isNumber(width) && width >= 0) || width === null)
		{
			this.maxWidth = width;
		}
	}

	setDeselectable(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.deselectable = flag;
		}
	}

	isDeselectable(): boolean
	{
		return this.deselectable !== null ? this.deselectable : this.getSelector().isDeselectable();
	}

	getCustomData(): Map<string, any>
	{
		return this.customData;
	}

	getLink(): ?string
	{
		return this.link;
	}

	getOnclick(): ?Function
	{
		return this.onclick;
	}

	render(): void
	{
		const titleNode = this.getTitleNode();
		if (titleNode)
		{
			titleNode.renderTo(this.getTitleContainer());

			//Dom.attr(this.getContentContainer(), 'title', this.getTitle());
		}
		else
		{
			this.getTitleContainer().textContent = '';
			Dom.attr(this.getContentContainer(), 'title', '');
		}

		const avatar = this.getAvatar();
		const bgImage = this.getAvatarOption('bgImage');
		if (Type.isStringFilled(avatar))
		{
			Dom.style(this.getAvatarContainer(), 'background-image', `url('${avatar}')`);
		}
		else
		{
			Dom.style(this.getAvatarContainer(), 'background-image', bgImage);
		}

		const bgColor = this.getAvatarOption('bgColor');
		const bgSize = this.getAvatarOption('bgSize');

		Dom.style(this.getAvatarContainer(), 'background-color', bgColor);
		Dom.style(this.getAvatarContainer(), 'background-size', bgSize);

		const hasAvatar = avatar || (bgColor && bgColor !== 'none') || (bgImage && bgImage !== 'none');
		if (hasAvatar)
		{
			Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--has-avatar');
		}
		else
		{
			Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag--has-avatar');
		}

		const maxWidth = this.getMaxWidth();
		if (maxWidth > 0)
		{
			Dom.style(this.getContainer(), 'max-width', `${maxWidth}px`);
		}
		else
		{
			Dom.style(this.getContainer(), 'max-width', null);
		}

		if (this.isDeselectable())
		{
			Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag-readonly');
		}
		else
		{
			Dom.addClass(this.getContainer(), 'ui-tag-selector-tag-readonly');
		}

		Dom.style(this.getTitleContainer(), 'color', this.getTextColor());
		Dom.style(this.getTitleContainer(), 'font-weight', this.getFontWeight());
		Dom.style(this.getContainer(), 'background-color', this.getBgColor());

		this.rendered = true;
	}

	getContainer(): HTMLElement
	{
		return this.cache.remember('container', () => {
			return Tag.render`
				<div class="ui-tag-selector-item ui-tag-selector-tag">
					${this.getContentContainer()}
					${this.getRemoveIcon()}
				</div>`
			;
		});
	}

	getContentContainer(): HTMLElement
	{
		return this.cache.remember('content-container', () => {
			if (Type.isStringFilled(this.getLink()))
			{
				return Tag.render`
					<a
						class="ui-tag-selector-tag-content"
						onclick="${this.handleContainerClick.bind(this)}"
						href="${this.getLink()}"
						target="_blank"
					>
						${this.getAvatarContainer()}
						${this.getTitleContainer()}
					</a>
				`;
			}
			else
			{
				const className = Type.isFunction(this.getOnclick()) ? ' ui-tag-selector-tag-content--clickable' : '';
				return Tag.render`
					<div 
						class="ui-tag-selector-tag-content${className}" 
						onclick="${this.handleContainerClick.bind(this)}"
					>
						${this.getAvatarContainer()}
						${this.getTitleContainer()}
					</div>
					
				`;
			}
		});
	}

	getAvatarContainer(): HTMLElement
	{
		return this.cache.remember('avatar', () => {
			return Tag.render`
				<div class="ui-tag-selector-tag-avatar"></div>
			`;
		});
	}

	getTitleContainer(): HTMLElement
	{
		return this.cache.remember('title', () => {
			return Tag.render`
				<div class="ui-tag-selector-tag-title"></div>
			`;
		});
	}

	getRemoveIcon(): HTMLElement
	{
		return this.cache.remember('remove-icon', () => {
			return Tag.render`
				<div class="ui-tag-selector-tag-remove" onclick="${this.handleRemoveIconClick.bind(this)}"></div>
			`;
		});
	}

	getEntityTagOption(option: string): any
	{
		return Entity.getTagOption(this.getEntityId(), option, this.getEntityType());
	}

	getEntityItemOption(option: string): any
	{
		return Entity.getItemOption(this.getEntityId(), option, this.getEntityType());
	}

	isRendered(): boolean
	{
		return this.rendered && this.getSelector() && this.getSelector().isRendered();
	}

	remove(animate: boolean = true): Promise
	{
		if (animate === false)
		{
			Dom.remove(this.getContainer());
			return Promise.resolve();
		}

		return new Promise(resolve => {
			Dom.style(this.getContainer(), 'width', `${this.getContainer().offsetWidth}px`);
			Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--remove');
			Animation.handleAnimationEnd(this.getContainer(), 'ui-tag-selector-tag-remove').then(() => {
				Dom.remove(this.getContainer());
				resolve();
			});
		});
	}

	show(): Promise
	{
		return new Promise(resolve => {
			Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--show');
			Animation.handleAnimationEnd(this.getContainer(), 'ui-tag-selector-tag-show').then(() => {
				Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag--show');
				resolve();
			});
		});
	}

	handleContainerClick(): void
	{
		const fn = this.getOnclick();
		if (Type.isFunction(fn))
		{
			fn(this);
		}
	}

	handleRemoveIconClick(event: MouseEvent): void
	{
		event.stopPropagation();
		if (this.isDeselectable())
		{
			this.getSelector().removeTag(this);
		}
	}
}