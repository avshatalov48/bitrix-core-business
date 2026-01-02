/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports) {
	'use strict';

	const ToggleSize = {
	  S: 'S',
	  M: 'M'
	};

	// @vue/component
	const Toggle = {
	  name: 'ToggleControl',
	  props: {
	    size: {
	      type: String,
	      required: true
	    },
	    isEnabled: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['change'],
	  computed: {
	    containerClasses() {
	      const classes = [`--size-${this.size.toLowerCase()}`];
	      if (!this.isEnabled) {
	        classes.push('--off');
	      }
	      return classes;
	    }
	  },
	  template: `
		<div :class="containerClasses" class="bx-im-toggle__container bx-im-toggle__scope">
			<span class="bx-im-toggle__cursor"></span>
			<span class="bx-im-toggle__enabled"></span>
			<span class="bx-im-toggle__disabled"></span>
		</div>
	`
	};

	exports.ToggleSize = ToggleSize;
	exports.Toggle = Toggle;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {})));
//# sourceMappingURL=toggle.bundle.js.map
