import { Text, Tag, Uri, Loc, Dom, Reflection, Event, Runtime, ajax as Ajax, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

const Item = Reflection.namespace('BX.UI.Viewer.Item');
const Util = Reflection.namespace('BX.util');
const BXPromise = Reflection.namespace('BX.Promise');

const DEFAULT_SCALE = 1.4;
const SCALE_MIN = 0.5;
const SCALE_MAX = 3;

const PAGES_TO_PRELOAD = 3;

// noinspection JSClosureCompilerSyntax
/**
 * @memberof BX.UI.Viewer
 * @extends BX.UI.Viewer.Item
 */
export class Document extends Item
{
	static #loadingLibraryPromise = null;
	#pageNumber: number = 1;
	#loadingDocumentPromise: Promise = null;

	pdfDocument;
	pdfPages: Object<number,Object> = {};
	scale: number = DEFAULT_SCALE;
	pdfRenderedPages: Object<number,Object> = {};
	lastRenderedPdfPage: number = 0;
	contentNode: Element;
	previewHtml: Element;
	extraActions: HTMLElement = null;
	disableAnnotationLayer: boolean = true;

	constructor (options)
	{
		super(options);

		options = options || {};

		this.scale = options.scale || DEFAULT_SCALE;
	}

	setPropertiesByNode(node:HTMLElement): void
	{
		super.setPropertiesByNode(node);

		this.disableAnnotationLayer = node.dataset.hasOwnProperty('disableAnnotationLayer') ? true : this.disableAnnotationLayer;
	}

	applyReloadOptions(options)
	{
		this.controller.unsetCachedData(this.src);
	}

	listContainerModifiers(): Array<string>
	{
		const result = [
			'ui-viewer-document',
		];
		if (this.controller.stretch)
		{
			result.push('--stretch');
		}

		return result;
	}

	setSrc(src: string|Uri): this
	{
		this.src = src;
		this._pdfSrc = null;

		return this.#resetState();
	}

	setPdfSource(pdfSource: string|Uri|ArrayBuffer): this
	{
		this._pdfSrc = pdfSource;

		return this.#resetState();
	}

	#resetState(): this
	{
		this.pdfRenderedPages = {};
		this.lastRenderedPdfPage = null;
		this.pdfDocument = null;
		this.pdfPages = {};
		this.setPageNumber(1);
		if (this.printer)
		{
			this.hidePrintProgress();
			this.printer.destroy();
		}
	}

	loadLibrary(): Promise
	{
		if (Document.#loadingLibraryPromise !== null)
		{
			return Document.#loadingLibraryPromise;
		}
		Document.#loadingLibraryPromise = new Promise((resolve, reject) => {
			Runtime.loadExtension('ui.pdfjs').then(() => {
				if (!pdfjsLib.GlobalWorkerOptions.workerSrc)
				{
					pdfjsLib.GlobalWorkerOptions.workerSrc = '/bitrix/js/ui/pdfjs/pdf.worker.js';
				}

				Document.#loadingLibraryPromise = null;

				resolve();
			})
			.catch(reject);
		});

		return Document.#loadingLibraryPromise;
	}

	loadData(): BXPromise
	{
		const promise = new BXPromise();

		if (this._pdfSrc)
		{
			this.loadLibrary().then(() => {
				promise.fulfill(this);
			});

			return promise;
		}

		console.log('loadData pdf');
		const ajaxPromise = Ajax.promise({
			url: Uri.addParam(this.src, {ts: 'bxviewer'}),
			method: 'GET',
			dataType: 'json',
			headers: [
				{
					name: 'BX-Viewer-src',
					value: this.src
				},
				{
					name: 'BX-Viewer',
					value: 'document'
				}
			]
		});

		ajaxPromise.then((response) => {
			if (!response || !response.data)
			{
				this.isTransforming = false;
				promise.reject({
					item: this,
					message: Loc.getMessage("JS_UI_VIEWER_ITEM_TRANSFORMATION_ERROR_1").replace('#DOWNLOAD_LINK#', this.getSrc()),
					type: 'error'
				});

				return promise;
			}

			if (response.data.hasOwnProperty('pullTag'))
			{
				if (!this.isTransforming)
				{
					this.transformationPromise = promise;
					this.registerTransformationHandler(response.data.pullTag);
				}
				this.isTransforming = true;
			}

			if (response.data.data && response.data.data.src)
			{
				this.isTransforming = false;
				this._pdfSrc = response.data.data.src;
				this.loadLibrary().then(() => {
					promise.fulfill(this);
				});
			}
		});

		return promise;
	}

	render(): HTMLDivElement
	{
		this.controller.showLoading();

		this.contentNode = Dom.create('div', {
			props: {
				className: 'ui-viewer-item-document-content',
				tabIndex: 2208
			},
		});

		Event.bind(this.contentNode, 'scroll', Runtime.throttle(this.handleScrollDocument.bind(this), 100));

		return this.contentNode;
	}

	renderExtraActions(): HTMLElement
	{
		if (this.extraActions === null)
		{
			this.extraActions = Tag.render`
				<div class="ui-viewer-extra-actions">
					<div 
						class="ui-viewer-action-btn" 
						onclick="${this.zoomOut.bind(this)}"
						title="${Text.encode(Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_SCALE_ZOOM_OUT'))}"
					>
						<div class="ui-icon-set --zoom-out ui-viewer-action-btn-icon"></div>
					</div>
					<div 
						class="ui-viewer-action-btn" 
						onclick="${this.zoomIn.bind(this)}" 
						title="${Text.encode(Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_SCALE_ZOOM_IN'))}"
					>
						<div class="ui-icon-set --zoom-in ui-viewer-action-btn-icon"></div>
					</div>
					<div 
						class="ui-viewer-action-btn" 
						onclick="${this.print.bind(this)}" 
						title="${Text.encode(Loc.getMessage('JS_UI_VIEWER_ITEM_ACTION_PRINT'))}"
					>
						<div class="ui-icon-set --print-1 ui-viewer-action-btn-icon"></div>
					</div>
				</div>
			`;
		}

		return this.extraActions;
	}

	zoomIn(): void
	{
		const newScale = Math.min(SCALE_MAX, Math.max(SCALE_MIN, this.scale * 1.1));
		void this.updateScale(newScale).then(() => {
			this.controller.adjustControlsSize(this.getContentWidth());
		});
	}

	zoomOut(): void
	{
		const newScale = Math.min(SCALE_MAX, Math.max(SCALE_MIN, this.scale * 0.9));
		void this.updateScale(newScale).then(() => {
			this.controller.adjustControlsSize(this.getContentWidth());
		});
	}

	getFirstDocumentPageHeight(): Promise<number>
	{
		if (this._height)
		{
			return Promise.resolve(this._height);
		}

		return new Promise((resolve) => {
			this.getDocumentPage(this.pdfDocument, 1).then((page) => {
				const viewport = page.getViewport(this.scale);
				this._height = viewport.height;

				resolve(this._height);
			});
		});
	}

	handleScrollDocument(event): void
	{
		this.getFirstDocumentPageHeight().then((height) => {
			const scrollBottom = this.contentNode.scrollHeight - this.contentNode.scrollTop - this.contentNode.clientHeight;
			if (scrollBottom < height * PAGES_TO_PRELOAD && this.pdfDocument.numPages > this.lastRenderedPdfPage)
			{
				for (let i = this.lastRenderedPdfPage + 1; i <= Math.min(this.pdfDocument.numPages, this.lastRenderedPdfPage + PAGES_TO_PRELOAD); i++)
				{
					this.renderDocumentPage(this.pdfDocument, i);
				}
			}

			this.setPageNumber((this.contentNode.scrollTop / height) + 1);
		});
	}

	loadDocument(): Promise<Object>
	{
		if (this.pdfDocument)
		{
			return Promise.resolve(this.pdfDocument);
		}

		if (this.#loadingDocumentPromise)
		{
			return this.#loadingDocumentPromise;
		}

		this.#loadingDocumentPromise = new Promise((resolve) => {
			this.loadData().then(() => {
				pdfjsLib.getDocument(this._pdfSrc).promise.then((pdf) => {
					this.pdfDocument = pdf;
					this.#loadingDocumentPromise = null;

					resolve(this.pdfDocument);
				});
			});
		});

		return this.#loadingDocumentPromise;
	}

	getDocumentPage(pdf, pageNumber): Promise<Object>
	{
		if (this.pdfPages[pageNumber])
		{
			return Promise.resolve(this.pdfPages[pageNumber]);
		}

		return new Promise((resolve) => {
			pdf.getPage(pageNumber).then((page) => {
				this.pdfPages[pageNumber] = page;

				resolve(this.pdfPages[pageNumber]);
			});
		});
	}

	renderDocumentPage(pdf, pageNumber): Promise<Object>
	{
		const pagePromise = this.pdfRenderedPages[pageNumber];
		if (pagePromise instanceof Promise)
		{
			return pagePromise;
		}
		else if(!!pagePromise)
		{
			return Promise.resolve(pagePromise);
		}

		this.pdfRenderedPages[pageNumber] = new Promise((resolve) => {
			this.getDocumentPage(pdf, pageNumber).then((page) => {
				const canvas = this.createCanvasPage();
				const viewport = page.getViewport(this.scale);
				canvas.height = viewport.height;
				canvas.width = viewport.width;
				const renderPromise = page.render({canvasContext: canvas.getContext('2d'), viewport: viewport});

				if (!this.disableAnnotationLayer)
				{
					renderPromise.then(function () {
						return page.getAnnotations();
					}).then(function (annotationData) {
						const annotationLayer = Dom.create('div', {
							props: { className: 'ui-viewer-pdf-annotation-layer' },
						});

						Dom.insertAfter(annotationLayer, canvas);
						Dom.adjust(annotationLayer, {
							style: {
								margin: '-' + canvas.offsetHeight + 'px auto 0 auto',
								height: canvas.height + 'px',
								width: canvas.width + 'px',
							},
						});

						pdfjsLib.AnnotationLayer.render({
							viewport: viewport.clone({ dontFlip: true }),
							linkService: pdfjsLib.SimpleLinkService,
							div: annotationLayer,
							annotations: annotationData,
							page: page,
						});
					});
				}

				renderPromise.then(function() {
					return page.getTextContent();
				}).then(function(textContent) {
					const textLayer = Dom.create('div', {
						props: { className: 'ui-viewer-pdf-text-layer' },
					});

					Dom.insertAfter(textLayer, canvas);
					Dom.adjust(textLayer, {
						style: {
							margin: '-' + canvas.offsetHeight + 'px auto 0 auto',
							height: canvas.height + 'px',
							width: canvas.width + 'px',
						},
					});

					pdfjsLib.renderTextLayer({
						textContent: textContent,
						container: textLayer,
						viewport: viewport,
						textDivs: [],
					});
				});

				this.lastRenderedPdfPage = Math.max(pageNumber, this.lastRenderedPdfPage);

				if (pageNumber === 1)
				{
					this.firstWidthDocumentPage = canvas.width;
				}

				renderPromise.then(() => {
					this.controller.hideLoading();
					this.pdfRenderedPages[pageNumber] = page;

					resolve(page, canvas);
				});
			});
		});

		return this.pdfRenderedPages[pageNumber];
	}

	createCanvasPage(): HTMLCanvasElement
	{
		const canvas = document.createElement('canvas');
		canvas.className = 'ui-viewer-document-page-canvas';
		this.contentNode.appendChild(canvas);

		return canvas;
	}

	getContentWidth(): Promise<Number>
	{
		return new Promise((resolve) => {
			this.loadDocument().then(() => {
				this.renderDocumentPage(this.pdfDocument, 1).then((page) => {
					const contentWidth = page.getViewport(this.scale).width;
					const scrollWidth = this.contentNode.offsetWidth - this.contentNode.clientWidth;

					resolve(contentWidth + scrollWidth);
				});
			});
		});
	}

	afterRender(): void
	{
		this.loadDocument().then((pdf) => {
			for (let i = 1; i <= Math.min(pdf.numPages, PAGES_TO_PRELOAD); i++)
			{
				if (i === 1)
				{
					this._handleControls = this.controller.handleVisibleControls.bind(this.controller);
					this.controller.enableReadingMode(true);

					Runtime.throttle(Event.bind(window, 'mousemove', this._handleControls), 20);
				}

				this.renderDocumentPage(pdf, i);
			}
		});
	}

	beforeHide(): void
	{
		this.pdfRenderedPages = {};
		Event.unbind(window, 'mousemove', this._handleControls);
		if (this.printer)
		{
			this.hidePrintProgress();
			this.printer.destroy();
		}
	}

	updatePrintProgressMessage(index: number, total: number): void
	{
		const progress = Math.round((index / total) * 100);
		this.controller.setTextOnLoading(Loc.getMessage('JS_UI_VIEWER_ITEM_PREPARING_TO_PRINT').replace('#PROGRESS#', progress));
	}

	showPrintProgress(index: number, total: number): void
	{
		this.contentNode.style.opacity = 0.7;
		this.contentNode.style.filter = 'blur(2px)';

		this.controller.showLoading({
			zIndex: 1,
		});

		this.updatePrintProgressMessage(index, total);
	}

	hidePrintProgress(): void
	{
		this.contentNode.style.opacity = null;
		this.contentNode.style.filter = null;

		this.controller.hideLoading();
	}

	print(): void
	{
		if (!this.pdfDocument)
		{
			console.warn('Where is pdf document to print?');

			return;
		}

		this.showPrintProgress(0, this.pdfDocument.numPages);

		this.printer = new PrintService({
			pdf: this.pdfDocument
		});

		this.printer.init().then(() => {
			this.printer.prepare({
				onProgress: this.updatePrintProgressMessage.bind(this)
			}).then(() => {
				this.hidePrintProgress();
				this.printer.performPrint();
			});
		});
	}

	handleKeyPress(event): void
	{
		if (!this.isLoaded)
		{
			return false;
		}

		if (['PageDown', 'PageUp', 'ArrowDown', 'ArrowUp'].includes(event.code))
		{
			BX.focus(this.contentNode);

			return false;
		}

		if (event.code === 'Equal')
		{
			event.preventDefault();
			event.stopPropagation();

			this.zoomIn();

			return true;
		}

		if (event.code === 'Minus')
		{
			event.preventDefault();
			event.stopPropagation();

			this.zoomOut();

			return true;
		}

		return false;
	}

	getScale(): number
	{
		return this.scale;
	}

	setScale(scale: number): this
	{
		this.scale = scale;

		return this;
	}

	updateScale(scale: number): Promise<void>
	{
		scale = Number(scale);
		if (this.scale === scale)
		{
			return Promise.resolve();
		}

		const ratio = scale / this.scale;

		const updatePageScale = ((
			page,
			canvases: Array<number, HTMLCanvasElement>,
			textLayers: Array<number, HTMLDivElement>,
		): Promise => {
			const canvas = canvases[page.pageIndex];
			if (!canvas)
			{
				return Promise.resolve();
			}

			return new Promise((resolve) => {
				const viewport = page.getViewport(this.scale);
				canvas.width = viewport.width;
				canvas.height = viewport.height;
				page.render({
					canvasContext: canvas.getContext('2d'),
					viewport,
				}).then(() => {
					const textLayer = textLayers[page.pageIndex];
					if (textLayer)
					{
						Dom.clean(textLayer);
						Dom.adjust(textLayer, {
							style: {
								margin: '-' + canvas.offsetHeight + 'px auto 0 auto',
								height: viewport.height + 'px',
								width: viewport.width + 'px',
							},
						});

						page.getTextContent().then((textContent) => {
							pdfjsLib.renderTextLayer({
								textContent,
								container: textLayer,
								viewport,
								textDivs: [],
							});

							resolve();
						});
					}
					else
					{
						resolve();
					}
				});
			});
		});

		const promises = [];
		this.scale = scale;
		const canvases = Array.from(this.contentNode.querySelectorAll('canvas[class="ui-viewer-document-page-canvas"]'));
		const textLayers = Array.from(this.contentNode.querySelectorAll('div[class="ui-viewer-pdf-text-layer"]'));
		Object.values(this.pdfRenderedPages).forEach((renderedPage) => {
			if (renderedPage instanceof Promise)
			{
				promises.push(new Promise((resolve) => {
					renderedPage.then((page) => {
						updatePageScale(page, canvases, textLayers).then(resolve);
					});
				}));
			}
			else
			{
				promises.push(updatePageScale(renderedPage, canvases, textLayers));
			}
		});

		const scrollTop = this.contentNode.scrollTop * ratio;
		this.contentNode.scrollTo(this.contentNode.scrollLeft, scrollTop);

		return Promise.all(promises);
	}

	getPagesNumber(): ?number
	{
		if (!this.pdfDocument)
		{
			return null;
		}

		return Text.toInteger(this.pdfDocument._pdfInfo.numPages);
	}

	scrollToPage(pageNumber: number): Promise<void>
	{
		const isChanged = this.setPageNumber(pageNumber) !== null;
		if (!isChanged)
		{
			return Promise.resolve();
		}

		return new Promise((resolve) => {
			const renderPromises = [];
			for (let i = 1; i < pageNumber; i++)
			{
				renderPromises.push(this.renderDocumentPage(this.pdfDocument, i));
			}
			Promise.all(renderPromises).then((pages) => {
				let height = 0;

				pages.forEach((page) => {
					const viewport = page.getViewport(this.scale);
					height += viewport.height + 7;
				});

				this.contentNode.scrollTo(this.contentNode.scrollLeft, height);

				resolve();
			});
		});
	}

	getPageNumber(): number
	{
		return this.#pageNumber;
	}

	setPageNumber(pageNumber: number): this|null
	{
		pageNumber = Text.toInteger(pageNumber);
		if (pageNumber < 0)
		{
			pageNumber = 1;
		}

		let numPages = this.getPagesNumber();
		if (!numPages)
		{
			numPages = 1;
		}

		if (pageNumber > numPages)
		{
			pageNumber = numPages;
		}

		if (this.#pageNumber !== pageNumber)
		{
			this.#pageNumber = pageNumber;
			EventEmitter.emit(this, 'BX.UI.Viewer.Item.Document:updatePageNumber');

			return this;
		}

		return null;
	}
}

