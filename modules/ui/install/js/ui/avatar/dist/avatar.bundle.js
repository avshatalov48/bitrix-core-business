/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var AvatarBase = /*#__PURE__*/function () {
	  function AvatarBase(options) {
	    var _this$options$userNam, _Type$isString, _this$options$title, _this$options$picPath;
	    babelHelpers.classCallCheck(this, AvatarBase);
	    this.options = _objectSpread(_objectSpread({}, this.getDefaultOptions()), main_core.Type.isPlainObject(options) ? options : {});
	    this.node = {
	      avatar: null,
	      initials: null,
	      svgUserPic: null,
	      svgMask: null,
	      svgDefaultUserPic: null
	    };
	    this.events = null;
	    this.title = null;
	    this.userName = (_this$options$userNam = this.options.userName) !== null && _this$options$userNam !== void 0 ? _this$options$userNam : this.title;
	    this.initials = main_core.Type.isString(this.options.initials) ? this.options.initials : null;
	    this.picPath = null;
	    this.userpicPath = (_Type$isString = main_core.Type.isString(this.options.userpicPath)) !== null && _Type$isString !== void 0 ? _Type$isString : this.picPath;
	    this.unicId = null;
	    this.events = {};
	    this.size = null;
	    this.baseColor = null;
	    this.borderColor = null;
	    this.borderInnerColor = null;
	    this.setMask();
	    this.setBaseColor(this.options.baseColor);
	    this.setBorderColor(this.options.borderColor);
	    this.setBorderInnerColor(this.options.borderInnerColor);
	    this.setTitle((_this$options$title = this.options.title) !== null && _this$options$title !== void 0 ? _this$options$title : this.options.userName);
	    if (this.initials) {
	      this.setInitials(this.initials);
	    }
	    this.setSize(this.options.size);
	    this.setPic((_this$options$picPath = this.options.picPath) !== null && _this$options$picPath !== void 0 ? _this$options$picPath : this.options.userpicPath);
	    this.setEvents(this.options.events);
	    if (!this.title && !this.initials && !this.picPath) {
	      this.setDefaultUserPic();
	    }
	  }
	  babelHelpers.createClass(AvatarBase, [{
	    key: "setEvents",
	    value: function setEvents(events) {
	      var _this = this;
	      if (main_core.Type.isObject(events)) {
	        this.events = events;
	        var eventKeys = Object.keys(this.events);
	        var _loop = function _loop() {
	          var event = _eventKeys[_i];
	          main_core.Event.bind(_this.getContainer(), event, function () {
	            _this.events[event]();
	          });
	          main_core.Dom.addClass(_this.getContainer(), '--cursor-pointer');
	        };
	        for (var _i = 0, _eventKeys = eventKeys; _i < _eventKeys.length; _i++) {
	          _loop();
	        }
	      }
	      return this;
	    }
	  }, {
	    key: "hexToRgb",
	    value: function hexToRgb(hex) {
	      if (!/^#([\dA-Fa-f]{3}){1,2}$/.test(hex)) {
	        return hex;
	      }
	      var color = hex.length === 4 ? [hex[1], hex[1], hex[2], hex[2], hex[3], hex[3]]
	      // eslint-disable-next-line unicorn/no-useless-spread
	      : babelHelpers.toConsumableArray(hex.slice(1));
	      var rgb = parseInt(color.join(''), 16);
	      return "".concat(rgb >> 16 & 255, ", ").concat(rgb >> 8 & 255, ", ").concat(rgb & 255);
	    }
	  }, {
	    key: "setBorderColor",
	    value: function setBorderColor(colorCode) {
	      if (main_core.Type.isString(colorCode)) {
	        this.borderColor = colorCode;
	        main_core.Dom.style(this.getContainer(), '--ui-avatar-border-color', this.borderColor);
	      }
	      return this;
	    }
	  }, {
	    key: "setBorderInnerColor",
	    value: function setBorderInnerColor(colorCode) {
	      if (main_core.Type.isString(colorCode)) {
	        this.borderInnerColor = colorCode;
	        main_core.Dom.style(this.getContainer(), '--ui-avatar-border-inner-color', this.borderInnerColor);
	      }
	    }
	  }, {
	    key: "setBaseColor",
	    value: function setBaseColor(colorCode) {
	      if (main_core.Type.isString(colorCode)) {
	        this.baseColor = this.hexToRgb(colorCode);
	        main_core.Dom.style(this.getContainer(), '--ui-avatar-base-color-rgb', this.baseColor);
	      }
	      return this;
	    }
	  }, {
	    key: "getUnicId",
	    value: function getUnicId() {
	      if (!this.unicId) {
	        this.unicId = "ui-avatar-".concat(Date.now(), "-").concat(Math.random().toString(36).slice(2, 11));
	      }
	      return this.unicId;
	    }
	  }, {
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {};
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(text) {
	      if (main_core.Type.isString(text) && text.trim().length > 0) {
	        this.title = text;
	        if (this.title.length > 0) {
	          this.getContainer().setAttribute('title', this.title);
	          var validSymbolsPattern = /(?:[ 0-9A-Za-z\xAA\xB2\xB3\xB5\xB9\xBA\xBC-\xBE\xC0-\xD6\xD8-\xF6\xF8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0560-\u0588\u05D0-\u05EA\u05EF-\u05F2\u0620-\u064A\u0660-\u0669\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07C0-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u0860-\u086A\u0870-\u0887\u0889-\u088E\u08A0-\u08C9\u0904-\u0939\u093D\u0950\u0958-\u0961\u0966-\u096F\u0971-\u0980\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09E6-\u09F1\u09F4-\u09F9\u09FC\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A66-\u0A6F\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0AE6-\u0AEF\u0AF9\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B66-\u0B6F\u0B71-\u0B77\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0BE6-\u0BF2\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D\u0C58-\u0C5A\u0C5D\u0C60\u0C61\u0C66-\u0C6F\u0C78-\u0C7E\u0C80\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDD\u0CDE\u0CE0\u0CE1\u0CE6-\u0CEF\u0CF1\u0CF2\u0D04-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D54-\u0D56\u0D58-\u0D61\u0D66-\u0D78\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0DE6-\u0DEF\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E50-\u0E59\u0E81\u0E82\u0E84\u0E86-\u0E8A\u0E8C-\u0EA3\u0EA5\u0EA7-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0ED0-\u0ED9\u0EDC-\u0EDF\u0F00\u0F20-\u0F33\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F-\u1049\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u1090-\u1099\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1369-\u137C\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16EE-\u16F8\u1700-\u1711\u171F-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u17E0-\u17E9\u17F0-\u17F9\u1810-\u1819\u1820-\u1878\u1880-\u1884\u1887-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191E\u1946-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u19D0-\u19DA\u1A00-\u1A16\u1A20-\u1A54\u1A80-\u1A89\u1A90-\u1A99\u1AA7\u1B05-\u1B33\u1B45-\u1B4C\u1B50-\u1B59\u1B83-\u1BA0\u1BAE-\u1BE5\u1C00-\u1C23\u1C40-\u1C49\u1C4D-\u1C7D\u1C80-\u1C88\u1C90-\u1CBA\u1CBD-\u1CBF\u1CE9-\u1CEC\u1CEE-\u1CF3\u1CF5\u1CF6\u1CFA\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2070\u2071\u2074-\u2079\u207F-\u2089\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2150-\u2189\u2460-\u249B\u24EA-\u24FF\u2776-\u2793\u2C00-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2CFD\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005-\u3007\u3021-\u3029\u3031-\u3035\u3038-\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312F\u3131-\u318E\u3192-\u3195\u31A0-\u31BF\u31F0-\u31FF\u3220-\u3229\u3248-\u324F\u3251-\u325F\u3280-\u3289\u32B1-\u32BF\u3400-\u4DBF\u4E00-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA62B\uA640-\uA66E\uA67F-\uA69D\uA6A0-\uA6EF\uA717-\uA71F\uA722-\uA788\uA78B-\uA7CA\uA7D0\uA7D1\uA7D3\uA7D5-\uA7D9\uA7F2-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA830-\uA835\uA840-\uA873\uA882-\uA8B3\uA8D0-\uA8D9\uA8F2-\uA8F7\uA8FB\uA8FD\uA8FE\uA900-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF-\uA9D9\uA9E0-\uA9E4\uA9E6-\uA9FE\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA50-\uAA59\uAA60-\uAA76\uAA7A\uAA7E-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB69\uAB70-\uABE2\uABF0-\uABF9\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF10-\uFF19\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC]|\uD800[\uDC00-\uDC0B\uDC0D-\uDC26\uDC28-\uDC3A\uDC3C\uDC3D\uDC3F-\uDC4D\uDC50-\uDC5D\uDC80-\uDCFA\uDD07-\uDD33\uDD40-\uDD78\uDD8A\uDD8B\uDE80-\uDE9C\uDEA0-\uDED0\uDEE1-\uDEFB\uDF00-\uDF23\uDF2D-\uDF4A\uDF50-\uDF75\uDF80-\uDF9D\uDFA0-\uDFC3\uDFC8-\uDFCF\uDFD1-\uDFD5]|\uD801[\uDC00-\uDC9D\uDCA0-\uDCA9\uDCB0-\uDCD3\uDCD8-\uDCFB\uDD00-\uDD27\uDD30-\uDD63\uDD70-\uDD7A\uDD7C-\uDD8A\uDD8C-\uDD92\uDD94\uDD95\uDD97-\uDDA1\uDDA3-\uDDB1\uDDB3-\uDDB9\uDDBB\uDDBC\uDE00-\uDF36\uDF40-\uDF55\uDF60-\uDF67\uDF80-\uDF85\uDF87-\uDFB0\uDFB2-\uDFBA]|\uD802[\uDC00-\uDC05\uDC08\uDC0A-\uDC35\uDC37\uDC38\uDC3C\uDC3F-\uDC55\uDC58-\uDC76\uDC79-\uDC9E\uDCA7-\uDCAF\uDCE0-\uDCF2\uDCF4\uDCF5\uDCFB-\uDD1B\uDD20-\uDD39\uDD80-\uDDB7\uDDBC-\uDDCF\uDDD2-\uDE00\uDE10-\uDE13\uDE15-\uDE17\uDE19-\uDE35\uDE40-\uDE48\uDE60-\uDE7E\uDE80-\uDE9F\uDEC0-\uDEC7\uDEC9-\uDEE4\uDEEB-\uDEEF\uDF00-\uDF35\uDF40-\uDF55\uDF58-\uDF72\uDF78-\uDF91\uDFA9-\uDFAF]|\uD803[\uDC00-\uDC48\uDC80-\uDCB2\uDCC0-\uDCF2\uDCFA-\uDD23\uDD30-\uDD39\uDE60-\uDE7E\uDE80-\uDEA9\uDEB0\uDEB1\uDF00-\uDF27\uDF30-\uDF45\uDF51-\uDF54\uDF70-\uDF81\uDFB0-\uDFCB\uDFE0-\uDFF6]|\uD804[\uDC03-\uDC37\uDC52-\uDC6F\uDC71\uDC72\uDC75\uDC83-\uDCAF\uDCD0-\uDCE8\uDCF0-\uDCF9\uDD03-\uDD26\uDD36-\uDD3F\uDD44\uDD47\uDD50-\uDD72\uDD76\uDD83-\uDDB2\uDDC1-\uDDC4\uDDD0-\uDDDA\uDDDC\uDDE1-\uDDF4\uDE00-\uDE11\uDE13-\uDE2B\uDE3F\uDE40\uDE80-\uDE86\uDE88\uDE8A-\uDE8D\uDE8F-\uDE9D\uDE9F-\uDEA8\uDEB0-\uDEDE\uDEF0-\uDEF9\uDF05-\uDF0C\uDF0F\uDF10\uDF13-\uDF28\uDF2A-\uDF30\uDF32\uDF33\uDF35-\uDF39\uDF3D\uDF50\uDF5D-\uDF61]|\uD805[\uDC00-\uDC34\uDC47-\uDC4A\uDC50-\uDC59\uDC5F-\uDC61\uDC80-\uDCAF\uDCC4\uDCC5\uDCC7\uDCD0-\uDCD9\uDD80-\uDDAE\uDDD8-\uDDDB\uDE00-\uDE2F\uDE44\uDE50-\uDE59\uDE80-\uDEAA\uDEB8\uDEC0-\uDEC9\uDF00-\uDF1A\uDF30-\uDF3B\uDF40-\uDF46]|\uD806[\uDC00-\uDC2B\uDCA0-\uDCF2\uDCFF-\uDD06\uDD09\uDD0C-\uDD13\uDD15\uDD16\uDD18-\uDD2F\uDD3F\uDD41\uDD50-\uDD59\uDDA0-\uDDA7\uDDAA-\uDDD0\uDDE1\uDDE3\uDE00\uDE0B-\uDE32\uDE3A\uDE50\uDE5C-\uDE89\uDE9D\uDEB0-\uDEF8]|\uD807[\uDC00-\uDC08\uDC0A-\uDC2E\uDC40\uDC50-\uDC6C\uDC72-\uDC8F\uDD00-\uDD06\uDD08\uDD09\uDD0B-\uDD30\uDD46\uDD50-\uDD59\uDD60-\uDD65\uDD67\uDD68\uDD6A-\uDD89\uDD98\uDDA0-\uDDA9\uDEE0-\uDEF2\uDF02\uDF04-\uDF10\uDF12-\uDF33\uDF50-\uDF59\uDFB0\uDFC0-\uDFD4]|\uD808[\uDC00-\uDF99]|\uD809[\uDC00-\uDC6E\uDC80-\uDD43]|\uD80B[\uDF90-\uDFF0]|[\uD80C\uD81C-\uD820\uD822\uD840-\uD868\uD86A-\uD86C\uD86F-\uD872\uD874-\uD879\uD880-\uD883\uD885-\uD887][\uDC00-\uDFFF]|\uD80D[\uDC00-\uDC2F\uDC41-\uDC46]|\uD811[\uDC00-\uDE46]|\uD81A[\uDC00-\uDE38\uDE40-\uDE5E\uDE60-\uDE69\uDE70-\uDEBE\uDEC0-\uDEC9\uDED0-\uDEED\uDF00-\uDF2F\uDF40-\uDF43\uDF50-\uDF59\uDF5B-\uDF61\uDF63-\uDF77\uDF7D-\uDF8F]|\uD81B[\uDE40-\uDE96\uDF00-\uDF4A\uDF50\uDF93-\uDF9F\uDFE0\uDFE1\uDFE3]|\uD821[\uDC00-\uDFF7]|\uD823[\uDC00-\uDCD5\uDD00-\uDD08]|\uD82B[\uDFF0-\uDFF3\uDFF5-\uDFFB\uDFFD\uDFFE]|\uD82C[\uDC00-\uDD22\uDD32\uDD50-\uDD52\uDD55\uDD64-\uDD67\uDD70-\uDEFB]|\uD82F[\uDC00-\uDC6A\uDC70-\uDC7C\uDC80-\uDC88\uDC90-\uDC99]|\uD834[\uDEC0-\uDED3\uDEE0-\uDEF3\uDF60-\uDF78]|\uD835[\uDC00-\uDC54\uDC56-\uDC9C\uDC9E\uDC9F\uDCA2\uDCA5\uDCA6\uDCA9-\uDCAC\uDCAE-\uDCB9\uDCBB\uDCBD-\uDCC3\uDCC5-\uDD05\uDD07-\uDD0A\uDD0D-\uDD14\uDD16-\uDD1C\uDD1E-\uDD39\uDD3B-\uDD3E\uDD40-\uDD44\uDD46\uDD4A-\uDD50\uDD52-\uDEA5\uDEA8-\uDEC0\uDEC2-\uDEDA\uDEDC-\uDEFA\uDEFC-\uDF14\uDF16-\uDF34\uDF36-\uDF4E\uDF50-\uDF6E\uDF70-\uDF88\uDF8A-\uDFA8\uDFAA-\uDFC2\uDFC4-\uDFCB\uDFCE-\uDFFF]|\uD837[\uDF00-\uDF1E\uDF25-\uDF2A]|\uD838[\uDC30-\uDC6D\uDD00-\uDD2C\uDD37-\uDD3D\uDD40-\uDD49\uDD4E\uDE90-\uDEAD\uDEC0-\uDEEB\uDEF0-\uDEF9]|\uD839[\uDCD0-\uDCEB\uDCF0-\uDCF9\uDFE0-\uDFE6\uDFE8-\uDFEB\uDFED\uDFEE\uDFF0-\uDFFE]|\uD83A[\uDC00-\uDCC4\uDCC7-\uDCCF\uDD00-\uDD43\uDD4B\uDD50-\uDD59]|\uD83B[\uDC71-\uDCAB\uDCAD-\uDCAF\uDCB1-\uDCB4\uDD01-\uDD2D\uDD2F-\uDD3D\uDE00-\uDE03\uDE05-\uDE1F\uDE21\uDE22\uDE24\uDE27\uDE29-\uDE32\uDE34-\uDE37\uDE39\uDE3B\uDE42\uDE47\uDE49\uDE4B\uDE4D-\uDE4F\uDE51\uDE52\uDE54\uDE57\uDE59\uDE5B\uDE5D\uDE5F\uDE61\uDE62\uDE64\uDE67-\uDE6A\uDE6C-\uDE72\uDE74-\uDE77\uDE79-\uDE7C\uDE7E\uDE80-\uDE89\uDE8B-\uDE9B\uDEA1-\uDEA3\uDEA5-\uDEA9\uDEAB-\uDEBB]|\uD83C[\uDD00-\uDD0C]|\uD83E[\uDFF0-\uDFF9]|\uD869[\uDC00-\uDEDF\uDF00-\uDFFF]|\uD86D[\uDC00-\uDF39\uDF40-\uDFFF]|\uD86E[\uDC00-\uDC1D\uDC20-\uDFFF]|\uD873[\uDC00-\uDEA1\uDEB0-\uDFFF]|\uD87A[\uDC00-\uDFE0]|\uD87E[\uDC00-\uDE1D]|\uD884[\uDC00-\uDF4A\uDF50-\uDFFF]|\uD888[\uDC00-\uDFAF])/;
	          var words = this.title.split(/[\s,]/).filter(function (word) {
	            var firstLetter = word.charAt(0);
	            return validSymbolsPattern.test(firstLetter);
	          });
	          var initials = '';
	          if (words.length > 0) {
	            initials = words.length > 1 ? words[0].charAt(0) + words[1].charAt(0) : initials = words[0].charAt(0);
	          }
	          this.setInitials(initials.toUpperCase());
	        }
	      }
	      return this;
	    }
	  }, {
	    key: "getInitialsNode",
	    value: function getInitialsNode() {
	      if (!this.node.initials) {
	        this.node.initials = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar__text\" style=\"font-size: calc(var(--ui-avatar-size) / 2.6)\"></div>\n\t\t\t"])));
	      }
	      return this.node.initials;
	    }
	  }, {
	    key: "setInitials",
	    value: function setInitials(text) {
	      if (this.picPath) {
	        return this;
	      }
	      if (main_core.Type.isString(text)) {
	        this.getInitialsNode().textContent = text;
	        if (!this.getInitialsNode().parentNode) {
	          this.node.initials = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-avatar__text\"></div>\n\t\t\t\t"])));
	          main_core.Dom.append(this.getInitialsNode(), this.getContainer());
	          main_core.Dom.style(this.getInitialsNode(), 'font-size', 'calc(var(--ui-avatar-size) / 2.6)');
	        }
	        this.getInitialsNode().textContent = text;
	      }
	      return this;
	    }
	  }, {
	    key: "getSvgElement",
	    value: function getSvgElement(tag, attr) {
	      if (main_core.Type.isString(tag) || main_core.Type.isObject(attr)) {
	        var svg = document.createElementNS('http://www.w3.org/2000/svg', tag);
	        Object.keys(attr).forEach(function (attrSingle) {
	          if (Object.prototype.hasOwnProperty.call(attr, attrSingle)) {
	            svg.setAttributeNS(null, attrSingle, attr[attrSingle]);
	          }
	        });
	        return svg;
	      }
	      return null;
	    }
	  }, {
	    key: "getMaskNode",
	    value: function getMaskNode() {
	      if (!this.node.svgMask) {
	        this.node.svgMask = this.getSvgElement('circle', {
	          cx: 51,
	          cy: 51,
	          r: 51,
	          fill: 'white'
	        });
	      }
	      return this.node.svgMask;
	    }
	  }, {
	    key: "setMask",
	    value: function setMask() {
	      var mask = this.getSvgElement('mask', {
	        id: "".concat(this.getUnicId(), "-").concat(this.constructor.name)
	      });
	      main_core.Dom.append(this.getMaskNode(), mask);
	      main_core.Dom.prepend(mask, this.getContainer().querySelector('svg'));
	    }
	  }, {
	    key: "getDefaultUserPic",
	    value: function getDefaultUserPic() {
	      if (!this.node.svgDefaultUserPic) {
	        this.node.svgDefaultUserPic = this.getSvgElement('svg', {
	          width: 56,
	          height: 64,
	          viewBox: '0 0 28 32',
	          x: 23,
	          y: 20
	        });
	        this.node.svgDefaultUserPic.innerHTML = "\n\t\t\t\t<path fill=\"#fff\" d=\"M25.197 29.5091C26.5623 29.0513 27.3107 27.5994 27.0337 26.1625L26.6445 24.143C26.4489 22.8806 25.0093 21.4633 21.7893 20.6307C20.6983 20.3264 19.6613 19.8546 18.7152 19.232C18.5082 19.1138 18.5397 18.0214 18.5397 18.0214L17.5026 17.8636C17.5026 17.7749 17.4139 16.4649 17.4139 16.4649C18.6548 16.048 18.5271 13.5884 18.5271 13.5884C19.3151 14.0255 19.8283 12.0791 19.8283 12.0791C20.7604 9.37488 19.3642 9.53839 19.3642 9.53839C19.6085 7.88753 19.6085 6.20972 19.3642 4.55887C18.7435 -0.917471 9.39785 0.569216 10.506 2.35777C7.77463 1.85466 8.39788 8.06931 8.39788 8.06931L8.99031 9.67863C8.16916 10.2112 8.33041 10.8225 8.51054 11.5053C8.58564 11.7899 8.66401 12.087 8.67586 12.396C8.73309 13.9469 9.68211 13.6255 9.68211 13.6255C9.7406 16.1851 11.0028 16.5184 11.0028 16.5184C11.2399 18.1258 11.0921 17.8523 11.0921 17.8523L9.9689 17.9881C9.9841 18.3536 9.95432 18.7197 9.88022 19.078C9.2276 19.3688 8.82806 19.6003 8.43247 19.8294C8.0275 20.064 7.62666 20.2962 6.9627 20.5873C4.42693 21.6985 1.8838 22.3205 1.39387 24.2663C1.28119 24.7138 1.1185 25.4832 0.962095 26.2968C0.697567 27.673 1.44264 29.0328 2.74873 29.4755C5.93305 30.5548 9.46983 31.1912 13.2024 31.2728H14.843C18.5367 31.192 22.0386 30.5681 25.197 29.5091Z\"/>\n\t\t\t";
	      }
	      return this.node.svgDefaultUserPic;
	    }
	  }, {
	    key: "getUserPicNode",
	    value: function getUserPicNode() {
	      if (!this.node.svgUserPic) {
	        this.node.svgUserPic = this.getSvgElement('image', {
	          height: 102,
	          width: 102,
	          mask: "url(#".concat(this.getUnicId(), "-").concat(this.constructor.name, ")"),
	          preserveAspectRatio: 'xMidYMid slice'
	        });
	      }
	      return this.node.svgUserPic;
	    }
	  }, {
	    key: "setDefaultUserPic",
	    value: function setDefaultUserPic() {
	      if (!this.getDefaultUserPic().parentNode) {
	        main_core.Dom.append(this.getDefaultUserPic(), this.getContainer().querySelector('svg'));
	      }
	      main_core.Dom.addClass(this.getContainer(), '--default-user-pic');
	      main_core.Dom.remove(this.getInitialsNode());
	      this.node.initials = null;
	      return this;
	    }
	  }, {
	    key: "removeDefaultUserPic",
	    value: function removeDefaultUserPic() {
	      main_core.Dom.remove(this.getDefaultUserPic());
	      main_core.Dom.removeClass(this.getContainer(), '--default-user-pic');
	      this.node.svgDefaultUserPic = null;
	      return this;
	    }
	  }, {
	    key: "setPic",
	    value: function setPic(url) {
	      this.setUserPic(url);
	    }
	  }, {
	    key: "removePic",
	    value: function removePic() {
	      this.removeUserPic();
	    }
	  }, {
	    key: "setUserPic",
	    value: function setUserPic(url) {
	      if (main_core.Type.isString(url) && url !== '') {
	        this.picPath = url;
	        if (!this.getUserPicNode().parentNode) {
	          main_core.Dom.append(this.getUserPicNode(), this.getContainer().querySelector('svg'));
	        }
	        this.getUserPicNode().setAttributeNS('http://www.w3.org/1999/xlink', 'href', url);
	        main_core.Dom.removeClass(this.getContainer(), '--default-user-pic');
	        main_core.Dom.remove(this.getInitialsNode());
	        this.node.initials = null;
	      }
	      return this;
	    }
	  }, {
	    key: "removeUserPic",
	    value: function removeUserPic() {
	      main_core.Dom.remove(this.getUserPicNode());
	      this.picPath = null;
	      this.setInitials(this.title);
	      main_core.Dom.style(this.getContainer(), '--ui-avatar-base-color-rgb', this.baseColor);
	    }
	  }, {
	    key: "setSize",
	    value: function setSize(size) {
	      if (main_core.Type.isNumber(size) && size > 0) {
	        this.size = size;
	        main_core.Dom.style(this.getContainer(), '--ui-avatar-size', "".concat(this.size, "px"));
	      }
	      return this;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --base\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<circle class=\"ui-avatar-base\" cx=\"51\" cy=\"51\" r=\"51\" />\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.node.avatar;
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      if (main_core.Type.isDomNode(node)) {
	        main_core.Dom.append(this.getContainer(), node);
	      }
	      return null;
	    }
	  }]);
	  return AvatarBase;
	}();

	var AvatarRound = /*#__PURE__*/function (_AvatarBase) {
	  babelHelpers.inherits(AvatarRound, _AvatarBase);
	  function AvatarRound() {
	    babelHelpers.classCallCheck(this, AvatarRound);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarRound).apply(this, arguments));
	  }
	  return AvatarRound;
	}(AvatarBase);

	var _templateObject$1;
	var AvatarRoundGuest = /*#__PURE__*/function (_AvatarBase) {
	  babelHelpers.inherits(AvatarRoundGuest, _AvatarBase);
	  function AvatarRoundGuest() {
	    babelHelpers.classCallCheck(this, AvatarRoundGuest);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarRoundGuest).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarRoundGuest, [{
	    key: "getMaskNode",
	    value: function getMaskNode() {
	      if (!this.node.svgMask) {
	        this.node.svgMask = this.getSvgElement('circle', {
	          cx: 51,
	          cy: 51,
	          r: 42.5,
	          fill: 'white'
	        });
	      }
	      return this.node.svgMask;
	    }
	  }, {
	    key: "getDefaultUserPic",
	    value: function getDefaultUserPic() {
	      if (!this.node.svgDefaultUserPic) {
	        this.node.svgDefaultUserPic = this.getSvgElement('svg', {
	          width: 56,
	          height: 64,
	          viewBox: '0 0 28 32',
	          x: 23,
	          y: 20
	        });
	        this.node.svgDefaultUserPic.innerHTML = "\n\t\t\t\t<path class=\"ui-avatar-default-path\" d=\"M25.197 29.5091C26.5623 29.0513 27.3107 27.5994 27.0337 26.1625L26.6445 24.143C26.4489 22.8806 25.0093 21.4633 21.7893 20.6307C20.6983 20.3264 19.6613 19.8546 18.7152 19.232C18.5082 19.1138 18.5397 18.0214 18.5397 18.0214L17.5026 17.8636C17.5026 17.7749 17.4139 16.4649 17.4139 16.4649C18.6548 16.048 18.5271 13.5884 18.5271 13.5884C19.3151 14.0255 19.8283 12.0791 19.8283 12.0791C20.7604 9.37488 19.3642 9.53839 19.3642 9.53839C19.6085 7.88753 19.6085 6.20972 19.3642 4.55887C18.7435 -0.917471 9.39785 0.569216 10.506 2.35777C7.77463 1.85466 8.39788 8.06931 8.39788 8.06931L8.99031 9.67863C8.16916 10.2112 8.33041 10.8225 8.51054 11.5053C8.58564 11.7899 8.66401 12.087 8.67586 12.396C8.73309 13.9469 9.68211 13.6255 9.68211 13.6255C9.7406 16.1851 11.0028 16.5184 11.0028 16.5184C11.2399 18.1258 11.0921 17.8523 11.0921 17.8523L9.9689 17.9881C9.9841 18.3536 9.95432 18.7197 9.88022 19.078C9.2276 19.3688 8.82806 19.6003 8.43247 19.8294C8.0275 20.064 7.62666 20.2962 6.9627 20.5873C4.42693 21.6985 1.8838 22.3205 1.39387 24.2663C1.28119 24.7138 1.1185 25.4832 0.962095 26.2968C0.697567 27.673 1.44264 29.0328 2.74873 29.4755C5.93305 30.5548 9.46983 31.1912 13.2024 31.2728H14.843C18.5367 31.192 22.0386 30.5681 25.197 29.5091Z\"/>\n\t\t\t";
	      }
	      return this.node.svgDefaultUserPic;
	    }
	  }, {
	    key: "getUserPicNode",
	    value: function getUserPicNode() {
	      if (!this.node.svgUserpic) {
	        this.node.svgUserpic = this.getSvgElement('image', {
	          height: 86,
	          width: 86,
	          x: 8,
	          y: 8,
	          mask: "url(#".concat(this.getUnicId(), "-").concat(this.constructor.name, ")"),
	          preserveAspectRatio: 'xMidYMid slice'
	        });
	      }
	      return this.node.svgUserpic;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --round --guest\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<circle class=\"ui-avatar-border-inner\" cx=\"51\" cy=\"51\" r=\"51\"/>\n\t\t\t\t\t\t<circle class=\"ui-avatar-base\" cx=\"51\" cy=\"51\" r=\"42.5\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-border\" d=\"M51 98.26C77.101 98.26 98.26 77.101 98.26 51C98.26 24.899 77.101 3.74 51 3.74C24.899 3.74 3.74 24.899 3.74 51C3.74 77.101 24.899 98.26 51 98.26ZM51 102C79.1665 102 102 79.1665 102 51C102 22.8335 79.1665 0 51 0C22.8335 0 0 22.8335 0 51C0 79.1665 22.8335 102 51 102Z\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarRoundGuest;
	}(AvatarBase);

	var _templateObject$2;
	var AvatarRoundExtranet = /*#__PURE__*/function (_AvatarRoundGuest) {
	  babelHelpers.inherits(AvatarRoundExtranet, _AvatarRoundGuest);
	  function AvatarRoundExtranet() {
	    babelHelpers.classCallCheck(this, AvatarRoundExtranet);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarRoundExtranet).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarRoundExtranet, [{
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --round --extranet\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<circle class=\"ui-avatar-border-inner\" cx=\"51\" cy=\"51\" r=\"51\"/>\n\t\t\t\t\t\t<circle class=\"ui-avatar-base\" cx=\"51\" cy=\"51\" r=\"42.5\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-border\" d=\"M51 98.26C77.101 98.26 98.26 77.101 98.26 51C98.26 24.899 77.101 3.74 51 3.74C24.899 3.74 3.74 24.899 3.74 51C3.74 77.101 24.899 98.26 51 98.26ZM51 102C79.1665 102 102 79.1665 102 51C102 22.8335 79.1665 0 51 0C22.8335 0 0 22.8335 0 51C0 79.1665 22.8335 102 51 102Z\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarRoundExtranet;
	}(AvatarRoundGuest);

	var _templateObject$3;
	var AvatarRoundAccent = /*#__PURE__*/function (_AvatarRoundGuest) {
	  babelHelpers.inherits(AvatarRoundAccent, _AvatarRoundGuest);
	  function AvatarRoundAccent() {
	    babelHelpers.classCallCheck(this, AvatarRoundAccent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarRoundAccent).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarRoundAccent, [{
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --round --accent\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<circle class=\"ui-avatar-border-inner\" cx=\"51\" cy=\"51\" r=\"51\"/>\n\t\t\t\t\t\t<circle class=\"ui-avatar-base\" cx=\"51\" cy=\"51\" r=\"42.5\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-border\" fill=\"url(#ui-avatar-gradient-accent-", ")\" d=\"M51 98.26C77.101 98.26 98.26 77.101 98.26 51C98.26 24.899 77.101 3.74 51 3.74C24.899 3.74 3.74 24.899 3.74 51C3.74 77.101 24.899 98.26 51 98.26ZM51 102C79.1665 102 102 79.1665 102 51C102 22.8335 79.1665 0 51 0C22.8335 0 0 22.8335 0 51C0 79.1665 22.8335 102 51 102Z\"/>\n\t\t\t\t\t\t<linearGradient id=\"ui-avatar-gradient-accent-", "\" x1=\"13.3983\" y1=\"2.16102\" x2=\"53.5932\" y2=\"60.0763\" gradientUnits=\"userSpaceOnUse\">\n\t\t\t\t\t\t\t<stop stop-color=\"var(--ui-avatar-color-gradient-start)\"/>\n\t\t\t\t\t\t\t<stop offset=\"1\" stop-color=\"var(--ui-avatar-color-gradient-stop)\"/>\n\t\t\t\t\t\t</linearGradient>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])), this.getUnicId(), this.getUnicId());
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarRoundAccent;
	}(AvatarRoundGuest);

	var _templateObject$4;
	var AvatarHexagon = /*#__PURE__*/function (_AvatarBase) {
	  babelHelpers.inherits(AvatarHexagon, _AvatarBase);
	  function AvatarHexagon() {
	    babelHelpers.classCallCheck(this, AvatarHexagon);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarHexagon).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarHexagon, [{
	    key: "getUserPicNode",
	    value: function getUserPicNode() {
	      if (!this.node.svgUserpic) {
	        this.node.svgUserpic = this.getSvgElement('image', {
	          height: 102,
	          width: 102,
	          mask: "url(#".concat(this.getUnicId(), "-").concat(this.constructor.name, ")"),
	          preserveAspectRatio: 'xMidYMid slice'
	        });
	      }
	      return this.node.svgUserpic;
	    }
	  }, {
	    key: "getMaskNode",
	    value: function getMaskNode() {
	      if (!this.node.svgMask) {
	        this.node.svgMask = this.getSvgElement('path', {
	          "class": 'ui-avatar-mask',
	          d: 'M40.4429 2.77436C47.0211 -0.823713 54.979 -0.823711 61.5572 2.77436L88.9207 17.7412C95.9759 21.6001 100.363 29.001 100.363 37.0426V64.9573C100.363 72.9989 95.9759 80.3998 88.9207 84.2588L61.5572 99.2256C54.979 102.824 47.0211 102.824 40.4429 99.2256L13.0794 84.2588C6.02419 80.3998 1.6366 72.9989 1.6366 64.9573V37.0426C1.6366 29.001 6.0242 21.6001 13.0794 17.7412L40.4429 2.77436Z'
	        });
	      }
	      return this.node.svgMask;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --hexagon --base\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<path class=\"ui-avatar-base\" d=\"M40.4429 2.77436C47.0211 -0.823713 54.979 -0.823711 61.5572 2.77436L88.9207 17.7412C95.9759 21.6001 100.363 29.001 100.363 37.0426V64.9573C100.363 72.9989 95.9759 80.3998 88.9207 84.2588L61.5572 99.2256C54.979 102.824 47.0211 102.824 40.4429 99.2256L13.0794 84.2588C6.02419 80.3998 1.6366 72.9989 1.6366 64.9573V37.0426C1.6366 29.001 6.0242 21.6001 13.0794 17.7412L40.4429 2.77436Z\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarHexagon;
	}(AvatarBase);

	var _templateObject$5;
	var AvatarHexagonGuest = /*#__PURE__*/function (_AvatarBase) {
	  babelHelpers.inherits(AvatarHexagonGuest, _AvatarBase);
	  function AvatarHexagonGuest() {
	    babelHelpers.classCallCheck(this, AvatarHexagonGuest);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarHexagonGuest).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarHexagonGuest, [{
	    key: "getUserPicNode",
	    value: function getUserPicNode() {
	      if (!this.node.svgUserpic) {
	        this.node.svgUserpic = this.getSvgElement('image', {
	          height: 86,
	          width: 86,
	          x: 8,
	          y: 8,
	          mask: "url(#".concat(this.getUnicId(), "-").concat(this.constructor.name, ")"),
	          preserveAspectRatio: 'xMidYMid slice'
	        });
	      }
	      return this.node.svgUserpic;
	    }
	  }, {
	    key: "getDefaultUserPic",
	    value: function getDefaultUserPic() {
	      if (!this.node.svgDefaultUserPic) {
	        this.node.svgDefaultUserPic = this.getSvgElement('svg', {
	          width: 56,
	          height: 64,
	          viewBox: '0 0 28 32',
	          x: 23,
	          y: 20
	        });
	        this.node.svgDefaultUserPic.innerHTML = "\n\t\t\t\t<path class=\"ui-avatar-default-path\" d=\"M25.197 29.5091C26.5623 29.0513 27.3107 27.5994 27.0337 26.1625L26.6445 24.143C26.4489 22.8806 25.0093 21.4633 21.7893 20.6307C20.6983 20.3264 19.6613 19.8546 18.7152 19.232C18.5082 19.1138 18.5397 18.0214 18.5397 18.0214L17.5026 17.8636C17.5026 17.7749 17.4139 16.4649 17.4139 16.4649C18.6548 16.048 18.5271 13.5884 18.5271 13.5884C19.3151 14.0255 19.8283 12.0791 19.8283 12.0791C20.7604 9.37488 19.3642 9.53839 19.3642 9.53839C19.6085 7.88753 19.6085 6.20972 19.3642 4.55887C18.7435 -0.917471 9.39785 0.569216 10.506 2.35777C7.77463 1.85466 8.39788 8.06931 8.39788 8.06931L8.99031 9.67863C8.16916 10.2112 8.33041 10.8225 8.51054 11.5053C8.58564 11.7899 8.66401 12.087 8.67586 12.396C8.73309 13.9469 9.68211 13.6255 9.68211 13.6255C9.7406 16.1851 11.0028 16.5184 11.0028 16.5184C11.2399 18.1258 11.0921 17.8523 11.0921 17.8523L9.9689 17.9881C9.9841 18.3536 9.95432 18.7197 9.88022 19.078C9.2276 19.3688 8.82806 19.6003 8.43247 19.8294C8.0275 20.064 7.62666 20.2962 6.9627 20.5873C4.42693 21.6985 1.8838 22.3205 1.39387 24.2663C1.28119 24.7138 1.1185 25.4832 0.962095 26.2968C0.697567 27.673 1.44264 29.0328 2.74873 29.4755C5.93305 30.5548 9.46983 31.1912 13.2024 31.2728H14.843C18.5367 31.192 22.0386 30.5681 25.197 29.5091Z\"/>\n\t\t\t";
	      }
	      return this.node.svgDefaultUserPic;
	    }
	  }, {
	    key: "getMaskNode",
	    value: function getMaskNode() {
	      if (!this.node.svgMask) {
	        this.node.svgMask = this.getSvgElement('path', {
	          "class": 'ui-avatar-mask',
	          d: 'M44.2368 10.2019C48.4219 7.93252 53.5781 7.93252 57.7632 10.2019L85.2368 25.0997C89.4219 27.3692 92 31.5632 92 36.1021V65.8977C92 70.4365 89.4219 74.6306 85.2368 76.9L57.7632 91.7978C53.5781 94.0672 48.4219 94.0672 44.2368 91.7978L16.7632 76.9C12.5781 74.6306 10 70.4365 10 65.8977V36.1021C10 31.5632 12.5781 27.3692 16.7632 25.0997L44.2368 10.2019Z'
	        });
	      }
	      return this.node.svgMask;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --hexagon --guest\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<path class=\"ui-avatar-border-inner\" d=\"M40.4429 2.77436C47.0211 -0.823713 54.979 -0.823711 61.5572 2.77436L88.9207 17.7412C95.9759 21.6001 100.363 29.001 100.363 37.0426V64.9573C100.363 72.9989 95.9759 80.3998 88.9207 84.2588L61.5572 99.2256C54.979 102.824 47.0211 102.824 40.4429 99.2256L13.0794 84.2588C6.02419 80.3998 1.6366 72.9989 1.6366 64.9573V37.0426C1.6366 29.001 6.0242 21.6001 13.0794 17.7412L40.4429 2.77436Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-border\" d=\"M87.126 21.0224L59.7625 6.05561C54.3025 3.06921 47.6975 3.06921 42.2376 6.0556L14.8741 21.0224C9.01831 24.2253 5.3766 30.3681 5.3766 37.0426V64.9573C5.3766 71.6319 9.0183 77.7746 14.8741 80.9775L42.2376 95.9443C47.6975 98.9307 54.3025 98.9307 59.7625 95.9443L87.126 80.9775C92.9818 77.7746 96.6235 71.6319 96.6235 64.9573V37.0426C96.6235 30.3681 92.9818 24.2253 87.126 21.0224ZM61.5572 2.77436C54.979 -0.823711 47.0211 -0.823713 40.4429 2.77436L13.0794 17.7412C6.0242 21.6001 1.6366 29.001 1.6366 37.0426V64.9573C1.6366 72.9989 6.02419 80.3998 13.0794 84.2588L40.4429 99.2256C47.0211 102.824 54.979 102.824 61.5572 99.2256L88.9207 84.2588C95.9759 80.3998 100.363 72.9989 100.363 64.9573V37.0426C100.363 29.001 95.9759 21.6001 88.9207 17.7412L61.5572 2.77436Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-base\" d=\"M44.2368 10.2019C48.4219 7.93252 53.5781 7.93252 57.7632 10.2019L85.2368 25.0997C89.4219 27.3692 92 31.5632 92 36.1021V65.8977C92 70.4365 89.4219 74.6306 85.2368 76.9L57.7632 91.7978C53.5781 94.0672 48.4219 94.0672 44.2368 91.7978L16.7632 76.9C12.5781 74.6306 10 70.4365 10 65.8977V36.1021C10 31.5632 12.5781 27.3692 16.7632 25.0997L44.2368 10.2019Z\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarHexagonGuest;
	}(AvatarBase);

	var _templateObject$6;
	var AvatarHexagonExtranet = /*#__PURE__*/function (_AvatarHexagonGuest) {
	  babelHelpers.inherits(AvatarHexagonExtranet, _AvatarHexagonGuest);
	  function AvatarHexagonExtranet() {
	    babelHelpers.classCallCheck(this, AvatarHexagonExtranet);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarHexagonExtranet).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarHexagonExtranet, [{
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --hexagon --extranet\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<path class=\"ui-avatar-border-inner\" d=\"M40.4429 2.77436C47.0211 -0.823713 54.979 -0.823711 61.5572 2.77436L88.9207 17.7412C95.9759 21.6001 100.363 29.001 100.363 37.0426V64.9573C100.363 72.9989 95.9759 80.3998 88.9207 84.2588L61.5572 99.2256C54.979 102.824 47.0211 102.824 40.4429 99.2256L13.0794 84.2588C6.02419 80.3998 1.6366 72.9989 1.6366 64.9573V37.0426C1.6366 29.001 6.0242 21.6001 13.0794 17.7412L40.4429 2.77436Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-border\" d=\"M87.126 21.0224L59.7625 6.05561C54.3025 3.06921 47.6975 3.06921 42.2376 6.0556L14.8741 21.0224C9.01831 24.2253 5.3766 30.3681 5.3766 37.0426V64.9573C5.3766 71.6319 9.0183 77.7746 14.8741 80.9775L42.2376 95.9443C47.6975 98.9307 54.3025 98.9307 59.7625 95.9443L87.126 80.9775C92.9818 77.7746 96.6235 71.6319 96.6235 64.9573V37.0426C96.6235 30.3681 92.9818 24.2253 87.126 21.0224ZM61.5572 2.77436C54.979 -0.823711 47.0211 -0.823713 40.4429 2.77436L13.0794 17.7412C6.0242 21.6001 1.6366 29.001 1.6366 37.0426V64.9573C1.6366 72.9989 6.02419 80.3998 13.0794 84.2588L40.4429 99.2256C47.0211 102.824 54.979 102.824 61.5572 99.2256L88.9207 84.2588C95.9759 80.3998 100.363 72.9989 100.363 64.9573V37.0426C100.363 29.001 95.9759 21.6001 88.9207 17.7412L61.5572 2.77436Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-base\" d=\"M44.2368 10.2019C48.4219 7.93252 53.5781 7.93252 57.7632 10.2019L85.2368 25.0997C89.4219 27.3692 92 31.5632 92 36.1021V65.8977C92 70.4365 89.4219 74.6306 85.2368 76.9L57.7632 91.7978C53.5781 94.0672 48.4219 94.0672 44.2368 91.7978L16.7632 76.9C12.5781 74.6306 10 70.4365 10 65.8977V36.1021C10 31.5632 12.5781 27.3692 16.7632 25.0997L44.2368 10.2019Z\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarHexagonExtranet;
	}(AvatarHexagonGuest);

	var _templateObject$7;
	var AvatarHexagonAccent = /*#__PURE__*/function (_AvatarHexagonGuest) {
	  babelHelpers.inherits(AvatarHexagonAccent, _AvatarHexagonGuest);
	  function AvatarHexagonAccent() {
	    babelHelpers.classCallCheck(this, AvatarHexagonAccent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarHexagonAccent).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarHexagonAccent, [{
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$7 || (_templateObject$7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --hexagon --accent\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<path class=\"ui-avatar-border-inner\" d=\"M40.4429 2.77436C47.0211 -0.823713 54.979 -0.823711 61.5572 2.77436L88.9207 17.7412C95.9759 21.6001 100.363 29.001 100.363 37.0426V64.9573C100.363 72.9989 95.9759 80.3998 88.9207 84.2588L61.5572 99.2256C54.979 102.824 47.0211 102.824 40.4429 99.2256L13.0794 84.2588C6.02419 80.3998 1.6366 72.9989 1.6366 64.9573V37.0426C1.6366 29.001 6.0242 21.6001 13.0794 17.7412L40.4429 2.77436Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-border\" fill=\"url(#ui-avatar-gradient-accent-", ")\"  d=\"M87.126 21.0224L59.7625 6.05561C54.3025 3.06921 47.6975 3.06921 42.2376 6.0556L14.8741 21.0224C9.01831 24.2253 5.3766 30.3681 5.3766 37.0426V64.9573C5.3766 71.6319 9.0183 77.7746 14.8741 80.9775L42.2376 95.9443C47.6975 98.9307 54.3025 98.9307 59.7625 95.9443L87.126 80.9775C92.9818 77.7746 96.6235 71.6319 96.6235 64.9573V37.0426C96.6235 30.3681 92.9818 24.2253 87.126 21.0224ZM61.5572 2.77436C54.979 -0.823711 47.0211 -0.823713 40.4429 2.77436L13.0794 17.7412C6.0242 21.6001 1.6366 29.001 1.6366 37.0426V64.9573C1.6366 72.9989 6.02419 80.3998 13.0794 84.2588L40.4429 99.2256C47.0211 102.824 54.979 102.824 61.5572 99.2256L88.9207 84.2588C95.9759 80.3998 100.363 72.9989 100.363 64.9573V37.0426C100.363 29.001 95.9759 21.6001 88.9207 17.7412L61.5572 2.77436Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-base\" d=\"M44.2368 10.2019C48.4219 7.93252 53.5781 7.93252 57.7632 10.2019L85.2368 25.0997C89.4219 27.3692 92 31.5632 92 36.1021V65.8977C92 70.4365 89.4219 74.6306 85.2368 76.9L57.7632 91.7978C53.5781 94.0672 48.4219 94.0672 44.2368 91.7978L16.7632 76.9C12.5781 74.6306 10 70.4365 10 65.8977V36.1021C10 31.5632 12.5781 27.3692 16.7632 25.0997L44.2368 10.2019Z\"/>\n\t\t\t\t\t\t<linearGradient id=\"ui-avatar-gradient-accent-", "\" x1=\"13.3983\" y1=\"2.16102\" x2=\"53.5932\" y2=\"60.0763\" gradientUnits=\"userSpaceOnUse\">\n\t\t\t\t\t\t\t<stop stop-color=\"var(--ui-avatar-color-gradient-start)\"/>\n\t\t\t\t\t\t\t<stop offset=\"1\" stop-color=\"var(--ui-avatar-color-gradient-stop)\"/>\n\t\t\t\t\t\t</linearGradient>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])), this.getUnicId(), this.getUnicId());
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarHexagonAccent;
	}(AvatarHexagonGuest);

	var _templateObject$8;
	var AvatarSquare = /*#__PURE__*/function (_AvatarBase) {
	  babelHelpers.inherits(AvatarSquare, _AvatarBase);
	  function AvatarSquare() {
	    babelHelpers.classCallCheck(this, AvatarSquare);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarSquare).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarSquare, [{
	    key: "getMaskNode",
	    value: function getMaskNode() {
	      if (!this.node.svgMask) {
	        this.node.svgMask = this.getSvgElement('path', {
	          "class": 'ui-avatar-mask',
	          d: 'M12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z'
	        });
	      }
	      return this.node.svgMask;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$8 || (_templateObject$8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --square --base\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<path class=\"ui-avatar-base\" d=\"M12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarSquare;
	}(AvatarBase);

	var _templateObject$9;
	var AvatarSquareGuest = /*#__PURE__*/function (_AvatarSquare) {
	  babelHelpers.inherits(AvatarSquareGuest, _AvatarSquare);
	  function AvatarSquareGuest() {
	    babelHelpers.classCallCheck(this, AvatarSquareGuest);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarSquareGuest).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarSquareGuest, [{
	    key: "getMaskNode",
	    value: function getMaskNode() {
	      if (!this.node.svgMask) {
	        this.node.svgMask = this.getSvgElement('path', {
	          "class": 'ui-avatar-mask',
	          d: 'M8.47241 14.4724C8.47241 11.1587 11.1587 8.47241 14.4724 8.47241H87.4724C90.7861 8.47241 93.4724 11.1587 93.4724 14.4724V87.4724C93.4724 90.7861 90.7861 93.4724 87.4724 93.4724H14.4724C11.1587 93.4724 8.47241 90.7861 8.47241 87.4724V14.4724Z'
	        });
	      }
	      return this.node.svgMask;
	    }
	  }, {
	    key: "getDefaultUserPic",
	    value: function getDefaultUserPic() {
	      if (!this.node.svgDefaultUserPic) {
	        this.node.svgDefaultUserPic = this.getSvgElement('svg', {
	          width: 56,
	          height: 64,
	          viewBox: '0 0 28 32',
	          x: 23,
	          y: 20
	        });
	        this.node.svgDefaultUserPic.innerHTML = "\n\t\t\t\t<path class=\"ui-avatar-default-path\" d=\"M25.197 29.5091C26.5623 29.0513 27.3107 27.5994 27.0337 26.1625L26.6445 24.143C26.4489 22.8806 25.0093 21.4633 21.7893 20.6307C20.6983 20.3264 19.6613 19.8546 18.7152 19.232C18.5082 19.1138 18.5397 18.0214 18.5397 18.0214L17.5026 17.8636C17.5026 17.7749 17.4139 16.4649 17.4139 16.4649C18.6548 16.048 18.5271 13.5884 18.5271 13.5884C19.3151 14.0255 19.8283 12.0791 19.8283 12.0791C20.7604 9.37488 19.3642 9.53839 19.3642 9.53839C19.6085 7.88753 19.6085 6.20972 19.3642 4.55887C18.7435 -0.917471 9.39785 0.569216 10.506 2.35777C7.77463 1.85466 8.39788 8.06931 8.39788 8.06931L8.99031 9.67863C8.16916 10.2112 8.33041 10.8225 8.51054 11.5053C8.58564 11.7899 8.66401 12.087 8.67586 12.396C8.73309 13.9469 9.68211 13.6255 9.68211 13.6255C9.7406 16.1851 11.0028 16.5184 11.0028 16.5184C11.2399 18.1258 11.0921 17.8523 11.0921 17.8523L9.9689 17.9881C9.9841 18.3536 9.95432 18.7197 9.88022 19.078C9.2276 19.3688 8.82806 19.6003 8.43247 19.8294C8.0275 20.064 7.62666 20.2962 6.9627 20.5873C4.42693 21.6985 1.8838 22.3205 1.39387 24.2663C1.28119 24.7138 1.1185 25.4832 0.962095 26.2968C0.697567 27.673 1.44264 29.0328 2.74873 29.4755C5.93305 30.5548 9.46983 31.1912 13.2024 31.2728H14.843C18.5367 31.192 22.0386 30.5681 25.197 29.5091Z\"/>\n\t\t\t";
	      }
	      return this.node.svgDefaultUserPic;
	    }
	  }, {
	    key: "getUserPicNode",
	    value: function getUserPicNode() {
	      if (!this.node.svgUserpic) {
	        this.node.svgUserpic = this.getSvgElement('image', {
	          height: 86,
	          width: 86,
	          x: 8,
	          y: 8,
	          mask: "url(#".concat(this.getUnicId(), "-").concat(this.constructor.name, ")"),
	          preserveAspectRatio: 'xMidYMid slice'
	        });
	      }
	      return this.node.svgUserpic;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$9 || (_templateObject$9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --square --guest\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<path class=\"ui-avatar-border-inner\" d=\"M12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-border\" d=\"M90 3.74H12C7.43813 3.74 3.74 7.43813 3.74 12V90C3.74 94.5619 7.43813 98.26 12 98.26H90C94.5619 98.26 98.26 94.5619 98.26 90V12C98.26 7.43813 94.5619 3.74 90 3.74ZM12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-base\" d=\"M8.47241 14.4724C8.47241 11.1587 11.1587 8.47241 14.4724 8.47241H87.4724C90.7861 8.47241 93.4724 11.1587 93.4724 14.4724V87.4724C93.4724 90.7861 90.7861 93.4724 87.4724 93.4724H14.4724C11.1587 93.4724 8.47241 90.7861 8.47241 87.4724V14.4724Z\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarSquareGuest;
	}(AvatarSquare);

	var _templateObject$a;
	var AvatarSquareExtranet = /*#__PURE__*/function (_AvatarSquareGuest) {
	  babelHelpers.inherits(AvatarSquareExtranet, _AvatarSquareGuest);
	  function AvatarSquareExtranet() {
	    babelHelpers.classCallCheck(this, AvatarSquareExtranet);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarSquareExtranet).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarSquareExtranet, [{
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$a || (_templateObject$a = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --square --extranet\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t<path class=\"ui-avatar-border-inner\" d=\"M12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-border\" d=\"M90 3.74H12C7.43813 3.74 3.74 7.43813 3.74 12V90C3.74 94.5619 7.43813 98.26 12 98.26H90C94.5619 98.26 98.26 94.5619 98.26 90V12C98.26 7.43813 94.5619 3.74 90 3.74ZM12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-base\" d=\"M8.47241 14.4724C8.47241 11.1587 11.1587 8.47241 14.4724 8.47241H87.4724C90.7861 8.47241 93.4724 11.1587 93.4724 14.4724V87.4724C93.4724 90.7861 90.7861 93.4724 87.4724 93.4724H14.4724C11.1587 93.4724 8.47241 90.7861 8.47241 87.4724V14.4724Z\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarSquareExtranet;
	}(AvatarSquareGuest);

	var _templateObject$b;
	var AvatarSquareAccent = /*#__PURE__*/function (_AvatarSquareGuest) {
	  babelHelpers.inherits(AvatarSquareAccent, _AvatarSquareGuest);
	  function AvatarSquareAccent() {
	    babelHelpers.classCallCheck(this, AvatarSquareAccent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AvatarSquareAccent).apply(this, arguments));
	  }
	  babelHelpers.createClass(AvatarSquareAccent, [{
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.node.avatar) {
	        this.node.avatar = main_core.Tag.render(_templateObject$b || (_templateObject$b = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-avatar --square --accent\">\n\t\t\t\t\t<svg viewBox=\"0 0 102 102\">\n\t\t\t\t\t\t\n\t\t\t\t\t\t<path class=\"ui-avatar-border-inner\" d=\"M12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-border\" fill=\"url(#ui-avatar-gradient-accent-", ")\"  d=\"M90 3.74H12C7.43813 3.74 3.74 7.43813 3.74 12V90C3.74 94.5619 7.43813 98.26 12 98.26H90C94.5619 98.26 98.26 94.5619 98.26 90V12C98.26 7.43813 94.5619 3.74 90 3.74ZM12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z\"/>\n\t\t\t\t\t\t<path class=\"ui-avatar-base\" d=\"M8.47241 14.4724C8.47241 11.1587 11.1587 8.47241 14.4724 8.47241H87.4724C90.7861 8.47241 93.4724 11.1587 93.4724 14.4724V87.4724C93.4724 90.7861 90.7861 93.4724 87.4724 93.4724H14.4724C11.1587 93.4724 8.47241 90.7861 8.47241 87.4724V14.4724Z\"/>\n\t\t\t\t\t\t<linearGradient id=\"ui-avatar-gradient-accent-", "\" x1=\"13.3983\" y1=\"2.16102\" x2=\"53.5932\" y2=\"60.0763\" gradientUnits=\"userSpaceOnUse\">\n\t\t\t\t\t\t\t<stop stop-color=\"var(--ui-avatar-color-gradient-start)\"/>\n\t\t\t\t\t\t\t<stop offset=\"1\" stop-color=\"var(--ui-avatar-color-gradient-stop)\"/>\n\t\t\t\t\t\t</linearGradient>\n\t\t\t\t\t</svg>\n\t\t\t\t</div>\n\t\t\t"])), this.getUnicId(), this.getUnicId());
	      }
	      return this.node.avatar;
	    }
	  }]);
	  return AvatarSquareAccent;
	}(AvatarSquareGuest);

	exports.AvatarBase = AvatarBase;
	exports.AvatarRound = AvatarRound;
	exports.AvatarRoundGuest = AvatarRoundGuest;
	exports.AvatarRoundExtranet = AvatarRoundExtranet;
	exports.AvatarRoundAccent = AvatarRoundAccent;
	exports.AvatarHexagon = AvatarHexagon;
	exports.AvatarHexagonGuest = AvatarHexagonGuest;
	exports.AvatarHexagonExtranet = AvatarHexagonExtranet;
	exports.AvatarHexagonAccent = AvatarHexagonAccent;
	exports.AvatarSquare = AvatarSquare;
	exports.AvatarSquareGuest = AvatarSquareGuest;
	exports.AvatarSquareExtranet = AvatarSquareExtranet;
	exports.AvatarSquareAccent = AvatarSquareAccent;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=avatar.bundle.js.map
