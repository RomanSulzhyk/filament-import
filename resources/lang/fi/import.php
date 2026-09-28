<?php

return [

    'action' => [
        'label' => 'Tuo',
        'modal_heading' => 'Tuo :label',
        'submit' => 'Tuo',
        'download_template' => 'Lataa malli',
    ],

    'steps' => [
        'upload' => 'Lataa tiedosto',
        'mapping' => 'Yhdistä sarakkeet',
    ],

    'fields' => [
        'file' => [
            'label' => 'Taulukko',
            'helper' => '.xlsx- tai .csv-tiedosto. Sen ensimmäisen ei-tyhjän rivin on sisällettävä sarakeotsikot.',
        ],
        'mapping' => [
            'helper' => 'Valitse, mikä tiedostosi sarake täyttää kunkin kentän. Vastaavuudet ehdotettiin automaattisesti; jätä kenttä tyhjäksi ohittaaksesi sen.',
            'placeholder' => 'Älä tuo',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Tuonti valmis',
            'body' => ':created luotu, :updated päivitetty, :failed epäonnistui.',
            'body_with_skipped' => ':created luotu, :updated päivitetty, :skipped ohitettu, :failed epäonnistui.',
        ],
        'failed' => [
            'title' => 'Tuontia ei voitu aloittaa',
        ],
        'download_failures' => 'Lataa epäonnistuneet rivit',
    ],

    'errors' => [
        'too_many_rows' => 'Tiedostossa on yli :limit riviä, mikä on välittömän tuonnin raja. Jaa tiedosto osiin tai nosta asetusta filament-import.sync_row_limit.',
        'unexpected' => 'Odottamaton virhe. Riviä ei tuotu.',
        'unreadable' => 'Tiedostoa ei voitu lukea. Lataa .xlsx- tai .csv-tiedosto, jossa on otsikkorivi.',
        'no_headers' => 'Tiedostossa ei ole otsikkoriviä.',
        'legacy_xls' => 'Tämä on vanhanmallinen .xls-tiedosto. Avaa se Excelissä, tallenna se .xlsx-muodossa ja lataa se uudelleen.',
        'too_large_uncompressed' => 'Purettuna tämä taulukko sisältää liikaa dataa turvallisesti luettavaksi. Jaa se pienempiin tiedostoihin.',
    ],

    'failures_file' => [
        'row_column' => 'Rivi',
        'errors_column' => 'Virheet',
    ],

];
