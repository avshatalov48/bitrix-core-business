import {Dom, Text, Loc, Tag, Reflection, Uri} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Document} from './item-document';

const InlineController = Reflection.namespace('BX.UI.Viewer.InlineController');

/**
 * @memberof BX.UI.Viewer
 * @extends BX.UI.Viewer.InlineController
 */
export class SingleDocumentController extends InlineController
{
	listingControl: ListingControl;
	scaleControl: ScaleControl;

	bindEvents(): void
	{
		if (!this.eventsAlreadyBinded && this.getDocumentItem())
		{
			EventEmitter.subscribe(this.getDocumentItem(), 'BX.UI.Viewer.Item.Document:updatePageNumber', () => {
				this.getListingControl().update(this.getDocumentItem().getPageNumber());
			});
		}

		super.bindEvents();
	}

	getDocumentItem(): ?Document
	{
		return this.items[0];
	}

	updateControls(): void
	{
		super.updateControls();

		this.updateListingControl();
	}

	getViewerContainer(): HTMLElement
	{
		if (!this.layout.container)
		{
			this.layout.inner = Tag.render`<div class="ui-viewer__single-document--container ">${this.getItemContainer()}</div>`;
			if (this.stretch)
			{
				Dom.addClass(this.layout.inner, '--stretch');
			}
			this.layout.container = Tag.render`<div class="">${this.layout.inner}${this.getControlsContainer()}</div>`;
		}

		return this.layout.container;
	}

	getControlsContainer(): HTMLElement
	{
		if (!this.layout.controlsContainer)
		{
			return Tag.render`<div class="ui-viewer__single-document--controls">
				${this.getListingControl().render()}
				${this.getScaleControl().render()}
			</div>`;
		}

		return this.layout.controlsContainer;
	}

	getListingControl(): ListingControl
	{
		if (!this.listingControl)
		{
			this.listingControl = new ListingControl();
			this.listingControl.subscribe('pageUpdated', () => {
				this.getDocumentItem()?.scrollToPage(this.listingControl.getCurrent());
			});
			this.updateListingControl();
		}

		return this.listingControl;
	}

	getScaleControl(): ScaleControl
	{
		if (!this.scaleControl)
		{
			this.scaleControl = new ScaleControl();
			this.scaleControl.subscribe('scaleUpdated', () => {
				this.getDocumentItem()?.updateScale(this.scaleControl.getScale());
			});
		}

		return this.scaleControl;
	}

	updateListingControl(): void
	{
		const item = this.getDocumentItem();
		if (item)
		{
			item.loadDocument().then(() => {
				this.listingControl.update(1, item.getPagesNumber());
			});
		}
	}

	setScale(scale: number): this
	{
		this.getDocumentItem()?.setScale(scale);
		this.getScaleControl().update(scale);

		return this;
	}

	setPdfSource(pdfSource: string|Uri|ArrayBuffer): this
	{
		this.getDocumentItem()?.setPdfSource(pdfSource);

		return this;
	}

	print(): void
	{
		this.getDocumentItem()?.print();
	}
}

class ListingControl extends EventEmitter
{
	pages: number;
	current: number;
	container: HTMLElement = null;
	pagesContainer: HTMLElement = null;

	constructor(current:number = 1, pages:number = 1)
	{
		super();
		this.setEventNamespace('BX.UI.Viewer.SingleDocumentController.ListingControl');
		this.pages = Text.toInteger(pages);
		this.current = Text.toInteger(current);
		this.arrowClickHandler = this.handleArrowClick.bind(this);
	}

	update(current: number, pages: number = null): void
	{
		current = Text.toInteger(current);
		pages = Text.toInteger(pages);
		if (pages >= 1)
		{
			this.pages = pages;
		}
		if (current < 1)
		{
			current = 1;
		}
		if (current > this.pages)
		{
			current = this.pages;
		}
		if (current !== this.current)
		{
			this.current = current;
			this.emit('pageUpdated', {page: this.current});
		}
		this.adjust();
	}

	adjust(): void
	{
		this.pagesContainer.innerHTML = this.renderPages();
	}

	getCurrent(): number
	{
		return this.current;
	}

