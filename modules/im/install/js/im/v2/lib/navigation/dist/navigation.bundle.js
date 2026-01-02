/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_infoHelper,im_v2_const,im_v2_lib_analytics,im_v2_lib_feature,im_v2_lib_layout,im_v2_lib_phone,im_v2_lib_utils,im_v2_lib_market) {
	'use strict';

	const customClickHandler = {
	  [im_v2_const.NavigationMenuItem.copilot]: onCopilotClick,
	  [im_v2_const.NavigationMenuItem.call]: onCallClick,
	  [im_v2_const.NavigationMenuItem.timemanager]: onTimeManagerClick,
	  [im_v2_const.NavigationMenuItem.homepage]: onHomepageClick,
	  [im_v2_const.NavigationMenuItem.market]: onMarketClick
	};
	const NavigationManager = {
	  open(menuItem) {
	    const {
	      id,
	      entityId
	    } = menuItem;
	    if (!im_v2_const.NavigationMenuItem[id]) {
	      return;
	    }
	    if (customClickHandler[id]) {
	      customClickHandler[id](menuItem);
	      return;
	    }
	    changeLayout({
	      layoutName: id,
	      layoutEntityId: entityId
	    });
	  },
	  isLayout(id) {
	    return Boolean(im_v2_const.Layout[id]);
	  },
	  isMarketApp(payload) {
	    const {
	      id,
	      entityId
	    } = payload;
	    const isMarketMenuItem = id === im_v2_const.NavigationMenuItem.market;
	    return isMarketMenuItem && Boolean(entityId);
	  }
	};
	function onCopilotClick(payload) {
	  if (!im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.copilotActive)) {
	    const promoter = new ui_infoHelper.FeaturePromoter({
	      code: im_v2_const.SliderCode.copilotDisabled
	    });
	    promoter.show();
	    im_v2_lib_analytics.Analytics.getInstance().copilot.onOpenTab({
	      isAvailable: false
	    });
	    return;
	  }
	  changeLayout({
	    layoutName: im_v2_const.Layout.copilot,
	    layoutEntityId: payload.entityId
	  });
	}
	function onCallClick(payload) {
	  const KEYPAD_OFFSET_TOP = -30;
	  const KEYPAD_OFFSET_LEFT = 64;
	  im_v2_lib_phone.PhoneManager.getInstance().openKeyPad({
	    bindElement: payload == null ? void 0 : payload.target,
	    offsetTop: KEYPAD_OFFSET_TOP,
	    offsetLeft: KEYPAD_OFFSET_LEFT
	  });
	}
	function onTimeManagerClick() {
	  var _BX$Timeman, _BX$Timeman$Monitor;
	  (_BX$Timeman = BX.Timeman) == null ? void 0 : (_BX$Timeman$Monitor = _BX$Timeman.Monitor) == null ? void 0 : _BX$Timeman$Monitor.openReport();
	}
	function onHomepageClick() {
	  im_v2_lib_utils.Utils.browser.openLink('/');
	}
	function onMarketClick(payload) {
	  const {
	    entityId
	  } = payload;
	  if (entityId) {
	    changeLayout({
	      layoutName: im_v2_const.Layout.market,
	      layoutEntityId: entityId
	    });
	    return;
	  }
	  im_v2_lib_market.MarketManager.openMarketplace();
	}
	function changeLayout({
	  layoutName,
	  layoutEntityId
	}) {
	  const layoutManager = im_v2_lib_layout.LayoutManager.getInstance();
	  if (!layoutManager.isValidLayout(layoutName)) {
	    return;
	  }
	  let entityId = layoutEntityId;
	  const lastOpenedElement = layoutManager.getLastOpenedElement(layoutName);
	  if (!entityId && lastOpenedElement) {
	    entityId = lastOpenedElement;
	  }
	  void layoutManager.setLayout({
	    name: layoutName,
	    entityId
	  });
	}

	exports.NavigationManager = NavigationManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.UI,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=navigation.bundle.js.map
