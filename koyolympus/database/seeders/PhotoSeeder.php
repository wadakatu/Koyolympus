<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Photo;
use DB;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Storage;
use Throwable;

class PhotoSeeder extends Seeder
{
    private $number = 300;

    /**
     * @return void
     *
     * @throws Exception
     */
    public function run()
    {
        $this->command->getOutput()->progressStart($this->number);
        for ($i = 1; $i <= $this->number; $i++) {
            $genre   = $this->getPhotoGenre($i);
            $fileUrl = config("const.PHOTO.GENRE_FILE_URL.$genre");

            $photo            = Photo::factory()->make(['genre' => $genre]);
            $fileName         = $photo->id . '-test.jpeg';
            $photo->file_path = $fileUrl . '/' . $fileName;

            DB::beginTransaction();
            try {
                $photo->save();
                DB::commit();
                $this->putPhotoToS3($fileUrl, $fileName);
            } catch (Throwable $e) {
                DB::rollBack();
                $this->deletePhotoFromS3($fileUrl, $fileName);

                continue;
            } finally {
                $this->command->getOutput()->progressAdvance();
            }
        }
        $this->command->getOutput()->progressFinish();
    }

    private function getPhotoGenre(int $index): int
    {
        $remainder = $index % 10;

        switch ($remainder) {
            case $remainder < 7:
                $genre = $remainder;
                break;
            default:
                $genre = 1;
                break;
        }

        return $genre;
    }

    private function putPhotoToS3(string $fileUrl, string $fileName)
    {
        $file = new UploadedFile(public_path('/images/P8182102.jpeg'), $fileName);
        Storage::disk('s3')->putFileAs($fileUrl, $file, $fileName, 'public');
    }

    private function deletePhotoFromS3(string $fileUrl, string $fileName)
    {
        Storage::disk('s3')->delete($fileUrl . '/' . $fileName);
    }
}
