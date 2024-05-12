import { UpdateFeatures } from '../../../base/src/const/features';

const onOpenTariffSettings = () => BX.SidePanel.Instance.open(`${window.location.origin}/settings/license_all.php`);
const onHelpClick = (ARTICLE_CODE: string) => BX.Helper.show(`redirect=detail&code=${ARTICLE_CODE}`);

export const metaData = {
	[UpdateFeatures.tariff]: {
		title: 'default title',
		description: 'default description',
		detailButton: {
			text: 'button text',
			callback: onOpenTariffSettings,
		},
		infoButton: {
			text: 'button text',
			callback: () => onHelpClick('12925062'),
		},
	},
};
