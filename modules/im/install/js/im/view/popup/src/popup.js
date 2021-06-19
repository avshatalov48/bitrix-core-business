import { Chat } from "./type/chat";
import { User } from "./type/user";
import { Users } from "./type/users";

export const Popup = {
	props: ['type', 'value', 'popupInstance'],
	components:
	{
		Chat,
		User,
		Users,
	},
	//language=Vue
	template: `
		<component :is="type" :value="value" :popupInstance="popupInstance"/>
	`
};