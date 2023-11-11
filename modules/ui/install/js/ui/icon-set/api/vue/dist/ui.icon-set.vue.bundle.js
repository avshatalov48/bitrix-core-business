/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,ui_iconSet_api_core) {
	'use strict';

	const BIcon = {
	  props: {
	    name: {
	      type: String,
	      required: true,
	      validator(value) {
	        return Object.values(ui_iconSet_api_core.Set).includes(value);
	      }
	    },
	    color: {
	      type: String
	    },
	    size: {
	      type: Number
	    }
	  },
	  computed: {
	    className() {
	      return ['ui-icon-set', `--${this.name}`];
	    },
	    inlineSize() {
	      return this.size ? '--ui-icon-set__icon-size: ' + this.size + 'px;' : '';
	    },
	    inlineColor() {
	      return this.color ? '--ui-icon-set__icon-color: ' + this.color + ';' : '';
	    },
	    inlineStyle() {
	      return this.inlineSize + this.inlineColor;
	    }
	  },
	  template: `<div
				:class="className"
				:style="inlineStyle"
				>
	</div>`
	};

	exports.Set = ui_iconSet_api_core.Set;
	exports.BIcon = BIcon;

}((this.BX.UI.IconSet = this.BX.UI.IconSet || {}),BX,BX.UI.IconSet));
//# sourceMappingURL=ui.icon-set.vue.bundle.js.map
