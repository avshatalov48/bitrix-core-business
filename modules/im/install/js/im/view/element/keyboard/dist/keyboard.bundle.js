(function (exports,ui_designTokens,ui_vue,im_lib_utils,im_lib_logger) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Attach element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var _ButtonType = Object.freeze({
	  newline: 'NEWLINE',
	  button: 'BUTTON'
	});
	ui_vue.BitrixVue.component('bx-im-view-element-keyboard', {
	  /*
	   * @emits 'click' {action: string, params: Object}
	   */
	  props: {
	    buttons: {
	      type: Array,
	      "default": function _default() {
	        return [];
	      }
	    },
	    messageId: {
	      "default": 0
	    },
	    userId: {
	      "default": 0
	    },
	    dialogId: {
	      "default": 0
	    }
	  },
	  data: function data() {
	    return {
	      isMobile: im_lib_utils.Utils.platform.isMobile(),
	      isBlocked: false,
	      localButtons: []
	    };
	  },
	  created: function created() {
	    this.localButtons = this.prepareButtons(this.buttons);
	  },
	  watch: {
	    buttons: function buttons() {
	      clearTimeout(this.recoverStateButton);
	      this.isBlocked = false;
	      this.localButtons = this.prepareButtons(this.buttons);
	    }
	  },
	  methods: {
	    click: function click(button) {
	      var _this = this;
	      if (this.isBlocked) {
	        return false;
	      }
	      if (button.DISABLED && button.DISABLED === 'Y') {
	        return false;
	      }
	      if (button.ACTION && button.ACTION_VALUE.toString()) {
	        this.$emit('click', {
	          action: 'ACTION',
	          params: {
	            dialogId: this.dialogId,
	            messageId: this.messageId,
	            botId: button.BOT_ID,
	            action: button.ACTION,
	            value: button.ACTION_VALUE
	          }
	        });
	      } else if (button.FUNCTION) {
	        var execFunction = button.FUNCTION.toString().replace('#MESSAGE_ID#', this.messageId).replace('#DIALOG_ID#', this.dialogId).replace('#USER_ID#', this.userId);
	        eval(execFunction);
	      } else if (button.APP_ID) {
	        im_lib_logger.Logger.warn('Messenger keyboard: open app is not implemented.');
	      } else if (button.LINK) {
	        if (im_lib_utils.Utils.platform.isBitrixMobile()) {
	          app.openNewPage(button.LINK);
	        } else {
	          window.open(button.LINK, '_blank');
	        }
	      } else if (button.WAIT !== 'Y') {
	        if (button.BLOCK === 'Y') {
	          this.isBlocked = true;
	        }
	        button.WAIT = 'Y';
	        this.$emit('click', {
	          action: 'COMMAND',
	          params: {
	            dialogId: this.dialogId,
	            messageId: this.messageId,
	            botId: button.BOT_ID,
	            command: button.COMMAND,
	            params: button.COMMAND_PARAMS
	          }
	        });
	        this.recoverStateButton = setTimeout(function () {
	          _this.isBlocked = false;
	          button.WAIT = 'N';
	        }, 10000);
	      }
	      return true;
	    },
	    getStyles: function getStyles(button) {
	      var styles = {};
	      if (button.WIDTH) {
	        styles['width'] = button.WIDTH + 'px';
	      } else if (button.DISPLAY === 'BLOCK') {
	        styles['width'] = '225px';
	      }
	      if (button.BG_COLOR) {
	        styles['backgroundColor'] = button.BG_COLOR;
	      }
	      if (button.TEXT_COLOR) {
	        styles['color'] = button.TEXT_COLOR;
	      }
	      return styles;
	    },
	    prepareButtons: function prepareButtons(buttons) {
	      return buttons.filter(function (button) {
	        if (!button.CONTEXT) {
	          return true;
	        }
	        if (im_lib_utils.Utils.platform.isBitrixMobile() && button.CONTEXT === 'DESKTOP') {
	          return false;
	        }
	        if (!im_lib_utils.Utils.platform.isBitrixMobile() && button.CONTEXT === 'MOBILE') {
	          return false;
	        }

	        // TODO activate this buttons
	        if (!im_lib_utils.Utils.platform.isBitrixMobile() && (button.ACTION === 'DIALOG' || button.ACTION === 'CALL')) {
	          return false;
	        }
	        return true;
	      });
	    }
	  },
	  computed: {
	    ButtonType: function ButtonType() {
	      return _ButtonType;
	    }
	  },
	  template: "\n\t\t<div :class=\"['bx-im-element-keyboard', {'bx-im-element-keyboard-mobile': isMobile}]\">\n\t\t\t<template v-for=\"(button, index) in localButtons\">\n\t\t\t\t<div v-if=\"button.TYPE === ButtonType.newline\" class=\"bx-im-element-keyboard-button-separator\"></div>\n\t\t\t\t<span v-else-if=\"button.TYPE === ButtonType.button\" :class=\"[\n\t\t\t\t\t'bx-im-element-keyboard-button', \n\t\t\t\t\t'bx-im-element-keyboard-button-'+button.DISPLAY.toLowerCase(), \n\t\t\t\t\t{\n\t\t\t\t\t\t'bx-im-element-keyboard-button-disabled': isBlocked || button.DISABLED === 'Y',\n\t\t\t\t\t\t'bx-im-element-keyboard-button-progress': button.WAIT === 'Y',\n\t\t\t\t\t}\n\t\t\t\t]\" @click=\"click(button)\">\n\t\t\t\t\t<span class=\"bx-im-element-keyboard-button-text\" :style=\"getStyles(button)\">{{button.TEXT}}</span>\n\t\t\t\t</span>\n\t\t\t</template>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX,BX,BX.Messenger.Lib,BX.Messenger.Lib));
//# sourceMappingURL=keyboard.bundle.js.map
