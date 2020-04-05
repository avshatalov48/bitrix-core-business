import type { BaseButtonOptions } from '../base-button-options';
import SplitSubButtonType from './split-sub-button-type';

export type SplitSubButtonOptions = Exclude<BaseButtonOptions, 'baseClass' | 'text' > & {
	buttonType?: SplitSubButtonType
};