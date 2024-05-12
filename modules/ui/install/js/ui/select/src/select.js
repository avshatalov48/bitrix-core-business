import { Type, Dom, Tag, Loc, bind } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu, Popup, MenuItemOptions, PopupOptions } from 'main.popup';
import './select.css';
import 'ui.';

export type SelectOption = {
	label: string;
	value: string;
}

export type SelectOptions = {
	options: SelectOption[];
	value: string;
	placeholder: string;
	isSearchable: boolean;
	containerClassname: string;
	popupParams: PopupOptions;
}

const ScrollDirection = Object.freeze({
	TOP: -1,
	BOTTOM: 1,
	NONE: 0,
});

export class Select extends EventEmitter
{
	#placeholder: string = '';
	#isSearchable: boolean = false;
	#isSearching: boolean = false;
	#searchValue: string = '';
	#selectedOption: SelectOption | null = null;
	#options: SelectOption[] = [];
	#container: HTMLElement | null = null;
	#containerClassname: string = '';
	#menu: Menu | null = null;
	#emptySearchPopup: Popup | null = null;
	#highlightedOptionIndex: number = 0;
	#popupParams: PopupOptions = {};

	constructor(options: SelectOptions)
	{
		super();
		this.setEventNamespace('BX.UI.Select');
		this.#placeholder = Type.isString(options.placeholder) ? options.placeholder : '';
		this.#isSearchable = options.isSearchable === true || false;
		this.#options = Array.isArray(options.options) ? options.options : [];
		this.#popupParams = Type.isPlainObject(options.popupParams) ? options.popupParams : {};
		this.#selectedOption = this.#findOptionByValue(options.value) || null;
		this.#containerClassname = Type.isString(options?.containerClassname) ? options.containerClassname : '';
		this.#highlightedOptionIndex = this.#getOptionIndex(this.#selectedOption?.value) || 0;
		this.#renderContainer();
	}

