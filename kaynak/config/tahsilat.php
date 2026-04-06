<?php

return [
    'para_birimi' => 'TRY',

    'tahsilat_yontemleri' => [
        'muvekkil_hesabina_reddiyat' => 'Müvekkil hesabına reddiyat',
        'vekil_hesabina_reddiyat' => 'Vekil hesabına reddiyat',
        'muvekkil_hesabina_mail_order' => 'Müvekkil hesabına mail order',
        'vekil_hesabina_mail_order' => 'Vekil hesabına mail order',
        'muvekkil_hesabina_eft_havale' => 'Müvekkil hesabına EFT / havale',
        'vekil_hesabina_eft_havale' => 'Vekil hesabına EFT / havale',
        'elden_alindi' => 'Elden alındı',
        // YENİ EKLENEN VEKALET ÜCRETİ SEÇENEKLERİ
        'vekalet_ucreti_vekil_hesabina_eft_havale' => 'Vekalet ücreti vekil hesabına EFT / havale',
        'vekalet_ucreti_reddiyat' => 'Vekalet ücreti reddiyat',
        'vekalet_ucreti_mail_order' => 'Vekalet ücreti mail order',
        'vekalet_ucreti_elden_alindi' => 'Vekalet ücreti elden alındı',
    ],

    'tahsilat_birimleri' => [
        'sulhen' => 'Sulhen',
        'satis' => 'Satış',
        'istihkak' => 'İstihkak',
        'takibin_devami' => 'Takibin devamı',
        'nami_mustear' => 'Namı müstear',
        'tasarrufun_iptali' => 'Tasarrufun iptali',
        'itirazin_iptali' => 'İtirazın iptali',
        'konkordato' => 'Konkordato',
    ],

    'haciz_turleri' => [
        'istihkakli' => 'İstihkaklı',
        'nami_mustear' => 'Namı müstear',
        '97' => '97',
        'ihtiyati' => 'İhtiyati',
        'sulhen' => 'Sulhen',
    ],

    'tab_tanimlari' => [
        ['key' => 'dashboard', 'label' => 'İzlence'],
        ['key' => 'tahsilat', 'label' => 'Günlük Tahsilat'],
        ['key' => 'tum_tahsilatlar', 'label' => 'Tüm Tahsilatlar'],
        ['key' => 'protokol', 'label' => 'Protokoller'],
        ['key' => 'prim', 'label' => 'Prim Pivotu'],
    ],

    'varsayilan_kademeler' => [
        [
            'kademe' => 'kademe_1',
            'kademe_adi' => 'Kademe 1',
            'varsayilan_prim_orani' => '10.00',
        ],
        [
            'kademe' => 'kademe_2',
            'kademe_adi' => 'Kademe 2',
            'varsayilan_prim_orani' => '8.00',
        ],
        [
            'kademe' => 'kademe_3',
            'kademe_adi' => 'Kademe 3',
            'varsayilan_prim_orani' => '6.00',
        ],
    ],
];
