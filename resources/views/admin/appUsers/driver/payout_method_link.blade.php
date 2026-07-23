@php
    $paymentMethods = [
        'stripe' => 'Stripe',
        'paypal' => 'Paypal',
        'upi' => 'UPI',
        'bank' => 'Bank Account',
        'keepz' => 'Keepz Split',
    ];
@endphp

<ul class="nav navbar-pills nav-tabs nav-stacked no-margin" role="tablist">
    @foreach($paymentMethods as $key => $label)
        <li class="{{ request()->routeIs('admin.driver.' . $key) ? 'active' : '' }}">
            <a href="{{ route('admin.driver.' . $key, $appUser->id) }}" data-group="payout">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>
