/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events,ui_forms,ui_fonts_comforterBrush,main_core,ui_buttons,main_popup,ui_dialogs_messagebox) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	class Tab extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setOptions(options);
	    this.setEventNamespace('BX.UI.SignUp.Tabs.Tab');
	    this.subscribeFromOptions(options.events);
	  }
	  setOptions(options) {
	    this.cache.set('options', {
	      ...options
	    });
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  getIconNode() {
	    return this.cache.remember('iconNode', () => {
	      return main_core.Tag.render(_t || (_t = _`
				<span style="background-image: url('${0}');"></span>
			`), this.getOptions().icon);
	    });
	  }
	  getHeaderLayout() {
	    return this.cache.remember('headerLayout', () => {
	      return main_core.Tag.render(_t2 || (_t2 = _`
				<div 
					class="ui-sign-up-tabs-tab-header" 
					data-id="${0}"
					onclick="${0}"
				>
					<div class="ui-sign-up-tabs-tab-header-icon">
						${0}	
					</div>
					<div class="ui-sign-up-tabs-tab-header-text">
						<span>${0}</span>
					</div>
				</div>
			`), main_core.Text.encode(this.getOptions().id), this.onHeaderClick.bind(this), this.getIconNode(), this.getOptions().header);
	    });
	  }
	  onHeaderClick(event) {
	    event.preventDefault();
	    this.emit('onHeaderClick');
	  }
	  getContent() {
	    return this.getOptions().content;
	  }
	  activate() {
	    main_core.Dom.addClass(this.getHeaderLayout(), 'ui-sign-up-tabs-tab-header-active');
	    main_core.Dom.style(this.getIconNode(), {
	      'background-image': `url('${this.getOptions().activeIcon}')`
	    });
	  }
	  deactivate() {
	    main_core.Dom.removeClass(this.getHeaderLayout(), 'ui-sign-up-tabs-tab-header-active');
	    main_core.Dom.style(this.getIconNode(), {
	      'background-image': `url('${this.getOptions().icon}')`
	    });
	  }
	  isActive() {
	    return main_core.Dom.hasClass(this.getHeaderLayout(), 'ui-sign-up-tabs-tab-header-active');
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3;
	class Tabs extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.SignUp.Tabs');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	    this.onTabHeaderClick = this.onTabHeaderClick.bind(this);
	    const {
	      defaultState
	    } = this.getOptions();
	    if (main_core.Type.isStringFilled(defaultState)) {
	      const currentTab = this.getTabs().find(tab => {
	        return tab.getOptions().id === defaultState;
	      });
	      if (currentTab) {
	        this.setCurrentTab(currentTab);
	        currentTab.activate();
	      } else {
	        const [firstTab] = this.getTabs();
	        this.setCurrentTab(firstTab);
	        firstTab.activate();
	      }
	    } else {
	      const [firstTab] = this.getTabs();
	      this.setCurrentTab(firstTab);
	      firstTab.activate();
	    }
	  }
	  getCurrentTab() {
	    return this.cache.get('currentTab');
	  }
	  setCurrentTab(tab) {
	    this.cache.set('currentTab', tab);
	  }
	  setOptions(options) {
	    this.cache.set('options', {
	      ...options
	    });
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  getTabs() {
	    return this.cache.remember('tabs', () => {
	      return this.getOptions().tabs.map(options => {
	        return new Tab({
	          ...options,
	          events: {
	            onHeaderClick: this.onTabHeaderClick
	          }
	        });
	      });
	    });
	  }
	  onTabHeaderClick(event) {
	    const targetTab = event.getTarget();
	    this.setCurrentTab(targetTab);
	    this.getTabs().forEach(tab => {
	      tab.deactivate();
	    });
	    targetTab.activate();
	    main_core.Dom.replace(this.getBodyLayout().firstElementChild, targetTab.getContent().getLayout());
	  }
	  getHeaderLayout() {
	    return this.cache.remember('headerLayout', () => {
	      return main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="ui-sign-up-tabs-header">
					${0}
				</div>
			`), this.getTabs().map(tab => tab.getHeaderLayout()));
	    });
	  }
	  getBodyLayout() {
	    return this.cache.remember('bodyLayout', () => {
	      return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<div class="ui-sign-up-tabs-body">
					${0}
				</div>
			`), this.getCurrentTab().getContent().getLayout());
	    });
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t3 || (_t3 = _$1`
				<div class="ui-sign-up-tabs">
					${0}
					${0}
				</div>
			`), this.getHeaderLayout(), this.getBodyLayout());
	    });
	  }
	}

	let _$2 = t => t,
	  _t$2;
	class Footer extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.SignUp.Footer');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	  }
	  setOptions(options) {
	    this.cache.set('options', options);
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  getSaveButton() {
	    return this.cache.remember('saveButtons', () => {
	      return new ui_buttons.Button({
	        text: main_core.Loc.getMessage('UI_SIGN_UP_SAVE_BUTTON_LABEL'),
	        color: BX.UI.Button.Color.PRIMARY,
	        round: true,
	        noCaps: true,
	        className: `ui-sign-up-special-${this.getOptions().mode}-btn`,
	        onclick: () => {
	          this.emit('onSaveClick');
	          const promise = this.emitAsync('onSaveClickAsync');
	          if (promise) {
	            this.getSaveButton().setWaiting(true);
	            promise.then(() => {
	              this.getSaveButton().setWaiting(false);
	            });
	          }
	        }
	      });
	    });
	  }
	  getCancelButton() {
	    return this.cache.remember('cancelButtons', () => {
	      return new ui_buttons.Button({
	        text: main_core.Loc.getMessage('UI_SIGN_UP_CANCEL_BUTTON_LABEL'),
	        color: ui_buttons.ButtonColor.LIGHT_BORDER,
	        round: true,
	        noCaps: true,
	        className: `ui-sign-up-special-${this.getOptions().mode}-btn`,
	        onclick: () => {
	          this.emit('onCancelClick');
	        }
	      });
	    });
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      const layout = main_core.Tag.render(_t$2 || (_t$2 = _$2`
				<div class="ui-sign-up-footer">
					${0}
				</div>
			`), this.getSaveButton().render());
	      if (this.getOptions().mode === 'desktop') {
	        main_core.Dom.append(this.getCancelButton().render(), layout);
	      }
	      return layout;
	    });
	  }
	}

	let _$3 = t => t,
	  _t$3;
	var _loadImage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadImage");
	class CanvasWrapper {
	  constructor(options) {
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setOptions(options);
	  }
	  setOptions(options) {
	    this.cache.set('options', options);
	  }
	  getOptions() {
	    return this.cache.get('options');
	  }
	  getDevicePixelRatio() {
	    return window.devicePixelRatio;
	  }
	  getLayout() {
	    const canvas = this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t$3 || (_t$3 = _$3`
				<canvas class="ui-sign-up-canvas"></canvas>
			`));
	    });
	    const timeoutId = setTimeout(() => {
	      if (main_core.Type.isDomNode(canvas.parentElement) && !this.cache.has('adjustCanvas')) {
	        const parentRect = {
	          width: canvas.parentElement.clientWidth,
	          height: canvas.parentElement.clientHeight
	        };
	        if (parentRect.width > 0 && parentRect.height > 0) {
	          void this.cache.remember('adjustCanvas', () => {
	            const canvas = this.getLayout();
	            const ratio = this.getDevicePixelRatio();
	            canvas.width = parentRect.width * ratio;
	            canvas.height = parentRect.height * ratio;
	            main_core.Dom.style(canvas, {
	              width: `${parentRect.width}px`,
	              height: `${parentRect.height}px`
	            });
	            const context2d = this.getLayout().getContext('2d');
	            const {
	              context2d: context2dOptions = {}
	            } = this.getOptions();
	            if (main_core.Type.isPlainObject(context2dOptions)) {
	              Object.assign(context2d, context2dOptions);
	            }
	            context2d.scale(ratio, ratio);
	          });
	        }
	      }
	      clearTimeout(timeoutId);
	    });
	    return canvas;
	  }
	  clear() {
	    const canvas = this.getLayout();
	    const context = canvas.getContext('2d');
	    context.clearRect(0, 0, canvas.width * 2, canvas.height * 2);
	  }
	  renderText(text, textColor = null) {
	    const preparedText = String(text).trim();
	    const canvas = this.getLayout();
	    const context = canvas.getContext('2d');
	    context.font = '34px Comforter Brush';
	    this.clear();
	    const ratio = this.getDevicePixelRatio();
	    const maxTextWidth = canvas.width - 20;
	    let fontSize = main_core.Text.toNumber(context.font);
	    while (fontSize > 1 && context.measureText(preparedText).width * ratio > maxTextWidth) {
	      fontSize -= 1;
	      context.font = `${fontSize}px Comforter Brush`;
	    }
	    const textWidth = context.measureText(preparedText).width * ratio;
	    if (textColor !== null && textColor !== '') {
	      context.fillStyle = textColor;
	    }
	    context.fillText(preparedText, (canvas.width - textWidth) / (2 * ratio), 34);
	  }
	  renderImage(file) {
	    return babelHelpers.classPrivateFieldLooseBase(CanvasWrapper, _loadImage)[_loadImage](file).then(image => {
	      const canvas = this.getLayout();
	      const context2d = canvas.getContext('2d');
	      const wRatio = canvas.clientWidth / image.width;
	      const hRatio = canvas.clientHeight / image.height;
	      const ratio = Math.min(wRatio, hRatio);
	      const offsetX = (canvas.clientWidth - image.width * ratio) / 2;
	      const offsetY = (canvas.clientHeight - image.height * ratio) / 2;
	      this.clear();
	      context2d.drawImage(image, 0, 0, image.width, image.height, offsetX, offsetY, image.width * ratio, image.height * ratio);
	    });
	  }
	}
	function _loadImage2(file) {
	  const fileReader = new FileReader();
	  return new Promise(resolve => {
	    fileReader.readAsDataURL(file);
	    main_core.Event.bindOnce(fileReader, 'loadend', () => {
	      const image = new Image();
	      image.src = fileReader.result;
	      main_core.Event.bindOnce(image, 'load', () => {
	        resolve(image);
	      });
	    });
	  });
	}
	Object.defineProperty(CanvasWrapper, _loadImage, {
	  value: _loadImage2
	});

	class Content extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.SignUp.Content');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	  }
	  getColor() {
	    var _this$getOptions$colo;
	    return (_this$getOptions$colo = this.getOptions().color) != null ? _this$getOptions$colo : null;
	  }
	  setOptions(options) {
	    this.cache.set('options', {
	      ...options
	    });
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  getLayout() {
	    throw new Error('Must be implemented in a child class');
	  }
	  getCanvas() {
	    throw new Error('Must be implemented in a child class');
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$2,
	  _t3$1;
	class InitialsContent extends Content {
	  constructor(options) {
	    super(options);
	    this.setEventNamespace('BX.UI.SignUp.Content.InitialsContent');
	    this.subscribeFromOptions(options == null ? void 0 : options.events);
	    this.onInput = this.onInput.bind(this);
	    void this.forceLoadFonts();
	  }
	  forceLoadFonts() {
	    const allFonts = [...document.fonts];
	    const comforterBrushFonts = allFonts.filter(font => {
	      return String(font.family).includes('Comforter Brush');
	    });
	    return Promise.all(comforterBrushFonts.map(font => font.load()));
	  }
	  getNameInput() {
	    return this.cache.remember('nameInput', () => {
	      return main_core.Tag.render(_t$4 || (_t$4 = _$4`
				<input type="text" class="ui-ctl-element" oninput="${0}">
			`), this.onInput);
	    });
	  }
	  getInitialsInput() {
	    return this.cache.remember('initialsInput', () => {
	      return main_core.Tag.render(_t2$2 || (_t2$2 = _$4`
				<input type="text" class="ui-ctl-element" oninput="${0}">
			`), this.onInput);
	    });
	  }
	  getTextValue() {
	    const name = String(this.getNameInput().value);
	    const initials = String(this.getInitialsInput().value);
	    return `${name} ${initials}`;
	  }
	  onInput() {
	    this.getCanvas().renderText(this.getTextValue(), this.getColor());
	    this.emit('onChange');
	  }
	  getCanvas() {
	    return this.cache.remember('canvas', () => {
	      return new CanvasWrapper({
	        context2d: {
	          fillStyle: '#000000',
	          font: '34px Comforter Brush'
	        }
	      });
	    });
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t3$1 || (_t3$1 = _$4`
				<div class="ui-sign-up-content">
					<div class="ui-sign-up-initials-form">
						<div class="ui-sign-up-initials-form-left">
							<div class="ui-sign-up-initials-form-label">
								${0}
							</div>
							<div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
								${0}
							</div>
						</div>
						<div class="ui-sign-up-initials-form-right">
							<div class="ui-sign-up-initials-form-label">
								${0}
							</div>
							<div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
								${0}
							</div>
						</div>
					</div>
					<div class="ui-sign-up-initials-preview">
						${0}
					</div>
				</div>
			`), main_core.Loc.getMessage('UI_SIGN_UP_TAB_INITIALS_LAST_NAME_LABEL'), this.getNameInput(), main_core.Loc.getMessage('UI_SIGN_UP_TAB_INITIALS_INITIALS_LABEL'), this.getInitialsInput(), this.getCanvas().getLayout());
	    });
	  }
	}

	function getPoint(event) {
	  if (!main_core.Type.isNil(window.TouchEvent) && event instanceof window.TouchEvent) {
	    const rect = event.target.getBoundingClientRect();
	    const {
	      touches,
	      changedTouches
	    } = event;
	    const [touch] = touches.length > 0 ? touches : changedTouches;
	    return {
	      x: touch.clientX - rect.left,
	      y: touch.clientY - rect.top
	    };
	  }
	  return {
	    x: event.offsetX,
	    y: event.offsetY
	  };
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$3;
	let preventScrolling = false;
	main_core.Event.bind(window, 'touchmove', event => {
	  if (preventScrolling) {
	    event.preventDefault();
	  }
	}, {
	  passive: false
	});
	class TouchContent extends Content {
	  constructor(options) {
	    super(options);
	    this.setEventNamespace('BX.UI.SignUp.Content.TouchContent');
	    this.subscribeFromOptions(options == null ? void 0 : options.events);
	    const canvasLayout = this.getCanvas().getLayout();
	    main_core.Event.bind(canvasLayout, 'mousedown', this.onCanvasMouseDown.bind(this));
	    main_core.Event.bind(document, 'mouseup', this.onCanvasMouseUp.bind(this));
	    main_core.Event.bind(canvasLayout, 'mousemove', this.onCanvasMouseMove.bind(this));
	    main_core.Event.bind(canvasLayout, 'touchstart', this.onCanvasMouseDown.bind(this));
	    main_core.Event.bind(canvasLayout, 'touchend', this.onCanvasMouseUp.bind(this));
	    main_core.Event.bind(canvasLayout, 'touchmove', this.onCanvasMouseMove.bind(this));
	  }
	  setIsDrawing(value) {
	    this.cache.set('isDrawing', value);
	  }
	  getIsDrawing() {
	    return this.cache.get('isDrawing', false);
	  }
	  setStartEvent(event) {
	    this.cache.set('startEvent', event);
	  }
	  getStartEvent() {
	    return this.cache.get('startEvent');
	  }
	  onCanvasMouseDown(event) {
	    this.setIsDrawing(true);
	    preventScrolling = true;
	    const context2d = this.getCanvas().getLayout().getContext('2d');
	    context2d.beginPath();
	    const point = getPoint(event);
	    context2d.moveTo(point.x, point.y);
	    this.setStartEvent(event);
	    this.emit('onChange');
	  }
	  onCanvasMouseUp(event) {
	    this.setIsDrawing(false);
	    preventScrolling = false;
	    const canvasLayout = this.getCanvas().getLayout();
	    const context2d = canvasLayout.getContext('2d');
	    context2d.closePath();
	    if (event.currentTarget === canvasLayout) {
	      const startEvent = this.getStartEvent();
	      const startPoint = getPoint(startEvent);
	      const currentPoint = getPoint(event);
	      if (startPoint.x === currentPoint.x && startPoint.y === currentPoint.y) {
	        context2d.lineTo(currentPoint.x, currentPoint.y);
	        context2d.stroke();
	      }
	    }
	    this.emit('onChange');
	  }
	  onCanvasMouseMove(event) {
	    if (this.getIsDrawing()) {
	      const context2d = this.getCanvas().getLayout().getContext('2d');
	      const point = getPoint(event);
	      const strokeColor = this.getColor();
	      if (strokeColor !== null && strokeColor !== '') {
	        context2d.strokeStyle = strokeColor;
	      }
	      context2d.lineTo(point.x, point.y);
	      context2d.stroke();
	    }
	    this.emit('onChange');
	  }
	  onCanvasMouseOut() {
	    this.setIsDrawing(false);
	    preventScrolling = false;
	    const context2d = this.getCanvas().getLayout().getContext('2d');
	    context2d.closePath();
	    this.emit('onChange');
	  }
	  getCanvas() {
	    return this.cache.remember('canvas', () => {
	      return new CanvasWrapper({
	        context2d: {
	          lineWidth: TouchContent.LineWidth,
	          strokeStyle: '000000',
	          lineJoin: 'round',
	          lineCap: 'round'
	        }
	      });
	    });
	  }
	  getClearButton() {
	    return this.cache.remember('clearButton', () => {
	      return main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<div class="ui-sign-up-touch-clear-button" onclick="${0}">
					${0}
				</div>
			`), this.onClearClick.bind(this), main_core.Loc.getMessage('UI_SIGN_UP_TOUCH_CLEAR_BUTTON'));
	    });
	  }
	  onClearClick(event) {
	    event.preventDefault();
	    this.getCanvas().clear();
	    this.emit('onChange');
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      const onTouchMove = event => {
	        event.preventDefault();
	        event.stopPropagation();
	      };
	      return main_core.Tag.render(_t2$3 || (_t2$3 = _$5`
				<div class="ui-sign-up-content" ontouchmove="${0}">
					<div class="ui-sign-up-touch-form-label">
						${0}
					</div>
					<div class="ui-sign-up-content-touch-preview">
						${0}
						${0}
					</div>
				</div>
			`), onTouchMove, (() => {
	        if (this.getOptions().mode === 'mobile') {
	          return main_core.Loc.getMessage('UI_SIGN_UP_TOUCH_LAYOUT_MOBILE_LABEL');
	        }
	        return main_core.Loc.getMessage('UI_SIGN_UP_TOUCH_LAYOUT_LABEL');
	      })(), this.getClearButton(), this.getCanvas().getLayout());
	    });
	  }
	}
	TouchContent.LineWidth = 3;

	let _$6 = t => t,
	  _t$6,
	  _t2$4,
	  _t3$2,
	  _t4,
	  _t5;
	class PhotoContent extends Content {
	  constructor(options) {
	    super(options);
	    this.setEventNamespace('BX.UI.SignUp.Content.PhotoContent');
	    this.subscribeFromOptions(options == null ? void 0 : options.events);
	  }
	  getTakePhotoButton() {
	    return this.cache.remember('takePhotoButton', () => {
	      return new ui_buttons.Button({
	        text: main_core.Loc.getMessage('UI_SIGN_UP_TAKE_SIGN_PHOTO'),
	        color: ui_buttons.ButtonColor.LIGHT_BORDER,
	        round: true,
	        noCaps: true,
	        className: 'ui-sign-up-special-mobile-btn'
	      });
	    });
	  }
	  getUploadPhoto() {
	    return this.cache.remember('uploadPhoto', () => {
	      return new ui_buttons.Button({
	        text: main_core.Loc.getMessage('UI_SIGN_UP_UPLOAD_SIGN_PHOTO'),
	        color: ui_buttons.ButtonColor.LIGHT_BORDER,
	        round: true,
	        noCaps: true,
	        className: 'ui-sign-up-special-mobile-btn',
	        onclick: this.onUploadPhotoClick.bind(this)
	      });
	    });
	  }
	  getFileInput() {
	    return this.cache.remember('fileInput', () => {
	      return main_core.Tag.render(_t$6 || (_t$6 = _$6`
				<input hidden type="file" onchange="${0}" accept="image/*">
			`), this.onFileChange.bind(this));
	    });
	  }
	  onUploadPhotoClick() {
	    this.getFileInput().click();
	  }
	  onFileChange(event) {
	    const [file] = event.target.files;
	    if (main_core.Type.isFile(file)) {
	      if (!main_core.Type.isStringFilled(file.type) || !file.type.startsWith('image')) {
	        ui_dialogs_messagebox.MessageBox.alert(main_core.Loc.getMessage('UI_SIGN_UP_BAD_IMAGE_FORMAT_ALERT_MESSAGE'));
	        return false;
	      }
	      main_core.Dom.replace(this.getButtonsLayout(), this.getPreviewLayout());
	      this.getCanvas().renderImage(file).then(() => {
	        this.emit('onChange');
	      });
	    }
	  }
	  getButtonsLayout() {
	    return this.cache.remember('buttonsLayout', () => {
	      // const takePhotoLayout = Tag.render`
	      // 	<div class="ui-sign-up-content-photo-button-wrapper">
	      // 		${this.getOptions().mode !== 'desktop' ? this.getTakePhotoButton().render() : ''}
	      // 	</div>
	      // `;
	      return main_core.Tag.render(_t2$4 || (_t2$4 = _$6`
				<div class="ui-sign-up-content-photo-buttons">
					<div class="ui-sign-up-content-photo-button-wrapper">
						${0}
					</div>
				</div>
			`), this.getUploadPhoto().render());
	    });
	  }
	  getCanvas() {
	    return this.cache.remember('canvas', () => {
	      return new CanvasWrapper({});
	    });
	  }
	  getMoreButton() {
	    return this.cache.remember('moreButton', () => {
	      return main_core.Tag.render(_t3$2 || (_t3$2 = _$6`
				<div 
					class="ui-sign-up-content-photo-more-button"
					onclick="${0}"
				></div>
			`), this.onMoreButtonClick.bind(this));
	    });
	  }
	  onMoreButtonClick(event) {
	    event.preventDefault();
	    this.getMoreMenu().show();
	  }
	  getMoreMenu() {
	    return this.cache.remember('moreMenu', () => {
	      return main_popup.PopupMenu.create({
	        id: 'moreMenu',
	        bindElement: this.getMoreButton(),
	        items: [{
	          id: 'upload',
	          text: main_core.Loc.getMessage('UI_SIGN_UP_UPLOAD_NEW'),
	          onclick: this.onUploadPhotoClick.bind(this)
	        }]
	      });
	    });
	  }
	  getPreviewLayout() {
	    return this.cache.remember('previewLayout', () => {
	      return main_core.Tag.render(_t4 || (_t4 = _$6`
				<div class="ui-sign-up-content-photo-preview">
					${0}
					${0}
				</div>
			`), this.getCanvas().getLayout(), this.getMoreButton());
	    });
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t5 || (_t5 = _$6`
				<div class="ui-sign-up-content">
					${0}
					${0}
				</div>
			`), this.getButtonsLayout(), this.getFileInput());
	    });
	  }
	}

	var InitialsTabIcon = "/bitrix/js/ui/sign-up/dist/images/initials.svg";

	var InitialsActiveTabIcon = "/bitrix/js/ui/sign-up/dist/images/initials-active.svg";

	var TouchTabIcon = "/bitrix/js/ui/sign-up/dist/images/touch.svg";

	var TouchActiveTabIcon = "/bitrix/js/ui/sign-up/dist/images/touch-active.svg";

	var PhotoTabIcon = "/bitrix/js/ui/sign-up/dist/images/photo.svg";

	var PhotoActiveTabIcon = "/bitrix/js/ui/sign-up/dist/images/photo-active.svg";

	let _$7 = t => t,
	  _t$7;

	/**
	 * @memberOf BX.UI
	 */
	class SignUp extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.SignUp');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	    this.onChangeDebounced = main_core.Runtime.debounce(this.onChangeDebounced, 200, this);
	    if (!this.hasValue()) {
	      this.getFooter().getSaveButton().setDisabled(true);
	    }
	  }
	  setOptions(options) {
	    this.cache.set('options', {
	      mode: 'desktop',
	      ...options
	    });
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  getFooter() {
	    return this.cache.remember('footer', () => {
	      return new Footer({
	        mode: this.getOptions().mode,
	        events: {
	          onSaveClickAsync: () => {
	            return this.emitAsync('onSaveClickAsync');
	          },
	          onSaveClick: () => {
	            this.emit('onSaveClick');
	          },
	          onCancelClick: () => {
	            this.emit('onCancelClick');
	          }
	        }
	      });
	    });
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t$7 || (_t$7 = _$7`
				<div class="ui-sign-up">
					${0}
					${0}
				</div>
			`), this.getTabs().getLayout(), this.getFooter().getLayout());
	    });
	  }
	  renderTo(target) {
	    if (!main_core.Type.isDomNode(target)) {
	      throw new TypeError('Target is not a HTMLElement');
	    }
	    main_core.Dom.append(this.getLayout(), target);
	  }
	  getInitialsContent() {
	    return this.cache.remember('initialsContent', () => {
	      return new InitialsContent({
	        events: {
	          onChange: this.onChangeDebounced
	        },
	        color: this.getOptions().signColor
	      });
	    });
	  }
	  getTouchContent() {
	    return this.cache.remember('touchContent', () => {
	      return new TouchContent({
	        mode: this.getOptions().mode,
	        events: {
	          onChange: this.onChangeDebounced
	        },
	        color: this.getOptions().signColor
	      });
	    });
	  }
	  getPhotoContent() {
	    return this.cache.remember('photoContent', () => {
	      return new PhotoContent({
	        mode: this.getOptions().mode,
	        events: {
	          onChange: this.onChangeDebounced
	        }
	      });
	    });
	  }
	  getTabs() {
	    return this.cache.remember('tabs', () => {
	      return new Tabs({
	        defaultState: this.getOptions().defaultState,
	        tabs: [{
	          id: 'initials',
	          header: main_core.Loc.getMessage('UI_SIGN_UP_TAB_INITIALS_TITLE'),
	          icon: InitialsTabIcon,
	          activeIcon: InitialsActiveTabIcon,
	          content: this.getInitialsContent()
	        }, {
	          id: 'touch',
	          header: main_core.Loc.getMessage('UI_SIGN_UP_TAB_TOUCH_TITLE'),
	          icon: TouchTabIcon,
	          activeIcon: TouchActiveTabIcon,
	          content: this.getTouchContent()
	        }, {
	          id: 'photo',
	          header: main_core.Loc.getMessage('UI_SIGN_UP_TAB_PHOTO_TITLE'),
	          icon: PhotoTabIcon,
	          activeIcon: PhotoActiveTabIcon,
	          content: this.getPhotoContent()
	        }]
	      });
	    });
	  }
	  getCanvas() {
	    return this.getTabs().getCurrentTab().getContent().getCanvas().getLayout();
	  }
	  onChangeDebounced() {
	    this.getFooter().getSaveButton().setDisabled(!this.hasValue());
	  }
	  hasValue() {
	    const canvas = this.getCanvas();
	    const context = canvas.getContext('2d');
	    const pixelBuffer = new Uint32Array(context.getImageData(0, 0, canvas.width, canvas.height).data.buffer);
	    let pixelsCount = 0;
	    return pixelBuffer.some(color => {
	      return color !== 0 && pixelsCount++ > SignUp.MIN_PIXELS_REQUIRED;
	    });
	  }
	  async getValue() {
	    const canvas = this.getTabs().getCurrentTab().getContent().getCanvas().getLayout();
	    return await new Promise(resolve => {
	      canvas.toBlob(resolve, 'image/png');
	    });
	  }
	}
	SignUp.MIN_PIXELS_REQUIRED = 100;

	exports.SignUp = SignUp;

}((this.BX.UI = this.BX.UI || {}),BX.Event,BX,BX,BX,BX.UI,BX.Main,BX.UI.Dialogs));
//# sourceMappingURL=sign-up.bundle.js.map
