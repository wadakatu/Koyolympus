<?php

declare(strict_types=1);

use App\Http\Models\Photo;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class PhotoSeeder extends Seeder
{
    private $number = 300;

    /**
     * @return void
     * @throws Exception
     */
    public function run()
    {
        $this->command->getOutput()->progressStart($this->number);
        for ($i = 1; $i <= $this->number; $i++) {
            $index = (6 < $i % 10 || $i % 10 === 0) ? 1 : $i % 10;
            $fileUrl = config("const.PHOTO.GENRE_FILE_URL.$index");

            $photo = factory(Photo::class)->make();
            $fileName = $photo->id . "-test.jpeg";
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
            }finally{
                $this->command->getOutput()->progressAdvance();
            }
        }
        $this->command->getOutput()->progressFinish();
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
