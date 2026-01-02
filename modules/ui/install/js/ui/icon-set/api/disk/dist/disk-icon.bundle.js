/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.IconSet = this.BX.UI.IconSet || {};
this.BX.UI.IconSet.Api = this.BX.UI.IconSet.Api || {};
(function (exports,main_core,ui_iconSet_api_core) {
	'use strict';

	const DiskIconType = Object.freeze({
	  png: 'png',
	  jpg: 'jpg',
	  jpeg: 'jpeg',
	  gif: 'gif',
	  bmp: 'bmp',
	  webp: 'webp',
	  svg: 'svg',
	  xml: 'xml',
	  pdf: 'pdf',
	  xls: 'xls',
	  xlsx: 'xlsx',
	  doc: 'doc',
	  docx: 'docx',
	  txt: 'txt',
	  ppt: 'ppt',
	  pptx: 'pptx',
	  rar: 'rar',
	  zip: 'zip',
	  gzip: 'gzip',
	  gz: 'gz',
	  archive: 'archive',
	  folder: 'folder',
	  folderGroup: 'folderGroup',
	  folderShared: 'folderShared',
	  folderCollab: 'folderCollab',
	  folder24: 'folder24',
	  folderPerson: 'folderPerson',
	  // Добавляем видео типы
	  mp4: 'mp4',
	  avi: 'avi',
	  mov: 'mov',
	  wmv: 'wmv',
	  webm: 'webm',
	  mkv: 'mkv',
	  video: 'video',
	  file: 'file'
	});
	const TypeIcon = Object.freeze({
	  [DiskIconType.file]: ui_iconSet_api_core.Disk.EMPTY,
	  [DiskIconType.png]: ui_iconSet_api_core.Disk.IMAGE,
	  [DiskIconType.jpg]: ui_iconSet_api_core.Disk.IMAGE,
	  [DiskIconType.jpeg]: ui_iconSet_api_core.Disk.IMAGE,
	  [DiskIconType.gif]: ui_iconSet_api_core.Disk.IMAGE,
	  [DiskIconType.bmp]: ui_iconSet_api_core.Disk.IMAGE,
	  [DiskIconType.webp]: ui_iconSet_api_core.Disk.IMAGE,
	  [DiskIconType.svg]: ui_iconSet_api_core.Disk.SVG,
	  [DiskIconType.xml]: ui_iconSet_api_core.Disk.XML,
	  [DiskIconType.pdf]: ui_iconSet_api_core.Disk.PDF,
	  [DiskIconType.xls]: ui_iconSet_api_core.Disk.XLS,
	  [DiskIconType.xlsx]: ui_iconSet_api_core.Disk.XLSX,
	  [DiskIconType.doc]: ui_iconSet_api_core.Disk.DOC,
	  [DiskIconType.docx]: ui_iconSet_api_core.Disk.DOCX,
	  [DiskIconType.txt]: ui_iconSet_api_core.Disk.TXT,
	  [DiskIconType.ppt]: ui_iconSet_api_core.Disk.PPT,
	  [DiskIconType.pptx]: ui_iconSet_api_core.Disk.PPTX,
	  [DiskIconType.rar]: ui_iconSet_api_core.Disk.RAR,
	  [DiskIconType.zip]: ui_iconSet_api_core.Disk.ZIP,
	  [DiskIconType.gzip]: ui_iconSet_api_core.Disk.ARCHIVE,
	  [DiskIconType.gz]: ui_iconSet_api_core.Disk.ARCHIVE,
	  [DiskIconType.archive]: ui_iconSet_api_core.Disk.ARCHIVE,
	  [DiskIconType.folder]: ui_iconSet_api_core.Disk.FOLDER,
	  [DiskIconType.folderGroup]: ui_iconSet_api_core.Disk.FOLDER_GROUP,
	  [DiskIconType.folderShared]: ui_iconSet_api_core.Disk.FOLDER_SHARED,
	  [DiskIconType.folderCollab]: ui_iconSet_api_core.Disk.FOLDER_COLLAB,
	  [DiskIconType.folder24]: ui_iconSet_api_core.Disk.FOLDER_24,
	  [DiskIconType.folderPerson]: ui_iconSet_api_core.Disk.FOLDER_PERSON,
	  [DiskIconType.mp4]: ui_iconSet_api_core.Disk.VIDEO || ui_iconSet_api_core.Disk.FILE,
	  [DiskIconType.avi]: ui_iconSet_api_core.Disk.VIDEO || ui_iconSet_api_core.Disk.FILE,
	  [DiskIconType.mov]: ui_iconSet_api_core.Disk.VIDEO || ui_iconSet_api_core.Disk.FILE,
	  [DiskIconType.wmv]: ui_iconSet_api_core.Disk.VIDEO || ui_iconSet_api_core.Disk.FILE,
	  [DiskIconType.webm]: ui_iconSet_api_core.Disk.VIDEO || ui_iconSet_api_core.Disk.FILE,
	  [DiskIconType.mkv]: ui_iconSet_api_core.Disk.VIDEO || ui_iconSet_api_core.Disk.FILE,
	  [DiskIconType.video]: ui_iconSet_api_core.Disk.VIDEO || ui_iconSet_api_core.Disk.FILE
	});
	const CompactTypeIcon = Object.freeze({
	  [DiskIconType.file]: ui_iconSet_api_core.Disk.EMPTY,
	  [DiskIconType.png]: ui_iconSet_api_core.DiskCompact.IMAGE,
	  [DiskIconType.jpg]: ui_iconSet_api_core.DiskCompact.IMAGE,
	  [DiskIconType.jpeg]: ui_iconSet_api_core.DiskCompact.IMAGE,
	  [DiskIconType.gif]: ui_iconSet_api_core.DiskCompact.IMAGE,
	  [DiskIconType.bmp]: ui_iconSet_api_core.DiskCompact.IMAGE,
	  [DiskIconType.webp]: ui_iconSet_api_core.DiskCompact.IMAGE,
	  [DiskIconType.svg]: ui_iconSet_api_core.DiskCompact.IMAGE,
	  [DiskIconType.xml]: ui_iconSet_api_core.DiskCompact.XML,
	  [DiskIconType.pdf]: ui_iconSet_api_core.DiskCompact.PDF,
	  [DiskIconType.xls]: ui_iconSet_api_core.DiskCompact.XLS,
	  [DiskIconType.xlsx]: ui_iconSet_api_core.DiskCompact.XLSX,
	  [DiskIconType.doc]: ui_iconSet_api_core.DiskCompact.DOC,
	  [DiskIconType.docx]: ui_iconSet_api_core.DiskCompact.DOCX,
	  [DiskIconType.txt]: ui_iconSet_api_core.DiskCompact.TXT,
	  [DiskIconType.ppt]: ui_iconSet_api_core.DiskCompact.PPT,
	  [DiskIconType.pptx]: ui_iconSet_api_core.DiskCompact.PPTX,
	  [DiskIconType.rar]: ui_iconSet_api_core.DiskCompact.RAR,
	  [DiskIconType.zip]: ui_iconSet_api_core.DiskCompact.ZIP,
	  [DiskIconType.gzip]: ui_iconSet_api_core.DiskCompact.ARCHIVE,
	  [DiskIconType.gz]: ui_iconSet_api_core.DiskCompact.ARCHIVE,
	  [DiskIconType.archive]: ui_iconSet_api_core.DiskCompact.ARCHIVE,
	  [DiskIconType.folder]: ui_iconSet_api_core.DiskCompact.FOLDER,
	  [DiskIconType.folderGroup]: ui_iconSet_api_core.DiskCompact.FOLDER_GROUP,
	  [DiskIconType.folderShared]: ui_iconSet_api_core.DiskCompact.FOLDER_SHARED,
	  [DiskIconType.folderCollab]: ui_iconSet_api_core.DiskCompact.FOLDER_COLLAB,
	  [DiskIconType.folder24]: ui_iconSet_api_core.DiskCompact.FOLDER_24,
	  [DiskIconType.folderPerson]: ui_iconSet_api_core.DiskCompact.FOLDER_PERSON,
	  [DiskIconType.mp4]: ui_iconSet_api_core.DiskCompact.VIDEO || ui_iconSet_api_core.DiskCompact.FILE,
	  [DiskIconType.avi]: ui_iconSet_api_core.DiskCompact.VIDEO || ui_iconSet_api_core.DiskCompact.FILE,
	  [DiskIconType.mov]: ui_iconSet_api_core.DiskCompact.VIDEO || ui_iconSet_api_core.DiskCompact.FILE,
	  [DiskIconType.wmv]: ui_iconSet_api_core.DiskCompact.VIDEO || ui_iconSet_api_core.DiskCompact.FILE,
	  [DiskIconType.webm]: ui_iconSet_api_core.DiskCompact.VIDEO || ui_iconSet_api_core.DiskCompact.FILE,
	  [DiskIconType.mkv]: ui_iconSet_api_core.DiskCompact.VIDEO || ui_iconSet_api_core.DiskCompact.FILE,
	  [DiskIconType.video]: ui_iconSet_api_core.DiskCompact.VIDEO || ui_iconSet_api_core.DiskCompact.FILE
	});

	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _observer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("observer");
	var _pendingElements = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pendingElements");
	class LazyLoadManager {
	  constructor() {
	    Object.defineProperty(this, _observer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pendingElements, {
	      writable: true,
	      value: new WeakMap()
	    });
	    // eslint-disable-next-line @bitrix24/bitrix24-rules/no-io-without-polyfill
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer] = new IntersectionObserver(entries => {
	      entries.forEach(entry => {
	        if (entry.isIntersecting) {
	          const data = babelHelpers.classPrivateFieldLooseBase(this, _pendingElements)[_pendingElements].get(entry.target);
	          if (data) {
	            data.callback();
	            babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].unobserve(entry.target);
	            babelHelpers.classPrivateFieldLooseBase(this, _pendingElements)[_pendingElements].delete(entry.target);
	          }
	        }
	      });
	    }, {
	      root: null,
	      rootMargin: '50px',
	      threshold: 0.1
	    });
	  }
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(LazyLoadManager, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(LazyLoadManager, _instance)[_instance] = new LazyLoadManager();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(LazyLoadManager, _instance)[_instance];
	  }
	  observe(element, callback) {
	    babelHelpers.classPrivateFieldLooseBase(this, _pendingElements)[_pendingElements].set(element, {
	      callback
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].observe(element);
	  }
	}
	Object.defineProperty(LazyLoadManager, _instance, {
	  writable: true,
	  value: void 0
	});

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _size = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("size");
	var _type = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("type");
	var _previewUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("previewUrl");
	var _alt = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("alt");
	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _responsive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("responsive");
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _icon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icon");
	var _resizeObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resizeObserver");
	var _appendContentToWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendContentToWrapper");
	var _renderWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderWrapper");
	var _getIconType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getIconType");
	var _createIconInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createIconInstance");
	var _renderIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderIcon");
	var _appendIconWithLazyPreview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendIconWithLazyPreview");
	var _loadPreviewImage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadPreviewImage");
	var _updateSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateSize");
	var _replaceIconElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("replaceIconElement");
	var _updateIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateIcon");
	var _updateContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateContent");
	var _updateWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateWrapper");
	var _setupResizeObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setupResizeObserver");
	var _destroyResizeObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("destroyResizeObserver");
	var _onContainerResize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onContainerResize");
	var _getCurrentIconType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentIconType");
	var _updateIconForResponsive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateIconForResponsive");
	var _validateOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validateOptions");
	var _isExistingIconType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isExistingIconType");
	class DiskIcon {
	  constructor(_options = {}) {
	    var _options$alt, _options$title, _options$responsive;
	    Object.defineProperty(this, _isExistingIconType, {
	      value: _isExistingIconType2
	    });
	    Object.defineProperty(this, _validateOptions, {
	      value: _validateOptions2
	    });
	    Object.defineProperty(this, _updateIconForResponsive, {
	      value: _updateIconForResponsive2
	    });
	    Object.defineProperty(this, _getCurrentIconType, {
	      value: _getCurrentIconType2
	    });
	    Object.defineProperty(this, _onContainerResize, {
	      value: _onContainerResize2
	    });
	    Object.defineProperty(this, _destroyResizeObserver, {
	      value: _destroyResizeObserver2
	    });
	    Object.defineProperty(this, _setupResizeObserver, {
	      value: _setupResizeObserver2
	    });
	    Object.defineProperty(this, _updateWrapper, {
	      value: _updateWrapper2
	    });
	    Object.defineProperty(this, _updateContent, {
	      value: _updateContent2
	    });
	    Object.defineProperty(this, _updateIcon, {
	      value: _updateIcon2
	    });
	    Object.defineProperty(this, _replaceIconElement, {
	      value: _replaceIconElement2
	    });
	    Object.defineProperty(this, _updateSize, {
	      value: _updateSize2
	    });
	    Object.defineProperty(this, _loadPreviewImage, {
	      value: _loadPreviewImage2
	    });
	    Object.defineProperty(this, _appendIconWithLazyPreview, {
	      value: _appendIconWithLazyPreview2
	    });
	    Object.defineProperty(this, _renderIcon, {
	      value: _renderIcon2
	    });
	    Object.defineProperty(this, _createIconInstance, {
	      value: _createIconInstance2
	    });
	    Object.defineProperty(this, _getIconType, {
	      value: _getIconType2
	    });
	    Object.defineProperty(this, _renderWrapper, {
	      value: _renderWrapper2
	    });
	    Object.defineProperty(this, _appendContentToWrapper, {
	      value: _appendContentToWrapper2
	    });
	    Object.defineProperty(this, _size, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _type, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _previewUrl, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _alt, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _responsive, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icon, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _resizeObserver, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _validateOptions)[_validateOptions](_options);
	    babelHelpers.classPrivateFieldLooseBase(this, _size)[_size] = _options.size;
	    babelHelpers.classPrivateFieldLooseBase(this, _type)[_type] = babelHelpers.classPrivateFieldLooseBase(this, _isExistingIconType)[_isExistingIconType](_options.type) ? _options.type : DiskIconType.file;
	    babelHelpers.classPrivateFieldLooseBase(this, _previewUrl)[_previewUrl] = _options.previewUrl;
	    babelHelpers.classPrivateFieldLooseBase(this, _alt)[_alt] = (_options$alt = _options.alt) != null ? _options$alt : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = (_options$title = _options.title) != null ? _options$title : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive] = (_options$responsive = _options.responsive) != null ? _options$responsive : false;
	  }
	  static render(options) {
	    const icon = new DiskIcon(options);
	    return icon.render();
	  }
	  render() {
	    const wrapper = babelHelpers.classPrivateFieldLooseBase(this, _renderWrapper)[_renderWrapper]();
	    babelHelpers.classPrivateFieldLooseBase(this, _appendContentToWrapper)[_appendContentToWrapper](wrapper);
	    babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = wrapper;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setupResizeObserver)[_setupResizeObserver]();
	    }
	    return wrapper;
	  }
	  setType(type) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isExistingIconType)[_isExistingIconType](type)) {
	      console.warn(`DiskIcon: Type "${type}" is not supported`);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _type)[_type] = type;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateIcon)[_updateIcon]();
	  }
	  setPreviewUrl(previewUrl) {
	    babelHelpers.classPrivateFieldLooseBase(this, _previewUrl)[_previewUrl] = previewUrl;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateContent)[_updateContent]();
	  }
	  setAlt(alt) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	      return;
	    }
	    const img = babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].querySelector('img');
	    if (img) {
	      main_core.Dom.attr(img, 'alt', alt);
	    }
	  }
	  setTitle(title) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	      return;
	    }
	    main_core.Dom.attr(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], 'title', title);
	  }
	  setSize(size) {
	    if (!main_core.Type.isNumber(size) || size <= 0) {
	      console.warn('DiskIcon: size must be the positive number');
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _size)[_size] = size;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateSize)[_updateSize]();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateIcon)[_updateIcon]();
	  }
	  setResponsive(responsive) {
	    babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive] = responsive;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateWrapper)[_updateWrapper]();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateIcon)[_updateIcon]();
	    if (babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setupResizeObserver)[_setupResizeObserver]();
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _destroyResizeObserver)[_destroyResizeObserver]();
	    }
	  }

	  // @deprecated used only for vue component
	  renderOnNode(target) {
	    const wrapper = babelHelpers.classPrivateFieldLooseBase(this, _renderWrapper)[_renderWrapper]();
	    main_core.Dom.clean(target);
	    [...target.attributes].forEach(attr => {
	      target.removeAttribute(attr.name);
	    });
	    [...wrapper.attributes].forEach(attr => {
	      target.setAttribute(attr.name, attr.value);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = target;
	    babelHelpers.classPrivateFieldLooseBase(this, _appendContentToWrapper)[_appendContentToWrapper](target);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setupResizeObserver)[_setupResizeObserver]();
	    }
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _destroyResizeObserver)[_destroyResizeObserver]();
	    babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon] = null;
	  }
	}
	function _appendContentToWrapper2(wrapper) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _previewUrl)[_previewUrl]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _appendIconWithLazyPreview)[_appendIconWithLazyPreview](wrapper);
	  } else {
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderIcon)[_renderIcon](), wrapper);
	  }
	}
	function _renderWrapper2() {
	  const wrapperClass = babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive] ? 'ui-icon-set_disk-icon --responsive' : 'ui-icon-set_disk-icon';
	  const wrapper = main_core.Tag.render(_t || (_t = _`<span title="${0}" class="${0}"></span>`), babelHelpers.classPrivateFieldLooseBase(this, _title)[_title], wrapperClass);
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive]) {
	    main_core.Dom.style(wrapper, {
	      width: `${babelHelpers.classPrivateFieldLooseBase(this, _size)[_size]}px`,
	      height: `${babelHelpers.classPrivateFieldLooseBase(this, _size)[_size]}px`
	    });
	  }
	  return wrapper;
	}
	function _getIconType2(width) {
	  const effectiveWidth = width != null ? width : babelHelpers.classPrivateFieldLooseBase(this, _size)[_size];
	  return effectiveWidth >= 40 ? TypeIcon[babelHelpers.classPrivateFieldLooseBase(this, _type)[_type]] : CompactTypeIcon[babelHelpers.classPrivateFieldLooseBase(this, _type)[_type]];
	}
	function _createIconInstance2(size, responsive) {
	  const effectiveSize = size != null ? size : babelHelpers.classPrivateFieldLooseBase(this, _size)[_size];
	  const effectiveResponsive = responsive != null ? responsive : babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive];
	  return new ui_iconSet_api_core.Icon({
	    icon: babelHelpers.classPrivateFieldLooseBase(this, _getIconType)[_getIconType](effectiveSize),
	    size: effectiveSize,
	    responsive: effectiveResponsive
	  });
	}
	function _renderIcon2(size, responsive) {
	  const icon = babelHelpers.classPrivateFieldLooseBase(this, _createIconInstance)[_createIconInstance](size, responsive);
	  return icon.render();
	}
	function _appendIconWithLazyPreview2(container) {
	  const icon = babelHelpers.classPrivateFieldLooseBase(this, _renderIcon)[_renderIcon]();
	  const iconWrapper = main_core.Tag.render(_t2 || (_t2 = _`<span class="ui-icon-set_disk-icon__temp-icon">${0}</span>`), icon);

	  // Создаем placeholder для превью
	  const img = main_core.Tag.render(_t3 || (_t3 = _`
			<img
				class="ui-icon-set_disk-icon__img --hidden"
				alt="${0}"
			/>
		`), babelHelpers.classPrivateFieldLooseBase(this, _alt)[_alt]);
	  main_core.Dom.append(iconWrapper, container);
	  main_core.Dom.append(img, container);
	  const lazyManager = LazyLoadManager.getInstance();
	  lazyManager.observe(container, () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _loadPreviewImage)[_loadPreviewImage](img, iconWrapper);
	  });
	}
	function _loadPreviewImage2(img, iconWrapper) {
	  main_core.Dom.attr(img, 'src', babelHelpers.classPrivateFieldLooseBase(this, _previewUrl)[_previewUrl]);
	  main_core.bind(img, 'load', () => {
	    main_core.Dom.removeClass(img, '--hidden');
	    main_core.Dom.hide(iconWrapper);
	  }, {
	    once: true
	  });
	  main_core.bind(img, 'error', () => {
	    main_core.Dom.removeClass(iconWrapper, 'ui-icon-set_disk-icon__temp-icon');
	  }, {
	    once: true
	  });
	}
	function _updateSize2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	    return;
	  }
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], {
	    width: `${babelHelpers.classPrivateFieldLooseBase(this, _size)[_size]}px`,
	    height: `${babelHelpers.classPrivateFieldLooseBase(this, _size)[_size]}px`
	  });
	}
	function _replaceIconElement2(size, responsive) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	    return;
	  }
	  const newIconElement = babelHelpers.classPrivateFieldLooseBase(this, _renderIcon)[_renderIcon](size, responsive);
	  const existingIconElement = babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].querySelector('.ui-icon-set');
	  if (existingIconElement && existingIconElement.parentNode) {
	    main_core.Dom.replace(existingIconElement, newIconElement);
	  } else {
	    main_core.Dom.append(newIconElement, babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon] = babelHelpers.classPrivateFieldLooseBase(this, _createIconInstance)[_createIconInstance](size, responsive);
	}
	function _updateIcon2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] || !babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _replaceIconElement)[_replaceIconElement](babelHelpers.classPrivateFieldLooseBase(this, _size)[_size], babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive]);
	}
	function _updateContent2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	    return;
	  }
	  main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	  babelHelpers.classPrivateFieldLooseBase(this, _appendContentToWrapper)[_appendContentToWrapper](babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	}
	function _updateWrapper2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive]) {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], '--responsive');
	    main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], {
	      width: '',
	      height: ''
	    });
	  } else {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], '--responsive');
	    main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], {
	      width: `${babelHelpers.classPrivateFieldLooseBase(this, _size)[_size]}px`,
	      height: `${babelHelpers.classPrivateFieldLooseBase(this, _size)[_size]}px`
	    });
	  }
	}
	function _setupResizeObserver2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] || babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver] = new ResizeObserver(entries => {
	    for (const entry of entries) {
	      const {
	        width,
	        height
	      } = entry.contentRect;
	      babelHelpers.classPrivateFieldLooseBase(this, _onContainerResize)[_onContainerResize](Math.min(width, height));
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver].observe(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	}
	function _destroyResizeObserver2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver].disconnect();
	    babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver] = null;
	  }
	}
	function _onContainerResize2(width) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _responsive)[_responsive]) {
	    return;
	  }
	  const currentIconType = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentIconType)[_getCurrentIconType]();
	  const expectedIconType = babelHelpers.classPrivateFieldLooseBase(this, _getIconType)[_getIconType](width);
	  if (currentIconType !== expectedIconType) {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateIconForResponsive)[_updateIconForResponsive](width);
	  }
	}
	function _getCurrentIconType2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]) {
	    return '';
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon].getIcon ? babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon].getIcon() : '';
	}
	function _updateIconForResponsive2(containerWidth) {
	  babelHelpers.classPrivateFieldLooseBase(this, _replaceIconElement)[_replaceIconElement](containerWidth, true);
	}
	function _validateOptions2(options) {
	  let isValid = true;
	  if (main_core.Type.isUndefined(options.type) !== false && !babelHelpers.classPrivateFieldLooseBase(this, _isExistingIconType)[_isExistingIconType](options.type)) {
	    console.warn(`DiskIcon: Type "${options.type}" is not supported`);
	    isValid = false;
	  }
	  if (options.responsive === false && main_core.Type.isUndefined(options.size) !== false && (!main_core.Type.isNumber(options.size) || options.size <= 0)) {
	    console.warn('DiskIcon: size must be the positive number');
	    isValid = false;
	  }
	  if (main_core.Type.isNil(options.previewUrl) === false && !main_core.Type.isString(options.previewUrl)) {
	    console.warn('DiskIcon: previewUrl must be a string or null');
	    isValid = false;
	  }
	  if (main_core.Type.isUndefined(options.alt) !== false && !main_core.Type.isString(options.alt)) {
	    console.warn('DiskIcon: alt must be a string');
	    isValid = false;
	  }
	  if (main_core.Type.isUndefined(options.title) !== false && !main_core.Type.isString(options.title)) {
	    console.warn('DiskIcon: title must be a string');
	    isValid = false;
	  }
	  if (main_core.Type.isUndefined(options.responsive) !== false && !main_core.Type.isBoolean(options.responsive)) {
	    console.warn('DiskIcon: responsive must be a boolean');
	    isValid = false;
	  }
	  return isValid;
	}
	function _isExistingIconType2(type) {
	  return Object.values(DiskIconType).includes(type);
	}

	const DiskIconType$1 = DiskIconType;

	// @vue/component
	const BDiskIcon = {
	  props: {
	    type: {
	      type: String,
	      required: false,
	      default: 'file'
	    },
	    size: {
	      type: Number,
	      required: false
	    },
	    previewUrl: {
	      type: String,
	      required: false,
	      default: null
	    },
	    alt: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    title: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    responsive: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      diskIcon: null
	    };
	  },
	  watch: {
	    type() {
	      this.updateDiskIcon();
	    },
	    size() {
	      this.updateDiskIcon();
	    },
	    previewUrl() {
	      this.updateDiskIcon();
	    },
	    alt() {
	      this.updateDiskIcon();
	    },
	    title() {
	      this.updateDiskIcon();
	    },
	    responsive() {
	      this.updateDiskIcon();
	    }
	  },
	  mounted() {
	    this.initDiskIcon();
	  },
	  beforeUnmount() {
	    if (this.diskIcon) {
	      this.diskIcon.destroy();
	    }
	  },
	  methods: {
	    initDiskIcon() {
	      const options = {
	        type: this.type,
	        size: this.size,
	        previewUrl: this.previewUrl || null,
	        alt: this.alt,
	        title: this.title,
	        responsive: this.responsive
	      };
	      this.diskIcon = new DiskIcon(options);
	      this.diskIcon.renderOnNode(this.$el);
	    },
	    updateDiskIcon() {
	      if (!this.diskIcon) {
	        return;
	      }
	      this.diskIcon.setType(this.type);
	      if (this.responsive === false) {
	        this.diskIcon.setSize(this.size);
	      }
	      this.diskIcon.setPreviewUrl(this.previewUrl || null);
	      this.diskIcon.setAlt(this.alt);
	      this.diskIcon.setTitle(this.title);
	      this.diskIcon.setResponsive(this.responsive);
	    }
	  },
	  template: '<div></div>'
	};

	var diskIcon = /*#__PURE__*/Object.freeze({
		DiskIconType: DiskIconType$1,
		BDiskIcon: BDiskIcon
	});

	exports.Vue = diskIcon;
	exports.DiskIconType = DiskIconType;
	exports.DiskIcon = DiskIcon;

}((this.BX.UI.IconSet.Api.Disk = this.BX.UI.IconSet.Api.Disk || {}),BX,BX.UI.IconSet));
//# sourceMappingURL=disk-icon.bundle.js.map
