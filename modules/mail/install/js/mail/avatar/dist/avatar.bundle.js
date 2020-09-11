this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"mail-ui-avatar mail-ui-avatar-", "\">", "</span>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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
	    key: "numberToRGB",
	    value: function numberToRGB(index) {
	      var color = (index & 0x00FFFFFF).toString(16);
	      color = color.toUpperCase();
	      return '00000'.substring(0, 6 - color.length) + color;
	    }
	  }, {
	    key: "stringToColor",
	    value: function stringToColor(name) {
	      return this.numberToRGB(this.stringToHashCode(name));
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

	      config['fullName'] = config['fullName'].replace(/[\u0026\u005c\u002f\u005c\u005c\u0023\u002c\u002b\u0028\u0029\u0024\u007e\u0025\u002e\u0027\u0022\u003a\u002a\u003f\u003c\u003e\u007b\u007d\u00ab\u00bb]/g, '').toUpperCase();
	      var brokenName = config['fullName'].split(' ');
	      var abbreviation = brokenName[0][0];

	      if (brokenName.length > 1) {
	        abbreviation += brokenName[1][0];
	      }

	      var avatar = main_core.Tag.render(_templateObject(), config['size'], abbreviation);
	      avatar.style.backgroundColor = this.stringToColor(config['email']);
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
