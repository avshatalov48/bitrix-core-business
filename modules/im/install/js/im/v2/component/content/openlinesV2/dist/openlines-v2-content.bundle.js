/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,imopenlines_v2_component_content_openlines) {
	'use strict';

	// @vue/component
	const OpenlinesV2Content = {
	  name: 'OpenlinesV2Content',
	  components: {
	    OpenLinesContent: imopenlines_v2_component_content_openlines.OpenLinesContent
	  },
	  props: {
	    entityId: {
	      type: String,
	      default: ''
	    }
	  },
	  template: `
		<OpenLinesContent
			:dialogId="entityId"
		/>
	`
	};

	exports.OpenlinesV2Content = OpenlinesV2Content;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.OpenLines.v2.Component.Content));
//# sourceMappingURL=openlines-v2-content.bundle.js.map
