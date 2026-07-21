@extends('layouts.admin')

@section('content')
    @php($i = 0)
    <section class="content">
        <div class="row">
            <div class="col-md-3 settings_bar_gap">
                <div class="box box-info box_info">
                    <h4 class="all_settings f-18 mt-1" style="margin-left:15px;">
                        {{ trans('global.manage_settings') }}
                    </h4>
                    @include('admin.generalSettings.general-setting-links.links')
                </div>
            </div>

            @include('admin.generalSettings.smssettings.smsnavicon')

            <input class="check statusdata" type="checkbox"
                data-onstyle="success"
                id="user{{ $i }}"
                data-offstyle="danger"
                data-toggle="toggle"
                data-on="Active"
                data-off="Inactive"
                data-url="{{ route('admin.update-sms-provider-name') }}"
                data-user-value="ubill"
                {{ ($sms_provider_name->meta_value ?? '') === 'ubill' ? 'checked' : '' }}>
            <label for="user{{ $i }}" style="margin-left: 91%; margin-top: 8px;" class="checktoggle">checkbox</label>

            <form method="post" action="{{ route('admin.updateUbill') }}"
                class="form-horizontal smssettingform" novalidate="novalidate">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="ubill_api_key">
                            API Key <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input class="form-control" type="password" name="ubill_api_key"
                                id="ubill_api_key"
                                placeholder="{{ $ubill_api_key ? 'Configured — leave blank to keep it' : 'uBILL API Key' }}"
                                autocomplete="new-password">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="ubill_brand_id">
                            Brand ID <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input class="form-control" type="password" name="ubill_brand_id"
                                id="ubill_brand_id"
                                placeholder="{{ $ubill_brand_id ? 'Configured — leave blank to keep it' : 'uBILL Brand ID' }}"
                                autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-info" id="submitBtn">{{ trans('global.save') }}</button>
                    <a class="btn btn-danger" href="{{ route('admin.settings') }}">{{ trans('global.cancel') }}</a>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.generalSettings.smssettings.toastrmsg')
@endsection
