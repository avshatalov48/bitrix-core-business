/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Vue3 = this.BX.UI.Vue3 || {};
(function (exports,main_core,ui_switcher) {
	'use strict';

	/*
	Example:

	<Switcher
		:is-checked="myBoolData"
		@check="myBoolData = true"
		@uncheck="myBoolData = false"
		:options="{
			size: 'extra-small',
			color: 'green',
		}"
	/>
	 */

	const Switcher = {
	  name: 'Switcher',
	  emits: ['check', 'uncheck'],
	  props: {
	    isChecked: {
	      type: Boolean,
	      required: true
	    },
	    options: {
	      /** @type SwitcherOptions */
	      type: Object,
	      default: {}
	    }
	  },
	  switcher: null,
	  mounted() {
	    this.renderSwitcher();
	  },
	  watch: {
	    isChecked() {
	      this.switcher.check(this.isChecked, false);
	    },
	    options(newOptions, oldOptions) {
	      if (this.isOptionsEqual(newOptions, oldOptions)) {
	        return;
	      }

	      // re-render switcher since options has changed
	      this.switcher = null;
	      main_core.Dom.clean(this.$refs.container);
	      this.renderSwitcher();
	    }
	  },
	  methods: {
	    renderSwitcher() {
	      this.switcher = new ui_switcher.Switcher({
	        ...this.options,
	        checked: this.isChecked,
	        handlers: {
	          // checked for when the switcher is made off and unchecked for when the switcher is made on
	          // it looks like a bug, but I'm not sure
	          checked: () => {
	            // switch it back until the state is muted and we reactively change it to a new state
	            this.switcher.check(this.isChecked, false);
	            this.$emit('uncheck');
	          },
	          unchecked: () => {
	            // switch it back until the state is muted and we reactively change it to a new state
	            this.switcher.check(this.isChecked, false);
	            this.$emit('check');
	          }
	        }
	      });
	      this.switcher.renderTo(this.$refs.container);
	    },
	    isOptionsEqual(newOptions, oldOptions) {
	      if (Object.keys(newOptions).length !== Object.keys(oldOptions).length) {
	        return false;
	      }
	      for (const [key, value] of Object.entries(newOptions)) {
	        if (!Object.hasOwn(oldOptions, key)) {
	          return false;
	        }
	        if (value !== oldOptions[key]) {
	          return false;
	        }
	      }
	      for (const [key, value] of Object.entries(oldOptions)) {
	        if (!Object.hasOwn(newOptions, key)) {
	          return false;
	        }
	        if (value !== newOptions[key]) {
	          return false;
	        }
	      }
	      return true;
	    }
	  },
	  template: '<a ref="container"></a>'
	};

	exports.Switcher = Switcher;

}((this.BX.UI.Vue3.Components = this.BX.UI.Vue3.Components || {}),BX,BX.UI));
//# sourceMappingURL=switcher.bundle.js.map
