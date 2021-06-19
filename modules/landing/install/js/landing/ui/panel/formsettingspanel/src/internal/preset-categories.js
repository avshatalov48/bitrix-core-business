import {Loc} from 'landing.loc';
import {PresetCategory} from 'landing.ui.panel.basepresetpanel';

const presetCategories = [
	new PresetCategory({
		id: 'crm',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CATEGORY_CRM'),
	}),
	new PresetCategory({
		id: 'products',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CATEGORY_PRODUCTS_2'),
	}),
	new PresetCategory({
		id: 'social',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CATEGORY_SOCIAL'),
	}),
	new PresetCategory({
		id: 'crm_automation',
		title: Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CATEGORY_CRM_AUTOMATION'),
	}),
];

export default presetCategories;