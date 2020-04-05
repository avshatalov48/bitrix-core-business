(function (exports,ui_vue,im_utils) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * ChatTeaser element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.component('bx-messenger-element-chat-teaser', {
	  /*
	   * @emits 'click' {}
	   */
	  props: {
	    messageCounter: {
	      default: 0
	    },
	    messageLastDate: {
	      default: 0
	    },
	    languageId: {
	      default: 'en'
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('IM_MESSENGER_COMMENT_', this.$root.$bitrixMessages);
	    },
	    formattedDate: function formattedDate() {
	      return im_utils.Utils.date.format(this.messageLastDate, null, this.$root.$bitrixMessages);
	    },
	    formattedCounter: function formattedCounter() {
	      return this.messageCounter + ' ' + im_utils.Utils.text.getLocalizeForNumber('IM_MESSENGER_COMMENT', this.messageCounter, this.languageId, this.$root.$bitrixMessages);
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-element-chat-teaser\" @click=\"$emit('click', $event)\">\n\t\t\t<span class=\"bx-im-element-chat-teaser-join\">{{localize.IM_MESSENGER_COMMENT_OPEN}}</span>\n\t\t\t<span class=\"bx-im-element-chat-teaser-comment\">\n\t\t\t\t<span class=\"bx-im-element-chat-teaser-counter\">{{formattedCounter}}</span>, {{formattedDate}}\n\t\t\t</span>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX,BX.Messenger));
//# sourceMappingURL=chatteaser.bundle.js.map
