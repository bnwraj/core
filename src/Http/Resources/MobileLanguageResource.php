<?php

namespace Vtlabs\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MobileLanguageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'language_code' => $this->language_code,
            'language_title' => $this->language_title,
            'content' => empty($request->skip_content) ? $this->content : null,
            'type' => $this->type
        ];
    }
}