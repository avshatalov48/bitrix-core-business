export const TitleBox = {
	props: {
		options: {
			type: Object,
			required: true,
		},
	},
	template: `
		<div v-if="options !== null" class="inventory-management-card-title-box">
			<div v-html="options.title" class="inventory-management-card-title"></div>
			<div
				v-if="options.subTitle"
				v-html="options.subTitle" class="inventory-management-card-subtitle">
			</div>
		</div>
	`,
};
