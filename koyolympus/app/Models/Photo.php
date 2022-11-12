<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Traits\DateFormat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Photo extends Model
{
    use DateFormat;

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
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!Arr::get($this->attributes, 'id')) {
            $this->setId();
        }
    }

    /**
     * idカラムにUUIDを設定
     *
     * @return void
     */
    public function setId()
    {
        $this->attributes['id'] = $this->getRandomId();
    }

    /**
     * UUID取得
     *
     * @return string
     */
    public function getRandomId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * S3のURLを取得
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('s3')->url($this->attributes['file_path']);
    }

    /**
     * 全ての写真情報を取得（10件ごと）
     *
     * @param string|null $genre
     * @return LengthAwarePaginator
     */
    public function getAllPhoto(?string $genre): LengthAwarePaginator
    {
        $query = Photo::query();

        if (isset($genre)) {
            return $query->where('genre', $genre)->orderBy('created_at', 'desc')->paginate();
        }
        return $query
            ->orderBy('created_at', 'desc')->paginate();
    }

    /**
     * ランダムに全ての写真情報を取得
     *
     * @return Collection
     */
    public function getAllPhotoRandomly(): Collection
    {
        $query = Photo::query();

        return $query->inRandomOrder()->get();
    }

    /**
     * 写真情報を作成
     *
     * @param string $fileName
     * @param string $filePath
     * @param int $genre
     * @return string
     */
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

    /**
     * 写真情報を削除
     *
     * @param string $id
     * @return void
     * @throws \Exception
     */
    public function deletePhotoInfo(string $id)
    {
        self::query()
            ->where('id', $id)
            ->delete();
    }

    /**
     * 作成日の降順に並べて写真情報を全て取得
     *
     * @return Collection<Photo>
     */
    public function getAllPhotoOrderByCreatedAtDesc(): Collection
    {
        return self::query()->orderBy('created_at', 'desc')->get();
    }
}
