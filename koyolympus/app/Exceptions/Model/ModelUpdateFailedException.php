<?php

declare(strict_types=1);

namespace App\Exceptions\Model;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ModelUpdateFailedException extends Exception
{
    private Model $model;

    public function __construct(Model $model, string $message = '')
    {
        parent::__construct($message);
        $this->model = $model;
    }

    public function report(): void
    {
        Log::error('Model update failed.');
        Log::error('Table Name：' . $this->model->getTable());
        Log::error('Target Record：' . $this->model->getAttribute('id'));
        Log::error('ーーーーーーーーーーーーーーーーーーーーーーーーーーーー');
    }
}
