<?php
declare(strict_types=1);

namespace App\Http\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Photo extends Model
{
    protected $guarded = [];
    protected $appends = [
        'url',
    ];
    protected $visible = [
        'id',
        'genre',
        'url',
    ];
    protected $perPage = 10;
    protected $keyType = 'string';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!Arr::get($this->attributes, 'id')) {
            $this->setId();
        }
    }

    public function setId()
    {
        $this->attributes['id'] = $this->getRandomId();
    }

    public function getRandomId(): string
    {
        return Str::uuid()->toString();
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('s3')->url($this->attributes['file_path']);
    }

    public function getAllPhoto(?int $genre): LengthAwarePaginator
    {
        $query = Photo::query();

        if (isset($genre)) {
            return $query->where('genre', $genre)->orderBy('created_at', 'desc')->paginate();
        }
        return $query
            ->orderBy('created_at', 'desc')->paginate();
    }

    public function getAllPhotoRandomly(): Collection
    {
        $query = Photo::query();

        return $query->inRandomOrder()->get();
    }

    public function createPhotoInfo(string $fileName, string $filePath, int $genre): string
    {
        $photo = new Photo();

        $uniqueFileName = $photo->id . '.' . $fileName;

        $uniqueFilePath = $filePath . '/' . $uniqueFileName;

        $photo->fill([
            'file_name' => $uniqueFileName,
            'file_path' => $uniqueFilePath,
            'genre' => $genre,
        ]);

        $photo->save();

        return $uniqueFileName;
    }

    public function deletePhotoInfo(string $fileName)
    {
        $fileIdAndName = explode('.', $fileName);

        self::query()
            ->where('id', $fileIdAndName[0])
            ->delete();
    }

    public function getAllPhotoOrderByCreatedAtDesc(): Collection
    {
        return self::query()->orderBy('created_at', 'desc')->get();
    }
}
