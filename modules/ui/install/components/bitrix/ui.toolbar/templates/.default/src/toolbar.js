import { bind, Dom, Event, Text, Type, Uri } from 'main.core';
import { type BaseEvent } from 'main.core.events';
import { Popup } from 'main.popup';
import { ButtonColor, ButtonIcon, ButtonManager } from 'ui.buttons';

import { TitleEditor, TitleEditorEvents, type TitleEditorOptions } from './title-editor';
import { ToolbarStar } from './toolbar-star';

export type ToolbarOptions = {
	titleMinWidth?: number;
	titleMaxWidth?: number;
	filterMinWidth?: number;
	filterMaxWidth?: number;
	id?: string;
	target: HTMLElement;
	buttonIds?: string[];
	titleEditor: TitleEditorOptions;
};

export const ToolbarEvents = {
	beforeStartEditing: 'beforeStartEditing',
	startEditing: 'startEditing',
	finishEditing: 'finishEditing',
};

export class Toolbar extends Event.EventEmitter
{
	titleMinWidth: number;
	titleMaxWidth: number;
	filterMinWidth: number;
	filterMaxWidth: number;
	id: string;
	toolbarContainer: HTMLElement;
	buttons: Object;
	buttonIds: string[];
	windowWidth: number;
	#copyLinkButton: HTMLElement;
	#titleEditor: ?TitleEditor;

	static TitleEditor = TitleEditor;
	static Star = ToolbarStar;

