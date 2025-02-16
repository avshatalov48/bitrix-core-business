import { computed } from 'ui.vue3';
import { AccessRightItem } from '../../store/model/access-rights-model';
import type { UserGroup } from '../../store/model/user-groups-model';
import { shouldRowBeRendered } from '../../utils';
import { ColumnLayout } from '../layout/column-layout';
import { MenuCell } from './column/menu-cell';
import { ValueCell } from './column/value-cell';

export const Column = {
	name: 'Column',
	components: {
		ColumnLayout,
		ValueCell,
		MenuCell,
	},
	props: {
		userGroup: {
			/** @type UserGroup */
			type: Object,
			required: true,
		},
		rights: {
			type: Map,
			required: true,
		},
	},
	provide(): Object {
		return {
			userGroup: computed(() => this.userGroup),
		};
	},
	computed: {
		renderedRights(): Map<string, AccessRightItem> {
			const result = new Map();
			for (const [rightId: string, right: AccessRightItem] of this.rights)
			{
				if (shouldRowBeRendered(right))
				{
					result.set(rightId, right);
				}
			}

			return result;
		},
	},
	template: `
		<ColumnLayout ref="column">
			<MenuCell/>
			<ValueCell
				v-for="[rightId, accessRightItem] in renderedRights"
				:key="rightId"
				:right="accessRightItem"
			/>
		</ColumnLayout>
	`,
};
