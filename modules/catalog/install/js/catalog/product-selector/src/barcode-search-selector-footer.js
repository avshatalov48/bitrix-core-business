import {Loc, Tag, Dom, Type} from 'main.core';
import {BaseEvent} from "main.core.events";
import ProductSearchSelectorFooter from "./product-search-selector-footer";

export class BarcodeSearchSelectorFooter extends ProductSearchSelectorFooter
{
	constructor(id, options = {})
	{
		super(id, options);
		this.isEmptyBarcode = options.isEmptyBarcode;
		this.getDialog().subscribe('SearchTab:onLoad', this.handleOnSearchLoad.bind(this));
	}

	getContent(): HTMLElement
	{
		this.barcodeContent = super.getContent();
		this.scannerContent = this.getScannerContent();
		Dom.style(this.barcodeContent, 'display', 'none');

		return Tag.render`
			<div class="catalog-footers-container">
				${this.barcodeContent}
				${this.scannerContent}
			</div>
		`;
	}

	isViewEditButton(): boolean
	{
		return !this.isEmptyBarcode && super.isViewEditButton();
	}

	getScannerContent(): HTMLElement
	{
		const phrase = Tag.render`
			<div>${Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_BARCODE')}</div>
		`;

		const createButton = phrase.querySelector('create-button');

		Dom.replace(createButton, this.getScannerLabelContainer());

		return Tag.render`
			<div class="ui-selector-search-footer-box">
				${phrase}
				${this.getLoaderContainer()}
			</div>
		`;
	}

	getScannerLabelContainer(): HTMLElement
	{
		return this.cache.remember('scannerLabel', () => {
			return Tag.render`
				<span onclick="${this.handleScannerClick.bind(this)}">
					<span class="ui-selector-footer-link ui-selector-footer-link-add footer-link--warehouse-barcode-icon">
						${Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_BARCODE_START_SCAN_LABEL')}
					</span>
					${this.getScannerQueryContainer()}
				</span>
			`;
		});
	}

	getScannerQueryContainer(): HTMLElement
	{
		return this.cache.remember('scanner_name-container', () => {
			return Tag.render`
				<span class="ui-selector-search-footer-query"></span>
			`;
		});
	}

	handleScannerClick(): void
	{
		const inputEntity = this.options?.inputEntity;
		if (inputEntity)
		{
			inputEntity.startMobileScanner();
		}
	}

	handleOnSearch(event: BaseEvent): void
	{
		const { query } = event.getData();

		if (!Type.isStringFilled(query))
		{
			this.show();
			Dom.style(this.scannerContent, 'display', '');
			Dom.style(this.barcodeContent, 'display', 'none');
		}
		else if (this.options.currentValue === query)
		{
			this.hide();
		}
		else
		{
			this.show();
			Dom.style(this.barcodeContent, 'display', '');
			Dom.style(this.scannerContent, 'display', 'none');
		}

		this.getQueryContainer().textContent = " " + query;
		this.getScannerQueryContainer().textContent = " " + query;
	}

	handleOnSearchLoad(event: BaseEvent): void
	{
		const {searchTab} = event.getData();
		this.getDialog().getItems().forEach(item => {
			if (item.getCustomData().get('BARCODE') === searchTab.getLastSearchQuery().getQuery())
			{
				this.hide();
			}
		});
	}
}