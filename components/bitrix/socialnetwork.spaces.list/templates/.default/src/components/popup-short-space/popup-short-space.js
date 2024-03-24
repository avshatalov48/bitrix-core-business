// @vue/component
import { Modes } from '../../const/mode';
import type { SpaceModel } from '../../model/space-model';
import { BasePopup } from '../popup/base-popup';
import { SpaceContent } from '../space/space-content';

const POPUP_ID = 'sn-spaces__short';

export const PopupShortSpace = {
	components: {
		BasePopup,
		SpaceContent,
	},
	emits: ['closeSpacePopup', 'popupSpaceClick'],
	props: {
		bindElement: {
			type: Object,
			required: true,
		},
		context: {
			type: String,
			required: true,
		},
		options: {
			type: Object,
			required: true,
		},
		space: {
			type: Object,
			default: () => {},
		},
		mode: {
			type: String,
			required: true,
		},
		link: {
			type: String,
			required: true,
		},
		isInvitation: {
			type: Boolean,
			default: false,
		},
	},
	data(): Object
	{
		return {
			modes: Modes,
		};
	},
	computed: {
		POPUP_ID(): string {
			return `${POPUP_ID}-${this.context}`;
		},
		config(): Object {
			return {
				className: 'sn-spaces__list-popup',
				width: 293,
				height: this.heightPopup,
				closeIcon: false,
				closeByEsc: true,
				overlay: false,
				padding: 0,
				animation: 'fading-slide',
				offsetLeft: this.options.left,
				offsetTop: -70,
				bindOptions: {
					position: 'bottom',
				},
				bindElement: this.bindElement,
			};
		},
		classModifiers(): string
		{
			const classModifiers = [];

			if (this.isInvitation)
			{
				classModifiers.push('--invitation');
			}

			return classModifiers.join(' ');
		},
		spaceModel(): SpaceModel
		{
			return this.space;
		},
		heightPopup(): number
		{
			return this.isInvitation ? 115 : 70;
		},
	},
	methods: {
		closePopupShortSpace()
		{
			this.$emit('closeSpacePopup');
		},
		onSpaceClick()
		{
			this.$emit('popupSpaceClick');
		},
	},
	template: `
		<BasePopup
			:config="config"
			:id="POPUP_ID"
		>
			<div
				ref="popup-content"
				class="sn-spaces__popup-list_collapsed-mode"
				@click="onSpaceClick"
				@mouseleave="closePopupShortSpace"
			>
				<div 
					class="sn-spaces__popup-list-item"
					:class="classModifiers"
				>
					<SpaceContent 
						:space="space" 
						:mode="mode"
						:is-invitation="isInvitation"
						:showAvatar="false"
					/>
				</div>
			</div>
		</BasePopup>
	`,
};
