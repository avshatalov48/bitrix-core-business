import { type Slider } from '../slider';
import { type Label } from '../label';

export type LabelOptions = {
	bgColor?: string | [string, number],
	color?: string,
	text?: string,
	className?: string,
	iconClass?: string,
	iconTitle?: string,
	hidden?: boolean,
	visible?: boolean,
	onclick?: (label: Label, slider: Slider) => void,
};
