@props([
    'branchId' => null,
    'selected' => null,
    'name' => 'closest_location',
    'id' => 'closest_location',
])
@php
    $options = \App\Models\CoverageLocation::optionsForBranch($branchId ? (int) $branchId : null);
    $current = old($name, $selected);
    // Keep a member's stored value selectable even after it has been retired
    // from the list, so editing their profile can't silently blank it.
    $showCurrent = $current && ! in_array($current, $options, true);
@endphp
<select name="{{ $name }}" id="{{ $id }}" {{ $attributes }}>
    <option value="">Select closest location</option>
    @foreach ($options as $option)
        <option value="{{ $option }}" @selected($current === $option)>{{ $option }}</option>
    @endforeach
    @if ($showCurrent)
        <option value="{{ $current }}" selected>{{ $current }} (no longer listed)</option>
    @endif
</select>
