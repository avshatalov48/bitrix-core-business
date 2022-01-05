this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;
	var Ears = /*#__PURE__*/function () {
	  function Ears(options) {
	    babelHelpers.classCallCheck(this, Ears);
	    this.container = options.container;
	    this.smallSize = options.smallSize || null;
	    this.noScrollbar = options.noScrollbar ? options.noScrollbar : false;
	    this.className = options.className ? options.className : null;
	    this.wrapper = null;
	    this.leftEar = null;
	    this.rightEar = null;
	    this.parentContainer = this.container.parentNode;
	    this.delay = 6;
	    this.scrollTimeout = null;
	    this.cache = new main_core.Cache.MemoryCache();
	  }

	  babelHelpers.createClass(Ears, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      this.container.addEventListener('scroll', this.toggleEars.bind(this));
	      this.container.addEventListener('wheel', this.onWheel.bind(this));
	      this.getLeftEar().addEventListener('mouseenter', this.scrollLeft.bind(this));
	      this.getLeftEar().addEventListener('mouseleave', this.stopScroll.bind(this));
	      this.getLeftEar().addEventListener('mousedown', this.stopScroll.bind(this));
	      this.getLeftEar().addEventListener('mouseup', this.scrollLeft.bind(this));
	      this.getRightEar().addEventListener('mouseenter', this.scrollRight.bind(this));
	      this.getRightEar().addEventListener('mouseleave', this.stopScroll.bind(this));
	      this.getRightEar().addEventListener('mousedown', this.stopScroll.bind(this));
	      this.getRightEar().addEventListener('mouseup', this.scrollRight.bind(this));
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      var _this = this;

	      this.setWrapper();
	      this.bindEvents();
	      setTimeout(function () {
	        if (_this.container.scrollWidth > _this.container.offsetWidth) {
	          _this.toggleRightEar();

	          var activeItem = _this.container.querySelector('[data-role="ui-ears-active"]');

	          activeItem ? _this.scrollToActiveItem(activeItem) : null;
	        }
	      }, 600);
	    }
	  }, {
	    key: "scrollToActiveItem",
	    value: function scrollToActiveItem(activeItem) {
	      var _this2 = this;

	      var scrollToPoint = activeItem.offsetLeft - (this.container.offsetWidth / 2 - activeItem.offsetWidth / 2);
	      var scrollWidth = 0;
	      var interval = setInterval(function () {
	        if (scrollWidth >= scrollToPoint || scrollWidth + _this2.container.offsetWidth >= _this2.container.scrollWidth) {
	          clearInterval(interval);
	        }

	        _this2.container.scrollLeft = scrollWidth += 10;
	      }, 10);
	    }
	  }, {
	    key: "onWheel",
	    value: function onWheel(event) {
	      var _this3 = this;

	      if (event.deltaY < 0 || event.deltaX > 0) {
	        this.scrollRight();
	      } else {
	        this.scrollLeft();
	      }

	      clearTimeout(this.scrollTimeout);
	      this.scrollTimeout = setTimeout(function () {
	        return _this3.stopScroll();
	      }, 150);
	    }
	  }, {
	    key: "setWrapper",
	    value: function setWrapper() {
	      this.container.classList.add('ui-ear-container');

	      if (this.noScrollbar) {
	        this.container.classList.add('ui-ear-container-no-scrollbar');
	      }

	      main_core.Dom.append(this.getWrapper(), this.parentContainer);
	    }
	  }, {
	    key: "getWrapper",
	    value: function getWrapper() {
	      var _this4 = this;

	      return this.cache.remember('wrapper', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='ui-ears-wrapper ", " ", "'>\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), _this4.smallSize ? ' ui-ears-wrapper-sm' : '', _this4.className ? _this4.className : '', _this4.getLeftEar(), _this4.getRightEar(), _this4.container);
	      });
	    }
	  }, {
	    key: "getLeftEar",
	    value: function getLeftEar() {
	      return this.cache.remember('leftEar', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='ui-ear ui-ear-left'></div>\n\t\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getRightEar",
	    value: function getRightEar() {
	      return this.cache.remember('rightEar', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='ui-ear ui-ear-right'></div>\n\t\t\t\t"])));
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
	        this.getRightEar().classList.add('ui-ear-show');
	      } else {
	        this.getRightEar().classList.remove('ui-ear-show');
	      }
	    }
	  }, {
	    key: "toggleLeftEar",
	    value: function toggleLeftEar() {
	      if (this.container.scrollLeft > 0) {
	        this.getLeftEar().classList.add('ui-ear-show');
	      } else {
	        this.getLeftEar().classList.remove('ui-ear-show');
	      }
	    }
	  }, {
	    key: "scrollLeft",
	    value: function scrollLeft() {
	      this.stopScroll('right');
	      this.container.scrollLeft -= 10;
	      this.setDelay();
	      this.scrollInterval = setInterval(this.scrollLeft.bind(this), this.delay);
	      this.left = true;
	    }
	  }, {
	    key: "scrollRight",
	    value: function scrollRight() {
	      this.stopScroll('left');
	      this.container.scrollLeft += 10;
	      this.setDelay();
	      this.scrollInterval = setInterval(this.scrollRight.bind(this), this.delay);
	      this.right = true;
	    }
	  }, {
	    key: "setDelay",
	    value: function setDelay() {
	      if (this.container.scrollWidth < this.container.offsetWidth * 1.6) {
	        this.delay = 20;
	        return;
	      }

	      var fullScrollLeft = this.container.scrollWidth - this.container.offsetWidth;
	      var conditionRight = this.container.scrollLeft > fullScrollLeft / 1.3;
	      var conditionLeft = this.container.scrollLeft < fullScrollLeft / 4;

	      if (this.container.scrollLeft === fullScrollLeft) {
	        this.delay = 6;
	      }

	      if (this.left) {
	        if (conditionLeft) {
	          this.delay = 25;
	        } else {
	          this.delay = 6;
	        }
	      }

	      if (this.right) {
	        if (conditionRight) {
	          this.delay = 25;
	        } else {
	          this.delay = 6;
	        }
	      }
	    }
	  }, {
	    key: "stopScroll",
	    value: function stopScroll(direction) {
	      if (this.scrollInterval) {
	        clearInterval(this.scrollInterval);
	        this.scrollInterval = 0;
	      }

	      if (direction === 'right') {
	        this.right = false;
	      } else if (direction === 'left') {
	        this.left = false;
	      }
	    }
	  }]);
	  return Ears;
	}();

	exports.Ears = Ears;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=ears.bundle.js.map
