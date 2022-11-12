this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_uploader_core,ui_sidepanel_layout,main_loader,main_core,main_core_events,ui_buttons) {
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
	    this.cache.set('options', { ...options
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
					<span class="ui-link">${0}</span>
				</div>
			`), main_core.Text.encode(this.getOptions().contact.label));
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
	    this.cache.set('options', { ...options
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
	    this.cache.set('options', { ...options
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
	    _t2$1;
	class ActionPanel extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.Stamp.Uploader.ActionPanel');
	    this.setOptions(options);
	  }

	  setOptions(options) {
	    this.cache.set('options', { ...options
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

	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t2$1 || (_t2$1 = _$3`
				<div class="ui-stamp-uploader-action-panel">
					${0}
				</div>
			`), this.getCropButton());
	    });
	  }

	}

	let _$4 = t => t,
	    _t$4,
	    _t2$2,
	    _t3,
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
	      return main_core.Tag.render(_t3 || (_t3 = _$4`
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
	    _t3$1;
	class Preview extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.Stamp.Uploader');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	  }

	  setOptions(options) {
	    this.cache.set('options', { ...options
	    });
	  }

	  getOptions() {
	    return this.cache.get('options', {});
	  }

	  getImagePreviewLayout() {
	    return this.cache.remember('imagePreviewLayout', () => {
	      return main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<div class="ui-stamp-uploader-preview-image"></div>
			`));
	    });
	  }

	  getActionButtonLayout() {
	    return this.cache.remember('actionButtonLayout', () => {
	      return main_core.Tag.render(_t2$3 || (_t2$3 = _$5`
				<div class="ui-stamp-uploader-preview-actions-button"></div>
			`));
	    });
	  }

	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t3$1 || (_t3$1 = _$5`
				<div 
					class="ui-stamp-uploader-preview" 
					title="${0}"
				>
					${0}
					${0}
				</div>
			`), main_core.Loc.getMessage('UI_STAMP_UPLOADER_PREVIEW_TITLE'), this.getImagePreviewLayout(), this.getActionButtonLayout());
	    });
	  }

	  show(src) {
	    main_core.Dom.style(this.getImagePreviewLayout(), {
	      backgroundImage: `url(${src})`
	    });
	    main_core.Dom.addClass(this.getLayout(), 'ui-stamp-uploader-preview-show');
	  }

	  hide() {
	    main_core.Dom.removeClass(this.getLayout(), 'ui-stamp-uploader-preview-show');
	  }

	}

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
	    this.cache.set('options', { ...options
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
	        text: main_core.Loc.getMessage('UI_STAMP_UPLOADER_SELECT_PHOTO_BUTTON_LABEL'),
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
	    _t2$4;

	var _delay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("delay");

	/**
	 * @namespace BX.UI.Stamp
	 */
	class Uploader extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.setEventNamespace('BX.UI.Stamp.Uploader');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	    this.cache.remember('fileUploader', () => {
	      const dropzoneLayout = this.getDropzone().getLayout();
	      const previewLayout = this.getPreview().getLayout();
	      const fileSelectButtonLayout = this.getFileSelect().getLayout();
	      return new ui_uploader_core.Uploader({
	        acceptOnlyImages: true,
	        controller: this.getOptions().controller.upload,
	        browseElement: [dropzoneLayout, previewLayout, fileSelectButtonLayout],
	        dropElement: [dropzoneLayout, previewLayout],
	        imagePreviewHeight: 556,
	        imagePreviewWidth: 1000,
	        autoUpload: false,
	        events: {
	          'File:onAdd': event => {
	            const {
	              file
	            } = event.getData();
	            this.getPreview().show(URL.createObjectURL(file.clientPreview));
	            this.setUploaderFile(file);

	            if (this.getMode() === Uploader.Mode.SLIDER) {
	              this.getSliderButtons().saveButton.setDisabled(false);
	            }

	            if (this.getMode() === Uploader.Mode.INLINE) {
	              this.getInlineSaveButton().setDisabled(false);
	            }
	          },
	          'File:onUploadProgress': event => {
	            const {
	              progress,
	              file
	            } = event.getData();
	            this.getStatus().updateUploadStatus({
	              percent: progress,
	              size: file.getSize() / 100 * progress
	            });
	          }
	        }
	      });
	    });
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
	    this.cache.set('options', { ...options
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
	      return new ActionPanel({});
	    });
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
	      })());
	    });
	  }

	  renderTo(target) {
	    if (main_core.Type.isDomNode(target)) {
	      main_core.Dom.append(this.getLayout(), target);
	    }
	  }

	  upload() {
	    return new Promise(resolve => {
	      const file = this.getUploaderFile();

	      if (file) {
	        this.getPreview().hide();
	        this.getStatus().showUploadStatus({
	          reset: true
	        });
	        file.upload();
	        file.uploadController.subscribeOnce('onUpload', event => {
	          resolve(event.getData().fileInfo);
	        });
	      }
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
	            babelHelpers.classPrivateFieldLooseBase(Uploader, _delay)[_delay](() => {
	              this.getPreview().show(uploaderFile.serverPreviewUrl);
	              this.getStatus().showPreparingStatus();
	            }, 1000);

	            return this.emitAsync('onSaveAsync', {
	              file: uploaderFile
	            });
	          }).then(() => {
	            this.getStatus().hide();

	            babelHelpers.classPrivateFieldLooseBase(Uploader, _delay)[_delay](() => {
	              saveButton.setWaiting(false);
	              saveButton.setDisabled(true);
	            }, 500);
	          });
	        }
	      });
	      button.setDisabled(true);
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
	                this.upload().then(uploaderFile => {
	                  babelHelpers.classPrivateFieldLooseBase(Uploader, _delay)[_delay](() => {
	                    this.getPreview().show(uploaderFile.serverPreviewUrl);
	                    this.getStatus().showPreparingStatus();
	                  }, 1000);

	                  return this.emitAsync('onSaveAsync', {
	                    file: uploaderFile
	                  });
	                }).then(() => {
	                  this.getStatus().hide();

	                  babelHelpers.classPrivateFieldLooseBase(Uploader, _delay)[_delay](() => {
	                    saveButton.setWaiting(false);
	                    saveButton.setDisabled(true);
	                    BX.SidePanel.Instance.close();
	                  }, 500);
	                });
	              }
	            });
	            saveButton.setDisabled(true);
	            this.setSliderButtons({
	              saveButton,
	              cancelButton
	            });
	            return [saveButton, cancelButton];
	          }
	        });
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

	Object.defineProperty(Uploader, _delay, {
	  value: _delay2
	});
	Uploader.Mode = {
	  SLIDER: 'slider',
	  INLINE: 'inline'
	};

	exports.Uploader = Uploader;

}((this.BX.UI.Stamp = this.BX.UI.Stamp || {}),BX.UI.Uploader,BX.UI.SidePanel,BX,BX,BX.Event,BX.UI));
//# sourceMappingURL=uploader.bundle.js.map
