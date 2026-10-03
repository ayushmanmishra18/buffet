<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Route model binding: on edit the bound model is $this->route('advertisement')
        $isUpdate = $this->route('advertisement') instanceof \App\Models\Advertisement;

        return [
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'link'        => ['nullable', 'string', 'max:500'],
            'position'    => ['required', 'string', 'in:top,after_hero,middle,bottom'],
            'status'      => ['required', 'numeric'],
            'image'       => $isUpdate
                                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
                                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'       => trans('validation.attributes.title'),
            'description' => trans('validation.attributes.description'),
            'link'        => trans('validation.attributes.url'),
            'position'    => 'Position',
            'status'      => trans('validation.attributes.status'),
            'image'       => trans('validation.attributes.image'),
        ];
    }
}
