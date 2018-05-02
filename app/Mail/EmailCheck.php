<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCheck extends Mailable {
    use Queueable, SerializesModels;
    
    public $token;
    
    public function __construct(string $token) {
        $this->token = $token;
    }
    
    public function build() {
        return $this->view('email.checkEmail')->subject('Проверка почты');
    }
}