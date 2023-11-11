/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,im_v2_lib_logger,im_v2_const) {
	'use strict';

	const IFRAME_PATH = '/desktop_app/';
	const IFRAME_PARAMS = {
	  IFRAME: 'Y',
	  IM_BACKGROUND: 'light',
	  IM_LINES: 'Y',
	  IM_MENU: 'N',
	  IM_STATUS: 'N',
	  IM_V2_LAYOUT: 'Y'
	};

	// @vue/component
	const OpenlineContent = {
	  name: 'OpenlineContent',
	  props: {
	    entityId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      isLoading: true
	    };
	  },
	  computed: {
	    iframeLink() {
	      const params = new URLSearchParams(IFRAME_PARAMS);
	      return `${IFRAME_PATH}?${params.toString()}`;
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Content: Openline created');
	    this.subscribeToLinesInitEvent();
	  },
	  methods: {
	    subscribeToLinesInitEvent() {
	      main_core.Event.bind(window, im_v2_const.EventType.lines.onInit, async () => {
	        await this.$nextTick();
	        this.isLoading = false;
	      });
	    }
	  },
	  template: `
		<div class="bx-im-content-openline__container">
			<iframe class="bx-im-content-openline__iframe" :src="iframeLink" ref="frame" />
			<div v-if="isLoading" class="bx-im-content-openline__loader">Loading...</div>
		</div>
	`
	};

	exports.OpenlineContent = OpenlineContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=openline-content.bundle.js.map
