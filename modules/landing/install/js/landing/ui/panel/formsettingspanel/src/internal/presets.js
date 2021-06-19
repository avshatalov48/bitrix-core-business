import {Loc} from 'landing.loc';
import {Preset} from 'landing.ui.panel.basepresetpanel';
import siteIcon from '../images/icons/integration.svg';
import widgetAutoShowIcon from '../images/icons/autoshow.svg';
import callbackIcon from '../images/icons/revertcall.svg';
import vkIcon from '../images/icons/vk.svg';
import facebookIcon from '../images/icons/facebook.svg';
import crmFormIcon from '../images/icons/crm.svg';
import serviceIcon from '../images/icons/service.svg';
import product1Icon from '../images/icons/products1.svg';
import product2Icon from '../images/icons/products2.svg';
import product3Icon from '../images/icons/products3.svg';
import product4Icon from '../images/icons/products4.svg';
import smartIcon from '../images/icons/smart.svg';

const presets = [
	new Preset({
		id: 'contacts',
		category: 'crm',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS_DESCRIPTION'),
		icon: siteIcon,
		items: [
			'fields',
			'agreements',
			'crm',
			'embed',
			'other',
		],
		options: {
			templateId: 'contacts',
			name: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS'),
			agreements: {
				use: true,
			},
			data: {
				title: '',
				desc: '',
				buttonCaption: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS_BUTTON'),
				fields: [
					{name: 'LEAD_NAME'},
					{name: 'LEAD_LAST_NAME'},
					{name: 'LEAD_PHONE'},
					{name: 'LEAD_EMAIL'},
				],
				agreements: [{checked: true}],
				dependencies: [],
				recaptcha: {use: false},
			},
			captcha: {key: '', secret: ''},
			result: {
				success: {
					text: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS_SUCCESS_TEXT'),
				},
				failure: {
					text: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS_FAILURE_TEXT'),
				},
			},
			document: {
				scheme: 1,
			},
		},
	}),
	new Preset({
		id: 'feedback',
		category: 'crm',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_DESCRIPTION'),
		icon: widgetAutoShowIcon,
		items: [
			'fields',
			'agreements',
			'crm',
			'embed',
			'other',
		],
		options: {
			templateId: 'feedback',
			name: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK'),
			agreements: {
				use: true,
			},
			data: {
				title: '',
				desc: '',
				buttonCaption: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_BUTTON'),
				fields: [
					{name: 'LEAD_NAME'},
					{name: 'LEAD_PHONE'},
					{name: 'LEAD_EMAIL'},
					{name: 'LEAD_COMMENTS'},
				],
				agreements: [{checked: true}],
				dependencies: [],
				recaptcha: {use: false},
			},
			captcha: {key: '', secret: ''},
			result: {
				success: {
					text: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_SUCCESS_TEXT'),
				},
				failure: {
					text: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_FAILURE_TEXT'),
				},
			},
			document: {
				scheme: 1,
			},
		},
	}),
	new Preset({
		id: 'callback',
		category: 'crm',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK_DESCRIPTION'),
		icon: callbackIcon,
		defaultSection: 'callback',
		items: [
			'fields',
			'agreements',
			'crm',
			'embed',
			'callback',
			'other',
		],
		options: {
			templateId: 'callback',
			name: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK'),
			agreements: {
				use: true,
			},
			data: {
				title: '',
				desc: '',
				buttonCaption: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK_BUTTON'),
				fields: [
					{name: 'LEAD_PHONE'},
				],
				agreements: [{checked: true}],
				dependencies: [],
				recaptcha: {use: false},
			},
			captcha: {key: '', secret: ''},
			result: {
				success: {
					text: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK_SUCCESS_TEXT'),
				},
				failure: {
					text: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK_FAILURE_TEXT'),
				},
			},
			document: {
				scheme: 1,
			},
		},
	}),

	new Preset({
		id: 'expert',
		category: 'crm',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_EXPERT'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_EXPERT_DESCRIPTION'),
		icon: serviceIcon,
		items: [
			'fields',
			'agreements',
			'crm',
			'identify',
			'button_and_header',
			'spam_protection',
			'fields_rules',
			'actions',
			'default_values',
			'analytics',
			'facebook',
			'vk',
			'callback',
			'embed',
			'other',
		],
		options: {
			templateId: 'expert',
			name: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_EXPERT'),
			agreements: {
				use: true,
			},
			result: {
				success: {
					text: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_SUCCESS_TEXT'),
				},
				failure: {
					text: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_FAILURE_TEXT'),
				},
			},
		},
	}),

	new Preset({
		id: 'products1',
		category: 'products',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_1'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_1_DESCRIPTION'),
		icon: product1Icon,
		items: [
			'fields',
			'agreements',
			'crm',
			'embed',
			'other',
		],
		options: {
			templateId: 'products1',
			name: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_1'),
			agreements: {
				use: true,
			},
			data: {
				title: '',
				desc: '',
				fields: [
					{name: 'LEAD_NAME'},
					{name: 'LEAD_PHONE'},
					{name: 'LEAD_EMAIL'},
					{type: 'product', bigPic: false},
				],
				agreements: [{checked: true}],
				dependencies: [],
				recaptcha: {use: false},
			},
			captcha: {key: '', secret: ''},
			document: {
				scheme: 1,
			},
		},
	}),
	new Preset({
		id: 'products2',
		category: 'products',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_2'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_2_DESCRIPTION'),
		icon: product2Icon,
		items: [
			'fields',
			'agreements',
			'crm',
			'embed',
			'other',
		],
		options: {
			templateId: 'products2',
			name: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_2'),
			agreements: {
				use: true,
			},
			data: {
				title: '',
				desc: '',
				fields: [
					{name: 'LEAD_NAME'},
					{name: 'LEAD_PHONE'},
					{name: 'LEAD_EMAIL'},
					{type: 'product', bigPic: false},
				],
				agreements: [{checked: true}],
				dependencies: [],
				recaptcha: {use: false},
			},
			captcha: {key: '', secret: ''},
			document: {
				scheme: 8,
			},
			payment: {use: true},
		},
	}),
	new Preset({
		id: 'products3',
		category: 'products',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_3'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_3_DESCRIPTION'),
		icon: product3Icon,
		items: [
			'fields',
			'agreements',
			'crm',
			'embed',
			'other',
		],
		options: {
			templateId: 'products3',
			name: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_3'),
			agreements: {
				use: true,
			},
			data: {
				title: '',
				desc: '',
				fields: [
					{name: 'LEAD_NAME'},
					{name: 'LEAD_PHONE'},
					{name: 'LEAD_EMAIL'},
					{type: 'product', bigPic: false},
				],
				agreements: [{checked: true}],
				dependencies: [],
				recaptcha: {use: false},
			},
			captcha: {key: '', secret: ''},
			document: {
				scheme: 1,
			},
		},
	}),
	new Preset({
		id: 'products4',
		category: 'products',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_4'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_4_DESCRIPTION'),
		icon: product4Icon,
		items: [
			'fields',
			'agreements',
			'crm',
			'embed',
			'other',
		],
		options: {
			templateId: 'products4',
			name: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_4'),
			agreements: {
				use: true,
			},
			data: {
				title: '',
				desc: '',
				fields: [
					{name: 'LEAD_NAME'},
					{name: 'LEAD_PHONE'},
					{name: 'LEAD_EMAIL'},
					{type: 'product', bigPic: true},
				],
				agreements: [{checked: true}],
				dependencies: [],
				recaptcha: {use: false},
			},
			captcha: {key: '', secret: ''},
			document: {
				scheme: 1,
			},
		},
	}),

	new Preset({
		id: 'vk',
		category: 'social',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_VK'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_VK_DESCRIPTION'),
		icon: vkIcon,
		items: [
			'fields',
			'embed',
			'other',
		],
		disabled: true,
		soon: true,
	}),
	new Preset({
		id: 'facebook',
		category: 'social',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FB'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FB_DESCRIPTION'),
		icon: facebookIcon,
		items: [
			'embed',
			'other',
		],
		disabled: true,
		soon: true,
	}),

	new Preset({
		id: 'personalisation',
		category: 'crm_automation',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PERSONALIZATION'),
		description: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PERSONALIZATION_DESCRIPTION'),
		icon: crmFormIcon,
		items: [
			'embed',
			'other',
		],
		disabled: true,
		soon: true,
	}),
];

export default presets;