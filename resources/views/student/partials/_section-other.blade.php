@php $student = $student ?? null; @endphp
<div class="row gy-3">
    <div class="col-12"><h6 class="text-md fw-semibold text-primary-light mb-12">Medical Details</h6></div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Blood Group</label>
        <select name="blood_group" class="form-control form-select">
            <option value="" disabled selected>Select blood group</option>
            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)
                <option value="{{ $group }}" {{ old('blood_group', $student?->blood_group ?? '') == $group ? 'selected' : '' }}>{{ $group }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Height</label>
        <input type="text" name="height" class="form-control" value="{{ old('height', $student?->height ?? '') }}" placeholder="e.g. 150cm">
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Weight</label>
        <input type="text" name="weight" class="form-control" value="{{ old('weight', $student?->weight ?? '') }}" placeholder="e.g. 45kg">
    </div>

    <div class="col-12 mt-16"><h6 class="text-md fw-semibold text-primary-light mb-12">Other Information</h6></div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Bank Account Number</label>
        <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $student?->bank_account_number ?? '') }}" placeholder="Enter account number">
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Bank Name</label>
        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $student?->bank_name ?? '') }}" placeholder="Enter bank name">
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">National ID Number</label>
        <input type="text" name="national_id_number" class="form-control" value="{{ old('national_id_number', $student?->national_id_number ?? '') }}" placeholder="Enter ID number">
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Previous School</label>
        <input type="text" name="previous_school_name" class="form-control" value="{{ old('previous_school_name', $student?->previous_school_name ?? '') }}" placeholder="Enter school name">
    </div>
    <div class="col-xxl-6 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Previous School Address</label>
        <input type="text" name="previous_school_address" class="form-control" value="{{ old('previous_school_address', $student?->previous_school_address ?? '') }}" placeholder="Enter school address">
    </div>
    <div class="col-xxl-6 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Current Address</label>
        <input type="text" name="current_address" class="form-control" value="{{ old('current_address', $student?->current_address ?? '') }}" placeholder="Enter current address">
    </div>
    <div class="col-xxl-6 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Permanent Address</label>
        <input type="text" name="permanent_address" class="form-control" value="{{ old('permanent_address', $student?->permanent_address ?? '') }}" placeholder="Enter permanent address">
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Hostel</label>
        <input type="text" name="hostel" class="form-control" value="{{ old('hostel', $student?->hostel ?? '') }}" placeholder="Enter hostel">
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Room No</label>
        <input type="text" name="room_no" class="form-control" value="{{ old('room_no', $student?->room_no ?? '') }}" placeholder="Enter room number">
    </div>
    <div class="col-12">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Additional Notes</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Enter any important notes about the student">{{ old('notes', $student?->notes ?? '') }}</textarea>
    </div>
</div>
