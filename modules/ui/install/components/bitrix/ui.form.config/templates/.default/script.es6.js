import { Reflection } from 'main.core';
import { ConfigItem } from './src/config-item.es6';
import { Config } from './src/config.es6';

const namespace = Reflection.namespace('BX.Ui.Form');

namespace.Config = Config;
namespace.ConfigItem = ConfigItem;
