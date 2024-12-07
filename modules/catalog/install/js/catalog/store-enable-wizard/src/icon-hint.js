import { BIcon, Set } from 'ui.icon-set.api.vue';
import { Tag } from 'main.core';
import 'ui.hint';

const IconHint = {
	components: {
		BIcon,
	},
	props: {
		title: {
			type: String,
		},
		helpLink: {
			type: String,
		},
	},
	data()
	{
		return {
			timer: null,
		};
	},
	created()
	{
		this.hint = BX.UI.Hint.createInstance({
			popupParameters: {
				maxWidth: 430,
				className: 'inventory-management__popup-hint',
				borderRadius: '10px',
				autoHide: true,
			},
		});
	},
	beforeUnmount()
	{
		this.hint.hide();
	},
	computed: {
		set()
		{
			return Set;
		},
		getContent()
		{
			return Tag.render`
				<div>
					${this.title
					.replace(
						'[link]',
						`<a class="inventory-management__popup-link --hint-link" onclick="if(top.BX.Helper) { top.BX.Helper.show('${this.helpLink}'); event.preventDefault(); }" href="#">`,
					)
					.replace('[/link]', '</a>')}
				</div>		
			`;
		},
	},
	methods: {
		mouseenter(ev)
		{
			this.hint.show(ev.target, this.getContent.outerHTML);
		},
	},
	template: `
		<div
			@mouseenter="mouseenter"
			class="inventory-management__icon-hint"
			ref="hintNode"
			>
			<BIcon :name="set.HELP" :size="23" color="var(--ui-color-base-40)"></BIcon>
		</div>
	`,
};

export {
	IconHint,
};
