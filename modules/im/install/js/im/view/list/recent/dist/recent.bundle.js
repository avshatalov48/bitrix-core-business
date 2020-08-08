(function (exports,ui_vue_components_list,im_view_list_item_recent,ui_vue,im_lib_logger) {
	'use strict';

	/**
	 * Bitrix UI
	 * Recent list Vue component
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.cloneComponent('bx-im-view-list-recent', 'bx-list', {
	  props: ['recentData'],
	  data: function data() {
	    return Object.assign(this.parentData(), {
	      cssPrefix: 'bx-messenger-list-recent',
	      elementComponent: 'bx-im-view-list-item-recent',
	      showSectionNames: false
	    });
	  },
	  created: function created() {
	    this.parentCreated();
	  },
	  computed: {
	    list: function list() {
	      return this.recentData;
	    },
	    sections: function sections() {
	      return ['pinned', 'general'];
	    }
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
	  }
	});

}((this.window = this.window || {}),window,window,BX,BX.Messenger.Lib));
//# sourceMappingURL=recent.bundle.js.map
