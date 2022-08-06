import {EventEmitter} from 'main.core.events';
import {EventType, AvatarSize} from 'im.v2.const';
import {Avatar} from 'im.v2.component.elements';
import '../css/search.css';

// @vue/component
export const CarouselUser = {
	name: 'CarouselUser',
	components: {Avatar},
	props: {
		user: {
			type: Object,
			required: true
		}
	},
	computed:
	{
		name()
		{
			return this.user.dialog.name.split(' ')[0];
		},
		AvatarSize: () => AvatarSize,
	},
	methods:
	{
		onClick()
		{
			EventEmitter.emit(EventType.dialog.open, {dialogId: this.user.dialogId});
		}
	},
	// language=Vue
	template: `
		<div class="bx-messenger-carousel-item" @click="onClick">
			<Avatar :dialogId="user.dialogId" :size="AvatarSize.L" />
			<div class="bx-messenger-carousel-item-title">{{name}}</div>
		</div>
	`
};