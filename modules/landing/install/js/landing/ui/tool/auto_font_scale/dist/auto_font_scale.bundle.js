this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core) {
	'use strict';

	class Entry {
	  constructor(element) {
	    this.maxHeight = 0;
	    this.element = element;
	    Entry.MIN_SIZE = 8;
	    this.startSize = parseInt(main_core.Dom.style(element, "font-size"));
	    this.currentSize = this.startSize;
	    this.letterSpacingRatio = main_core.Dom.style(element, "letter-spacing");
	    this.letterSpacingRatio = parseFloat(this.letterSpacingRatio) || 0;
	    this.letterSpacingRatio /= this.startSize;
	    this.currentLetterSpacing = Entry.MIN_LETTER_SPACING;
	    this.startLineHeight = parseFloat(main_core.Dom.style(element, "line-height"));
	    this.startLineHeight = main_core.Type.isNumber(this.startLineHeight) ? (this.startLineHeight / this.startSize).toFixed(1) : Entry.MIN_LINE_HEIGHT;
	    this.currentLineHeight = this.startLineHeight;
	    this.calcCurrentWidth();
	    this.calcMaxHeight();
	  }

	  /**
	   * Calculate max height by parent element
	   */
	  calcMaxHeight() {
	    this.maxHeight = document.documentElement.clientHeight * 0.9;
	    if (this.element.offsetParent) {
	      // todo: need check parent height if it has, f.e. min-height: 75vh?
	      this.maxHeight = Math.min(this.maxHeight, this.element.offsetParent.clientHeight);
	    }
	  }
	  calcCurrentWidth() {
	    main_core.Dom.addClass(this.element, Entry.WIDTH_RESET_CLASS);
	    this.prevWidth = this.element.clientWidth;
	    main_core.Dom.removeClass(this.element, Entry.WIDTH_RESET_CLASS);
	  }

	  /**
	   * Resets font size style
	   */
	  resetSize() {
	    this.element.style.setProperty('font-size', null);
	    this.element.style.setProperty('letter-spacing', null);
	    this.element.style.setProperty('line-height', null);
	    this.currentSize = this.startSize;
	    this.currentLineHeight = this.startLineHeight;
	    this.currentLetterSpacing = this.letterSpacingRatio * this.startSize;
	  }

	  /**
	   * Check needed and adjust
	   */
	  adjust() {
	    this.calcMaxHeight();
	    if (this.isNeedDecrease()) {
	      this.decreaseSize();
	    } else if (this.isNeedIncrease()) {
	      this.increaseSize();
	    }
	  }

	  /**
	   * Check if need decrease size
	   * @return {boolean}
	   */
	  isNeedDecrease() {
	    const fullScrollWidth = this.element.scrollWidth;
	    main_core.Dom.addClass(this.element, Entry.WIDTH_RESET_CLASS);
	    const res = (fullScrollWidth > this.element.clientWidth || this.element.offsetHeight > this.maxHeight) && this.currentSize > Entry.MIN_SIZE;
	    main_core.Dom.removeClass(this.element, Entry.WIDTH_RESET_CLASS);
	    return res;
	  }

	  /**
	   * Decrease size step-by-step, until text is big
	   */
	  decreaseSize() {
	    let newSize = this.currentSize - this.currentSize * Entry.STEP_SIZE_PERCENTS / 100;
	    newSize = Math.floor(newSize);
	    newSize = Math.max(Entry.MIN_SIZE, newSize);
	    if (this.currentSize !== newSize) {
	      if (!this.intervalId) {
	        this.intervalId = setInterval(() => {
	          if (this.isNeedDecrease()) {
	            this.decreaseSize();
	          } else {
	            this.finishAdjust();
	          }
	        });
	      }
	      this.setFontSize(newSize);
	    }
	  }

	  /**
	   * Check if need increase size
	   * @return {boolean}
	   */
	  isNeedIncrease() {
	    if (this.isNeedDecrease()) {
	      return false;
	    }
	    main_core.Dom.addClass(this.element, Entry.WIDTH_RESET_CLASS);
	    const res = this.element.clientWidth > this.prevWidth && this.element.clientWidth - this.prevWidth > this.element.clientWidth * Entry.STEP_SIZE_PERCENTS / 100 && this.currentSize < this.startSize;
	    main_core.Dom.removeClass(this.element, Entry.WIDTH_RESET_CLASS);
	    return res;
	  }

	  /**
	   * Increase size step-by-step, until text is small
	   */
	  increaseSize() {
	    let newSize = this.currentSize + this.currentSize * Entry.STEP_SIZE_PERCENTS / (100 - Entry.STEP_SIZE_PERCENTS);
	    newSize = Math.ceil(newSize);
	    newSize = Math.min(this.startSize, newSize);
	    if (this.currentSize !== newSize) {
	      if (!this.intervalId) {
	        this.intervalId = setInterval(() => {
	          if (this.isNeedIncrease()) {
	            this.increaseSize();
	          } else if (this.isNeedDecrease()) {
	            // one step correction if size big
	            this.decreaseSize();
	            this.finishAdjust();
	          } else {
	            this.finishAdjust();
	          }
	        });
	      }
	      this.setFontSize(newSize);
	    }
	  }

	  /**
	   * Stop increase or decrease processing
	   */
	  finishAdjust() {
	    clearInterval(this.intervalId);
	    this.intervalId = null;
	    this.calcCurrentWidth();
	  }

	  /**
	   * Set (if needed) new font size, calculate and set new letter spacing and line height
	   * @param size
	   */
	  setFontSize(size) {
	    if (size !== this.currentSize && size <= this.startSize) {
	      this.currentSize = size;
	      this.element.style.setProperty('font-size', this.currentSize + "px", "important");

	      // LINE HEIGHT correction
	      if (this.startLineHeight > Entry.MIN_LINE_HEIGHT) {
	        let newLineHeight = Entry.MIN_LINE_HEIGHT + (this.startLineHeight - Entry.MIN_LINE_HEIGHT) * size / this.startSize;
	        newLineHeight = newLineHeight.toFixed(1);
	        newLineHeight = Math.max(newLineHeight, Entry.MIN_LINE_HEIGHT);
	        if (newLineHeight <= this.startLineHeight && newLineHeight !== this.currentLineHeight) {
	          this.element.style.setProperty('line-height', newLineHeight, "important");
	          this.currentLineHeight = newLineHeight;
	        }
	      }

	      // LETTER SPACING correction
	      if (this.letterSpacingRatio > Entry.MIN_LETTER_SPACING) {
	        let newLetterSpacing = this.letterSpacingRatio * size;
	        newLetterSpacing = Math.round(newLetterSpacing);
	        if (newLetterSpacing !== this.currentLetterSpacing) {
	          this.element.style.setProperty('letter-spacing', newLetterSpacing + 'px', "important");
	          this.currentLetterSpacing = newLetterSpacing;
	        }
	      }
	    }
	  }
	}
	Entry.STEP_SIZE_PERCENTS = 10;
	Entry.MIN_SIZE = 12;
	Entry.MIN_LINE_HEIGHT = 1.4;
	Entry.MIN_LETTER_SPACING = 0;
	Entry.WIDTH_RESET_CLASS = 'scroll-width-reset';

	const bind = BX.Landing.Utils.bind;
	const slice = BX.Landing.Utils.slice;
	const onCustomEvent = BX.Landing.Utils.onCustomEvent;
	let lastWidth = BX.width(window);
	class AutoFontScale {
	  /**
	   * First init
	   * @param parentElement - find all elements in this parent
	   */
	  static init(parentElement) {
	    const elements = AutoFontScale.findElements(parentElement);
	    new AutoFontScale(elements);
	  }

	  /**
	   * Find elements by parent
	   * @param parentElement - find all elements in this parent
	   * @return {*}
	   */
	  static findElements(parentElement) {
	    const negativeString = AutoFontScale.NEGATIVE_SELECTORS.map(sel => ':not(' + sel + ')').join('');
	    const summarySelector = AutoFontScale.SELECTORS.map(sel => sel + negativeString).join(', ');
	    return slice(parentElement.querySelectorAll(summarySelector));
	  }

	  /**
	   * Checks than need adjust
	   * @return {boolean}
	   */
	  static isNeedAdjust() {
	    return BX.width(window) <= AutoFontScale.WIDTH_LIMIT;
	  }

	  /**
	   * Checks that window resize
	   * @return {boolean}
	   */
	  static isResized() {
	    const result = lastWidth !== BX.width(window);
	    lastWidth = BX.width(window);
	    return result;
	  }

	  /**
	   * Implements interface for works with responsive texts
	   * @param {HTMLElement[]} elements
	   */
	  constructor(elements) {
	    this.entries = elements.map(this.createEntry, this);
	    this.onResize = main_core.Runtime.debounce(this.onResize, 500);
	    bind(window, "resize", this.onResize.bind(this, false));
	    bind(window, "orientationchange", this.onResize.bind(this, true));
	    onCustomEvent("BX.Landing.Block:init", this.onAddBlock.bind(this));
	    this.adjust(true);
	  }
	  onResize(forceAdjust) {
	    this.adjust(forceAdjust);
	  }

	  /**
	   * Adjusts text
	   * @param {boolean} [forceAdjust]
	   */
	  adjust(forceAdjust) {
	    if (forceAdjust === true || AutoFontScale.isResized()) {
	      const needAdjust = AutoFontScale.isNeedAdjust();
	      this.entries.forEach(entry => {
	        if (needAdjust) {
	          entry.adjust();
	        } else {
	          entry.resetSize();
	        }
	      });
	    }
	  }

	  /**
	   * Creates entry
	   * @param {HTMLElement} element
	   * @return {Entry}
	   */
	  createEntry(element) {
	    return new Entry(element);
	  }

	  /**
	   * Adds elements
	   * @param {HTMLElement[]} elements
	   */
	  addElements(elements) {
	    elements.forEach(element => {
	      const containsElement = this.entries.some(entry => {
	        return entry.element === element;
	      });
	      if (!containsElement) {
	        this.entries.push(this.createEntry(element));
	      }
	    }, this);
	  }

	  /**
	   * Handles add block event
	   * @param {BX.Landing.Event.Block} event
	   */
	  onAddBlock(event) {
	    const elements = AutoFontScale.findElements(event.block);
	    this.addElements(elements);
	  }
	}
	AutoFontScale.WIDTH_LIMIT = 768;
	AutoFontScale.SELECTORS = ['h1', 'h2', 'h3', 'h4', 'h5', '[data-auto-font-scale]'];
	AutoFontScale.NEGATIVE_SELECTORS = ['[class*=product-]'];

	exports.AutoFontScale = AutoFontScale;

}((this.BX.Landing.UI.Tool = this.BX.Landing.UI.Tool || {}),BX));
//# sourceMappingURL=auto_font_scale.bundle.js.map
