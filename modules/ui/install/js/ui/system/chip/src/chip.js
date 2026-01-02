import { Type, Tag, Dom, Event } from 'main.core';
import { Icon, Outline } from 'ui.icon-set.api.core';

import { ChipDesign, ChipSize } from './const';
import type { ChipImage } from './types';

import './chip.css';

export type ChipOptions = {
	size?: string,
	design?: string,
	icon?: string,
	iconColor?: string,
	iconBackground?: string,
	image?: ChipImage,
	text?: string,
	rounded?: boolean,
	withClear?: boolean,
	dropdown?: boolean,
	dropdownActive?: boolean,
	lock?: boolean,
	compact?: boolean,
	trimmable?: boolean,
	onClick?: Function,
	onClear?: Function,
};

export class Chip
{
	#size: string;
	#design: string;
	#icon: ?string = null;
	#iconColor: ?string = null;
	#iconBackground: ?string = null;
	#image: ?ChipImage = null;
	#text: string;
	#rounded: boolean;
	#withClear: boolean;
	#dropdown: boolean;
	#dropdownActive: boolean;
	#lock: boolean;
	#compact: boolean;
	#trimmable: boolean;
	#onClick: ?Function = null;
	#onClear: ?Function = null;

	#wrapper: ?HTMLElement = null;
	#iconElement: ?HTMLElement = null;
	#textElement: ?HTMLElement = null;
	#dropdownIconElement: ?HTMLElement = null;
	#clearIconElement: ?HTMLElement = null;
	#lockIconElement: ?HTMLElement = null;
	#iconInstance: ?Icon = null;
	#dropdownIconInstance: ?Icon = null;
	#clearIconInstance: ?Icon = null;
	#lockIconInstance: ?Icon = null;

	constructor(options: ChipOptions = {})
	{
		this.#size = options.size ?? ChipSize.Lg;
		this.#design = options.design ?? ChipDesign.Outline;
		this.#icon = options.icon ?? null;
		this.#iconColor = options.iconColor ?? null;
		this.#iconBackground = options.iconBackground ?? null;
		this.#image = options.image ?? null;
		this.#text = options.text ?? '';
		this.#rounded = options.rounded === true;
		this.#withClear = options.withClear === true;
		this.#dropdown = options.dropdown === true;
		this.#dropdownActive = options.dropdownActive === true;
		this.#lock = options.lock === true;
		this.#compact = options.compact ?? true;
		this.#trimmable = options.trimmable === true;
		this.#onClick = options.onClick ?? null;
		this.#onClear = options.onClear ?? null;
	}

