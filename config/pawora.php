<?php

return [
    'shipping' => [
        'free_threshold' => 700000,
        'ghn' => [
            'enabled' => env('GHN_ENABLED', false),
            'token' => env('GHN_TOKEN'),
            'shop_id' => env('GHN_SHOP_ID'),
            'api_url' => env('GHN_API_URL', 'https://online-gateway.ghn.vn/shiip/public-api/v2'),
            'ssl_verify' => env('GHN_SSL_VERIFY', true),
            'service_type_id' => env('GHN_SERVICE_TYPE_ID', 2),
            'from_district_id' => env('GHN_FROM_DISTRICT_ID'),
            'default_weight' => env('GHN_DEFAULT_WEIGHT', 500),
        ],
        'zones' => [
            'local' => ['fee' => 25000, 'provinces' => ['Thành phố Hồ Chí Minh']],
            'nearby' => ['fee' => 30000, 'provinces' => ['Đồng Nai','Tây Ninh','Thành phố Hà Nội','Thành phố Hải Phòng','Thành phố Đà Nẵng','Thành phố Cần Thơ']],
            'remote' => ['fee' => 45000, 'provinces' => ['Cao Bằng','Lai Châu','Điện Biên','Sơn La','Lạng Sơn','Tuyên Quang','Lào Cai','Đắk Lắk','Gia Lai','Lâm Đồng']],
            'standard' => ['fee' => 40000, 'provinces' => []],
        ],
        'provinces' => [
            'Thành phố Hà Nội','Thành phố Hải Phòng','Thành phố Huế','Thành phố Đà Nẵng','Thành phố Cần Thơ','Thành phố Hồ Chí Minh',
            'Lai Châu','Điện Biên','Sơn La','Lạng Sơn','Cao Bằng','Tuyên Quang','Lào Cai','Thái Nguyên','Phú Thọ','Bắc Ninh','Hưng Yên','Ninh Bình','Quảng Ninh','Thanh Hóa','Nghệ An','Hà Tĩnh','Quảng Trị','Quảng Ngãi','Gia Lai','Khánh Hòa','Lâm Đồng','Đắk Lắk','Đồng Nai','Tây Ninh','Vĩnh Long','Đồng Tháp','Cà Mau','An Giang',
        ],
    ],
];
