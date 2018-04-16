<?php

return [
    'gateways' => [
        'direct_pay' => '직접결제',
        'nicepay' => '나이스페이',
    ],
    'methods' => [
        'direct_bank' => '무통장 입금',
        'card' => '카드',
    ],
    'messages' => [
        'cannot pay' => '결제할수 없는 주문입니다',
        'checking for deposit' => '입금 확인중인 주문 입니다',
        'payment pending order exists' => '결제되지 않은 주문이 있습니다.',
        'waiting for approval' => '결제확인중인 주문 입니다',
        'direct bank terms' => '무통장입금 결제는 입금확인후 상품이 준비됩니다.',
        'pay succeed' => '결제가 완료되었습니다',
        'cannot cancel' => '취소할수 없는 주문입니다',
    ],
    'bank_info' => '입금계좌정보',
    'bank_infos' => [
        'bank_name' => '은행이름',
        'account_number' => '계좌번호',
        'account_name' => '예금주',
    ],
    'user cancel' => '사용자 취소',
];
