import { Runtime } from 'main.core';
import { OneCPlanRestrictionSlider } from './onec-plan-restriction-slider';

class ToolAvailabilityManager
{
	static openInventoryManagementToolDisabledSlider()
	{
		ToolAvailabilityManager.openSliderByCode('limit_store_inventory_management_off');
	}

	static openSliderByCode(sliderCode)
	{
		Runtime.loadExtension('ui.info-helper').then(() => {
			top.BX.UI.InfoHelper.show(sliderCode);
		});
	}
}

export {
	ToolAvailabilityManager,
	OneCPlanRestrictionSlider,
};
