import { AccessRightSection } from '../../access-rights-model';
import type { AccessRightItem, AccessRightsCollection } from '../../access-rights-model';
import { BaseEvent, EventEmitter } from 'main.core.events';

export type RightSaveData = {
	id: string,
	name: string,
	rightData: Object,
};

export class AccessRightsExporter implements Transformer<AccessRightsCollection, RightSaveData[]>
{
	transform(source: AccessRightsCollection, appGuid: string): RightSaveData[]
	{
		const result: RightSaveData[] = [];

		for (const accessRightSection: AccessRightSection of source.values())
		{
			for (const accessRight: AccessRightItem of accessRightSection.rights.values())
			{
				const data = {
					id: accessRight.id,
					name: accessRight.title,
					additionalRightData: {},
				};
				const eventResults = EventEmitter.emit('BX.UI.AccessRights.V2:additionalRightData', {
					guid: appGuid,
					right: accessRight,
				});
				for (const eventResult: BaseEvent of eventResults)
				{
					data.additionalRightData = { ...data.additionalRightData, ...eventResult.getData()?.additionalRightData };
				}
				result.push(data);
			}
		}

		return result;
	}
}
