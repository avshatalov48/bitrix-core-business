(function (exports,ui_vue_components_list,im_view_list_item_sidebar,ui_vue,im_lib_logger) {
	'use strict';

	/**
	 * Bitrix IM
	 * Sidebar Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.cloneComponent('bx-im-view-list-sidebar', 'bx-list', {
	  props: ['recentData'],
	  data: function data() {
	    return Object.assign(this.parentData(), {
	      cssPrefix: 'bx-messenger-list-sidebar',
	      elementComponent: 'bx-im-view-list-item-sidebar',
	      showSectionNames: false
	    });
	  },
	  created: function created() {
	    this.parentCreated();
	  },
	  methods: {
	    onClick: function onClick(event, id) {
	      this.$emit('click', {
	        id: id,
	        $event: event
	      });
	    },
	    onRightClick: function onRightClick(event, id) {
	      this.$emit('rightClick', {
	        id: id,
	        $event: event
	      });
	    },
	    onScroll: function onScroll(event) {
	      this.$emit('scroll', event);
	    }
	  },
	  computed: {
	    list: function list() {
	      return this.recentData;
	    },
	    sections: function sections() {
	      return ['pinned', 'general'];
	    }
	  }
	});

}((this.window = this.window || {}),window,window,BX,BX.Messenger.Lib));
//# sourceMappingURL=sidebar.bundle.js.map
