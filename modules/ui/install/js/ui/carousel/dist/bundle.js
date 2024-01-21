/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_core_events,main_loader) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9;
	class Carousel {
	  constructor(options = {}) {
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
	    this.infinite = options.infinite || false;

	    //node
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
	    this.sliderAllCount = this.content.length;

	    // this.draggable = true;
	    this.defaultSettings();
	  }
	  defaultSettings() {
	    this.defaultParameters = {};
	    for (let key in this) {
	      this.defaultParameters[key] = this[key];
	    }
	  }
	  getItem(item, index) {
	    let itemClass = this.slidActive - 1 === index ? '--active' : '';
	    let slideItem = main_core.Tag.render(_t || (_t = _`
			<div class="ui-carousel__slide ${0}">
				${0}
			</div>
		`), itemClass, item.html);
	    main_core.Event.bind(slideItem, 'mouseenter', this.stopSlide.bind(this));
	    main_core.Event.bind(slideItem, 'mouseleave', this.runSlide.bind(this));
	    if (this.arrayItems.indexOf(item) === -1) {
	      this.arrayItems.push(slideItem);
	    }
	    return slideItem;
	  }
	  setTrackCharacteristics() {
	    if (this.arrayItemsWidth === 0 && this.arrayItemsHeight === 0) {
	      for (let i = 0; i < this.arrayItems.length; i++) {
	        this.arrayItemsWidth += this.arrayItems[i].offsetWidth;
	        this.arrayItemsHeight += this.arrayItems[i].offsetHeight;
	      }
	    }
	  }
	  initSlider() {
	    this.runSlide();
	  }
	  stopSlide() {
	    clearInterval(this.sliderInterval);
	  }
	  changeActivePoint() {
	    if (this.arrayDotsItem.length > 1) {
	      for (let i = 0; i < this.arrayDotsItem.length; i++) {
	        if (this.arrayDotsItem[i].classList.contains('--active')) {
	          this.arrayDotsItem[i].classList.remove('--active');
	        }
	      }
	      this.arrayDotsItem[this.slidActive - 1].classList.add('--active');
	    }
	  }
	  changeActiveSlide() {
	    if (this.arrayItems.length > 1) {
	      for (let i = 0; i < this.arrayItems.length; i++) {
	        if (this.arrayItems[i].classList.contains('--active')) {
	          this.arrayItems[i].classList.remove('--active');
	        }
	      }
	      this.arrayItems[this.slidActive - 1].classList.add('--active');
	    }
	  }
	  changeActiveArrow(id) {
	    if (!this.infinite) {
	      this.arrayArrowsItem.map(item => {
	        item.classList.remove('--disabled');
	      });
	      if (this.slidActive === this.sliderAllCount || id >= this.isLastSlide && this.isLastSlide > 0) {
	        this.arrayArrowsItem[this.arrayArrowsItem.length - 1].classList.add('--disabled');
	      } else if (this.slidActive === 1) {
	        this.arrayArrowsItem[0].classList.add('--disabled');
	      }
	    }
	  }
	  changeActive(id) {
	    this.changeActivePoint();
	    this.changeActiveSlide();
	    if (!this.infinite) {
	      this.changeActiveArrow(id);
	    }
	  }
	  runSlide() {
	    if (this.sliderAllCount > 1 && this.autoPlay) {
	      this.trackOffsetStep();
	      this.sliderInterval = setInterval(() => {
	        let sliderNum = this.slidActive + 1 > this.sliderAllCount ? 1 : this.slidActive + 1;
	        this.showSlide(sliderNum);
	      }, this.autoPlaySpeed);
	    }
	  }
	  trackOffsetStep() {
	    if (this.offsetCache === 0) {
	      this.offsetCache = this.wrapper ? this.wrapper.offsetWidth : 0;
	    }
	    return this.offsetCache;
	  }
	  getTrackShift(id) {
	    let shift = 0;
	    if (id > 1) {
	      let cycleLength = 0;
	      if (id >= this.isLastSlide && this.isLastSlide > 0) {
	        cycleLength = this.isLastSlide - 1;
	      } else {
	        cycleLength = id - 1;
	      }
	      if (this.vertical) {
	        let sumShift = this.arrayItemsHeight;
	        for (let i = 0; i < cycleLength; i++) {
	          sumShift -= this.arrayItems[i].offsetHeight;
	          if (sumShift < this.wrapper.offsetHeight) {
	            this.isLastSlide = id;
	            shift += sumShift + this.arrayItems[i].offsetHeight - this.wrapper.offsetHeight;
	          } else {
	            shift += this.arrayItems[i].offsetHeight;
	          }
	        }
	      } else {
	        let sumShift = this.arrayItemsWidth;
	        for (let i = 0; i < cycleLength; i++) {
	          sumShift -= this.arrayItems[i].offsetWidth;
	          if (sumShift < this.wrapper.offsetWidth) {
	            this.isLastSlide = id;
	            shift += sumShift + this.arrayItems[i].offsetWidth - this.wrapper.offsetWidth;
	          } else {
	            shift += this.arrayItems[i].offsetWidth;
	          }
	        }
	      }
	    }
	    return shift;
	  }
	  showSlide(id) {
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
	  nextSlide() {
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
	  prevSlide() {
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
	  getArrows() {
	    let verticalClass = this.vertical ? '--vertical' : '';
	    this.nodeArrows = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="ui-carousel__arrows--container ${0}"></div>
		`), verticalClass);
	    let arrowDisabledClass = this.infinite ? '' : '--disabled';
	    let arrowPrev = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-carousel__arrow ${0} --prev ${0} ${0}"></div>
		`), this.arrowsClass, arrowDisabledClass, verticalClass);
	    let arrowNext = main_core.Tag.render(_t4 || (_t4 = _`
			<div class="ui-carousel__arrow ${0} --next ${0}"></div>
		`), this.arrowsClass, verticalClass);
	    main_core.Event.bind(arrowPrev, 'click', this.prevSlide.bind(this));
	    main_core.Event.bind(arrowNext, 'click', this.nextSlide.bind(this));
	    this.arrayArrowsItem.push(arrowPrev);
	    this.arrayArrowsItem.push(arrowNext);
	    this.nodeArrows.append(arrowPrev);
	    this.nodeArrows.append(arrowNext);
	    return this.nodeArrows;
	  }
	  getDots() {
	    this.nodeDots = main_core.Tag.render(_t5 || (_t5 = _`
			<div class="ui-carousel__dots ${0}"></div>
		`), this.dotsClass);
	    for (let i = 0; i < this.sliderAllCount; i++) {
	      let dotClassActive = this.slidActive === i + 1 ? '--active' : '';
	      let nodeDotsItem = main_core.Tag.render(_t6 || (_t6 = _`
				<div class="ui-carousel__dots--item ${0} ${0}">
				</div>
			`), this.dotsClass, dotClassActive);
	      main_core.Event.bind(nodeDotsItem, 'click', this.showSlide.bind(this, i + 1));
	      this.arrayDotsItem.push(nodeDotsItem);
	      this.nodeDots.append(nodeDotsItem);
	    }
	    return this.nodeDots;
	  }
	  getCarouselContent() {
	    let verticalClass = this.vertical ? '--vertical' : '';
	    this.track = main_core.Tag.render(_t7 || (_t7 = _`
			<div class="ui-carousel__track ${0}"></div>
		`), verticalClass);
	    this.content.map((item, index) => {
	      this.track.appendChild(this.getItem(item, index));
	    });
	    this.wrapper = main_core.Tag.render(_t8 || (_t8 = _`
			<div class="ui-carousel__wrapper">
				${0}
			</div>
		`), this.track);
	    this.carouselContainer = main_core.Tag.render(_t9 || (_t9 = _`
			<div class="ui-carousel__container ui-carousel__scope ${0}">
				${0}
			</div>
		`), verticalClass, this.wrapper);
	    if (this.dots && this.sliderAllCount > 1) {
	      this.carouselContainer.appendChild(this.getDots());
	    }
	    if (this.arrows && this.sliderAllCount > 1) {
	      this.carouselContainer.appendChild(this.getArrows());
	    }
	    return this.carouselContainer;
	  }
	  responsiveCarousel() {
	    if (this.responsive) {
	      for (let i = 0; i < this.responsive.length; i++) {
	        if (window.innerWidth > this.responsive[0].breakpoint) {
	          let newData = this.defaultParameters;
	          for (let key in newData) {
	            this[key] = newData[key];
	          }
	          break;
	        } else if (window.innerWidth <= this.responsive[i].breakpoint) {
	          let newData = this.responsive[i].settings;
	          for (let key in newData) {
	            this[key] = newData[key];
	          }
	        }
	      }
	    }
	  }
	  carouselResize() {
	    if (this.responsive) {
	      window.addEventListener('resize', () => {
	        for (let i = 0; i < this.responsive.length; i++) {
	          if (this.windowWidth > this.responsive[i].breakpoint && window.innerWidth < this.responsive[i].breakpoint) {
	            this.windowWidth = window.innerWidth;
	            this.init();
	          } else if (this.windowWidth < this.responsive[i].breakpoint && window.innerWidth > this.responsive[i].breakpoint) {
	            this.windowWidth = window.innerWidth;
	            this.init();
	          }
	        }
	      });
	    }
	  }
	  init() {
	    if (this.target && this.content) {
	      this.windowWidth = window.innerWidth;
	      main_core.Dom.clean(this.target);
	      this.responsiveCarousel();
	      this.target.appendChild(this.getCarouselContent());
	      this.initSlider();
	      this.carouselResize();
	    }
	  }
	}

	exports.Carousel = Carousel;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event,BX));
//# sourceMappingURL=bundle.js.map
