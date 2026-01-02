type TypographyAlign = 'left' | 'center' | 'right' | 'justify';
type TypographyTransform = 'uppercase' | 'lowercase' | 'capitalize';
type TypographyWrap = 'truncate' | 'break-words' | 'break-all';

export type DisplayTitleSize = 'xl' | 'lg' | 'md' | 'sm' | 'xs';
export type TextSize = '2xl' | 'xl' | 'lg' | 'md' | 'sm' | 'xs' | '2xs' | '3xs' | '4xs';

export type DisplayTitleOptions = {
	accent?: ?boolean,
	align?: ?TypographyAlign,
	transform?: ?TypographyTransform,
	wrap?: ?TypographyWrap,
	tag?: ?string,
	className?: ?(string | Array<string> | { [key: string]: boolean }),
	size: DisplayTitleSize,
};

export type TextOptions = {
	accent?: ?boolean,
	align?: ?TypographyAlign,
	transform?: ?TypographyTransform,
	wrap?: ?TypographyWrap,
	tag?: ?string,
	className?: ?(string | Array<string> | { [key: string]: boolean }),
	size: TextSize,
};
