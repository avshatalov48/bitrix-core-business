import { mapGetters, mapMutations } from 'ui.vue3.vuex';

export const Footer = {
	computed: {
		getButtonClass(): Array
		{
			const classes = [
				'ui-btn',
				'ui-btn-round',
				'ui-btn-no-caps',
				'ui-btn-lg',
				'catalog-warehouse__master-clear--btn',
			];
			if (this.isLoading === true)
			{
				classes.push('ui-btn-wait');
			}

			if (this.isRestrictedAccess === true)
			{
				classes.push('ui-btn-disabled');
			}

			if (this.isUsed === true)
			{
				classes.push('ui-btn-default');
			}
			else
			{
				classes.push('ui-btn-success');
			}

			return classes;
		},
		getHintClass(): Array
		{
			return [
				'ui-link-dashed',
				'catalog-warehouse__master-clear--hint',
			];
		},
		getButtonText(): String
		{
			return this.isUsed
				? this.$Bitrix.Loc.getMessage('CAT_WAREHOUSE_MASTER_NEW_DEACTIVATE_BUTTON')
				: this.$Bitrix.Loc.getMessage('CAT_WAREHOUSE_MASTER_NEW_ACTIVATE_BUTTON');
		},
		...mapGetters([
			'isLoading',
			'isUsed',
			'isRestrictedAccess',
		]),
	},

	methods: {
		openHelpdesk()
		{
			if (top.BX.Helper)
			{
				top.BX.Helper.show('redirect=detail&code=14566618');
			}
		},
		onButtonClick()
		{
			this.$emit('onButtonClick');
		},
		...mapMutations([
			'setIsLoading',
		]),
	},

	// language = Vue
	template: `
	<div class="catalog-warehouse__master-clear--footer">
		<button 
			:class="getButtonClass"
			v-on:click="onButtonClick"
		>{{ getButtonText }}</button>
		<span 
			:class="getHintClass"
			v-on:click="openHelpdesk"
		>
			{{ $Bitrix.Loc.getMessage('CAT_WAREHOUSE_MASTER_NEW_HINT_MORE') }}
		</span>
	</div>
	`,
};
