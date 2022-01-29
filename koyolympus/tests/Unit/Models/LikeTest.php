<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Http\Models\Like;

class LikeTest extends TestCase
{
    private $like;

    protected function setUp(): void
    {
        parent::setUp();

        $this->like = new Like();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
