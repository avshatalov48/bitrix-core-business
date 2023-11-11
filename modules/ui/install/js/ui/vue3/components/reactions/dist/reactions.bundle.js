/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vue3 = this.BX.Vue3 || {};
(function (exports,ui_fonts_opensans,ui_vue3,main_core,main_core_events,ui_reactionsSelect,ui_lottie) {
	'use strict';

	/**
	 * Bitrix UI
	 * Reaction picker Vue component
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	const ReactionTypeNone = 'none';
	const ReactionOrder = Object.keys(ui_reactionsSelect.reactionType);
	const ReactionIconClass = {
	  ...ui_reactionsSelect.reactionCssClass,
	  none: 'ui-vue-reactions-icon-none'
	};
	const Reactions = ui_vue3.BitrixVue.mutableComponent('bx-reactions', {
	  props: {
	    name: {
	      default: ''
	    },
	    values: {
	      default: {}
	    },
	    currentUserId: {
	      default: 0
	    },
	    canOpenList: {
	      default: true
	    }
	  },
	  data: () => ({
	    localValues: {},
	    userReaction: ReactionTypeNone,
	    buttonAnimate: false
	  }),
	  emits: ['list', 'set'],
	  mounted() {
	    this.userReactionAnimation = null;
	    this.selectorPopup = null;
	    this.selectorPopupHideTimeout = null;
	    this.selectorPopupShowTimeout = null;
	    this.localValues = {
	      ...this.values
	    };
	    if (main_core.Type.isStringFilled(this.name)) {
	      this.$Bitrix.eventEmitter.subscribe(`ui:reaction:press:${this.name}`, this.onPress);
	    }
	  },
	  beforeUnmount() {
	    if (this.selectorPopup) {
	      this.selectorPopup.hide();
	      this.selectorPopup = null;
	    }
	    if (this.userReactionAnimation) {
	      this.userReactionAnimation.destroy();
	    }
	    clearTimeout(this.selectorPopupHideTimeout);
	    this.selectorPopupHideTimeout = null;
	    clearTimeout(this.selectorPopupShowTimeout);
	    this.selectorPopupShowTimeout = null;
	    if (main_core.Type.isStringFilled(this.name)) {
	      this.$Bitrix.eventEmitter.unsubscribe(`ui:reaction:press:${this.name}`, this.onPress);
	    }
	  },
	  watch: {
	    values: {
	      handler(values) {
	        this.localValues = {
	          ...values
	        };
	      },
	      deep: true
	    }
	  },
	  methods: {
	    list() {
	      if (!this.canOpenList) {
	        return false;
	      }
	      this.$emit('list', {
	        values: this.localValues
	      });
	      return true;
	    },
	    set(reaction) {
	      if (!ReactionOrder.includes(reaction)) {
	        return false;
	      }
	      if (this.localValues[this.userReaction]) {
	        this.localValues[this.userReaction] = this.localValues[this.userReaction].filter(element => element !== this.currentUserId);
	      }
	      if (!this.localValues[reaction]) {
	        this.localValues = {
	          ...this.localValues,
	          [reaction]: []
	        };
	      }
	      this.localValues[reaction].push(this.currentUserId);
	      this.buttonAnimate = true;
	      setTimeout(() => {
	        this.buttonAnimate = false;
	      }, 400);
	      this.$emit('set', {
	        action: 'set',
	        type: reaction
	      });
	      this.animateReactionButton(reaction);
	      this.hideEmotionSelector(0);
	      return true;
	    },
	    unset() {
	      if (this.userReaction === ReactionTypeNone) {
	        return true;
	      }
	      if (this.localValues[this.userReaction]) {
	        this.localValues[this.userReaction] = this.localValues[this.userReaction].filter(element => element !== this.currentUserId);
	      }
	      this.$emit('set', {
	        action: 'remove',
	        type: this.userReaction
	      });
	      this.animateReactionButton(ReactionTypeNone);
	      this.hideEmotionSelector(0);
	      return true;
	    },
	    press(reaction = ui_reactionsSelect.reactionType.like) {
	      if (this.userReaction === ReactionTypeNone) {
	        return this.set(reaction);
	      }
	      this.unset();
	    },
	    showEmotionSelector(event) {
	      var _this$name;
	      if (this.selectorPopup) {
	        clearTimeout(this.selectorPopupHideTimeout);
	        return false;
	      }
	      const popupName = (_this$name = this.name) != null ? _this$name : Date.now();
	      this.selectorPopup = new ui_reactionsSelect.ReactionsSelect({
	        name: popupName,
	        position: event.target
	      }).subscribe('select', selectEvent => {
	        var _this$selectorPopup;
	        const {
	          reaction
	        } = selectEvent.getData();
	        this.set(reaction);
	        (_this$selectorPopup = this.selectorPopup) == null ? void 0 : _this$selectorPopup.hide();
	      }).subscribe('mouseleave', () => {
	        this.hideEmotionSelector(500);
	      }).subscribe('mouseenter', () => {
	        clearTimeout(this.selectorPopupHideTimeout);
	      }).subscribe('hide', () => {
	        clearTimeout(this.selectorPopupHideTimeout);
	        this.selectorPopup = null;
	      });
	      clearTimeout(this.selectorPopupShowTimeout);
	      this.selectorPopupShowTimeout = setTimeout(() => {
	        var _this$selectorPopup2;
	        return (_this$selectorPopup2 = this.selectorPopup) == null ? void 0 : _this$selectorPopup2.show();
	      }, 1000);
	    },
	    hideEmotionSelector(timeout = 1000) {
	      clearTimeout(this.selectorPopupShowTimeout);
	      clearTimeout(this.selectorPopupHideTimeout);
	      if (!timeout) {
	        var _this$selectorPopup3;
	        (_this$selectorPopup3 = this.selectorPopup) == null ? void 0 : _this$selectorPopup3.hide();
	        return true;
	      }
	      this.selectorPopupHideTimeout = setTimeout(() => {
	        var _this$selectorPopup4;
	        (_this$selectorPopup4 = this.selectorPopup) == null ? void 0 : _this$selectorPopup4.hide();
	      }, timeout);
	    },
	    onPress(event) {
	      const data = event.getData();
	      if (!data.reaction) {
	        data.reaction = ui_reactionsSelect.reactionType.like;
	      }
	      this.press(data.reaction);
	    },
	    animateReactionButton(reaction) {
	      if (this.currentUserId <= 0) {
	        return true;
	      }
	      if (this.userReactionAnimation) {
	        this.userReactionAnimation.destroy();
	      }
	      if (reaction === ReactionTypeNone) {
	        return true;
	      }
	      this.userReactionAnimation = ui_lottie.Lottie.loadAnimation({
	        animationData: ui_reactionsSelect.ReactionsSelect.getLottieAnimation(reaction),
	        container: this.$refs['reactions-button-icon'],
	        loop: false,
	        autoplay: false,
	        renderer: 'svg',
	        rendererSettings: {
	          viewBoxOnly: true
	        }
	      });
	      this.userReactionAnimation.addEventListener('complete', () => {
	        this.userReactionAnimation.destroy();
	      });
	      this.userReactionAnimation.addEventListener('destroy', () => {
	        this.userReactionAnimation = null;
	      });
	      this.userReactionAnimation.play();
	      return true;
	    }
	  },
	  computed: {
	    types() {
	      this.userReaction = ReactionTypeNone;
	      return ReactionOrder.filter(type => {
	        if (!main_core.Type.isArray(this.localValues[type]) || this.localValues[type].length <= 0) {
	          return false;
	        }
	        if (this.currentUserId > 0 && this.userReaction === ReactionTypeNone && this.localValues[type].includes(this.currentUserId)) {
	          this.userReaction = type;
	        }
	        return true;
	      }).map(type => {
	        return {
	          type,
	          count: this.localValues[type].length
	        };
	      });
	    },
	    counter() {
	      return this.types.map(element => element.count).reduce((result, value) => result + value, 0);
	    },
	    isTypesShowed() {
	      if (this.counter <= 0) {
	        return false;
	      }
	      return !(this.userReaction !== ReactionTypeNone && this.counter === 1);
	    },
	    isMobile() {
	      const UA = navigator.userAgent.toLowerCase();
	      return UA.includes('android') || UA.includes('iphone') || UA.includes('ipad') || UA.includes('bitrixmobile');
	    },
	    ReactionIconClass: () => ReactionIconClass
	  },
	  template: `
		<div :class="['ui-vue-reactions', {'ui-vue-reactions-mobile': isMobile}]">
			<transition name="ui-vue-reactions-result-animation">
				<div v-if="isTypesShowed" :class="['ui-vue-reactions-result', {'ui-vue-reactions-result-active': canOpenList}]" @click="list">
					<transition-group tag="div" class="ui-vue-reactions-result-types" name="ui-vue-reactions-result-type-animation" >
						<span v-for="element in types" :class="['ui-vue-reactions-result-type', ReactionIconClass[element.type]]" :key="element.type"></span>
					</transition-group>	
					<div class="ui-vue-reactions-result-counter">{{counter}}</div>
				</div>
			</transition>
			<div v-if="currentUserId > 0"  class="ui-vue-reactions-button" @click.prevent="press()" @mouseenter="showEmotionSelector" @mouseleave="hideEmotionSelector()">
				<div class="ui-vue-reactions-button-container">
					<div :class="['ui-vue-reactions-button-icon', ReactionIconClass[userReaction], {'ui-vue-reactions-button-pressed': buttonAnimate}]" ref="reactions-button-icon"></div>
				</div>
			</div>
		</div>
	`
	});

	exports.Reactions = Reactions;

}((this.BX.Vue3.Components = this.BX.Vue3.Components || {}),BX,BX.Vue3,BX,BX.Event,BX.Ui,BX.UI));
//# sourceMappingURL=reactions.bundle.js.map
