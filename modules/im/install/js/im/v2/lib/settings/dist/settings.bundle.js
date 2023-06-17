this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,im_v2_const,im_v2_lib_logger,im_v2_lib_utils) {
	'use strict';

	class SettingsManager {
	  static init($Bitrix) {
	    if (this.instance) {
	      return;
	    }
	    this.instance = new this($Bitrix);
	  }
	  constructor($Bitrix) {
	    this.store = null;
	    this.$Bitrix = null;
	    this.$Bitrix = $Bitrix;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.initSettings();
	    this.onSettingsChangeHandler = this.onSettingsChange.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.settingsChange, this.onSettingsChangeHandler);
	    if (im_v2_lib_utils.Utils.platform.isBitrixDesktop() && !main_core.Type.isUndefined(BX.desktop)) {
	      BX.desktop.addCustomEvent('bxSaveSettings', settings => {
	        this.onSettingsChangeHandler({
	          data: settings
	        });
	      });
	    }
	  }
	  initSettings() {
	    if (!BX.MessengerProxy) {
	      return false;
	    }
	    this.initGeneralSettings();
	    this.initRecentSettings();
	  }
	  initGeneralSettings() {
	    const initialSettings = {};
	    Object.entries(im_v2_const.SettingsMap).forEach(([oldName, name]) => {
	      initialSettings[name] = BX.MessengerProxy.getOption(oldName);
	    });
	    this.store.dispatch('application/setOptions', initialSettings);
	  }
	  initRecentSettings() {
	    const initialSettings = {};
	    Object.entries(im_v2_const.RecentSettingsMap).forEach(([oldName, name]) => {
	      initialSettings[name] = BX.MessengerProxy.getOption(oldName);
	    });
	    this.store.dispatch('recent/setOptions', initialSettings);
	  }
	  onSettingsChange({
	    data: event
	  }) {
	    im_v2_lib_logger.Logger.warn('Im.RecentList: SettingsChange', event);
	    const generalSettings = {};
	    const recentSettings = {};
	    Object.entries(event).forEach(([name, value]) => {
	      if (Object.keys(im_v2_const.RecentSettingsMap).includes(name)) {
	        recentSettings[im_v2_const.RecentSettingsMap[name]] = value;
	      }
	      if (Object.keys(im_v2_const.SettingsMap).includes(name)) {
	        generalSettings[im_v2_const.SettingsMap[name]] = value;
	      }
	    });
	    this.store.dispatch('application/setOptions', generalSettings);
	    this.store.dispatch('recent/setOptions', recentSettings);
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.settingsChange, this.onSettingsChangeHandler);
	  }
	}
	SettingsManager.instance = null;

	exports.SettingsManager = SettingsManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=settings.bundle.js.map
