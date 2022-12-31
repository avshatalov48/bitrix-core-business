import { Type, Dom } from 'main.core';
import { GroupData } from '@/types/group';
import { Advice } from 'ui.advice';

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

	methods: {
		renderAdvice() {
			Dom.clean(this.$refs.container);

			const advice = new Advice({
				content: this.groupData.adviceTitle,
				avatarImg: this.getAvatar,
				anglePosition: Advice.AnglePosition.BOTTOM,
			});

			advice.renderTo(this.$refs.container);
		},
	},

	mounted() {
		this.renderAdvice();
	},

	updated() {
		this.renderAdvice();
	},

	template: `
		<div ref="container"></div>
	`,
};