/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports) {
	'use strict';

	// @vue/component
	const ScrollWithGradient = {
	  name: 'ScrollWithGradient',
	  props: {
	    containerMaxHeight: {
	      type: Number,
	      default: 0,
	      required: false
	    },
	    gradientHeight: {
	      type: Number,
	      default: 0
	    },
	    withShadow: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {
	      showTopGradient: false,
	      showBottomGradient: false
	    };
	  },
	  computed: {
	    contentHeightStyle() {
	      if (!this.containerMaxHeight) {
	        return {
	          height: '100%'
	        };
	      }
	      return {
	        maxHeight: `${this.containerMaxHeight}px`
	      };
	    },
	    gradientHeightStyle() {
	      return {
	        maxHeight: `${this.gradientHeightStyle}px`
	      };
	    }
	  },
	  mounted() {
	    // const container = this.$refs['scroll-container'];
	    // this.showBottomGradient = container.scrollHeight > container.clientHeight;
	  },
	  methods: {
	    onScroll(event) {
	      this.$emit('scroll', event);
	      const scrollPosition = Math.floor(event.target.scrollTop + event.target.clientHeight);
	      this.showBottomGradient = scrollPosition !== event.target.scrollHeight;
	      if (event.target.scrollTop === 0) {
	        this.showTopGradient = false;
	        return;
	      }
	      this.showTopGradient = true;
	    }
	  },
	  template: `
		<div class="bx-im-scroll-with-gradient__container">
			<Transition name="gradient-fade">
				<div v-if="showTopGradient" class="bx-im-scroll-with-gradient__gradient --top" :style="gradientHeightStyle">
					<div v-if="withShadow" class="bx-im-scroll-with-gradient__gradient-inner"></div>
				</div>
			</Transition>
			<div 
				class="bx-im-scroll-with-gradient__content" 
				:style="contentHeightStyle" 
				@scroll="onScroll"
				ref="scroll-container"
			>
				<slot></slot>
			</div>
			<Transition name="gradient-fade">
				<div v-if="showBottomGradient" class="bx-im-scroll-with-gradient__gradient --bottom" :style="gradientHeightStyle">
					<div v-if="withShadow" class="bx-im-scroll-with-gradient__gradient-inner"></div>
				</div>
			</Transition>
		</div>
	`
	};

	exports.ScrollWithGradient = ScrollWithGradient;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {})));
//# sourceMappingURL=scroll-with-gradient.bundle.js.map