const PRINT_SCALE = 1;

export class PrintService
{
	constructor(options)
	{
		options = options || {};
		this.pdf = options.pdf;
		this.iframe = null;
		this.documentOverview = {};
	}

	init()
	{
		if (this.documentOverview)
		{
			return Promise.resolve(this.documentOverview);
		}

		return new Promise((resolve) => {
			this.pdf.getPage(1).then((page) => {
				const viewport = page.getViewport(PRINT_SCALE);

				this.documentOverview = {
					width: viewport.width, height: viewport.height, rotation: viewport.rotation
				};

				resolve(this.documentOverview);
			});
		});
	}

	/**
	 * @param {?Object} options
	 * @param {Function} [options.onProgress]
	 * @return {BXPromise}
	 */
	prepare(options)
	{
		options = options || {};
		const pageCount = this.pdf.numPages;
		let currentPage = -1;
		const promise = new BXPromise();
		let onProgress = null;
		if (Type.isFunction(options.onProgress))
		{
			onProgress = options.onProgress;
		}

		this.frame = this.createIframe();

		const process = () => {
			if (++currentPage >= pageCount)
			{
				console.log('finish', this.frame.contentWindow.document);

				setTimeout(() => {
					promise.fulfill();
				}, 1000);

				return;
			}

			this.renderPage(currentPage + 1).then(function () {
				if (onProgress)
				{
					onProgress(currentPage + 1, pageCount);
				}
				process();
			});
		};

		process();

		return promise;
	}

