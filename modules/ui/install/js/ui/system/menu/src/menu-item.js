import { Dom, Event, Tag, Text, Type } from 'main.core';
import { Button as UiButton } from 'ui.buttons';
import { Counter, CounterColor } from 'ui.cnt';
import { Outline } from 'ui.icon-set.api.core';
import 'ui.icon-set.outline';

import { Menu } from './menu';
import { mouse } from './mouse';
import { MenuItemDesign } from './const';
import type { MenuItemOptions, MenuItemCallbacks } from './types';

import './menu-item.css';

export class MenuItem
{
	#options: MenuItemOptions;
	#callbacks: MenuItemCallbacks;

	#subMenu: Menu;
	#element: HTMLElement;
	#showTimeout: number;
	#closeTimeout: number;
	#subMenuHovered: boolean;

	constructor(options: MenuItemOptions, callbacks: MenuItemCallbacks)
	{
		const defaultItemOptions: MenuItemOptions = {
			closeOnSubItemClick: true,
		};

		this.#options = { ...defaultItemOptions, ...options };
		this.#callbacks = callbacks;
	}

	getOptions(): MenuItemOptions
	{
		return this.#options;
	}

	getSubMenu(): Menu
	{
		return this.#subMenu;
	}

	render(): HTMLElement
	{
		if (this.#element)
		{
			return this.#element;
		}

		const uiButtonOptions = this.#options.uiButtonOptions;
		const isUiButton = Boolean(uiButtonOptions);
		const classNameIsUiButton = isUiButton ? ' --is-ui-button' : '';
		const classNameDesign = this.#options.design ? ` --${this.#options.design}` : '';

		this.#element = Tag.render`
			<div class="ui-popup-menu-item${classNameIsUiButton}${classNameDesign}">
				${isUiButton
					? new UiButton(uiButtonOptions).render()
					: Tag.render`
						<button
							class="ui-popup-menu-item-action"
							title="${Text.encode(this.#options.title)}"
							onclick="${this.#options.onClick}"
							onmouseenter="${this.#onMouseEnter}"
							onmouseleave="${this.#onMouseLeave}"
						>${this.#renderHeader()}${this.#renderButtons()}</button>
					`
				}
			</div>
		`;

		return this.#element;
	}

	showSubMenu = (): void => {
		this.#subMenuHovered = false;
		this.#subMenu ??= new Menu({
			...this.#options.subMenu,
			targetContainer: this.#callbacks.getTargetContainer(),
			autoHide: false,
			items: this.#options.subMenu.items.map((itemOptions: MenuItemOptions): MenuItemOptions => {
				if (!itemOptions)
				{
					return null;
				}

				return {
					...itemOptions,
					onClick: () => this.#onSubMenuItemClick(itemOptions),
				};
			}),
			offsetLeft: this.#element.offsetWidth,
			offsetTop: -this.#element.offsetHeight,
			bindOptions: {
				forceBindPosition: true,
				forceTop: true,
				forceLeft: true,
			},
			events: {
				onFirstShow: this.#onFirstShow,
				onShow: this.#onShow,
				onClose: this.#onClose,
				onDestroy: this.#onClose,
			},
		});

		this.#subMenu.show(this.#element);
	};

	adjustSubMenu = (): void => {
		if (!this.#subMenu)
		{
			return;
		}

		let offsetLeft = this.#element.offsetWidth;
		let offsetTop = -this.#element.offsetHeight;
		this.#subMenu.getPopup().setOffset({ offsetLeft, offsetTop });
		this.#subMenu.getPopup().adjustPosition();

		const targetContainer = this.#callbacks.getTargetContainer();
		const targetIsBody = targetContainer === document.body;
		const targetRect = {
			...targetContainer.getBoundingClientRect().toJSON(),
			...(targetIsBody ? { top: 0 } : null),
			...(targetIsBody ? { right: window.innerWidth } : null),
			...(targetIsBody ? { bottom: window.innerHeight } : null),
			...(targetIsBody ? { left: 0 } : null),
		};

		let popupRect = this.#subMenu.getPopupContainer().getBoundingClientRect();
		if (popupRect.right >= targetRect.right)
		{
			offsetLeft = -popupRect.width;
		}

		if (popupRect.bottom >= targetRect.bottom)
		{
			offsetTop = -popupRect.height;
		}

		this.#subMenu.getPopup().setOffset({ offsetLeft, offsetTop });
		this.#subMenu.getPopup().adjustPosition();

		popupRect = this.#subMenu.getPopupContainer().getBoundingClientRect();
		if (popupRect.left <= targetRect.left)
		{
			offsetLeft = this.#element.offsetWidth;
		}

		if (popupRect.top <= targetRect.top)
		{
			offsetTop = -this.#element.offsetHeight;
		}

		this.#subMenu.getPopup().setOffset({ offsetLeft, offsetTop });
		this.#subMenu.getPopup().adjustPosition();
	};

	closeSubMenu = (): void => {
		clearTimeout(this.#showTimeout);
		this.#subMenu?.close();
	};

	closeSubMenuWithTimeout(): void
	{
		clearTimeout(this.#closeTimeout);
		this.#closeTimeout = setTimeout(this.closeSubMenu, 200);
	}

	destroy(): void
	{
		this.#subMenu?.destroy();
	}

	#onMouseEnter = (): void => {
		if (this.#options.design === MenuItemDesign.Disabled)
		{
			return;
		}

		this.#subMenuHovered = false;
		this.#callbacks.onMouseEnter?.();
		if (this.#options.subMenu)
		{
			clearTimeout(this.#closeTimeout);
			this.#showTimeout = setTimeout(this.showSubMenu, 200);
		}
	};

	#onMouseLeave = (event: MouseEvent): void => {
		clearTimeout(this.#showTimeout);

		const subMenuContainer = this.#subMenu?.getPopupContainer();
		if (!this.#subMenuHovered && subMenuContainer && !subMenuContainer.contains(event.relatedTarget))
		{
			const distance = mouse.getPosition().left - subMenuContainer.getBoundingClientRect().left;
			const distanceDelta = Math.abs(distance) - Math.abs(distance + mouse.getDelta().left);
			if (distanceDelta <= 1)
			{
				this.closeSubMenu();
			}
			else
			{
				this.closeSubMenuWithTimeout();
			}
		}
	};

	#onSubMenuItemClick(item: MenuItemOptions): void
	{
		item.onClick?.();

		if (!item.subMenu && this.#options.closeOnSubItemClick)
		{
			this.#callbacks.onSubMenuItemClick?.();
		}
	}

	#onFirstShow = (): void => {
		Event.bind(this.#subMenu.getPopupContainer(), 'mouseenter', (): void => {
			clearTimeout(this.#closeTimeout);
			this.#subMenuHovered = true;
		});
	};

	#onShow = (): void => {
		this.adjustSubMenu();
		Dom.addClass(this.#element, '--hovered');
	};

	#onClose = (): void => {
		Dom.removeClass(this.#element, '--hovered');
	};

	#renderHeader(): HTMLElement
	{
		return Tag.render`
			<div class="ui-popup-menu-item-header">
				${this.#renderTitle()}
				${this.#renderSubtitle()}
			</div>
		`;
	}

	#renderTitle(): HTMLElement
	{
		return Tag.render`
			<div class="ui-popup-menu-item-title">
				${this.#renderLock()}
				<div class="ui-popup-menu-item-title-text">${Text.encode(this.#options.title)}</div>
				${this.#renderBadgeText()}
			</div>
		`;
	}

	#renderLock(): HTMLElement
	{
		if (!this.#options.isLocked)
		{
			return '';
		}

		return Tag.render`
			<div class="ui-popup-menu-item-lock">
				<div class="ui-icon-set --${Outline.LOCK_L}"></div>
			</div>
		`;
	}

	#renderBadgeText(): HTMLElement
	{
		if (!Type.isStringFilled(this.#options.badgeText?.title))
		{
			return '';
		}

		const color = this.#options.badgeText.color;
		const style = color ? `--badge-color: ${color};` : '';

		return Tag.render`
			<div class="ui-popup-menu-item-badge-text" style="${style}">
				${Text.encode(this.#options.badgeText.title)}
			</div>
		`;
	}

	#renderSubtitle(): HTMLElement
	{
		if (!Type.isStringFilled(this.#options.subtitle))
		{
			return '';
		}

		return Tag.render`
			<div class="ui-popup-menu-item-subtitle">${Text.encode(this.#options.subtitle)}</div>
		`;
	}

	#renderButtons(): HTMLElement
	{
		return Tag.render`
			<div class="ui-popup-menu-item-buttons">
				${this.#renderCheck()}
				${this.#renderExtra()}
				${this.#renderCounter()}
				${this.#renderIcon()}
				${this.#renderArrow()}
			</div>
		`;
	}

	#renderCheck(): HTMLElement
	{
		if (!Type.isBoolean(this.#options.isSelected))
		{
			return '';
		}

		if (!this.#options.isSelected)
		{
			return Tag.render`
				<div class="ui-popup-menu-item-check"></div>
			`;
		}

		return Tag.render`
			<div class="ui-popup-menu-item-check">
				<div class="ui-icon-set --${Outline.CHECK_L}"></div>
			</div>
		`;
	}

	#renderExtra(): HTMLElement
	{
		if (!this.#options.extraIcon)
		{
			return '';
		}

		const extra = Tag.render`
			<div class="ui-popup-menu-item-extra ${this.#options.extraIcon.isSelected ? '--selected' : ''}">
				<div class="ui-icon-set --${this.#options.extraIcon.icon}"></div>
			</div>
		`;

		Event.bind(extra, 'click', (event: MouseEvent): void => {
			this.#options.extraIcon.onClick();
			event.stopPropagation();
		}, true);

		return extra;
	}

	#renderCounter(): HTMLElement
	{
		if (!this.#options.counter)
		{
			return '';
		}

		if (!this.#options.counter.value)
		{
			return Tag.render`
				<div class="ui-popup-menu-item-counter"></div>
			`;
		}

		return Tag.render`
			<div class="ui-popup-menu-item-counter">
				${new Counter({ color: CounterColor.DANGER, ...this.#options.counter }).render()}
			</div>
		`;
	}

	#renderIcon(): HTMLElement
	{
		if (this.#options.icon)
		{
			return Tag.render`
				<div class="ui-popup-menu-item-icon">
					<div class="ui-icon-set --${this.#options.icon}"></div>
				</div>
			`;
		}

		if (this.#options.svg)
		{
			return Tag.render`
				<div class="ui-popup-menu-item-svg">
					${this.#options.svg}
				</div>
			`;
		}

		return '';
	}

	#renderArrow(): HTMLElement
	{
		if (!this.#options.subMenu)
		{
			return '';
		}

		return Tag.render`
			<div class="ui-popup-menu-item-arrow">
				<div class="ui-icon-set --${Outline.CHEVRON_RIGHT_L}"></div>
			</div>
		`;
	}
}
