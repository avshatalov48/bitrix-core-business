import { AccessRightItem } from '../../store/model/access-rights-model';
import { shouldRowBeRendered } from '../../utils';
import { CellLayout } from '../layout/cell-layout';
import { ColumnLayout } from '../layout/column-layout';
import { TitleCell } from './title-column/title-cell';

export const TitleColumn = {
	name: 'TitleColumn',
	components: { TitleCell, ColumnLayout, CellLayout },
	props: {
		rights: {
			type: Map,
			required: true,
		},
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
		<ColumnLayout>
			<CellLayout
				v-for="[rightId, accessRightItem] in renderedRights"
				:key="rightId"
				:class="{
					'ui-access-rights-v2-group-children': accessRightItem.group,
				}"
			>
				<TitleCell :right="accessRightItem" />
			</CellLayout>
		</ColumnLayout>
	`,
};
