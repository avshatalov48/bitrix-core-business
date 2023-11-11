/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_fonts_opensans,ui_designTokens,ui_notification,ui_entitySelector,ui_dialogs_messagebox,main_loader,main_core,main_core_events,main_popup,ui_buttons,ui_sidepanel_layout) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	var _parent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parent");
	class DefaultTab extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    Object.defineProperty(this, _parent, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Main.Avatar.Editor');
	  }
	  getPriority() {
	    return this.constructor.priority;
	  }
	  setParentTab(tab) {
	    babelHelpers.classPrivateFieldLooseBase(this, _parent)[_parent] = tab;
	  }
	  getHeaderContainer() {
	    return this.cache.remember('headerContainer', () => {
	      const id = this.constructor.code;
	      const title = this.getHeader();
	      if (title === null) {
	        return main_core.Tag.render(_t || (_t = _`<span style="display: none;" data-bx-role="tab-header"  data-bx-state="hidden" data-bx-name="${0}"></span>`), id);
	      }
	      return main_core.Tag.render(_t2 || (_t2 = _`<span class="ui-avatar-editor__tab-button-item" data-bx-role="tab-header" data-bx-state="visible" data-bx-name="${0}">${0}</span>`), id, title);
	    });
	  }
	  getHeader() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _parent)[_parent] !== null) return this.constructor.code.toUpperCase();
	    return null;
	  }
	  getBodyContainer() {
	    return this.cache.remember('bodyContainer', () => {
	      const id = this.constructor.code;
	      return main_core.Tag.render(_t3 || (_t3 = _`
				<div class="ui-avatar-editor__content-block ui-avatar-editor__${0}-block" data-bx-role="tab-body" data-bx-name="${0}">${0}</div>`), id, id, this.getBody());
	    });
	  }
	  getBody() {
	    const id = this.constructor.code;
	    return this.cache.remember('body', () => {
	      return main_core.Tag.render(_t4 || (_t4 = _`
			<div>
				${0}
			</div>`), id.toUpperCase());
	    });
	  }
	  inactivate() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _parent)[_parent]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _parent)[_parent].getHeaderContainer().removeAttribute('data-bx-active');
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _parent)[_parent].getHeaderContainer(), 'ui-avatar-editor__tab-button-active');
	    }
	    this.getHeaderContainer().removeAttribute('data-bx-active');
	    main_core.Dom.removeClass(this.getHeaderContainer(), 'ui-avatar-editor__tab-button-active');
	    this.getBodyContainer().removeAttribute('data-bx-active');
	    this.emit('onInactive');
	    return this;
	  }
	  activate() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _parent)[_parent]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _parent)[_parent].getHeaderContainer().setAttribute('data-bx-active', 'Y');
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _parent)[_parent].getHeaderContainer(), 'ui-avatar-editor__tab-button-active');
	    }
	    this.getHeaderContainer().setAttribute('data-bx-active', 'Y');
	    main_core.Dom.addClass(this.getHeaderContainer(), 'ui-avatar-editor__tab-button-active');
	    this.getBodyContainer().setAttribute('data-bx-active', 'Y');
	    this.emit('onActive');
	    return this;
	  }
	  showError({
	    message,
	    code
	  }) {
	    const errorContainer = this.getBody().querySelector('[data-bx-role="error-container"]');
	    if (errorContainer) {
	      errorContainer.innerText = message || code;
	    }
	    main_core.Dom.addClass(this.getBodyContainer(), 'ui-avatar-editor--error');
	  }
	  static isAvailable() {
	    return true;
	  }
	  static get code() {
	    return 'default';
	  }
	}
	DefaultTab.priority = 1;

	let _$1 = t => t,
	  _t$1;
	var _isCameraEnabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCameraEnabled");
	class CameraTab extends DefaultTab {
	  getHeader() {
	    return null;
	  }
	  getBody() {
	    return this.cache.remember('body', () => {
	      const res = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div>
					<div class="ui-avatar-editor__camera-block-image">
						<div class="ui-avatar-editor__btn-back" data-bx-role="button-back"></div>
						<div class="ui-avatar-editor__user-loader-item">
							<div class="ui-avatar-editor__loader">
								<svg class="ui-avatar-editor__circular" viewBox="25 25 50 50">
									<circle class="ui-avatar-editor__path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
								</svg>
							</div>
						</div>
						<div class="ui-avatar-editor__error">
							<span>
								${0}
							</span>
							<span data-bx-role="tab-camera-error"></span>
						</div>
						<div class="ui-avatar-editor__camera-block-image-inner">
							<video autoplay></video>
						</div>
					</div>
					<div class="ui-avatar-editor__button-layout" data-bx-role="camera-button">
						<div class="ui-avatar-editor__button">
							<span class="ui-avatar-editor__button-icon"></span>
						</div>
					</div>
				</div>
			`), main_core.Loc.getMessage('JS_AVATAR_EDITOR_ERROR'));
	      const video = res.querySelector('VIDEO');
	      video.addEventListener("playing", event => {
	        const visibleWidth = res.clientWidth,
	          visibleHeight = res.clientHeight,
	          w = video.clientWidth,
	          h = video.clientHeight,
	          scale = Math.max(w > 0 ? visibleWidth / w : 1, h > 0 ? visibleHeight / h : 1),
	          left = (w * scale - w) / 2 + (visibleWidth - w * scale) / 2,
	          top = (h * scale - h) / 2 + (visibleHeight - h * scale) / 2;
	        main_core.Dom.adjust(video.parentNode, {
	          style: {
	            width: w + 'px',
	            height: h + 'px',
	            transform: 'translate(' + Math.ceil(left) + 'px, ' + Math.ceil(top) + 'px) scale(' + scale + ', ' + scale + ')'
	          }
	        });
	      });
	      res.querySelector('[data-bx-role="camera-button"]').onclick = () => {
	        this.emit('onSetFile', video);
	      };
	      res.querySelector('[data-bx-role="button-back"]').onclick = () => {
	        this.emit('onClickBack');
	      };
	      return res;
	    });
	  }
	  inactivate() {
	    this.stopStreaming();
	    return super.inactivate();
	  }
	  activate() {
	    this.startStreaming();
	    return super.activate();
	  }
	  startStreaming() {
	    const video = this.getBody().querySelector('VIDEO');
	    video.setAttribute("active", "Y");
	    navigator.mediaDevices.getUserMedia({
	      audio: false,
	      video: {
	        width: {
	          max: 1024,
	          min: 640,
	          ideal: 1024
	        },
	        height: {
	          max: 860,
	          min: 480,
	          ideal: 860
	        }
	      }
	    }).then(function (stream) {
	      if (video.hasAttribute("active")) {
	        video.srcObject = stream;
	      } else {
	        stream.getTracks()[0].stop();
	      }
	    }).catch(error => {
	      this.getBody().querySelector('[data-bx-role="tab-camera-error"]').innerHTML = main_core.Text.encode(error);
	    });
	  }
	  stopStreaming() {
	    const video = this.getBody().querySelector('VIDEO');
	    video.removeAttribute("active");
	    video.pause();
	    video.src = "";
	    if (video.srcObject) {
	      video.srcObject.getTracks()[0].stop();
	    }
	  }
	  static check() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isCameraEnabled)[_isCameraEnabled] === null) {
	      if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
	        navigator.mediaDevices.enumerateDevices().then(devices => {
	          babelHelpers.classPrivateFieldLooseBase(this, _isCameraEnabled)[_isCameraEnabled] = Array.from(devices).filter(function (deviceInfo) {
	            return deviceInfo.kind === 'videoinput';
	          }).length > 0;
	        }).catch(() => {
	          babelHelpers.classPrivateFieldLooseBase(this, _isCameraEnabled)[_isCameraEnabled] = false;
	        });
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _isCameraEnabled)[_isCameraEnabled] = false;
	      }
	    }
	  }
	  static isAvailable() {
	    this.check();
	    return babelHelpers.classPrivateFieldLooseBase(this, _isCameraEnabled)[_isCameraEnabled];
	  }
	  static get code() {
	    return 'camera';
	  }
	}
	CameraTab.priority = 2;
	Object.defineProperty(CameraTab, _isCameraEnabled, {
	  writable: true,
	  value: null
	});

	var _ajaxRepo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ajaxRepo");
	class Backend {
	  static saveMask({
	    id,
	    title,
	    accessCode
	  }, file) {
	    if (main_core.Loc.getMessage('USER_ID') <= 0) {
	      return;
	    }
	    const formObj = new FormData();
	    formObj.append('id', id);
	    formObj.append('title', title);
	    if (accessCode.length > 0) {
	      Array.from(accessCode).forEach((accessCode, index) => {
	        formObj.append('accessCode[' + index + '][0]', accessCode[0]);
	        formObj.append('accessCode[' + index + '][1]', accessCode[1]);
	      });
	    } else {
	      formObj.append('accessCode[]', '');
	    }
	    if (file instanceof Blob) {
	      formObj.append('file[changed]', 'Y');
	      formObj.append('file', file, file['name']);
	    } else {
	      formObj.append('file[changed]', 'N');
	    }
	    return main_core.ajax.runAction('ui.avatar.mask.save', {
	      data: formObj,
	      analyticsLabel: {
	        ui: 'avatarMask',
	        actionType: 'edit',
	        action: 'save'
	      }
	    });
	  }
	  static getMaskList(actionName, {
	    page,
	    size
	  }) {
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.avatar.mask.get' + actionName, {
	        data: {},
	        navigation: {
	          page: page,
	          size: size
	        },
	        analyticsLabel: {
	          ui: 'avatarMask',
	          actionType: 'read',
	          action: 'list'
	        }
	      }).then(({
	        data: {
	          groupedItems
	        }
	      }) => {
	        resolve(groupedItems);
	      }).catch(reject);
	    });
	  }
	  static getMaskInitialInfo({
	    size,
	    recentlyUsedListSize
	  }) {
	    return new Promise((resolve, reject) => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _ajaxRepo)[_ajaxRepo]['getMaskInitialInfo']) {
	        return resolve(babelHelpers.classPrivateFieldLooseBase(this, _ajaxRepo)[_ajaxRepo]['getMaskInitialInfo']);
	      }
	      main_core.ajax.runAction('ui.avatar.mask.getMaskInitialInfo', {
	        data: {
	          recentlyUsedListSize: recentlyUsedListSize
	        },
	        navigation: {
	          page: 1,
	          size: size
	        },
	        analyticsLabel: {
	          ui: 'avatarMask',
	          actionType: 'read',
	          action: 'initialInfo'
	        }
	      }).then(({
	        data: {
	          initialInfo
	        }
	      }) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _ajaxRepo)[_ajaxRepo]['getMaskInitialInfo'] = initialInfo;
	        resolve(initialInfo);
	      }).catch(reject);
	    });
	  }
	  static getMaskAccessCode(itemId) {
	    return main_core.ajax.runAction('ui.avatar.mask.getMaskAccessCode', {
	      data: {
	        id: itemId
	      },
	      analyticsLabel: {
	        ui: 'avatarMask',
	        actionType: 'edit',
	        action: 'accessCode'
	      }
	    });
	  }
	  static deleteMask(itemId) {
	    return main_core.ajax.runAction('ui.avatar.mask.delete', {
	      data: {
	        id: itemId
	      },
	      analyticsLabel: {
	        ui: 'avatarMask',
	        actionType: 'edit',
	        action: 'delete'
	      }
	    });
	  }
	  static useRecently(itemId) {
	    return main_core.ajax.runAction('ui.avatar.mask.useRecently', {
	      data: {
	        id: itemId
	      },
	      analyticsLabel: {
	        ui: 'avatarMask',
	        actionType: 'read',
	        action: 'read'
	      }
	    });
	  }
	  static cleanUp() {
	    return main_core.ajax.runAction('ui.avatar.mask.cleanUp', {
	      analyticsLabel: {
	        ui: 'avatarMask',
	        actionType: 'edit',
	        action: 'cleanUp'
	      }
	    });
	  }
	}
	Object.defineProperty(Backend, _ajaxRepo, {
	  writable: true,
	  value: {}
	});

	let _$2 = t => t,
	  _t$2;
	function isImage(name, type, size) {
	  type = type ? String(type) : null;
	  size = size ? Number(size) : null;
	  name = String(name).toLowerCase();
	  let ext = name.split('.').pop();
	  if (ext === name) {
	    ext = null;
	  }
	  return (type === null || type.indexOf("image/") === 0) && (size === null || size < 20 * 1024 * 1024) && ext !== name && 'jpg,bmp,jpeg,jpe,gif,png,webp'.split(',').indexOf(ext) >= 0;
	}
	var _fileId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fileId");
	var _fileAccept = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fileAccept");
	class UploadTab extends DefaultTab {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _fileId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _fileAccept, {
	      writable: true,
	      value: 'image/*'
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _fileId)[_fileId] = ['fileUpload_', new Date().valueOf()].join('_');
	    if (options && options.fileAccept) {
	      babelHelpers.classPrivateFieldLooseBase(this, _fileAccept)[_fileAccept] = options.fileAccept;
	    }
	  }
	  getHeader() {
	    return null;
	  }
	  getBody() {
	    return this.cache.remember('body', () => {
	      const res = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div>
				<div class="ui-avatar-editor__btn-back" data-bx-role="button-back"></div>
				<div class="ui-avatar-editor__upload-link-container">
					<div data-bx-role="error-container" class="ui-avatar-editor__upload-error-desc"></div>
					<label for="${0}" class="ui-avatar-editor__upload-link">
						${0}
						<input type="file" id="${0}" data-bx-role="file-button" accept="${0}" />
					</label>
					<div class="ui-avatar-editor__upload-desc">
						${0}
					</div>
				</div>
				<div class="ui-avatar-editor__upload-info">
					<div class="ui-avatar-editor__upload-info-item"><!-- place for limit text --></div>
				</div>
			</div>`), babelHelpers.classPrivateFieldLooseBase(this, _fileId)[_fileId], main_core.Loc.getMessage('JS_AVATAR_EDITOR_PICK_UP_THE_FILE'), babelHelpers.classPrivateFieldLooseBase(this, _fileId)[_fileId], main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _fileAccept)[_fileAccept]), main_core.Loc.getMessage('JS_AVATAR_EDITOR_DROP_FILES_INTO_THIS_AREA'));
	      const f = event => {
	        const {
	          target
	        } = event;
	        const fileButton = res.querySelector('[data-bx-role="file-button"]');
	        const file = Array.from(target && target.files ? target.files : fileButton.files).shift();
	        if (isImage(file.name, file.type, file.size)) {
	          this.emit('onSetFile', file);
	        }
	        main_core.Event.unbindAll(fileButton);
	        const node = fileButton.cloneNode(true, {
	          value: ""
	        });
	        main_core.Dom.adjust(node, {
	          props: {
	            value: ""
	          },
	          attrs: {}
	        });
	        node.setAttribute("new", "Y" + new Date().valueOf());
	        fileButton.parentNode.insertBefore(node, fileButton);
	        fileButton.parentNode.removeChild(fileButton);
	        main_core.Event.bind(node, "change", f);
	      };
	      main_core.Event.bind(res.querySelector('[data-bx-role="file-button"]'), 'change', f);
	      const dropZone = new BX.DD.dropFiles(res);
	      if (dropZone && dropZone.supported()) {
	        main_core_events.EventEmitter.subscribe(dropZone, 'dropFiles', (files, e) => {
	          if (e && e["dataTransfer"] && e["dataTransfer"]["items"] && e["dataTransfer"]["items"].length > 0) {
	            const fileCopy = [];
	            Array.from(e["dataTransfer"]["items"]).forEach(item => {
	              if (item["webkitGetAsEntry"] && item["getAsFile"]) {
	                let entry = item["webkitGetAsEntry"]();
	                if (entry && entry.isFile) {
	                  fileCopy.push(item["getAsFile"]());
	                }
	              }
	            });
	            if (fileCopy.length > 0) {
	              files = fileCopy;
	            }
	          }
	          f({
	            target: {
	              files: files
	            }
	          });
	        }, {
	          compatMode: true
	        });
	        main_core_events.EventEmitter.subscribe(dropZone, 'dragEnter', e => {
	          if (e && e["dataTransfer"] && e.dataTransfer.types && e.dataTransfer.items) {
	            const isFileTransfer = Array.from(e.dataTransfer.types).filter(type => {
	              return type === "Files";
	            }).length > 0;
	            if (isFileTransfer) {
	              main_core.Dom.addClass(res.parentNode, 'dnd-over');
	            }
	          }
	        }, {
	          compatMode: true
	        });
	        main_core_events.EventEmitter.subscribe(dropZone, 'dragLeave', () => {
	          main_core.Dom.removeClass(res.parentNode, 'dnd-over');
	        }, {
	          compatMode: true
	        });
	      }
	      res.querySelector('[data-bx-role="button-back"]').onclick = () => {
	        this.emit('onClickBack');
	      };
	      return res;
	    });
	  }
	  deleteError() {
	    this.getBody().querySelector('[data-bx-role="error-container"]').innerText = '';
	    main_core.Dom.removeClass(this.getBodyContainer(), 'ui-avatar-editor--error');
	  }
	  static get code() {
	    return 'upload';
	  }
	}
	UploadTab.priority = 3;

	const Options = {
	  maskSize: 400,
	  imageSize: 1024,
	  rawSrc: document.currentScript.src,
	  rawPath: null,
	  eventNamespace: 'Main.Avatar.Editor',
	  get path() {
	    if (Options.rawPath === null) {
	      const res = Options.rawSrc.split('/');
	      let buf;
	      while (buf = res.pop()) {
	        if (buf === 'dist') {
	          break;
	        }
	      }
	      Options.rawPath = new main_core.Uri(res.join('/')).getPath();
	    }
	    return Options.rawPath;
	  },
	  getCollections: () => {
	    const settings = Extension.getSettings('ui.avatar-editor');
	    return Array.from(settings['commonCollection'])[{
	      title: 'Sys',
	      items: ['001_flower.png', '002_flower.png', '003_christmas_tree256.png', '005_red_rectangle.png', '005_blue_circle.png', '004_bow_purple.png'].map(function (title) {
	        return {
	          id: title,
	          title: title,
	          thumb: [Options.path, 'badges', title].join('/').replace('//', '/'),
	          src: [Options.path, 'badges', title].join('/').replace('//', '/')
	        };
	      })
	    }];
	  }
	};

	let _$3 = t => t,
	  _t$3;
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _changesCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("changesCount");
	var _getEditor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEditor");
	var _initAccessSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initAccessSelector");
	var _showSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSlider");
	var _subscribedToASliderEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribedToASliderEvents");
	class MaskEditor extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _showSlider, {
	      value: _showSlider2
	    });
	    Object.defineProperty(this, _initAccessSelector, {
	      value: _initAccessSelector2
	    });
	    Object.defineProperty(this, _getEditor, {
	      value: _getEditor2
	    });
	    this.cache = new main_core.Cache.MemoryCache();
	    Object.defineProperty(this, _data, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _changesCount, {
	      writable: true,
	      value: 0
	    });
	    this.setEventNamespace([Options.eventNamespace, 'mask:editor'].join(':'));
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = [this.getEventNamespace(), new Date().getTime()].join(':');
	  }
	  getContentContainer() {
	    return this.cache.remember('content', () => {
	      const res = main_core.Tag.render(_t$3 || (_t$3 = _$3`<div class="ui-avatar-editor--scope">
						<ol class="ui-avatar-editor-list">
							<li class="ui-avatar-editor-list-item">
								<span class="ui-avatar-editor-list-item-num">1</span>
								${0}
								<div class="ui-avatar-editor-list-link-box">
									<a href="${0}" class="ui-avatar-editor-list-link">${0}</a>
									<a href="/bitrix/js/ui/avatar-editor/dist/user_frame_template.zip" download class="ui-avatar-editor-list-link">${0}</a>
								</div>
							</li>
							<li class="ui-avatar-editor-list-item">
								<span class="ui-avatar-editor-list-item-num">2</span>
								${0}
								<div class="ui-avatar-editor-mask-file" data-bx-role="mask-file"></div>
							</li>
							<li class="ui-avatar-editor-list-item">${0}
								<span class="ui-avatar-editor-list-item-num">3</span>
								<div class="ui-form">
									<div class="ui-form-row">
										<div class="ui-form-label">
											<div class="ui-ctl-label-text">${0}</div>
										</div>
										<div class="ui-form-content">
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
												<input data-bx-role="title" type="text" class="ui-ctl-element" placeholder="${0}">
											</div>
										</div>
									</div>
									<div class="ui-form-row">
										<div class="ui-form-label">
											<div class="ui-ctl-label-text">${0}</div>
										</div>
										<div class="ui-form-content">
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" data-bx-role="access-container"></div>
										</div>
									</div>
								</div>
							</li>
						</ol>
					</div>`), main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_CONTENT_1_POINT').replace(/#SIZE/gi, main_core.Loc.getMessage('UI_AVATAR_MASK_MAX_SIZE')), main_core.Loc.getMessage('UI_AVATAR_MASK_PATH_ARTICLE'), main_core.Loc.getMessage('JS_AVATAR_EDITOR_HOW_TO'), main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_DOWNLOAD_TEMPLATE2'), main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_CONTENT_2_POINT'), main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_CONTENT_3_POINT'), main_core.Loc.getMessage('JS_AVATAR_EDITOR_TITLE'), main_core.Loc.getMessage('JS_AVATAR_EDITOR_PLACEHOLDER'), main_core.Loc.getMessage('JS_AVATAR_EDITOR_ACCESS'));
	      res.querySelector('[data-bx-role="mask-file"]').appendChild(babelHelpers.classPrivateFieldLooseBase(this, _getEditor)[_getEditor]().getContainer());
	      babelHelpers.classPrivateFieldLooseBase(this, _getEditor)[_getEditor]().getCanvasZooming().setDefaultValue(0.5).reset();
	      return res;
	    });
	  }
	  isModified() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _changesCount)[_changesCount] > 0;
	  }
	  openNew() {
	    babelHelpers.classPrivateFieldLooseBase(this, _showSlider)[_showSlider]().then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _changesCount)[_changesCount] = 0;
	      babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = {
	        id: null,
	        title: '',
	        src: null,
	        accessCode: [['meta-user', 'all-users']]
	      };
	      babelHelpers.classPrivateFieldLooseBase(this, _getEditor)[_getEditor]().reset();
	      this.getContentContainer().querySelector('[data-bx-role="title"]').value = '';
	      babelHelpers.classPrivateFieldLooseBase(this, _initAccessSelector)[_initAccessSelector]();
	    });
	  }
	  openSaved(data) {
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = {
	      id: data.id,
	      title: data.title,
	      src: data.src,
	      accessCode: data.accessCode || null
	    };
	    this.getContentContainer().querySelector('[data-bx-role="title"]').value = main_core.Text.encode(data.title);
	    babelHelpers.classPrivateFieldLooseBase(this, _showSlider)[_showSlider]().then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _getEditor)[_getEditor]().loadSrc(data.src).then(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _changesCount)[_changesCount] = 0;
	        if (!data.accessCode) {
	          Backend.getMaskAccessCode(data.id).then(({
	            data: {
	              accessCode
	            }
	          }) => {
	            this.emit('maskAccessCodeHasGot', accessCode);
	            babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].accessCode = accessCode;
	            babelHelpers.classPrivateFieldLooseBase(this, _initAccessSelector)[_initAccessSelector]();
	          });
	        } else {
	          babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].accessCode = Array.from(data.accessCode);
	          babelHelpers.classPrivateFieldLooseBase(this, _initAccessSelector)[_initAccessSelector]();
	        }
	      });
	    });
	  }
	  checkOpened() {
	    return new Promise((resolve, reject) => {
	      let isSuccess = true;
	      if (babelHelpers.classPrivateFieldLooseBase(this, _getEditor)[_getEditor]().isEmpty()) {
	        babelHelpers.classPrivateFieldLooseBase(this, _getEditor)[_getEditor]().getTab(UploadTab.code).showError({
	          message: main_core.Loc.getMessage('JS_AVATAR_EDITOR_ERROR_IMAGE_IS_NOT_CHOSEN')
	        });
	        isSuccess = false;
	      }
	      const title = this.getContentContainer().querySelector('[data-bx-role="title"]').value.trim();
	      if (title.length <= 0) {
	        this.getContentContainer().querySelector('[data-bx-role="title"]').style.border = '3px solid red';
	        isSuccess = false;
	      }
	      if (isSuccess) {
	        return resolve();
	      }
	      return reject();
	    });
	  }
	  saveOpened() {
	    return new Promise((resolve, reject) => {
	      const cb = ({
	        blob
	      }) => {
	        Backend.saveMask({
	          id: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].id,
	          title: this.getContentContainer().querySelector('[data-bx-role="title"]').value,
	          accessCode: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].accessCode
	        }, blob).then(resolve).catch(reject);
	      };
	      if (!this.isModified()) {
	        return cb({
	          blob: null
	        });
	      }
	      return babelHelpers.classPrivateFieldLooseBase(this, _getEditor)[_getEditor]().packBlob().then(cb);
	    });
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getEditor)[_getEditor]().reset();
	    this.getContentContainer().querySelector('[data-bx-role="title"]').value = '';
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = null;
	    this.cache.storage.clear();
	  }
	  /**
	   * Emits specified event with specified event object
	   * @param {string} eventName
	   * @param {BaseEvent | any} event
	   * @return {this}
	   */
	  emit(eventName, event) {
	    BX.SidePanel.Instance.postMessageAll(babelHelpers.classPrivateFieldLooseBase(this, _id)[_id], eventName, event);
	    return this;
	  }
	  static subscribe(eventName, listener) {
	    main_core_events.EventEmitter.subscribe([Options.eventNamespace, 'mask:editor', eventName].join(':'), listener);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _subscribedToASliderEvents)[_subscribedToASliderEvents]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribedToASliderEvents)[_subscribedToASliderEvents] = true;
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', ({
	      data: [BXSidePanelMessageEvent]
	    }) => {
	      if (BXSidePanelMessageEvent.getSender().getUrl().indexOf([Options.eventNamespace, 'mask:editor'].join(':')) === 0) {
	        main_core_events.EventEmitter.emit([Options.eventNamespace, 'mask:editor', BXSidePanelMessageEvent.getEventId()].join(':'), BXSidePanelMessageEvent.getData());
	      }
	    });
	  }
	  static getInstance() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	    }
	    if (window === window.top) {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	      }
	      return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	    }
	    return null;
	  }
	  static getPromiseWithInstance() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] || this.getInstance()) {
	      return new Promise(resolve => {
	        resolve(babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]);
	      });
	    }
	    return new Promise(resolve => {
	      top.BX.Runtime.loadExtension(['ui.avatar-editor']).then(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = top.BX.UI.AvatarEditor.MaskEditor.getInstance();
	        resolve(babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]);
	      });
	    });
	  }
	}
	function _getEditor2() {
	  return this.cache.remember('editor', () => {
	    const res = new Editor({
	      enableCamera: false,
	      enableUpload: true,
	      uploadTabOptions: {
	        fileAccept: 'image/png'
	      },
	      enableMask: false
	    });
	    res.subscribe('onChange', ({
	      data
	    }) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _changesCount)[_changesCount]++;
	    });
	    return res;
	  });
	}
	function _initAccessSelector2() {
	  return this.cache.remember('TagSelector', () => {
	    const handler = ({
	      target
	    }) => {
	      if (target instanceof ui_entitySelector.Dialog) {
	        babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].accessCode = target.getSelectedItems().map(item => {
	          return [item.entityId, item.id];
	        });
	      }
	    };
	    const selector = new top.BX.UI.EntitySelector.TagSelector({
	      id: this.constructor.name,
	      dialogOptions: {
	        id: this.constructor.name,
	        context: null,
	        preselectedItems: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].accessCode,
	        events: {
	          'Item:onSelect': handler,
	          'Item:onDeselect': handler
	        },
	        entities: [{
	          id: 'meta-user',
	          options: {
	            'all-users': {
	              title: 'All users',
	              allowView: true
	            }
	          }
	        }, {
	          id: 'user',
	          options: {
	            emailUsers: false,
	            inviteGuestLink: false,
	            myEmailUsers: false
	          }
	        }, {
	          id: 'department',
	          options: {
	            selectMode: 'usersAndDepartments',
	            allowFlatDepartments: false
	          }
	        }]
	      }
	    });
	    selector.renderTo(this.getContentContainer().querySelector('[data-bx-role="access-container"]'));
	    return selector;
	  });
	}
	function _showSlider2() {
	  return new Promise((resolve, reject) => {
	    BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldLooseBase(this, _id)[_id], {
	      width: 800,
	      cacheable: false,
	      allowChangeHistory: false,
	      events: {
	        onCloseByEsc: event => {
	          event.denyAction();
	        },
	        onOpen: () => {
	          setTimeout(() => {
	            this.emit('onOpen', {});
	          }, 0);
	          resolve();
	        },
	        onCloseComplete: this.destroy.bind(this)
	      },
	      contentCallback: slider => {
	        return ui_sidepanel_layout.Layout.createContent({
	          extensions: [],
	          title: main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_TITLE'),
	          content: () => {
	            const res = this.getContentContainer();
	            setTimeout(() => {
	              babelHelpers.classPrivateFieldLooseBase(this, _getEditor)[_getEditor]().getCanvasZooming().setDefaultValue(0.5).reset();
	            }, 0);
	            return res;
	          },
	          buttons: ({
	            CancelButton,
	            SaveButton
	          }) => {
	            return [new SaveButton({
	              onclick: button => {
	                button.setWaiting(true);
	                this.checkOpened().then(this.saveOpened.bind(this)).then(({
	                  data
	                }) => {
	                  this.emit('onSave', {
	                    id: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].id,
	                    data: data
	                  });
	                  button.setWaiting(false);
	                  slider.close();
	                }).catch(error => {
	                  if (error) {
	                    BX.UI.Notification.Center.notify({
	                      content: ['Error is here', ...arguments].join('-')
	                    });
	                  }
	                  button.setWaiting(false);
	                });
	              }
	            }), new CancelButton({
	              onclick: () => {
	                slider.close();
	              }
	            })];
	          }
	        });
	      },
	      label: {
	        text: main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_LABEL')
	      }
	    });
	  });
	}
	Object.defineProperty(MaskEditor, _instance, {
	  writable: true,
	  value: void 0
	});
	Object.defineProperty(MaskEditor, _subscribedToASliderEvents, {
	  writable: true,
	  value: false
	});

	let _$4 = t => t,
	  _t$4;
	var _repo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("repo");
	var _template = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("template");
	class MaskItem extends main_core_events.EventEmitter {
	  constructor(data, template) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    Object.defineProperty(this, _template, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Main.Avatar.Editor');
	    this.data = data;
	    babelHelpers.classPrivateFieldLooseBase(this, _template)[_template] = template;
	    MaskEditor.subscribe('onSave', event => {
	      try {
	        const {
	          data: {
	            id,
	            data
	          }
	        } = event;
	        if (String(this.data.id) === String(id)) {
	          this.update(data);
	        }
	      } catch (e) {
	        console.log(e.message);
	      }
	    });
	  }
	  getContainer() {
	    return this.cache.remember('container', () => {
	      const itemText = babelHelpers.classPrivateFieldLooseBase(this, _template)[_template].replace(/#MASK_ID#/gi, main_core.Text.encode(this.data.id)).replace(/#MASK_TITLE#/gi, main_core.Text.encode(this.data.title || '')).replace(/#MASK_SUBTITLE#/gi, main_core.Text.encode(this.data.description || '')).replace(/#MASK_SRC#/gi, main_core.Text.encode(this.data.src));
	      const res = main_core.Tag.render(_t$4 || (_t$4 = _$4`${0}`), itemText);
	      main_core.Event.bind(res.querySelector('[data-bx-role="mask-item-menu-pointer"]'), 'click', this.onClickMenuPointer.bind(this));
	      babelHelpers.classPrivateFieldLooseBase(this.constructor, _repo)[_repo].set(res, this);
	      main_core.Event.bind(res, 'click', this.setActive.bind(this));
	      return res;
	    });
	  }
	  getData() {
	    return Object.assign({}, this.data);
	  }
	  getId() {
	    return this.data.id;
	  }
	  update(data) {
	    this.data.title = data.title;
	    this.data.src = data.src;
	    this.data.description = data.description;
	    this.data.accessCode = data.accessCode;
	    this.data.editable = data.editable;
	    const oldContainer = this.getContainer();
	    this.cache.delete('container');
	    const newContainer = this.getContainer();
	    main_core.Dom.replace(oldContainer, newContainer);
	  }
	  setActive() {
	    this.emit('onClickMask');
	  }
	  onClickMenuPointer(event) {
	    event.preventDefault();
	    event.stopPropagation();
	    const thisPopupId = 'mask-item-menu-context-' + this.data.id;
	    const thisPopup = main_popup.MenuManager.create(thisPopupId, event.target, [{
	      href: this.data.src,
	      dataset: {
	        id: 'download'
	      },
	      text: main_core.Loc.getMessage('JS_AVATAR_EDITOR_DOWNLOAD_BUTTON'),
	      onclick: (event, item) => {
	        item.getMenuWindow().close();
	      }
	    }, this.data.editable ? {
	      text: main_core.Loc.getMessage('JS_AVATAR_EDITOR_EDIT_BUTTON'),
	      onclick: (event, item) => {
	        this.emit('onClickEditMask');
	        item.getMenuWindow().close();
	      }
	    } : null, this.data.editable ? {
	      text: main_core.Loc.getMessage('JS_AVATAR_EDITOR_DELETE_BUTTON'),
	      onclick: (event, item) => {
	        this.emit('onClickDeleteMask');
	        item.getMenuWindow().close();
	      }
	    } : null], {
	      closeByEsc: true,
	      autoHide: true,
	      offsetTop: 0,
	      offsetLeft: 15,
	      angle: true,
	      cacheable: false,
	      targetContainer: event.target.closest('.ui-avatar-editor__mask-block-container'),
	      className: 'popup-window-content-frame-item-menu',
	      events: {
	        onFirstShow: ({
	          compatData: [popup]
	        }) => {
	          popup.getContentContainer().querySelector('[data-id="download"]').setAttribute('download', '');
	        }
	      }
	    });
	    thisPopup.show();
	    main_core_events.EventEmitter.subscribeOnce(thisPopup.getPopupWindow().getEventNamespace() + ':onBeforeShow', () => {
	      thisPopup.close();
	    });
	    return false;
	  }
	  static getByNode(node) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _repo)[_repo].get(node);
	  }
	}
	Object.defineProperty(MaskItem, _repo, {
	  writable: true,
	  value: new WeakMap()
	});

	let _$5 = t => t,
	  _t$5,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5,
	  _t6;
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _state = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("state");
	var _pageSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pageSize");
	var _pageNumber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pageNumber");
	var _getTemplateGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTemplateGroup");
	var _getTemplateItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTemplateItem");
	class MaskList extends main_core_events.EventEmitter {
	  constructor({
	    initialPageSize,
	    items
	  }) {
	    super();
	    Object.defineProperty(this, _getTemplateItem, {
	      value: _getTemplateItem2
	    });
	    Object.defineProperty(this, _getTemplateGroup, {
	      value: _getTemplateGroup2
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: void 0
	    });
	    this.cache = new main_core.Cache.MemoryCache();
	    Object.defineProperty(this, _state, {
	      writable: true,
	      value: this.constructor.paginationStates['ready']
	    });
	    Object.defineProperty(this, _pageSize, {
	      writable: true,
	      value: 10
	    });
	    Object.defineProperty(this, _pageNumber, {
	      writable: true,
	      value: 1
	    });
	    this.setEventNamespace('Main.Avatar.Editor');
	    babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = this.getContainer().querySelector('[data-bx-role="avatar-mask-list-container"]');
	    babelHelpers.classPrivateFieldLooseBase(this, _pageSize)[_pageSize] = this.constructor.regularPageSize;
	    this.loadItems(items);
	    this.setReady();
	  }
	  static getTemplate() {
	    return `<div>
				<div class="ui-avatar-editor--scope" data-bx-role="avatar-mask-list-container">
					<section class="ui-avatar-editor__mask-block-list-container" id="mask_group">
						<h3 class="ui-avatar-editor__mask-title" data-bx-role="group_title" data-bx-group-id="#GROUP_ID#">#GROUP_TITLE#</h3>
						<ul class="ui-avatar-editor__mask-block-mask-box" data-bx-role="group_body" data-bx-group-id="#GROUP_ID#">
							<li class="ui-avatar-editor__mask-block-mask-element" 
								id="mask_item"
								data-bx-role="mask_item"
								title="#MASK_TITLE# \n #MASK_SUBTITLE#"
								data-bx-id="#MASK_ID#">
								<div data-bx-role="mask-thumb" class="ui-avatar-editor__mask-block-mask-image" style="background-image: url('#MASK_SRC#'); "/></div>
								<div class="ui-avatar-editor__mask-block-mask-name">#MASK_TITLE#</div>
								<div class="ui-avatar-editor__mask-block-mask-subname">#MASK_SUBTITLE#</div>
								<div class="ui-avatar-editor__mask-block-mask-menu" data-bx-role="mask-item-menu-pointer"></div>
							</li>
						</ul>
					</section>
				</div>
				<nav class="ui-avatar-editor-pagination" data-bx-role="avatar-mask-list-pagination"></nav>
			</div>`;
	  }
	  static setByNode(node, object) {
	    return this.repoList.set(node, object);
	  }
	  static getByNode(node) {
	    return this.repoList.get(node);
	  }
	  setPageSize(pageSize) {
	    babelHelpers.classPrivateFieldLooseBase(this, _pageSize)[_pageSize] = pageSize;
	    return this;
	  }
	  getContainer() {
	    return this.cache.remember('container', () => {
	      const res = main_core.Tag.render(_t$5 || (_t$5 = _$5`${0}`), this.constructor.getTemplate());
	      main_core.Dom.remove(res.querySelector('#mask_item'));
	      main_core.Dom.remove(res.querySelector('#mask_group'));
	      return res;
	    });
	  }
	  isReady() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] === this.constructor.paginationStates.ready;
	  }
	  setReady() {
	    this.getMoreButton().setWaiting(false);
	    babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] = this.constructor.paginationStates.ready;
	  }
	  setBusy() {
	    this.getMoreButton().setWaiting(true);
	    babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] = this.constructor.paginationStates.inprogress;
	  }
	  setFinished() {
	    this.getMoreButton().setDisabled(true);
	    babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] = this.constructor.paginationStates.finished;
	    main_core.Dom.remove(this.getContainer().querySelector('[data-bx-role="avatar-mask-list-pagination"]'));
	  }
	  getMoreButton() {
	    return this.cache.remember('moreButton', () => {
	      const butt = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_LIST_PAGINATION'),
	        baseClass: 'ui-btn ui-btn-light-border',
	        size: ui_buttons.ButtonSize.SMALL,
	        noCaps: true,
	        round: true,
	        onclick: this.load.bind(this)
	      });
	      butt.renderTo(this.getContainer().querySelector('[data-bx-role="avatar-mask-list-pagination"]'));
	      return butt;
	    });
	  }
	  load() {
	    if (!this.isReady()) {
	      return;
	    }
	    this.setBusy();
	    Backend.getMaskList(this.constructor.name.replace('Mask', ''), {
	      page: ++babelHelpers.classPrivateFieldLooseBase(this, _pageNumber)[_pageNumber],
	      size: babelHelpers.classPrivateFieldLooseBase(this, _pageSize)[_pageSize]
	    }).then(this.loadItems.bind(this)).catch(this.terminate.bind(this));
	  }
	  loadItems(items) {
	    this.renderItems(items);
	    this.finish(items);
	  }
	  renderItems(data) {
	    let maxCount = babelHelpers.classPrivateFieldLooseBase(this, _pageSize)[_pageSize];
	    Object.values(data).forEach(({
	      id,
	      title,
	      items
	    }) => {
	      if (maxCount <= 0) {
	        return;
	      }
	      items = Object.values(items).slice(0, maxCount);
	      maxCount -= items.length;
	      id = id || '0';
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].querySelector(`[data-bx-group-id="${id}"][data-bx-role="group_body"]`)) {
	        const groupText = babelHelpers.classPrivateFieldLooseBase(this, _getTemplateGroup)[_getTemplateGroup]().replace(/#GROUP_ID#/gi, main_core.Text.encode(id)).replace(/#GROUP_TITLE#/gi, main_core.Text.encode(title || ''));
	        babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].appendChild(main_core.Tag.render(_t2$1 || (_t2$1 = _$5`${0}`), groupText));
	      }
	      const badgeContainer = babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].querySelector(`[data-bx-group-id="${id}"][data-bx-role="group_body"]`);
	      items.forEach(item => {
	        const maskItem = new MaskItem(item, babelHelpers.classPrivateFieldLooseBase(this, _getTemplateItem)[_getTemplateItem]());
	        badgeContainer.appendChild(maskItem.getContainer());
	      });
	    });
	  }
	  renderItemsReverse(data) {
	    Object.values(data).forEach(({
	      id,
	      title,
	      items
	    }) => {
	      id = id || '0';
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].querySelector(`[data-bx-group-id="${id}"][data-bx-role="group_body"]`)) {
	        const groupText = babelHelpers.classPrivateFieldLooseBase(this, _getTemplateGroup)[_getTemplateGroup]().replace(/#GROUP_ID#/gi, main_core.Text.encode(id)).replace(/#GROUP_TITLE#/gi, main_core.Text.encode(title || ''));
	        main_core.Dom.prepend(main_core.Tag.render(_t3$1 || (_t3$1 = _$5`${0}`), groupText), babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	      }
	      const badgeContainer = babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].querySelector(`[data-bx-group-id="${id}"][data-bx-role="group_body"]`);
	      items.forEach(item => {
	        const maskItem = new MaskItem(item, babelHelpers.classPrivateFieldLooseBase(this, _getTemplateItem)[_getTemplateItem]());
	        main_core.Dom.prepend(maskItem.getContainer(), badgeContainer);
	      });
	    });
	  }
	  finish(data) {
	    let thisPageItemCount = 0;
	    data.forEach(({
	      items
	    }) => {
	      thisPageItemCount += items.length;
	    });
	    if (thisPageItemCount >= babelHelpers.classPrivateFieldLooseBase(this, _pageSize)[_pageSize]) {
	      this.setReady();
	    } else {
	      this.setFinished();
	    }
	  }
	  terminate(data) {
	    let errors = [];
	    if (data instanceof Error) {
	      console.log('data: ', data);
	      errors.push(data);
	    } else if (data['errors']) {
	      errors = data.errors;
	    } else {
	      errors.push({
	        message: 'Some error'
	      });
	    }
	    this.setFinished();
	    errors.forEach(({
	      code,
	      message
	    }) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].appendChild(main_core.Tag.render(_t4$1 || (_t4$1 = _$5`<pre>${0}</pre>`), main_core.Text.encode(message)));
	    });
	  }
	}
	function _getTemplateGroup2() {
	  return this.cache.remember('templateGroup', () => {
	    const maskGroup = main_core.Tag.render(_t5 || (_t5 = _$5`${0}`), this.constructor.getTemplate()).querySelector('#mask_group');
	    const maskItem = maskGroup.querySelector('#mask_item');
	    maskItem.parentNode.removeChild(maskItem);
	    maskGroup.removeAttribute('id');
	    return maskGroup.outerHTML.trim();
	  });
	}
	function _getTemplateItem2() {
	  return this.cache.remember('templateItem', () => {
	    const maskItem = main_core.Tag.render(_t6 || (_t6 = _$5`${0}`), this.constructor.getTemplate()).querySelector('#mask_item');
	    maskItem.removeAttribute('id');
	    return maskItem.outerHTML.trim();
	  });
	}
	MaskList.repoList = new WeakMap();
	MaskList.paginationStates = {
	  ready: 0,
	  inprogress: 1,
	  finished: 3
	};
	MaskList.regularPageSize = 9;
	MaskList.shortPageSize = 3;
	class MaskRecentlyUsedList extends MaskList {}
	class MaskSystemList extends MaskList {}
	class MaskUserList extends MaskList {
	  constructor() {
	    super(...arguments);
	    MaskEditor.subscribe('onSave', event => {
	      try {
	        const {
	          data: {
	            id,
	            data
	          }
	        } = event;
	        if (id === null) {
	          this.renderItemsReverse({
	            'doesNotMatter': {
	              items: [data]
	            }
	          });
	        }
	      } catch (e) {
	        console.log(e.message);
	      }
	    });
	  }
	}
	class MaskSharedList extends MaskList {}

	let _$6 = t => t,
	  _t$6;
	var _ready = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ready");
	var _callbacks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("callbacks");
	var _fulfillReadyCallbacksTimeout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fulfillReadyCallbacksTimeout");
	var _deleteMaskVisually = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteMaskVisually");
	class MaskTab extends DefaultTab {
	  constructor() {
	    super();
	    Object.defineProperty(this, _deleteMaskVisually, {
	      value: _deleteMaskVisually2
	    });
	    Object.defineProperty(this, _ready, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _callbacks, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _fulfillReadyCallbacksTimeout, {
	      writable: true,
	      value: void 0
	    });
	    this.badges = null;
	    this.activeId = null;
	    this.mask = this.mask.bind(this);
	    this.subscribeOnce('onActive', this.initialize.bind(this));
	  }
	  getHeader() {
	    return main_core.Loc.getMessage('JS_AVATAR_EDITOR_MASKS');
	  }
	  getBody() {
	    return this.cache.remember('body', () => {
	      return main_core.Tag.render(_t$6 || (_t$6 = _$6`<div class="ui-avatar-editor__mask-block-container">
				<div class="ui-avatar-editor__mask-block-content">
					<div data-bx-role="semantic-container" data-bx-id="recently-used" style="display: none;">
						<h3 class="ui-avatar-editor__mask-title">${0}</h3>
						<div data-bx-role="list-container" data-bx-id="recently-used"></div>
					</div>
					<div data-bx-role="list-container" data-bx-id="system"></div>
					<div data-bx-role="semantic-container" data-bx-id="shared" style="display: none">
						<h3 class="ui-avatar-editor__mask-title">${0}</h3>
						<div data-bx-role="list-container" data-bx-id="shared">
							<a class="ui-btn ui-btn-lg ui-btn-link ui-btn-wait ui-btn-no-caps ui-btn-icon-add">...</a>
						</div>
					</div>
					<div data-bx-role="semantic-container" data-bx-id="my-own">
						<h3 class="ui-avatar-editor__mask-title" data-bx-id="rest-market-export-menu">
							${0}
							<div data-bx-id="rest-market-export-menu-pointer" class="ui-avatar-editor__menu-more"></div>
						</h3>
						<div data-bx-role="list-container" data-bx-id="my-own"></div>

						<a href="#" class="ui-avatar-editor__mask-create-box" data-bx-role="semantic-container" data-bx-id="rest-market" style="display: none;">
							<div class="ui-avatar-editor__mask-btn-load">
								<div class="ui-avatar-editor__mask-btn-load-icon"></div>
								${0}
								<div class="ui-avatar-editor__mask-btn-load-cloud"></div>
							</div>
						</a>

						<div class="ui-avatar-editor__mask-create-box">
							<div class="ui-avatar-editor__mask-btn-add" data-bx-id="avatar-mask-list-own-create">${0}</div>
							<a href="/bitrix/js/ui/avatar-editor/dist/user_frame_template.zip" download class="ui-avatar-editor__mask-link">${0}</a>
						</div>
					</div>
				</div>
			</div>`), main_core.Loc.getMessage('JS_AVATAR_EDITOR_RECENT_MASKS'), main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_LIST_SHARED'), main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_LIST_MY_OWN'), main_core.Loc.getMessage('JS_AVATAR_EDITOR_LOAD_FROM_MARKET'), main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_ADD_MY_OWN'), main_core.Loc.getMessage('UI_AVATAR_EDITOR_MASK_DOWNLOAD_TEMPLATE1'));
	    });
	  }
	  initialize() {
	    Backend.getMaskInitialInfo({
	      size: MaskList.regularPageSize,
	      recentlyUsedListSize: MaskList.shortPageSize
	    }).then(this.initializeData.bind(this)).catch(error => {
	      console.log('errors: ', error);
	    });
	  }
	  initializeData({
	    recentlyUsedItems,
	    systemItems,
	    myOwnItems,
	    sharedItems,
	    restMarketInfo
	  }) {
	    const body = this.getBody();
	    if (main_core.Loc.getMessage('USER_ID') > 0) {
	      main_core.Event.bind(body.querySelector('[data-bx-id="avatar-mask-list-own-create"]'), 'click', this.onClickCreateMask.bind(this));
	      main_core_events.EventEmitter.subscribe(this.getEventNamespace() + ':' + 'onClickEditMask', this.onClickEditMask.bind(this));
	      main_core_events.EventEmitter.subscribe(this.getEventNamespace() + ':' + 'onClickDeleteMask', this.onClickDeleteMask.bind(this));
	    }
	    if (restMarketInfo['available'] === 'Y') {
	      const menuItem = body.querySelector('[data-bx-id="rest-market-export-menu"]');
	      main_core.Dom.addClass(menuItem, '--menuable');
	      main_core.Event.bind(menuItem.querySelector('[data-bx-id="rest-market-export-menu-pointer"]'), 'click', event => {
	        this.onClickOwnMaskMenu(event, restMarketInfo);
	      });
	      const marketLink = body.querySelector('[data-bx-role="semantic-container"][data-bx-id="rest-market"]');
	      marketLink.style.display = '';
	      marketLink.href = restMarketInfo['marketUrl'];
	    }
	    [[MaskRecentlyUsedList, recentlyUsedItems, body.querySelector('[data-bx-role="list-container"][data-bx-id="recently-used"]')], [MaskSystemList, systemItems, body.querySelector('[data-bx-role="list-container"][data-bx-id="system"]')], [MaskUserList, myOwnItems, body.querySelector('[data-bx-role="list-container"][data-bx-id="my-own"]')], [MaskSharedList, sharedItems, body.querySelector('[data-bx-role="list-container"][data-bx-id="shared"]')]].forEach(([className, items, container]) => {
	      items = items || [];
	      if (items.length > 0) {
	        const semanticContainer = container.closest('[data-bx-role="semantic-container"]');
	        if (semanticContainer) {
	          semanticContainer.style.display = '';
	        }
	      }
	      container.innerHTML = '';
	      /**
	       * @typedef {MaskList} list
	       */
	      const list = new className({
	        initialPageSize: this.constructor.initialPageSize,
	        items: items
	      });
	      container.appendChild(list.getContainer());
	      MaskList.setByNode(container, list);
	    });
	    main_core_events.EventEmitter.subscribe(this.getEventNamespace() + ':' + 'onClickMask', ({
	      target: maskItem
	    }) => {
	      /**
	       * @typedef {MaskItem} maskItem
	       */
	      if (this.getBody().contains(maskItem.getContainer())) {
	        if (this.activeId === maskItem.getId()) {
	          this.unmask();
	        } else {
	          this.mask(maskItem.getData());
	        }
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _ready)[_ready] = true;
	    this.fulfillReadyCallbacks();
	  }
	  onReady(callback) {
	    babelHelpers.classPrivateFieldLooseBase(this, _callbacks)[_callbacks].push(callback);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _ready)[_ready]) {
	      this.fulfillReadyCallbacks();
	    }
	  }
	  fulfillReadyCallbacks() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _fulfillReadyCallbacksTimeout)[_fulfillReadyCallbacksTimeout] > 0) {
	      return;
	    }
	    const callback = babelHelpers.classPrivateFieldLooseBase(this, _callbacks)[_callbacks].shift();
	    if (callback) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _callbacks)[_callbacks].length > 0) {
	        babelHelpers.classPrivateFieldLooseBase(this, _fulfillReadyCallbacksTimeout)[_fulfillReadyCallbacksTimeout] = setTimeout(() => {
	          babelHelpers.classPrivateFieldLooseBase(this, _fulfillReadyCallbacksTimeout)[_fulfillReadyCallbacksTimeout] = 0;
	          this.fulfillReadyCallbacks();
	        }, 10);
	      }
	      callback.call(this);
	    }
	  }
	  unmask() {
	    if (this.activeId !== null) {
	      let foundAtLeastOneNode;
	      this.getBody().querySelectorAll(`[data-bx-role="mask_item"][data-bx-id="${this.activeId}"]`).forEach(node => {
	        foundAtLeastOneNode = node;
	        main_core.Dom.removeClass(node, '--active');
	      });
	      if (foundAtLeastOneNode) {
	        this.emit('onUnsetMask', this.activeId);
	      }
	    }
	    this.activeId = null;
	  }
	  maskById(id) {
	    this.onReady(() => {
	      const maskItem = MaskItem.getByNode(this.getBody().querySelector(`[data-bx-role="mask_item"][data-bx-id="${id}"]`));
	      if (maskItem instanceof MaskItem) {
	        maskItem.setActive();
	      }
	    });
	  }
	  mask({
	    id,
	    src,
	    thumb
	  }) {
	    if (this.activeId !== id && main_core.Type.isStringFilled(id)) {
	      this.unmask();
	      let foundAtLeastOneNode;
	      this.getBody().querySelectorAll(`[data-bx-role="mask_item"][data-bx-id="${id}"]`).forEach(node => {
	        foundAtLeastOneNode = node;
	        main_core.Dom.addClass(node, ' --active');
	      });
	      if (foundAtLeastOneNode) {
	        this.activeId = id;
	        this.emit('onSetMask', {
	          id: id,
	          src: src,
	          thumb: thumb || src
	        });
	      }
	    }
	  }
	  onClickCreateMask(event) {
	    event.stopImmediatePropagation();
	    MaskEditor.getPromiseWithInstance().then(maskEditor => {
	      maskEditor.openNew();
	    });
	  }
	  onClickEditMask(event) {
	    /* @var MaskItem maskItem */
	    const maskItem = event.getTarget();
	    if (this.getBody().contains(maskItem.getContainer())) {
	      event.stopImmediatePropagation();
	      MaskEditor.getPromiseWithInstance().then(maskEditor => {
	        maskEditor.openSaved(Object.assign({}, maskItem.getData()));
	      });
	    }
	  }
	  onClickDeleteMask({
	    target
	  }) {
	    if (this.getBody().contains(target.getContainer())) {
	      /* @var MaskItem target */
	      babelHelpers.classPrivateFieldLooseBase(this, _deleteMaskVisually)[_deleteMaskVisually](target);
	      Backend.deleteMask(target.getId()).then(() => {
	        this.getBody().querySelectorAll(`[data-bx-role="mask_item"][data-bx-id="${target.getId()}"]`).forEach(node => {
	          main_core.Dom.remove(node);
	        });
	      }).catch(({
	        errors
	      }) => {
	        BX.UI.Notification.Center.notify({
	          content: [main_core.Loc.getMessage('JS_AVATAR_EDITOR_ERROR'), ...errors.map(({
	            message,
	            code
	          }) => {
	            return message || code;
	          })].join(' ')
	        });
	      });
	    }
	  }
	  onClickOwnMaskMenu(event, urls) {
	    const thisPopupId = 'mask-item-menu-context-own-masks';
	    const isFilled = !!this.getBody().querySelector('[data-bx-role="list-container"][data-bx-id="my-own"]').querySelector(`[data-bx-role="mask_item"]`);
	    const thisPopup = main_popup.MenuManager.create(thisPopupId, event.target, [isFilled && main_core.Type.isStringFilled(urls.exportUrl) ? {
	      href: urls.exportUrl,
	      text: main_core.Loc.getMessage('JS_AVATAR_EDITOR_EXPORT_BUTTON'),
	      onclick: (event, item) => {
	        this.emit('onClickExport');
	        item.getMenuWindow().close();
	      }
	    } : null, isFilled ? {
	      text: main_core.Loc.getMessage('JS_AVATAR_EDITOR_CLEAN_BUTTON'),
	      onclick: (event, item) => {
	        item.getMenuWindow().close();
	        new ui_dialogs_messagebox.MessageBox({
	          message: main_core.Loc.getMessage('JS_AVATAR_EDITOR_CLEAN_NOTIFICATION'),
	          title: main_core.Loc.getMessage('JS_AVATAR_EDITOR_CLEAN_NOTIFICATION_TITLE'),
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	          okCaption: 'Ok',
	          onOk: messageBox => {
	            messageBox.close();
	            this.cleanUp();
	          }
	        }).show();
	      }
	    } : null, {
	      href: urls.importUrl,
	      text: main_core.Loc.getMessage('JS_AVATAR_EDITOR_IMPORT_BUTTON'),
	      onclick: (event, item) => {
	        this.emit('onClickImport');
	        item.getMenuWindow().close();
	      }
	    }], {
	      closeByEsc: true,
	      autoHide: true,
	      offsetTop: 0,
	      offsetLeft: 15,
	      angle: true,
	      cacheable: false,
	      targetContainer: event.target.closest('.ui-avatar-editor__mask-block-container'),
	      className: 'popup-window-content-frame-item-menu'
	    });
	    thisPopup.show();
	    main_core_events.EventEmitter.subscribeOnce(thisPopup.getPopupWindow().getEventNamespace() + ':onBeforeShow', () => {
	      thisPopup.close();
	    });
	    return false;
	  }
	  cleanUp()
	  //delete all my masks
	  {
	    const container = this.getBody().querySelector('[data-bx-role="list-container"][data-bx-id="my-own"]');
	    const loader = new main_loader.Loader({
	      target: container,
	      color: 'rgba(82, 92, 105, 0.9)'
	    });
	    loader.show();
	    Backend.cleanUp().then(() => {
	      MaskList.getByNode(container).setFinished();
	      container.querySelectorAll(`[data-bx-role="mask_item"]`).forEach(node => {
	        babelHelpers.classPrivateFieldLooseBase(this, _deleteMaskVisually)[_deleteMaskVisually](MaskItem.getByNode(node));
	      });
	      loader.hide();
	    });
	  }

	  //TODO delete this string and its using after testing
	  static isAvailable() {
	    return main_core.Loc.getMessage('UI_AVATAR_MASK_IS_AVAILABLE') === true;
	  }
	  static get code() {
	    return 'mask';
	  }
	}
	function _deleteMaskVisually2(maskItem) {
	  /* @var MaskItem target */
	  this.getBody().querySelectorAll(`[data-bx-role="mask_item"][data-bx-id="${maskItem.getId()}"]`).forEach(node => {
	    // node.style.display = 'none';
	    main_core.Dom.remove(node);
	  });
	  const listContainer = this.getBodyContainer().querySelector('[data-bx-role="list-container"][data-bx-id="recently-used"]');
	  if (listContainer.childNodes.length <= 1) {
	    const semanticContainer = listContainer.closest('[data-bx-role="semantic-container"]');
	    if (semanticContainer) {
	      semanticContainer.style.display = 'none';
	    }
	  }
	  if (String(this.activeId) === String(maskItem.getId())) {
	    this.unmask();
	  }
	}
	MaskTab.maxCount = 5;
	MaskTab.priority = 4;

	let _$7 = t => t,
	  _t$7;
	class CanvasTab extends DefaultTab {
	  getHeader() {
	    return main_core.Loc.getMessage('JS_AVATAR_EDITOR_PHOTO');
	  }
	  getBody() {
	    return this.cache.remember('body', () => {
	      const res = main_core.Tag.render(_t$7 || (_t$7 = _$7`
				<div class="ui-avatar-editor__content-block" data-bx-role="tab-canvas-body">
					<div class="ui-avatar-editor__control" data-bx-role="canvas-zooming">
						<div class="ui-avatar-editor__control-controller" data-bx-role="zoom-minus-button">
							<span class="ui-avatar-editor__control-minus"></span>
						</div>
						<div class="ui-avatar-editor__control-inner" data-bx-role="zoom-scale">
							<div class="ui-avatar-editor__control-slide-container ui-avatar-editor__control-slide-drag-state">
								<div class="ui-avatar-editor__control-slide" data-bx-role="zoom-knob"></div>
							</div>
						</div>
						<div class="ui-avatar-editor__control-controller" data-bx-role="zoom-plus-button">
							<span class="ui-avatar-editor__control-plus"></span>
						</div>
					</div>
					<div class="ui-avatar-editor__camera-block-image">
						<div class="ui-avatar-editor__user-loader-item" data-bx-role="canvas-loader">
							<div class="ui-avatar-editor__loader">
								<svg class="ui-avatar-editor__circular" viewBox="25 25 50 50">
									<circle class="ui-avatar-editor__path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"></circle>
								</svg>
							</div>
						</div>
						<div class="ui-avatar-editor__error" data-bx-role="canvas-error">
							<span>${0}</span>
							<span data-bx-role="tab-canvas-error"></span>
						</div>
						<div class="ui-avatar-editor__user-avatar-item" data-bx-role="canvas-holder">
							<span class="ui-avatar-editor__tab-avatar-image-item"></span>
						</div>

						<div data-editor-role="canvas-holder">
							<canvas data-bx-canvas="canvas" height="330" width="330"></canvas>
						</div>
					</div>
					<div class="ui-avatar-editor__button-layout">
						<div class="ui-avatar-editor__button" data-bx-role="button-add-picture" data-bx-id="upload-file">
							<span class="ui-avatar-editor__button-name">${0}</span>
						</div>
						<div class="ui-avatar-editor__button"  data-bx-role="button-add-picture" data-bx-id="snap-picture">
							<span class="ui-avatar-editor__button-name">${0}</span>
						</div>
					</div>
				</div>`), main_core.Loc.getMessage('JS_AVATAR_EDITOR_ERROR'), main_core.Loc.getMessage('JS_AVATAR_EDITOR_UPLOAD'), main_core.Loc.getMessage('JS_AVATAR_EDITOR_SNAP'));
	      return res;
	    });
	  }
	  static get code() {
	    return 'canvas';
	  }
	}
	CanvasTab.priority = 1;

	let _$8 = t => t,
	  _t$8;
	var _justACounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("justACounter");
	var _queue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("queue");
	var _image = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("image");
	var _canvas = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canvas");
	var _context = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("context");
	var _reader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("reader");
	var _isReady = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isReady");
	var _id$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _load = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("load");
	var _exec = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("exec");
	var _dataURLToBlob = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dataURLToBlob");
	class CanvasLoader {
	  constructor() {
	    Object.defineProperty(this, _exec, {
	      value: _exec2
	    });
	    Object.defineProperty(this, _load, {
	      value: _load2
	    });
	    Object.defineProperty(this, _justACounter, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _queue, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _image, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canvas, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _context, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _reader, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isReady, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _id$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _reader)[_reader] = new FileReader();
	    babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue] = new Map();
	    babelHelpers.classPrivateFieldLooseBase(this, _image)[_image] = new Image();
	    babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas] = main_core.Tag.render(_t$8 || (_t$8 = _$8`<canvas id="loadercanvas"></canvas>`));
	    // document.querySelector('#workarea-content').appendChild(this.#canvas);
	    babelHelpers.classPrivateFieldLooseBase(this, _context)[_context] = babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].getContext('2d');
	    babelHelpers.classPrivateFieldLooseBase(this, _id$1)[_id$1] = String(new Date().getTime());
	  }
	  push(file, successCallback, failCallback) {
	    const id = [babelHelpers.classPrivateFieldLooseBase(this, _id$1)[_id$1], babelHelpers.classPrivateFieldLooseBase(this, _justACounter)[_justACounter]++].join('_');
	    babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].set(id, [file, successCallback, failCallback]);
	    babelHelpers.classPrivateFieldLooseBase(this, _exec)[_exec]();
	  }
	  getCanvas() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas];
	  }
	  getContext() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _context)[_context];
	  }
	  pack(fileType) {
	    return new Promise((resolve, reject) => {
	      try {
	        if (babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas]['toBlob']) {
	          babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].toBlob(resolve, fileType);
	        } else {
	          resolve(babelHelpers.classPrivateFieldLooseBase(this.constructor, _dataURLToBlob)[_dataURLToBlob](babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].toDataURL(fileType)));
	        }
	      } catch (e) {
	        e.message = 'Packing error: ' + e.message;
	        reject(e);
	      }
	    });
	  }
	  static getInstance() {
	    if (this.instance === null) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  static loadFile(file, successCallback, failCallback) {
	    if (!window["FileReader"]) {
	      return failCallback(new Error({
	        message: 'FileReader is not supported.'
	      }));
	    }
	    let newFile = file;
	    if (main_core.Type.isString(file)) {
	      newFile = {
	        src: file,
	        name: file.split('/').pop()
	      };
	    }
	    this.getInstance().push(newFile, successCallback, failCallback);
	  }
	  static loadCanvas() {
	    this.getInstance().getCanvas();
	  }
	}
	function _load2(itemId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].has(itemId) || babelHelpers.classPrivateFieldLooseBase(this, _isReady)[_isReady] !== true) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _isReady)[_isReady] = false;
	  const [file, successCallback, failCallback] = babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].get(itemId);
	  babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].onload = function () {};
	  babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].onerror = function () {};

	  /* Almost all browsers cache images from local resource except of FF on 06.03.2017. It appears that
	  FF collect src and does not abort image uploading when src is changed. And we had a bug when in
	  onload event we got e.target.src of one element but source of image was from '/bitrix/images/1.gif'. */
	  // TODO check if chrome and other browsers cache local files for now. If it does not then delete next 2 strings
	  try {
	    window["URL"]["revokeObjectURL"](babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].src);
	  } catch (e) {}
	  if (!main_core.Browser.isFirefox()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].src = '/bitrix/images/1.gif';
	  }
	  const onFinish = () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].delete(itemId);
	    babelHelpers.classPrivateFieldLooseBase(this, _isReady)[_isReady] = true;
	    setTimeout(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _exec)[_exec]();
	    }, 0);
	  };
	  const onLoad = e => {
	    const image = e && e.target ? e.target : babelHelpers.classPrivateFieldLooseBase(this, _image)[_image];
	    if (image.src.indexOf('/bitrix/images/1.gif') >= 0) {
	      return;
	    }
	    if (!!successCallback) {
	      onFinish();
	      successCallback(image);
	    }
	  };
	  const onError = () => {
	    if (!!failCallback) {
	      try {
	        failCallback();
	      } catch (e) {
	        main_core.Runtime.debug(e);
	      }
	    }
	    onFinish();
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].name = file.name;
	  babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].onload = onLoad;
	  babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].onerror = onError;
	  if (main_core.Type.isPlainObject(file) && (file['src'] || file['tmp_url'])) {
	    const src = file['src'] || file['tmp_url'];
	    babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].src = encodeURI(src) + (src.indexOf("?") > 0 ? '&' : '?') + 'imageUploader' + babelHelpers.classPrivateFieldLooseBase(this, _id$1)[_id$1] + babelHelpers.classPrivateFieldLooseBase(this, _justACounter)[_justACounter]++;
	  } else {
	    const res = Object.prototype.toString.call(file);
	    if (res !== '[object File]' && res !== '[object Blob]') {
	      onError();
	    } else if (window["URL"]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].src = window["URL"]["createObjectURL"](file);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _reader)[_reader].onloadend = e => {
	        babelHelpers.classPrivateFieldLooseBase(this, _reader)[_reader].onloadend = null;
	        babelHelpers.classPrivateFieldLooseBase(this, _reader)[_reader].onerror = null;
	        babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].src = e.target.result;
	      };
	      babelHelpers.classPrivateFieldLooseBase(this, _reader)[_reader].onerror = () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _reader)[_reader].onloadend = null;
	        babelHelpers.classPrivateFieldLooseBase(this, _reader)[_reader].onerror = null;
	        onError();
	      };
	      babelHelpers.classPrivateFieldLooseBase(this, _reader)[_reader].readAsDataURL(file);
	    }
	  }
	}
	function _exec2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isReady)[_isReady] === true) {
	    const itemId = Array.from(babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].keys()).shift();
	    if (itemId) {
	      babelHelpers.classPrivateFieldLooseBase(this, _load)[_load](itemId);
	    }
	  }
	}
	function _dataURLToBlob2(dataURL) {
	  let marker = ';base64,',
	    parts,
	    contentType,
	    raw,
	    rawLength;
	  if (dataURL.indexOf(marker) < 0) {
	    parts = dataURL.split(',');
	    contentType = parts[0].split(':')[1];
	    raw = parts[1];
	    return new Blob([raw], {
	      type: contentType
	    });
	  }
	  parts = dataURL.split(marker);
	  contentType = parts[0].split(':')[1];
	  raw = window.atob(parts[1]);
	  rawLength = raw.length;
	  const uInt8Array = new Uint8Array(rawLength);
	  for (let i = 0; i < rawLength; ++i) {
	    uInt8Array[i] = raw.charCodeAt(i);
	  }
	  return new Blob([uInt8Array], {
	    type: contentType
	  });
	}
	Object.defineProperty(CanvasLoader, _dataURLToBlob, {
	  value: _dataURLToBlob2
	});
	CanvasLoader.instance = null;

	var _isSet = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSet");
	var _canvas$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canvas");
	var _ctx = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ctx");
	var _table = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("table");
	var _tableCloth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tableCloth");
	var _tableFrame = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tableFrame");
	var _stretchToCanvasSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stretchToCanvasSize");
	class CanvasDefault extends main_core_events.EventEmitter {
	  constructor(canvas, options) {
	    super();
	    Object.defineProperty(this, _isSet, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _canvas$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _ctx, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _table, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tableCloth, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tableFrame, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stretchToCanvasSize, {
	      writable: true,
	      value: true
	    });
	    this.setEventNamespace('Main.Avatar.Editor');
	    babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1] = canvas;
	    babelHelpers.classPrivateFieldLooseBase(this, _ctx)[_ctx] = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].getContext("2d");
	    babelHelpers.classPrivateFieldLooseBase(this, _table)[_table] = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].parentNode;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].clientWidth) {
	      babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].clientWidth;
	      babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].clientHeight;
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].width;
	      babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].height;
	    }
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1], {
	      style: {
	        // 'background' : '#fdbd00',
	      }
	    });
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _table)[_table], {
	      style: {
	        width: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width}px`,
	        height: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height}px`,
	        border: 'none',
	        position: 'relative',
	        overflow: 'visible'
	      },
	      dataset: {
	        role: 'table',
	        width: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width,
	        height: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth] = main_core.Dom.create('DIV');
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth], {
	      style: {
	        'border': 'none',
	        // 'background' : '#ffd7e1',
	        'position': 'absolute',
	        'display': 'flex',
	        'align-items': 'center',
	        'justify-content': 'center',
	        width: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width}px`,
	        height: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height}px`,
	        left: 0,
	        top: 0
	      },
	      dataset: {
	        role: 'tableCloth'
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].appendChild(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1]);
	    babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].appendChild(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth]);
	    babelHelpers.classPrivateFieldLooseBase(this, _tableFrame)[_tableFrame] = main_core.Dom.create('DIV');
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableFrame)[_tableFrame], {
	      style: {
	        'box-sizing': 'border-box',
	        // 'border' : '4px dotted grey',
	        'position': 'absolute',
	        width: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width}px`,
	        height: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height}px`
	      },
	      dataset: {
	        role: 'tableFrame'
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].appendChild(babelHelpers.classPrivateFieldLooseBase(this, _tableFrame)[_tableFrame]);
	    this.mouseMove = this.mouseMove.bind(this);
	    this.stopMoving = this.stopMoving.bind(this);
	    babelHelpers.classPrivateFieldLooseBase(this, _stretchToCanvasSize)[_stretchToCanvasSize] = !(options && options.stretchToCanvasSize === false);
	    this.reset();
	  }
	  isEmpty() {
	    return !babelHelpers.classPrivateFieldLooseBase(this, _isSet)[_isSet];
	  }
	  reset() {
	    babelHelpers.classPrivateFieldLooseBase(this, _ctx)[_ctx].clearRect(0, 0, babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].width, babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].height);
	    babelHelpers.classPrivateFieldLooseBase(this, _isSet)[_isSet] = false;
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth], {
	      style: {
	        width: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width}px`,
	        height: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height}px`,
	        left: 0,
	        top: 0
	      }
	    });
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1], {
	      style: {
	        transform: 'none',
	        width: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width}px`,
	        height: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height}px`
	      }
	    });
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableFrame)[_tableFrame], {
	      style: {
	        width: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width}px`,
	        height: `${babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height}px`,
	        left: 0,
	        top: 0
	      }
	    });
	    this.disableToMove();
	    this.emit('onReset', babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1]);
	  }
	  set(imageNode) {
	    //region set image
	    const image = {
	      width: 0,
	      height: 0,
	      scale: 1,
	      name: imageNode["name"]
	    };
	    if (imageNode.clientWidth) {
	      image.width = imageNode.clientWidth;
	      image.height = imageNode.clientHeight;
	    } else {
	      image.width = imageNode.width;
	      image.height = imageNode.height;
	    }
	    if (image.width <= 0 || image.height <= 0) {
	      return;
	    }
	    const scaleForImage = Math.ceil(Math.max(image.width > Options.imageSize ? Options.imageSize / image.width : babelHelpers.classPrivateFieldLooseBase(this, _stretchToCanvasSize)[_stretchToCanvasSize] !== false && image.width < babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width ? babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width / image.width : 1, image.height > Options.imageSize ? Options.imageSize / image.height : babelHelpers.classPrivateFieldLooseBase(this, _stretchToCanvasSize)[_stretchToCanvasSize] !== false && image.height < babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height ? babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height / image.height : 1) * 1000) / 1000;
	    image.width = Math.ceil(image.width * scaleForImage);
	    image.height = Math.ceil(image.height * scaleForImage);
	    const k = Math.ceil(Math.max(image.width > 0 ? babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width / image.width : 1, image.height > 0 ? babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height / image.height : 1) * 1000) / 1000;
	    image.scale = 0 < k && k < 1 ? k : 1;
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1], {
	      props: {
	        width: image.width,
	        height: image.height
	      },
	      style: {
	        width: image.width + 'px',
	        height: image.height + 'px',
	        transform: 'scale(' + image.scale + ', ' + image.scale + ')'
	      },
	      dataset: {
	        width: image.width,
	        height: image.height,
	        scale: image.scale,
	        initialScale: image.scale,
	        hasChanged: false
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _isSet)[_isSet] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _ctx)[_ctx].drawImage(imageNode, 0, 0, image.width, image.height);
	    //endregion

	    //region set TableCloth
	    const tableFrame = {
	      width: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width,
	      height: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height
	    };
	    const tableCloth = {
	      width: 2 * tableFrame.width + Math.ceil(image.scale * image.width),
	      height: 2 * tableFrame.height + Math.ceil(image.scale * image.height),
	      left: -1 * Math.ceil((tableFrame.width + image.scale * image.width) / 2),
	      top: -1 * Math.ceil((tableFrame.height + image.scale * image.height) / 2)
	    };
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth], {
	      style: {
	        width: `${tableCloth.width}px`,
	        height: `${tableCloth.height}px`,
	        top: `${tableCloth.top}px`,
	        left: `${tableCloth.left}px`
	      },
	      dataset: {
	        top: tableCloth.top,
	        left: tableCloth.left,
	        topToBeInTheCenter: tableCloth.top,
	        leftToBeInTheCenter: tableCloth.left,
	        height: tableCloth.height,
	        width: tableCloth.width
	      }
	    });
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableFrame)[_tableFrame], {
	      style: {
	        top: `${tableCloth.top * -1}px`,
	        left: `${tableCloth.left * -1}px`
	      }
	    });
	    //endregion

	    this.enableToMove();
	    this.emit('onSetImage', {
	      canvas: babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1]
	    });
	  }
	  scale(zoomScale) {
	    zoomScale = Math.max(0.01, 1 + zoomScale);
	    const oldScale = Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.scale);
	    const newScale = zoomScale * Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.initialScale);
	    //region set image
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1], {
	      style: {
	        transform: 'scale(' + newScale + ', ' + newScale + ')'
	      },
	      dataset: {
	        scale: newScale,
	        hasChanged: true
	      }
	    });
	    //endregion

	    //region set TableCloth
	    const tableFrame = {
	      height: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height,
	      width: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width
	    };
	    const tableCloth = {
	      height: 2 * tableFrame.height + Math.ceil(newScale * Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.height)),
	      width: 2 * tableFrame.width + Math.ceil(newScale * Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.width)),
	      topToBeInTheCenter: -1 * Math.ceil((tableFrame.height + newScale * Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.height)) / 2),
	      leftToBeInTheCenter: -1 * Math.ceil((tableFrame.width + newScale * Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.width)) / 2),
	      top: null,
	      left: null
	    };
	    const deltaTopOld = babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.top - babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter;
	    const deltaTop = deltaTopOld / oldScale * newScale;
	    tableCloth.top = Math.ceil(tableCloth.topToBeInTheCenter + deltaTop);
	    tableCloth.top = Math.max(Math.min(tableCloth.top, 0), -1 * (Number(tableCloth.height) - tableFrame.height));
	    const deltaLeftOld = babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.left - babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter;
	    const deltaLeft = deltaLeftOld / oldScale * newScale;
	    tableCloth.left = Math.ceil(deltaLeft + tableCloth.leftToBeInTheCenter);
	    tableCloth.left = Math.max(Math.min(tableCloth.left, 0), -1 * (Number(tableCloth.width) - tableFrame.width));
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth], {
	      style: {
	        width: `${tableCloth.width}px`,
	        height: `${tableCloth.height}px`,
	        top: `${tableCloth.top}px`,
	        left: `${tableCloth.left}px`
	      },
	      dataset: {
	        top: tableCloth.top,
	        left: tableCloth.left,
	        topToBeInTheCenter: tableCloth.topToBeInTheCenter,
	        leftToBeInTheCenter: tableCloth.leftToBeInTheCenter,
	        height: tableCloth.height,
	        width: tableCloth.width
	      }
	    });
	    //endregion

	    //region set Table sights
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableFrame)[_tableFrame], {
	      style: {
	        top: `${tableCloth.top * -1}px`,
	        left: `${tableCloth.left * -1}px`
	      }
	    });
	    //endregion

	    this.emit('onScale', {
	      zoomScale,
	      scale: newScale,
	      topOffsetFromTheCenter: Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.top) - Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter),
	      leftOffsetFromTheCenter: Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.left) - Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter),
	      topInPercent: Math.ceil(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.top * 1000 / Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter)) / 1000,
	      leftInPercent: Math.ceil(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.left * 1000 / Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter)) / 1000,
	      oldScale
	    });
	  }
	  onScale({
	    data: {
	      oldScale,
	      scale,
	      topOffsetFromTheCenter,
	      leftOffsetFromTheCenter,
	      topInPercent,
	      leftInPercent
	    }
	  }) {
	    const deltaScale = scale / oldScale;
	    let top, left, newScale;
	    if (Number(oldScale) === Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.scale)) {
	      newScale = scale;
	    } else {
	      newScale = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.scale * deltaScale;
	    }

	    //region set image
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1], {
	      style: {
	        transform: 'scale(' + newScale + ', ' + newScale + ')'
	      },
	      dataset: {
	        scale: newScale,
	        hasChanged: true
	      }
	    });
	    //endregion

	    //region set TableCloth
	    const tableFrame = {
	      height: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height,
	      width: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width
	    };
	    const tableCloth = {
	      height: 2 * tableFrame.height + Math.ceil(newScale * Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.height)),
	      width: 2 * tableFrame.width + Math.ceil(newScale * Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.width)),
	      topToBeInTheCenter: -1 * Math.ceil((tableFrame.height + newScale * Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.height)) / 2),
	      leftToBeInTheCenter: -1 * Math.ceil((tableFrame.width + newScale * Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.width)) / 2),
	      top: null,
	      left: null
	    };
	    if (Number(scale) === Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.scale)) {
	      top = Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter) + topOffsetFromTheCenter;
	      left = Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter) + leftOffsetFromTheCenter;
	    } else {
	      top = Math.ceil(Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter) * topInPercent);
	      left = Math.ceil(Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter) * leftInPercent);
	    }
	    tableCloth.top = Math.max(Math.min(top, 0), -1 * (Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.height) - babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height));
	    tableCloth.left = Math.max(Math.min(left, 0), -1 * (Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.width) - babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width));
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth], {
	      style: {
	        width: `${tableCloth.width}px`,
	        height: `${tableCloth.height}px`,
	        top: `${tableCloth.top}px`,
	        left: `${tableCloth.left}px`
	      },
	      dataset: {
	        top: tableCloth.top,
	        left: tableCloth.left,
	        topToBeInTheCenter: tableCloth.topToBeInTheCenter,
	        leftToBeInTheCenter: tableCloth.leftToBeInTheCenter,
	        height: tableCloth.height,
	        width: tableCloth.width
	      }
	    });
	    //endregion

	    //region set Table sights
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableFrame)[_tableFrame], {
	      style: {
	        top: `${tableCloth.top * -1}px`,
	        left: `${tableCloth.left * -1}px`
	      }
	    });
	    //endregion
	  }

	  enableToMove() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].style.cursor === 'move') {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].style.cursor = 'move';
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _table)[_table], 'mousedown', e => {
	      this.cursor = {
	        startX: e.pageX,
	        startY: e.pageY
	      };
	      main_core.Event.bind(document, 'mousemove', this.mouseMove);
	      main_core.Event.bind(document, 'mouseup', this.stopMoving);
	    });
	  }
	  disableToMove() {
	    babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].style.cursor = 'default';
	    main_core.Event.unbindAll(babelHelpers.classPrivateFieldLooseBase(this, _table)[_table]);
	    main_core.Event.unbind(document, 'mousemove', this.mouseMove);
	    main_core.Event.unbind(document, 'mouseup', this.stopMoving);
	  }
	  move(deltaX, deltaY) {
	    //region set image
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1], {
	      dataset: {
	        hasChanged: true
	      }
	    });
	    //endregion

	    //region set TableCloth
	    const tableFrame = {
	      height: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height,
	      width: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width
	    };
	    const left = Math.max(Math.min(Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.left) - deltaX, 0), -1 * (Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.width) - tableFrame.width));
	    const top = Math.max(Math.min(Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.top) - deltaY, 0), -1 * (Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.height) - tableFrame.height));
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth], {
	      style: {
	        top: `${top}px`,
	        left: `${left}px`
	      },
	      dataset: {
	        top: top,
	        left: left
	      }
	    });
	    this.emit('onMove', {
	      topOffsetFromTheCenter: top - Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter),
	      leftOffsetFromTheCenter: left - Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter),
	      scale: babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.scale,
	      topInPercent: Math.ceil(top * 1000 / Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter)) / 1000,
	      leftInPercent: Math.ceil(left * 1000 / Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter)) / 1000
	    });
	    //region set Table sights
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableFrame)[_tableFrame], {
	      style: {
	        top: `${top * -1}px`,
	        left: `${left * -1}px`
	      }
	    });
	    //endregion
	  }

	  onMove({
	    data: {
	      scale,
	      topOffsetFromTheCenter,
	      leftOffsetFromTheCenter,
	      topInPercent,
	      leftInPercent
	    }
	  }) {
	    let top, left;
	    if (Number(scale) === Number(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.scale)) {
	      top = Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter) + topOffsetFromTheCenter;
	      left = Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter) + leftOffsetFromTheCenter;
	    } else {
	      top = Math.ceil(Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter) * topInPercent);
	      left = Math.ceil(Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter) * leftInPercent);
	    }
	    left = Math.max(Math.min(left, 0), -1 * (Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.width) - babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width));
	    top = Math.max(Math.min(top, 0), -1 * (Number(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.height) - babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height));
	    main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth], {
	      style: {
	        top: `${top}px`,
	        left: `${left}px`
	      },
	      dataset: {
	        top: top,
	        left: left
	      }
	    });
	  }
	  mouseMove(e) {
	    if (this.cursor === null) {
	      return;
	    }
	    this.move(this.cursor.startX - e.pageX, this.cursor.startY - e.pageY);
	    this.cursor.startX = e.pageX;
	    this.cursor.startY = e.pageY;
	  }
	  stopMoving() {
	    BX.unbind(document, "mousemove", this.mouseMove);
	    BX.unbind(document, "mouseup", this.stopMoving);
	  }
	  getCanvas() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1];
	  }
	  getContext() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _ctx)[_ctx];
	  }
	  getTable() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _table)[_table];
	  }
	  getTableCloth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth];
	  }
	  packBlob() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSet)[_isSet] !== true) {
	      return Promise.reject({
	        message: 'Source canvas does not exist.',
	        code: 'empty data'
	      });
	    }
	    return new Promise((resolve, reject) => {
	      //region set TableCloth
	      const tableFrame = {
	        height: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].height,
	        width: babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width
	      };
	      const scale = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.scale;
	      const size = Math.min(Options.imageSize, Math.max(babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width / scale, babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width));
	      const scaleToResult = size / babelHelpers.classPrivateFieldLooseBase(this, _table)[_table].width;
	      const loader = CanvasLoader.getInstance();
	      loader.getCanvas().height = size;
	      loader.getCanvas().width = size;
	      loader.getContext().clearRect(0, 0, size, size);
	      //region getY
	      let imageY, imageY1, imageY2;
	      let canvasY1, canvasY2;
	      imageY = -1 * (babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.topToBeInTheCenter - babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.top) + tableFrame.height / 2 - babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].height * scale / 2;
	      if (imageY > 0) {
	        canvasY1 = imageY;
	        imageY1 = 0;
	      } else {
	        canvasY1 = 0;
	        imageY1 = -1 * imageY;
	      }
	      imageY2 = imageY + babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].height * scale;
	      if (imageY2 > tableFrame.height) {
	        canvasY2 = tableFrame.height;
	        imageY2 = tableFrame.height - imageY;
	      } else {
	        canvasY2 = imageY2;
	        imageY2 = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].height * scale;
	      }
	      //endregion
	      //region getX
	      let imageX, imageX1, imageX2;
	      let canvasX1, canvasX2;
	      imageX = -1 * (babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.leftToBeInTheCenter - babelHelpers.classPrivateFieldLooseBase(this, _tableCloth)[_tableCloth].dataset.left) + tableFrame.width / 2 - babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].width * scale / 2;
	      if (imageX > 0) {
	        canvasX1 = imageX;
	        imageX1 = 0;
	      } else {
	        canvasX1 = 0;
	        imageX1 = -1 * imageX;
	      }
	      imageX2 = imageX + babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].width * scale;
	      if (imageX2 > tableFrame.width) {
	        canvasX2 = tableFrame.width;
	        imageX2 = tableFrame.width - imageX;
	      } else {
	        canvasX2 = imageX2;
	        imageX2 = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].width * scale;
	      }
	      //endregion

	      imageX1 /= scale;
	      imageX2 /= scale;
	      imageY1 /= scale;
	      imageY2 /= scale;
	      canvasX1 *= scaleToResult;
	      canvasY1 *= scaleToResult;
	      canvasX2 *= scaleToResult;
	      canvasY2 *= scaleToResult;
	      loader.getContext().drawImage(babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1], imageX1, imageY1, imageX2 - imageX1, imageY2 - imageY1, canvasX1, canvasY1, canvasX2 - canvasX1, canvasY2 - canvasY1);
	      const changed = babelHelpers.classPrivateFieldLooseBase(this, _canvas$1)[_canvas$1].dataset.changed;
	      loader.pack().then(blob => {
	        blob.changed = changed;
	        blob.width = size;
	        blob.height = size;
	        resolve(blob);
	      }).catch(error => {
	        reject(error);
	      });
	    });
	  }
	}
	CanvasDefault.imageSize = {
	  width: 1024,
	  height: 1024
	};

	var _fileName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fileName");
	var _applyNameAndExtensionToBlob = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applyNameAndExtensionToBlob");
	class CanvasMaster extends CanvasDefault {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _applyNameAndExtensionToBlob, {
	      value: _applyNameAndExtensionToBlob2
	    });
	    Object.defineProperty(this, _fileName, {
	      writable: true,
	      value: void 0
	    });
	  }
	  load(file) {
	    this.emit('onLoad');
	    babelHelpers.classPrivateFieldLooseBase(this, _fileName)[_fileName] = null;
	    return new Promise((resolve, reject) => {
	      this.reset();
	      CanvasLoader.loadFile(file, imageNode => {
	        babelHelpers.classPrivateFieldLooseBase(this, _fileName)[_fileName] = imageNode.name;
	        this.set(imageNode);
	        resolve();
	      }, () => {
	        this.emit('onError', main_core.Loc.getMessage('JS_AVATAR_EDITOR_ERROR_IMAGE_DEPLOYING'));
	        reject();
	      });
	    });
	  }
	  getBlob() {
	    return new Promise((resolve, reject) => {
	      this.packBlob().then(blob => {
	        babelHelpers.classPrivateFieldLooseBase(this, _applyNameAndExtensionToBlob)[_applyNameAndExtensionToBlob](blob);
	        resolve({
	          blob
	        });
	      }).catch(reject);
	    });
	  }
	}
	function _applyNameAndExtensionToBlob2(result) {
	  result.name = babelHelpers.classPrivateFieldLooseBase(this, _fileName)[_fileName] || 'image';
	  let ext = result.name.split('.').pop().toLowerCase();
	  ext = ext === result.name ? '' : ext;
	  if (result.type === 'image/png' && ext !== 'png') {
	    if ('jpg,bmp,jpeg,jpe,gif,png,webp'.lastIndexOf(ext) > 0) {
	      result.name = result.name.substr(0, result.name.lastIndexOf('.'));
	    }
	    result.name = [result.name || 'image', 'png'].join('.');
	  }
	  return result;
	}

	class CanvasPreview extends CanvasDefault {}

	var _stepSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stepSize");
	var _value = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("value");
	var _defaultValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultValue");
	var _containerWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("containerWidth");
	var _scale = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("scale");
	var _knob = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("knob");
	var _getContainerWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContainerWidth");
	var _makeAStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("makeAStep");
	var _adjust = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjust");
	class CanvasZooming extends main_core_events.EventEmitter {
	  constructor({
	    knob,
	    scale,
	    minus,
	    plus
	  }, defaultValue) {
	    super();
	    Object.defineProperty(this, _adjust, {
	      value: _adjust2
	    });
	    Object.defineProperty(this, _makeAStep, {
	      value: _makeAStep2
	    });
	    Object.defineProperty(this, _getContainerWidth, {
	      value: _getContainerWidth2
	    });
	    Object.defineProperty(this, _stepSize, {
	      writable: true,
	      value: 0.01
	    });
	    Object.defineProperty(this, _value, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _defaultValue, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _containerWidth, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _scale, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _knob, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Main.Avatar.Editor');
	    main_core.Event.bind(minus, 'click', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _makeAStep)[_makeAStep](false);
	    });
	    main_core.Event.bind(plus, 'click', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _makeAStep)[_makeAStep](true);
	    });
	    this.stopMoving = this.stopMoving.bind(this);
	    this.move = this.move.bind(this);
	    main_core.Event.bind(knob, 'mousedown', event => {
	      this.startMoving(event);
	    });
	    if (defaultValue) {
	      this.setDefaultValue(defaultValue);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _scale)[_scale] = scale;
	    babelHelpers.classPrivateFieldLooseBase(this, _knob)[_knob] = knob;
	    this.reset();
	  }
	  setDefaultValue(defaultValue) {
	    babelHelpers.classPrivateFieldLooseBase(this, _defaultValue)[_defaultValue] = defaultValue > 0 && defaultValue <= 1 ? defaultValue : 0;
	    return this;
	  }
	  getValue() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _value)[_value];
	  }
	  reset() {
	    babelHelpers.classPrivateFieldLooseBase(this, _value)[_value] = babelHelpers.classPrivateFieldLooseBase(this, _defaultValue)[_defaultValue];
	    babelHelpers.classPrivateFieldLooseBase(this, _adjust)[_adjust]();
	  }
	  setValue(value) {
	    value = Math.ceil(value * 1000) / 1000;
	    if (value !== babelHelpers.classPrivateFieldLooseBase(this, _value)[_value] && value >= 0 && value <= 1) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value)[_value] = value;
	      babelHelpers.classPrivateFieldLooseBase(this, _adjust)[_adjust]();
	      this.emit('onChange', babelHelpers.classPrivateFieldLooseBase(this, _value)[_value] - babelHelpers.classPrivateFieldLooseBase(this, _defaultValue)[_defaultValue]);
	    }
	  }
	  move({
	    pageX
	  }) {
	    if (pageX > 0 && babelHelpers.classPrivateFieldLooseBase(this, _getContainerWidth)[_getContainerWidth]() > 0) {
	      const percent = (pageX - babelHelpers.classPrivateFieldLooseBase(this, _knob)[_knob].startPageX) / babelHelpers.classPrivateFieldLooseBase(this, _getContainerWidth)[_getContainerWidth]();
	      babelHelpers.classPrivateFieldLooseBase(this, _knob)[_knob].startPageX = pageX;
	      this.setValue(this.getValue() + percent);
	    }
	  }
	  startMoving({
	    pageX
	  }) {
	    babelHelpers.classPrivateFieldLooseBase(this, _knob)[_knob].startPageX = pageX;
	    main_core.Event.bind(document, 'mousemove', this.move);
	    main_core.Event.bind(document, 'mouseup', this.stopMoving);
	  }
	  stopMoving() {
	    main_core.Event.unbind(document, 'mousemove', this.move);
	    main_core.Event.unbind(document, 'mouseup', this.stopMoving);
	  }
	}
	function _getContainerWidth2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _containerWidth)[_containerWidth] > 0) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _containerWidth)[_containerWidth];
	  }
	  const containerPos = main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _scale)[_scale]);
	  let width = containerPos.width - main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _knob)[_knob]).width;
	  if (width > 0) {
	    babelHelpers.classPrivateFieldLooseBase(this, _containerWidth)[_containerWidth] = width;
	    return babelHelpers.classPrivateFieldLooseBase(this, _containerWidth)[_containerWidth];
	  }
	  return 0;
	}
	function _makeAStep2(increase) {
	  const value = Math.min(Math.max(this.getValue() + (increase === false ? -1 : 1) * babelHelpers.classPrivateFieldLooseBase(this, _stepSize)[_stepSize], 0), 1);
	  this.setValue(value);
	}
	function _adjust2() {
	  main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _knob)[_knob], {
	    style: {
	      left: [Math.ceil(babelHelpers.classPrivateFieldLooseBase(this, _getContainerWidth)[_getContainerWidth]() * this.getValue()), 'px'].join('')
	    }
	  });
	}

	var _canvas$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canvas");
	var _ctx$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ctx");
	var _container$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _activeMask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("activeMask");
	class CanvasMask extends main_core_events.EventEmitter {
	  constructor(container) {
	    super();
	    Object.defineProperty(this, _canvas$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _ctx$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _container$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _activeMask, {
	      writable: true,
	      value: null
	    });
	    this.setEventNamespace('Main.Avatar.Editor');
	    babelHelpers.classPrivateFieldLooseBase(this, _container$1)[_container$1] = container;
	    this.set = this.set.bind(this);
	    this.mask = this.mask.bind(this);
	    this.unmask = this.unmask.bind(this);
	  }
	  mask({
	    id,
	    src,
	    thumb
	  }) {
	    main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _container$1)[_container$1], 'background-image', 'url("' + main_core.Text.encode(thumb) + '")');
	    babelHelpers.classPrivateFieldLooseBase(this, _activeMask)[_activeMask] = {
	      id,
	      src
	    };
	    CanvasLoader.loadFile(src, this.set, this.unmask);
	  }
	  set(imageSource) {
	    //region set image
	    const trueK = Math.max(imageSource.width > 0 ? Options.maskSize / imageSource.width : 1, imageSource.height > 0 ? Options.maskSize / imageSource.height : 1);
	    const sourceS = parseInt(Options.maskSize / trueK);
	    const sourceX = parseInt((imageSource.width - sourceS) / 2);
	    const sourceY = parseInt((imageSource.height - sourceS) / 2);
	    this.getCanvas().width = Options.maskSize;
	    this.getCanvas().height = Options.maskSize;
	    this.getContext().clearRect(0, 0, Options.maskSize, Options.maskSize);
	    this.getContext().drawImage(imageSource, sourceX, sourceY, sourceS, sourceS, 0, 0, Options.maskSize, Options.maskSize);
	    //endregion
	  }

	  applyAndPack(imageSource) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _activeMask)[_activeMask] === null || imageSource.width < 100 || imageSource.height < 100) {
	      return Promise.reject({
	        message: 'Image for masking has not appropriate format',
	        code: 'bad image'
	      });
	    }
	    return new Promise(resolve => {
	      const trueK = Math.max(imageSource.width > 0 ? Options.maskSize / imageSource.width : 1, imageSource.height > 0 ? Options.maskSize / imageSource.height : 1);
	      const sourceS = parseInt(Options.maskSize / trueK);
	      const sourceX = parseInt((imageSource.width - sourceS) / 2);
	      const sourceY = parseInt((imageSource.height - sourceS) / 2);
	      const loader = CanvasLoader.getInstance();
	      loader.getCanvas().width = Options.maskSize;
	      loader.getCanvas().height = Options.maskSize;
	      loader.getContext().clearRect(0, 0, Options.maskSize, Options.maskSize);
	      loader.getContext().drawImage(imageSource, sourceX, sourceY, sourceS, sourceS, 0, 0, Options.maskSize, Options.maskSize);
	      loader.getContext().drawImage(this.getCanvas(), 0, 0, Options.maskSize, Options.maskSize);
	      loader.pack('image/png').then(blob => {
	        blob.name = 'mask.png';
	        blob.maskId = babelHelpers.classPrivateFieldLooseBase(this, _activeMask)[_activeMask].id;
	        resolve(blob, babelHelpers.classPrivateFieldLooseBase(this, _activeMask)[_activeMask].id);
	      });
	    });
	  }
	  getCanvas() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _canvas$2)[_canvas$2]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _canvas$2)[_canvas$2] = document.createElement('CANVAS');
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _canvas$2)[_canvas$2];
	  }
	  getContext() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _ctx$1)[_ctx$1]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _ctx$1)[_ctx$1] = this.getCanvas().getContext("2d");
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _ctx$1)[_ctx$1];
	  }
	  unmask() {
	    babelHelpers.classPrivateFieldLooseBase(this, _container$1)[_container$1].style.backgroundImage = '';
	    babelHelpers.classPrivateFieldLooseBase(this, _activeMask)[_activeMask] = null;
	  }
	}

	let _$9 = t => t,
	  _t$9;
	const hiddenCanvas = Symbol('hiddenCanvas');
	var _id$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _activeTabId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("activeTabId");
	var _previousActiveTabId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("previousActiveTabId");
	var _tabs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tabs");
	var _canvasMaster = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canvasMaster");
	var _canvasPreview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canvasPreview");
	var _canvasZooming = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canvasZooming");
	var _canvasMask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canvasMask");
	var _setActiveTabByDefault = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setActiveTabByDefault");
	var _setPreviousActiveTab = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setPreviousActiveTab");
	var _selectFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectFile");
	var _snapAPicture = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("snapAPicture");
	class Editor extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _snapAPicture, {
	      value: _snapAPicture2
	    });
	    Object.defineProperty(this, _selectFile, {
	      value: _selectFile2
	    });
	    Object.defineProperty(this, _setPreviousActiveTab, {
	      value: _setPreviousActiveTab2
	    });
	    Object.defineProperty(this, _setActiveTabByDefault, {
	      value: _setActiveTabByDefault2
	    });
	    Object.defineProperty(this, _id$2, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _activeTabId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _previousActiveTabId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tabs, {
	      writable: true,
	      value: new Map()
	    });
	    this.cache = new main_core.Cache.MemoryCache();
	    Object.defineProperty(this, _canvasMaster, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canvasPreview, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canvasZooming, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canvasMask, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Main.Avatar.Editor');
	    babelHelpers.classPrivateFieldLooseBase(this, _id$2)[_id$2] = Editor.justANumber++;
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    const tabsWithThePictureInside = [[CanvasTab, true], [MaskTab, options.enableMask, null]];
	    tabsWithThePictureInside.forEach(([tabClass, enabled, initialOptions]) => {
	      if (enabled === true && tabClass.isAvailable() !== false) {
	        const tab = new tabClass(initialOptions);
	        babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].set(tabClass.code, tab);
	        tab.subscribe('onSetMask', ({
	          data
	        }) => {
	          this.getContainer().setAttribute('data-badge-is-set', 'Y');
	          babelHelpers.classPrivateFieldLooseBase(this, _canvasMask)[_canvasMask].mask(data);
	        });
	        tab.subscribe('onUnsetMask', () => {
	          this.getContainer().removeAttribute('data-badge-is-set');
	          babelHelpers.classPrivateFieldLooseBase(this, _canvasMask)[_canvasMask].unmask();
	        });
	      }
	    });
	    const tabsWithConnectionsToThePicture = [[UploadTab, options.enableUpload, options.uploadTabOptions], [CameraTab, options.enableCamera, null]];
	    tabsWithConnectionsToThePicture.forEach(([tabClass, enabled, initialOptions]) => {
	      if (enabled !== false && tabClass.isAvailable() !== false) {
	        const tab = new tabClass(initialOptions);
	        babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].set(tabClass.code, tab);
	        tab.setParentTab(babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].get(CanvasTab.code));
	        tab.subscribe('onClickBack', () => {
	          this.setActiveTab(CanvasTab.code);
	        });
	        tab.subscribe('onSetFile', ({
	          data
	        }) => {
	          if (data instanceof Blob) {
	            babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].load(data);
	          } else {
	            babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].set(data);
	          }
	          this.setActiveTab(CanvasTab.code);
	        });
	        if (tab instanceof CameraTab) {
	          this.subscribe('onOpen', () => {
	            if (babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId] === CameraTab.code) {
	              tab.activate.call(tab);
	            }
	          });
	          this.subscribe('onClose', () => {
	            if (babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId] === CameraTab.code) {
	              tab.inactivate.call(tab);
	            }
	          });
	        }
	      }
	    });
	    let theFutureActiveTab = babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId];
	    babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].forEach((tab, tabId) => {
	      if (!theFutureActiveTab || babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].get(theFutureActiveTab).getPriority() < tab.getPriority()) {
	        theFutureActiveTab = tabId;
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _setActiveTabByDefault)[_setActiveTabByDefault](theFutureActiveTab);
	    main_core_events.EventEmitter.subscribe(this.getEventNamespace() + ':' + 'onEditMask', baseEvent => {
	      //TODO describe that true mask has changed and this view is not actual.
	    });
	    main_core_events.EventEmitter.subscribe(this.getEventNamespace() + ':' + 'onDeleteMask', baseEvent => {
	      //TODO describe that true mask has changed and this view is not actual.
	    });
	    this.init();
	  }
	  init() {
	    if (!this.getContainer().querySelector('canvas[data-bx-canvas="canvas"]')) {
	      return setTimeout(this.init.bind(this), 1);
	    }
	    const tabsWithConnectionsToThePicture = [UploadTab, CameraTab];
	    tabsWithConnectionsToThePicture.forEach(tabClass => {
	      this.getContainer().setAttribute('data-bx-' + tabClass.code + '-tab-available', babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].has(tabClass.code) ? 'Y' : 'N');
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster] = new CanvasMaster(this.getContainer().querySelector('canvas[data-bx-canvas="canvas"]'));
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasPreview)[_canvasPreview] = new CanvasPreview(this.getContainer().querySelector('canvas[data-bx-canvas="preview"]'));
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasZooming)[_canvasZooming] = new CanvasZooming({
	      knob: this.getContainer().querySelector('[data-bx-role="zoom-knob"]'),
	      scale: this.getContainer().querySelector('[data-bx-role="zoom-scale"]'),
	      plus: this.getContainer().querySelector('[data-bx-role="zoom-plus-button"]'),
	      minus: this.getContainer().querySelector('[data-bx-role="zoom-minus-button"]')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasMask)[_canvasMask] = babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].has(MaskTab.code) ? new CanvasMask(this.getContainer().querySelector('[data-bx-role="canvas-mask"]')) : false;
	    this.getContainer().querySelector('[data-bx-role="unset-canvas-mask"]').addEventListener('click', () => {
	      this.getContainer().removeAttribute('data-badge-is-set');
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasMask)[_canvasMask].unmask();
	      babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].get(MaskTab.code).unmask();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].subscribe('onLoad', event => {
	      this.getContainer().setAttribute('data-bx-canvas-load-status', 'loading');
	      this.emit('onChange');
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].subscribe('onReset', event => {
	      this.getContainer().setAttribute('data-bx-canvas-load-status', 'isnotset');
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasZooming)[_canvasZooming].reset();
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasPreview)[_canvasPreview].reset();
	      this.emit('onChange');
	    });
	    this.getContainer().setAttribute('data-bx-canvas-load-status', 'isnotset');
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].subscribe('onSetImage', ({
	      data: {
	        canvas
	      }
	    }) => {
	      this.getContainer().setAttribute('data-bx-canvas-load-status', 'set');
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasZooming)[_canvasZooming].reset();
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasPreview)[_canvasPreview].set(canvas);
	      this.emit('onSet');
	      this.emit('onChange');
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].subscribe('onMove', event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasPreview)[_canvasPreview].onMove(event);
	      this.emit('onChange');
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].subscribe('onScale', event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasPreview)[_canvasPreview].onScale(event);
	      this.emit('onChange');
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasZooming)[_canvasZooming].subscribe('onChange', ({
	      data
	    }) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].scale(data);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].subscribe('onError', ({
	      data
	    }) => {
	      this.getContainer().setAttribute('data-bx-canvas-load-status', 'errored');
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasZooming)[_canvasZooming].reset();
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasPreview)[_canvasPreview].reset();
	      this.getContainer().querySelector('[data-bx-role="tab-canvas-error"]').innerHTML = data;
	    });
	    this.emit('onReady');
	  }
	  ready(callback) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster]) {
	      callback.call();
	    } else {
	      this.subscribe('onReady', callback);
	    }
	    return this;
	  }
	  getId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id$2)[_id$2];
	  }
	  getContainer() {
	    return this.cache.remember('container', () => {
	      const res = main_core.Tag.render(_t$9 || (_t$9 = _$9`
				<div class="ui-avatar-editor__tab-wrapper ui-avatar-editor--scope">
					<input type="hidden" data-bx-active-tab="doesNotMatterForNowItIsAFile">
					<div class="ui-avatar-editor__tab-button-container" data-bx-role="headers" style="display:none;"></div>
					<div class="ui-avatar-editor__tab-container">
						<div data-bx-role="bodies"></div>
						<div class="ui-avatar-editor__tab-avatar-block">
							<div class="ui-avatar-editor__tab-avatar-inner">
								<div class="ui-avatar-editor__arrow-icon-container">
									<span class="ui-avatar-editor__arrow-icon"></span>
								</div>
								<div class="ui-avatar-editor__tab-avatar-image-container">
									<div data-bx-role="unset-canvas-mask" class="ui-avatar-editor__tab-avatar-image-not-allowed"></div>
									<span class="ui-avatar-editor__tab-avatar-image-item" data-bx-role="canvas-button">
										<div data-editor-role="preview-holder">
											<canvas data-bx-canvas="preview" height="165" width="165"></canvas>
										</div>
										<div class="ui-avatar-editor__tab-avatar-mask" data-bx-role="canvas-mask"></div>
									</span>
								</div>
								<div class="ui-avatar-editor__tab-avatar-desc-container">
									<span class="ui-avatar-editor__tab-avatar-desc-item"></span>
								</div>
							</div>
						</div>
					</div>
				</div>`));
	      const headers = res.querySelector('[data-bx-role="headers"]');
	      const bodies = res.querySelector('[data-bx-role="bodies"]');
	      Array.from(babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].entries()).forEach(([itemId, itemTab]) => {
	        main_core.Event.bind(itemTab.getHeaderContainer(), 'click', () => {
	          this.setActiveTab(itemId);
	        });
	        main_core.Dom.append(itemTab.getHeaderContainer(), headers);
	        main_core.Dom.append(itemTab.getBodyContainer(), bodies);
	      });
	      if (headers.querySelectorAll('[data-bx-state="visible"]').length > 1) {
	        headers.style.display = "block";
	      }
	      [[UploadTab.code, res.querySelector('[data-bx-role="button-add-picture"][data-bx-id="upload-file"]'), () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _selectFile)[_selectFile]();
	      }], [CameraTab.code, res.querySelector('[data-bx-role="button-add-picture"][data-bx-id="snap-picture"]'), () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _snapAPicture)[_snapAPicture]();
	      }]].forEach(([tabName, buttonNode, callback]) => {
	        if (babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].has(tabName)) {
	          main_core.Event.bind(buttonNode, 'click', callback);
	        } else {
	          main_core.Dom.remove(buttonNode);
	        }
	      });
	      return res;
	    });
	  }
	  setActiveTab(activeTab, isIt = false) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].has(activeTab)) {
	      return null;
	    }
	    const activeTabChangesCounter = this.cache.get('activeTabChangesCounter') || 0;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId] !== activeTab) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId] === null) {
	        babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId] = activeTab;
	      } else if (babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].has(babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId])) {
	        babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].get(babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId]).inactivate();
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId] === UploadTab.code || babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId] === CameraTab.code) {
	        babelHelpers.classPrivateFieldLooseBase(this, _previousActiveTabId)[_previousActiveTabId] = babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId];
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId] = activeTab;
	      babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].get(babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId]).activate();
	    }
	    this.cache.set('activeTabChangesCounter', activeTabChangesCounter + 1);
	    return babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].get(babelHelpers.classPrivateFieldLooseBase(this, _activeTabId)[_activeTabId]);
	  }
	  getTab(tabName) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].get(tabName);
	  }
	  loadJSON(data) {
	    return this.loadData(JSON.parse(data));
	  }
	  loadData(data) {
	    return new Promise((resolve, reject) => {
	      if (main_core.Type.isPlainObject(data) && data['src']) {
	        babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].load(data['src']).then(() => {
	          if (data['maskId'] && babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].has(MaskTab.code)) {
	            babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].get(MaskTab.code).maskById(data['maskId']);
	            babelHelpers.classPrivateFieldLooseBase(this, _setActiveTabByDefault)[_setActiveTabByDefault](MaskTab.code);
	          } else {
	            babelHelpers.classPrivateFieldLooseBase(this, _setActiveTabByDefault)[_setActiveTabByDefault](CanvasTab.code);
	          }
	          resolve(data['src'], this);
	        }).catch(reject);
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _setActiveTabByDefault)[_setActiveTabByDefault](UploadTab.code);
	        resolve(null, this);
	      }
	    });
	  }
	  loadSrc(src) {
	    return new Promise((resolve, reject) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].load(src).then(() => {
	        this.setActiveTab(babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].has(MaskTab.code) ? MaskTab.code : CanvasTab.code);
	        resolve(src, this);
	      }).catch(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setPreviousActiveTab)[_setPreviousActiveTab]();
	        reject(src, this);
	      });
	    });
	  }
	  reset() {
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].reset();
	    babelHelpers.classPrivateFieldLooseBase(this, _setPreviousActiveTab)[_setPreviousActiveTab]();
	    return this;
	  }
	  packBlobAndMask() {
	    return new Promise((resolve, reject) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].getBlob().then(({
	        blob
	      }) => {
	        var _loader$hiddenCanvas;
	        const loader = CanvasLoader.getInstance();
	        loader[hiddenCanvas] = (_loader$hiddenCanvas = loader[hiddenCanvas]) != null ? _loader$hiddenCanvas : document.createElement('canvas');
	        const canvas = loader[hiddenCanvas];
	        canvas.width = blob.width;
	        canvas.height = blob.height;
	        canvas.getContext('2d').drawImage(loader.getCanvas(), 0, 0);
	        if (!babelHelpers.classPrivateFieldLooseBase(this, _canvasMask)[_canvasMask]) {
	          return resolve({
	            blob,
	            canvas
	          });
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _canvasMask)[_canvasMask].applyAndPack(canvas).then((maskedBlob, maskId) => {
	          resolve({
	            blob,
	            maskedBlob,
	            maskId,
	            canvas
	          });
	        }).catch(() => {
	          resolve({
	            blob,
	            canvas
	          });
	        });
	      }).catch(error => {
	        return reject(error);
	      });
	    });
	  }
	  packBlob() {
	    return new Promise((resolve, reject) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].getBlob().then(resolve).catch(reject);
	    });
	  }
	  isEmpty() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].isEmpty();
	  }
	  isModified() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster].imageFrame.changed;
	  }
	  getCanvasEditor() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _canvasMaster)[_canvasMaster];
	  }
	  getCanvasZooming() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _canvasZooming)[_canvasZooming];
	  }
	  destroy() {}
	  static createInstance(id, options) {
	    if (this.repo.has(id)) {
	      this.repo.get(id).destroy();
	    }
	    const editor = new this(options);
	    if (document.querySelector('#' + id)) {
	      editor.ready(() => {
	        editor.loadJSON(document.querySelector('#' + id).getAttribute('data-bx-ui-avatar-editor-info'));
	      });
	    }
	    if (main_core.Type.isStringFilled(id)) {
	      this.repo.set(id, editor);
	    }
	    return editor;
	  }
	  static getInstanceById(id) {
	    if (this.repo.has(id)) {
	      return this.repo.get(id);
	    }
	    return null;
	  }
	  static getOrCreateInstanceById(id, options) {
	    return this.getInstanceById(id) || this.createInstance(...arguments);
	  }
	}
	function _setActiveTabByDefault2(activeTab) {
	  if (this.cache.get('activeTabChangesCounter') > 0) {
	    return;
	  }
	  this.setActiveTab(activeTab);
	  this.cache.delete('activeTabChangesCounter');
	}
	function _setPreviousActiveTab2() {
	  this.setActiveTab(babelHelpers.classPrivateFieldLooseBase(this, _previousActiveTabId)[_previousActiveTabId]);
	}
	function _selectFile2() {
	  // this.#canvasMaster.reset();
	  this.setActiveTab(UploadTab.code);
	  return this;
	}
	function _snapAPicture2() {
	  // this.#canvasMaster.reset();
	  this.setActiveTab(CameraTab.code);
	  return this;
	}
	Editor.justANumber = 0;
	Editor.repo = new Map();

	var _getPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopup");
	class EditorInPopup extends Editor {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getPopup, {
	      value: _getPopup2
	    });
	  }
	  hide() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().close();
	  }
	  show(tabCode) {
	    this.ready(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().show();
	      if (main_core.Type.isStringFilled(tabCode)) {
	        this.setActiveTab(tabCode);
	      }
	    });
	  }
	  showFile(url) {
	    this.ready(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().show();
	      if (url) {
	        return this.loadSrc(url);
	      }
	    });
	  }
	  apply() {
	    this.packBlobAndMask().then(({
	      blob,
	      maskedBlob,
	      maskId,
	      canvas
	    }) => {
	      if (blob instanceof Blob) {
	        if (maskId > 0) {
	          Backend.useRecently(maskId);
	        }
	        const ev = new main_core_events.BaseEvent({
	          compatData: [blob, canvas],
	          data: {
	            blob,
	            maskedBlob
	          }
	        });
	        main_core_events.EventEmitter.emit(this, 'onApply', ev, {
	          useGlobalNaming: true
	        });
	        this.emit('onApply', ev);
	      }
	    }).catch(error => {
	      console.log('error: ', error);
	    });
	  }
	  onApply(callback) {
	    this.subscribe('onApply', callback);
	    return this;
	  }
	  subscribeOnFormIsReady(fieldName, callback) {
	    this.subscribe('onApply', event => {
	      const formObj = new FormData();
	      const {
	        blob,
	        maskedBlob
	      } = event.getData();
	      formObj.append(fieldName, blob, blob['name']);
	      const maskedFileId = ['maskedFile', Editor.justANumber++].join(':');
	      formObj.append(main_core.Loc.getMessage('UI_AVATAR_MASK_REQUEST_FIELD_NAME') + fieldName, maskedFileId);
	      if (maskedBlob) {
	        formObj.append(main_core.Loc.getMessage('UI_AVATAR_MASK_REQUEST_FIELD_NAME') + '[' + maskedFileId + ']', maskedBlob, blob['name']);
	        formObj.append(main_core.Loc.getMessage('UI_AVATAR_MASK_REQUEST_FIELD_NAME') + '[' + maskedFileId + '][maskId]', maskedBlob['maskId']);
	        callback(new main_core_events.BaseEvent({
	          data: {
	            form: formObj,
	            blob,
	            maskedBlob,
	            maskId: maskedBlob['maskId']
	          }
	        }));
	      } else {
	        callback(new main_core_events.BaseEvent({
	          data: {
	            form: formObj,
	            blob
	          }
	        }));
	      }
	    });
	  }

	  //region Compatibility
	  click() {
	    this.show();
	  }
	  get popup() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]();
	  }
	  static isCameraAvailable() {
	    return CameraTab.isAvailable();
	  }
	  //endregion
	}
	function _getPopup2() {
	  this.cache.remember('okButton', () => {
	    const okButton = new ui_buttons.SaveButton({
	      onclick: () => {
	        if (okButton.getState() === ui_buttons.ButtonState.ACTIVE) {
	          this.apply();
	        }
	        this.hide();
	      }
	    });
	    if (this.isEmpty()) {
	      okButton.setState(ui_buttons.ButtonState.DISABLED);
	      this.subscribeOnce('onSet', () => {
	        okButton.setState(ui_buttons.ButtonState.ACTIVE);
	      });
	    }
	    return okButton;
	  });
	  return this.cache.remember('popup', () => {
	    return main_popup.PopupManager.create('popup_' + this.getId(), null, {
	      className: "ui-avatar-editor__popup",
	      autoHide: false,
	      lightShadow: true,
	      closeIcon: true,
	      closeByEsc: true,
	      titleBar: main_core.Loc.getMessage('JS_AVATAR_EDITOR_TITLE_BAR'),
	      content: this.getContainer(),
	      zIndex: BX.PopupWindowManager.getMaxZIndex() + 1,
	      overlay: {},
	      contentColor: "white",
	      contentNoPaddings: true,
	      bindOnResize: false,
	      draggable: true,
	      isScrollBlock: true,
	      buttons: [this.cache.remember('okButton'), new ui_buttons.CancelButton({
	        onclick: () => {
	          this.hide();
	        }
	      })],
	      events: {
	        onShow: () => {
	          this.emit('onOpen');
	        },
	        onClose: () => {
	          this.emit('onClose');
	        }
	      }
	    });
	  });
	}

	var _id$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _showSlider$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSlider");

	let currentEditor = EditorInPopup;
	/**
	 * @namespace BX.UI.AvatarEditor
	 */
	function createInstance() {
	  return currentEditor.createInstance(...arguments);
	}
	function isCameraAvailable() {
	  return currentEditor.isCameraAvailable();
	}
	function getInstanceById() {
	  return currentEditor.getInstanceById(...arguments);
	}
	function getOrCreateInstanceById() {
	  return currentEditor.getOrCreateInstanceById(...arguments);
	}
	CameraTab.check();
	const BX$1 = main_core.Reflection.namespace('BX');
	BX$1.AvatarEditor = currentEditor;

	exports.Editor = currentEditor;
	exports.MaskEditor = MaskEditor;
	exports.createInstance = createInstance;
	exports.getInstanceById = getInstanceById;
	exports.getOrCreateInstanceById = getOrCreateInstanceById;
	exports.isCameraAvailable = isCameraAvailable;

}((this.BX.UI.AvatarEditor = this.BX.UI.AvatarEditor || {}),BX,BX,BX,BX.UI.EntitySelector,BX.UI.Dialogs,BX,BX,BX.Event,BX.Main,BX.UI,BX.UI.SidePanel));
//# sourceMappingURL=main.avatar-editor.bundle.js.map
