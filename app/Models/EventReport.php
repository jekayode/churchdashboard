<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EventReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'reported_by',
        'attendance_male',
        'attendance_female',
        'attendance_children',
        'attendance_online',
        'first_time_guests',
        'converts',
        'start_time',
        'end_time',
        'number_of_cars',
        'notes',
        'report_date',
        'is_multi_service',
        'second_service_attendance_male',
        'second_service_attendance_female',
        'second_service_attendance_children',
        'second_service_attendance_online',
        'second_service_first_time_guests',
        'second_service_converts',
        'second_service_number_of_cars',
        'second_service_start_time',
        'second_service_end_time',
        'event_type',
        'service_type',
        'second_service_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'report_date' => 'date',
        'is_multi_service' => 'boolean',
        'second_service_start_time' => 'datetime',
        'second_service_end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'attendance_male' => 'integer',
        'attendance_female' => 'integer', 
        'attendance_children' => 'integer',
        'attendance_online' => 'integer',
        'first_time_guests' => 'integer',
        'converts' => 'integer',
        'number_of_cars' => 'integer',
        'second_service_attendance_male' => 'integer',
        'second_service_attendance_female' => 'integer',
        'second_service_attendance_children' => 'integer',
        'second_service_attendance_online' => 'integer',
        'second_service_first_time_guests' => 'integer',
        'second_service_converts' => 'integer',
        'second_service_number_of_cars' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'total_attendance',
        'adult_attendance',
        'second_service_total_attendance',
        'combined_total_attendance',
        'combined_first_time_guests',
        'combined_converts',
        'combined_cars',
        'combined_totals_by_gender',
    ];

    /**
     * Get the event this report belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who reported.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get total attendance.
     */
    public function getTotalAttendanceAttribute(): int
    {
        return ($this->attendance_male ?? 0) + 
               ($this->attendance_female ?? 0) + 
               ($this->attendance_children ?? 0) + 
               ($this->attendance_online ?? 0);
    }

    /**
     * Get adult attendance.
     */
    public function getAdultAttendanceAttribute(): int
    {
        return ($this->attendance_male ?? 0) + ($this->attendance_female ?? 0);
    }

    /**
     * Get second service total attendance.
     */
    public function getSecondServiceTotalAttendanceAttribute(): int
    {
        if (!$this->is_multi_service) {
            return 0;
        }
        
        return ($this->second_service_attendance_male ?? 0) + 
               ($this->second_service_attendance_female ?? 0) + 
               ($this->second_service_attendance_children ?? 0) +
               ($this->second_service_attendance_online ?? 0);
    }

    /**
     * Get combined total attendance (both services).
     */
    public function getCombinedTotalAttendanceAttribute(): int
    {
        return $this->total_attendance + $this->second_service_total_attendance;
    }

    /**
     * Get combined first time guests (both services).
     */
    public function getCombinedFirstTimeGuestsAttribute(): int
    {
        return ($this->first_time_guests ?? 0) + ($this->is_multi_service ? ($this->second_service_first_time_guests ?? 0) : 0);
    }

    /**
     * Get combined converts (both services).
     */
    public function getCombinedConvertsAttribute(): int
    {
        return ($this->converts ?? 0) + ($this->is_multi_service ? ($this->second_service_converts ?? 0) : 0);
    }

    /**
     * Get combined cars (both services).
     */
    public function getCombinedCarsAttribute(): int
    {
        return ($this->number_of_cars ?? 0) + ($this->is_multi_service ? ($this->second_service_number_of_cars ?? 0) : 0);
    }

    /**
     * Get combined totals by gender.
     */
    public function getCombinedTotalsByGenderAttribute(): array
    {
        return [
            'male' => ($this->attendance_male ?? 0) + ($this->is_multi_service ? ($this->second_service_attendance_male ?? 0) : 0),
            'female' => ($this->attendance_female ?? 0) + ($this->is_multi_service ? ($this->second_service_attendance_female ?? 0) : 0),
            'children' => ($this->attendance_children ?? 0) + ($this->is_multi_service ? ($this->second_service_attendance_children ?? 0) : 0),
            'online' => ($this->attendance_online ?? 0) + ($this->is_multi_service ? ($this->second_service_attendance_online ?? 0) : 0),
        ];
    }

    /**
     * Event types enum values.
     */
    public const EVENT_TYPES = [
        'Sunday Service',
        'Mid-Week Service',
        'Conferences',
        'Outreach',
        'Evangelism (Beautiful Feet)',
        'Water Baptism',
        'TECi',
        'Membership Class',
        'LifeGroup Meeting',
        'Prayer Meeting',
        'Youth Service',
        'Women Ministry',
        'Men Ministry',
        'Children Service',
        'Leadership Meeting',
        'Community Outreach',
        'Baby Dedication',
        'Holy Ghost Baptism',
        'Other'
    ];
}
