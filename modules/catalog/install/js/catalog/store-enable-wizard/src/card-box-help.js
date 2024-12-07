import { BIcon, Set } from 'ui.icon-set.api.vue';

const CardBoxHelp = {
	components: {
		BIcon,
	},
	props: {
		title: {
			type: String,
		},
		link: {
			type: String,
			required: false,
		},
	},
	computed: {
		set()
		{
			return Set;
		},
	},
	methods: {
		onClick()
		{
			if (top.BX && top.BX.Helper)
			{
				top.BX.Helper.show(this.link || 'redirect=detail&code=20233688');
			}
		},
	},
	template: `
		<div
			@click.stop="onClick"
			class="inventory-management__card-help">
			<BIcon :name="set.HELP" :size="23" color="var(--ui-color-base-40)"></BIcon>
			<div class="inventory-management__card-help-text">
				{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DETAILS')}}
			</div>
		</div>
	`,
};

export {
	CardBoxHelp,
};
