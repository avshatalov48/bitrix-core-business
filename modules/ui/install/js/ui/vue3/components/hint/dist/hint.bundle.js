this.BX = this.BX || {};
this.BX.Vue3 = this.BX.Vue3 || {};
(function (exports,ui_vue3_directives_hint) {
	'use strict';

	/**
	 * Hint Vue directive
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2021 Bitrix
	 */
	/*
		<Hint :text="$Bitrix.Loc.getMessage('HINT_PLAIN')"/>
		<Hint :html="$Bitrix.Loc.getMessage('HINT_PLAIN')"/>
		<Hint text="Custom position top and light mode" position="top" :popupOptions="{darkMode: false}"/>
	*/

	const Hint = {
	  props: {
	    text: {
	      default: ''
	    },
	    html: {
	      default: ''
	    },
	    position: {
	      default: 'bottom'
	    },
	    popupOptions: {
	      default() {
	        return {};
	      }

	    }
	  },
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  template: `
		<span class="ui-hint" v-hint="{text, html, position, popupOptions}" data-hint-init="vue">
			<span class="ui-hint-icon"/>
		</span>
	`
	};

	exports.Hint = Hint;

}((this.BX.Vue3.Components = this.BX.Vue3.Components || {}),BX.Vue3.Directives));
//# sourceMappingURL=hint.bundle.js.map
