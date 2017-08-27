<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Post extends Model {
    protected $table = 'posts';

    public function images() {
        return $this->hasMany('\App\Image');
    }

    public function user() {
        return $this->belongsTo('\App\User');
    }

    public function populateByRequestData($data) {
        $time = new Carbon;
        $time->timestamp = $data['publish_date'];
        $this->text = $data['post']['text'];
        $this->user_id = $data['user_id'];
        //$newPost->publish_date = Carbon::now()->addMinute(1)->toDateTimeString();
        $this->publish_date = $time->toDateTimeString();
        $this->group_id = $data['group_id'];
        $this->save();
        
        if(isset($data['images'])) {
            $this->images()->saveMany($data['images']);
        }
    }
}