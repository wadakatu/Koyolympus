<?php

declare(strict_types=1);

use App\Http\Models\Photo;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class PhotoSeeder extends Seeder
{
    private $number = 500;

    /**
     * @return void
     */
    public function run()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 5184, 3888)->size('2000');
        DB::beginTransaction();
        try {
            for ($i = 1; $i <= $this->number; $i++) {
                $index = (6 < $i % 10 || $i % 10 === 0) ? 1 : $i % 10;
                $fileUrl = config("const.PHOTO.GENRE_FILE_URL.$index");
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
        }
    }
}
