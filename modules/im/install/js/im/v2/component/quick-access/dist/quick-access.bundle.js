this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_component_list_elementList_recent,im_v2_lib_logger,im_v2_lib_init) {
	'use strict';

	const QuickAccess = {
	  name: 'QuickAccess',
	  props: {
	    compactMode: {
	      type: Boolean,
	      default: false
	    }
	  },
	  components: {
	    RecentList: im_v2_component_list_elementList_recent.RecentList
	  },
	  created() {
	    im_v2_lib_init.InitManager.start();
	    im_v2_lib_logger.Logger.warn('Quick access created');
	  },
	  template: `
		<RecentList :compactMode="compactMode" />
	`
	};

	exports.QuickAccess = QuickAccess;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Messenger.v2.Component.List,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=quick-access.bundle.js.map
