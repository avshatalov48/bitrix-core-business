export const WarehouseSection = {
	props: {
		title: String,
		description: String,
		iconType: String,
	},

	computed: {
		getIconClass(): String
		{
			const sectionIconClasses = {
				documents: '--docs',
				crm: '--crm',
				mobile: '--mobile',
			};

			return sectionIconClasses[this.$props.iconType] ?? '--docs';
		},
	},

	template: `
		<div class="catalog-warehouse__master-clear__section">
			<div 
				class="catalog-warehouse__master-clear_section_icon"
				:class="getIconClass"
			></div>
			<div class="catalog-warehouse__master-clear_section_inner">
				<div class="catalog-warehouse__master-clear__title">{{title}}</div>
				<div class="catalog-warehouse__master-clear__text">{{description}}</div>
			</div>
		</div>
	`,
};
