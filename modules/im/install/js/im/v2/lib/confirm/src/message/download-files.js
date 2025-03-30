import { Loc, Dom } from 'main.core';
import { Lottie } from 'ui.lottie';

import { showSingleButtonConfirm } from '../base/base';
import DownloadAllPermissionAnimation from './animations/download-all.json';

import './css/download-files.css';

export const showDownloadAllFilesConfirm = (): Promise<boolean> => {
	const HELPDESK_ARTICLE_CODE = '23597750';
	const articleLink = `BX.Helper?.show('redirect=detail&code=${HELPDESK_ARTICLE_CODE}')`;

	const text = Loc.getMessage('IM_LIB_DOWNLOAD_ALL_FILES_TEXT', {
		'[helpdesk]': `<a onclick="${articleLink}">`,
		'[/helpdesk]': '</a>',
	});

	const subTitle = Dom.create('div', {
		html: text,
		props: { className: 'bx-im-download-all-confirm__subtitle' },
	});

	const animationContainer = Dom.create('div');

	const contentContainer = Dom.create('div', {
		children: [subTitle, animationContainer],
	});

	Lottie.loadAnimation({
		animationData: DownloadAllPermissionAnimation,
		container: animationContainer,
		renderer: 'svg',
		loop: true,
		autoplay: true,
	});

	return showSingleButtonConfirm({
		title: Loc.getMessage('IM_LIB_DOWNLOAD_ALL_FILES_TITLE'),
		text: contentContainer,
		firstButtonCaption: Loc.getMessage('IM_LIB_DOWNLOAD_ALL_FILES_BUTTON'),
	});
};
