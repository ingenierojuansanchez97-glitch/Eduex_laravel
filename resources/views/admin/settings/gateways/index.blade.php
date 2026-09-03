@extends('layouts.admin')

@section('title', 'Payment Gateways')
@section('breadcrumb')
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
        <div class="breadcrumb-item active">Payment Gateways</div>
    </div>
@endsection

@section('main-content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-2 mb-md-0">Payment Gateway Configuration</h4>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-primary mr-3">{{ $gateways->count() }} gateways</span>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary"><i
                                class="fas fa-arrow-left"></i> Back to Settings</a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Update API keys, enable or disable gateways, and manage offline payment instructions. Changes take
                        effect immediately.
                    </p>

                    <div class="row">
                        @foreach ($gateways as $gateway)
                            <div class="col-lg-6 mb-4">
                                <div class="card border h-100 shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">{{ $gateway->name }}</h5>
                                            <small class="text-muted text-uppercase">{{ $gateway->type }} gateway</small>
                                        </div>
                                        <span
                                            class="badge badge-{{ $gateway->is_enabled ? 'success' : 'secondary' }}">{{ $gateway->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('admin.settings.gateways.update', $gateway->identifier) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="form-group">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="enable-{{ $gateway->identifier }}" name="is_enabled"
                                                        value="1" {{ $gateway->is_enabled ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="enable-{{ $gateway->identifier }}">Enable
                                                        {{ $gateway->name }}</label>
                                                </div>
                                            </div>

                                            @if ($gateway->type === 'online')
                                                <div class="border rounded p-3 mb-3">
                                                    <h6 class="mb-3">Credentials</h6>
                                                    @php($credentials = $gateway->credentials ?? [])
                                                    @php($fields = $credentialFieldMap[$gateway->identifier] ?? array_keys($credentials))
                                                    @forelse($fields as $key)
                                                        <div class="form-group">
                                                            <label
                                                                class="text-uppercase small font-weight-bold">{{ str_replace('_', ' ', $key) }}</label>
                                                            <input type="text" name="credentials[{{ $key }}]"
                                                                value="{{ $credentials[$key] ?? '' }}" class="form-control"
                                                                placeholder="Enter {{ $key }}">
                                                        </div>
                                                        <small class="text-muted">Mode= live/sandbox</small>
                                                    @empty
                                                        <div class="form-group">
                                                            <label
                                                                class="text-uppercase small font-weight-bold">Credential</label>
                                                            <input type="text" name="credentials[value]" value=""
                                                                class="form-control" placeholder="Enter credential value">
                                                        </div>
                                                    @endforelse
                                                </div>
                                            @else
                                                <div class="form-group">
                                                    <label for="instructions-{{ $gateway->identifier }}">Offline
                                                        Instructions</label>
                                                    <textarea name="offline_instructions" id="instructions-{{ $gateway->identifier }}" rows="5" class="form-control"
                                                        placeholder="Provide instructions for manual/offline payments.">{{ old('offline_instructions', $gateway->offline_instructions) }}</textarea>
                                                    <small class="text-muted">These instructions are displayed to
                                                        instructors and students when offline payments are selected.</small>
                                                </div>
                                            @endif

                                            <div class="text-right">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
