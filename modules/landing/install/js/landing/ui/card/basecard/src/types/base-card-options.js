interface BaseCardOptions
{
	id?: string;
	hidden?: boolean;
	title?: string;
	onClick?: () => {};
	className?: string;
}

export default BaseCardOptions;