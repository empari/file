<?php
namespace Empari\Laravel\Files\Domains\Files\Fractal\Transformers;

use League\Fractal\TransformerAbstract;
use Empari\Laravel\Files\Domains\Files\Models\File;

class FileTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @param File $model
     * @return array
     */
    public function transform(File $model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'slug' => $model->slug,
            'original_name' => $model->original_name,
            'extension' => $model->extension,
            'type' => $model->type,
            'mime_type' => $model->mime_type,
            'drive' => $model->drive,
            'description' => $model->description,
            'size' => [
                'total' => (int) $model->size,
                'readable' => $model->readableSize,
            ],
            'url' => $model->url,
            'thumbnail' => $model->thumbnail,
            'tags' => $model->tags,
            'created_at' => $model->created_at->format('Y-m-d H:i:s'),
            'last_modified' => $model->last_modified->format('Y-m-d H:i:s')
        ];
    }
}
