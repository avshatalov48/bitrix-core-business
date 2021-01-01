import {Text} from 'main.core';

const defaultOptions = {
	id: Text.getRandom(),
	text: '',
	html: '',
	onClick: () => {},
	attrs: {},
	disabled: false,
	className: null,
};

export default defaultOptions;