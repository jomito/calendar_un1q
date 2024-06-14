<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use App\Domain\Enums\Frequency;

class Event extends Model
{
    protected $fillable = ['title', 'description', 'start', 'end', 'recurring_pattern'];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'recurring_pattern' => 'array'
    ];

    public function validate()
    {
        if ($this->start >= $this->end) {
            throw new \InvalidArgumentException("Start date must be before end date");
        }
        if ($this->recurring_pattern) {
            if (!in_array($this->recurring_pattern['frequency'], Frequency::values())) {
                throw new \InvalidArgumentException("Invalid recurring frequency");
            }
            if (new DateTime($this->recurring_pattern['repeat_until']) <= $this->start) {
                throw new \InvalidArgumentException("Repeat until date must be after start date");
            }
        }
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start' => $this->start->format(DateTime::ISO8601),
            'end' => $this->end->format(DateTime::ISO8601),
            'recurring_pattern' => $this->recurring_pattern
        ];
    }
}
