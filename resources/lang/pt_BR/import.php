<?php

return [

    'action' => [
        'label' => 'Importar',
        'modal_heading' => 'Importar :label',
        'submit' => 'Importar',
        'download_template' => 'Baixar modelo',
    ],

    'steps' => [
        'upload' => 'Enviar arquivo',
        'mapping' => 'Associar colunas',
    ],

    'fields' => [
        'file' => [
            'label' => 'Planilha',
            'helper' => 'Um arquivo .xlsx ou .csv. A primeira linha não vazia deve conter os cabeçalhos das colunas.',
        ],
        'mapping' => [
            'helper' => 'Escolha qual coluna do seu arquivo preenche cada campo. As correspondências foram sugeridas automaticamente; deixe um campo vazio para ignorá-lo.',
            'placeholder' => 'Não importar',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importação concluída',
            'body' => ':created criados, :updated atualizados, :failed com falha.',
            'body_with_skipped' => ':created criados, :updated atualizados, :skipped ignorados, :failed com falha.',
        ],
        'failed' => [
            'title' => 'Não foi possível iniciar a importação',
        ],
        'download_failures' => 'Baixar linhas com falha',
    ],

    'errors' => [
        'too_many_rows' => 'Este arquivo tem mais de :limit linhas, que é o limite para uma importação imediata. Divida o arquivo ou aumente filament-import.sync_row_limit.',
        'unexpected' => 'Erro inesperado. A linha não foi importada.',
        'unreadable' => 'Não foi possível ler o arquivo. Envie um arquivo .xlsx ou .csv com uma linha de cabeçalho.',
        'no_headers' => 'O arquivo não tem linha de cabeçalho.',
        'legacy_xls' => 'Este é um arquivo .xls no formato antigo. Abra-o no Excel, salve-o como .xlsx e envie-o novamente.',
        'too_large_uncompressed' => 'Depois de descompactada, esta planilha contém dados demais para ser lida com segurança. Divida-a em arquivos menores.',
    ],

    'failures_file' => [
        'row_column' => 'Linha',
        'errors_column' => 'Erros',
    ],

];
