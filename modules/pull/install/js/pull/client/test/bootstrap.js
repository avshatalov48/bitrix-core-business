// required by the bitrix bootstrapping to load phrases
import { Loc } from 'main.core';

// workaround to place protobuf to global instead of window
import protobuf from 'pull.protobuf';
global.protobuf = protobuf;
