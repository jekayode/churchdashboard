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
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'attendance_male' => 0,
        'attendance_female' => 0,
        'attendance_children' => 0,
        'attendance_online' => 0,
        'first_time_guests' => 0,
        'converts' => 0,
        'number_of_cars' => 0,
        'is_multi_service' => false,
        'second_service_attendance_male' => 0,
        'second_service_attendance_female' => 0,
        'second_service_attendance_children' => 0,
        'second_service_attendance_online' => 0,
        'second_service_first_time_guests' => 0,
        'second_service_converts' => 0,
        'second_service_number_of_cars' => 0,
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
     * Mutator for number_of_cars to ensure it's never null.
     */
    public function setNumberOfCarsAttribute($value): void
    {
        $this->attributes['number_of_cars'] = $value ?: 0;
    }

    /**
     * Mutator for attendance_male to ensure it's never null.
     */
    public function setAttendanceMaleAttribute($value): void
    {
        $this->attributes['attendance_male'] = $value ?: 0;
    }

    /**
     * Mutator for attendance_female to ensure it's never null.
     */
    public function setAttendanceFemaleAttribute($value): void
    {
        $this->attributes['attendance_female'] = $value ?: 0;
    }

    /**
     * Mutator for attendance_children to ensure it's never null.
     */
    public function setAttendanceChildrenAttribute($value): void
    {
        $this->attributes['attendance_children'] = $value ?: 0;
    }

    /**
     * Mutator for attendance_online to ensure it's never null.
     */
    public function setAttendanceOnlineAttribute($value): void
    {
        $this->attributes['attendance_online'] = $value ?: 0;
    }

    /**
     * Mutator for first_time_guests to ensure it's never null.
     */
    public function setFirstTimeGuestsAttribute($value): void
    {
        $this->attributes['first_time_guests'] = $value ?: 0;
    }

    /**
     * Mutator for converts to ensure it's never null.
     */
    public function setConvertsAttribute($value): void
    {
        $this->attributes['converts'] = $value ?: 0;
    }

    /**
     * Mutator for second_service_number_of_cars to ensure it's never null.
     */
    public function setSecondServiceNumberOfCarsAttribute($value): void
    {
        $this->attributes['second_service_number_of_cars'] = $value ?: 0;
    }

    /**
     * Mutator for second_service_attendance_male to ensure it's never null.
     */
    public function setSecondServiceAttendanceMaleAttribute($value): void
    {
        $this->attributes['second_service_attendance_male'] = $value ?: 0;
    }

    /**
     * Mutator for second_service_attendance_female to ensure it's never null.
     */
    public function setSecondServiceAttendanceFemaleAttribute($value): void
    {
        $this->attributes['second_service_attendance_female'] = $value ?: 0;
    }

    /**
     * Mutator for second_service_attendance_children to ensure it's never null.
     */
    public function setSecondServiceAttendanceChildrenAttribute($value): void
    {
        $this->attributes['second_service_attendance_children'] = $value ?: 0;
    }

    /**
     * Mutator for second_service_attendance_online to ensure it's never null.
     */
    public function setSecondServiceAttendanceOnlineAttribute($value): void
    {
        $this->attributes['second_service_attendance_online'] = $value ?: 0;
    }

    /**
     * Mutator for second_service_first_time_guests to ensure it's never null.
     */
    public function setSecondServiceFirstTimeGuestsAttribute($value): void
    {
        $this->attributes['second_service_first_time_guests'] = $value ?: 0;
    }

    /**
     * Mutator for second_service_converts to ensure it's never null.
     */
    public function setSecondServiceConvertsAttribute($value): void
    {
        $this->attributes['second_service_converts'] = $value ?: 0;
    }

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
