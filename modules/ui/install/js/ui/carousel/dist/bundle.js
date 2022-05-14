this.BX = this.BX || {};
(function (exports,main_core,main_core_events,main_loader) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;
	var Carousel = /*#__PURE__*/function () {
	  function Carousel() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Carousel);
	    this.offsetCache = 0;
	    this.target = options.target || null;
	    this.content = options.content || null;
	    this.responsive = options.responsive || null;
	    this.autoPlaySpeed = options.autoPlaySpeed || 1000;
	    this.autoPlay = options.autoPlay || false;
	    this.dots = options.dots || false;
	    this.dotsClass = options.dotsClass || '--default';
	    this.arrows = options.arrows || false;
	    this.arrowsClass = options.arrowsClass || '--default';
	    this.vertical = options.vertical || false;
	    this.infinite = options.infinite || false; //node

	    this.carouselContainer = null;
	    this.wrapper = null;
	    this.track = null;
	    this.nodeDots = null;
	    this.nodeArrows = null;
	    this.arrayDotsItem = [];
	    this.arrayItems = [];
	    this.arrayItemsWidth = 0;
	    this.arrayItemsHeight = 0;
	    this.isLastSlide = 0;
	    this.arrayArrowsItem = [];
	    this.slidActive = 1;
	    this.sliderInterval = null;
	    this.sliderAllCount = this.content.length; // this.draggable = true;

	    this.defaultSettings();
	  }

	  babelHelpers.createClass(Carousel, [{
	    key: "defaultSettings",
	    value: function defaultSettings() {
	      this.defaultParameters = {};

	      for (var key in this) {
	        this.defaultParameters[key] = this[key];
	      }
	    }
	  }, {
	    key: "getItem",
	    value: function getItem(item, index) {
	      var itemClass = this.slidActive - 1 === index ? '--active' : '';
	      var slideItem = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-carousel__slide ", "\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), itemClass, item.html);
	      main_core.Event.bind(slideItem, 'mouseenter', this.stopSlide.bind(this));
	      main_core.Event.bind(slideItem, 'mouseleave', this.runSlide.bind(this));

	      if (this.arrayItems.indexOf(item) === -1) {
	        this.arrayItems.push(slideItem);
	      }

	      return slideItem;
	    }
	  }, {
	    key: "setTrackCharacteristics",
	    value: function setTrackCharacteristics() {
	      if (this.arrayItemsWidth === 0 && this.arrayItemsHeight === 0) {
	        for (var i = 0; i < this.arrayItems.length; i++) {
	          this.arrayItemsWidth += this.arrayItems[i].offsetWidth;
	          this.arrayItemsHeight += this.arrayItems[i].offsetHeight;
	        }
	      }
	    }
	  }, {
	    key: "initSlider",
	    value: function initSlider() {
	      this.runSlide();
	    }
	  }, {
	    key: "stopSlide",
	    value: function stopSlide() {
	      clearInterval(this.sliderInterval);
	    }
	  }, {
	    key: "changeActivePoint",
	    value: function changeActivePoint() {
	      if (this.arrayDotsItem.length > 1) {
	        for (var i = 0; i < this.arrayDotsItem.length; i++) {
	          if (this.arrayDotsItem[i].classList.contains('--active')) {
	            this.arrayDotsItem[i].classList.remove('--active');
	          }
	        }

	        this.arrayDotsItem[this.slidActive - 1].classList.add('--active');
	      }
	    }
	  }, {
	    key: "changeActiveSlide",
	    value: function changeActiveSlide() {
	      if (this.arrayItems.length > 1) {
	        for (var i = 0; i < this.arrayItems.length; i++) {
	          if (this.arrayItems[i].classList.contains('--active')) {
	            this.arrayItems[i].classList.remove('--active');
	          }
	        }

	        this.arrayItems[this.slidActive - 1].classList.add('--active');
	      }
	    }
	  }, {
	    key: "changeActiveArrow",
	    value: function changeActiveArrow(id) {
	      if (!this.infinite) {
	        this.arrayArrowsItem.map(function (item) {
	          item.classList.remove('--disabled');
	        });

	        if (this.slidActive === this.sliderAllCount || id >= this.isLastSlide && this.isLastSlide > 0) {
	          this.arrayArrowsItem[this.arrayArrowsItem.length - 1].classList.add('--disabled');
	        } else if (this.slidActive === 1) {
	          this.arrayArrowsItem[0].classList.add('--disabled');
	        }
	      }
	    }
	  }, {
	    key: "changeActive",
	    value: function changeActive(id) {
	      this.changeActivePoint();
	      this.changeActiveSlide();

	      if (!this.infinite) {
	        this.changeActiveArrow(id);
	      }
	    }
	  }, {
	    key: "runSlide",
	    value: function runSlide() {
	      var _this = this;

	      if (this.sliderAllCount > 1 && this.autoPlay) {
	        this.trackOffsetStep();
	        this.sliderInterval = setInterval(function () {
	          var sliderNum = _this.slidActive + 1 > _this.sliderAllCount ? 1 : _this.slidActive + 1;

	          _this.showSlide(sliderNum);
	        }, this.autoPlaySpeed);
	      }
	    }
	  }, {
	    key: "trackOffsetStep",
	    value: function trackOffsetStep() {
	      if (this.offsetCache === 0) {
	        this.offsetCache = this.wrapper ? this.wrapper.offsetWidth : 0;
	      }

	      return this.offsetCache;
	    }
	  }, {
	    key: "getTrackShift",
	    value: function getTrackShift(id) {
	      var shift = 0;

	      if (id > 1) {
	        var cycleLength = 0;

	        if (id >= this.isLastSlide && this.isLastSlide > 0) {
	          cycleLength = this.isLastSlide - 1;
	        } else {
	          cycleLength = id - 1;
	        }

	        if (this.vertical) {
	          var sumShift = this.arrayItemsHeight;

	          for (var i = 0; i < cycleLength; i++) {
	            sumShift -= this.arrayItems[i].offsetHeight;

	            if (sumShift < this.wrapper.offsetHeight) {
	              this.isLastSlide = id;
	              shift += sumShift + this.arrayItems[i].offsetHeight - this.wrapper.offsetHeight;
	            } else {
	              shift += this.arrayItems[i].offsetHeight;
	            }
	          }
	        } else {
	          var _sumShift = this.arrayItemsWidth;

	          for (var _i = 0; _i < cycleLength; _i++) {
	            _sumShift -= this.arrayItems[_i].offsetWidth;

	            if (_sumShift < this.wrapper.offsetWidth) {
	              this.isLastSlide = id;
	              shift += _sumShift + this.arrayItems[_i].offsetWidth - this.wrapper.offsetWidth;
	            } else {
	              shift += this.arrayItems[_i].offsetWidth;
	            }
	          }
	        }
	      }

	      return shift;
	    }
	  }, {
	    key: "showSlide",
	    value: function showSlide(id) {
	      this.setTrackCharacteristics();

	      if (id > 0 && id <= this.sliderAllCount) {
	        this.slidActive = id;

	        if (this.track) {
	          if (this.vertical) {
	            this.track.style.transform = 'translateY(' + -this.getTrackShift(id) + 'px)';
	          } else {
	            this.track.style.transform = 'translateX(' + -this.getTrackShift(id) + 'px)';
	          }
	        }
	      }

	      clearInterval(this.sliderInterval);
	      this.runSlide();
	      this.changeActive(id);
	    }
	  }, {
	    key: "nextSlide",
	    value: function nextSlide() {
	      if (this.infinite) {
	        if (this.slidActive === this.sliderAllCount) {
	          this.slidActive = 1;
	          this.showSlide(this.slidActive);
	        } else if (this.slidActive < this.sliderAllCount) {
	          this.slidActive += 1;
	          this.showSlide(this.slidActive);
	        }
	      } else {
	        if (this.slidActive < this.sliderAllCount) {
	          this.slidActive += 1;
	          this.showSlide(this.slidActive);
	        }
	      }
	    }
	  }, {
	    key: "prevSlide",
	    value: function prevSlide() {
	      if (this.infinite) {
	        if (this.slidActive === 1) {
	          this.slidActive = this.sliderAllCount;
	          this.showSlide(this.slidActive);
	        } else if (this.slidActive > 1) {
	          this.slidActive -= 1;
	          this.showSlide(this.slidActive);
	        }
	      } else {
	        if (this.slidActive > 1) {
	          this.slidActive -= 1;
	          this.showSlide(this.slidActive);
	        }
	      }
	    }
	  }, {
	    key: "getArrows",
	    value: function getArrows() {
	      var verticalClass = this.vertical ? '--vertical' : '';
	      this.nodeArrows = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-carousel__arrows--container ", "\"></div>\n\t\t"])), verticalClass);
	      var arrowDisabledClass = this.infinite ? '' : '--disabled';
	      var arrowPrev = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-carousel__arrow ", " --prev ", " ", "\"></div>\n\t\t"])), this.arrowsClass, arrowDisabledClass, verticalClass);
	      var arrowNext = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-carousel__arrow ", " --next ", "\"></div>\n\t\t"])), this.arrowsClass, verticalClass);
	      main_core.Event.bind(arrowPrev, 'click', this.prevSlide.bind(this));
	      main_core.Event.bind(arrowNext, 'click', this.nextSlide.bind(this));
	      this.arrayArrowsItem.push(arrowPrev);
	      this.arrayArrowsItem.push(arrowNext);
	      this.nodeArrows.append(arrowPrev);
	      this.nodeArrows.append(arrowNext);
	      return this.nodeArrows;
	    }
	  }, {
	    key: "getDots",
	    value: function getDots() {
	      this.nodeDots = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-carousel__dots ", "\"></div>\n\t\t"])), this.dotsClass);

	      for (var i = 0; i < this.sliderAllCount; i++) {
	        var dotClassActive = this.slidActive === i + 1 ? '--active' : '';
	        var nodeDotsItem = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-carousel__dots--item ", " ", "\">\n\t\t\t\t</div>\n\t\t\t"])), this.dotsClass, dotClassActive);
	        main_core.Event.bind(nodeDotsItem, 'click', this.showSlide.bind(this, i + 1));
	        this.arrayDotsItem.push(nodeDotsItem);
	        this.nodeDots.append(nodeDotsItem);
	      }

	      return this.nodeDots;
	    }
	  }, {
	    key: "getCarouselContent",
	    value: function getCarouselContent() {
	      var _this2 = this;

	      var verticalClass = this.vertical ? '--vertical' : '';
	      this.track = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-carousel__track ", "\"></div>\n\t\t"])), verticalClass);
	      this.content.map(function (item, index) {
	        _this2.track.appendChild(_this2.getItem(item, index));
	      });
	      this.wrapper = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-carousel__wrapper\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.track);
	      this.carouselContainer = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-carousel__container ui-carousel__scope ", "\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), verticalClass, this.wrapper);

	      if (this.dots && this.sliderAllCount > 1) {
	        this.carouselContainer.appendChild(this.getDots());
	      }

	      if (this.arrows && this.sliderAllCount > 1) {
	        this.carouselContainer.appendChild(this.getArrows());
	      }

	      return this.carouselContainer;
	    }
	  }, {
	    key: "responsiveCarousel",
	    value: function responsiveCarousel() {
	      if (this.responsive) {
	        for (var i = 0; i < this.responsive.length; i++) {
	          if (window.innerWidth > this.responsive[0].breakpoint) {
	            var newData = this.defaultParameters;

	            for (var key in newData) {
	              this[key] = newData[key];
	            }

	            break;
	          } else if (window.innerWidth <= this.responsive[i].breakpoint) {
	            var _newData = this.responsive[i].settings;

	            for (var _key in _newData) {
	              this[_key] = _newData[_key];
	            }
	          }
	        }
	      }
	    }
	  }, {
	    key: "carouselResize",
	    value: function carouselResize() {
	      var _this3 = this;

	      if (this.responsive) {
	        window.addEventListener('resize', function () {
	          for (var i = 0; i < _this3.responsive.length; i++) {
	            if (_this3.windowWidth > _this3.responsive[i].breakpoint && window.innerWidth < _this3.responsive[i].breakpoint) {
	              _this3.windowWidth = window.innerWidth;

	              _this3.init();
	            } else if (_this3.windowWidth < _this3.responsive[i].breakpoint && window.innerWidth > _this3.responsive[i].breakpoint) {
	              _this3.windowWidth = window.innerWidth;

	              _this3.init();
	            }
	          }
	        });
	      }
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      if (this.target && this.content) {
	        this.windowWidth = window.innerWidth;
	        main_core.Dom.clean(this.target);
	        this.responsiveCarousel();
	        this.target.appendChild(this.getCarouselContent());
	        this.initSlider();
	        this.carouselResize();
	      }
	    }
	  }]);
	  return Carousel;
	}();

	exports.Carousel = Carousel;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event,BX));
//# sourceMappingURL=bundle.js.map
