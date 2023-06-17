this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_const,im_v2_lib_utils) {
	'use strict';

	// @vue/component
	const SearchInput = {
	  props: {
	    searchMode: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['closeSearch', 'openSearch', 'updateSearch'],
	  data() {
	    return {
	      isActive: false,
	      searchQuery: ''
	    };
	  },
	  computed: {
	    isEmptyQuery() {
	      return this.searchQuery.length === 0;
	    }
	  },
	  watch: {
	    searchMode(newValue, oldValue) {
	      if (newValue === true && oldValue === false) {
	        this.focus();
	      }
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.close, this.onCloseClick);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.close, this.onCloseClick);
	  },
	  methods: {
	    onInputClick() {
	      this.isActive = true;
	      this.$emit('openSearch');
	    },
	    onCloseClick() {
	      this.isActive = false;
	      this.searchQuery = '';
	      this.$emit('closeSearch');
	    },
	    onClearInput() {
	      this.isActive = true;
	      this.searchQuery = '';
	      this.$emit('updateSearch', this.searchQuery);
	    },
	    onInputUpdate() {
	      this.isActive = true;
	      this.$emit('updateSearch', this.searchQuery);
	    },
	    onKeyUp(event) {
	      if (im_v2_lib_utils.Utils.key.isCombination(event, 'Escape')) {
	        this.onEscapePressed();
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.keyPressed, {
	        keyboardEvent: event
	      });
	    },
	    onEscapePressed() {
	      if (this.isEmptyQuery) {
	        this.onCloseClick();
	        this.$refs['searchInput'].blur();
	      } else {
	        this.onClearInput();
	      }
	    },
	    focus() {
	      this.isActive = true;
	      this.$refs.searchInput.focus();
	    }
	  },
	  template: `
		<div class="bx-im-search-input__scope bx-im-search-input__container">
			<div class="bx-im-search-input__search-icon"></div>
			<input
				@click="onInputClick"
				@input="onInputUpdate"
				@keyup="onKeyUp"
				v-model="searchQuery"
				class="bx-im-search-input__element"
				:placeholder="$Bitrix.Loc.getMessage('IM_SEARCH_INPUT_PLACEHOLDER')"
				ref="searchInput"
			/>
			<div v-if="isActive" @click="onCloseClick" class="bx-im-search-input__close-icon"></div>
		</div>
	`
	};

	exports.SearchInput = SearchInput;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=search-input.bundle.js.map
