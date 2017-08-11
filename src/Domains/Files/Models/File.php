<?php
namespace Empari\Laravel\Files\Domains\Files\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Empari\Support\Database\Traits\UuidsTrait;
use Spatie\Tags\HasTags;

/**
 * Class File
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $original_name
 * @property string $baseName
 * @property string $extension
 * @property string $type
 * @property string $mime_type
 * @property string $drive
 * @property string $description
 * @property int    $size
 * @property string $url
 * @property string $thumbnail
 * @property \Carbon\Carbon $last_modified
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property \Illuminate\Database\Eloquent\Collection $tags
 */
class File extends Model
{
    use UuidsTrait,
        SoftDeletes,
        HasTags,
        Sluggable,
        SluggableScopeHelpers;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'original_name',
        'extension',
        'type',
        'mime_type',
        'drive',
        'description',
        'size',
        'last_modified'
    ];

    protected $dates = [
        'last_modified', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'baseName'
            ]
        ];
    }

    /**
     * Return a URL
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return config('app.cdn')  . "/{$this->type}/{$this->id}.{$this->extension}";
    }

    public function getThumbnailAttribute()
    {
        if ($this->isImage()) {
            return config('app.cdn')  . "/{$this->type}/thumbnails/{$this->id}.{$this->extension}";
        } else {
            return null;
        }
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($file) {
            $file->name = $file->baseName;
            $file->extension = strtolower($file->extension);
            $file->mime_type = strtolower($file->mime_type);
            $file->type = $file->isImage() ? 'image' : strtolower($file->type);
        });
    }

    /**
     * File is image?
     *
     * @return bool
     */
    protected function isImage() : Bool
    {
        return strpos(strtolower($this->mime_type), 'image') !== FALSE;
    }

    /**
     * Get only name of the file
     *
     * @return string
     */
    protected function getBaseNameAttribute() : string
    {
        return basename($this->original_name, '.'. $this->extension);
    }

    /**
     * Size For Humans
     *
     * @return string
     */
    protected function getReadableSizeAttribute() : string
    {
        return $this->convertToReadableSize($this->size);
    }

    /**
     * Get Readable Size of the file
     *
     * @param $size
     * @return string
     */
    function convertToReadableSize($size) : string
    {
        $base = log($size) / log(1024);
        $suffix = array("", "KB", "MB", "GB", "TB");
        $f_base = floor($base);
        return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
    }

    /**
     * Save file into the UploadFile
     *
     * @param UploadedFile $file
     * @param array $data
     * @return File
     */
    public static function store(UploadedFile $file, Array $data = []) : File
    {
        //Save to Database
        $savedFile = Self::create([
            'name' => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'type' => $file->getType(),
            'mime_type' => $file->getMimeType(),
            'drive' => 's3',
            'size' => $file->getSize(),
            'last_modified' => date("Y-m-d H:i:s"),
            'description' => array_key_exists('description', $data) ? $data['description'] : null,
        ]);

        //Save Tags
        if (array_key_exists('tags', $data)) {
            $savedFile->attachTags($data['tags']);
        }

        // Save to S3
        $fileName = $savedFile->id . '.' . $savedFile->extension;
        $fileSystem = Storage::disk('s3');

        //Save Image
        if ($savedFile->isImage()) {
            $image_normal = Image::make($file)->widen(800, function ($constraint) {
                $constraint->upsize();
            });

            // prevent possible upsizing
            $image_thumb = Image::make($file)->resize(null, 100, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            //$image_thumb  = Image::make($file)->resize(100,100);
            $image_normal = $image_normal->stream();
            $image_thumb  = $image_thumb->stream();

            $filePath = '/'. $savedFile->type .'/' . $fileName;
            $fileSystem->put($filePath, $image_normal->__toString(), 'public');

            $filePath = '/'. $savedFile->type .'/thumbnails/' . $fileName;
            $fileSystem->put($filePath, $image_thumb->__toString(), 'public');
        } else {
            //Save any file
            $filePath = '/'. $savedFile->type .'/' . $fileName;
            $fileSystem->put($filePath, file_get_contents($file), 'public');
        }

        return $savedFile;
    }
}