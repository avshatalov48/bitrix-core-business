/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.System = this.BX.UI.System || {};
this.BX.UI.System.Skeleton = this.BX.UI.System.Skeleton || {};
(function (exports,ui_system_skeleton) {
	'use strict';

	// @vue/component
	const BLine = {
	  props: {
	    width: {
	      type: Number,
	      default: undefined
	    },
	    height: {
	      type: Number,
	      default: undefined
	    },
	    radius: {
	      type: Number,
	      default: undefined
	    }
	  },
	  data() {
	    return {
	      isMounted: false
	    };
	  },
	  mounted() {
	    this.$refs.line.after(this.render());
	    this.isMounted = true;
	  },
	  updated() {
	    this.render();
	  },
	  methods: {
	    render() {
	      var _this$line;
	      const line = ui_system_skeleton.Line(this.width, this.height, this.radius);
	      (_this$line = this.line) == null ? void 0 : _this$line.replaceWith(line);
	      this.line = line;
	      return line;
	    }
	  },
	  template: `
		<div v-if="!isMounted" ref="line"></div>
	`
	};

	// @vue/component
	const BCircle = {
	  components: {
	    BLine
	  },
	  props: {
	    size: {
	      type: Number,
	      default: 18
	    }
	  },
	  template: `
		<BLine :width="size" :height="size" :radius="99"/>
	`
	};

	exports.BLine = BLine;
	exports.BCircle = BCircle;
	exports.Circle = BCircle;

}((this.BX.UI.System.Skeleton.Vue = this.BX.UI.System.Skeleton.Vue || {}),BX.UI.System));
//# sourceMappingURL=vue.bundle.js.map