	// eslint-disable-next-line sonarjs/cognitive-complexity
	constructor(options: ToolbarOptions = {})
	{
		super(options);
		this.setEventNamespace('BX.UI.Toolbar');
		this.titleMinWidth = Type.isNumber(options.titleMinWidth) ? options.titleMinWidth : 158;
		this.titleMaxWidth = Type.isNumber(options.titleMaxWidth) ? options.titleMaxWidth : '';

		this.filterMinWidth = Type.isNumber(options.filterMinWidth) ? options.filterMinWidth : 300;
		this.filterMaxWidth = Type.isNumber(options.filterMaxWidth) ? options.filterMaxWidth : 748;

		this.id = Type.isStringFilled(options.id) ? options.id : Text.getRandom();
		this.toolbarContainer = options.target;

		if (!Type.isDomNode(this.toolbarContainer))
		{
			throw new Error('BX.UI.Toolbar: "target" parameter is required.');
		}

		this.titleContainer = this.toolbarContainer.querySelector('.ui-toolbar-title-box');
		this.filterContainer = this.toolbarContainer.querySelector('.ui-toolbar-filter-box');
		this.filterButtons = this.toolbarContainer.querySelector('.ui-toolbar-filter-buttons');
		this.rightButtons = this.toolbarContainer.querySelector('.ui-toolbar-right-buttons');
		this.afterTitleButtons = this.toolbarContainer.querySelector('.ui-toolbar-after-title-buttons');
		this.#copyLinkButton = this.toolbarContainer.querySelector('#ui-toolbar-copy-link-button');

		if (this.#copyLinkButton)
		{
			Event.bind(this.#copyLinkButton, 'click', this.#getClickOnCopyLinkButtonHandler());
		}

		if (!this.filterContainer)
		{
			this.filterMinWidth = 0;
			this.filterMaxWidth = 0;
		}

		this.buttons = Object.create(null);
		this.buttonIds = Type.isArray(options.buttonIds) ? options.buttonIds : [];

		if (this.buttonIds.length > 0)
		{
			this.buttonIds.forEach((buttonId) => {
				const button = ButtonManager.createByUniqId(buttonId);
				if (button)
				{
					const container = button.getContainer();
					container.originalWidth = container.offsetWidth;

					if (!button.getIcon() && !Type.isStringFilled(button.getDataSet().toolbarCollapsedIcon))
					{
						if (button.getColor() === ButtonColor.PRIMARY)
						{
							button.setDataSet({
								toolbarCollapsedIcon: ButtonIcon.ADD,
							});
						}
						else
						{
							console.warn(
								`BX.UI.Toolbar: the button "${button.getText()}" doesn't have an icon for collapsed mode. `
								+ 'Use the "data-toolbar-collapsed-icon" attribute.',
							);
						}
					}

					this.buttons[buttonId] = button;
				}
				else
				{
					console.warn(`BX.UI.Toolbar: the button "${buttonId}" wasn't initialized.`);
				}
			});
		}

		this.windowWidth = document.body.offsetWidth;
		this.reduceItemsWidth();

		bind(window, 'resize', () => {
			if (this.isWindowIncreased())
			{
				this.increaseItemsWidth();
			}
			else
			{
				this.reduceItemsWidth();
			}
		});

		if (options.titleEditor?.active === true)
		{
			this.#titleEditor = this.#initTitleEditor(options.titleEditor);
		}
	}

	getButtons(): Object
	{
		return this.buttons;
	}

	getButton(id): any | null
	{
		return id in this.buttons ? this.buttons[id] : null;
	}

	getId(): string
	{
		return this.id;
	}

	isWindowIncreased(): boolean
	{
		const previous = this.windowWidth;
		const current = document.body.offsetWidth;
		this.windowWidth = current;

		return current > previous;
	}

	getContainerSize(): number
	{
		return this.toolbarContainer.offsetWidth;
	}

	getInnerTotalWidth(): number
	{
		return this.toolbarContainer.scrollWidth;
	}

	reduceItemsWidth(): void
	{
		if (this.getInnerTotalWidth() <= this.getContainerSize())
		{
			return;
		}

		const buttons = Object.values(this.getButtons()).reverse();
		for (const button of buttons)
		{
			if (!button.getIcon() && !Type.isStringFilled(button.getDataSet()?.toolbarCollapsedIcon))
			{
				continue;
			}

			if (button.isCollapsed())
			{
				continue;
			}

			button.setCollapsed(true);

			if (!button.getIcon())
			{
				button.setIcon(button.getDataSet().toolbarCollapsedIcon);
			}

			if (this.getInnerTotalWidth() <= this.getContainerSize())
			{
				return;
			}
		}
	}

	increaseItemsWidth()
	{
		const buttons = Object.values(this.getButtons());
		for (const button of buttons)
		{
			const item = button.getContainer();
			if (!button.isCollapsed())
			{
				continue;
			}

			const newInnerWidth = this.titleMinWidth
				+ this.filterMinWidth
				+ (this.afterTitleButtons?.offsetWidth || 0)
				+ (this.filterButtons?.offsetWidth || 0)
				+ (this.rightButtons?.offsetWidth || 0)
				+ (item.originalWidth - item.offsetWidth);

			if (newInnerWidth > this.getContainerSize())
			{
				break;
			}

			button.setCollapsed(false);
			if (button.getIcon() === button.getDataSet().toolbarCollapsedIcon)
			{
				const icon = Type.isStringFilled(button.options.icon) ? button.options.icon : null;
				button.setIcon(icon);
			}
		}
	}

	setTitle(title: string): void
	{
		if (!this.titleContainer)
		{
			return;
		}

		const pagetitle = this.titleContainer.querySelector('#pagetitle');
		if (pagetitle)
		{
			pagetitle.textContent = title;
		}
	}

	getContainer(): ?HTMLElement
	{
		return this.toolbarContainer;
	}

	getRightButtonsContainer(): ?HTMLElement
	{
		return this.rightButtons;
	}

	getTitleEditor(): ?TitleEditor
	{
		return this.#titleEditor;
	}

	#getClickOnCopyLinkButtonHandler(): Function
	{
		let popup: ?Popup = null;

		return () => {
			if (popup !== null)
			{
				return;
			}

			const dataLink = Dom.attr(this.#copyLinkButton, 'data-link');
			const currentPageLink = window.location.href;
			let linkToCopy = Type.isStringFilled(dataLink) ? dataLink : currentPageLink;
			linkToCopy = Uri.removeParam(linkToCopy, ['IFRAME', 'IFRAME_TYPE']);

			const message = Dom.attr(this.#copyLinkButton, 'data-message');

			popup = new Popup({
				bindElement: this.#copyLinkButton,
				angle: true,
				darkMode: true,
				content: message,
				autoHide: true,
				cacheable: false,
			});

			popup.setOffset({
				offsetLeft: Dom.getPosition(this.#copyLinkButton).width / 2,
			});

			popup.show();

			BX.clipboard.copy(linkToCopy);

			setTimeout(() => {
				popup = null;
			}, 1000);
		};
	}

	#initTitleEditor(options: ?TitleEditorOptions): TitleEditor
	{
		const titleEditorOptions = Type.isPlainObject(options) ? options : {};
		const titleEditor = new TitleEditor({ ...titleEditorOptions });

		titleEditor.subscribe(TitleEditorEvents.beforeStartEditing, (editorEvent: BaseEvent) => {
			const toolbarEvent: BaseEvent = new Event.BaseEvent();

			this.emit(TitleEditorEvents.beforeStartEditing, toolbarEvent);
			if (toolbarEvent.isDefaultPrevented())
			{
				editorEvent.preventDefault();
			}
		});

		titleEditor.subscribe(TitleEditorEvents.startEditing, () => {
			this.emit(TitleEditorEvents.startEditing);
		});

		titleEditor.subscribe(TitleEditorEvents.finishEditing, (event: BaseEvent) => {
			const updatedTitle = event.getData().updatedTitle;

			this.emit(TitleEditorEvents.finishEditing, {
				updatedTitle,
			});
		});

		return titleEditor;
	}
}
