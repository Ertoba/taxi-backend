@extends('layouts.admin')

@section('content')
<section class="content">
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            <div class="box box-info box_info">
                <h4 class="all_settings f-18 ms-3 mt-1" style="margin-left:15px;">{{ trans('global.manage_settings') }}</h4>
                @include('admin.generalSettings.general-setting-links.links')
            </div>
        </div>

        <div class="col-md-9">
            <div class="nav-tabs-custom">
                @include('admin.generalSettings.payment-methods.payment-links')
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="box-body">
                            <div class="alert alert-info">
                                Keepz Split sends the driver share directly to the driver's saved Georgian IBAN and sends the platform share to the receiver below. Payments fail closed when either receiver is missing or invalid.
                            </div>

                            <form method="POST" action="{{ route('admin.keepz_split.update') }}" class="form-horizontal">
                                @csrf
                                <input type="hidden" name="split_status" value="0">

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Environment</label>
                                    <div class="col-sm-6">
                                        <select name="mode" class="form-control">
                                            <option value="test" {{ old('mode', $mode) === 'test' ? 'selected' : '' }}>Test</option>
                                            <option value="live" {{ old('mode', $mode) === 'live' ? 'selected' : '' }}>Live</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Enable Keepz Split</label>
                                    <div class="col-sm-6" style="padding-top:7px;">
                                        <input type="checkbox" name="split_status" value="1"
                                            {{ old('split_status', $splitStatus === 'Active' ? 1 : 0) ? 'checked' : '' }}>
                                        <span class="text-muted">No fallback to the main receiver is allowed.</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Platform Receiver Type <span class="text-danger">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="receiver_type" class="form-control">
                                            @foreach(['IBAN', 'BRANCH', 'USER'] as $type)
                                                <option value="{{ $type }}" {{ old('receiver_type', $receiverType ?: 'IBAN') === $type ? 'selected' : '' }}>
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Platform IBAN / Receiver Identifier <span class="text-danger">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" name="receiver_identifier"
                                            class="form-control @error('receiver_identifier') is-invalid @enderror"
                                            value="{{ old('receiver_identifier', $receiverIdentifier) }}"
                                            placeholder="GE00AA0000000000000000 or Keepz receiver UUID"
                                            autocomplete="off">
                                        @error('receiver_identifier')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> {{ __('global.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
