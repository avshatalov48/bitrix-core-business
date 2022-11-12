import { Type } from 'main.core';
import { GroupData } from '@/types/group';

import '../css/item-list-advice.css';

export const ItemListAdvice = {
	name: 'ui-entity-catalog-item-list-advice',
	props: {
		groupData: {
			type: GroupData,
			required: true,
		},
	},
	computed: {
		getAvatar: function(): string
		{
			return (
				Type.isStringFilled(this.groupData.adviceAvatar)
					? this.groupData.adviceAvatar
					: '/bitrix/js/ui/entity-catalog/images/ui-entity-catalog--nata.jpg'
			);
		},
	},
	template: `
		<div class="ui-entity-catalog__advice-box" v-if="groupData.adviceTitle">
			<div class="ui-entity-catalog__advice-avatar">
				<span class="ui-entity-catalog__avatar ui-icon ui-icon-common-user">
					<i :style="{ backgroundImage: 'url(' + getAvatar + ')'}"></i>
				</span>
			</div>
			<div class="ui-entity-catalog__advice-text">
				<div class="ui-entity-catalog__advice-text-title">
					{{ groupData.adviceTitle }}
				</div>
			</div>
		</div>
	`,
};