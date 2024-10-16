<?php

declare(strict_types=1);

namespace EMS\Release;

class Config
{
    public static array $applications = [
        'elasticms-admin' => 'elasticms/elasticms-admin',
        'elasticms-web' => 'elasticms/elasticms-web',
        'elasticms-cli' => 'elasticms/elasticms-cli',
    ];

    public static array $docker = [
        'elasticms-admin-docker' => 'elasticms/elasticms-admin-docker',
        'elasticms-web-docker' => 'elasticms/elasticms-web-docker',
        'elasticms-cli-docker' => 'elasticms/elasticms-cli-docker',
    ];

    public static array $packages = [
        'EMSClientHelperBundle' => 'elasticms/client-helper-bundle',
        'EMSCommonBundle' => 'elasticms/common-bundle',
        'EMSCoreBundle' => 'elasticms/core-bundle',
        'EMSFormBundle' => 'elasticms/form-bundle',
        'EMSSubmissionBundle' => 'elasticms/submission-bundle',
        'helpers' => 'elasticms/helpers',
        'xliff' => 'elasticms/xliff'
    ];
}