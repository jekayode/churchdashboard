<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'type',
        'service_type',
        'day_of_week',
        'service_time',
        'service_end_time',
        'service_name',
        'has_multiple_services',
        'second_service_time',
        'second_service_end_time',
        'second_service_name',
        'venue',
        'address',
        'location',
        'start_date',
        'end_date',
        'max_capacity',
        'frequency',
        'parent_event_id',
        'is_recurring',
        'is_recurring_instance',
        'recurrence_rules',
        'recurrence_end_date',
        'max_occurrences',
        'registration_type',
        'registration_link',
        'custom_form_fields',
        'status',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'service_time' => 'datetime:H:i',
        'service_end_time' => 'datetime:H:i',
        'second_service_time' => 'datetime:H:i',
        'second_service_end_time' => 'datetime:H:i',
        'day_of_week' => 'integer',
        'max_capacity' => 'integer',
        'has_multiple_services' => 'boolean',
        'recurrence_rules' => 'array',
        'recurrence_end_date' => 'date',
        'max_occurrences' => 'integer',
        'is_recurring' => 'boolean',
        'is_recurring_instance' => 'boolean',
        'custom_form_fields' => 'array',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'start_date_time',
        'end_date_time', 
        'type',
        'total_registrations',
        'registrations_count',
        'checked_in_count',
    ];

    /**
     * Get the branch this event belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the registrations for this event.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get the reports for this event.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(EventReport::class);
    }

    /**
     * Get the parent event (for recurring instances).
     */
    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'parent_event_id');
    }

    /**
     * Get the recurring instances of this event.
     */
    public function recurringInstances(): HasMany
    {
        return $this->hasMany(Event::class, 'parent_event_id');
    }

    /**
     * Scope to get published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope to get events by branch.
     */
    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get recurring events only.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope to get recurring instances only.
     */
    public function scopeRecurringInstances($query)
    {
        return $query->where('is_recurring_instance', true);
    }

    /**
     * Scope to get main events (not recurring instances).
     */
    public function scopeMainEvents($query)
    {
        return $query->where('is_recurring_instance', false);
    }

    /**
     * Scope to get events by service type.
     */
    public function scopeByServiceType($query, string $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    /**
     * Scope to get Sunday services.
     */
    public function scopeSundayServices($query)
    {
        return $query->where('service_type', 'Sunday Service');
    }

    /**
     * Scope to get midweek services.
     */
    public function scopeMidweekServices($query)
    {
        return $query->where('service_type', 'MidWeek');
    }

    /**
     * Scope to get church services (all service types).
     */
    public function scopeChurchServices($query)
    {
        return $query->where('type', 'service');
    }

    /**
     * Scope to get regular events (non-service events).
     */
    public function scopeRegularEvents($query)
    {
        return $query->where('type', '!=', 'service');
    }

    /**
     * Scope to get events by day of week.
     */
    public function scopeByDayOfWeek($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Check if the event is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_date > now();
    }

    /**
     * Get total registrations count.
     */
    public function getTotalRegistrationsAttribute(): int
    {
        return $this->registrations()->count();
    }

    /**
     * Get registrations count (alias for frontend compatibility).
     */
    public function getRegistrationsCountAttribute(): int
    {
        return $this->getTotalRegistrationsAttribute();
    }

    /**
     * Get checked-in registrations count.
     */
    public function getCheckedInCountAttribute(): int
    {
        return $this->registrations()->where('checked_in', true)->count();
    }

    /**
     * Get start_date_time accessor for frontend compatibility.
     */
    public function getStartDateTimeAttribute(): ?string
    {
        return $this->start_date ? $this->start_date->toISOString() : null;
    }

    /**
     * Get end_date_time accessor for frontend compatibility.
     */
    public function getEndDateTimeAttribute(): ?string
    {
        return $this->end_date ? $this->end_date->toISOString() : null;
    }

    /**
     * Get type accessor.
     */
    public function getTypeAttribute(): string
    {
        return $this->attributes['type'] ?? 'other';
    }

    /**
     * Check if this is a church service.
     */
    public function isChurchService(): bool
    {
        return $this->type === 'service';
    }

    /**
     * Check if this is a Sunday service.
     */
    public function isSundayService(): bool
    {
        return $this->service_type === 'Sunday Service';
    }

    /**
     * Check if this is a midweek service.
     */
    public function isMidweekService(): bool
    {
        return $this->service_type === 'MidWeek';
    }

    /**
     * Get the day name for this event.
     */
    public function getDayNameAttribute(): ?string
    {
        if ($this->day_of_week === null) {
            return null;
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$this->day_of_week] ?? null;
    }

    /**
     * Get formatted service time.
     */
    public function getFormattedServiceTimeAttribute(): ?string
    {
        if (!$this->service_time) {
            return null;
        }

        return $this->service_time instanceof \Carbon\Carbon 
            ? $this->service_time->format('g:i A')
            : date('g:i A', strtotime($this->service_time));
    }

    /**
     * Generate recurring instances for this event.
     */
    public function generateRecurringInstances(int $weeksAhead = 12): array
    {
        if (!$this->is_recurring || $this->day_of_week === null) {
            return [];
        }

        $instances = [];
        $startDate = now()->startOfWeek(); // Start from beginning of current week
        
        for ($i = 0; $i < $weeksAhead; $i++) {
            $instanceDate = $startDate->copy()->addWeeks($i)->addDays($this->day_of_week);
            
            // Combine date with service time
            if ($this->service_time) {
                $serviceTime = $this->service_time instanceof \Carbon\Carbon 
                    ? $this->service_time 
                    : \Carbon\Carbon::createFromFormat('H:i:s', $this->service_time);
                
                $instanceDateTime = $instanceDate->copy()
                    ->setHour($serviceTime->hour)
                    ->setMinute($serviceTime->minute)
                    ->setSecond(0);
            } else {
                $instanceDateTime = $instanceDate->copy()->setTime(10, 0, 0); // Default to 10 AM
            }

            // Skip if in the past
            if ($instanceDateTime->isPast()) {
                continue;
            }

            // Check if we've reached the end date
            if ($this->recurrence_end_date && $instanceDateTime->toDateString() > $this->recurrence_end_date) {
                break;
            }

            $instances[] = [
                'branch_id' => $this->branch_id,
                'parent_event_id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
                'service_type' => $this->service_type,
                'day_of_week' => $this->day_of_week,
                'service_time' => $this->service_time,
                'service_name' => $this->service_name,
                'location' => $this->location,
                'start_date' => $instanceDateTime,
                'end_date' => $instanceDateTime->copy()->addHour(), // Default 1 hour duration
                'max_capacity' => $this->max_capacity,
                'frequency' => 'once', // Instances are not recurring themselves
                'is_recurring' => false,
                'is_recurring_instance' => true,
                'registration_type' => $this->registration_type,
                'registration_link' => $this->registration_link,
                'custom_form_fields' => $this->custom_form_fields,
                'status' => $this->status,
                'is_public' => $this->is_public,
            ];
        }

        return $instances;
    }

    /**
     * Create recurring instances in the database.
     */
    public function createRecurringInstances(int $weeksAhead = 12): int
    {
        $instances = $this->generateRecurringInstances($weeksAhead);
        $created = 0;

        foreach ($instances as $instanceData) {
            // Check if instance already exists
            $exists = Event::where('parent_event_id', $this->id)
                ->where('start_date', $instanceData['start_date'])
                ->exists();

            if (!$exists) {
                Event::create($instanceData);
                $created++;
            }
        }

        return $created;
    }

    /**
     * Check if this service has multiple sessions.
     */
    public function hasMultipleSessions(): bool
    {
        return $this->has_multiple_services && $this->second_service_time !== null;
    }

    /**
     * Get the formatted service times.
     */
    public function getServiceTimesAttribute(): array
    {
        $times = [];
        
        if ($this->service_time) {
            $times[] = [
                'name' => $this->service_name ?? 'Service',
                'start' => $this->service_time,
                'end' => $this->service_end_time,
            ];
        }
        
        if ($this->has_multiple_services && $this->second_service_time) {
            $times[] = [
                'name' => $this->second_service_name ?? 'Second Service',
                'start' => $this->second_service_time,
                'end' => $this->second_service_end_time,
            ];
        }
        
        return $times;
    }
}
