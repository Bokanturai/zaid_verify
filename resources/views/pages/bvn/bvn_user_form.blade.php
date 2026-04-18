{{-- Service Field Selection --}}
<div class="col-12 col-md-6">
    <label for="service_field_id" class="form-label fw-semibold">Select Bank / Service Type <span class="text-danger">*</span></label>
    <select class="form-select @error('service_field_id') is-invalid @enderror" id="service_field_id" name="service_field_id" required>
        <option value="" selected disabled>Choose...</option>
        @foreach($serviceFields as $field)
            <option value="{{ $field->id }}" {{ old('service_field_id') == $field->id ? 'selected' : '' }}>
                {{ $field->field_name }} (₦{{ number_format($field->getPriceForUserType(auth()->user()->role), 2) }})
            </option>
        @endforeach
    </select>
    @error('service_field_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- BVN --}}
<div class="col-12 col-md-6">
    <label for="bvn" class="form-label fw-semibold">BVN (11 Digits) <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('bvn') is-invalid @enderror" id="bvn" name="bvn" value="{{ old('bvn') }}" placeholder="Enter 11 digit BVN" required pattern="[0-9]{11}" maxlength="11">
    @error('bvn')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- First Name --}}
<div class="col-12 col-md-4">
    <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="First Name" required>
    @error('first_name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Last Name --}}
<div class="col-12 col-md-4">
    <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="Last Name" required>
    @error('last_name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Middle Name --}}
<div class="col-12 col-md-4">
    <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
    <input type="text" class="form-control @error('middle_name') is-invalid @enderror" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" placeholder="Middle Name (Optional)">
    @error('middle_name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Phone Number --}}
<div class="col-12 col-md-6">
    <label for="phone_no" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('phone_no') is-invalid @enderror" id="phone_no" name="phone_no" value="{{ old('phone_no') }}" placeholder="Phone Number" required>
    @error('phone_no')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Email --}}
<div class="col-12 col-md-6">
    <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Email Address" required>
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Date of Birth --}}
<div class="col-12 col-md-6">
    <label for="dob" class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
    <input type="date" class="form-control @error('dob') is-invalid @enderror" id="dob" name="dob" value="{{ old('dob') }}" required>
    @error('dob')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Agent Location --}}
<div class="col-12 col-md-6">
    <label for="agent_location" class="form-label fw-semibold">Agent Location <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('agent_location') is-invalid @enderror" id="agent_location" name="agent_location" value="{{ old('agent_location') }}" placeholder="Agent Location" required>
    @error('agent_location')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Bank Details Section --}}
<div class="col-12 mt-3">
    <h6 class="text-primary fw-bold border-bottom pb-2">Bank Account Information</h6>
</div>

{{-- Bank Name --}}
<div class="col-12 col-md-4">
    <label for="bank_name" class="form-label fw-semibold">Bank Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank Name" required>
    @error('bank_name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Account Number --}}
<div class="col-12 col-md-4">
    <label for="account_no" class="form-label fw-semibold">Account Number <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('account_no') is-invalid @enderror" id="account_no" name="account_no" value="{{ old('account_no') }}" placeholder="Account Number" required>
    @error('account_no')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Account Name --}}
<div class="col-12 col-md-4">
    <label for="account_name" class="form-label fw-semibold">Account Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('account_name') is-invalid @enderror" id="account_name" name="account_name" value="{{ old('account_name') }}" placeholder="Account Name" required>
    @error('account_name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Address Details Section --}}
<div class="col-12 mt-3">
    <h6 class="text-primary fw-bold border-bottom pb-2">Address Information</h6>
</div>

{{-- State --}}
<div class="col-12 col-md-6">
    <label for="state" class="form-label fw-semibold">State <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}" placeholder="State" required>
    @error('state')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- LGA --}}
<div class="col-12 col-md-6">
    <label for="lga" class="form-label fw-semibold">LGA <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('lga') is-invalid @enderror" id="lga" name="lga" value="{{ old('lga') }}" placeholder="LGA" required>
    @error('lga')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Address --}}
<div class="col-12">
    <label for="address" class="form-label fw-semibold">Residential Address <span class="text-danger">*</span></label>
    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Full Residential Address" required>{{ old('address') }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
