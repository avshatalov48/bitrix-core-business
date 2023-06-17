this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core) {
	'use strict';

	// @vue/component
	const ExpandAnimation = {
	  props: {
	    duration: {
	      type: Number,
	      default: 300
	    }
	  },
	  methods: {
	    onBeforeEnter(element) {
	      main_core.Dom.style(element, 'overflow', 'hidden');
	      main_core.Dom.style(element, 'transition', `height ${this.duration}ms, opacity ${this.duration}ms`);
	    },
	    onBeforeLeave(element) {
	      this.onBeforeEnter(element);
	    },
	    onEnter(element) {
	      main_core.Dom.style(element, 'height', 0);
	      main_core.Dom.style(element, 'opacity', 0);
	      requestAnimationFrame(() => {
	        requestAnimationFrame(() => {
	          main_core.Dom.style(element, 'opacity', 1);
	          main_core.Dom.style(element, 'height', `${element.scrollHeight}px`);
	        });
	      });
	    },
	    onAfterEnter(element) {
	      main_core.Dom.style(element, 'height', 'auto');
	    },
	    onLeave(element) {
	      main_core.Dom.style(element, 'height', `${element.scrollHeight}px`);
	      requestAnimationFrame(() => {
	        main_core.Dom.style(element, 'height', 0);
	        main_core.Dom.style(element, 'opacity', 0);
	      });
	    }
	  },
	  template: `
		<Transition
			@before-enter="onBeforeEnter"
			@enter="onEnter"
			@after-enter="onAfterEnter"
			@before-leave="onBeforeLeave"
			@leave="onLeave"
		>
			<slot></slot>
		</Transition>
	`
	};

	exports.ExpandAnimation = ExpandAnimation;

}((this.BX.Messenger.v2.Component.Animation = this.BX.Messenger.v2.Component.Animation || {}),BX));
//# sourceMappingURL=registry.bundle.js.map
