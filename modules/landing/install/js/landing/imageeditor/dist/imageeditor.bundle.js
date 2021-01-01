this.BX = this.BX || {};
(function (exports,main_imageeditor,main_core) {
	'use strict';

	var assetPath = '/bitrix/js/main/imageeditor/external/photoeditorsdk/assets';
	var landingAssetsPath = '/bitrix/images/landing/imageeditor/assets';
	function pathResolver(path) {
	  if (path.includes('sites_recommended_transform_default')) {
	    var _path$split = path.split('sites_recommended_transform_default'),
	        _path$split2 = babelHelpers.slicedToArray(_path$split, 2),
	        fileName = _path$split2[1];

	    return "".concat(assetPath, "/ui/desktop/editor/controls/transform/ratios/imgly_transform_common_4-3").concat(fileName);
	  }

	  if (path.includes('sites_recommended_transform_retina')) {
	    var _path$split3 = path.split('sites_recommended_transform_retina'),
	        _path$split4 = babelHelpers.slicedToArray(_path$split3, 2),
	        _fileName = _path$split4[1];

	    return "".concat(assetPath, "/ui/desktop/editor/controls/transform/ratios/imgly_transform_common_4-3").concat(_fileName);
	  }

	  if (path.includes('landing-transform-3-4')) {
	    var _path$split5 = path.split('landing-transform-3-4'),
	        _path$split6 = babelHelpers.slicedToArray(_path$split5, 2),
	        _fileName2 = _path$split6[1];

	    return "".concat(landingAssetsPath, "/transform/ratios/landing-transform-3-4").concat(_fileName2);
	  }

	  if (path.includes('landing-transform-4-3')) {
	    var _path$split7 = path.split('landing-transform-4-3'),
	        _path$split8 = babelHelpers.slicedToArray(_path$split7, 2),
	        _fileName3 = _path$split8[1];

	    return "".concat(landingAssetsPath, "/transform/ratios/landing-transform-4-3").concat(_fileName3);
	  }

	  if (path.includes('landing-transform-9-16')) {
	    var _path$split9 = path.split('landing-transform-9-16'),
	        _path$split10 = babelHelpers.slicedToArray(_path$split9, 2),
	        _fileName4 = _path$split10[1];

	    return "".concat(landingAssetsPath, "/transform/ratios/landing-transform-9-16").concat(_fileName4);
	  }

	  if (path.includes('landing-transform-16-9')) {
	    var _path$split11 = path.split('landing-transform-16-9'),
	        _path$split12 = babelHelpers.slicedToArray(_path$split11, 2),
	        _fileName5 = _path$split12[1];

	    return "".concat(landingAssetsPath, "/transform/ratios/landing-transform-16-9").concat(_fileName5);
	  }

	  if (path.includes('landing-transform-1-1')) {
	    var _path$split13 = path.split('landing-transform-1-1'),
	        _path$split14 = babelHelpers.slicedToArray(_path$split13, 2),
	        _fileName6 = _path$split14[1];

	    return "".concat(landingAssetsPath, "/transform/ratios/landing-transform-1-1").concat(_fileName6);
	  }

	  if (path.includes('landing-transform-custom')) {
	    var _path$split15 = path.split('landing-transform-custom'),
	        _path$split16 = babelHelpers.slicedToArray(_path$split15, 2),
	        _fileName7 = _path$split16[1];

	    return "".concat(landingAssetsPath, "/transform/ratios/landing-transform-custom").concat(_fileName7);
	  }

	  return path;
	}

	function getMimeType(path) {
	  var imageExtension = BX.util.getExtension(path);
	  return "image/".concat(imageExtension === 'jpg' ? 'jpeg' : imageExtension);
	}

	var proxyPath = '/bitrix/tools/landing/proxy.php';

	var isValidDimensions = function isValidDimensions() {
	  var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	      width = _ref.width,
	      height = _ref.height;

	  return main_core.Type.isNumber(width) && main_core.Type.isNumber(height);
	};

	function buildOptions() {
	  var _ref2 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	      image = _ref2.image,
	      dimensions = _ref2.dimensions;

	  var preparedDimensions = {
	    width: dimensions.width || dimensions.maxWidth || dimensions.minWidth,
	    height: dimensions.height || dimensions.maxHeight || dimensions.minHeight
	  };
	  return {
	    image: image,
	    megapixels: 100,
	    proxy: proxyPath,
	    defaultControl: 'transform',
	    assets: {
	      resolver: pathResolver
	    },
	    export: {
	      format: getMimeType(image),
	      type: BX.Main.ImageEditor.renderType.BLOB,
	      quality: 1
	    },
	    controlsOptions: {
	      transform: {
	        categories: [{
	          identifier: 'sites_recommended',
	          defaultName: main_core.Loc.getMessage('LANDING_IMAGE_EDITOR_RECOMMENDED_RATIOS'),
	          ratios: [{
	            identifier: 'sites_recommended_transform_retina',
	            defaultName: main_core.Loc.getMessage('LANDING_IMAGE_EDITOR_TRANSFORM_DEFAULT'),
	            ratio: function () {
	              if (isValidDimensions(preparedDimensions)) {
	                return preparedDimensions.width / preparedDimensions.height;
	              }

	              return undefined;
	            }()
	          }, {
	            identifier: 'landing-transform-custom',
	            defaultName: main_core.Loc.getMessage('IMAGE_EDITOR_RATIOS_CUSTOM'),
	            ratio: '*'
	          }]
	        }, {
	          identifier: 'sites_other',
	          defaultName: main_core.Loc.getMessage('LANDING_IMAGE_EDITOR_OTHER_RATIOS'),
	          ratios: [{
	            identifier: 'landing-transform-1-1',
	            defaultName: '1:1',
	            ratio: 1
	          }, {
	            identifier: 'landing-transform-3-4',
	            defaultName: '3:4',
	            ratio: 3 / 4
	          }, {
	            identifier: 'landing-transform-4-3',
	            defaultName: '4:3',
	            ratio: 4 / 3
	          }, {
	            identifier: 'landing-transform-9-16',
	            defaultName: '9:16',
	            ratio: 9 / 16
	          }, {
	            identifier: 'landing-transform-16-9',
	            defaultName: '16:9',
	            ratio: 16 / 9
	          }]
	        }],
	        replaceCategories: false,
	        availableRatios: ['sites_recommended_transform_default', 'sites_recommended_transform_retina', 'landing-transform-3-4', 'landing-transform-4-3', 'landing-transform-9-16', 'landing-transform-16-9', 'landing-transform-1-1', 'landing-transform-custom']
	      }
	    }
	  };
	}

	function getFilename(path) {
	  return path.split('\\').pop().split('/').pop();
	}

	/**
	 * @memberOf BX.Landing
	 */

	var ImageEditor = /*#__PURE__*/function () {
	  function ImageEditor() {
	    babelHelpers.classCallCheck(this, ImageEditor);
	  }

	  babelHelpers.createClass(ImageEditor, null, [{
	    key: "edit",
	    value: function edit(options) {
	      var imageEditor = BX.Main.ImageEditor.getInstance();
	      var preparedOptions = buildOptions(options);
	      return imageEditor.edit(preparedOptions).then(function (file) {
	        file.name = decodeURIComponent(getFilename(options.image));
	        return file;
	      });
	    }
	  }]);
	  return ImageEditor;
	}();

	exports.ImageEditor = ImageEditor;

}((this.BX.Landing = this.BX.Landing || {}),BX.Main,BX));
//# sourceMappingURL=imageeditor.bundle.js.map
