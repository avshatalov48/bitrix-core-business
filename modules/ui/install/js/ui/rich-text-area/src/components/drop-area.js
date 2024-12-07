import '../css/drop-area.css';

// @vue/component
export const DropArea = {
	name: 'DropArea',
	props: {
		show: {
			type: Boolean,
			required: true,
		},
	},
	template: `
		<Transition name="ui-rich-text-area-fade">
			<div v-if="show" class="ui-rich-text-area-drop-area">
				<div class="ui-rich-text-area-drop-area-box">
					<label class="ui-rich-text-area-drop-area-text">
						{{ $Bitrix.Loc.getMessage('UI_RICH_TEXT_AREA_DROP_AREA') }}
					</label>
				</div>
			</div>
		</Transition>
	`,
};
