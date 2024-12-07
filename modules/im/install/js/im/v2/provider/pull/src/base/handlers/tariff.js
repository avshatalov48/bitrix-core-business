import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';

import { ChangeTariffParams } from '../../types/tariff';

export class TariffPullHandler
{
	handleChangeTariff(params: ChangeTariffParams)
	{
		Logger.warn('TariffPullHandler: handleChangeTariff', params);
		const { tariffRestrictions } = params;
		if (!tariffRestrictions)
		{
			return;
		}

		if (tariffRestrictions.fullChatHistory?.isAvailable === true)
		{
			return;
		}

		void Core.getStore().dispatch('application/tariffRestrictions/set', tariffRestrictions);
	}
}
