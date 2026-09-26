@extends('layouts.app')

@section('title', 'Create Company')

@section('content')

<style>
    .company-page {
        padding: 40px 20px;
        background: #f8fafc;
        min-height: calc(100vh - 70px);
    }

    .company-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .company-header {
        margin-bottom: 25px;
    }

    .company-header h1 {
        margin-bottom: 8px;
        font-size: 30px;
        font-weight: 700;
        color: #1e293b;
    }

    .company-header p {
        margin: 0;
        color: #64748b;
        font-size: 15px;
    }

    .company-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 35px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
    }

    /* Avatar */
    .avatar-section {
        text-align: center;
        margin-bottom: 35px;
    }

    .avatar-preview {
        width: 130px;
        height: 130px;
        margin: 0 auto 15px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 13px;
        gap: 5px;
    }

    .avatar-icon {
        font-size: 35px;
    }

    #avatarPreview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }

    .upload-btn {
        display: inline-block;
        padding: 9px 18px;
        border-radius: 8px;
        background: #2563eb;
        color: #fff;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: 0.2s;
    }

    .upload-btn:hover {
        background: #1d4ed8;
    }

    .avatar-input {
        display: none;
    }

    .avatar-help {
        margin-top: 8px;
        color: #94a3b8;
        font-size: 12px;
    }

    /* Form */
    .form-label {
        font-weight: 600;
        color: #334155;
        margin-bottom: 7px;
    }

    .required {
        color: #ef4444;
    }

    .form-control {
        min-height: 45px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 13px;
        color: #334155;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .is-invalid {
        border-color: #ef4444 !important;
    }

    .error-message {
        margin-top: 5px;
        color: #ef4444;
        font-size: 13px;
    }

    /* Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #e2e8f0;
    }

    .btn-cancel,
    .btn-save {
        padding: 11px 22px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #334155;
    }

    .btn-save {
        background: #2563eb;
        color: #ffffff;
    }

    .btn-save:hover {
        background: #1d4ed8;
    }

    @media (max-width: 576px) {
        .company-page {
            padding: 20px 12px;
        }

        .company-card {
            padding: 22px;
        }

        .company-header h1 {
            font-size: 25px;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn-cancel,
        .btn-save {
            width: 100%;
            text-align: center;
        }
    }
</style>


<section class="company-page">

    <div class="company-wrapper">

        {{-- Header --}}
        <div class="company-header">

            <h1>
                Create Your Company
            </h1>

            <p>
                Set up your company profile to get started
                with employee management.
            </p>

        </div>


        {{-- Success Message --}}
        @if(session('success'))

            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>

        @endif


        {{-- Card --}}
        <div class="company-card">

            {{-- Form --}}
            <form
                action="{{ route('company.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf


                {{-- Company Logo --}}
                <div class="avatar-section">

                    <div class="avatar-preview">

                        <div
                            class="avatar-placeholder"
                            id="avatarPlaceholder"
                        >

                            <span class="avatar-icon">
                                🏢
                            </span>

                            Company Logo

                        </div>

                        <img
                            id="avatarPreview"
                            src=""
                            alt="Company Logo"
                        >

                    </div>


                    <label
                        for="avatar"
                        class="upload-btn"
                    >
                        Upload Company Logo
                    </label>


                    <input
                        type="file"
                        id="avatar"
                        name="avatar"
                        class="avatar-input @error('avatar') is-invalid @enderror"
                        accept="image/jpeg,image/png,image/webp"
                    >


                    <div class="avatar-help">
                        PNG, JPG or WEBP · Recommended 500×500px
                    </div>


                    @error('avatar')
                        <div class="error-message">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                <div class="row g-4">


                    {{-- Company Name --}}
                    <div class="col-md-6">

                        <label
                            for="company_name"
                            class="form-label"
                        >
                            Company Name
                            <span class="required">*</span>
                        </label>


                        <input
                            type="text"
                            name="company_name"
                            id="company_name"
                            class="form-control @error('company_name') is-invalid @enderror"
                            value="{{ old('company_name') }}"
                            placeholder="Enter company name"
                            required
                        >


                        @error('company_name')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Email --}}
                    <div class="col-md-6">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Company Email
                            <span class="required">*</span>
                        </label>


                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="company@example.com"
                            required
                        >


                        @error('email')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Phone --}}
                    <div class="col-md-6">

                        <label
                            for="phone"
                            class="form-label"
                        >
                            Phone Number
                        </label>


                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone') }}"
                            placeholder="+880 1XXXXXXXXX"
                        >


                        @error('phone')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Address --}}
                    <div class="col-md-6">

                        <label
                            for="address"
                            class="form-label"
                        >
                            Address
                        </label>


                        <input
                            type="text"
                            name="address"
                            id="address"
                            class="form-control @error('address') is-invalid @enderror"
                            value="{{ old('address') }}"
                            placeholder="Company address"
                        >


                        @error('address')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Package --}}
                    <div class="col-md-6">

                        <label
                            for="package"
                            class="form-label"
                        >
                            Package
                            <span class="required">*</span>
                        </label>


                        <select
                            name="package"
                            id="package"
                            class="form-control @error('package') is-invalid @enderror"
                            required
                        >

                            <option value="">
                                Select Package
                            </option>

                            <option
                                value="Basic"
                                {{ old('package') == 'Basic' ? 'selected' : '' }}
                            >
                                Basic
                            </option>

                            <option
                                value="Standard"
                                {{ old('package') == 'Standard' ? 'selected' : '' }}
                            >
                                Standard
                            </option>

                            <option
                                value="Premium"
                                {{ old('package') == 'Premium' ? 'selected' : '' }}
                            >
                                Premium
                            </option>

                        </select>


                        @error('package')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Billing Cycle --}}
                    <div class="col-md-6">

                        <label
                            for="billing_cycle"
                            class="form-label"
                        >
                            Billing Cycle
                            <span class="required">*</span>
                        </label>


                        <select
                            name="billing_cycle"
                            id="billing_cycle"
                            class="form-control @error('billing_cycle') is-invalid @enderror"
                            required
                        >

                            <option value="">
                                Select Billing Cycle
                            </option>

                            <option
                                value="monthly"
                                {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}
                            >
                                Monthly
                            </option>

                            <option
                                value="yearly"
                                {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}
                            >
                                Yearly
                            </option>

                        </select>


                        @error('billing_cycle')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Company Details --}}
                    <div class="col-12">

                        <label
                            for="details"
                            class="form-label"
                        >
                            Company Details
                        </label>


                        <textarea
                            name="details"
                            id="details"
                            class="form-control @error('details') is-invalid @enderror"
                            rows="5"
                            placeholder="Tell us something about your company..."
                        >{{ old('details') }}</textarea>


                        @error('details')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                </div>


                {{-- Actions --}}
                <div class="form-actions">

                    <a
                        href="{{ url('/') }}"
                        class="btn-cancel"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn-save"
                    >
                        Create Company
                    </button>

                </div>


            </form>

        </div>

    </div>

</section>


{{-- Avatar Preview --}}
<script>
    document.getElementById('avatar').addEventListener('change', function (event) {

        const file = event.target.files[0];

        const preview = document.getElementById('avatarPreview');
        const placeholder = document.getElementById('avatarPlaceholder');

        if (file) {

            const reader = new FileReader();

            reader.onload = function (e) {

                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';

            };

            reader.readAsDataURL(file);

        } else {

            preview.src = '';
            preview.style.display = 'none';
            placeholder.style.display = 'flex';

        }

    });
</script>

@endsection
