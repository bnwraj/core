<?php

namespace Vtlabs\Core\Traits;

use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

trait CoreHasMediaTrait {
    use HasMediaTrait;

    public function registerMediaConversions(Media $media = null)
    {
        if(!isset($this->availableMediaConversions)) return;

        if(in_array("thumb", array_keys($this->availableMediaConversions))) {
            $this->addMediaConversion('thumb')
                ->width($this->availableMediaConversions['thumb']["width"]);
        }

        if(in_array("small", array_keys($this->availableMediaConversions))) {
            $this->addMediaConversion('small')
                ->width($this->availableMediaConversions['small']["width"]);
        }

        if(in_array("medium", array_keys($this->availableMediaConversions))) {
            $this->addMediaConversion('medium')
                ->width($this->availableMediaConversions['medium']["width"]);
        }

        if(in_array("large", array_keys($this->availableMediaConversions))) {
            $this->addMediaConversion('large')
                ->width($this->availableMediaConversions['large']["width"]);
        }
    }

    public function getMediaUrlsAttribute()
    {
        $mediaItems = [];
        foreach($this->media as $mediaItem) {
            $urls = ["default" => $mediaItem->getFullUrl()];
            
            if(isset($this->availableMediaConversions)) {
                $availableMediaConversions = array_keys($this->availableMediaConversions) ?? [];
                
                foreach($availableMediaConversions as $conversion) {
                    $urls[$conversion] = $mediaItem->getFullUrl($conversion);
                }
            }
            
            $mediaItems[$mediaItem->collection_name][] = $urls;
        }

        return !empty($mediaItems) ? $mediaItems : (object)[];
    }
}