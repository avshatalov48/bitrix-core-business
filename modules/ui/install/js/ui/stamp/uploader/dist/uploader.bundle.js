this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_uploader_core,ui_dialogs_messagebox,ui_sidepanel_layout,main_loader,ui_draganddrop_draggable,main_core,main_core_events,ui_buttons) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	class Header {
	  constructor(options) {
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setOptions(options);
	  }
	  setOptions(options) {
	    this.cache.set('options', {
	      ...options
	    });
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  setValue(value) {
	    if (main_core.Type.isString(value) || main_core.Type.isNumber(value)) {
	      this.getValueLayout().textContent = value;
	    }
	  }
	  getValueLayout() {
	    return this.cache.remember('valueLayout', () => {
	      return main_core.Tag.render(_t || (_t = _`
				<div class="ui-stamp-uploader-header-text-value">
					<span title="${0}">${0}</span>
				</div>
			`), main_core.Text.encode(this.getOptions().contact.label), main_core.Text.encode(this.getOptions().contact.label));
	    });
	  }
	  getChangeContactButton() {
	    return this.cache.remember('changeContactButton', () => {
	      return new ui_buttons.Button({
	        text: main_core.Loc.getMessage('UI_STAMP_UPLOADER_HEADER_CHANGE_CONTACT_BUTTON_LABEL'),
	        size: ui_buttons.Button.Size.EXTRA_SMALL,
	        color: ui_buttons.Button.Color.LIGHT_BORDER,
	        round: true
	      });
	    });
	  }
	  getLayout() {
	    return this.cache.remember('headerLayout', () => {
	      return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-stamp-uploader-header">
					<div class="ui-stamp-uploader-header-icon">
						<div class="ui-stamp-uploader-header-icon-image"></div>
					</div>
					<div class="ui-stamp-uploader-header-text">
						<div class="ui-stamp-uploader-header-text-label">
							${0}
						</div>
						${0}
					</div>
					<div class="ui-stamp-uploader-header-action">
						
					</div>
				</div>
			`), main_core.Loc.getMessage('UI_STAMP_UPLOADER_HEADER_TITLE'), this.getValueLayout());
	    });
	  }
	  appendTo(target) {
	    if (main_core.Type.isDomNode(target)) {
	      main_core.Dom.append(this.getLayout(), target);
	    }
	  }
	  prependTo(target) {
	    if (main_core.Type.isDomNode(target)) {
	      main_core.Dom.prepend(this.getLayout(), target);
	    }
	  }
	  renderTo(target) {
	    this.appendTo(target);
	  }
	}

	let _$1 = t => t,
	  _t$1;
	class UploadLayout {
	  constructor(options) {
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setOptions(options);
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
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="ui-stamp-uploader-upload-layout">
					${0}
				</div>
			`), this.getOptions().children.map(item => item.getLayout()));
	    });
	  }
	}

	let _$2 = t => t,
	  _t$2;
	class Dropzone extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.Stamp.Uploader.Dropzone');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
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
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t$2 || (_t$2 = _$2`
				<div class="ui-stamp-uploader-dropzone">
					<div class="ui-stamp-uploader-dropzone-icon"></div>
					<div class="ui-stamp-uploader-dropzone-header">
						${0}
					</div>
					<div class="ui-stamp-uploader-dropzone-text">
						${0}
					</div>
				</div>
			`), main_core.Loc.getMessage('UI_STAMP_UPLOADER_DROPZONE_HEADER'), main_core.Loc.getMessage('UI_STAMP_UPLOADER_DROPZONE_TEXT'));
	    });
	  }
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$1,
	  _t3;
	class ActionPanel extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.Stamp.Uploader.ActionPanel');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	  }
	  setOptions(options) {
	    this.cache.set('options', {
	      ...options
	    });
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  getCropButton() {
	    return this.cache.remember('cropButton', () => {
	      const onClick = event => {
	        event.preventDefault();
	        this.emit('onCropClick');
	      };
	      return main_core.Tag.render(_t$3 || (_t$3 = _$3`
				<div 
					class="ui-stamp-uploader-crop-button"
					onclick="${0}"
				>
					${0}
				</div>
			`), onClick, main_core.Loc.getMessage('UI_STAMP_UPLOADER_CROP_BUTTON_LABEL'));
	    });
	  }
	  getApplyButton() {
	    return this.cache.remember('applyButton', () => {
	      return new ui_buttons.ApplyButton({
	        color: ui_buttons.Button.Color.PRIMARY,
	        size: ui_buttons.Button.Size.EXTRA_SMALL,
	        round: true,
	        onclick: () => {
	          this.emit('onApplyClick');
	        }
	      });
	    });
	  }
	  getCancelButton() {
	    return this.cache.remember('cancelButton', () => {
	      return new ui_buttons.CancelButton({
	        color: ui_buttons.Button.Color.LIGHT_BORDER,
	        size: ui_buttons.Button.Size.EXTRA_SMALL,
	        round: true,
	        onclick: () => {
	          this.emit('onCancelClick');
	        }
	      });
	    });
	  }
	  getCropActionsLayout() {
	    return this.cache.remember('cropActionsLayout', () => {
	      return main_core.Tag.render(_t2$1 || (_t2$1 = _$3`
				<div class="ui-stamp-uploader-action-crop-actions" hidden>
					${0}
					${0}
				</div>
			`), this.getApplyButton().render(), this.getCancelButton().render());
	    });
	  }
	  showCropAction() {
	    main_core.Dom.show(this.getCropActionsLayout());
	    main_core.Dom.hide(this.getCropButton());
	  }
	  hideCropActions() {
	    main_core.Dom.hide(this.getCropActionsLayout());
	    main_core.Dom.show(this.getCropButton());
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t3 || (_t3 = _$3`
				<div class="ui-stamp-uploader-action-panel">
					${0}
					${0}
				</div>
			`), this.getCropActionsLayout(), this.getCropButton());
	    });
	  }
	  disable() {
	    main_core.Dom.addClass(this.getLayout(), 'ui-stamp-uploader-action-panel-disabled');
	  }
	  enable() {
	    main_core.Dom.removeClass(this.getLayout(), 'ui-stamp-uploader-action-panel-disabled');
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$2,
	  _t3$1,
	  _t4;
	class Status {
	  constructor() {
	    this.cache = new main_core.Cache.MemoryCache();
	  }
	  static formatSize(bytes) {
	    if (bytes === 0) {
	      return `0 ${main_core.Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE_B')}`;
	    }
	    const sizes = [main_core.Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE_B'), main_core.Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE_KB'), main_core.Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE_MB')];
	    const textIndex = Math.floor(Math.log(bytes) / Math.log(1024));
	    return {
	      number: parseFloat((bytes / Math.pow(1024, textIndex)).toFixed(2)),
	      text: sizes[textIndex]
	    };
	  }
	  getUploadStatusLayout() {
	    return this.cache.remember('statusLayout', () => {
	      const loaderLayout = main_core.Tag.render(_t$4 || (_t$4 = _$4`
				<div class="ui-stamp-uploader-upload-status-loader"></div>
			`));
	      const loader = new main_loader.Loader({
	        target: loaderLayout,
	        mode: 'inline',
	        size: 45
	      });
	      void loader.show();
	      return main_core.Tag.render(_t2$2 || (_t2$2 = _$4`
				<div class="ui-stamp-uploader-upload-status">
					${0}
					<div class="ui-stamp-uploader-upload-status-text">
						${0}
					</div>
					<div class="ui-stamp-uploader-upload-status-percent">
						${0}
					</div>
					<div class="ui-stamp-uploader-upload-status-size">
						${0}
					</div>
				</div>
			`), loaderLayout, main_core.Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_TEXT'), main_core.Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_PERCENT'), main_core.Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE'));
	    });
	  }
	  updateUploadStatus(options = {
	    percent: 0,
	    size: 0
	  }) {
	    const percentNode = this.cache.remember('percentNode', () => {
	      return this.getUploadStatusLayout().querySelector('.ui-stamp-uploader-upload-status-percent');
	    });
	    const sizeNode = this.cache.remember('sizeNode', () => {
	      return this.getUploadStatusLayout().querySelector('.ui-stamp-uploader-upload-status-size');
	    });
	    percentNode.innerHTML = main_core.Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_PERCENT').replace('{{number}}', `<strong>${main_core.Text.encode(options.percent)}</strong>`);
	    const formatted = Status.formatSize(options.size);
	    sizeNode.textContent = main_core.Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE').replace('{{number}}', formatted.number).replace('{{text}}', formatted.text);
	  }
	  getPreparingStatusLayout() {
	    return this.cache.remember('preparingStatusLayout', () => {
	      return main_core.Tag.render(_t3$1 || (_t3$1 = _$4`
				<div class="ui-stamp-uploader-preparing-status">
					<div class="ui-stamp-uploader-preparing-status-icon"></div>
					<div class="ui-stamp-uploader-preparing-status-text">
						${0}		
					</div>
				</div>
			`), main_core.Loc.getMessage('UI_STAMP_UPLOADER_PREPARING_STATUS'));
	    });
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t4 || (_t4 = _$4`
				<div class="ui-stamp-uploader-status"></div>
			`));
	    });
	  }
	  showUploadStatus(options = {
	    reset: false
	  }) {
	    const layout = this.getLayout();
	    const uploadStatusLayout = this.getUploadStatusLayout();
	    const preparingStatusLayout = this.getPreparingStatusLayout();
	    main_core.Dom.remove(preparingStatusLayout);
	    main_core.Dom.append(uploadStatusLayout, layout);
	    if (options.reset === true) {
	      this.updateUploadStatus({
	        percent: 0,
	        size: 0
	      });
	    }
	    this.setOpacity(1);
	    this.show();
	  }
	  showPreparingStatus() {
	    const layout = this.getLayout();
	    const uploadStatusLayout = this.getUploadStatusLayout();
	    const preparingStatusLayout = this.getPreparingStatusLayout();
	    main_core.Dom.remove(uploadStatusLayout);
	    main_core.Dom.append(preparingStatusLayout, layout);
	    this.setOpacity(.45);
	    this.show();
	  }
	  setOpacity(value) {
	    main_core.Dom.style(this.getLayout(), 'background-color', `rgba(255, 255, 255, ${value})`);
	  }
	  hide() {
	    main_core.Dom.removeClass(this.getLayout(), 'ui-stamp-uploader-status-show');
	  }
	  show() {
	    main_core.Dom.addClass(this.getLayout(), 'ui-stamp-uploader-status-show');
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$3,
	  _t3$2,
	  _t4$1;
	var _loadImage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadImage");
	var _setIsCropEnabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setIsCropEnabled");
	class Preview extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    Object.defineProperty(this, _setIsCropEnabled, {
	      value: _setIsCropEnabled2
	    });
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.Stamp.Uploader');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	    const draggable = this.cache.remember('draggable', () => {
	      return new ui_draganddrop_draggable.Draggable({
	        container: this.getLayout(),
	        draggable: '.ui-stamp-uploader-preview-crop > div',
	        type: ui_draganddrop_draggable.Draggable.HEADLESS,
	        context: window.top
	      });
	    });
	    draggable.subscribe('start', this.onDragStart.bind(this));
	    draggable.subscribe('move', this.onDragMove.bind(this));
	    draggable.subscribe('end', this.onDragEnd.bind(this));
	  }
	  setOptions(options) {
	    this.cache.set('options', {
	      ...options
	    });
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  getDraggable() {
	    return this.cache.get('draggable');
	  }
	  getDevicePixelRatio() {
	    return window.devicePixelRatio;
	  }
	  getCanvas() {
	    const canvas = this.cache.remember('canvas', () => {
	      return main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<canvas class="ui-stamp-uploader-preview-canvas"></canvas>
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
	            const ratio = this.getDevicePixelRatio();
	            canvas.width = parentRect.width * ratio;
	            canvas.height = parentRect.height * ratio;
	            main_core.Dom.style(canvas, {
	              width: `${parentRect.width}px`,
	              height: `${parentRect.height}px`
	            });
	            const context2d = canvas.getContext('2d');
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
	  getImagePreviewLayout() {
	    return this.cache.remember('imagePreviewLayout', () => {
	      return main_core.Tag.render(_t2$3 || (_t2$3 = _$5`
				<div class="ui-stamp-uploader-preview-image">
					${0}
				</div>
			`), this.getCanvas());
	    });
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t3$2 || (_t3$2 = _$5`
				<div 
					class="ui-stamp-uploader-preview" 
					title="${0}"
				>
					${0}
					${0}
				</div>
			`), main_core.Loc.getMessage('UI_STAMP_UPLOADER_PREVIEW_TITLE'), this.getImagePreviewLayout(), this.getCropControl());
	    });
	  }
	  clear() {
	    const canvas = this.getCanvas();
	    const context = canvas.getContext('2d');
	    context.clearRect(0, 0, canvas.width, canvas.height);
	  }
	  setSourceImage(image) {
	    this.cache.set('sourceImage', image);
	  }
	  getSourceImage() {
	    return this.cache.get('sourceImage', null);
	  }
	  setSourceImageRect(rect) {
	    this.cache.set('sourceImageRect', rect);
	  }
	  getSourceImageRect() {
	    return this.cache.get('sourceImageRect', {});
	  }
	  setCurrentDrawOptions(drawOptions) {
	    this.cache.set('currentDrawOptions', drawOptions);
	  }
	  getCurrentDrawOptions() {
	    return this.cache.get('currentDrawOptions', {});
	  }
	  applyCrop() {
	    const cropRect = this.getCropRect();
	    const drawOptions = this.getCurrentDrawOptions();
	    const sourceImageRect = this.getSourceImageRect();
	    const imageScaleRatio = sourceImageRect.width / drawOptions.dWidth;
	    const canvas = this.getCanvas();
	    const cropOptions = {
	      sX: (cropRect.left - drawOptions.dX) * imageScaleRatio,
	      sY: (cropRect.top - drawOptions.dY) * imageScaleRatio,
	      sWidth: cropRect.width * imageScaleRatio,
	      sHeight: cropRect.height * imageScaleRatio,
	      dWidth: cropRect.width,
	      dHeight: cropRect.height,
	      dX: (canvas.clientWidth - cropRect.width) / 2,
	      dY: (canvas.clientHeight - cropRect.height) / 2
	    };
	    return this.renderImage(this.getSourceImage(), cropOptions);
	  }
	  renderImage(file, drawOptions = {}) {
	    const canvas = this.getCanvas();
	    const context2d = canvas.getContext('2d');
	    return babelHelpers.classPrivateFieldLooseBase(Preview, _loadImage)[_loadImage](file).then(sourceImage => {
	      const sourceImageRect = {
	        width: sourceImage.width,
	        height: sourceImage.height
	      };
	      const scaleRatio = Math.min(canvas.clientWidth / sourceImageRect.width, canvas.clientHeight / sourceImageRect.height);
	      const preparedDrawOptions = {
	        sX: 0,
	        sY: 0,
	        sWidth: sourceImageRect.width,
	        sHeight: sourceImageRect.height,
	        dX: (canvas.clientWidth - sourceImageRect.width * scaleRatio) / 2,
	        dY: (canvas.clientHeight - sourceImageRect.height * scaleRatio) / 2,
	        dWidth: sourceImageRect.width * scaleRatio,
	        dHeight: sourceImageRect.height * scaleRatio,
	        ...drawOptions
	      };
	      this.setSourceImageRect(sourceImageRect);
	      this.setCurrentDrawOptions(preparedDrawOptions);
	      this.clear();
	      context2d.drawImage(sourceImage, preparedDrawOptions.sX, preparedDrawOptions.sY, preparedDrawOptions.sWidth, preparedDrawOptions.sHeight, preparedDrawOptions.dX, preparedDrawOptions.dY, preparedDrawOptions.dWidth, preparedDrawOptions.dHeight);
	    });
	  }
	  setInitialCropRect(rect) {
	    this.cache.set('initialCropRect', rect);
	  }
	  getInitialCropRect() {
	    return this.cache.get('initialCropRect');
	  }
	  getCropControl() {
	    return this.cache.remember('cropControl', () => {
	      return main_core.Tag.render(_t4$1 || (_t4$1 = _$5`
				<div class="ui-stamp-uploader-preview-crop">
					<div class="ui-stamp-uploader-preview-crop-top"></div>
					<div class="ui-stamp-uploader-preview-crop-right"></div>
					<div class="ui-stamp-uploader-preview-crop-bottom"></div>
					<div class="ui-stamp-uploader-preview-crop-left"></div>
					<div class="ui-stamp-uploader-preview-crop-rotate"></div>
				</div>
			`));
	    });
	  }
	  isCropEnabled() {
	    return this.cache.get('isCropEnabled', false);
	  }
	  enableCrop() {
	    this.renderImage(this.getSourceImage()).then(() => {
	      const control = this.getCropControl();
	      const drawOptions = this.getCurrentDrawOptions();
	      main_core.Dom.style(control, {
	        top: `${drawOptions.dY}px`,
	        bottom: `${drawOptions.dY}px`,
	        left: `${drawOptions.dX}px`,
	        right: `${drawOptions.dX}px`
	      });
	      main_core.Dom.addClass(control, 'ui-stamp-uploader-preview-crop-show');
	      babelHelpers.classPrivateFieldLooseBase(this, _setIsCropEnabled)[_setIsCropEnabled](true);
	    });
	  }
	  disableCrop() {
	    main_core.Dom.removeClass(this.getCropControl(), 'ui-stamp-uploader-preview-crop-show');
	    babelHelpers.classPrivateFieldLooseBase(this, _setIsCropEnabled)[_setIsCropEnabled](false);
	  }
	  onDragStart() {
	    const cropControl = this.getCropControl();
	    this.setInitialCropRect({
	      top: main_core.Text.toNumber(main_core.Dom.style(cropControl, 'top')),
	      left: main_core.Text.toNumber(main_core.Dom.style(cropControl, 'left')),
	      right: main_core.Text.toNumber(main_core.Dom.style(cropControl, 'right')),
	      bottom: main_core.Text.toNumber(main_core.Dom.style(cropControl, 'bottom'))
	    });
	  }
	  onDragMove(event) {
	    const data = event.getData();
	    const initialRect = this.getInitialCropRect();
	    const drawOptions = this.getCurrentDrawOptions();
	    const requiredOffset = 20;
	    const canvasWidth = drawOptions.dX + drawOptions.dWidth + drawOptions.dX;
	    const canvasHeight = drawOptions.dY + drawOptions.dHeight + drawOptions.dY;
	    if (data.source.matches('.ui-stamp-uploader-preview-crop-right')) {
	      const position = Math.max(Math.min(initialRect.right - data.offsetX, canvasWidth - initialRect.left - requiredOffset), drawOptions.dX);
	      main_core.Dom.style(this.getCropControl(), 'right', `${position}px`);
	    }
	    if (data.source.matches('.ui-stamp-uploader-preview-crop-left')) {
	      const position = Math.max(Math.min(initialRect.left + data.offsetX, canvasWidth - initialRect.right - requiredOffset), drawOptions.dX);
	      main_core.Dom.style(this.getCropControl(), 'left', `${position}px`);
	    }
	    if (data.source.matches('.ui-stamp-uploader-preview-crop-top')) {
	      const position = Math.max(drawOptions.dY, Math.min(initialRect.top + data.offsetY, canvasHeight - initialRect.bottom - requiredOffset));
	      main_core.Dom.style(this.getCropControl(), 'top', `${position}px`);
	    }
	    if (data.source.matches('.ui-stamp-uploader-preview-crop-bottom')) {
	      const position = Math.max(Math.min(canvasHeight - initialRect.top - requiredOffset, initialRect.bottom - data.offsetY), drawOptions.dY);
	      main_core.Dom.style(this.getCropControl(), 'bottom', `${position}px`);
	    }
	  }
	  getCropRect() {
	    const cropControl = this.getCropControl();
	    const width = cropControl.clientWidth;
	    const height = cropControl.clientHeight;
	    const left = Math.round(main_core.Text.toNumber(main_core.Dom.style(cropControl, 'left')));
	    const top = Math.round(main_core.Text.toNumber(main_core.Dom.style(cropControl, 'top')));
	    const canvas = this.getCanvas();
	    const canvasRect = canvas.getBoundingClientRect();
	    const right = canvasRect.width - (left + width);
	    const bottom = canvasRect.height - (top + height);
	    return {
	      width,
	      height,
	      top,
	      left,
	      right,
	      bottom
	    };
	  }
	  async getValue() {
	    const canvas = this.getCanvas();
	    return await new Promise(resolve => {
	      canvas.toBlob(resolve, 'image/png');
	    });
	  }
	  onDragEnd(event) {}
	  show(file) {
	    this.setSourceImage(file);
	    void this.renderImage(file);
	    main_core.Dom.addClass(this.getLayout(), 'ui-stamp-uploader-preview-show');
	  }
	  hide() {
	    main_core.Dom.removeClass(this.getLayout(), 'ui-stamp-uploader-preview-show');
	  }
	  getFile() {
	    const drawOptions = this.getCurrentDrawOptions();
	    const canvas = document.createElement('canvas');
	    const context2d = canvas.getContext('2d');
	    return new Promise(resolve => {
	      this.getCanvas().toBlob(blob => {
	        void babelHelpers.classPrivateFieldLooseBase(Preview, _loadImage)[_loadImage](blob).then(image => {
	          const ratio = this.getDevicePixelRatio();
	          canvas.width = drawOptions.dWidth * ratio;
	          canvas.height = drawOptions.dHeight * ratio;
	          context2d.drawImage(image, 0, 0, image.width, image.height, -((image.width - canvas.width) / 2), -((image.height - canvas.height) / 2), image.width, image.height);
	          canvas.toBlob(resultBlob => {
	            resolve(resultBlob);
	          });
	        });
	      });
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
	function _setIsCropEnabled2(value) {
	  this.cache.set('isCropEnabled', value);
	}
	Object.defineProperty(Preview, _loadImage, {
	  value: _loadImage2
	});

	let _$6 = t => t,
	  _t$6;
	class Message {
	  constructor() {
	    this.cache = new main_core.Cache.MemoryCache();
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t$6 || (_t$6 = _$6`
				<div class="ui-stamp-uploader-message">
					<div class="ui-stamp-uploader-message-icon"></div>
					<div class="ui-stamp-uploader-message-text">
						<div class="ui-stamp-uploader-message-text-header">
							${0}
						</div>
						<div class="ui-stamp-uploader-message-text-description">
							${0}
						</div>
					</div>
				</div>
			`), main_core.Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_MESSAGE_TITLE'), main_core.Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_MESSAGE_DESCRIPTION'));
	    });
	  }
	}

	let _$7 = t => t,
	  _t$7;
	class FileSelect extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.Stamp.Uploader.FileSelect');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	  }
	  setOptions(options) {
	    this.cache.set('options', {
	      ...options
	    });
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  getTakePhotoButton() {
	    return this.cache.remember('takePhotoButton', () => {
	      return new ui_buttons.Button({
	        text: main_core.Loc.getMessage('UI_STAMP_UPLOADER_TAKE_PHOTO_BUTTON_LABEL'),
	        color: ui_buttons.Button.Color.LIGHT_BORDER,
	        size: ui_buttons.Button.Size.LARGE,
	        icon: ui_buttons.Button.Icon.CAMERA,
	        round: true,
	        onclick: () => {
	          this.emit('onTakePhotoClick');
	        }
	      });
	    });
	  }
	  getSelectPhotoButton() {
	    return this.cache.remember('selectPhotoButton', () => {
	      return new ui_buttons.Button({
	        text: main_core.Loc.getMessage('UI_STAMP_UPLOADER_SELECT_PHOTO_BUTTON_LABEL_1'),
	        color: ui_buttons.Button.Color.LIGHT_BORDER,
	        size: ui_buttons.Button.Size.LARGE,
	        icon: ui_buttons.Button.Icon.DOWNLOAD,
	        round: true,
	        onclick: () => {
	          this.emit('onTakePhotoClick');
	        }
	      });
	    });
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t$7 || (_t$7 = _$7`
				<div class="ui-stamp-uploader-file-select">
					<div class="ui-stamp-uploader-file-select-select-photo">
						${0}
					</div>
				</div>
			`), this.getSelectPhotoButton().render());
	    });
	  }
	}

	let _$8 = t => t,
	  _t$8,
	  _t2$4,
	  _t3$3;
	var _delay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("delay");
	var _setPreventConfirmShow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setPreventConfirmShow");
	var _isConfirmShowPrevented = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isConfirmShowPrevented");
	/**
	 * @namespace BX.UI.Stamp
	 */
	class Uploader extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _isConfirmShowPrevented, {
	      value: _isConfirmShowPrevented2
	    });
	    Object.defineProperty(this, _setPreventConfirmShow, {
	      value: _setPreventConfirmShow2
	    });
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.Stamp.Uploader');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	    this.cache.remember('fileUploader', () => {
	      var _this$getOptions$cont;
	      const dropzoneLayout = this.getDropzone().getLayout();
	      const previewLayout = this.getPreview().getLayout();
	      const fileSelectButtonLayout = this.getFileSelect().getLayout();
	      main_core.Event.bind(previewLayout, 'click', event => {
	        if (this.getPreview().isCropEnabled()) {
	          event.stopImmediatePropagation();
	        }
	      });
	      const acceptedFileTypes = ['image/png', 'image/jpeg'];
	      return new ui_uploader_core.Uploader({
	        controller: (_this$getOptions$cont = this.getOptions().controller) == null ? void 0 : _this$getOptions$cont.upload,
	        assignAsFile: true,
	        browseElement: [dropzoneLayout, previewLayout, fileSelectButtonLayout, this.getHiddenInput()],
	        dropElement: [dropzoneLayout, previewLayout],
	        imagePreviewHeight: 556,
	        imagePreviewWidth: 1000,
	        autoUpload: false,
	        acceptedFileTypes,
	        events: {
	          [ui_uploader_core.UploaderEvent.FILE_ADD]: event => {
	            const {
	              file,
	              error
	            } = event.getData();
	            if (main_core.Type.isNil(error) && ui_uploader_core.Helpers.isValidFileType(file.getBinary(), acceptedFileTypes)) {
	              this.getPreview().show(file.getClientPreview());
	              this.setUploaderFile(file);
	              if (this.getMode() === Uploader.Mode.SLIDER) {
	                this.getSliderButtons().saveButton.setDisabled(false);
	                this.getActionPanel().enable();
	              }
	              if (this.getMode() === Uploader.Mode.INLINE) {
	                this.getInlineSaveButton().setDisabled(false);
	                this.getActionPanel().enable();
	              }
	              this.setIsChanged(true);
	            }
	          },
	          [ui_uploader_core.UploaderEvent.FILE_UPLOAD_PROGRESS]: event => {
	            const {
	              progress,
	              file
	            } = event.getData();
	            this.getStatus().updateUploadStatus({
	              percent: progress,
	              size: file.getSize() / 100 * progress
	            });
	          },
	          [ui_uploader_core.UploaderEvent.FILE_ERROR]: function (event) {
	            const {
	              error
	            } = event.getData();
	            Uploader.showAlert(error.getMessage());
	          }
	        }
	      });
	    });
	  }
	  static showAlert(...args) {
	    const TopMessageBox = main_core.Reflection.getClass('top.BX.UI.Dialogs.MessageBox');
	    if (!main_core.Type.isNil(TopMessageBox)) {
	      TopMessageBox.alert(...args);
	    }
	  }
	  static showConfirm(options) {
	    const TopMessageBox = main_core.Reflection.getClass('top.BX.UI.Dialogs.MessageBox');
	    const TopMessageBoxButtons = main_core.Reflection.getClass('top.BX.UI.Dialogs.MessageBoxButtons');
	    if (!main_core.Type.isNil(TopMessageBox)) {
	      TopMessageBox.show({
	        modal: true,
	        buttons: TopMessageBoxButtons.OK_CANCEL,
	        ...options
	      });
	    }
	  }
	  setIsChanged(value) {
	    this.cache.set('isChanged', value);
	  }
	  isChanged() {
	    return this.cache.get('isChanged', false);
	  }
	  getFileUploader() {
	    return this.cache.get('fileUploader');
	  }
	  setUploaderFile(file) {
	    this.cache.set('uploaderFile', file);
	  }
	  getUploaderFile() {
	    return this.cache.get('uploaderFile', null);
	  }
	  setOptions(options) {
	    this.cache.set('options', {
	      ...options
	    });
	  }
	  getOptions() {
	    return this.cache.get('options', {});
	  }
	  getMode() {
	    const {
	      mode
	    } = this.getOptions();
	    if (Object.values(Uploader.Mode).includes(mode)) {
	      return mode;
	    }
	    return Uploader.Mode.SLIDER;
	  }
	  getHeader() {
	    return this.cache.remember('header', () => {
	      return new Header(this.getOptions());
	    });
	  }
	  getPreview() {
	    return this.cache.remember('preview', () => {
	      return new Preview({});
	    });
	  }
	  getFileSelect() {
	    return this.cache.remember('fileSelect', () => {
	      return new FileSelect({
	        events: {
	          onTakePhotoClick: () => {
	            this.emit('onTakePhotoClick');
	          },
	          onSelectPhotoClick: () => {}
	        }
	      });
	    });
	  }
	  getUploadLayout() {
	    return this.cache.remember('uploadLayout', () => {
	      return new UploadLayout({
	        children: [(() => {
	          if (this.getMode() === Uploader.Mode.INLINE) {
	            return this.getFileSelect();
	          }
	          return this.getDropzone();
	        })(), this.getActionPanel(), this.getStatus(), this.getPreview()]
	      });
	    });
	  }
	  getDropzone() {
	    return this.cache.remember('dropzone', () => {
	      return new Dropzone({});
	    });
	  }
	  getActionPanel() {
	    return this.cache.remember('actionPanel', () => {
	      return new ActionPanel({
	        events: {
	          onCropClick: this.onCropClick.bind(this),
	          onApplyClick: this.onCropApplyClick.bind(this),
	          onCancelClick: this.onCropCancelClick.bind(this)
	        }
	      });
	    });
	  }
	  onCropApplyClick() {
	    this.getPreview().applyCrop();
	    this.getPreview().disableCrop();
	    this.getActionPanel().hideCropActions();
	    this.getInlineSaveButton().setDisabled(false);
	    this.getActionPanel().enable();
	  }
	  onCropCancelClick() {
	    this.getPreview().disableCrop();
	    this.getActionPanel().hideCropActions();
	    this.getInlineSaveButton().setDisabled(false);
	    this.getActionPanel().enable();
	  }
	  onCropClick() {
	    this.getPreview().enableCrop();
	    this.getActionPanel().showCropAction();
	    this.getInlineSaveButton().setDisabled(true);
	    this.getActionPanel().enable();
	  }
	  getStatus() {
	    return this.cache.remember('status', () => {
	      return new Status();
	    });
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      const mode = this.getMode();
	      return main_core.Tag.render(_t$8 || (_t$8 = _$8`
				<div class="ui-stamp-uploader ui-stamp-uploader-mode-${0}">
					${0}
					${0}
					${0}
					${0}
					${0}
				</div>
			`), mode, (() => {
	        if (mode === Uploader.Mode.SLIDER) {
	          return this.getMessage().getLayout();
	        }
	        return '';
	      })(), this.getHeader().getLayout(), this.getUploadLayout().getLayout(), (() => {
	        if (mode === Uploader.Mode.INLINE) {
	          return main_core.Tag.render(_t2$4 || (_t2$4 = _$8`
								<div class="ui-stamp-uploader-footer">
									${0}
								</div>
							`), this.getInlineSaveButton().render());
	        }
	        return '';
	      })(), this.getHiddenInput());
	    });
	  }
	  getHiddenInput() {
	    return this.cache.remember('hiddenInput', () => {
	      return main_core.Tag.render(_t3$3 || (_t3$3 = _$8`
				<input type="file" name="STAMP_UPLOADER_INPUT" hidden>
			`));
	    });
	  }
	  renderTo(target) {
	    if (main_core.Type.isDomNode(target)) {
	      main_core.Dom.append(this.getLayout(), target);
	    }
	  }
	  upload() {
	    return new Promise(resolve => {
	      this.getPreview().getFile().then(blob => {
	        this.getFileUploader().addFile(blob);
	        const [resultFile] = this.getFileUploader().getFiles();
	        resultFile.subscribeOnce(ui_uploader_core.FileEvent.LOAD_COMPLETE, () => {
	          this.getPreview().hide();
	          const {
	            controller
	          } = this.getOptions();
	          if (!controller) {
	            resolve(resultFile);
	            return;
	          }
	          this.getStatus().showUploadStatus({
	            reset: true
	          });
	          resultFile.upload({
	            onComplete: () => {
	              resolve(resultFile);
	            },
	            onError: console.error
	          });
	        });
	      });
	    });
	  }
	  getMessage() {
	    return this.cache.remember('message', () => {
	      return new Message();
	    });
	  }
	  getInlineSaveButton() {
	    return this.cache.remember('inlineSaveButton', () => {
	      const button = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('UI_STAMP_UPLOADER_SAVE_BUTTON_LABEL'),
	        color: ui_buttons.Button.Color.PRIMARY,
	        size: ui_buttons.Button.Size.LARGE,
	        round: true,
	        onclick: () => {
	          const saveButton = this.getInlineSaveButton();
	          saveButton.setWaiting(true);
	          this.upload().then(uploaderFile => {
	            const {
	              controller
	            } = this.getOptions();
	            if (!controller) {
	              return this.emitAsync('onSaveAsync', {
	                file: uploaderFile.toJSON()
	              });
	            }
	            return Promise.all([new Promise(resolve => {
	              babelHelpers.classPrivateFieldLooseBase(Uploader, _delay)[_delay](() => {
	                this.getPreview().show(uploaderFile.getClientPreview());
	                this.getStatus().showPreparingStatus();
	                resolve();
	              }, 1000);
	            }), this.emitAsync('onSaveAsync', {
	              file: uploaderFile.toJSON()
	            })]);
	          }).then(() => {
	            this.getStatus().hide();
	            babelHelpers.classPrivateFieldLooseBase(Uploader, _delay)[_delay](() => {
	              saveButton.setWaiting(false);
	              saveButton.setDisabled(true);
	              this.getActionPanel().disable();
	            }, 500);
	          });
	        }
	      });
	      button.setDisabled(true);
	      this.getActionPanel().disable();
	      return button;
	    });
	  }
	  setSliderButtons(buttons) {
	    this.cache.set('sliderButtons', buttons);
	  }
	  getSliderButtons() {
	    return this.cache.get('sliderButtons', {
	      saveButton: null,
	      cancelButton: null
	    });
	  }
	  show() {
	    const SidePanelInstance = main_core.Reflection.getClass('BX.SidePanel.Instance');
	    if (main_core.Type.isNil(SidePanelInstance)) {
	      return;
	    }
	    this.getPreview().hide();
	    this.getStatus().hide();
	    this.getActionPanel().disable();
	    SidePanelInstance.open('stampUploader', {
	      width: 640,
	      contentCallback: () => {
	        return ui_sidepanel_layout.Layout.createContent({
	          extensions: ['ui.stamp.uploader'],
	          content: () => {
	            return this.getLayout();
	          },
	          design: {
	            section: false
	          },
	          buttons: ({
	            cancelButton,
	            SaveButton
	          }) => {
	            const saveButton = new SaveButton({
	              onclick: () => {
	                saveButton.setWaiting(true);
	                this.setIsChanged(false);
	                babelHelpers.classPrivateFieldLooseBase(this, _setPreventConfirmShow)[_setPreventConfirmShow](true);
	                this.upload().then(uploaderFile => {
	                  return Promise.all([new Promise(resolve => {
	                    babelHelpers.classPrivateFieldLooseBase(Uploader, _delay)[_delay](() => {
	                      this.getPreview().show(uploaderFile.getClientPreview());
	                      this.getStatus().showPreparingStatus();
	                      resolve();
	                    }, 1000);
	                  }), this.emitAsync('onSaveAsync', {
	                    file: uploaderFile.toJSON()
	                  })]);
	                }).then(() => {
	                  this.getStatus().hide();
	                  babelHelpers.classPrivateFieldLooseBase(Uploader, _delay)[_delay](() => {
	                    saveButton.setWaiting(false);
	                    saveButton.setDisabled(true);
	                    this.getActionPanel().disable();
	                    const topSlider = BX.SidePanel.Instance.getTopSlider();
	                    if (topSlider && topSlider.url === 'stampUploader') {
	                      topSlider.close();
	                    }
	                  }, 500);
	                });
	              }
	            });
	            saveButton.setDisabled(true);
	            this.getActionPanel().disable();
	            this.setSliderButtons({
	              saveButton,
	              cancelButton
	            });
	            return [saveButton, cancelButton];
	          }
	        });
	      },
	      events: {
	        onClose: event => {
	          if (this.isChanged()) {
	            event.denyAction();
	            if (!babelHelpers.classPrivateFieldLooseBase(this, _isConfirmShowPrevented)[_isConfirmShowPrevented]()) {
	              Uploader.showConfirm({
	                message: main_core.Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_CLOSE_CONFIRM'),
	                onOk: messageBox => {
	                  this.setIsChanged(false);
	                  event.getSlider().close();
	                  messageBox.close();
	                },
	                okCaption: main_core.Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_CLOSE_CONFIRM_CLOSE'),
	                onCancel: messageBox => {
	                  messageBox.close();
	                },
	                cancelCaption: main_core.Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_CLOSE_CONFIRM_CANCEL')
	              });
	            } else {
	              this.setIsChanged(false);
	              event.getSlider().close();
	            }
	          }
	        }
	      }
	    });
	  }
	}
	function _delay2(callback, delay) {
	  const timeoutId = setTimeout(() => {
	    callback();
	    clearTimeout(timeoutId);
	  }, delay);
	}
	function _setPreventConfirmShow2(value) {
	  this.cache.set('preventConfirmShow', value);
	}
	function _isConfirmShowPrevented2() {
	  return this.cache.get('preventConfirmShow', false);
	}
	Object.defineProperty(Uploader, _delay, {
	  value: _delay2
	});
	Uploader.Mode = {
	  SLIDER: 'slider',
	  INLINE: 'inline'
	};

	exports.Uploader = Uploader;

}((this.BX.UI.Stamp = this.BX.UI.Stamp || {}),BX.UI.Uploader,BX.UI.Dialogs,BX.UI.SidePanel,BX,BX.UI.DragAndDrop,BX,BX.Event,BX.UI));
//# sourceMappingURL=uploader.bundle.js.map
