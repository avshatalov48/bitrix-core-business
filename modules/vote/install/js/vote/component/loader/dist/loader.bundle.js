/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vote = this.BX.Vote || {};
(function (exports) {
	'use strict';

	const LoaderSize = Object.freeze({
	  S: 'S',
	  M: 'M'
	});

	// @vue/component
	const Loader = {
	  name: 'VoteLoader',
	  props: {
	    size: {
	      type: String,
	      default: LoaderSize.S
	    }
	  },
	  computed: {
	    sizeClassName() {
	      return `--size-${this.size.toLowerCase()}`;
	    }
	  },
	  template: `
		<div class="vote-loader__container">
			<div class="vote-loader" :class="sizeClassName"></div>
		</div>
	`
	};

	exports.LoaderSize = LoaderSize;
	exports.Loader = Loader;

}((this.BX.Vote.Component = this.BX.Vote.Component || {})));
//# sourceMappingURL=loader.bundle.js.map
