const AI_ASSISTANT_FORM_ID = 'im.ai-assistant.feedback';
const COPILOT_FORM_ID = 'im.copilot.feedback';
const GENERAL_FORM_ID = 'im-v2-feedback';

export const FormContext = {
	aiAssistantBot: 'chat_ai-assistant_one_by_one',
	copilotBot: 'chat_copilot_tab_one_by_one',
	copilotGroup: 'chat_copilot_tab_multi',
	general: 'profile',
};

export const FormConfigAiAssistant = {
	id: AI_ASSISTANT_FORM_ID,
	forms: [
		{ zones: ['es'], id: 838, lang: 'es', sec: 'm82wkx' },
		{ zones: ['en'], id: 834, lang: 'en', sec: 'qnauno' },
		{ zones: ['de'], id: 836, lang: 'de', sec: 'frcsm3' },
		{ zones: ['com.br'], id: 840, lang: 'com.br', sec: 'ufjnte' },
		{ zones: ['ru', 'kz', 'by', 'uz'], id: 2982, lang: 'ru', sec: 'vqmcxn' },
	],
};

export const FormConfigCopilot = {
	id: COPILOT_FORM_ID,
	forms: [
		{ zones: ['es'], id: 684, lang: 'es', sec: 'svvq1x' },
		{ zones: ['en'], id: 686, lang: 'en', sec: 'tjwodz' },
		{ zones: ['de'], id: 688, lang: 'de', sec: 'nrwksg' },
		{ zones: ['com.br'], id: 690, lang: 'com.br', sec: 'kpte6m' },
		{ zones: ['ru', 'by', 'kz'], id: 692, lang: 'ru', sec: 'jbujn0' },
	],
};

export const FormConfigGeneral = {
	id: GENERAL_FORM_ID,
	forms: [
		{ zones: ['ru'], id: 550, sec: '50my2x', lang: 'ru' },
		{ zones: ['en'], id: 560, sec: '621lbr', lang: 'en' },
	],
};
