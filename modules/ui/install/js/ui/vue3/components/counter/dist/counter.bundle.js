/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Vue3 = this.BX.UI.Vue3 || {};
(function (exports,ui_cnt) {
	'use strict';

	// @vue/component
	const Counter = {
	  name: 'BCounter',
	  props: {
	    value: {
	      type: Number,
	      default: 0
	    },
	    maxValue: {
	      type: Number,
	      default: 99
	    },
	    style: {
	      type: String,
	      required: false,
	      default: ui_cnt.CounterStyle.FILLED_ALERT,
	      validator: value => {
	        return Object.values(ui_cnt.CounterStyle).includes(value);
	      }
	    },
	    size: {
	      type: String,
	      required: false,
	      default: ui_cnt.CounterSize.MEDIUM,
	      validator: value => {
	        return Object.values(ui_cnt.CounterSize).includes(value);
	      }
	    },
	    border: {
	      type: Boolean,
	      default: false
	    },
	    percent: {
	      type: Boolean,
	      default: false
	    }
	  },
	  watch: {
	    value(newValue) {
	      this.counter.update(newValue);
	    },
	    style(newStyle) {
	      this.counter.setStyle(newStyle);
	    },
	    size(newSize) {
	      this.counter.setSize(newSize);
	    },
	    maxValue(newMaxValue) {
	      this.counter.setMaxValue(newMaxValue);
	    },
	    border(useBorder) {
	      this.counter.setBorder(useBorder);
	    }
	  },
	  beforeMount() {
	    this.counter = new ui_cnt.Counter({
	      useAirDesign: true,
	      style: this.style,
	      size: this.size,
	      value: this.value,
	      maxValue: this.maxValue,
	      usePercentSymbol: this.percent,
	      border: this.border,
	      node: this.$refs.counter
	    });
	  },
	  mounted() {
	    this.counter.renderOnNode(this.$refs.counter);
	  },
	  unmounted() {
	    this.counter.destroy();
	    this.counter = null;
	  },
	  template: `
		<div ref="counter"></div>
	`
	};

	exports.Counter = Counter;

}((this.BX.UI.Vue3.Components = this.BX.UI.Vue3.Components || {}),BX.UI));
//# sourceMappingURL=counter.bundle.js.map
