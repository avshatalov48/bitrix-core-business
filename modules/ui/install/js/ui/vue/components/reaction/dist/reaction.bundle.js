(function (exports,ui_fonts_opensans,ui_vue,main_core_events) {
	'use strict';

	/**
	 * Bitrix UI
	 * Reaction picker Vue component
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	var ReactionType = Object.freeze({
	  none: 'none',
	  like: 'like',
	  kiss: 'kiss',
	  laugh: 'laugh',
	  wonder: 'wonder',
	  cry: 'cry',
	  angry: 'angry'
	});
	var ReactionOrder = ['like', 'kiss', 'laugh', 'wonder', 'cry', 'angry'];
	ui_vue.BitrixVue.component('bx-reaction', {
	  /**
	   * @emits 'set' {values: object}
	   * @emits 'list' {action: string, type: string}
	   */
	  props: {
	    id: {
	      "default": ''
	    },
	    values: {
	      "default": {}
	    },
	    userId: {
	      "default": 0
	    },
	    openList: {
	      "default": true
	    }
	  },
	  data: function data() {
	    return {
	      localValues: {},
	      userReaction: ReactionType.none,
	      buttonAnimate: false
	    };
	  },
	  created: function created() {
	    this.localValues = Object.assign({}, this.values);
	    main_core_events.EventEmitter.subscribe('ui:reaction:press', this.onPress);
	  },
	  destroy: function destroy() {
	    main_core_events.EventEmitter.unsubscribe('ui:reaction:press', this.onPress);
	  },
	  watch: {
	    values: function values(_values) {
	      this.localValues = Object.assign({}, _values);
	    }
	  },
	  methods: {
	    list: function list() {
	      if (this.openList) ;

	      this.$emit('list', {
	        values: this.localValues
	      });
	    },
	    press: function press() {
	      var _this = this;

	      var emotion = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : ReactionType.like;

	      if (this.userReaction === ReactionType.none) {
	        if (!this.localValues[emotion]) {
	          this.localValues = Object.assign({}, this.localValues, babelHelpers.defineProperty({}, emotion, []));
	        }

	        this.localValues[emotion].push(this.userId);
	        this.buttonAnimate = true;
	        setTimeout(function () {
	          return _this.buttonAnimate = false;
	        }, 400);
	        this.$emit('set', {
	          action: 'set',
	          type: emotion
	        });
	      } else {
	        if (this.localValues[this.userReaction]) {
	          this.localValues[this.userReaction] = this.localValues[this.userReaction].filter(function (element) {
	            return element !== _this.userId;
	          });
	        }

	        this.$emit('set', {
	          action: 'remove',
	          type: this.userReaction
	        });
	      }
	    },
	    onPress: function onPress(event) {
	      var data = event.getData();

	      if (!this.id || data.id !== this.id) {
	        return false;
	      }

	      if (!data.emotion) {
	        data.emotion = ReactionType.like;
	      }

	      this.press(data.emotion);
	    }
	  },
	  computed: {
	    types: function types() {
	      var _this2 = this;

	      this.userReaction = ReactionType.none;
	      return ReactionOrder.filter(function (type) {
	        if (typeof _this2.localValues[type] === 'undefined' || !(_this2.localValues[type] instanceof Array) || _this2.localValues[type].length <= 0) {
	          return false;
	        }

	        if (_this2.userId > 0 && _this2.userReaction === ReactionType.none && _this2.localValues[type].includes(_this2.userId)) {
	          _this2.userReaction = type;
	        }

	        return true;
	      }).map(function (type) {
	        return {
	          type: type,
	          count: _this2.localValues[type].length
	        };
	      });
	    },
	    counter: function counter() {
	      return this.types.map(function (element) {
	        return element.count;
	      }).reduce(function (result, value) {
	        return result + value;
	      }, 0);
	    },
	    isTypesShowed: function isTypesShowed() {
	      if (this.counter <= 0) {
	        return false;
	      }

	      if (this.userReaction !== ReactionType.none && this.counter === 1) {
	        return false;
	      }

	      return true;
	    },
	    isMobile: function isMobile() {
	      var UA = navigator.userAgent.toLowerCase();
	      return UA.includes('android') || UA.includes('iphone') || UA.includes('ipad') || UA.includes('bitrixmobile');
	    }
	  },
	  template: "\n\t\t<div :class=\"['ui-vue-reaction', {'ui-vue-reaction-mobile': isMobile}]\">\n\t\t\t<transition name=\"ui-vue-reaction-result-animation\">\n\t\t\t\t<div v-if=\"isTypesShowed\" class=\"ui-vue-reaction-result\" @click=\"list\">\n\t\t\t\t\t<transition-group tag=\"div\" class=\"ui-vue-reaction-result-types\" name=\"ui-vue-reaction-result-type-animation\" >\n\t\t\t\t\t\t<span v-for=\"element in types\" :class=\"['ui-vue-reaction-result-type', 'ui-vue-reaction-icon-'+element.type]\" :key=\"element.type\"></span>\n\t\t\t\t\t</transition-group>\t\n\t\t\t\t\t<div class=\"ui-vue-reaction-result-counter\">{{counter}}</div>\n\t\t\t\t</div>\n\t\t\t</transition>\n\t\t\t<div v-if=\"userId > 0\"  class=\"ui-vue-reaction-button\" @click.prevent=\"press()\">\n\t\t\t\t<div class=\"ui-vue-reaction-button-container\">\n\t\t\t\t\t<div :class=\"['ui-vue-reaction-button-icon', 'ui-vue-reaction-icon-'+userReaction, {'ui-vue-reaction-button-pressed': buttonAnimate}]\"></div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX,BX,BX.Event));
//# sourceMappingURL=reaction.bundle.js.map
