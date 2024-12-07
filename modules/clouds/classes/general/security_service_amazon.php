<?php

class CCloudSecurityService_Amazon extends CCloudSecurityService_STS
{
	protected $service_host = 'sts.amazonaws.com';

	public function GetID()
	{
		return 'amazon_sts';
	}

	public function GetName()
	{
		return 'AWS Security Token Service';
	}

	public function GetDefaultBucketControlPolicy($bucket, $prefix)
	{
		return [
			'Statement' => [
				[
					'Effect' => 'Allow',
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
