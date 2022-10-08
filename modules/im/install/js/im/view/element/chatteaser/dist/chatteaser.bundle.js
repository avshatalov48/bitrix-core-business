(function (exports,ui_designTokens,ui_vue,im_lib_utils) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * ChatTeaser element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.BitrixVue.component('bx-im-view-element-chat-teaser', {
	  /*
	   * @emits 'click' {}
	   */
	  props: {
	    messageCounter: {
	      "default": 0
	    },
	    messageLastDate: {
	      "default": 0
	    },
	    languageId: {
	      "default": 'en'
	    }
	  },
	  computed: {
	    formattedDate: function formattedDate() {
	      return im_lib_utils.Utils.date.format(this.messageLastDate, null, this.$Bitrix.Loc.getMessages());
	    },
	    formattedCounter: function formattedCounter() {
	      return this.messageCounter + ' ' + im_lib_utils.Utils.text.getLocalizeForNumber('IM_MESSENGER_COMMENT', this.messageCounter, this.languageId, this.$Bitrix.Loc.getMessages());
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-element-chat-teaser\" @click=\"$emit('click', $event)\">\n\t\t\t<span class=\"bx-im-element-chat-teaser-join\">{{$Bitrix.Loc.getMessage('IM_MESSENGER_COMMENT_OPEN')}}</span>\n\t\t\t<span class=\"bx-im-element-chat-teaser-comment\">\n\t\t\t\t<span class=\"bx-im-element-chat-teaser-counter\">{{formattedCounter}}</span>, {{formattedDate}}\n\t\t\t</span>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX,BX,BX.Messenger.Lib));
//# sourceMappingURL=chatteaser.bundle.js.map
