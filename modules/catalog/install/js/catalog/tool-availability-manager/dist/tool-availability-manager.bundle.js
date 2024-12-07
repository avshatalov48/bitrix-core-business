/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	class OneCPlanRestrictionSlider {
	  static show({
	    onActivateSuccessHandler
	  } = {}) {
	    top.BX.UI.InfoHelper.show('limit_crm_1c_inventory_control', {
	      featureId: 'catalog_inventory_management_1c'
	    });
	    const context = top;
	    const onSuccessHandler = () => {
	      var _context$BX$SidePanel;
	      (_context$BX$SidePanel = context.BX.SidePanel.Instance.getTopSlider()) == null ? void 0 : _context$BX$SidePanel.close();
	      if (main_core.Type.isFunction(onActivateSuccessHandler)) {
	        onActivateSuccessHandler();
	      }
	    };
	    top.BX.Event.EventEmitter.subscribeOnce('BX.UI.InfoHelper:onActivateTrialFeatureSuccess', onSuccessHandler);
	    top.BX.Event.EventEmitter.subscribeOnce('BX.UI.InfoHelper:onActivateDemoLicenseSuccess', onSuccessHandler);
	  }
	}

	class ToolAvailabilityManager {
	  static openInventoryManagementToolDisabledSlider() {
	    ToolAvailabilityManager.openSliderByCode('limit_store_inventory_management_off');
	  }
	  static openSliderByCode(sliderCode) {
	    main_core.Runtime.loadExtension('ui.info-helper').then(() => {
	      top.BX.UI.InfoHelper.show(sliderCode);
	    });
	  }
	}

	exports.ToolAvailabilityManager = ToolAvailabilityManager;
	exports.OneCPlanRestrictionSlider = OneCPlanRestrictionSlider;

}((this.BX.Catalog = this.BX.Catalog || {}),BX));
//# sourceMappingURL=tool-availability-manager.bundle.js.map
