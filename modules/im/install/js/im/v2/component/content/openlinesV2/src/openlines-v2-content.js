import { OpenLinesContent } from 'imopenlines.v2.component.content.openlines';

// @vue/component
export const OpenlinesV2Content = {
	name: 'OpenlinesV2Content',
	components: { OpenLinesContent },
	props:
	{
		entityId: {
			type: String,
			default: '',
		},
	},
	template: `
		<OpenLinesContent
			:dialogId="entityId"
		/>
	`,
};
