@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/driver-profile.css') }}">
@endsection

@section('content')
<section class="content">
    @include('admin.appUsers.driver.menu')

    <div class="row" style="margin-top:20px">
        <div class="col-md-3 settings_bar_gap">
            <div class="box box-info box_info">
                <h4 class="all_settings f-18 mt-1" style="margin-left:15px;">Payout Methods</h4>
                @include('admin.appUsers.driver.payout_method_link')
            </div>
        </div>

        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Keepz Split Receiver</h3>
                </div>

                <div class="alert alert-info" style="margin:15px;">
                    This IBAN receives the driver's share directly when a rider pays through Keepz. Gateway keys are never stored in the driver profile.
                </div>

                <form method="POST" action="{{ route('admin.driver.keepz.update', $appUser->id) }}" class="form-horizontal">
                    @csrf
                    <input type="hidden" name="is_active" value="0">

                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Active</label>
                            <div class="col-sm-6" style="padding-top:7px;">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', (int)($details['is_active'] ?? 0)) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Account Holder <span class="text-danger">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="account_name"
                                    class="form-control @error('account_name') is-invalid @enderror"
                                    value="{{ old('account_name', $details['account_name'] ?? '') }}"
                                    maxlength="120" autocomplete="name">
                                @error('account_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Georgian IBAN <span class="text-danger">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="iban"
                                    class="form-control @error('iban') is-invalid @enderror"
                                    value="{{ old('iban', $details['keepz_receiver_identifier'] ?? '') }}"
                                    placeholder="GE00AA0000000000000000"
                                    maxlength="32" autocomplete="off">
                                @error('iban')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-info btn-space">{{ trans('global.save') }}</button>
                        <a class="btn btn-danger" href="{{ route('admin.driver.profile', $appUser->id) }}">{{ trans('global.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
