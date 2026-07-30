<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = [
        'path',
        'method',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'session_id',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function locationLabel(): string
    {
        if ($this->city && $this->country) {
            return $this->city.', '.$this->country;
        }

        if ($this->city) {
            return $this->city;
        }

        if ($this->country) {
            return $this->country;
        }

        return $this->ip_address ?: '—';
    }

    public function userAgentShort(): string
    {
        $ua = (string) $this->user_agent;

        if ($ua === '') {
            return '—';
        }

        return mb_strlen($ua) > 60 ? mb_substr($ua, 0, 57).'…' : $ua;
    }
}
