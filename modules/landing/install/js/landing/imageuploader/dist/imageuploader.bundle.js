this.BX = this.BX || {};
(function (exports,main_core,landing_imagecompressor,landing_backend) {
	'use strict';

	function renameX(filename, x) {
	  var name = filename.replace(/@[1-9]x/, '');
	  return name ? name.replace(/\.[^.]+$/, "@".concat(x, "x.").concat(BX.util.getExtension(name))) : name;
	}

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	/**
	 * @memberOf BX.Landing
	 */

	var ImageUploader = /*#__PURE__*/function () {
	  function ImageUploader(options) {
	    babelHelpers.classCallCheck(this, ImageUploader);
	    this.options = _objectSpread({
	      uploadParams: {},
	      additionalParams: {},
	      dimensions: {},
	      sizes: ['1x']
	    }, options);
	  }

	  babelHelpers.createClass(ImageUploader, [{
	    key: "setSizes",
	    value: function setSizes(sizes) {
	      this.options.sizes = sizes;
	      return this;
	    }
	  }, {
	    key: "getDimensions",
	    value: function getDimensions() {
	      var dimensions = Object.entries(this.options.dimensions);
	      return this.options.sizes.map(function (size) {
	        return Number.parseInt(size);
	      }).filter(function (size) {
	        return main_core.Type.isNumber(size);
	      }).map(function (size) {
	        return dimensions.reduce(function (acc, _ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              key = _ref2[0],
	              value = _ref2[1];

	          acc[key] = value * size;
	          return acc;
	        }, {});
	      });
	    }
	  }, {
	    key: "upload",
	    value: function upload(file) {
	      var _this = this;

	      var additionalParams = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      return Promise.all(this.getDimensions().map(function (dimensions) {
	        var isSvg = _this.options.allowSvg && main_core.Type.isStringFilled(file.type) && file.type.includes('svg');

	        if (isSvg) {
	          return file;
	        }

	        return landing_imagecompressor.ImageCompressor.compress(file, dimensions);
	      })).then(function (files) {
	        var uploadParams = _objectSpread(_objectSpread(_objectSpread({}, _this.options.uploadParams), _this.options.additionalParams), additionalParams);

	        var uploads = files.map(function (currentFile, index) {
	          var name = currentFile.name;
	          Object.defineProperty(currentFile, 'name', {
	            get: function get() {
	              return renameX(name, index + 1);
	            },
	            configurable: true
	          });
	          return landing_backend.Backend.getInstance().upload(currentFile, uploadParams);
	        });
	        return Promise.all(uploads);
	      });
	    }
	  }]);
	  return ImageUploader;
	}();

	exports.ImageUploader = ImageUploader;

}((this.BX.Landing = this.BX.Landing || {}),BX,BX.Landing,BX.Landing));
//# sourceMappingURL=imageuploader.bundle.js.map
