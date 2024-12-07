import { SidebarDetailBlock } from 'im.v2.const';

import { TariffLimit } from '../../../elements/tariff-limit/tariff-limit';

// @vue/component
export const TariffLimitPreview = {
	name: 'TariffLimitPreview',
	components: { TariffLimit },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
	},
	template: `
		<TariffLimit :dialogId="dialogId" :panel="SidebarDetailBlock.main" />
	`,
};
