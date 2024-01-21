/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_const,im_v2_component_elements) {
	'use strict';

	// @vue/component
	const ChatSearchInput = {
	  name: 'ChatSearchInput',
	  components: {
	    SearchInput: im_v2_component_elements.SearchInput
	  },
	  props: {
	    searchMode: {
	      type: Boolean,
	      required: true
	    },
	    isLoading: {
	      type: Boolean,
	      required: false
	    },
	    delayForFocusOnStart: {
	      type: Number,
	      default: 0
	    },
	    withIcon: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['closeSearch', 'openSearch', 'updateSearch'],
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.close, this.onClose);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.close, this.onClose);
	  },
	  methods: {
	    onInputFocus() {
	      this.$emit('openSearch');
	    },
	    onClose() {
	      this.$emit('closeSearch');
	    },
	    onInputUpdate(query) {
	      this.$emit('updateSearch', query);
	    },
	    onKeyPressed(event) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.keyPressed, {
	        keyboardEvent: event
	      });
	    }
	  },
	  template: `
		<SearchInput
			:placeholder="$Bitrix.Loc.getMessage('IM_SEARCH_INPUT_PLACEHOLDER_V2')"
			:searchMode="searchMode"
			:isLoading="isLoading"
			:withLoader="true"
			:delayForFocusOnStart="delayForFocusOnStart"
			:withIcon="withIcon"
			@inputFocus="onInputFocus"
			@inputBlur="onClose"
			@queryChange="onInputUpdate"
			@keyPressed="onKeyPressed"
			@close="onClose"
		/>
	`
	};

	exports.ChatSearchInput = ChatSearchInput;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=chat-search-input.bundle.js.map
