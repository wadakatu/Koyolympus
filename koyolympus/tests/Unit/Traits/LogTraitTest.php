<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use App\Traits\LogTrait;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LogTraitTest extends TestCase
{
    private $class;

    private $title = 'title';

    private $message = 'message';

    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new class
        {
            use LogTrait;
        };
    }

    /**
     * @test
     */
    public function outputLog()
    {
        Log::shouldReceive('info')->once()->with("$this->title $this->message");

        $this->class->outputLog($this->title, $this->message);
    }

    /**
     * @test
     */
    public function outputErrorLog()
    {
        Log::shouldReceive('error')->once()->with("$this->title $this->message");

        $this->class->outputErrorLog($this->title, $this->message);
    }

    /**
     * @test
     */
    public function outputThrowableLog()
    {
        Log::shouldReceive('error')->once()->with("$this->title 例外/エラー発生");
        Log::shouldReceive('error')->once()->with("$this->title $this->message");

        $this->class->outputThrowableLog($this->title, $this->message);
    }
}
