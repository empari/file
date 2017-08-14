<?php
namespace Empari\Laravel\Files\Units\Files\Http\Requests;

use Empari\Support\Http\Requests\FormRequest;

class FileImageUploadRequest extends FormRequest
{
    public function rules()
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'description' => 'max:255',
            'tags' => 'array',
        ];
    }
}