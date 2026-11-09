<?php

return [
    'video_disk' => env('VIGILA_VIDEO_DISK', env('FILESYSTEM_DISK', 'local')),
    'aws_region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'rates' => [
        'ec2_hour' => (float) env('VIGILA_RATE_EC2_HOUR', 0.12),
        's3_gb_month' => (float) env('VIGILA_RATE_S3_GB_MONTH', 0.023),
        'db_hour' => (float) env('VIGILA_RATE_DB_HOUR', 0.25),
    ],
    'estimated_ec2_instances' => (int) env('VIGILA_ESTIMATED_EC2_INSTANCES', 2),
];
