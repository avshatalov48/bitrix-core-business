/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Vue3 = this.BX.UI.Vue3 || {};
(function (exports,ui_avatar,ui_vue3) {
	'use strict';

	const AvatarConcreteClassByType = Object.freeze({
	  round: ui_avatar.AvatarRound,
	  'round-guest': ui_avatar.AvatarRoundGuest,
	  'round-extranet': ui_avatar.AvatarRoundExtranet,
	  'round-accent': ui_avatar.AvatarRoundAccent,
	  hexagon: ui_avatar.AvatarHexagon,
	  'hexagon-guest': ui_avatar.AvatarHexagonGuest,
	  'hexagon-extranet': ui_avatar.AvatarHexagonExtranet,
	  'hexagon-accent': ui_avatar.AvatarHexagonAccent,
	  square: ui_avatar.AvatarSquare,
	  'square-guest': ui_avatar.AvatarSquareGuest,
	  'square-extranet': ui_avatar.AvatarSquareExtranet,
	  'square-accent': ui_avatar.AvatarSquareAccent
	});

	// @vue/component
	const Avatar = {
	  name: 'UiAvatar',
	  props: {
	    options: {
	      /** @type AvatarOptions */
	      type: Object,
	      required: false,
	      default: () => ({})
	    },
	    type: {
	      /** @type AvatarType */
	      type: String,
	      required: false,
	      default: 'round',
	      validator(val) {
	        return Object.keys(AvatarConcreteClassByType).includes(val);
	      }
	    }
	  },
	  data() {
	    return {
	      size: null
	    };
	  },
	  created() {
	    var _this$options$size;
	    const ConcreteClass = AvatarConcreteClassByType[this.type];

	    /** @type AvatarBase */
	    this.instance = new ConcreteClass(this.options);
	    this.size = (_this$options$size = this.options.size) != null ? _this$options$size : null;
	  },
	  mounted() {
	    this.instance.renderTo(this.$refs.container);
	  },
	  template: `
		<div ref="container" :style="{ 'width': size + 'px', 'height': size + 'px'}"></div>
	`
	};

	exports.Avatar = Avatar;

}((this.BX.UI.Vue3.Components = this.BX.UI.Vue3.Components || {}),BX.UI,BX.Vue3));
//# sourceMappingURL=avatar.bundle.js.map
