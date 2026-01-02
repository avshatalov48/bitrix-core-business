/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_popup) {
	'use strict';

	// @vue/component
	const Dropdown = {
	  name: 'ChatDropdown',
	  props: {
	    items: {
	      type: Object,
	      required: true
	    },
	    id: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['itemChange'],
	  data() {
	    return {
	      selectedElement: '',
	      menuOpened: false
	    };
	  },
	  computed: {
	    formattedItems() {
	      const map = {};
	      this.items.forEach(item => {
	        map[item.value] = item;
	      });
	      return map;
	    },
	    defaultItem() {
	      return this.items.find(item => {
	        return item.default === true;
	      });
	    }
	  },
	  created() {
	    this.menuInstance = null;
	    if (this.defaultItem) {
	      this.selectedElement = this.defaultItem.value;
	    }
	  },
	  beforeUnmount() {
	    var _this$menuInstance;
	    (_this$menuInstance = this.menuInstance) == null ? void 0 : _this$menuInstance.destroy();
	  },
	  methods: {
	    toggleMenu() {
	      if (!this.menuInstance) {
	        this.menuInstance = this.getMenuInstance();
	      }
	      if (this.menuOpened) {
	        this.menuInstance.close();
	        return;
	      }
	      this.menuInstance.show();
	      const width = this.$refs.container.clientWidth;
	      this.menuInstance.getPopupWindow().setWidth(width);
	      this.menuOpened = true;
	    },
	    getMenuInstance() {
	      return main_popup.MenuManager.create({
	        id: this.id,
	        bindOptions: {
	          forceBindPosition: true,
	          position: 'bottom'
	        },
	        targetContainer: document.body,
	        bindElement: this.$refs.container,
	        items: this.getMenuItems(),
	        events: {
	          onClose: () => {
	            this.menuOpened = false;
	          }
	        }
	      });
	    },
	    getMenuItems() {
	      return Object.values(this.formattedItems).map(item => {
	        return {
	          text: item.text,
	          onclick: () => {
	            this.selectedElement = item.value;
	            this.$emit('itemChange', item.value);
	            this.menuInstance.close();
	          }
	        };
	      });
	    }
	  },
	  template: `
		<div class="bx-im-dropdown__container bx-im-dropdown__scope">
			<div @click="toggleMenu" class="ui-ctl ui-ctl-xl ui-ctl-w100 ui-ctl-after-icon ui-ctl-dropdown" ref="container">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<div class="ui-ctl-element">{{ formattedItems[selectedElement].text }}</div>
			</div>
		</div>
	`
	};

	exports.Dropdown = Dropdown;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX.Main));
//# sourceMappingURL=dropdown.bundle.js.map
