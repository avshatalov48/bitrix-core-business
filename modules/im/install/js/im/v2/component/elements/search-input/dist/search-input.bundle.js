/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core_events,im_v2_const,im_v2_lib_utils,im_v2_lib_escManager,im_v2_component_elements_loader) {
	'use strict';

	// @vue/component
	const SearchInput = {
	  name: 'SearchInput',
	  components: {
	    Spinner: im_v2_component_elements_loader.Spinner
	  },
	  props: {
	    placeholder: {
	      type: String,
	      default: ''
	    },
	    searchMode: {
	      type: Boolean,
	      default: true
	    },
	    withIcon: {
	      type: Boolean,
	      default: true
	    },
	    withLoader: {
	      type: Boolean,
	      default: false
	    },
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    delayForFocusOnStart: {
	      type: Number || null,
	      default: null
	    }
	  },
	  emits: ['queryChange', 'inputFocus', 'inputBlur', 'keyPressed', 'close', 'closeByEsc'],
	  data() {
	    return {
	      query: '',
	      hasFocus: false
	    };
	  },
	  computed: {
	    SpinnerSize: () => im_v2_component_elements_loader.SpinnerSize,
	    SpinnerColor: () => im_v2_component_elements_loader.SpinnerColor,
	    isEmptyQuery() {
	      return this.query.length === 0;
	    }
	  },
	  watch: {
	    searchMode(newValue) {
	      if (newValue === true) {
	        this.focus();
	      } else {
	        this.query = '';
	        this.blur();
	      }
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.key.onBeforeEscape, this.onBeforeEscape);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.key.onBeforeEscape, this.onBeforeEscape);
	  },
	  mounted() {
	    if (this.delayForFocusOnStart === 0) {
	      this.focus();
	    } else if (this.delayForFocusOnStart > 0) {
	      setTimeout(() => {
	        this.focus();
	      }, this.delayForFocusOnStart);
	    }
	  },
	  methods: {
	    onBeforeEscape() {
	      if (!this.hasFocus) {
	        return im_v2_lib_escManager.EscEventAction.ignored;
	      }
	      if (this.isEmptyQuery) {
	        this.closeByEsc();
	      } else {
	        this.onClearInput();
	      }
	      return im_v2_lib_escManager.EscEventAction.handled;
	    },
	    onInputUpdate() {
	      this.$emit('queryChange', this.query);
	    },
	    onFocus() {
	      this.focus();
	      this.$emit('inputFocus');
	    },
	    onCloseClick() {
	      this.query = '';
	      this.hasFocus = false;
	      this.$emit('queryChange', this.query);
	      this.$emit('close');
	    },
	    onClearInput() {
	      this.query = '';
	      this.focus();
	      this.$emit('queryChange', this.query);
	    },
	    onKeyUp(event) {
	      if (im_v2_lib_utils.Utils.key.isCombination(event, 'Escape')) {
	        return;
	      }
	      this.$emit('keyPressed', event);
	    },
	    closeByEsc() {
	      this.query = '';
	      this.hasFocus = false;
	      this.$emit('queryChange', this.query);
	      this.$emit('closeByEsc');
	    },
	    focus() {
	      this.hasFocus = true;
	      this.$refs.searchInput.focus();
	    },
	    blur() {
	      this.hasFocus = false;
	      this.$refs.searchInput.blur();
	    }
	  },
	  template: `
		<div class="bx-im-search-input__scope bx-im-search-input__container" :class="{'--has-focus': hasFocus}">
			<div v-if="!isLoading" class="bx-im-search-input__search-icon"></div>
			<Spinner 
				v-if="withLoader && isLoading" 
				:size="SpinnerSize.XXS" 
				:color="SpinnerColor.grey" 
				class="bx-im-search-input__loader"
			/>
			<input
				@focus="onFocus"
				@input="onInputUpdate"
				@keyup="onKeyUp"
				v-model="query"
				class="bx-im-search-input__element"
				:class="{'--with-icon': withIcon}"
				:placeholder="placeholder"
				ref="searchInput"
			/>
			<div v-if="hasFocus" class="bx-im-search-input__close-icon" @click="onCloseClick"></div>
		</div>
	`
	};

	exports.SearchInput = SearchInput;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=search-input.bundle.js.map
