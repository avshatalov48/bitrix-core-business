import { Loc } from 'main.core';

export const locMixin = {
	methods: {
		loc(name: string, replacements?: { [key: string]: string }): string
		{
			return Loc.getMessage(name, replacements);
		},
	},
};
