import type { LinkOptions } from './link-options';
import type { MinimizeOptions } from './minimize-options';
import type { SliderOptions } from './slider-options';

export type RuleOptions = {
	condition: string[] | RegExp[],
	stopParameters?: string[],
	handler?: (event: MouseEvent, link: LinkOptions) => boolean,
	validate?: Function,
	allowCrossDomain?: boolean,
	mobileFriendly?: boolean,
	loader?: string,
	options?: SliderOptions | (link: LinkOptions) => SliderOptions,
	minimizeOptions: (link: LinkOptions) => MinimizeOptions,
};