	renderTo(targetContainer: HTMLElement): HTMLElement | null
	{
		if (Type.isDomNode(targetContainer))
		{
			Dom.clean(targetContainer);
			this.#renderContainer();
			Dom.append(this.#container, targetContainer);

			return targetContainer;
		}

		return null;
	}

	#renderContainer(): HTMLElement
	{
		this.#container = Tag.render`
			<div class="${this.#getContainerClassname()}">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<input
					ref="input"
					class="ui-ctl-element"
					type="text"
					placeholder="${this.#placeholder}"
					${this.#isInputReadonly() ? 'readonly' : ''}
					value="${this.#selectedOption?.label || ''}"
				>
			</div>
		`;

		bind(this.#container.input, 'input', this.#handleInput.bind(this));
		bind(this.#container.input, 'focus', this.#handleFocus.bind(this));
		bind(this.#container.input, 'blur', this.#handleBlur.bind(this));
		bind(this.#container.input, 'mouseup', this.#handleInputClick.bind(this));
		bind(this.#container.input, 'keydown', this.#handleKeyDown.bind(this));

		this.#container = this.#container.root;

		return this.#container;
	}

	#isInputReadonly(): boolean
	{
		return !this.#isSearchable || (!this.isMenuShown() && !this.#emptySearchPopup);
	}

	#handleInputClick(): void
	{
		if (this.getInput() === document.activeElement)
		{
			setTimeout(() => {
				this.showMenu();
			}, 100);
		}
	}

	showMenu(): void
	{
		if (!this.#menu)
		{
			this.#createMenu();
		}

		this.#menu.show();
		this.#updateMenu();
	}

	#createMenu(): Menu
	{
		const { width } = Dom.getPosition(this.#container);
		const events = this.#popupParams?.events ?? {};
		this.#menu = new Menu({
			width,
			bindElement: this.#container,
			items: this.#getMenuItems(),
			closeByEsc: true,
			autoHide: false,
			className: 'select-menu-popup',
			...this.#popupParams,
			events: {
				...events,
				onAfterClose: () => {
					if (!this.#emptySearchPopup)
					{
						this.#searchValue = '';
						this.#setSelectedOption(this.#selectedOption);
						this.#updateSelect();
						if (events.onAfterClose)
						{
							events.onAfterClose();
						}
					}
				},
			},
		});

		return this.#menu;
	}

	hideMenu(): void
	{
		if (this.#menu)
		{
			this.#menu.close();
		}
	}

	#getMenuItems(): MenuItemOptions[]
	{
		if (this.#isSearching)
		{
			return this.#getFilteredOptions()
				.map((option, index) => {
					return this.#getMenuItemFromOption(option, index === this.#highlightedOptionIndex);
				});
		}

		return this.#options.map((option, index) => {
			return this.#getMenuItemFromOption(option, index === this.#highlightedOptionIndex);
		});
	}

	#handleInput(e): void
	{
		e.preventDefault();
		this.#highlightedOptionIndex = 0;
		this.#isSearching = true;
		this.#searchValue = e.target.value;
		this.#updateMenu();
	}

	#handleKeyDown(e): void
	{
		const { keyCode } = e;
		const arrowUpKeyCode = 38;
		const arrowDownKeyCode = 40;
		const enterKeyCode = 13;
		const spaceKeyCode = 32;

		// eslint-disable-next-line default-case
		switch (keyCode)
		{
			case enterKeyCode: this.#handleEnterKey(e); break;
			case spaceKeyCode: this.#handleSpaceKey(e); break;
			case arrowUpKeyCode: this.#handleArrowUpKey(e); break;
			case arrowDownKeyCode: this.#handleArrowDownKey(e); break;
		}
	}

	#handleSpaceKey(e): void
	{
		if (!this.isMenuShown() && !this.#emptySearchPopup)
		{
			e.preventDefault();
			this.showMenu();
			this.#updateSelect();
		}
	}

	#handleArrowUpKey(e): void
	{
		e.preventDefault();
		if (!this.isMenuShown() || this.#highlightedOptionIndex === 0)
		{
			return;
		}

		this.#highlightedOptionIndex--;

		this.#scrollToHighlightedItem();
		this.#highlightOption(this.#highlightedOptionIndex);
	}

	#handleArrowDownKey(e): void
	{
		e.preventDefault();
		if (!this.isMenuShown() || this.#highlightedOptionIndex === this.#getMenuItems().length - 1)
		{
			return;
		}

		this.#highlightedOptionIndex++;
		this.#scrollToHighlightedItem();
		this.#highlightOption(this.#highlightedOptionIndex);
	}

	#handleEnterKey(e): void
	{
		e.preventDefault();
		const options = this.#getFilteredOptions();
		this.#selectedOption = options[this.#highlightedOptionIndex];
		this.hideMenu();
	}

	#updateMenu(): void
	{
		if (!this.#menu)
		{
			return;
		}

		this.#options.forEach(({ value }) => {
			this.#menu.removeMenuItem(value, {
				destroyEmptyPopup: false,
			});
		});

		const filteredOptions = this.#getFilteredOptions(this.#searchValue);

		if (filteredOptions.length > 0)
		{
			if (!this.isMenuShown())
			{
				this.showMenu();
			}
			this.#hideEmptySearchPopup();
			filteredOptions.forEach((option, index) => {
				this.#menu.addMenuItem(this.#getMenuItemFromOption(option, index === this.#highlightedOptionIndex), null);
			});

			this.#scrollToHighlightedItem();
			this.#highlightOption(this.#highlightedOptionIndex);
		}
		else
		{
			this.#showEmptySearchPopup();
			this.hideMenu();
		}
	}

	#getMenuItemFromOption(option, isHoverOption: boolean = false): MenuItemOptions
	{
		const isHover = isHoverOption === true;
		const className = `ui-select__menu-item menu-popup-no-icon ${isHover ? 'menu-popup-item-open' : ''}`;

		return ({
			id: option.value,
			text: option.label,
			onclick: () => {
				this.#selectedOption = option;
			},
			className,
		});
	}

	#getFilteredOptions(): SelectOption[]
	{
		return this.#options.filter(this.#getOptionFilter(this.#searchValue));
	}

	#getOptionFilter(searchStr): (option: SelectOption) => boolean
	{
		const lowerCaseSearchStr = Type.isString(searchStr) ? searchStr.toLowerCase() : '';

		return (option) => {
			const lowerCaseOptionLabel = option.label.toLowerCase();

			return lowerCaseOptionLabel.indexOf(lowerCaseSearchStr) === 0;
		};
	}

	#showEmptySearchPopup(): void
	{
		if (!this.#emptySearchPopup || !this.#emptySearchPopup?.isShown())
		{
			const { width } = Dom.getPosition(this.#container);
			const events = this.#popupParams?.events ?? {};

			this.#emptySearchPopup = new Popup({
				width,
				bindElement: this.#container,
				content: Loc.getMessage('UI_SELECT_NOTHING_FOUND'),
				closeByEsc: true,
				...this.#popupParams,
				events: {
					...events,
					onAfterClose: () => {
						this.#emptySearchPopup = null;
						this.#setSelectedOption(this.#selectedOption);
						if (!this.isMenuShown())
						{
							this.#searchValue = '';
							this.#updateSelect();
							if (events.onAfterClose)
							{
								events.onAfterClose();
							}
						}
					},
				},
			});

			this.#emptySearchPopup.show();
		}
	}

	#hideEmptySearchPopup(): void
	{
		if (this.#emptySearchPopup)
		{
			this.#emptySearchPopup.destroy();
			this.#emptySearchPopup = null;
		}
	}

	#setSelectedOption(option: SelectOption): void
	{
		if (!option)
		{
			this.#selectedOption = null;

			return;
		}

		this.emit('update', option.value);
		this.#searchValue = '';
		const input = this.getInput();
		input.value = option.label;
		this.#highlightedOptionIndex = this.#getOptionIndex(option.value);
		this.#selectedOption = option;
	}

	getInput(): HTMLElement | null
	{
		return this.#container.querySelector('input');
	}

	getValue(): string
	{
		return this.#selectedOption?.value || '';
	}

	setValue(value: string): void
	{
		const option = this.#findOptionByValue(value);
		this.#setSelectedOption(option);
	}

	#findOptionByValue(value: string): SelectOption | null
	{
		return this.#options.find((option) => {
			return option.value === value;
		});
	}

	#highlightOption(optionIndex: number): void
	{
		if (!this.#menu)
		{
			return;
		}

		const menuItems = this.#menu.itemsContainer.children;

		for (let i = 0; i < menuItems.length; i++)
		{
			const item = menuItems.item(i);
			Dom.removeClass(item, 'menu-popup-item-open');
			if (i === optionIndex)
			{
				Dom.addClass(item, 'menu-popup-item-open');
			}
		}
	}

	#scrollToHighlightedItem(): void
	{
		const popupContent: HTMLElement = this.#menu.getPopupWindow().getContentContainer();
		const menuItems: HTMLCollection = this.#menu.itemsContainer.children;
		const highlightedItem = menuItems.item(this.#highlightedOptionIndex);

		const {
			height: popupContentHeight,
		} = Dom.getPosition(popupContent);

		const {
			height: highlightedItemHeight,
		} = Dom.getPosition(highlightedItem);

		const direction = this.#getScrollDirectionToHighlightedItem(popupContent, highlightedItem);

		if (direction !== ScrollDirection.NONE)
		{
			popupContent.scroll({
				left: 0,
				top: (highlightedItemHeight * (this.#highlightedOptionIndex) + direction * popupContentHeight),
				behavior: 'smooth',
			});
		}
	}

	#getScrollDirectionToHighlightedItem(popupContent: HTMLElement, highlightedItem: HTMLElement): number
	{
		const {
			bottom: popupContentBottom,
			top: popupContentTop,
		} = Dom.getPosition(popupContent);

		const {
			bottom: highlightedItemBottom,
			top: highlightedItemTop,
		} = Dom.getPosition(highlightedItem);

		if (popupContentTop > highlightedItemTop)
		{
			return ScrollDirection.TOP;
		}

		if (popupContentBottom < highlightedItemBottom)
		{
			return ScrollDirection.BOTTOM;
		}

		return ScrollDirection.NONE;
	}

	#getOptionIndex(optionValue: string): number
	{
		return this.#options.findIndex((option) => {
			return option.value === optionValue;
		});
	}

	isMenuShown(): boolean
	{
		return this.#menu && this.#menu.getPopupWindow().isShown();
	}

	#handleBlur(): void
	{
		this.hideMenu();
		this.#hideEmptySearchPopup();
	}

	#handleFocus(e): void
	{
		setTimeout(() => {
			this.showMenu();
			this.#updateSelect();
		}, 100);
		e.preventDefault();
	}

	#updateSelect(): void
	{
		this.#updateInput();
		this.#updateContainerClassname();
	}

	#updateInput(): void
	{
		const input = this.getInput();

		if (this.#isInputReadonly())
		{
			input.setAttribute('readonly', 'readonly');
		}
		else
		{
			input.removeAttribute('readonly');
		}
	}

	#updateContainerClassname(): void
	{
		this.#container.className = this.#getContainerClassname();
	}

	#getContainerClassname(): string
	{
		const openMenuClassnameModifier = this.isMenuShown() || this.#emptySearchPopup ? '--open' : '';

		return `ui-select ui-ctl ui-ctl-after-icon ui-ctl-dropdown ${this.#containerClassname} ${openMenuClassnameModifier}`;
	}
}
