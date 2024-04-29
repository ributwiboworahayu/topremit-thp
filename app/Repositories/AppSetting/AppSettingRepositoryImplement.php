<?php

namespace App\Repositories\AppSetting;

use App\Models\AppSetting;
use Illuminate\Database\Eloquent\Model;
use LaravelEasyRepository\Implementations\Eloquent;

class AppSettingRepositoryImplement extends Eloquent implements AppSettingRepository
{

    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected AppSetting $model;

    public function __construct(AppSetting $model)
    {
        $this->model = $model;
    }

    public function getValueByKey(string $key): string
    {
        return $this->model->where('key', $key)->value('value') ?? '0';
    }
}
