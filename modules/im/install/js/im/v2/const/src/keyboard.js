import { BotCommand } from 'im.v2.const';

export type RawKeyboardButtonConfig = {
	TEXT: string,
	TYPE?: $Values<typeof KeyboardButtonType>,
	CONTEXT?: $Values<typeof KeyboardButtonContext>,
	LINK?: string,
	COMMAND?: $Values<typeof BotCommand>,
	COMMAND_PARAMS: string,
	DISPLAY: $Values<typeof KeyboardButtonDisplay>,
	WIDTH: number,
	BG_COLOR: string,
	TEXT_COLOR: string,
	BLOCK: 'Y' | 'N',
	DISABLED: 'Y' | 'N',
	VOTE: 'Y' | 'N',
	WAIT: 'Y' | 'N',
	APP_ID: string,
	APP_PARAMS: string,
	BOT_ID: number,
	ACTION: $Values<typeof KeyboardButtonAction>,
	ACTION_VALUE: string,
};

export type KeyboardButtonConfig = {
	text: string,
	type?: $Values<typeof KeyboardButtonType>,
	context?: $Values<typeof KeyboardButtonContext>,
	link?: string,
	command?: $Values<typeof BotCommand>,
	commandParams?: string, // FOO|BAR
	display: $Values<typeof KeyboardButtonDisplay>,
	width: number,
	bgColor?: string,
	textColor?: string,
	block?: boolean,
	disabled?: boolean,
	vote?: boolean,
	wait?: boolean,
	appId: string,
	appParams: string, // FOO|BAR
	botId: number,
	action: $Values<typeof KeyboardButtonAction>,
	actionValue: string, // PUT - text, SEND - text, COPY - text, CALL - number, DIALOG - dialogId
};

export const KeyboardButtonType = {
	button: 'BUTTON',
	newLine: 'NEWLINE',
};

export const KeyboardButtonContext = {
	all: 'ALL',
	mobile: 'MOBILE',
	desktop: 'DESKTOP',
};

export const KeyboardButtonDisplay = {
	block: 'BLOCK',
	line: 'LINE',
};

export const KeyboardButtonAction = {
	put: 'PUT',
	send: 'SEND',
	copy: 'COPY',
	call: 'CALL',
	dialog: 'DIALOG',
};
