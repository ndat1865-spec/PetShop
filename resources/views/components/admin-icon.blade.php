@props(['name'])
<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'aria-hidden' => 'true', 'fill' => 'none']) }}>
@switch($name)
    @case('dashboard')<rect x="3.5" y="3.5" width="7" height="7" rx="2"/><rect x="13.5" y="3.5" width="7" height="4.5" rx="2"/><rect x="13.5" y="10.5" width="7" height="10" rx="2"/><rect x="3.5" y="13" width="7" height="7.5" rx="2"/>@break
    @case('orders')<path d="M6 3.5h12v17l-2.2-1.4-1.9 1.4-1.9-1.4-1.9 1.4-1.9-1.4L6 20.5z"/><path d="M9 8h6M9 12h6M9 16h3"/>@break
    @case('products')<path d="m4.5 7.8 7.5-4 7.5 4v8.4l-7.5 4-7.5-4z"/><path d="m4.8 7.9 7.2 4 7.2-4M12 12v8"/><path d="m8.3 5.8 7.4 4"/>@break
    @case('inventory')<path d="M4 5h16v14H4z"/><path d="M8 5v14M16 5v14M4 10h16M4 15h16"/><path d="M10.5 7.5h3M10.5 12.5h3"/>@break
    @case('categories')<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><path d="M17 13.5v7M13.5 17h7"/>@break
    @case('customers')<path d="M15.5 20v-1.5a4.5 4.5 0 0 0-4.5-4.5H7a4.5 4.5 0 0 0-4.5 4.5V20"/><circle cx="9" cy="7" r="3.5"/><path d="M17 11a3 3 0 1 0 0-6M18 14.3a4.2 4.2 0 0 1 3.5 4.2V20"/>@break
    @case('reviews')<path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>@break
    @case('contacts')<path d="M3.5 5.5h17v12h-11L5 21v-3.5H3.5z"/><path d="M7.5 9h9M7.5 13h6"/>@break
    @case('users')<circle cx="9" cy="7" r="3.5"/><path d="M2.5 20v-1.5A4.5 4.5 0 0 1 7 14h4a4.5 4.5 0 0 1 4.5 4.5V20"/><path d="m16 10 1.7 1.7L21.5 8"/>@break
    @case('coupons')<path d="M4 6.5A2.5 2.5 0 0 0 6.5 4h11A2.5 2.5 0 0 0 20 6.5v2a2.5 2.5 0 0 0 0 5v2A2.5 2.5 0 0 0 17.5 18h-11A2.5 2.5 0 0 0 4 15.5v-2a2.5 2.5 0 0 0 0-5z"/><path d="m9 15 6-6M9.5 9h.01M14.5 15h.01"/>@break
    @case('shipments')<path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/><path d="M5.5 9.5h5M5.5 12.5h3"/>@break
    @case('payments')<path d="M3.5 7h15A2.5 2.5 0 0 1 21 9.5v8A2.5 2.5 0 0 1 18.5 20h-13A2.5 2.5 0 0 1 3 17.5v-12A2.5 2.5 0 0 1 5.5 3H17"/><path d="M15.5 11.5H21v4h-5.5a2 2 0 0 1 0-4Z"/><circle cx="16" cy="13.5" r=".5"/>@break
    @case('returns')<path d="M8 7H4V3"/><path d="M4.5 7.2A8.5 8.5 0 1 1 4 16"/><path d="M8 12h8M8 12l3-3M8 12l3 3"/>@break
    @case('audit')<path d="M8 4h8M9 2.5h6v3H9z"/><path d="M6 4H4.5v17h15V4H18"/><path d="m8 11 2 2 5-5M8 17h8"/>@break
    @case('chat')<path d="M4 5.5h16v11H9l-5 4v-15Z"/><path d="M8 10h8M8 13h5"/>@break
    @case('chat-rules')<path d="M4 5h16v12H8l-4 3V5Z"/><path d="m9 11 2 2 4-5M17 8h1"/>@break
    @case('settings')<path d="M4 6h8M16 6h4M4 12h3M11 12h9M4 18h10M18 18h2"/><circle cx="14" cy="6" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="16" cy="18" r="2"/>@break
    @case('store')<path d="M4 9.5V20h16V9.5M3 9.5l2-5h14l2 5"/><path d="M3 9.5a3 3 0 0 0 5 2 3 3 0 0 0 4 0 3 3 0 0 0 4 0 3 3 0 0 0 5-2M9 20v-5h6v5"/>@break
    @case('logout')<path d="M10 4H5v16h5M14 8l4 4-4 4M8 12h10"/>@break
    @case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
    @case('close')<path d="m6 6 12 12M18 6 6 18"/>@break
    @case('search')<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>@break
    @case('back')<path d="m15 18-6-6 6-6"/>@break
    @case('attachment')<path d="m12 5-6.5 6.5a4 4 0 0 0 5.7 5.7l7.1-7.1a2.8 2.8 0 0 0-4-4l-7.2 7.2a1.5 1.5 0 0 0 2.1 2.1l6.1-6.1"/>@break
    @case('send')<path d="m5 12 14-7-4 14-3-6-7-1Z"/><path d="m12 13 7-8"/>@break
@endswitch
</svg>
