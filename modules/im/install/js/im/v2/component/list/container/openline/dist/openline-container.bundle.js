this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_list_elementList_openline,im_v2_component_search2_searchInput,im_v2_component_search2_searchResult,im_v2_lib_logger) {
	'use strict';

	// @vue/component
	const OpenlineListContainer = {
	  components: {
	    OpenlineList: im_v2_component_list_elementList_openline.OpenlineList,
	    SearchInput: im_v2_component_search2_searchInput.SearchInput,
	    SearchResult: im_v2_component_search2_searchResult.SearchResult
	  },
	  emits: ['selectEntity'],
	  data() {
	    return {
	      searchMode: false,
	      searchQuery: ''
	    };
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('List: Openline container created');
	  },
	  methods: {
	    onChatClick(dialogId) {
	      this.$emit('selectEntity', {
	        layoutName: 'openline',
	        entityId: dialogId
	      });
	    },
	    onOpenSearch() {
	      this.searchMode = true;
	    },
	    onCloseSearch() {
	      this.searchMode = false;
	      this.searchQuery = '';
	    },
	    onUpdateSearch(query) {
	      this.searchQuery = query;
	    }
	  },
	  template: `
		<SearchInput @openSearch="onOpenSearch" @closeSearch="onCloseSearch" @updateSearch="onUpdateSearch" />
		<SearchResult v-if="searchMode" :searchQuery="searchQuery" />
		<OpenlineList v-else @chatClick="onChatClick" />
	`
	};

	exports.OpenlineListContainer = OpenlineListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Component.List,BX,BX,BX.Messenger.v2.Lib));
//# sourceMappingURL=openline-container.bundle.js.map
