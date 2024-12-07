import { Dom, Event, Tag, Loc } from 'main.core';
import { Popup as MainPopup } from 'main.popup';

import 'ui.hint';

type PopupOptions = {
	width: ?any,
	title: ?string,
	texts: string[],
	primaryButtonText: string,
	primaryButtonClass: ?string,
	secondaryButtonText: string,
	hideHelp: ?boolean,
	hideIcon: ?boolean,
	events: {
		onPrimaryClick: () => void,
		onSecondaryClick: () => void,
		onClose: () => void,
	},
}

class Popup
{
	#options: PopupOptions;
	#popup: MainPopup;
	#iconNode: ?HTMLElement;
	#titleNode: ?HTMLElement;
	#primaryButton: HTMLElement;
	#secondaryButton: HTMLElement;
	#helpLink: ?HTMLElement;

	constructor(options = {})
	{
		this.#options = options;

		const contentNode = this.#getContent();
		this.#popup = new MainPopup({
			content: contentNode,
			width: this.#options.width ?? 527,
			overlay: true,
			events: {
				onClose: this.#options.events.onClose.bind(this),
			},
		});

		Event.bind(this.#primaryButton, 'click', this.#options.events.onPrimaryClick.bind(this));
		Event.bind(this.#secondaryButton, 'click', this.#options.events.onSecondaryClick.bind(this));

		BX.UI.Hint.init(contentNode);
	}

	show(value: boolean): void
	{
		if (value === true)
		{
			if (this.#popup.isShown())
			{
				return;
			}

			this.#popup.show();
			this.#popup.resizeOverlay();
		}
		else
		{
			if (!this.#popup.isShown())
			{
				return;
			}

			this.#popup.close();
		}
	}

	load(value: boolean)
	{
		const clockClass = 'ui-btn-clock';

		if (value && !Dom.hasClass(this.#primaryButton, clockClass))
		{
			Dom.addClass(this.#primaryButton, clockClass);
		}
		else if (value === false && Dom.hasClass(this.#primaryButton, clockClass))
		{
			Dom.removeClass(this.#primaryButton, clockClass);
		}
	}

	#getContent(): HTMLElement
	{
		const primaryButtonClass = this.#options.primaryButtonClass ?? 'ui-btn-primary';

		this.#iconNode = this.#options.hideIcon ? '' : Tag.render`<div class="inventory-management__popup-icon"></div>`;

		this.#primaryButton = Tag.render`
			<button class="ui-btn ${primaryButtonClass}">
				${this.#options.primaryButtonText}
			</button>
		`;

		this.#secondaryButton = Tag.render`
			<button	class="ui-btn ui-btn-light-border inventory-management__popup-cancel">
				${this.#options.secondaryButtonText}
			</button>
		`;

		this.#titleNode = this.#options.title
			? Tag.render`
				<div class="inventory-management__popup-title">
					${this.#options.title}
				</div>
			`
			: null
		;

		this.#helpLink = this.#options.hideHelp
			? ''
			: Tag.render`
				<a href="#" class="inventory-management__popup-link">
					${Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DETAILS')}
				</a>
			`
		;
		if (this.#helpLink && this.#options.helpCode)
		{
			Event.bind(this.#helpLink, 'click', (event) => {
				if (top.BX && top.BX.Helper)
				{
					event.preventDefault();
					top.BX.Helper.show(`redirect=detail&code=${this.#options.helpCode}`);
				}
			});
		}

		const isSimplePopup = this.#options.texts.length === 1 && !this.#options.title;

		return Tag.render`
			<div class="inventory-management__popup">
				${this.#iconNode}
				${this.#titleNode}
				${this.#options.texts.map((text) => `
					<div class="inventory-management__popup-text${isSimplePopup ? ' --no-margin' : ''}">
						${text.text}
						${text.hint ? `<span data-hint="${text.hint}"></span>` : ''}
					</div>
				`).join('')}
				${this.#helpLink}
				<div class="ui-btn-container ui-btn-container-center">
					${this.#primaryButton}
					${this.#secondaryButton}
				</div>
			</div>
		`;
	}
}

export {
	Popup,
};
