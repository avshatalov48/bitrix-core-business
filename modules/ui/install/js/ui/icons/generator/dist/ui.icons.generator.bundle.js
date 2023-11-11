/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Icons = this.BX.UI.Icons || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI.Icons.Generator}
	 */
	var FileType = function FileType() {
	  babelHelpers.classCallCheck(this, FileType);
	};
	babelHelpers.defineProperty(FileType, "ARCHIVE", 'archive');
	babelHelpers.defineProperty(FileType, "MEDIA", "media");
	babelHelpers.defineProperty(FileType, "PICTURE", "picture");
	babelHelpers.defineProperty(FileType, "AUDIO", 'audio');
	babelHelpers.defineProperty(FileType, "NONE", "none");

	var _Object$freeze;
	var pictureIcon = "<svg width=\"47\" height=\"46\" viewBox=\"0 0 47 46\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\">\n\t<g clip-path=\"url(#clip0_8133_191353)\">\n\t\t<path opacity=\"0.9\" d=\"M36.159 28.8509L10 54.3294L67 54.8122L40.3454 28.8509C39.1805 27.7164 37.3238 27.7164 36.159 28.8509Z\" fill=\"#7FDEFC\"/>\n\t\t<path opacity=\"0.9\" d=\"M14.5661 21.8695L-20 56.7756H54L18.7904 21.8695C17.6209 20.7102 15.7356 20.7102 14.5661 21.8695Z\" fill=\"#2FC6F6\"/>\n\t\t<circle cx=\"31\" cy=\"10\" r=\"6\" fill=\"white\"/>\n\t</g>\n\t<defs>\n\t\t<clipPath id=\"clip0_8133_191353\">\n\t\t\t<rect width=\"47\" height=\"46\" rx=\"2\" fill=\"white\"/>\n\t\t</clipPath>\n\t</defs>\n</svg>";
	var audioIcon = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"38\" height=\"38\" viewBox=\"0 0 38 38\" fill=\"none\">\n<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M15.0893 14.4632L12.8875 14.6591V24.7692C12.232 24.4188 11.4597 24.2166 10.6329 24.2166C8.25839 24.2166 6.3335 25.8843 6.3335 27.9415C6.3335 29.9987 8.25839 31.6663 10.6329 31.6663C13.005 31.6663 15.0856 30.0019 15.0893 27.9475M15.0893 14.4632V27.9415V14.4632Z\" fill=\"#11A9D9\"/>\n<path d=\"M14.045 7.78021C13.3883 7.84485 12.886 8.42959 12.886 9.1295V14.6697L27.8159 13.1786V23.1779C27.1607 22.828 26.3889 22.6261 25.5627 22.6261C23.1883 22.6261 21.2634 24.2937 21.2634 26.3509C21.2634 28.4081 23.1883 30.0758 25.5627 30.0758C27.9041 30.0758 30.0278 28.4543 30.0804 26.4367L30.0815 26.3509V12.9772L30.0835 12.977V7.68821C30.0835 6.89039 29.4372 6.26523 28.6886 6.33891L14.045 7.78021Z\" fill=\"#11A9D9\"/>\n</svg>";
	var getSvgFromString = function getSvgFromString(svg) {
	  var parser = new DOMParser();
	  var doc = parser.parseFromString(svg, "image/svg+xml");
	  return doc.querySelector('svg');
	};
	var FileTypeIcon = Object.freeze((_Object$freeze = {}, babelHelpers.defineProperty(_Object$freeze, FileType.PICTURE, function () {
	  return getSvgFromString(pictureIcon);
	}), babelHelpers.defineProperty(_Object$freeze, FileType.AUDIO, function () {
	  return getSvgFromString(audioIcon);
	}), _Object$freeze));
	var getFileTypeIcon = function getFileTypeIcon(fileType) {
	  if (FileTypeIcon[fileType]) {
	    return FileTypeIcon[fileType]();
	  }
	  return null;
	};

	var _docColorByType, _angleColorByType;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var presets = Object.freeze({
	  'RAR': {
	    color: '#7eab34',
	    fileType: 'archive'
	  },
	  'ZIP': {
	    color: '#ac5fbd',
	    fileType: 'archive'
	  },
	  'GZIP': {
	    color: '#8F44A0',
	    fileType: 'archive'
	  },
	  'GZ': {
	    color: '#BA7ED5',
	    fileType: 'archive'
	  },
	  'JPG': {
	    color: '#1D95A5',
	    fileType: 'picture'
	  },
	  'JPEG': {
	    color: '#1D95A5',
	    fileType: 'picture'
	  },
	  'WEBP': {
	    color: '#0D7683',
	    fileType: 'picture'
	  },
	  'GIF': {
	    color: '#2E85D3',
	    fileType: 'picture'
	  },
	  'PNG': {
	    color: '#1CC09D',
	    fileType: 'picture'
	  },
	  'MOV': {
	    color: '#CB8600',
	    fileType: 'media'
	  },
	  '3GP': {
	    color: '#ACB75F',
	    fileType: 'media'
	  },
	  'WEBM': {
	    color: '#ACB75F',
	    fileType: 'media'
	  },
	  'AVI': {
	    color: '#FF5752',
	    fileType: 'media'
	  },
	  'MP3': {
	    color: '#0B66C3',
	    fileType: 'audio'
	  },
	  'WAV': '#1D62AA',
	  'PHP': '#746781',
	  'PDF': '#d73b41',
	  'PSD': '#7e8997',
	  'TXT': '#9ba4ae',
	  'DOC': '#2c77b1',
	  'DOCX': '#2c77b1',
	  'PPT': '#e89e00',
	  'PPTX': '#e89e00',
	  'XLS': '#54b51e',
	  'XLSX': '#54b51e',
	  'none': '#7e8997'
	});
	var docColorByType = (_docColorByType = {}, babelHelpers.defineProperty(_docColorByType, FileType.PICTURE, '#C3F0FF'), babelHelpers.defineProperty(_docColorByType, FileType.AUDIO, '#C3F0FF'), _docColorByType);
	var angleColorByType = (_angleColorByType = {}, babelHelpers.defineProperty(_angleColorByType, FileType.PICTURE, '#00789E'), babelHelpers.defineProperty(_angleColorByType, FileType.AUDIO, '#00789E'), _angleColorByType);
	var fileTypesWithoutShowingExtension = [FileType.PICTURE, FileType.AUDIO];

	/**
	 * @namespace {BX.UI.Icons.Generator}
	 */
	var _getBaseIcon = /*#__PURE__*/new WeakSet();
	var _getBaseIconParams = /*#__PURE__*/new WeakSet();
	var _addFileExtensionToIcon = /*#__PURE__*/new WeakSet();
	var _addFileTypeIcon = /*#__PURE__*/new WeakSet();
	var _isShowFileExtension = /*#__PURE__*/new WeakSet();
	var _createSvgElement = /*#__PURE__*/new WeakSet();
	var FileIcon = /*#__PURE__*/function () {
	  function FileIcon(iconOptions) {
	    babelHelpers.classCallCheck(this, FileIcon);
	    _classPrivateMethodInitSpec(this, _createSvgElement);
	    _classPrivateMethodInitSpec(this, _isShowFileExtension);
	    _classPrivateMethodInitSpec(this, _addFileTypeIcon);
	    _classPrivateMethodInitSpec(this, _addFileExtensionToIcon);
	    _classPrivateMethodInitSpec(this, _getBaseIconParams);
	    _classPrivateMethodInitSpec(this, _getBaseIcon);
	    var options = main_core.Type.isPlainObject(iconOptions) ? iconOptions : {};
	    this.name = null;
	    this.fileType = null;
	    this.align = main_core.Type.isStringFilled(options.align) ? options.align : "left";
	    this.color = null;
	    this.size = main_core.Type.isNumber(options.size) ? options.size : null;
	    this.mini = main_core.Type.isBoolean(options.mini) ? options.mini : false;
	    this.setColor(options.color);
	    this.setName(options.name);
	    this.setType(this.fileType);
	  }
	  babelHelpers.createClass(FileIcon, [{
	    key: "setColor",
	    value: function setColor(color) {
	      var preset = presets[this.name];
	      if (preset && this.color === null) {
	        this.color = main_core.Type.isStringFilled(preset) ? preset : preset.color;
	      } else if (main_core.Type.isStringFilled(color)) {
	        this.color = color;
	      }
	    }
	  }, {
	    key: "setName",
	    value: function setName(name) {
	      if (main_core.Type.isStringFilled(name) && name.length < 6) {
	        this.name = name.toUpperCase();
	        this.setColor();
	        this.setType();
	      } else {
	        this.name = null;
	      }
	    }
	  }, {
	    key: "setType",
	    value: function setType(fileType) {
	      var preset = presets[this.name];
	      if (preset && this.fileType === null) {
	        this.fileType = main_core.Type.isStringFilled(preset.fileType) ? preset.fileType : null;
	      } else {
	        this.fileType = fileType;
	      }
	    }
	  }, {
	    key: "generate",
	    value: function generate() {
	      var icon = _classPrivateMethodGet(this, _getBaseIcon, _getBaseIcon2).call(this);
	      _classPrivateMethodGet(this, _addFileTypeIcon, _addFileTypeIcon2).call(this, icon);
	      if (this.name && _classPrivateMethodGet(this, _isShowFileExtension, _isShowFileExtension2).call(this)) {
	        _classPrivateMethodGet(this, _addFileExtensionToIcon, _addFileExtensionToIcon2).call(this, icon);
	      }
	      return icon;
	    }
	  }, {
	    key: "generateURI",
	    value: function generateURI() {
	      return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(this.generate().outerHTML);
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      if (main_core.Type.isDomNode(node)) {
	        return node.appendChild(this.generate());
	      }
	      return null;
	    }
	  }]);
	  return FileIcon;
	}();
	function _getBaseIcon2() {
	  var _classPrivateMethodGe = _classPrivateMethodGet(this, _getBaseIconParams, _getBaseIconParams2).call(this),
	    viewBox = _classPrivateMethodGe.viewBox,
	    size = _classPrivateMethodGe.size;
	  var container = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'svg', {
	    'width': size ? "".concat(size, "px") : '100%',
	    'viewBox': viewBox,
	    'style': 'display:block',
	    'fill': 'none'
	  });
	  var sheetIcon = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'path', {
	    'fill-rule': "evenodd",
	    'clip-rule': 'evenodd',
	    'd': "\n\t\t\t\tM 0 5\n\t\t\t\tc 0 0 0 -4 5 -5\n\t\t\t\tH 63\n\t\t\t\tl 26 28\n\t\t\t\tv 82\n\t\t\t\tc 0 0 0 4 -5 5\n\t\t\t\th -79\n\t\t\t\tc 0 0 -4 0 -5 -5\n\t\t\t\tZ",
	    'fill': docColorByType[this.fileType] || '#e5e8eb'
	  });
	  var sheetAngleIconStartPosX = 63;
	  var sheetAngleIcon = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'path', {
	    'fill-rule': "evenodd",
	    'clip-rule': 'evenodd',
	    'opacity': '0.3',
	    'd': "\n\t\t\t\tM ".concat(sheetAngleIconStartPosX, " 0\n\t\t\t \tL ").concat(sheetAngleIconStartPosX + 26, " 28\n\t\t\t \tH ").concat(sheetAngleIconStartPosX + 3, "\n\t\t\t \tC ").concat(sheetAngleIconStartPosX + 3, " 28 ").concat(sheetAngleIconStartPosX, " 28 ").concat(sheetAngleIconStartPosX, " 25\n\t\t\t \tV ").concat(sheetAngleIconStartPosX, "\n\t\t\t \tZ"),
	    'fill': angleColorByType[this.fileType] || '#535c69'
	  });
	  container.appendChild(sheetIcon);
	  container.appendChild(sheetAngleIcon);
	  return container;
	}
	function _getBaseIconParams2() {
	  var iconSize;
	  var viewBoxParam = '0 0 100 117';
	  if (this.name) {
	    if (this.align === 'center') {
	      viewBoxParam = '-12 0 112 117';
	      iconSize = this.size + this.size * .24;
	    } else {
	      iconSize = this.size + this.size * .12;
	    }
	  } else {
	    if (this.align === 'right') {
	      viewBoxParam = '0 0 100 117';
	      iconSize = this.size + this.size * .12;
	    } else {
	      viewBoxParam = '0 0 90 117';
	      iconSize = this.size;
	    }
	  }
	  return {
	    size: iconSize,
	    viewBox: viewBoxParam
	  };
	}
	function _addFileExtensionToIcon2(container) {
	  var nameNode = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'svg', {
	    'width': "65",
	    'height': "33",
	    'x': '35',
	    'y': '53'
	  });
	  var rect = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'rect', {
	    'width': "100%",
	    'height': '33',
	    'x': '0',
	    'y': '0',
	    'fill': this.color ? this.color : "#7e8997",
	    'rx': 2,
	    'ry': 2
	  });
	  var text = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'text', {
	    "x": "50%",
	    "y": "54%",
	    "dominant-baseline": "middle",
	    "fill": "#fff",
	    "text-anchor": "middle",
	    "style": 'color:#fff;' + 'font-family: "OpenSans-Semibold", "Open Sans", Helvetica, Arial, sans-serif;' + 'font-weight: 500;' + 'font-size: 23px;' + 'line-height: 25px;'
	  }, this.name);
	  var textNode = document.createTextNode(this.name);
	  text.appendChild(textNode);
	  nameNode.appendChild(rect);
	  nameNode.appendChild(text);
	  container.appendChild(nameNode);
	}
	function _addFileTypeIcon2(container) {
	  if (this.fileType === FileType.ARCHIVE) {
	    var iconType = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'path', {
	      'fill-rule': 'evenodd',
	      'clip-rule': 'evenodd',
	      'd': 'M22.3214 0H27.7486V1.96417H22.3214V0ZM22.3214 3.57123H27.7486V5.5354H22.3214V3.57123ZM22.3214 7.14246H27.7486V9.10663H22.3214V7.14246ZM22.3214 10.5351H27.7486V12.4993H22.3214V10.5351ZM22.3214 14.1063H27.7486V16.0705H22.3214V14.1063ZM22.3214 17.6776H27.7486V19.6417H22.3214V17.6776ZM22.3214 21.2488H27.7486V23.213H22.3214V21.2488ZM22.3214 24.82H27.7486V26.7842H22.3214V24.82ZM22.3214 28.3913H27.7486V30.3554H22.3214V28.3913ZM22.3214 31.7839H27.7486V33.7481H22.3214V31.7839ZM22.3214 35.3552H27.7486V37.3193H22.3214V35.3552ZM22.3214 38.9264H27.7486V40.8906H22.3214V38.9264ZM29.4993 1.19209e-07H34.9265V1.96417H29.4993V1.19209e-07ZM29.4993 3.57123H34.9265V5.5354H29.4993V3.57123ZM29.4993 7.14246H34.9265V9.10663H29.4993V7.14246ZM29.4993 10.5351H34.9265V12.4993H29.4993V10.5351ZM29.4993 14.1063H34.9265V16.0705H29.4993V14.1063ZM29.4993 17.6776H34.9265V19.6417H29.4993V17.6776ZM29.4993 21.2488H34.9265V23.213H29.4993V21.2488ZM29.4993 24.82H34.9265V26.7842H29.4993V24.82ZM29.4993 28.3913H34.9265V30.3554H29.4993V28.3913ZM29.4993 31.7839H34.9265V33.7481H29.4993V31.7839ZM29.4993 35.3552H34.9265V37.3193H29.4993V35.3552ZM29.4993 38.9264H34.9265V40.8906H29.4993V38.9264Z',
	      'fill': "#b9bec4"
	    });
	    container.appendChild(iconType);
	  } else if (this.fileType === FileType.MEDIA) {
	    var _iconType = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'path', {
	      'fill-rule': 'evenodd',
	      'clip-rule': 'evenodd',
	      'd': 'M77.6785 90.873V42.6587H11.6071V90.873H77.6785ZM13.799 85.5088L13.848 85.5087L13.8488 88.6073L23.7776 88.6064L23.7781 88.6579H13.799V85.5088ZM65.4958 88.6064V44.9261L75.4346 44.9263L75.4335 48.0269L67.4498 48.0273V53.0045L75.4335 53.0032V48.0269L75.4826 48.0273V44.8779H65.4974L65.4958 44.9261H23.7776L23.7781 44.8759H13.799V48.0253H13.848V53.0016H21.8248V48.0253H13.848L13.8488 44.9263L23.7776 44.9261V88.6064H65.4958ZM75.4335 85.5053L75.4346 88.6073L65.4958 88.6064L65.4974 88.6541H75.4826V85.5067L75.4335 85.5053ZM75.4335 85.5053V80.5274L67.4498 80.5275V85.5067L75.4335 85.5053ZM13.848 85.5087H21.8248V80.5307H13.848V85.5087ZM53.3524 67.5326L39.5164 56.1499V56.2398L39.4786 56.2087V79.2022L53.4164 67.7065L53.279 67.593L53.3524 67.5326ZM13.848 72.404H21.8248V77.3819H13.848V72.404ZM67.4498 72.403L75.4335 72.4022V77.3803H67.4498V72.403ZM13.848 64.2772H21.8248V69.2551H13.848V64.2772ZM67.4498 64.2765L75.4335 64.2756V69.2551L67.4498 69.2556V64.2765ZM13.848 56.1504H21.8248V61.1283H13.848V56.1504ZM67.4498 56.1519L75.4335 56.1504V61.1283L67.4498 61.1292V56.1519Z',
	      'fill': "#b9bec4"
	    });
	    container.appendChild(_iconType);
	  } else if (this.fileType === FileType.PICTURE) {
	    var iconContainer = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'g', {
	      style: "transform: scale(1.65) translate(3px, 19px);"
	    });
	    iconContainer.appendChild(getFileTypeIcon(this.fileType));
	    container.appendChild(iconContainer);
	  } else if (this.fileType === FileType.AUDIO) {
	    var _iconContainer = _classPrivateMethodGet(this, _createSvgElement, _createSvgElement2).call(this, 'g', {
	      style: "transform: scale(1.65) translate(7px, 19px);"
	    });
	    _iconContainer.appendChild(getFileTypeIcon(this.fileType));
	    container.appendChild(_iconContainer);
	  }
	}
	function _isShowFileExtension2() {
	  return !fileTypesWithoutShowingExtension.includes(this.fileType);
	}
	function _createSvgElement2(tag, params) {
	  var element;
	  if (tag === "svg") {
	    element = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
	    element.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
	  } else {
	    element = document.createElementNS('http://www.w3.org/2000/svg', tag);
	  }
	  for (var property in params) {
	    element.setAttributeNS(null, property, params[property]);
	  }
	  return element;
	}

	exports.FileIcon = FileIcon;
	exports.FileType = FileType;
	exports.FileIconPresets = presets;

}((this.BX.UI.Icons.Generator = this.BX.UI.Icons.Generator || {}),BX));
//# sourceMappingURL=ui.icons.generator.bundle.js.map
