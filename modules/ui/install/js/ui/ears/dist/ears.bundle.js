this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-ear ui-ear-right\"></div>\n\t\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-ear ui-ear-left\"></div>\n\t\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-ears-wrapper ", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Ears =
	/*#__PURE__*/
	function () {
	  function Ears(options) {
	    babelHelpers.classCallCheck(this, Ears);
	    this.container = options.container;
	    this.smallSize = options.smallSize || null;
	    this.noScrollbar = options.noScrollbar ? options.noScrollbar : false;
	    this.wrapper = null;
	    this.leftEar = null;
	    this.rightEar = null;
	    this.parentContainer = this.container.parentNode;
	    this.cache = new main_core.Cache.MemoryCache();
	  }

	  babelHelpers.createClass(Ears, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      this.container.addEventListener('scroll', this.toggleEars.bind(this));
	      this.getLeftEar().addEventListener('mouseenter', this.scrollLeft.bind(this));
	      this.getLeftEar().addEventListener('mouseleave', this.stopScroll.bind(this));
	      this.getRightEar().addEventListener('mouseenter', this.scrollRight.bind(this));
	      this.getRightEar().addEventListener('mouseleave', this.stopScroll.bind(this));
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      this.setWrapper();
	      this.bindEvents();
	      setTimeout(function () {
	        if (this.container.scrollWidth > this.container.offsetWidth) {
	          this.toggleRightEar();
	        }
	      }.bind(this), 600);
	    }
	  }, {
	    key: "setWrapper",
	    value: function setWrapper() {
	      this.container.classList.add("ui-ear-container");

	      if (this.noScrollbar) {
	        this.container.classList.add("ui-ear-container-no-scrollbar");
	      }

	      main_core.Dom.append(this.getWrapper(), this.parentContainer);
	    }
	  }, {
	    key: "getWrapper",
	    value: function getWrapper() {
	      var _this = this;

	      return this.cache.remember('wrapper', function () {
	        return main_core.Tag.render(_templateObject(), _this.smallSize ? ' ui-ears-wrapper-sm' : '', _this.getLeftEar(), _this.getRightEar(), _this.container);
	      });
	    }
	  }, {
	    key: "getLeftEar",
	    value: function getLeftEar() {
	      return this.cache.remember('leftEar', function () {
	        return main_core.Tag.render(_templateObject2());
	      });
	    }
	  }, {
	    key: "getRightEar",
	    value: function getRightEar() {
	      return this.cache.remember('rightEar', function () {
	        return main_core.Tag.render(_templateObject3());
	      });
	    }
	  }, {
	    key: "toggleEars",
	    value: function toggleEars() {
	      this.toggleRightEar();
	      this.toggleLeftEar();
	    }
	  }, {
	    key: "toggleRightEar",
	    value: function toggleRightEar() {
	      if (this.container.scrollWidth > this.container.offsetWidth && this.container.offsetWidth + this.container.scrollLeft < this.container.scrollWidth) {
	        this.getRightEar().classList.add("ui-ear-show");
	      } else {
	        this.getRightEar().classList.remove("ui-ear-show");
	      }
	    }
	  }, {
	    key: "toggleLeftEar",
	    value: function toggleLeftEar() {
	      if (this.container.scrollLeft > 0) {
	        this.getLeftEar().classList.add("ui-ear-show");
	      } else {
	        this.getLeftEar().classList.remove("ui-ear-show");
	      }
	    }
	  }, {
	    key: "scrollLeft",
	    value: function scrollLeft() {
	      this.stopScroll();
	      this.scrollInterval = setInterval(function () {
	        this.container.scrollLeft -= 10;
	      }.bind(this), 20);
	    }
	  }, {
	    key: "scrollRight",
	    value: function scrollRight() {
	      this.stopScroll();
	      this.scrollInterval = setInterval(function () {
	        this.container.scrollLeft += 10;
	      }.bind(this), 20);
	    }
	  }, {
	    key: "stopScroll",
	    value: function stopScroll() {
	      if (this.scrollInterval) {
	        clearInterval(this.scrollInterval);
	        this.scrollInterval = 0;
	      }
	    }
	  }]);
	  return Ears;
	}();

	exports.Ears = Ears;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=ears.bundle.js.map
