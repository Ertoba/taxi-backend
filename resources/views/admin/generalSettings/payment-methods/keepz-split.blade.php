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
                                Keepz requires the order's main BRANCH receiver to appear exactly once in splitDetails. The platform share is therefore sent to the active Keepz BRANCH below, while the driver's share is sent directly to the driver's saved Georgian IBAN. The platform IBAN entered here must be the settlement account that Keepz has mapped to this BRANCH.
                            </div>

                            <form method="POST" action="{{ route('admin.keepz_split.update') }}" class="form-horizontal">
                                @csrf
                                <input type="hidden" name="split_status" value="0">
                                <input type="hidden" name="mapping_confirmed" value="0">

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Active Keepz Environment</label>
                                    <div class="col-sm-6">
                                        <p class="form-control-static">
                                            <strong>{{ strtoupper($mode) }}</strong>
                                        </p>
                                        <span class="text-muted">Change the environment only from the main Keepz gateway settings.</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Main Keepz Receiver Type</label>
                                    <div class="col-sm-6">
                                        <p class="form-control-static">
                                            <strong>{{ strtoupper($mainReceiverType ?: 'Not configured') }}</strong>
                                        </p>
                                        <span class="text-muted">Split requires BRANCH.</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Main Keepz Receiver ID</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" value="{{ $mainReceiverId }}" readonly>
                                        <span class="text-muted">This UUID comes from the main Keepz gateway settings.</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Platform Settlement IBAN <span class="text-danger">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" name="platform_iban"
                                            class="form-control @error('platform_iban') is-invalid @enderror"
                                            value="{{ old('platform_iban', $platformIban) }}"
                                            placeholder="GE00AA0000000000000000"
                                            maxlength="40"
                                            autocomplete="off">
                                        <span class="text-muted">Enter the Georgian IBAN assigned by Keepz to the main BRANCH receiver.</span>
                                        @error('platform_iban')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Confirm Keepz Mapping <span class="text-danger">*</span></label>
                                    <div class="col-sm-6" style="padding-top:7px;">
                                        <input type="checkbox" name="mapping_confirmed" value="1"
                                            {{ old('mapping_confirmed', $mappingConfirmed ? 1 : 0) ? 'checked' : '' }}>
                                        <span class="text-muted">I confirm that Keepz has mapped this BRANCH receiver to the platform IBAN above.</span>
                                        @error('mapping_confirmed')
                                            <div><span class="text-danger">{{ $message }}</span></div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Enable Keepz Split</label>
                                    <div class="col-sm-6" style="padding-top:7px;">
                                        <input type="checkbox" name="split_status" value="1"
                                            {{ old('split_status', $splitStatus === 'Active' ? 1 : 0) ? 'checked' : '' }}>
                                        <span class="text-muted">Payments fail closed when the platform mapping or driver IBAN is missing. No fallback is allowed.</span>
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
