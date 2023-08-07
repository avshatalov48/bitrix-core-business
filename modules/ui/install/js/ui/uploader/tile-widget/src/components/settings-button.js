import type { BitrixVueComponentProps } from 'ui.vue3';

export const SettingsButton: BitrixVueComponentProps = {
	inject: ['widgetOptions', 'emitter'],
	data: () => ({
		selected: false,
	}),
	methods: {
		handleSettingsClick(): void
		{
			this.emitter.emit(
				'SettingsButton:onClick',
				{
					container: this.$refs['container'],
					button: this,
				}
			);
		},

		getContainer(): HTMLElement
		{
			return this.$refs['container'];
		},

		select(): void
		{
			this.selected = true;
		},

		deselect(): void
		{
			this.selected = false;
		}
	},
	// language=Vue
	template: `
		<div 
			class="ui-tile-uploader-settings" 
			:class="{ '--selected': this.selected }" 
			@click="handleSettingsClick" 
			ref="container"
		></div>
	`
};
