import { Type } from 'main.core';

export class OneCPlanRestrictionSlider
{
	static show({ onActivateSuccessHandler } = {}): void
	{
		top.BX.UI.InfoHelper.show('limit_crm_1c_inventory_control', { featureId: 'catalog_inventory_management_1c' });

		const context = top;
		const onSuccessHandler = () => {
			context.BX.SidePanel.Instance.getTopSlider()?.close();
			if (Type.isFunction(onActivateSuccessHandler))
			{
				onActivateSuccessHandler();
			}
		};
		top.BX.Event.EventEmitter.subscribeOnce('BX.UI.InfoHelper:onActivateTrialFeatureSuccess', onSuccessHandler);
		top.BX.Event.EventEmitter.subscribeOnce('BX.UI.InfoHelper:onActivateDemoLicenseSuccess', onSuccessHandler);
	}
}
