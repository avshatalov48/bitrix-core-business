this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,main_popup,main_core) {
	'use strict';

	let _ = t => t,
	    _t,
	    _t2,
	    _t3,
	    _t4,
	    _t5;

	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");

	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");

	var _description = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("description");

	var _className = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("className");

	var _image = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("image");

	var _videoUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("videoUrl");

	var _videoIframe = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("videoIframe");

	var _videoHtmlElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("videoHtmlElement");

	var _videoOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("videoOptions");

	var _videoPlayPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("videoPlayPromise");

	var _autoplay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("autoplay");

	var _html = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("html");

	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");

	var _setVideo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setVideo");

	class Slide {
	  constructor(_options) {
	    Object.defineProperty(this, _setVideo, {
	      value: _setVideo2
	    });
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _description, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _className, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _image, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _videoUrl, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _videoIframe, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _videoHtmlElement, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _videoOptions, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _videoPlayPromise, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _autoplay, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _html, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    _options = main_core.Type.isPlainObject(_options) ? _options : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = main_core.Type.isStringFilled(_options.id) ? _options.id : babelHelpers.classPrivateFieldLooseBase(this, _id)[_id];
	    babelHelpers.classPrivateFieldLooseBase(this, _className)[_className] = main_core.Type.isStringFilled(_options.className) ? _options.className : babelHelpers.classPrivateFieldLooseBase(this, _className)[_className];
	    babelHelpers.classPrivateFieldLooseBase(this, _image)[_image] = main_core.Type.isStringFilled(_options.image) ? _options.image : babelHelpers.classPrivateFieldLooseBase(this, _image)[_image];
	    babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = main_core.Type.isStringFilled(_options.title) ? _options.title : babelHelpers.classPrivateFieldLooseBase(this, _title)[_title];
	    babelHelpers.classPrivateFieldLooseBase(this, _description)[_description] = main_core.Type.isStringFilled(_options.description) ? _options.description : babelHelpers.classPrivateFieldLooseBase(this, _description)[_description];

	    babelHelpers.classPrivateFieldLooseBase(this, _setVideo)[_setVideo](_options.video);

	    babelHelpers.classPrivateFieldLooseBase(this, _autoplay)[_autoplay] = main_core.Type.isBoolean(_options.autoplay) ? _options.autoplay : babelHelpers.classPrivateFieldLooseBase(this, _autoplay)[_autoplay];

	    if (main_core.Type.isElementNode(_options.html) || main_core.Type.isStringFilled(_options.html)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _html)[_html] = _options.html;
	    }
	  }

	  getId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id)[_id];
	  }

	  getTitle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _title)[_title];
	  }

	  getDescription() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _description)[_description];
	  }

	  getBullet() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('bullet', () => {
	      return main_core.Tag.render(_t || (_t = _`<span class="ui-whats-new-bullet" title="${0}"></span>`), this.getTitle());
	    });
	  }

	  getVideoIframe() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _videoIframe)[_videoIframe];
	  }

	  getVideoHtmlElement() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _videoHtmlElement)[_videoHtmlElement];
	  }

	  pauseVideo() {
	    if (this.getVideoIframe()) {
	      this.getVideoIframe().contentWindow.postMessage(JSON.stringify({
	        event: 'command',
	        func: 'stopVideo'
	      }), '*');
	    } else if (this.getVideoHtmlElement()) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _videoPlayPromise)[_videoPlayPromise]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _videoPlayPromise)[_videoPlayPromise].then(() => {
	          this.getVideoHtmlElement().pause();
	          babelHelpers.classPrivateFieldLooseBase(this, _videoPlayPromise)[_videoPlayPromise] = null;
	        }).catch(() => {});
	      }
	    }
	  }

	  playVideo() {
	    if (this.getVideoIframe()) {
	      this.getVideoIframe().contentWindow.postMessage(JSON.stringify({
	        event: 'command',
	        func: 'playVideo'
	      }), '*');
	    } else if (this.getVideoHtmlElement()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _videoPlayPromise)[_videoPlayPromise] = this.getVideoHtmlElement().play();
	    }
	  }

	  isVideo() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _videoUrl)[_videoUrl] !== null || babelHelpers.classPrivateFieldLooseBase(this, _videoOptions)[_videoOptions] !== null;
	  }

	  isAutoplay() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _autoplay)[_autoplay];
	  }

	  getContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('container', () => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _videoUrl)[_videoUrl]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _videoIframe)[_videoIframe] = main_core.Tag.render(_t2 || (_t2 = _`<iframe 
						src="${0}" 
						id="${0}" 
						class="ui-whats-new-slide-item ${0}" 
						frameborder="0"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
						allowfullscreen></iframe>
				`), babelHelpers.classPrivateFieldLooseBase(this, _videoUrl)[_videoUrl], babelHelpers.classPrivateFieldLooseBase(this, _id)[_id], babelHelpers.classPrivateFieldLooseBase(this, _className)[_className]);
	        return babelHelpers.classPrivateFieldLooseBase(this, _videoIframe)[_videoIframe];
	      } else if (babelHelpers.classPrivateFieldLooseBase(this, _videoOptions)[_videoOptions]) {
	        const sources = [];

	        babelHelpers.classPrivateFieldLooseBase(this, _videoOptions)[_videoOptions].sources.forEach(source => {
	          sources.push(`<source src="${source.src}" type="${source.type}" />`);
	        });

	        babelHelpers.classPrivateFieldLooseBase(this, _videoHtmlElement)[_videoHtmlElement] = main_core.Tag.render(_t3 || (_t3 = _`<video>${0}</video>`), sources.join(''));

	        if (main_core.Type.isPlainObject(babelHelpers.classPrivateFieldLooseBase(this, _videoOptions)[_videoOptions].attrs)) {
	          main_core.Dom.attr(babelHelpers.classPrivateFieldLooseBase(this, _videoHtmlElement)[_videoHtmlElement], babelHelpers.classPrivateFieldLooseBase(this, _videoOptions)[_videoOptions].attrs);
	        }

	        return main_core.Tag.render(_t4 || (_t4 = _`
						<div 
							id="${0}" 
							class="ui-whats-new-slide-item ${0}"
						>${0}</div>`), babelHelpers.classPrivateFieldLooseBase(this, _id)[_id], babelHelpers.classPrivateFieldLooseBase(this, _className)[_className], babelHelpers.classPrivateFieldLooseBase(this, _videoHtmlElement)[_videoHtmlElement]);
	      } else {
	        var _babelHelpers$classPr;

	        return main_core.Tag.render(_t5 || (_t5 = _`<div 
						id="${0}" 
						class="ui-whats-new-slide-item ${0}" 
						${0}>${0}</div>`), babelHelpers.classPrivateFieldLooseBase(this, _id)[_id], babelHelpers.classPrivateFieldLooseBase(this, _className)[_className], babelHelpers.classPrivateFieldLooseBase(this, _image)[_image] ? 'style="background-image: url(' + babelHelpers.classPrivateFieldLooseBase(this, _image)[_image] + ')"' : '', (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _html)[_html]) != null ? _babelHelpers$classPr : '');
	      }
	    });
	  }

	}

	function _setVideo2(options) {
	  if (main_core.Type.isStringFilled(options)) {
	    const url = new URL(options);

	    if (url.host.includes('youtube')) {
	      url.searchParams.append('enablejsapi', '1');
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _videoUrl)[_videoUrl] = url.toString();
	  } else if (main_core.Type.isPlainObject(options) && main_core.Type.isArrayFilled(options.sources)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _videoOptions)[_videoOptions] = options;
	  }
	}

	let _$1 = t => t,
	    _t$1,
	    _t2$1,
	    _t3$1,
	    _t4$1,
	    _t5$1,
	    _t6,
	    _t7,
	    _t8;

	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");

	var _slides = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("slides");

	var _cache$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");

	var _position = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("position");

	var _popupOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupOptions");

	var _documentKeyDownHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentKeyDownHandler");

	var _destroying = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("destroying");

	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");

	var _unbindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unbindEvents");

	var _handleDocumentKeyDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDocumentKeyDown");

	var _handleBulletClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleBulletClick");

	var _handlePopupShow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePopupShow");

	var _handlePopupClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePopupClose");

	var _handlePopupDestroy = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePopupDestroy");

	class WhatsNew extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _handlePopupDestroy, {
	      value: _handlePopupDestroy2
	    });
	    Object.defineProperty(this, _handlePopupClose, {
	      value: _handlePopupClose2
	    });
	    Object.defineProperty(this, _handlePopupShow, {
	      value: _handlePopupShow2
	    });
	    Object.defineProperty(this, _handleBulletClick, {
	      value: _handleBulletClick2
	    });
	    Object.defineProperty(this, _handleDocumentKeyDown, {
	      value: _handleDocumentKeyDown2
	    });
	    Object.defineProperty(this, _unbindEvents, {
	      value: _unbindEvents2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _slides, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _cache$1, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    Object.defineProperty(this, _position, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _popupOptions, {
	      writable: true,
	      value: {}
	    });
	    this.infinityLoop = false;
	    Object.defineProperty(this, _documentKeyDownHandler, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _destroying, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace('BX.UI.Dialogs.WhatsNew');
	    options = main_core.Type.isPlainObject(options) ? options : {};

	    if (!main_core.Type.isArrayFilled(options.slides)) {
	      throw new Error('NewStructurePopup: "items" parameter is required.');
	    }

	    options.slides.forEach(slideOptions => {
	      babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides].push(new Slide(slideOptions));
	    });

	    if (main_core.Type.isPlainObject(options.popupOptions)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popupOptions)[_popupOptions] = options.popupOptions;
	    }

	    if (main_core.Type.isBoolean(options.infinityLoop)) {
	      this.infinityLoop = options.infinityLoop;
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _documentKeyDownHandler)[_documentKeyDownHandler] = babelHelpers.classPrivateFieldLooseBase(this, _handleDocumentKeyDown)[_handleDocumentKeyDown].bind(this);
	    this.subscribeFromOptions(options.events);
	  }

	  getPopup() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] !== null) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup(Object.assign({
	      className: 'ui-whats-new-popup',
	      closeIcon: false,
	      closeByEsc: true,
	      overlay: true,
	      cacheable: false,
	      animation: 'scale',
	      content: this.getContentContainer(),
	      width: 720,
	      height: 530,
	      autoHide: true
	    }, babelHelpers.classPrivateFieldLooseBase(this, _popupOptions)[_popupOptions]));

	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].subscribe('onDestroy', babelHelpers.classPrivateFieldLooseBase(this, _handlePopupDestroy)[_handlePopupDestroy].bind(this));

	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].subscribe('onShow', babelHelpers.classPrivateFieldLooseBase(this, _handlePopupShow)[_handlePopupShow].bind(this));

	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].subscribe('onClose', babelHelpers.classPrivateFieldLooseBase(this, _handlePopupClose)[_handlePopupClose].bind(this));

	    this.selectSlide();
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	  }

	  getCurrentSlide() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides][babelHelpers.classPrivateFieldLooseBase(this, _position)[_position]];
	  }

	  getSlides() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides];
	  }

	  getSlideByPosition(position) {
	    var _babelHelpers$classPr;

	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides][position]) != null ? _babelHelpers$classPr : null;
	  }

	  getPositionBySlide(slide) {
	    for (let position = 0; position < babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides].length; position++) {
	      const current = babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides][position];

	      if (current === slide) {
	        return position;
	      }
	    }

	    return null;
	  }

	  getFirstPosition() {
	    return 0;
	  }

	  getLastPosition() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides].length - 1;
	  }

	  getContentContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].remember('content', () => {
	      return main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="ui-whats-new-content"> 
					${0}
					<div class="ui-whats-new-slide-wrap"> 
						${0} 
						${0} 
						<div class="ui-whats-new-slide-inner">${0}</div>  
					</div> 
					${0}
					<div class="ui-whats-new-close-btn" onclick="${0}"></div>
				</div>
			`), this.getHeadContainer(), this.getPrevBtn(), this.getNextBtn(), this.getSliderBox(), this.getBulletBox(), this.hide.bind(this));
	    });
	  }

	  getHeadContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].remember('head', () => {
	      return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<div class="ui-whats-new-head"> 
					${0}
					${0}
				</div>
			`), this.getTitleContainer(), this.getDescContainer());
	    });
	  }

	  getTitleContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].remember('title', () => {
	      return main_core.Tag.render(_t3$1 || (_t3$1 = _$1`<div class="ui-whats-new-title"></div>`));
	    });
	  }

	  getDescContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].remember('description', () => {
	      return main_core.Tag.render(_t4$1 || (_t4$1 = _$1`<div class="ui-whats-new-desc"></div>`));
	    });
	  }

	  getSliderBox() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].remember('sliderBox', () => {
	      return main_core.Tag.render(_t5$1 || (_t5$1 = _$1`<div class="ui-whats-new-slide-box">${0}</div>`), babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides].map(slide => slide.getContainer()));
	    });
	  }

	  getBulletBox() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].remember('bulletBox', () => {
	      return this.isMoreThan1Slide() ? main_core.Tag.render(_t6 || (_t6 = _$1`<div class="ui-whats-new-bullet-box" onclick="${0}">${0}</div>`), babelHelpers.classPrivateFieldLooseBase(this, _handleBulletClick)[_handleBulletClick].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides].map(slide => slide.getBullet())) : null;
	    });
	  }

	  getPrevBtn() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].remember('prevBtn', () => {
	      return main_core.Tag.render(_t7 || (_t7 = _$1`
				<div 
					class="ui-whats-new-slide-btn --btn-prev" 
					onclick="${0}">
				</div>`), this.selectPrevSlide.bind(this));
	    });
	  }

	  getNextBtn() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].remember('nextBtn', () => {
	      return main_core.Tag.render(_t8 || (_t8 = _$1`
				<div 
					class="ui-whats-new-slide-btn --btn-next" 
					onclick="${0}">
				</div>
			`), this.selectNextSlide.bind(this));
	    });
	  }

	  isMoreThan1Slide() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides].length > 1;
	  }

	  show() {
	    this.getPopup().show();
	  }

	  hide() {
	    this.getPopup().close();
	  }

	  destroy() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _destroying)[_destroying]) {
	      return;
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _destroying)[_destroying] = true;
	    this.emit('onDestroy');

	    babelHelpers.classPrivateFieldLooseBase(this, _unbindEvents)[_unbindEvents]();

	    this.getPopup().destroy();

	    for (const property in this) {
	      if (this.hasOwnProperty(property)) {
	        delete this[property];
	      }
	    }

	    Object.setPrototypeOf(this, null);
	  }

	  selectPrevSlide() {
	    if (this.infinityLoop && babelHelpers.classPrivateFieldLooseBase(this, _position)[_position] === this.getFirstPosition()) {
	      this.selectSlide(this.getLastPosition());
	    } else {
	      this.selectSlide(babelHelpers.classPrivateFieldLooseBase(this, _position)[_position] - 1);
	    }
	  }

	  selectNextSlide() {
	    if (this.infinityLoop && babelHelpers.classPrivateFieldLooseBase(this, _position)[_position] === this.getLastPosition()) {
	      this.selectSlide(this.getFirstPosition());
	    } else {
	      this.selectSlide(babelHelpers.classPrivateFieldLooseBase(this, _position)[_position] + 1);
	    }
	  }

	  selectSlide(position = 0) {
	    const firstPosition = this.getFirstPosition();
	    const lastPosition = this.getLastPosition();
	    position = Math.min(Math.max(position, firstPosition), lastPosition);

	    if (babelHelpers.classPrivateFieldLooseBase(this, _position)[_position] === position) {
	      return;
	    }

	    const currentSlide = this.getSlideByPosition(babelHelpers.classPrivateFieldLooseBase(this, _position)[_position]);
	    const newSlide = this.getSlideByPosition(position);
	    const event = new main_core_events.BaseEvent({
	      data: {
	        currentSlide,
	        newSlide
	      }
	    });
	    this.emit('Slide:onBeforeSelect', event);

	    if (event.isDefaultPrevented()) {
	      return;
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _position)[_position] = position; // Ears

	    if (!this.isMoreThan1Slide()) {
	      main_core.Dom.addClass(this.getPrevBtn(), '--hide');
	      main_core.Dom.addClass(this.getNextBtn(), '--hide');
	    } else if (!this.infinityLoop) {
	      if (position === firstPosition) {
	        main_core.Dom.addClass(this.getPrevBtn(), '--hide');
	        main_core.Dom.removeClass(this.getNextBtn(), '--hide');
	      } else if (position === lastPosition) {
	        main_core.Dom.removeClass(this.getPrevBtn(), '--hide');
	        main_core.Dom.addClass(this.getNextBtn(), '--hide');
	      } else {
	        main_core.Dom.removeClass(this.getPrevBtn(), '--hide');
	        main_core.Dom.removeClass(this.getNextBtn(), '--hide');
	      }
	    } // Sliding


	    main_core.Dom.style(this.getSliderBox(), {
	      transform: 'translateX(' + -position * this.getSliderBox().offsetWidth + 'px)'
	    }); // Bullets

	    babelHelpers.classPrivateFieldLooseBase(this, _slides)[_slides].forEach((slide, index) => {
	      if (position === index) {
	        main_core.Dom.addClass(slide.getBullet(), '--active');
	      } else {
	        main_core.Dom.removeClass(slide.getBullet(), '--active');
	      }
	    }); // Header


	    main_core.Dom.style(this.getHeadContainer(), {
	      opacity: 0,
	      transition: 'none'
	    });
	    const title = newSlide.getTitle().trim();
	    const desc = newSlide.getDescription().trim();

	    if (main_core.Type.isStringFilled(title)) {
	      main_core.Dom.removeClass(this.getContentContainer(), '--empty-head');

	      if (main_core.Type.isStringFilled(desc)) {
	        main_core.Dom.removeClass(this.getContentContainer(), '--empty-desc');
	      } else {
	        main_core.Dom.addClass(this.getContentContainer(), '--empty-desc');
	      }
	    } else {
	      main_core.Dom.addClass(this.getContentContainer(), '--empty-head');
	    }

	    this.getTitleContainer().innerHTML = title;
	    this.getDescContainer().innerHTML = desc;

	    const finalize = () => {
	      this.getSlides().forEach(slide => {
	        if (this.getCurrentSlide() !== slide) {
	          main_core.Dom.style(slide.getContainer(), 'opacity', null);
	          slide.pauseVideo();
	        }
	      });
	      main_core.Dom.style(this.getHeadContainer(), 'opacity', null);
	    };

	    if (newSlide.isVideo() && newSlide.isAutoplay()) {
	      newSlide.playVideo();
	    }

	    setTimeout(finalize, 700);
	    requestAnimationFrame(() => {
	      requestAnimationFrame(() => {
	        if (currentSlide) {
	          main_core.Dom.style(currentSlide.getContainer(), 'opacity', 0);
	        }

	        main_core.Dom.style(newSlide.getContainer(), 'opacity', 1);
	        main_core.Dom.style(this.getHeadContainer(), 'opacity', 1);
	        main_core.Dom.style(this.getHeadContainer(), 'transition', null);
	      });
	    });
	    this.emit('Slide:onSelect', {
	      slide: newSlide
	    });
	  }

	}

	function _bindEvents2() {
	  main_core.Event.bind(document, 'keydown', babelHelpers.classPrivateFieldLooseBase(this, _documentKeyDownHandler)[_documentKeyDownHandler]);
	}

	function _unbindEvents2() {
	  main_core.Event.unbind(document, 'keydown', babelHelpers.classPrivateFieldLooseBase(this, _documentKeyDownHandler)[_documentKeyDownHandler]);
	}

	function _handleDocumentKeyDown2(event) {
	  if (!this.getPopup().isShown()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _unbindEvents)[_unbindEvents]();

	    return;
	  }

	  if (event.metaKey || event.ctrlKey || event.altKey) {
	    return;
	  }

	  if (event.key === 'ArrowLeft') {
	    this.selectPrevSlide();
	  } else if (event.key === 'ArrowRight') {
	    this.selectNextSlide();
	  }
	}

	function _handleBulletClick2(event) {
	  const slide = this.getSlides().find(slide => {
	    return event.target === slide.getBullet();
	  });
	  const position = this.getPositionBySlide(slide);

	  if (position !== null) {
	    this.selectSlide(position);
	  }
	}

	function _handlePopupShow2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();

	  this.emit('onShow');
	}

	function _handlePopupClose2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _unbindEvents)[_unbindEvents]();

	  this.getSlides().forEach(slide => {
	    slide.pauseVideo();
	  });
	  this.emit('onHide');
	}

	function _handlePopupDestroy2() {
	  this.getSlides().forEach(slide => {
	    slide.pauseVideo();
	  });
	  this.destroy();
	}

	exports.WhatsNew = WhatsNew;
	exports.Slide = Slide;

}((this.BX.UI.Dialogs = this.BX.UI.Dialogs || {}),BX.Event,BX.Main,BX));
//# sourceMappingURL=whats-new.bundle.js.map
