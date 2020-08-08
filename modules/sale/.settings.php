<?php
return array(
    'controllers' => array(
        'value' => array(
            'defaultNamespace' => '\\Bitrix\\Sale\\Controller',
			'namespaces' => array(
				'\\Bitrix\\Sale\\Exchange\\Integration\\Controller' => 'integration',
			),
            'restIntegration' => [
                'enabled' => true,
            ],
        ),
        'readonly' => true,
    )
);