	render(): HTMLElement
	{
		if (!this.container)
		{
			this.pagesContainer = Tag.render`<div class="ui-viewer__single-document--listing-info">
				${this.renderPages()}
			</div>`;

			this.container = Tag.render`<div class="ui-viewer__single-document--listing">
				<div class="ui-viewer__single-document--listing--btn --prev" onclick="${this.arrowClickHandler}"></div>
				${this.pagesContainer}
				<div class="ui-viewer__single-document--listing--btn --next" onclick="${this.arrowClickHandler}"></div>
			</div>`
		}

		return this.container;
	}

	renderPages(): string
	{
		return Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_LISTING_PAGES')
			.replace('#CURRENT#', this.current)
			.replace('#ALL#', this.pages)
		;
	}

	handleArrowClick(event: MouseEvent): void
	{
		if (event.target.classList.contains('--prev'))
		{
			this.update(this.current - 1);
		}
		if (event.target.classList.contains('--next'))
		{
			this.update(this.current + 1);
		}
	}
}

// const SCALE_MIN = 0.92;
const SCALE_MIN = 0.5;
const SCALE_MAX = 3;

class ScaleControl extends EventEmitter
{
	scale: number = SCALE_MIN;
	container: HTMLElement = null;
	zoomInContainer: HTMLElement = null;
	zoomOutContainer: HTMLElement = null;
	zoomValueNode: HTMLElement = null;

	constructor()
	{
		super();
		this.scale = SCALE_MIN;
		this.setEventNamespace('BX.UI.Viewer.SingleDocumentController.ScaleControl');
		this.scaleClickHandler = this.handleScaleClick.bind(this);
	}

	getScale(): number
	{
		return this.scale;
	}

	setDefaultScale(): void
	{
		this.update(SCALE_MIN);
	}

	adjust()
	{
		if (this.scale <= SCALE_MIN)
		{
			Dom.hide(this.getZoomOutContainer());
		}
		else
		{
			Dom.show(this.getZoomOutContainer());
		}
		if (this.scale >= SCALE_MAX)
		{
			Dom.hide(this.getZoomInContainer());
		}
		else
		{
			Dom.show(this.getZoomInContainer());
		}

		this.getZoomValueNode().innerText = Math.round(this.scale * 100);
	}

	update(scale: number): void
	{
		scale = Text.toNumber(scale);
		if (scale < SCALE_MIN)
		{
			scale = SCALE_MIN;
		}
		if (scale > SCALE_MAX)
		{
			scale = SCALE_MAX;
		}
		if (scale !== this.scale)
		{
			this.scale = scale;
			this.emit('scaleUpdated');
			this.adjust();
		}
	}

	render(): HTMLElement
	{
		if (!this.container)
		{
			this.container = Tag.render`<div class="ui-viewer__single-document--zoom">
				${this.getZoomOutContainer()}
				${this.getZoomValueNode()}
				${this.getZoomInContainer()}
			</div>`;

			this.adjust();
		}

		return this.container;
	}

	getZoomInContainer(): HTMLElement
	{
		if (!this.zoomInContainer)
		{
			this.zoomInContainer = Tag.render`<div
				class="ui-viewer__single-document--zoom-control --zoom-in"
				onclick="${this.scaleClickHandler}"
			>
<!--				${Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_SCALE_ZOOM_IN')}-->
			</div>`;
		}

		return this.zoomInContainer;
	}

	getZoomOutContainer(): HTMLElement
	{
		if (!this.zoomOutContainer)
		{
			this.zoomOutContainer = Tag.render`<div 
				class="ui-viewer__single-document--zoom-control --zoom-out"
				onclick="${this.scaleClickHandler}"
			>
<!--				${Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_SCALE_ZOOM_OUT')}-->
			</div>`;
		}

		return this.zoomOutContainer;
	}

	getZoomValueNode(): HTMLElement
	{
		if (!this.zoomValueNode)
		{
			this.zoomValueNode = Tag.render`<span class="ui-viewer__single-document--zoom-value">100</span>`;
		}

		return this.zoomValueNode;
	}

	handleScaleClick(event: MouseEvent): void
	{
		let scale = this.scale;
		if (event.target.classList.contains('--zoom-in'))
		{
			scale = this.scale * 1.1;
		}
		if (event.target.classList.contains('--zoom-out'))
		{
			scale = this.scale * 0.9;
		}
		this.update(scale);
	}
}
