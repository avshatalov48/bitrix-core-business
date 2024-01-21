/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,im_v2_lib_market,im_v2_component_elements) {
	'use strict';

	// @vue/component
	const MarketContent = {
	  name: 'MarketContent',
	  components: {
	    Spinner: im_v2_component_elements.Spinner
	  },
	  props: {
	    entityId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isLoading: true,
	      handleResult: true
	    };
	  },
	  computed: {
	    SpinnerSize: () => im_v2_component_elements.SpinnerSize
	  },
	  watch: {
	    entityId(newValue) {
	      this.isLoading = true;
	      this.load(newValue);
	    }
	  },
	  beforeUnmount() {
	    this.marketManager.unloadPlacement(this.entityId);
	    this.handleResult = false;
	  },
	  created() {
	    this.marketManager = im_v2_lib_market.MarketManager.getInstance();
	  },
	  mounted() {
	    this.load(this.entityId);
	  },
	  methods: {
	    load(placementId) {
	      this.marketManager.loadPlacement(placementId).then(response => {
	        if (!this.handleResult || this.entityId !== placementId) {
	          return;
	        }
	        main_core.Runtime.html(this.$refs['im-messenger-placement'], response);
	      }).finally(() => {
	        this.isLoading = false;
	      });
	    }
	  },
	  template: `
		<div class="bx-content-market__container">
			<div v-if="isLoading" class="bx-content-market__loader-container">
				<Spinner :size="SpinnerSize.L" />
			</div>
			<div 
				v-show="!isLoading" 
				class="bx-content-market__placement-container" 
				ref="im-messenger-placement"
			></div>
		</div>
	`
	};

	exports.MarketContent = MarketContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=market-content.bundle.js.map
