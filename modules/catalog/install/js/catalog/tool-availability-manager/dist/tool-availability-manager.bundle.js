/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

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

}((this.BX.Catalog = this.BX.Catalog || {}),BX));
//# sourceMappingURL=tool-availability-manager.bundle.js.map
