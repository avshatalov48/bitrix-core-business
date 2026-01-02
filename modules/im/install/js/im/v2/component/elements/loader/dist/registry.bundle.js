/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_loader) {
	'use strict';

	const LOADER_SIZE = 'xs';
	const LOADER_TYPE = 'BULLET';

	// @vue/component
	const Loader = {
	  name: 'MessengerLoader',
	  mounted() {
	    this.loader = new ui_loader.Loader({
	      target: this.$refs['messenger-loader'],
	      type: LOADER_TYPE,
	      size: LOADER_SIZE
	    });
	    this.loader.render();
	    this.loader.show();
	  },
	  beforeUnmount() {
	    this.loader.hide();
	    this.loader = null;
	  },
	  template: `
		<div class="bx-im-elements-loader__container" ref="messenger-loader"></div>
	`
	};

	const SpinnerSize = Object.freeze({
	  XXS: 'XXS',
	  XS: 'XS',
	  S: 'S',
	  L: 'L'
	});
	const SpinnerColor = Object.freeze({
	  grey: 'grey',
	  blue: 'blue',
	  copilot: 'copilot'
	});

	// @vue/component
	const Spinner = {
	  name: 'MessengerSpinner',
	  props: {
	    size: {
	      type: String,
	      default: SpinnerSize.S
	    },
	    color: {
	      type: String,
	      default: SpinnerColor.blue
	    }
	  },
	  computed: {
	    sizeClassName() {
	      return `--size-${this.size.toLowerCase()}`;
	    },
	    colorClassName() {
	      return `--color-${this.color.toLowerCase()}`;
	    }
	  },
	  template: `
		<div class="bx-im-elements-spinner__container bx-im-elements-spinner__scope">
			<div class="bx-im-elements-spinner__spinner" :class="[sizeClassName, colorClassName]"></div>
		</div>
	`
	};

	// @vue/component
	const LineLoader = {
	  name: 'LineLoader',
	  props: {
	    width: {
	      type: Number,
	      required: true
	    },
	    height: {
	      type: Number,
	      required: true
	    }
	  },
	  computed: {
	    containerStyles() {
	      return {
	        width: `${this.width}px`,
	        height: `${this.height}px`
	      };
	    }
	  },
	  template: `
		<div class="bx-im-elements-line-loader__container" :style="containerStyles">
			<div class="bx-im-elements-line-loader__content"></div>
		</div>
	`
	};

	exports.Loader = Loader;
	exports.Spinner = Spinner;
	exports.SpinnerSize = SpinnerSize;
	exports.SpinnerColor = SpinnerColor;
	exports.LineLoader = LineLoader;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX.UI));
//# sourceMappingURL=registry.bundle.js.map
