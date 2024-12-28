import { Type } from 'main.core';
import { validateStack } from '../helpers/validate-helpers';

// eslint-disable-next-line no-unused-vars
import type { ImageType, StackType } from '../image-stack-steps-options';
import { imageTypeEnum } from '../image-stack-steps-options';

import { StackStatus } from './stack-status';

import { Image } from './types/image';
import { ImageStub } from './types/image-stub';
import { User } from './types/user';
import { UserStub } from './types/user-stub';
import { Icon } from './types/icon';
import { Counter } from './types/counter';

import '../css/stack.css';

export const Stack = {
	name: 'ui-image-stack-steps-step-stack',
	components: {
		StackStatus,
	},
	props: {
		/** @var { StackType } status */
		stack: {
			type: Object,
			required: true,
			validator: (value) => {
				return validateStack(value);
			},
		},
	},
	computed: {
		hasStatus(): boolean
		{
			return Type.isPlainObject(this.stack.status);
		},
	},
	methods: {
		getComponent(image: ImageType): {}
		{
			switch (image.type)
			{
				case imageTypeEnum.IMAGE:
					return Image;
				case imageTypeEnum.USER:
					return User;
				case imageTypeEnum.ICON:
					return Icon;
				case imageTypeEnum.USER_STUB:
					return UserStub;
				case imageTypeEnum.COUNTER:
					return Counter;
				default:
					return ImageStub;
			}
		},
		computeKey(image: ImageType, index: number): string
		{
			let key = 'image-stub';

			// eslint-disable-next-line default-case
			switch (image.type)
			{
				case imageTypeEnum.IMAGE:
					key = image.data.src;
					break;
				case imageTypeEnum.USER:
					key = String(image.data.userId);
					break;
				case imageTypeEnum.ICON:
					key = `${image.data.icon}-${image.data.color}`;
					break;
				case imageTypeEnum.USER_STUB:
					key = 'user-stub';
					break;
				case imageTypeEnum.COUNTER:
					key = 'counter';
					break;
			}

			return `${key}-${index}`;
		},
	},
	template: `
		<div class="ui-image-stack-steps-step-stack">
			<StackStatus v-if="hasStatus" :status="stack.status"/>
			<template v-for="(image, index) in stack.images" :key="computeKey(image, index)">
				<component :is="getComponent(image)" v-bind="image.data"/>
			</template>
		</div>
	`,
};
