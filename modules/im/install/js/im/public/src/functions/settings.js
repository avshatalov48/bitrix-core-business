const SectionNameMap = {
	notify: 'notification',
};

export const prepareSettingsSection = (legacySectionName: string): string => {
	return SectionNameMap[legacySectionName] ?? '';
};
