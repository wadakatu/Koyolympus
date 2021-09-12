<?php

namespace App\Exceptions\Model;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ModelUpdateFailedException extends Exception
{
    private $model;

    public function __construct(Model $model, string $message = "")
    {
        parent::__construct($message);
        $this->model = $model;
    }

    public function report()
    {
        Log::error('Model update failed.');
        Log::error('Table Name：' . get_class($this->model));
        Log::error('Target Record：' . $this->model);
        Log::error('ーーーーーーーーーーーーーーーーーーーーーーーーーーーー');
    }
}