	renderPage(pageNumber)
	{
		return this.pdf.getPage(pageNumber).then(function (page) {
			const scratchCanvas = document.createElement('canvas');
			const viewport = page.getViewport(1);
			// The size of the canvas in pixels for printing.
			const PRINT_RESOLUTION = 150;
			const PRINT_UNITS = PRINT_RESOLUTION / 72.0;
			scratchCanvas.width = Math.floor(viewport.width * PRINT_UNITS);
			scratchCanvas.height = Math.floor(viewport.height * PRINT_UNITS);

			// The physical size of the img as specified by the PDF document.
			const CSS_UNITS = 96.0 / 72.0;
			const width = Math.floor(viewport.width * CSS_UNITS) + 'px';
			const height = Math.floor(viewport.height * CSS_UNITS) + 'px';

			const ctx = scratchCanvas.getContext('2d');
			ctx.save();
			ctx.fillStyle = 'rgb(255, 255, 255)';
			ctx.fillRect(0, 0, scratchCanvas.width, scratchCanvas.height);
			ctx.restore();

			const renderContext = {
				canvasContext: ctx,
				transform: [PRINT_UNITS, 0, 0, PRINT_UNITS, 0, 0],
				viewport: page.getViewport(1, viewport.rotation),
				intent: 'print'
			};

			return page.render(renderContext).promise.then(function () {
				return {
					scratchCanvas: scratchCanvas, width: width, height: height
				}
			});
		}).then((printItem) => {

			const img = document.createElement('img');
			img.style.width = printItem.width;
			img.style.height = printItem.height;

			const scratchCanvas = printItem.scratchCanvas;
			if (('toBlob' in scratchCanvas) && !this.disableCreateObjectURL)
			{
				scratchCanvas.toBlob(function (blob) {
					img.src = URL.createObjectURL(blob);
				});
			}
			else
			{
				img.src = scratchCanvas.toDataURL();
			}

			const wrapper = document.createElement('div');
			wrapper.appendChild(img);

			this.frame.contentWindow.document.body.appendChild(wrapper);
		});
	}

	destroy()
	{
		if (this.frame)
		{
			Dom.remove(this.frame);
		}
	}

	createIframe()
	{
		const frame = document.createElement("iframe");
		frame.src = "about:blank";
		frame.name = "document-print-frame";
		frame.style.display = "none";
		document.body.appendChild(frame);

		const frameWindow = frame.contentWindow;
		const frameDoc = frameWindow.document;
		frameDoc.open();
		frameDoc.write('<html><head>');

		const pageSize = this.getDocumentOverview();
		let headTags = "<style>";
		headTags += "html, body { background: #fff !important; height: 100%; }";
		headTags += '@supports ((size:A4) and (size:1pt 1pt)) {' + '@page { size: ' + pageSize.width + 'pt ' + pageSize.height + 'pt;}' + '}';
		headTags += '#ad{ display:none;}';
		headTags += '#leftbar{ display:none;}';
		headTags += "</style>";

		frameDoc.write(headTags);

		frameDoc.write('</head><body>');
		frameDoc.write('</body></html>');
		frameDoc.close();

		return frame;
	}

	performPrint()
	{
		this.frame.contentWindow.focus();
		this.frame.contentWindow.print();
	}

	getDocumentOverview()
	{
		return this.documentOverview;
	}
}
