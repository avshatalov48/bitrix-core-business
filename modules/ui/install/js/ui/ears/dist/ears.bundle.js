this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var TouchController = /*#__PURE__*/function () {
	  function TouchController(_ref) {
	    var target = _ref.target;
	    babelHelpers.classCallCheck(this, TouchController);
	    this.target = target ? target : null;
	    this.pos = {
	      top: 0,
	      left: 0,
	      x: 0,
	      y: 0
	    };
	    this.touchInit = false;
	    this.init();
	  }

	  babelHelpers.createClass(TouchController, [{
	    key: "init",
	    value: function init() {
	      if (!this.target) {
	        console.warn('BX.UI.Ears: TouchController not initialized');
	        return;
	      }

	      this.target.addEventListener('mousedown', this.mouseDownHandler.bind(this));
	      this.target.addEventListener('mousemove', this.mouseMoveHandler.bind(this));
	      this.target.addEventListener('mouseup', this.mouseUpHandler.bind(this));
	      this.target.addEventListener('mouseleave', this.mouseUpHandler.bind(this));
	    }
	  }, {
	    key: "mouseDownHandler",
	    value: function mouseDownHandler(ev) {
	      this.touchInit = true;
	      this.target.style.cursor = 'grabbing';
	      this.target.style.userSelect = 'none';
	      this.target.parentNode.classList.add('--grabbing');
	      this.pos = {
	        left: this.target.scrollLeft,
	        x: ev.clientX
	      };
	    }
	  }, {
	    key: "mouseMoveHandler",
	    value: function mouseMoveHandler(ev) {
	      if (!this.touchInit) {
	        return;
	      }

	      var dx = ev.clientX - this.pos.x;
	      this.target.scrollLeft = this.pos.left - dx;
	    }
	  }, {
	    key: "mouseUpHandler",
	    value: function mouseUpHandler() {
	      this.touchInit = false;
	      this.target.style.cursor = 'grab';
	      this.target.style.removeProperty('user-select');
	      this.target.parentNode.classList.remove('--grabbing');
	    }
	  }]);
	  return TouchController;
	}();

	var _templateObject, _templateObject2, _templateObject3;
	var Ears = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Ears, _EventEmitter);

	  function Ears(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Ears);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Ears).apply(this, arguments));

	    _this.setEventNamespace('BX.UI.Ears');

	    _this.container = options.container || null;
	    _this.smallSize = options.smallSize || null;
	    _this.noScrollbar = options.noScrollbar ? options.noScrollbar : false;
	    _this.className = options.className ? options.className : null;
	    _this.mousewheel = options.mousewheel || null;
	    _this.touchScroll = options.touchScroll || null; // layouts

	    _this.wrapper = null;
	    _this.leftEar = null;
	    _this.rightEar = null;
	    _this.parentContainer = main_core.Type.isDomNode(_this.container) ? _this.container.parentNode : null;
	    _this.delay = 12;
	    _this.scrollTimeout = null;
	    _this.cache = new main_core.Cache.MemoryCache();
	    return _this;
	  }

	  babelHelpers.createClass(Ears, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      this.container.addEventListener('scroll', this.toggleEars.bind(this));

	      if (this.mousewheel) {
	        this.container.addEventListener('wheel', this.onWheel.bind(this));
	      }

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
	      var _this2 = this;

	      if (!this.container) {
	        console.warn('BX.UI.Ears.Preview: \'container\' is not defined');
	        return;
	      }

	      this.setWrapper();
	      this.bindEvents();

	      if (this.touchScroll) {
	        this.initTouchScroll();
	      }

	      setTimeout(function () {
	        if (_this2.container.scrollWidth > _this2.container.offsetWidth) {
	          _this2.toggleRightEar();

	          var activeItem = _this2.container.querySelector('[data-role="ui-ears-active"]');

	          activeItem ? _this2.scrollToActiveItem(activeItem) : null;
	        }
	      }, 600);
	      return this;
	    }
	  }, {
	    key: "scrollToActiveItem",
	    value: function scrollToActiveItem(activeItem) {
	      var _this3 = this;

	      var scrollToPoint = activeItem.offsetLeft - (this.container.offsetWidth / 2 - activeItem.offsetWidth / 2);
	      var scrollWidth = 0;
	      var interval = setInterval(function () {
	        if (scrollWidth >= scrollToPoint || scrollWidth + _this3.container.offsetWidth >= _this3.container.scrollWidth) {
	          clearInterval(interval);
	        }

	        _this3.container.scrollLeft = scrollWidth += 10;
	      }, 10);
	    }
	  }, {
	    key: "onWheel",
	    value: function onWheel(event) {
	      var _this4 = this;

	      if (event.deltaY < 0 || event.deltaX > 0) {
	        this.scrollRight();
	      } else {
	        this.scrollLeft();
	      }

	      clearTimeout(this.scrollTimeout);
	      this.scrollTimeout = setTimeout(function () {
	        return _this4.stopScroll();
	      }, 150);
	      event.preventDefault();
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
	      var _this5 = this;

	      return this.cache.remember('wrapper', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='ui-ears-wrapper ", " ", "'>\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), _this5.smallSize ? ' ui-ears-wrapper-sm' : '', _this5.className ? _this5.className : '', _this5.getLeftEar(), _this5.getRightEar(), _this5.container);
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
	      var previous = this.container.scrollLeft;
	      this.container.scrollLeft -= 10;
	      this.emit('onEarsAreMoved');

	      if (this.container.scrollLeft <= 0 && previous > 0) {
	        this.emit('onEarsAreHidden');
	      }

	      this.setDelay();
	      this.scrollInterval = setInterval(this.scrollLeft.bind(this), this.delay);
	      this.left = true;
	    }
	  }, {
	    key: "scrollRight",
	    value: function scrollRight() {
	      this.stopScroll('left');
	      this.container.scrollLeft += 10;
	      this.emit('onEarsAreMoved');

	      if (this.container.scrollLeft <= 10) {
	        this.emit('onEarsAreShown');
	      }

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
	        this.delay = 12;
	      }

	      if (this.left) {
	        if (conditionLeft) {
	          this.delay = 25;
	        } else {
	          this.delay = 12;
	        }
	      }

	      if (this.right) {
	        if (conditionRight) {
	          this.delay = 25;
	        } else {
	          this.delay = 12;
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
	  }, {
	    key: "initTouchScroll",
	    value: function initTouchScroll() {
	      new TouchController({
	        target: this.container
	      });
	    }
	  }]);
	  return Ears;
	}(main_core_events.EventEmitter);

	exports.Ears = Ears;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event));
//# sourceMappingURL=ears.bundle.js.map
