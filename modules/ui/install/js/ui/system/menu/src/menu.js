import { Dom, Event, Tag } from 'main.core';
import { Popup } from 'main.popup';
import { Main } from 'ui.icon-set.api.core';
import 'ui.icon-set.main';

import { MenuItem } from './menu-item';
import { MenuSectionDesign, MenuRichHeaderDesign } from './const';
import type { MenuOptions, MenuItemOptions, MenuSectionOptions } from './types';
import './menu.css';

export class Menu
{
	#options: MenuOptions;
	#items: MenuItem[];
	#popup: Popup;
	#container: HTMLElement;

	constructor(options: MenuOptions)
	{
		const defaultOptions: MenuOptions = {
			noAllPaddings: true,
			autoHide: true,
			closeByEsc: true,
			autoHideHandler: this.#shouldHide,
			closeOnItemClick: true,
		};

		this.#options = { ...defaultOptions, ...options };
	}

	getOptions(): MenuOptions
	{
		return this.#options;
	}

	getPopup(): Popup
	{
		return this.#popup;
	}

	getPopupContainer(): HTMLElement
	{
		return this.#popup.getPopupContainer();
	}

	show(bindElement: HTMLElement): void
	{
		this.#items ??= this.#prepareItems(this.#options.items);

		this.#popup ??= new Popup({
			...this.#options,
			content: this.#render(),
			events: {
				...this.#options.events,
				onClose: this.#onPopupClose,
				onDestroy: this.#onPopupDestroy,
				onBeforeAdjustPosition: this.#onBeforeAdjustPosition,
			},
		});

		this.#popup.setBindElement(bindElement ?? this.#options.bindElement);
		this.#popup.show();
	}

	updateItems(itemsOptions: MenuItemOptions[]): void
	{
		const openedItem = this.#items?.find((item: MenuItem) => item.getSubMenu()?.getPopup()?.isShown());

		this.#items?.forEach((item: MenuItem): void => item.destroy());
		this.#items = this.#prepareItems(itemsOptions);
		this.#render();

		if (openedItem && !openedItem?.getSubMenu().getOptions().closeOnItemClick)
		{
			this.#items.find((item: MenuItem) => item.getOptions().id === openedItem.getOptions().id)?.showSubMenu();
		}
	}

	close(): void
	{
		this.#popup.close();
	}

	destroy(): void
	{
		this.#items?.forEach((item) => item.destroy());
		this.#popup.destroy();
	}

	#shouldHide = (event: MouseEvent): void => {
		const notSelfClick = !this.getPopupContainer().contains(event.target);
		const notSubMenuClick = !this.#items.some((item) => {
			return item.getSubMenu()?.getPopupContainer()?.contains(event.target);
		});

		return notSelfClick && notSubMenuClick;
	};

	#onPopupClose = (): void => {
		this.#items.forEach((item: MenuItem): void => item.closeSubMenu());
		this.#options.events?.onClose?.();
	};

	#onPopupDestroy = (): void => {
		this.#options.events?.onDestroy?.();
		this.destroy();
	};

	#onBeforeAdjustPosition = (): void => {
		this.#items.forEach((item: MenuItem): void => item.adjustSubMenu());
	};

	#prepareItems(itemsOptions: MenuItemOptions[]): MenuItem[]
	{
		const items: MenuItem[] = itemsOptions.map((itemOptions: ?MenuItemOptions): MenuItem => {
			if (!itemOptions)
			{
				return null;
			}

			const item: MenuItem = new MenuItem(
				{
					...itemOptions,
					onClick: (): void => this.#onItemClick(itemOptions),
				},
				{
					getTargetContainer: () => this.getPopup().getTargetContainer(),
					onMouseEnter: () => items.filter((it) => it !== item).forEach((it) => it.closeSubMenuWithTimeout()),
					onSubMenuItemClick: this.#onSubMenuItemClick,
				},
			);

			return item;
		}).filter((it) => it);

		return items;
	}

	#onItemClick = (itemOptions: MenuItemOptions): void => {
		itemOptions.onClick?.();

		if (!itemOptions.subMenu && this.#options.closeOnItemClick)
		{
			this.close();
		}
	};

	#onSubMenuItemClick = (): void => {
		if (this.#options.closeOnItemClick)
		{
			this.close();
		}
	};

	#render(): HTMLElement
	{
		this.#container ??= Tag.render`
			<div class="ui-popup-menu-container"></div>
		`;

		const itemsContainer = Tag.render`
			<div class="ui-popup-menu-items">
				${this.#renderRichHeader()}
				${this.#renderItems()}
			</div>
		`;

		Dom.clean(this.#container);
		Dom.append(itemsContainer, this.#container);

		return this.#container;
	}

	#renderRichHeader(): HTMLElement
	{
		if (!this.#options.richHeader)
		{
			return '';
		}

		const design = this.#options.richHeader.design ?? MenuRichHeaderDesign.Default;
		const richHeader = Tag.render`
			<div class="ui-popup-menu-rich-header --${design}">
				<div class="ui-popup-menu-rich-header-image">
					<div class="ui-icon-set --${this.#getRichHeaderIcon(design)}"></div>
				</div>
				<div class="ui-popup-menu-rich-header-header">
					${this.#renderRichHeaderSubtitle()}
					<div class="ui-popup-menu-rich-header-title">
						${this.#options.richHeader.title}
					</div>
				</div>
				<div class="ui-popup-menu-rich-header-buttons">
					${this.#renderRichHeaderIcon()}
				</div>
			</div>
		`;

		if (this.#options.richHeader.onClick)
		{
			Event.bind(richHeader, 'click', this.#options.richHeader.onClick);
		}

		return richHeader;
	}

	#getRichHeaderIcon(design: string): string
	{
		return {
			[MenuRichHeaderDesign.Default]: Main.DIAMOND,
			[MenuRichHeaderDesign.Copilot]: Main.COPILOT_AI,
		}[design] ?? Main.DIAMOND;
	}

	#renderRichHeaderSubtitle(): HTMLElement
	{
		if (!this.#options.richHeader.subtitle)
		{
			return '';
		}

		return Tag.render`
			<div class="ui-popup-menu-rich-header-subtitle">
				${this.#options.richHeader.subtitle}
			</div>
		`;
	}

	#renderRichHeaderIcon(): HTMLElement
	{
		if (!this.#options.richHeader.icon)
		{
			return '';
		}

		return Tag.render`
			<div class="ui-popup-menu-rich-header-icon">
				<div class="ui-icon-set --${this.#options.richHeader.icon}"></div>
			</div>
		`;
	}

	#renderItems(): HTMLElement[]
	{
		const baseSection = 'base';
		const itemsBySection = this.#items.reduce((result, item) => {
			const sectionCode = item.getOptions().sectionCode ?? baseSection;
			const sectionItems = result[sectionCode] ?? [];

			return {
				...result,
				[sectionCode]: [...sectionItems, item],
			};
		}, {});

		return [
			...(itemsBySection[baseSection]?.map((item) => item.render()) ?? []),
			...(this.#options.sections?.flatMap((options: MenuSectionOptions, index: number) => {
				const items = itemsBySection[options.code];
				if (!items)
				{
					return null;
				}

				const isFirstSection = !itemsBySection[baseSection] && index === 0;
				const withoutTitle = !options.title;

				return [
					!(isFirstSection && withoutTitle) && this.#renderSection(options),
					...(items.map((item) => item.render()) ?? []),
				];
			}) ?? []).filter((it) => it),
		];
	}

	#renderSection(options: MenuSectionOptions): HTMLElement
	{
		return Tag.render`
			<div class="ui-popup-menu-section --${options.design ?? MenuSectionDesign.Default}">
				${this.#renderSectionTitle(options.title)}
				<div class="ui-popup-menu-section-divider"></div>
			</div>
		`;
	}

	#renderSectionTitle(title: string): HTMLElement
	{
		if (!title)
		{
			return '';
		}

		return Tag.render`
			<div class="ui-popup-menu-section-title">${title}</div>
		`;
	}
}
