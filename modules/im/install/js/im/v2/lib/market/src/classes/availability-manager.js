import {DialogType} from 'im.v2.const';

import type {ImModelMarketApplication} from 'im.v2.model';

const MarketTypes = Object.freeze({
	user: 'user',
	chat: 'chat',
	lines: 'lines',
	crm: 'crm',
	all: 'all',
});

export class AvailabilityManager
{
	getAvailablePlacements(placements: ImModelMarketApplication[], dialogType: string = '')
	{
		return placements.filter(placement => this.#canShowPlacementInChat(placement, dialogType));
	}

	#canShowPlacementInChat(placement: ImModelMarketApplication, dialogType: string)
	{
		if (!placement.options.context || !dialogType)
		{
			return true;
		}

		return placement.options.context.some(marketType => this.#matchDialogType(marketType, dialogType));
	}

	#matchDialogType(marketType: string, dialogType: $Values<typeof DialogType>)
	{
		switch (marketType) {
			case MarketTypes.user:
				return this.#isUser(dialogType);
			case MarketTypes.chat:
				return this.#isChat(dialogType);
			case MarketTypes.lines:
				return this.#isLines(dialogType);
			case MarketTypes.crm:
				return this.#isCrm(dialogType);
			case MarketTypes.all:
				return true;
			default:
				return false;
		}
	}

	#isUser(dialogType: $Values<typeof DialogType>): boolean
	{
		return dialogType === DialogType.user;
	}

	#isChat(dialogType: $Values<typeof DialogType>): boolean
	{
		return dialogType !== DialogType.lines && dialogType !== DialogType.crm && dialogType !== DialogType.user;
	}

	#isLines(dialogType: $Values<typeof DialogType>): boolean
	{
		return dialogType === DialogType.lines;
	}

	#isCrm(dialogType: $Values<typeof DialogType>): boolean
	{
		return dialogType === DialogType.crm;
	}
}
