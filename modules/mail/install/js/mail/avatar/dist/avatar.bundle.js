this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject;

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var Avatar = /*#__PURE__*/function () {
	  function Avatar() {
	    babelHelpers.classCallCheck(this, Avatar);
	  }

	  babelHelpers.createClass(Avatar, null, [{
	    key: "stringToHashCode",
	    value: function stringToHashCode(string) {
	      var hashCode = 0;

	      for (var i = 0; i < string.length; i++) {
	        hashCode = string.charCodeAt(i) + ((hashCode << 5) - hashCode);
	      }

	      return hashCode;
	    }
	  }, {
	    key: "alignChannelRangeColor",
	    value: function alignChannelRangeColor(chanelCode) {
	      if (chanelCode > 255) {
	        return 255;
	      } else if (chanelCode < 0) {
	        return 0;
	      } else {
	        return Math.ceil(chanelCode);
	      }
	    }
	  }, {
	    key: "hashToColor",
	    value: function hashToColor(hash) {
	      var maxIntensityAllChannels = 255 * 3;
	      var minIntensityAllChannels = 0;
	      var differenceCoefficientForGrayDetection = 0.20;
	      var r = (hash & 0xFF0000) >> 16;
	      var g = (hash & 0x00FF00) >> 8;
	      var b = hash & 0x0000FF;
	      var contrastRatioForPastelColors = 1.5;
	      var contrastRatioForDarkColors = 2.5;
	      var channelReductionCoefficientIfGray = 2;

	      if (maxIntensityAllChannels - (r + g + b) < 100) {
	        //Pastel colors or white
	        r /= contrastRatioForPastelColors;
	        g /= contrastRatioForPastelColors;
	        b /= contrastRatioForPastelColors;
	      } else if (r + g + b < 200 - minIntensityAllChannels) {
	        //Very dark colors
	        r *= contrastRatioForDarkColors;
	        g *= contrastRatioForDarkColors;
	        b *= contrastRatioForDarkColors;
	      }

	      var channels = [r, g, b];
	      channels.sort(function (a, b) {
	        return a - b;
	      });

	      if ((channels[channels.length - 1] - channels[0]) / channels[0] < differenceCoefficientForGrayDetection) {
	        //Shade of gray
	        g /= channelReductionCoefficientIfGray;
	      }

	      r = this.alignChannelRangeColor(r);
	      g = this.alignChannelRangeColor(g);
	      b = this.alignChannelRangeColor(b);
	      var color = "#" + ("0" + r.toString(16)).substr(-2) + ("0" + g.toString(16)).substr(-2) + ("0" + b.toString(16)).substr(-2);
	      return color.toUpperCase();
	    }
	  }, {
	    key: "stringToColor",
	    value: function stringToColor(name) {
	      return this.hashToColor(this.stringToHashCode(name));
	    }
	  }, {
	    key: "getInitials",
	    value: function getInitials(string, email) {
	      string = string.replace(/[0-9]|[-\u0026\u002f\u005c\u0023\u002c\u002b\u0028\u0029\u0024\u007e\u0025\u002e\u0027\u0022\u003a\u002a\u003f\u003c\u003e\u007b\u007d\u00ab\u00bb]/g, "");
	      string = string.replace(/^\s+|\s+$/g, '');
	      var names = string.split(' ');
	      var initials = names[0].substring(0, 1).toUpperCase();

	      if (names.length > 1) {
	        initials += names[names.length - 1].substring(0, 1).toUpperCase();
	      }

	      if (initials === '') {
	        initials = email[0].toUpperCase();
	      }

	      return initials;
	    }
	  }, {
	    key: "getAvatarData",
	    value: function getAvatarData() {
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        fullName: 'User Quest',
	        email: 'info@example.com'
	      };
	      return {
	        'abbreviation': this.getInitials(config['fullName'], config['email']),
	        'color': this.stringToColor(config['email'])
	      };
	    }
	  }, {
	    key: "build",
	    value: function build() {
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        size: 'small',
	        fullName: 'User Quest',
	        email: 'info@example.com'
	      };
	      var whiteList = new Set(['small', 'big']);

	      if (config['size'] === undefined || !whiteList.has(config['size'])) {
	        config['size'] = 'small';
	      }

	      var data = this.getAvatarData(config);
	      var avatar = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<span class=\"mail-ui-avatar mail-ui-avatar-", "\">", "</span>"])), config['size'], data['abbreviation']);
	      avatar.style.backgroundColor = data['color'];
	      return avatar;
	    }
	  }, {
	    key: "replaceElementWithAvatar",
	    value: function replaceElementWithAvatar(object, avatar) {
	      var parent = object.parentNode;
	      parent.insertBefore(avatar, object);
	      parent.removeChild(object);
	    }
	  }, {
	    key: "replaceTagsWithAvatars",
	    value: function replaceTagsWithAvatars() {
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        className: 'mail-ui-avatar'
	      };
	      var elements = document.getElementsByClassName(config['className']);

	      var _iterator = _createForOfIteratorHelper(elements),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var element = _step.value;
	          this.replaceElementWithAvatar(element, this.build({
	            fullName: element.getAttribute('user-name'),
	            email: element.getAttribute('email')
	          }));
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	  }]);
	  return Avatar;
	}();

	exports.Avatar = Avatar;

}((this.BX.Mail = this.BX.Mail || {}),BX));
//# sourceMappingURL=avatar.bundle.js.map
