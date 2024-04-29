<?php

namespace App\Traits;

trait ServiceResponser
{
    /**
     * @param mixed $data
     * @return array
     */
    protected function finalResultSuccess(mixed $data = []): array
    {
        return ['status' => true, 'data' => $data, 'message' => 'success'];
    }

    /**
     * @param mixed $dataFail
     * @param string $message
     * @return array
     */
    protected function finalResultFail(mixed $dataFail = [], string $message = ""): array
    {
        return ['status' => false, 'data' => $dataFail, 'message' => $message];
    }
}
