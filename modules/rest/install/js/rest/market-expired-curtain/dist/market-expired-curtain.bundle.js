/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,ui_bannerDispatcher,ui_notificationPanel,ui_iconSet_api_core,ui_buttons,ui_analytics) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _getPanel = /*#__PURE__*/new WeakSet();
	var _sendAnalytics = /*#__PURE__*/new WeakSet();
	var MarketExpiredCurtain = /*#__PURE__*/function () {
	  function MarketExpiredCurtain(id) {
	    babelHelpers.classCallCheck(this, MarketExpiredCurtain);
	    _classPrivateMethodInitSpec(this, _sendAnalytics);
	    _classPrivateMethodInitSpec(this, _getPanel);
	    this.id = id;
	  }
	  babelHelpers.createClass(MarketExpiredCurtain, [{
	    key: "show",
	    value: function show() {
	      var _this = this;
	      ui_bannerDispatcher.BannerDispatcher.critical.toQueue(function (onDone) {
	        var panel = _classPrivateMethodGet(_this, _getPanel, _getPanel2).call(_this, onDone);
	        panel.show();
	        _classPrivateMethodGet(_this, _sendAnalytics, _sendAnalytics2).call(_this, 'show_notification_panel');
	      });
	    }
	  }]);
	  return MarketExpiredCurtain;
	}();
	function _getPanel2(onDone) {
	  var _this2 = this;
	  var panel = new ui_notificationPanel.NotificationPanel({
	    content: main_core.Loc.getMessage('REST_SIDEPANEL_WRAPPER_MARKET_EXPIRED_NOTIFICATION_TEXT'),
	    backgroundColor: '#E89B06',
	    textColor: '#FFFFFF',
	    crossColor: '#FFFFFF',
	    leftIcon: new ui_iconSet_api_core.Icon({
	      icon: ui_iconSet_api_core.Main.MARKET_1,
	      color: '#FFFFFF'
	    }),
	    rightButtons: [new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_SIDEPANEL_WRAPPER_MARKET_EXPIRED_NOTIFICATION_BUTTON_TEXT'),
	      size: ui_buttons.Button.Size.EXTRA_SMALL,
	      color: ui_buttons.Button.Color.CURTAIN_WARNING,
	      tag: ui_buttons.Button.Tag.LINK,
	      noCaps: true,
	      round: true,
	      props: {
	        href: 'FEATURE_PROMOTER=limit_v2_nosubscription_marketplace_withapplications_off'
	      },
	      onclick: function onclick() {
	        panel.hide();
	        _classPrivateMethodGet(_this2, _sendAnalytics, _sendAnalytics2).call(_this2, 'click_button');
	      }
	    })],
	    events: {
	      onHide: function onHide() {
	        onDone();
	        BX.userOptions.save('rest', "marketTransitionCurtain".concat(_this2.id, "Ts"), null, Math.floor(Date.now() / 1000));
	      }
	    },
	    zIndex: 1001
	  });
	  return panel;
	}
	function _sendAnalytics2(event) {
	  var params = {
	    tool: 'infohelper',
	    category: 'market',
	    event: event,
	    type: 'notification_panel'
	  };
	  ui_analytics.sendData(params);
	}

	exports.MarketExpiredCurtain = MarketExpiredCurtain;

}((this.BX.Rest = this.BX.Rest || {}),BX,BX.UI,BX.UI,BX.UI.IconSet,BX.UI,BX.UI.Analytics));
//# sourceMappingURL=market-expired-curtain.bundle.js.map
