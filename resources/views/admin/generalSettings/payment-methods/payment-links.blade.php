<ul class="nav nav-tabs" style="display: inline-block;">
    @php
        $currentMethod = request()->route('method');
        $methodsToShow = ['paypal', 'stripe', 'keepz', 'cash'];
    @endphp

    @foreach($methodsToShow as $methodKey)
        <li class="{{ $currentMethod === $methodKey ? 'active' : '' }}">
            <a href="{{ route('admin.payment_methods.index', $methodKey) }}">
                {{ trans('global.' . $methodKey) }}
            </a>
        </li>
    @endforeach

    <li class="{{ request()->routeIs('admin.keepz_split.*') ? 'active' : '' }}">
        <a href="{{ route('admin.keepz_split.settings') }}">Keepz Split</a>
    </li>
</ul>
