<?php

namespace App\Repositories\AppSetting;

use LaravelEasyRepository\Repository;

interface AppSettingRepository extends Repository
{

    public function getValueByKey(string $key): string;
}
