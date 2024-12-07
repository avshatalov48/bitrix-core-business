<?php

class CCloudSecurityService_Yandex extends CCloudSecurityService_STS
{
	protected $service_host = 'sts.yandexcloud.net';

	public function GetID()
	{
		return 'yandex_sts';
	}

	public function GetName()
	{
		return 'YA Security Token Service';
	}

	public function GetDefaultBucketControlPolicy($bucket, $prefix)
	{
		return [
			'Statement' => [
				[
					'Effect' => 'Allow',
					'Principal' => '*',
					'Action' => [
						's3:DeleteObject',
						's3:GetObject',
						's3:PutObject',
						's3:PutObjectAcl'
					],
					'Resource' => 'arn:aws:s3:::' . $bucket . '/' . $prefix . '/*',
				],
				[
					'Effect' => 'Allow',
					'Principal' => '*',
					'Action' => [
						's3:ListBucket'
					],
					'Resource' => 'arn:aws:s3:::' . $bucket,
					'Condition' => [
						'StringLike' => [
							's3:prefix' => $prefix . '/*'
						],
					],
				],
			],
		];
	}
}
