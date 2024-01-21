/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,ui_designTokens,ui_forms,main_core_events) {
	'use strict';

	class LayoutForm extends main_core_events.EventEmitter {
	  constructor(params) {
	    var _this$params$containe;
	    super();
	    this.setEventNamespace('BX.UI.LayoutForm');
	    this.params = params != null ? params : {};
	    this.container = (_this$params$containe = this.params.container) != null ? _this$params$containe : document.documentElement;
	    this.nodes = null;
	    this.init();
	  }
	  init() {
	    this.nodes = [].slice.call(this.container.querySelectorAll('[' + LayoutForm.HIDDEN_ATTRIBUTE + ']'));
	    this.nodes.forEach(node => {
	      main_core.Event.bind(node, "click", event => {
	        event.preventDefault();
	        this.toggleBLock(node);
	        this.emit('onToggle', {
	          checkbox: node.querySelector(LayoutForm.CHECKBOX_SELECTOR)
	        });
	      });
	      node.querySelector(LayoutForm.CHECKBOX_SELECTOR).style.pointerEvents = 'none';
	      this.checkInitialBlockVisibility(node);
	    });
	  }
	  checkInitialBlockVisibility(node) {
	    const checkbox = node.querySelector(LayoutForm.CHECKBOX_SELECTOR);
	    if (checkbox && checkbox.checked) {
	      const content = node.nextElementSibling;
	      if (content) {
	        content.style.height = 'auto';
	        main_core.Dom.addClass(content, LayoutForm.SHOW_CLASS);
	      }
	    }
	  }
	  toggleBLock(node) {
	    const checkbox = node.querySelector(LayoutForm.CHECKBOX_SELECTOR);
	    if (checkbox) {
	      const content = node.nextElementSibling;
	      if (content) {
	        const height = content.scrollHeight;
	        if (height > 0) {
	          if (!checkbox.checked) {
	            checkbox.checked = true;
	            content.style.height = height + 'px';
	            main_core.Dom.addClass(content, LayoutForm.SHOW_CLASS);
	            const onTransitionEnd = () => {
	              content.style.height = 'auto';
	              main_core.Event.unbind(content, 'transitionend', onTransitionEnd);
	            };
	            main_core.Event.bind(content, 'transitionend', onTransitionEnd);
	          } else {
	            checkbox.checked = false;
	            content.style.height = height + 'px';
	            requestAnimationFrame(() => {
	              content.style.height = 0;
	              main_core.Dom.removeClass(content, LayoutForm.SHOW_CLASS);
	            });
	          }
	        }
	      }
	    }
	  }
	}
	LayoutForm.HIDDEN_ATTRIBUTE = 'data-form-row-hidden';
	LayoutForm.SHOW_CLASS = 'ui-form-row-hidden-show';
	LayoutForm.CHECKBOX_SELECTOR = '.ui-ctl-element[type="checkbox"]';

	exports.LayoutForm = LayoutForm;

}((this.BX.UI = this.BX.UI || {}),BX,BX,BX,BX.Event));
//# sourceMappingURL=layout-form.bundle.js.map
