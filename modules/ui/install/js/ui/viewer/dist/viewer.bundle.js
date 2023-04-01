this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	const Item = main_core.Reflection.namespace('BX.UI.Viewer.Item');
	const Util = main_core.Reflection.namespace('BX.util');
	const BXPromise = main_core.Reflection.namespace('BX.Promise');
	const DEFAULT_SCALE = 1.4;
	const PAGES_TO_PRELOAD = 3;

	// noinspection JSClosureCompilerSyntax
	/**
	 * @memberof BX.UI.Viewer
	 * @extends BX.UI.Viewer.Item
	 */
	var _loadingLibraryPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadingLibraryPromise");
	var _pageNumber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pageNumber");
	var _loadingDocumentPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadingDocumentPromise");
	var _resetState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resetState");
	class Document extends Item {
	  constructor(options) {
	    super(options);
	    Object.defineProperty(this, _resetState, {
	      value: _resetState2
	    });
	    Object.defineProperty(this, _pageNumber, {
	      writable: true,
	      value: 1
	    });
	    Object.defineProperty(this, _loadingDocumentPromise, {
	      writable: true,
	      value: null
	    });
	    this.pdfPages = {};
	    this.scale = DEFAULT_SCALE;
	    this.pdfRenderedPages = {};
	    this.lastRenderedPdfPage = 0;
	    this.disableAnnotationLayer = false;
	    options = options || {};
	    this.scale = options.scale || DEFAULT_SCALE;
	  }
	  setPropertiesByNode(node) {
	    super.setPropertiesByNode(node);
	    this.disableAnnotationLayer = node.dataset.hasOwnProperty('disableAnnotationLayer');
	  }
	  applyReloadOptions(options) {
	    this.controller.unsetCachedData(this.src);
	  }
	  listContainerModifiers() {
	    const result = ['ui-viewer-document'];
	    if (this.controller.stretch) {
	      result.push('--stretch');
	    }
	    return result;
	  }
	  setSrc(src) {
	    this.src = src;
	    this._pdfSrc = null;
	    return babelHelpers.classPrivateFieldLooseBase(this, _resetState)[_resetState]();
	  }
	  setPdfSource(pdfSource) {
	    this._pdfSrc = pdfSource;
	    return babelHelpers.classPrivateFieldLooseBase(this, _resetState)[_resetState]();
	  }
	  loadLibrary() {
	    if (babelHelpers.classPrivateFieldLooseBase(Document, _loadingLibraryPromise)[_loadingLibraryPromise] !== null) {
	      return babelHelpers.classPrivateFieldLooseBase(Document, _loadingLibraryPromise)[_loadingLibraryPromise];
	    }
	    babelHelpers.classPrivateFieldLooseBase(Document, _loadingLibraryPromise)[_loadingLibraryPromise] = new Promise((resolve, reject) => {
	      main_core.Runtime.loadExtension('ui.pdfjs').then(() => {
	        if (!pdfjsLib.GlobalWorkerOptions.workerSrc) {
	          pdfjsLib.GlobalWorkerOptions.workerSrc = '/bitrix/js/ui/pdfjs/pdf.worker.js';
	        }
	        babelHelpers.classPrivateFieldLooseBase(Document, _loadingLibraryPromise)[_loadingLibraryPromise] = null;
	        resolve();
	      }).catch(reject);
	    });
	    return babelHelpers.classPrivateFieldLooseBase(Document, _loadingLibraryPromise)[_loadingLibraryPromise];
	  }
	  loadData() {
	    const promise = new BXPromise();
	    if (this._pdfSrc) {
	      this.loadLibrary().then(() => {
	        promise.fulfill(this);
	      });
	      return promise;
	    }
	    console.log('loadData pdf');
	    const ajaxPromise = main_core.ajax.promise({
	      url: main_core.Uri.addParam(this.src, {
	        ts: 'bxviewer'
	      }),
	      method: 'GET',
	      dataType: 'json',
	      headers: [{
	        name: 'BX-Viewer-src',
	        value: this.src
	      }, {
	        name: 'BX-Viewer',
	        value: 'document'
	      }]
	    });
	    ajaxPromise.then(response => {
	      if (!response || !response.data) {
	        this.isTransforming = false;
	        promise.reject({
	          item: this,
	          message: main_core.Loc.getMessage("JS_UI_VIEWER_ITEM_TRANSFORMATION_ERROR_1").replace('#DOWNLOAD_LINK#', this.getSrc()),
	          type: 'error'
	        });
	        return promise;
	      }
	      if (response.data.hasOwnProperty('pullTag')) {
	        if (!this.isTransforming) {
	          this.transformationPromise = promise;
	          this.registerTransformationHandler(response.data.pullTag);
	        }
	        this.isTransforming = true;
	      }
	      if (response.data.data && response.data.data.src) {
	        this.isTransforming = false;
	        this._pdfSrc = response.data.data.src;
	        this.loadLibrary().then(() => {
	          promise.fulfill(this);
	        });
	      }
	    });
	    return promise;
	  }
	  render() {
	    this.controller.showLoading();
	    this.contentNode = main_core.Dom.create('div', {
	      props: {
	        className: 'ui-viewer-item-document-content',
	        tabIndex: 2208
	      }
	    });
	    main_core.Event.bind(this.contentNode, 'scroll', main_core.Runtime.throttle(this.handleScrollDocument.bind(this), 100));
	    return this.contentNode;
	  }
	  getNakedActions() {
	    const nakedActions = super.getNakedActions();
	    return this.insertPrintBeforeInfo(nakedActions);
	  }
	  insertPrintBeforeInfo(actions) {
	    actions = actions || [];
	    let infoIndex = null;
	    for (let i = 0; i < actions.length; i++) {
	      if (actions[i].type === 'info') {
	        infoIndex = i;
	      }
	    }
	    const printAction = {
	      type: 'print',
	      action: this.print.bind(this)
	    };
	    if (infoIndex === null) {
	      actions.push(printAction);
	    } else {
	      actions = Util.insertIntoArray(actions, infoIndex, printAction);
	    }
	    return actions;
	  }
	  getFirstDocumentPageHeight() {
	    if (this._height) {
	      return Promise.resolve(this._height);
	    }
	    return new Promise(resolve => {
	      this.getDocumentPage(this.pdfDocument, 1).then(page => {
	        const viewport = page.getViewport(this.scale);
	        this._height = viewport.height;
	        resolve(this._height);
	      });
	    });
	  }
	  handleScrollDocument(event) {
	    this.getFirstDocumentPageHeight().then(height => {
	      const scrollBottom = this.contentNode.scrollHeight - this.contentNode.scrollTop - this.contentNode.clientHeight;
	      if (scrollBottom < height * PAGES_TO_PRELOAD && this.pdfDocument.numPages > this.lastRenderedPdfPage) {
	        for (let i = this.lastRenderedPdfPage + 1; i <= Math.min(this.pdfDocument.numPages, this.lastRenderedPdfPage + PAGES_TO_PRELOAD); i++) {
	          this.renderDocumentPage(this.pdfDocument, i);
	        }
	      }
	      this.setPageNumber(this.contentNode.scrollTop / height + 1);
	    });
	  }
	  loadDocument() {
	    if (this.pdfDocument) {
	      return Promise.resolve(this.pdfDocument);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _loadingDocumentPromise)[_loadingDocumentPromise]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _loadingDocumentPromise)[_loadingDocumentPromise];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _loadingDocumentPromise)[_loadingDocumentPromise] = new Promise(resolve => {
	      this.loadData().then(() => {
	        pdfjsLib.getDocument(this._pdfSrc).promise.then(pdf => {
	          this.pdfDocument = pdf;
	          babelHelpers.classPrivateFieldLooseBase(this, _loadingDocumentPromise)[_loadingDocumentPromise] = null;
	          resolve(this.pdfDocument);
	        });
	      });
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadingDocumentPromise)[_loadingDocumentPromise];
	  }
	  getDocumentPage(pdf, pageNumber) {
	    if (this.pdfPages[pageNumber]) {
	      return Promise.resolve(this.pdfPages[pageNumber]);
	    }
	    return new Promise(resolve => {
	      pdf.getPage(pageNumber).then(page => {
	        this.pdfPages[pageNumber] = page;
	        resolve(this.pdfPages[pageNumber]);
	      });
	    });
	  }
	  renderDocumentPage(pdf, pageNumber) {
	    const pagePromise = this.pdfRenderedPages[pageNumber];
	    if (pagePromise instanceof Promise) {
	      return pagePromise;
	    } else if (!!pagePromise) {
	      return Promise.resolve(pagePromise);
	    }
	    this.pdfRenderedPages[pageNumber] = new Promise(resolve => {
	      this.getDocumentPage(pdf, pageNumber).then(page => {
	        const canvas = this.createCanvasPage();
	        const viewport = page.getViewport(this.scale);
	        canvas.height = viewport.height;
	        canvas.width = viewport.width;
	        const renderPromise = page.render({
	          canvasContext: canvas.getContext('2d'),
	          viewport: viewport
	        });
	        if (!this.disableAnnotationLayer) {
	          renderPromise.then(function () {
	            return page.getAnnotations();
	          }).then(function (annotationData) {
	            const annotationLayer = main_core.Dom.create('div', {
	              props: {
	                className: 'ui-viewer-pdf-annotation-layer'
	              }
	            });
	            main_core.Dom.insertAfter(annotationLayer, canvas);
	            main_core.Dom.adjust(annotationLayer, {
	              style: {
	                margin: '-' + canvas.offsetHeight + 'px auto 0 auto',
	                height: canvas.height + 'px',
	                width: canvas.width + 'px'
	              }
	            });
	            pdfjsLib.AnnotationLayer.render({
	              viewport: viewport.clone({
	                dontFlip: true
	              }),
	              linkService: pdfjsLib.SimpleLinkService,
	              div: annotationLayer,
	              annotations: annotationData,
	              page: page
	            });
	          });
	        }
	        renderPromise.then(function () {
	          return page.getTextContent();
	        }).then(function (textContent) {
	          const textLayer = main_core.Dom.create('div', {
	            props: {
	              className: 'ui-viewer-pdf-text-layer'
	            }
	          });
	          main_core.Dom.insertAfter(textLayer, canvas);
	          main_core.Dom.adjust(textLayer, {
	            style: {
	              margin: '-' + canvas.offsetHeight + 'px auto 0 auto',
	              height: canvas.height + 'px',
	              width: canvas.width + 'px'
	            }
	          });
	          pdfjsLib.renderTextLayer({
	            textContent: textContent,
	            container: textLayer,
	            viewport: viewport,
	            textDivs: []
	          });
	        });
	        this.lastRenderedPdfPage = Math.max(pageNumber, this.lastRenderedPdfPage);
	        if (pageNumber === 1) {
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
	  createCanvasPage() {
	    const canvas = document.createElement('canvas');
	    canvas.className = 'ui-viewer-document-page-canvas';
	    this.contentNode.appendChild(canvas);
	    return canvas;
	  }
	  getContentWidth() {
	    if (this.firstWidthDocumentPage) {
	      return Promise.resolve(this.firstWidthDocumentPage);
	    }
	    return new Promise(resolve => {
	      this.loadDocument().then(() => {
	        this.renderDocumentPage(this.pdfDocument, 1).then(page => {
	          resolve(page.getViewport(this.scale).width);
	        });
	      });
	    });
	  }
	  afterRender() {
	    this.loadDocument().then(pdf => {
	      for (let i = 1; i <= Math.min(pdf.numPages, PAGES_TO_PRELOAD); i++) {
	        if (i === 1) {
	          this._handleControls = this.controller.handleVisibleControls.bind(this.controller);
	          this.controller.enableReadingMode(true);
	          const printAction = this.controller.actionPanel.getItemById('print');
	          if (printAction) {
	            printAction.layout.container.classList.remove('ui-btn-disabled');
	          }
	          main_core.Runtime.throttle(main_core.Event.bind(window, 'mousemove', this._handleControls), 20);
	        }
	        this.renderDocumentPage(pdf, i);
	      }
	    });
	  }
	  beforeHide() {
	    this.pdfRenderedPages = {};
	    main_core.Event.unbind(window, 'mousemove', this._handleControls);
	    if (this.printer) {
	      this.hidePrintProgress();
	      this.printer.destroy();
	    }
	  }
	  updatePrintProgressMessage(index, total) {
	    const progress = Math.round(index / total * 100);
	    this.controller.setTextOnLoading(main_core.Loc.getMessage('JS_UI_VIEWER_ITEM_PREPARING_TO_PRINT').replace('#PROGRESS#', progress));
	  }
	  showPrintProgress(index, total) {
	    this.contentNode.style.opacity = 0.7;
	    this.contentNode.style.filter = 'blur(2px)';
	    this.controller.showLoading({
	      zIndex: 1
	    });
	    this.updatePrintProgressMessage(index, total);
	  }
	  hidePrintProgress() {
	    this.contentNode.style.opacity = null;
	    this.contentNode.style.filter = null;
	    this.controller.hideLoading();
	  }
	  print() {
	    if (!this.pdfDocument) {
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
	  handleKeyPress(event) {
	    switch (event.code) {
	      case 'PageDown':
	      case 'PageUp':
	      case 'ArrowDown':
	      case 'ArrowUp':
	        BX.focus(this.contentNode);
	        break;
	    }
	  }
	  getScale() {
	    return this.scale;
	  }
	  setScale(scale) {
	    this.scale = scale;
	    return this;
	  }
	  updateScale(scale) {
	    scale = Number(scale);
	    if (this.scale === scale) {
	      return Promise.resolve();
	    }
	    const ratio = scale / this.scale;
	    const updatePageScale = (page, canvases, textLayers) => {
	      const canvas = canvases[page.pageIndex];
	      if (!canvas) {
	        return Promise.resolve();
	      }
	      return new Promise(resolve => {
	        const viewport = page.getViewport(this.scale);
	        canvas.width = viewport.width;
	        canvas.height = viewport.height;
	        page.render({
	          canvasContext: canvas.getContext('2d'),
	          viewport: viewport
	        }).then(() => {
	          const textLayer = textLayers[page.pageIndex];
	          if (textLayer) {
	            main_core.Dom.clean(textLayer);
	            main_core.Dom.adjust(textLayer, {
	              style: {
	                margin: '-' + canvas.offsetHeight + 'px auto 0 auto',
	                height: viewport.height + 'px',
	                width: viewport.width + 'px'
	              }
	            });
	            page.getTextContent().then(textContent => {
	              pdfjsLib.renderTextLayer({
	                textContent: textContent,
	                container: textLayer,
	                viewport: viewport,
	                textDivs: []
	              });
	              resolve();
	            });
	          } else {
	            resolve();
	          }
	        });
	      });
	    };
	    const promises = [];
	    this.scale = scale;
	    const canvases = Array.from(this.contentNode.querySelectorAll('canvas[class="ui-viewer-document-page-canvas"]'));
	    const textLayers = Array.from(this.contentNode.querySelectorAll('div[class="ui-viewer-pdf-text-layer"]'));
	    Object.values(this.pdfRenderedPages).forEach(renderedPage => {
	      if (renderedPage instanceof Promise) {
	        promises.push(new Promise(resolve => {
	          renderedPage.then(page => {
	            updatePageScale(page, canvases, textLayers).then(resolve);
	          });
	        }));
	      } else {
	        promises.push(updatePageScale(renderedPage, canvases, textLayers));
	      }
	    });
	    const scrollTop = this.contentNode.scrollTop * ratio;
	    this.contentNode.scrollTo(this.contentNode.scrollLeft, scrollTop);
	    return Promise.all(promises);
	  }
	  getPagesNumber() {
	    if (!this.pdfDocument) {
	      return null;
	    }
	    return main_core.Text.toInteger(this.pdfDocument._pdfInfo.numPages);
	  }
	  scrollToPage(pageNumber) {
	    const isChanged = this.setPageNumber(pageNumber) !== null;
	    if (!isChanged) {
	      return Promise.resolve();
	    }
	    return new Promise(resolve => {
	      const renderPromises = [];
	      for (let i = 1; i < pageNumber; i++) {
	        renderPromises.push(this.renderDocumentPage(this.pdfDocument, i));
	      }
	      Promise.all(renderPromises).then(pages => {
	        let height = 0;
	        pages.forEach(page => {
	          const viewport = page.getViewport(this.scale);
	          height += viewport.height + 7;
	        });
	        this.contentNode.scrollTo(this.contentNode.scrollLeft, height);
	        resolve();
	      });
	    });
	  }
	  getPageNumber() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _pageNumber)[_pageNumber];
	  }
	  setPageNumber(pageNumber) {
	    pageNumber = main_core.Text.toInteger(pageNumber);
	    if (pageNumber < 0) {
	      pageNumber = 1;
	    }
	    let numPages = this.getPagesNumber();
	    if (!numPages) {
	      numPages = 1;
	    }
	    if (pageNumber > numPages) {
	      pageNumber = numPages;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _pageNumber)[_pageNumber] !== pageNumber) {
	      babelHelpers.classPrivateFieldLooseBase(this, _pageNumber)[_pageNumber] = pageNumber;
	      main_core_events.EventEmitter.emit(this, 'BX.UI.Viewer.Item.Document:updatePageNumber');
	      return this;
	    }
	    return null;
	  }
	}
	function _resetState2() {
	  this.pdfRenderedPages = {};
	  this.lastRenderedPdfPage = null;
	  this.pdfDocument = null;
	  this.pdfPages = {};
	  this.setPageNumber(1);
	  if (this.printer) {
	    this.hidePrintProgress();
	    this.printer.destroy();
	  }
	}
	Object.defineProperty(Document, _loadingLibraryPromise, {
	  writable: true,
	  value: null
	});
	const PRINT_SCALE = 1;
	class PrintService {
	  constructor(options) {
	    options = options || {};
	    this.pdf = options.pdf;
	    this.iframe = null;
	    this.documentOverview = {};
	  }
	  init() {
	    if (this.documentOverview) {
	      return Promise.resolve(this.documentOverview);
	    }
	    return new Promise(resolve => {
	      this.pdf.getPage(1).then(page => {
	        const viewport = page.getViewport(PRINT_SCALE);
	        this.documentOverview = {
	          width: viewport.width,
	          height: viewport.height,
	          rotation: viewport.rotation
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
	  prepare(options) {
	    options = options || {};
	    const pageCount = this.pdf.numPages;
	    let currentPage = -1;
	    const promise = new BXPromise();
	    let onProgress = null;
	    if (main_core.Type.isFunction(options.onProgress)) {
	      onProgress = options.onProgress;
	    }
	    this.frame = this.createIframe();
	    const process = () => {
	      if (++currentPage >= pageCount) {
	        console.log('finish', this.frame.contentWindow.document);
	        setTimeout(() => {
	          promise.fulfill();
	        }, 1000);
	        return;
	      }
	      this.renderPage(currentPage + 1).then(function () {
	        if (onProgress) {
	          onProgress(currentPage + 1, pageCount);
	        }
	        process();
	      });
	    };
	    process();
	    return promise;
	  }
	  renderPage(pageNumber) {
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
	          scratchCanvas: scratchCanvas,
	          width: width,
	          height: height
	        };
	      });
	    }).then(printItem => {
	      const img = document.createElement('img');
	      img.style.width = printItem.width;
	      img.style.height = printItem.height;
	      const scratchCanvas = printItem.scratchCanvas;
	      if ('toBlob' in scratchCanvas && !this.disableCreateObjectURL) {
	        scratchCanvas.toBlob(function (blob) {
	          img.src = URL.createObjectURL(blob);
	        });
	      } else {
	        img.src = scratchCanvas.toDataURL();
	      }
	      const wrapper = document.createElement('div');
	      wrapper.appendChild(img);
	      this.frame.contentWindow.document.body.appendChild(wrapper);
	    });
	  }
	  destroy() {
	    if (this.frame) {
	      main_core.Dom.remove(this.frame);
	    }
	  }
	  createIframe() {
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
	  performPrint() {
	    this.frame.contentWindow.focus();
	    this.frame.contentWindow.print();
	  }
	  getDocumentOverview() {
	    return this.documentOverview;
	  }
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9;
	const InlineController = main_core.Reflection.namespace('BX.UI.Viewer.InlineController');

	/**
	 * @memberof BX.UI.Viewer
	 * @extends BX.UI.Viewer.InlineController
	 */
	class SingleDocumentController extends InlineController {
	  bindEvents() {
	    if (!this.eventsAlreadyBinded && this.getDocumentItem()) {
	      main_core_events.EventEmitter.subscribe(this.getDocumentItem(), 'BX.UI.Viewer.Item.Document:updatePageNumber', () => {
	        this.getListingControl().update(this.getDocumentItem().getPageNumber());
	      });
	    }
	    super.bindEvents();
	  }
	  getDocumentItem() {
	    return this.items[0];
	  }
	  updateControls() {
	    super.updateControls();
	    this.updateListingControl();
	  }
	  getViewerContainer() {
	    if (!this.layout.container) {
	      this.layout.inner = main_core.Tag.render(_t || (_t = _`<div class="ui-viewer__single-document--container ">${0}</div>`), this.getItemContainer());
	      if (this.stretch) {
	        main_core.Dom.addClass(this.layout.inner, '--stretch');
	      }
	      this.layout.container = main_core.Tag.render(_t2 || (_t2 = _`<div class="">${0}${0}</div>`), this.layout.inner, this.getControlsContainer());
	    }
	    return this.layout.container;
	  }
	  getControlsContainer() {
	    if (!this.layout.controlsContainer) {
	      return main_core.Tag.render(_t3 || (_t3 = _`<div class="ui-viewer__single-document--controls">
				${0}
				${0}
			</div>`), this.getListingControl().render(), this.getScaleControl().render());
	    }
	    return this.layout.controlsContainer;
	  }
	  getListingControl() {
	    if (!this.listingControl) {
	      this.listingControl = new ListingControl();
	      this.listingControl.subscribe('pageUpdated', () => {
	        var _this$getDocumentItem;
	        (_this$getDocumentItem = this.getDocumentItem()) == null ? void 0 : _this$getDocumentItem.scrollToPage(this.listingControl.getCurrent());
	      });
	      this.updateListingControl();
	    }
	    return this.listingControl;
	  }
	  getScaleControl() {
	    if (!this.scaleControl) {
	      this.scaleControl = new ScaleControl();
	      this.scaleControl.subscribe('scaleUpdated', () => {
	        var _this$getDocumentItem2;
	        (_this$getDocumentItem2 = this.getDocumentItem()) == null ? void 0 : _this$getDocumentItem2.updateScale(this.scaleControl.getScale());
	      });
	    }
	    return this.scaleControl;
	  }
	  updateListingControl() {
	    const item = this.getDocumentItem();
	    if (item) {
	      item.loadDocument().then(() => {
	        this.listingControl.update(1, item.getPagesNumber());
	      });
	    }
	  }
	  setScale(scale) {
	    var _this$getDocumentItem3;
	    (_this$getDocumentItem3 = this.getDocumentItem()) == null ? void 0 : _this$getDocumentItem3.setScale(scale);
	    this.getScaleControl().update(scale);
	    return this;
	  }
	  setPdfSource(pdfSource) {
	    var _this$getDocumentItem4;
	    (_this$getDocumentItem4 = this.getDocumentItem()) == null ? void 0 : _this$getDocumentItem4.setPdfSource(pdfSource);
	    return this;
	  }
	  print() {
	    var _this$getDocumentItem5;
	    (_this$getDocumentItem5 = this.getDocumentItem()) == null ? void 0 : _this$getDocumentItem5.print();
	  }
	}
	class ListingControl extends main_core_events.EventEmitter {
	  constructor(current = 1, pages = 1) {
	    super();
	    this.container = null;
	    this.pagesContainer = null;
	    this.setEventNamespace('BX.UI.Viewer.SingleDocumentController.ListingControl');
	    this.pages = main_core.Text.toInteger(pages);
	    this.current = main_core.Text.toInteger(current);
	    this.arrowClickHandler = this.handleArrowClick.bind(this);
	  }
	  update(current, pages = null) {
	    current = main_core.Text.toInteger(current);
	    pages = main_core.Text.toInteger(pages);
	    if (pages >= 1) {
	      this.pages = pages;
	    }
	    if (current < 1) {
	      current = 1;
	    }
	    if (current > this.pages) {
	      current = this.pages;
	    }
	    if (current !== this.current) {
	      this.current = current;
	      this.emit('pageUpdated', {
	        page: this.current
	      });
	    }
	    this.adjust();
	  }
	  adjust() {
	    this.pagesContainer.innerHTML = this.renderPages();
	  }
	  getCurrent() {
	    return this.current;
	  }
	  render() {
	    if (!this.container) {
	      this.pagesContainer = main_core.Tag.render(_t4 || (_t4 = _`<div class="ui-viewer__single-document--listing-info">
				${0}
			</div>`), this.renderPages());
	      this.container = main_core.Tag.render(_t5 || (_t5 = _`<div class="ui-viewer__single-document--listing">
				<div class="ui-viewer__single-document--listing--btn --prev" onclick="${0}"></div>
				${0}
				<div class="ui-viewer__single-document--listing--btn --next" onclick="${0}"></div>
			</div>`), this.arrowClickHandler, this.pagesContainer, this.arrowClickHandler);
	    }
	    return this.container;
	  }
	  renderPages() {
	    return main_core.Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_LISTING_PAGES').replace('#CURRENT#', this.current).replace('#ALL#', this.pages);
	  }
	  handleArrowClick(event) {
	    if (event.target.classList.contains('--prev')) {
	      this.update(this.current - 1);
	    }
	    if (event.target.classList.contains('--next')) {
	      this.update(this.current + 1);
	    }
	  }
	}

	// const SCALE_MIN = 0.92;
	const SCALE_MIN = 0.5;
	const SCALE_MAX = 3;
	class ScaleControl extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.scale = SCALE_MIN;
	    this.container = null;
	    this.zoomInContainer = null;
	    this.zoomOutContainer = null;
	    this.zoomValueNode = null;
	    this.scale = SCALE_MIN;
	    this.setEventNamespace('BX.UI.Viewer.SingleDocumentController.ScaleControl');
	    this.scaleClickHandler = this.handleScaleClick.bind(this);
	  }
	  getScale() {
	    return this.scale;
	  }
	  setDefaultScale() {
	    this.update(SCALE_MIN);
	  }
	  adjust() {
	    if (this.scale <= SCALE_MIN) {
	      main_core.Dom.hide(this.getZoomOutContainer());
	    } else {
	      main_core.Dom.show(this.getZoomOutContainer());
	    }
	    if (this.scale >= SCALE_MAX) {
	      main_core.Dom.hide(this.getZoomInContainer());
	    } else {
	      main_core.Dom.show(this.getZoomInContainer());
	    }
	    this.getZoomValueNode().innerText = Math.round(this.scale * 100);
	  }
	  update(scale) {
	    scale = main_core.Text.toNumber(scale);
	    if (scale < SCALE_MIN) {
	      scale = SCALE_MIN;
	    }
	    if (scale > SCALE_MAX) {
	      scale = SCALE_MAX;
	    }
	    if (scale !== this.scale) {
	      this.scale = scale;
	      this.emit('scaleUpdated');
	      this.adjust();
	    }
	  }
	  render() {
	    if (!this.container) {
	      this.container = main_core.Tag.render(_t6 || (_t6 = _`<div class="ui-viewer__single-document--zoom">
				${0}
				${0}
				${0}
			</div>`), this.getZoomOutContainer(), this.getZoomValueNode(), this.getZoomInContainer());
	      this.adjust();
	    }
	    return this.container;
	  }
	  getZoomInContainer() {
	    if (!this.zoomInContainer) {
	      this.zoomInContainer = main_core.Tag.render(_t7 || (_t7 = _`<div
				class="ui-viewer__single-document--zoom-control --zoom-in"
				onclick="${0}"
			>
<!--				${0}-->
			</div>`), this.scaleClickHandler, main_core.Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_SCALE_ZOOM_IN'));
	    }
	    return this.zoomInContainer;
	  }
	  getZoomOutContainer() {
	    if (!this.zoomOutContainer) {
	      this.zoomOutContainer = main_core.Tag.render(_t8 || (_t8 = _`<div 
				class="ui-viewer__single-document--zoom-control --zoom-out"
				onclick="${0}"
			>
<!--				${0}-->
			</div>`), this.scaleClickHandler, main_core.Loc.getMessage('JS_UI_VIEWER_SINGLE_DOCUMENT_SCALE_ZOOM_OUT'));
	    }
	    return this.zoomOutContainer;
	  }
	  getZoomValueNode() {
	    if (!this.zoomValueNode) {
	      this.zoomValueNode = main_core.Tag.render(_t9 || (_t9 = _`<span class="ui-viewer__single-document--zoom-value">100</span>`));
	    }
	    return this.zoomValueNode;
	  }
	  handleScaleClick(event) {
	    let scale = this.scale;
	    if (event.target.classList.contains('--zoom-in')) {
	      scale = this.scale * 1.1;
	    }
	    if (event.target.classList.contains('--zoom-out')) {
	      scale = this.scale * 0.9;
	    }
	    this.update(scale);
	  }
	}

	exports.Document = Document;
	exports.PrintService = PrintService;
	exports.SingleDocumentController = SingleDocumentController;

}((this.BX.UI.Viewer = this.BX.UI.Viewer || {}),BX,BX.Event));
//# sourceMappingURL=viewer.bundle.js.map
