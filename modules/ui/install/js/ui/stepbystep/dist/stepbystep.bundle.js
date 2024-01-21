/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events,ui_hint,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	class StepByStepItem extends main_core_events.EventEmitter {
	  constructor(options = {}, number) {
	    super();
	    this.header = options == null ? void 0 : options.header;
	    this.node = options == null ? void 0 : options.node;
	    this.number = number;
	    this.isFirst = (options == null ? void 0 : options.isFirst) || '';
	    this.isLast = (options == null ? void 0 : options.isLast) || '';
	    this.class = main_core.Type.isString(options == null ? void 0 : options.nodeClass) ? options.nodeClass : null;
	    this.backgroundColor = main_core.Type.isString(options == null ? void 0 : options.backgroundColor) ? options.backgroundColor : null;
	    this.layout = {
	      container: null
	    };
	  }
	  getHeader() {
	    if (main_core.Type.isString(this.header)) {
	      return main_core.Tag.render(_t || (_t = _`
				<div class="ui-stepbystep__section-item--title">${0}</div>
			`), this.header);
	    }
	    if (main_core.Type.isObject(this.header)) {
	      let titleWrapper = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-stepbystep__section-item--title">

				</div>
			`));
	      if (this.header.title) {
	        titleWrapper.innerText = this.header.title;
	      }
	      if (main_core.Type.isString(this.header.hint)) {
	        let hintNode = main_core.Tag.render(_t3 || (_t3 = _`
					<span data-hint="${0}" class="ui-hint ui-stepbystep__section-item--hint">
						<i class="ui-hint-icon"></i>
					</span>
				`), this.header.hint);
	        titleWrapper.appendChild(hintNode);
	        this.initHint(titleWrapper);
	      }
	      return titleWrapper;
	    }
	    return '';
	  }
	  initHint(node) {
	    BX.UI.Hint.init(node);
	  }
	  getContent() {
	    if (this.node) {
	      return main_core.Tag.render(_t4 || (_t4 = _`
				<div class="ui-stepbystep__section-item--content">
					${0}
				</div>
			`), this.node);
	    }
	    return '';
	  }
	  getContainer() {
	    if (!this.layout.container) {
	      this.layout.container = main_core.Tag.render(_t5 || (_t5 = _`
				<div class="ui-stepbystep__section-item">
					<div class="ui-stepbystep__section-item--counter">
						<div class="ui-stepbystep__section-item--counter-number ${0} ${0}">
							<span>${0}</span>
						</div>
					</div>
					<div class="ui-stepbystep__section-item--information">
						${0}
						${0}
					</div>
				</div>
			`), this.isFirst, this.isLast, this.number, this.getHeader(), this.getContent());
	      if (this.backgroundColor) {
	        this.layout.container.style.backgroundColor = this.backgroundColor;
	      }
	      if (this.class) {
	        this.layout.container.classList.add(this.class);
	      }
	    }
	    return this.layout.container;
	  }
	}

	let _$1 = t => t,
	  _t$1;
	class StepByStep {
	  constructor(options = {}) {
	    this.target = options.target || null;
	    this.content = options.content || null;
	    this.contentWrapper = null;
	    this.items = [];
	    this.counter = 0;
	  }
	  getItem(item) {
	    if (item instanceof StepByStepItem) {
	      return item;
	    }
	    this.counter++;
	    if (this.counter === 1) {
	      item.isFirst = '--first';
	    }
	    if (this.counter === this.content.length) {
	      item.isLast = '--last';
	    }
	    item = new StepByStepItem(item, this.counter);
	    if (this.items.indexOf(item) === -1) {
	      this.items.push(item);
	    }
	    return item;
	  }
	  getContentWrapper() {
	    if (!this.contentWrapper) {
	      this.contentWrapper = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="ui-stepbystep__content ui-stepbystep__scope"></div>
			`));
	      this.content.map(item => {
	        item.html.map(itemObj => {
	          this.contentWrapper.appendChild(this.getItem(itemObj).getContainer());
	        });
	      });
	    }
	    return this.contentWrapper;
	  }
	  init() {
	    if (this.target && this.content) {
	      main_core.Dom.clean(this.target);
	      this.target.appendChild(this.getContentWrapper());
	    }
	  }
	}

	exports.StepByStep = StepByStep;

}((this.BX.UI = this.BX.UI || {}),BX.Event,BX,BX));
//# sourceMappingURL=stepbystep.bundle.js.map
