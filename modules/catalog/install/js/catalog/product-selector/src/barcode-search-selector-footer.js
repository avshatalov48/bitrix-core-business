import {Loc, Tag, Dom} from 'main.core';
import {BaseEvent} from "main.core.events";
import ProductSearchSelectorFooter from "./product-search-selector-footer";

export class BarcodeSearchSelectorFooter extends ProductSearchSelectorFooter
{
	constructor(id, options = {})
	{
		super(id, options);
		this.isEmptyBarcode = options.isEmptyBarcode;
	}

	getContent(): HTMLElement
	{
		this.barcodeContent =  super.getContent();
		this.scannerContent = this.getScannerContent();
		Dom.style(this.barcodeContent, 'display', 'none');
		Dom.style(this.scannerContent, 'display', 'none');

		return Tag.render`
			<div class="catalog-footers-container">
				${this.barcodeContent}
				${this.scannerContent}
			</div>
		`;
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

		if (this.isEmptyBarcode)
		{
			if (query === '')
			{
				this.show();
				Dom.style(this.scannerContent, 'display', '');
			}
			else
			{
				this.hide();
			}
		}
		else
		{
			if (query === '')
			{
				this.show();
				Dom.style(this.barcodeContent, 'display', 'none');
				Dom.style(this.scannerContent, 'display', '');
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
		}

		if (this.options.allowCreateItem !== false)
		{
			this.getQueryContainer().textContent = query;
			this.getScannerQueryContainer().textContent = query;
		}
	}
}