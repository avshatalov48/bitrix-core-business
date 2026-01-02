/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.System = this.BX.UI.System || {};
(function (exports,main_core,ui_system_menu) {
	'use strict';

	const BMenu = {
	  name: 'BMenu',
	  props: {
	    id: {
	      type: String,
	      default: () => `ui-vue3-menu-${main_core.Text.getRandom()}`
	    },
	    options: {
	      /** @type MenuOptions */
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    menuOptions() {
	      return {
	        ...this.defaultOptions,
	        ...this.options
	      };
	    },
	    defaultOptions() {
	      return {
	        id: this.id,
	        cacheable: false,
	        animation: 'fading',
	        events: {
	          onClose: this.handleClose,
	          onDestroy: this.handleClose
	        }
	      };
	    }
	  },
	  mounted() {
	    this.menu = new ui_system_menu.Menu(this.menuOptions);
	    this.menu.show();
	  },
	  unmounted() {
	    var _this$menu;
	    (_this$menu = this.menu) == null ? void 0 : _this$menu.close();
	  },
	  methods: {
	    handleClose() {
	      this.$emit('close');
	    }
	  },
	  template: `
		<div v-if="false"></div>
	`
	};

	Object.keys(ui_system_menu).forEach(function (key) { exports[key] = ui_system_menu[key]; });
	exports.BMenu = BMenu;

}((this.BX.UI.System.Menu = this.BX.UI.System.Menu || {}),BX,BX.UI.System));
//# sourceMappingURL=vue.bundle.js.map
