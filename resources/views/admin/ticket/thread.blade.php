@extends('layouts.admin')

@section('content')
<style>
    .mili-chat-shell {
        max-width: 980px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 35px rgba(0, 0, 0, .08);
    }
    .mili-chat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 20px;
        background: #111;
        color: #fff;
    }
    .mili-chat-header-main {
        display: flex;
        align-items: center;
        min-width: 0;
    }
    .mili-chat-logo {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        object-fit: cover;
        margin-right: 12px;
    }
    .mili-chat-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
    }
    .mili-chat-subtitle {
        margin-top: 3px;
        color: #bdbdbd;
        font-size: 12px;
    }
    .mili-chat-mode {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .mili-chat-mode .label {
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 11px;
    }
    .mili-chat-feed {
        height: min(62vh, 650px);
        min-height: 380px;
        overflow-y: auto;
        padding: 20px;
        background: #f5f6f8;
    }
    .mili-chat-row {
        display: flex;
        margin: 8px 0;
    }
    .mili-chat-row.support {
        justify-content: flex-end;
    }
    .mili-chat-bubble {
        max-width: min(74%, 620px);
        padding: 10px 12px;
        border-radius: 16px 16px 16px 4px;
        background: #fff;
        color: #222;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        overflow-wrap: anywhere;
    }
    .mili-chat-row.support .mili-chat-bubble {
        border-radius: 16px 16px 4px 16px;
        background: #ffca16;
        color: #111;
    }
    .mili-chat-sender {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: 700;
        opacity: .7;
    }
    .mili-chat-text {
        white-space: pre-wrap;
    }
    .mili-chat-time {
        display: block;
        margin-top: 5px;
        font-size: 10px;
        text-align: right;
        opacity: .6;
    }
    .mili-chat-photo {
        display: block;
        max-width: 100%;
        max-height: 360px;
        margin: 4px 0 7px;
        border-radius: 12px;
        object-fit: contain;
        background: #ececec;
    }
    .mili-chat-composer {
        padding: 14px;
        border-top: 1px solid #e7e7e7;
        background: #fff;
    }
    .mili-chat-form {
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }
    .mili-chat-message {
        min-height: 46px;
        max-height: 130px;
        resize: vertical;
        border-radius: 12px;
    }
    .mili-chat-file-label,
    .mili-chat-send {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        cursor: pointer;
        border: 0;
        flex: 0 0 46px;
    }
    .mili-chat-file-label {
        background: #eceff3;
        color: #222;
    }
    .mili-chat-send {
        background: #ffca16;
        color: #111;
    }
    .mili-chat-preview {
        display: none;
        align-items: center;
        gap: 10px;
        margin: 0 0 10px 56px;
        font-size: 12px;
    }
    .mili-chat-preview img {
        width: 52px;
        height: 52px;
        border-radius: 10px;
        object-fit: cover;
    }
    .mili-chat-empty {
        padding: 80px 20px;
        color: #777;
        text-align: center;
    }
    @media (max-width: 767px) {
        .mili-chat-header {
            align-items: flex-start;
            flex-direction: column;
        }
        .mili-chat-mode {
            justify-content: flex-start;
        }
        .mili-chat-bubble {
            max-width: 88%;
        }
        .mili-chat-feed {
            height: 58vh;
            min-height: 320px;
            padding: 12px;
        }
    }
</style>

<div class="content">
    <div class="mili-chat-shell">
        <div class="mili-chat-header">
            <div class="mili-chat-header-main">
                <img class="mili-chat-logo" src="{{ asset('brand/mili-taxi-icon.png') }}" alt="Mili Taxi">
                <div>
                    <h1 class="mili-chat-title">
                        {{ trim(($supportTicketReplies->appUser->first_name ?? '').' '.($supportTicketReplies->appUser->last_name ?? '')) ?: 'მომხმარებელი' }}
                    </h1>
                    <div class="mili-chat-subtitle">
                        {{ strtoupper($supportTicketData->app_role ?? 'rider') }} · #{{ $supportTicketData->id }}
                    </div>
                </div>
            </div>
            <div class="mili-chat-mode">
                <span id="chat-mode-label" class="label {{ $supportTicketData->operator_active ? 'label-warning' : 'label-info' }}">
                    {{ $supportTicketData->operator_active ? 'ოპერატორი პასუხობს' : 'AI ასისტენტი აქტიურია' }}
                </span>
                <form method="POST" action="{{ route('admin.ticket.thread.mode', $id) }}">
                    @csrf
                    <input type="hidden" name="mode" value="{{ $supportTicketData->operator_active ? 'ai' : 'operator' }}">
                    <button class="btn btn-sm {{ $supportTicketData->operator_active ? 'btn-info' : 'btn-warning' }}" type="submit">
                        {{ $supportTicketData->operator_active ? 'AI რეჟიმზე გადასვლა' : 'ოპერატორის რეჟიმზე გადასვლა' }}
                    </button>
                </form>
            </div>
        </div>

        <div id="mili-chat-feed" class="mili-chat-feed" aria-live="polite">
            @forelse($supportTicketReplies->replies as $reply)
                <div class="mili-chat-row {{ $reply->is_admin_reply ? 'support' : 'user' }}" data-message-id="{{ $reply->id }}">
                    <div class="mili-chat-bubble">
                        <span class="mili-chat-sender">
                            @if($reply->is_admin_reply)
                                {{ $reply->source === 'ai' ? 'AI' : 'ოპერატორი' }}
                            @else
                                {{ trim(($supportTicketReplies->appUser->first_name ?? '').' '.($supportTicketReplies->appUser->last_name ?? '')) ?: 'მომხმარებელი' }}
                            @endif
                        </span>
                        @if($reply->attachment_path)
                            <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('support-chat.attachment', now()->addMinutes(15), ['reply' => $reply->id]) }}" target="_blank" rel="noopener">
                                <img class="mili-chat-photo"
                                     src="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('support-chat.attachment', now()->addMinutes(15), ['reply' => $reply->id]) }}"
                                     alt="{{ $reply->attachment_name ?: 'Chat photo' }}">
                            </a>
                        @endif
                        @if(trim((string) $reply->message) !== '')
                            <div class="mili-chat-text">{{ $reply->message }}</div>
                        @endif
                        <span class="mili-chat-time">{{ optional($reply->created_at)->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="mili-chat-empty">მიმოწერა ჯერ არ დაწყებულა.</div>
            @endforelse
        </div>

        <div class="mili-chat-composer">
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <div id="mili-chat-preview" class="mili-chat-preview">
                <img id="mili-chat-preview-image" alt="არჩეული ფოტო">
                <span id="mili-chat-preview-name"></span>
                <button type="button" id="mili-chat-preview-remove" class="btn btn-xs btn-default">წაშლა</button>
            </div>
            <form id="mili-chat-form" class="mili-chat-form" method="POST"
                  action="{{ route('admin.ticket.thread.create', [$id]) }}" enctype="multipart/form-data">
                @csrf
                <label class="mili-chat-file-label" for="mili-chat-file" title="ფოტოს დამატება">
                    <i class="fa fa-camera"></i>
                </label>
                <input id="mili-chat-file" type="file" name="attachment"
                       accept="image/jpeg,image/png,image/webp" hidden>
                <textarea class="form-control mili-chat-message" name="message" id="mili-chat-message"
                          maxlength="2000" placeholder="დაწერეთ შეტყობინება...">{{ old('message') }}</textarea>
                <button class="mili-chat-send" type="submit" title="გაგზავნა">
                    <i class="fa fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    const feed = document.getElementById('mili-chat-feed');
    const form = document.getElementById('mili-chat-form');
    const message = document.getElementById('mili-chat-message');
    const file = document.getElementById('mili-chat-file');
    const preview = document.getElementById('mili-chat-preview');
    const previewImage = document.getElementById('mili-chat-preview-image');
    const previewName = document.getElementById('mili-chat-preview-name');
    const previewRemove = document.getElementById('mili-chat-preview-remove');
    const messagesUrl = @json(route('admin.ticket.thread.messages', $id));
    let lastSignature = '';

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    function scrollToBottom(smooth) {
        feed.scrollTo({ top: feed.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
    }

    function renderMessages(items) {
        const signature = items.map((item) => item.id).join(',');
        if (signature === lastSignature) return;
        lastSignature = signature;
        if (!items.length) {
            feed.innerHTML = '<div class="mili-chat-empty">მიმოწერა ჯერ არ დაწყებულა.</div>';
            return;
        }
        feed.innerHTML = items.map((item) => {
            const supportClass = item.is_support ? 'support' : 'user';
            const photo = item.attachment_url
                ? `<a href="${escapeHtml(item.attachment_url)}" target="_blank" rel="noopener"><img class="mili-chat-photo" src="${escapeHtml(item.attachment_url)}" alt="${escapeHtml(item.attachment_name || 'Chat photo')}"></a>`
                : '';
            const text = item.message
                ? `<div class="mili-chat-text">${escapeHtml(item.message)}</div>`
                : '';
            return `<div class="mili-chat-row ${supportClass}" data-message-id="${item.id}">
                <div class="mili-chat-bubble">
                    <span class="mili-chat-sender">${escapeHtml(item.sender)}</span>
                    ${photo}${text}
                    <span class="mili-chat-time">${escapeHtml(item.created_at)}</span>
                </div>
            </div>`;
        }).join('');
        scrollToBottom(true);
    }

    async function pollMessages() {
        if (document.hidden) return;
        try {
            const response = await fetch(messagesUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) return;
            const payload = await response.json();
            renderMessages(payload.messages || []);
        } catch (_) {
            // Keep the last successfully rendered conversation visible.
        }
    }

    file.addEventListener('change', function () {
        const selected = file.files && file.files[0];
        if (!selected) return;
        if (selected.size > 5 * 1024 * 1024) {
            alert('ფოტოს მაქსიმალური ზომაა 5 MB.');
            file.value = '';
            return;
        }
        previewImage.src = URL.createObjectURL(selected);
        previewName.textContent = selected.name;
        preview.style.display = 'flex';
    });

    previewRemove.addEventListener('click', function () {
        file.value = '';
        previewImage.removeAttribute('src');
        previewName.textContent = '';
        preview.style.display = 'none';
    });

    form.addEventListener('submit', function (event) {
        if (!message.value.trim() && !(file.files && file.files.length)) {
            event.preventDefault();
            message.focus();
        }
    });

    lastSignature = Array.from(feed.querySelectorAll('[data-message-id]'))
        .map((node) => node.dataset.messageId)
        .join(',');
    scrollToBottom(false);
    setInterval(pollMessages, 5000);
});
</script>
@endsection
