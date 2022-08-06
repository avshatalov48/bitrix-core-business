import { Dialog } from 'ui.entity-selector';
import { Tag, Event, Loc } from 'main.core';

import '../css/landing.selector.css';

type SelectorOptions = {
	node: HTMLElement,
	input: HTMLElement,
	urlLandingAdd: ?string,
	urlFolderAdd: ?string,
	urlFormAdd: ?string,
	siteType: string,
	siteId: number,
	folderId: number,
	landingId: number,
	items: Array<Item>,
	onSelect: () => {}
};

type Item = {
	id: number,
	entityId: string,
	tabs: string,
	title: string,
	nodeOptions: ?{
		dynamic: boolean
	}
};

export class Selector {
	#dialog: Dialog = null;
	#node: HTMLElement
	#input: HTMLElement
	#urlLandingAdd: ?string;
	#urlFolderAdd: ?string;
	#urlFormAdd: ?string;
	#siteType: string;
	#siteId: number;
	folderId: number;
	landingId: number;
	#items: Array<Item>;
	#onSelect: () => {};
	#selectorContainer: HTMLElement
	#overlayElement: HTMLElement
	#overlayShown: boolean

	constructor(options: SelectorOptions)
	{
		this.#node = options.node;
		this.#input = options.input;
		this.#urlLandingAdd = options.urlLandingAdd || null;
		this.#urlFolderAdd = options.urlFolderAdd || null;
		this.#urlFormAdd = options.urlFormAdd || null;
		this.#siteType = options.siteType;
		this.#siteId = options.siteId;
		this.folderId = options.folderId;
		this.landingId = options.landingId;
		this.#items = options.items || [];
		this.#onSelect = options.onSelect;
		this.#selectorContainer = BX('landing-selector');

		if (this.#node)
		{
			Event.bind(this.#input, 'click', this.#handleSearchClick.bind(this));
			Event.bind(this.#input, 'input', this.#onSearch.bind(this));
		}
	}

	#getDialog()
	{
		if (!this.#dialog)
		{
			this.#dialog = new Dialog({
				targetNode: this.#node,
				width: 565,
				height: 300,
				enableSearch: false,
				dropdownMode: true,
				showAvatars: true,
				compactView: false,
				dynamicLoad: true,
				multiple: false,
				context: 'landing',
				entities: [
					{
						id: 'landing',
						options: {
							siteType: this.#siteType,
							siteId: this.#siteId,
							landingId: this.landingId
						}
					}
				],
				items: this.#items,
				events: {
					'onHide': this.#hideSelector.bind(this),
					'Item:onSelect': this.#onSelect
				},
				footer: [
					this.#urlLandingAdd ? Tag.render`<a href="${this.#urlLandingAdd}" class="ui-selector-footer-link ui-selector-footer-link-add">${Loc.getMessage('LANDING_SELECTOR_ADD_PAGE')}</a>` : Tag.render``,
					this.#urlLandingAdd ? Tag.render`<span class="ui-selector-footer-conjunction">${Loc.getMessage('LANDING_SELECTOR_ADD_OR')}</span>` : Tag.render``,
					this.#urlFolderAdd ? Tag.render`<a href="${this.#urlFolderAdd}" class="ui-selector-footer-link">${Loc.getMessage('LANDING_SELECTOR_ADD_FOLDER')}</a>` : Tag.render``,
					this.#urlFormAdd ? Tag.render`<a href="${this.#urlFormAdd}" class="ui-selector-footer-link ui-selector-footer-link-add">${Loc.getMessage('LANDING_SELECTOR_ADD_FORM')}</a>` : Tag.render``,
				]
			});
		}

		return this.#dialog;
	}

	#handleSearchClick()
	{
		this.#showSelector();
	}

	#showSelector()
	{
		this.#getDialog().show();
		this.#showOverlay();
		BX.addClass(this.#node, 'landing-selector-active');
	}

	#hideSelector()
	{
		if (this.#overlayShown === true)
		{
			BX.addClass(this.#overlayElement, 'landing-selector-overlay-hiding');

			setTimeout(() => {
				BX.removeClass(this.#overlayElement, 'landing-selector-overlay-hiding');
				BX.remove(this.#overlayElement);
			}, 200);

			this.#overlayShown = false;
		}

		BX.removeClass(this.#node, 'landing-selector-active');
	}

	#showOverlay()
	{
		if (!this.#overlayElement)
		{
			this.#overlayElement = BX.create('div', {
				props: {className: 'landing-selector-overlay'}
			});
		}

		if (this.#overlayShown !== true)
		{
			document.querySelector('.landing-ui-panel.landing-ui-panel-top').appendChild(this.#overlayElement);
			this.#overlayShown = true
		}
	}

	#onSearch(event)
	{
		if (this.#dialog)
		{
			this.#dialog.show();
			this.#dialog.search(event.srcElement.value);
		}
	}

	onAddPage()
	{
		alert(this.landingId);
	}

	onAddFolder()
	{
		alert(this.folderId);
	}
}
