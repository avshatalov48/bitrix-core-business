(function (exports,ui_vue) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Textarea Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.component('bx-im-view-quote-panel', {
	  /**
	   * @emits 'close' {}
	   */
	  props: {
	    id: {
	      default: 0
	    },
	    title: {
	      default: ''
	    },
	    description: {
	      default: ''
	    },
	    color: {
	      default: ''
	    },
	    canClose: {
	      default: true
	    }
	  },
	  methods: {
	    close: function close(event) {
	      this.$emit('close', event);
	    }
	  },
	  computed: {
	    formattedTittle: function formattedTittle() {
	      return this.title ? this.title.substr(0, 255) : this.localize.IM_QUOTE_PANEL_DEFAULT_TITLE;
	    },
	    formattedDescription: function formattedDescription() {
	      return this.description ? this.description.substr(0, 255) : '';
	    },
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('IM_QUOTE_PANEL_', this.$root.$bitrixMessages);
	    }
	  },
	  template: "\n\t\t<transition enter-active-class=\"bx-im-quote-panel-animation-show\" leave-active-class=\"bx-im-quote-panel-animation-close\">\t\t\t\t\n\t\t\t<div v-if=\"id > 0\" class=\"bx-im-quote-panel\">\n\t\t\t\t<div class=\"bx-im-quote-panel-wrap\">\n\t\t\t\t\t<div class=\"bx-im-quote-panel-box\" :style=\"{borderLeftColor: color}\">\n\t\t\t\t\t\t<div class=\"bx-im-quote-panel-box-title\" :style=\"{color: color}\">{{formattedTittle}}</div>\n\t\t\t\t\t\t<div class=\"bx-im-quote-panel-box-desc\">{{formattedDescription}}</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div v-if=\"canClose\" class=\"bx-im-quote-panel-close\" @click=\"close\"></div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</transition>\n\t"
	});

}((this.window = this.window || {}),BX));
//# sourceMappingURL=quotepanel.bundle.js.map