	render(): HTMLElement
	{
		if (this.#wrapper)
		{
			return this.#wrapper;
		}

		this.#wrapper = Tag.render`
			<div class="ui-chip ${this.#getModifierClasses()}">
				${this.#renderIcon()}
				${this.#renderText()}
				${this.#renderDropdownIcon()}
				${this.#renderClearIcon()}
				${this.#renderLock()}
			</div>
		`;

		Dom.attr(this.#wrapper, 'tabindex', '0');

		this.#bindEvents();

		return this.#wrapper;
	}

	#getModifierClasses(): string
	{
		const classes = [
			`--${this.#design}`,
			`--${this.#size}`,
		];

		if (this.#rounded)
		{
			classes.push('--rounded');
		}

		if (this.#compact)
		{
			classes.push('--compact');
		}

		if (this.#trimmable)
		{
			classes.push('--trimmable');
		}

		if (this.#lock)
		{
			classes.push('--lock');
		}

		if (this.#withClear || this.#dropdown)
		{
			classes.push('--with-right-icon');
		}

		if (this.#text.length === 0)
		{
			classes.push('--no-text');
		}

		return classes.join(' ');
	}

	#renderIcon(): HTMLElement | null
	{
		if (this.#image)
		{
			return Tag.render`
				<img class="ui-chip-icon --image" src="${this.#image.src}" alt="${this.#image.alt || ''}">
			`;
		}

		if (this.#icon)
		{
			this.#iconElement = Tag.render`<div class="ui-chip-icon"></div>`;

			if (this.#iconBackground)
			{
				Dom.addClass(this.#iconElement, '--with-background');
				Dom.style(this.#iconElement, '--icon-background', this.#iconBackground);
			}

			this.#iconInstance = new Icon({
				icon: this.#icon,
				color: this.#iconColor || null,
			});

			this.#iconInstance.renderTo(this.#iconElement);

			return this.#iconElement;
		}

		return null;
	}

	#renderText(): HTMLElement
	{
		this.#textElement = Tag.render`<div class="ui-chip-text">${this.#text}</div>`;

		return this.#textElement;
	}

	#renderDropdownIcon(): HTMLElement | null
	{
		if (this.#dropdown)
		{
			this.#dropdownIconElement = Tag.render`<div class="ui-chip-right-icon ui-chip-dropdown-icon"></div>`;

			this.#dropdownIconInstance = new Icon({
				icon: Outline.CHEVRON_DOWN_M,
			});

			this.#dropdownIconInstance.renderTo(this.#dropdownIconElement);

			this.#updateDropdownStyle();

			return this.#dropdownIconElement;
		}

		return null;
	}

	#renderClearIcon(): HTMLElement | null
	{
		if (this.#withClear)
		{
			this.#clearIconElement = Tag.render`<div class="ui-chip-right-icon ui-chip-clear-icon"></div>`;

			this.#clearIconInstance = new Icon({
				icon: Outline.CROSS_M,
			});

			this.#clearIconInstance.renderTo(this.#clearIconElement);

			return this.#clearIconElement;
		}

		return null;
	}

	#renderLock(): HTMLElement | null
	{
		if (this.#lock)
		{
			this.#lockIconElement = Tag.render`<div class="ui-chip-lock"></div>`;

			this.#lockIconInstance = new Icon({
				icon: Outline.LOCK_M,
			});

			this.#lockIconInstance.renderTo(this.#lockIconElement);

			return this.#lockIconElement;
		}

		return null;
	}

	#bindEvents(): void
	{
		if (this.#wrapper)
		{
			Event.bind(this.#wrapper, 'click', this.#handleClick.bind(this));
			Event.bind(this.#wrapper, 'keydown', this.#handleKeydown.bind(this));

			this.#bindClearEvent();
		}
	}

	#bindClearEvent(): void
	{
		if (this.#withClear && this.#wrapper)
		{
			const clearIcon = this.#wrapper.querySelector('.ui-chip-clear-icon');
			if (clearIcon)
			{
				Event.bind(clearIcon, 'click', this.#handleClear.bind(this));
			}
		}
	}

	#unbindClearEvent(): void
	{
		if (this.#wrapper)
		{
			const clearIcon = this.#wrapper.querySelector('.ui-chip-clear-icon');
			if (clearIcon)
			{
				Event.unbindAll(clearIcon);
			}
		}
	}

	#handleClick(event: MouseEvent): void
	{
		if (Type.isFunction(this.#onClick))
		{
			this.#onClick(event);
		}
	}

	#handleKeydown(event: KeyboardEvent): void
	{
		if (event.key === 'Enter' && !(event.ctrlKey || event.metaKey) && Type.isFunction(this.#onClick))
		{
			this.#onClick(event);
		}
	}

	#handleClear(event: MouseEvent): void
	{
		event.stopPropagation();

		if (Type.isFunction(this.#onClear))
		{
			this.#onClear(event);
		}
	}

	#updateClasses(): void
	{
		if (!this.#wrapper)
		{
			return;
		}

		this.#wrapper.className = `ui-chip ${this.#getModifierClasses()}`;
	}

	getWrapper(): ?HTMLElement
	{
		return this.#wrapper;
	}

	setSize(size: string): void
	{
		this.#size = size;
		this.#updateClasses();
	}

	getSize(): string
	{
		return this.#size;
	}

	setDesign(design: string): void
	{
		this.#design = design;
		this.#updateClasses();
	}

	getDesign(): string
	{
		return this.#design;
	}

	setIcon(icon: ?string): void
	{
		this.#icon = icon;
		this.#updateIcon();
	}

	getIcon(): ?string
	{
		return this.#icon;
	}

	setIconColor(color: ?string): void
	{
		this.#iconColor = color;
		if (this.#iconInstance)
		{
			this.#iconInstance.setColor(color);
		}
	}

	getIconColor(): ?string
	{
		return this.#iconColor;
	}

	setIconBackground(background: ?string): void
	{
		this.#iconBackground = background;
		this.#updateIcon();
	}

	getIconBackground(): ?string
	{
		return this.#iconBackground;
	}

	setImage(image: ?ChipImage): void
	{
		this.#image = image;
		this.#updateIcon();
	}

	getImage(): ?ChipImage
	{
		return this.#image;
	}

	setText(text: string): void
	{
		this.#text = text;

		if (this.#wrapper)
		{
			const textElement = this.#wrapper.querySelector('.ui-chip-text');
			if (textElement)
			{
				textElement.textContent = text;
			}

			this.#updateClasses();
		}
	}

	getText(): string
	{
		return this.#text;
	}

	setRounded(rounded: boolean): void
	{
		this.#rounded = rounded === true;
		this.#updateClasses();
	}

	isRounded(): boolean
	{
		return this.#rounded;
	}

	setWithClear(withClear: boolean): void
	{
		this.#withClear = withClear === true;
		this.#updateClearIcon();
		this.#updateClasses();
	}

	isWithClear(): boolean
	{
		return this.#withClear;
	}

	setDropdown(dropdown: boolean): void
	{
		this.#dropdown = dropdown === true;
		this.#updateDropdownIcon();
		this.#updateClasses();
	}

	isDropdown(): boolean
	{
		return this.#dropdown;
	}

	setDropdownActive(active: boolean): void
	{
		this.#dropdownActive = active === true;
		this.#updateDropdownStyle();
	}

	isDropdownActive(): boolean
	{
		return this.#dropdownActive;
	}

	setLock(lock: boolean): void
	{
		this.#lock = lock === true;
		this.#updateLock();
		this.#updateClasses();
	}

	isLock(): boolean
	{
		return this.#lock;
	}

	setCompact(compact: boolean = true): void
	{
		this.#compact = compact;
		this.#updateClasses();
	}

	isCompact(): boolean
	{
		return this.#compact;
	}

	setTrimmable(trimmable: boolean): void
	{
		this.#trimmable = trimmable === true;
		this.#updateClasses();
	}

	isTrimmable(): boolean
	{
		return this.#trimmable;
	}

	setOnClick(callback: ?Function): void
	{
		this.#onClick = callback;
	}

	setOnClear(callback: ?Function): void
	{
		this.#onClear = callback;
	}

	#updateIcon(): void
	{
		if (!this.#wrapper)
		{
			return;
		}

		const oldIcon = this.#wrapper.querySelector('.ui-chip-icon');
		if (oldIcon)
		{
			Dom.remove(oldIcon);
		}

		this.#iconInstance = null;
		this.#iconElement = null;

		const newIcon = this.#renderIcon();
		if (newIcon)
		{
			const textElement = this.#wrapper.querySelector('.ui-chip-text');
			if (textElement)
			{
				Dom.insertBefore(newIcon, textElement);
			}
			else
			{
				this.#wrapper.prepend(newIcon);
			}
		}

		this.#updateClasses();
	}

	#updateDropdownIcon(): void
	{
		if (!this.#wrapper)
		{
			return;
		}

		const oldDropdownIcon = this.#wrapper.querySelector('.ui-chip-dropdown-icon');
		if (oldDropdownIcon)
		{
			Dom.remove(oldDropdownIcon);
		}

		this.#dropdownIconInstance = null;
		this.#dropdownIconElement = null;

		const newDropdownIcon = this.#renderDropdownIcon();
		if (newDropdownIcon)
		{
			// Вставляем перед clear иконкой, если она есть, или перед замком
			const clearIcon = this.#wrapper.querySelector('.ui-chip-clear-icon');
			const lockElement = this.#wrapper.querySelector('.ui-chip-lock');

			if (clearIcon)
			{
				Dom.insertBefore(newDropdownIcon, clearIcon);
			}
			else if (lockElement)
			{
				Dom.insertBefore(newDropdownIcon, lockElement);
			}
			else
			{
				this.#wrapper.append(newDropdownIcon);
			}
		}
	}

	#updateClearIcon(): void
	{
		if (!this.#wrapper)
		{
			return;
		}

		const oldClearIcon = this.#wrapper.querySelector('.ui-chip-clear-icon');
		if (oldClearIcon)
		{
			this.#unbindClearEvent();
			Dom.remove(oldClearIcon);
		}

		// Очищаем старый экземпляр
		this.#clearIconInstance = null;
		this.#clearIconElement = null;

		const newClearIcon = this.#renderClearIcon();
		if (newClearIcon)
		{
			const lockElement = this.#wrapper.querySelector('.ui-chip-lock');
			if (lockElement)
			{
				Dom.insertBefore(newClearIcon, lockElement);
			}
			else
			{
				this.#wrapper.append(newClearIcon);
			}

			this.#bindClearEvent();
		}
	}

	#updateLock(): void
	{
		if (!this.#wrapper)
		{
			return;
		}

		const oldLock = this.#wrapper.querySelector('.ui-chip-lock');
		if (oldLock)
		{
			Dom.remove(oldLock);
		}

		this.#lockIconInstance = null;
		this.#lockIconElement = null;

		const newLock = this.#renderLock();
		if (newLock)
		{
			this.#wrapper.append(newLock);
		}
	}

	#updateDropdownStyle(): void
	{
		if (this.#dropdownIconElement && this.#dropdownActive)
		{
			Dom.style(this.#dropdownIconElement, 'transform', 'rotate(180deg)');
		}
		else if (this.#dropdownIconElement)
		{
			Dom.style(this.#dropdownIconElement, 'transform', '');
		}
	}

	destroy(): void
	{
		if (this.#wrapper)
		{
			Event.unbindAll(this.#wrapper);

			if (this.#withClear)
			{
				const clearIcon = this.#wrapper.querySelector('.ui-chip-clear-icon');
				if (clearIcon)
				{
					Event.unbindAll(clearIcon);
				}
			}

			Dom.remove(this.#wrapper);
		}

		this.#iconInstance = null;
		this.#dropdownIconInstance = null;
		this.#clearIconInstance = null;
		this.#lockIconInstance = null;
		this.#wrapper = null;
		this.#iconElement = null;
		this.#textElement = null;
		this.#dropdownIconElement = null;
		this.#clearIconElement = null;
		this.#lockIconElement = null;
	}
}
