<?php

return [

    'action' => [
        'label' => 'Importar',
        'modal_heading' => 'Importar :label',
        'submit' => 'Importar',
        'download_template' => 'Transferir modelo',
    ],

    'steps' => [
        'upload' => 'Carregar ficheiro',
        'mapping' => 'Associar colunas',
    ],

    'fields' => [
        'file' => [
            'label' => 'Folha de cálculo',
            'helper' => 'Um ficheiro .xlsx ou .csv. A primeira linha não vazia deve conter os cabeçalhos das colunas.',
        ],
        'mapping' => [
            'helper' => 'Escolha que coluna do seu ficheiro preenche cada campo. As correspondências foram sugeridas automaticamente; deixe um campo vazio para o ignorar.',
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
        'download_failures' => 'Descarregar linhas com falha',
    ],

    'errors' => [
        'too_many_rows' => 'Este ficheiro tem mais de :limit linhas, que é o limite para uma importação imediata. Divida o ficheiro ou aumente filament-import.sync_row_limit.',
        'unexpected' => 'Erro inesperado. A linha não foi importada.',
        'unreadable' => 'Não foi possível ler o ficheiro. Carregue um ficheiro .xlsx ou .csv com uma linha de cabeçalho.',
        'no_headers' => 'O ficheiro não tem linha de cabeçalho.',
        'legacy_xls' => 'Este é um ficheiro .xls de formato antigo. Abra-o no Excel, guarde-o como .xlsx e carregue-o novamente.',
        'too_large_uncompressed' => 'Depois de descomprimida, esta folha de cálculo contém demasiados dados para ser lida em segurança. Divida-a em ficheiros mais pequenos.',
    ],

    'failures_file' => [
        'row_column' => 'Linha',
        'errors_column' => 'Erros',
    ],

];
