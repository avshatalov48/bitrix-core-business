import { KeyboardButtonAction } from 'im.v2.const';

export type ActionItem = $Values<typeof KeyboardButtonAction>;

export type ActionEvent = {
	action: ActionItem,
	payload: string,
};

export type CustomCommandEvent = {
	botId: string,
	command: string,
	payload: string,
};
