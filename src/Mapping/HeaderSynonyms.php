<?php

namespace RomanSulzhyk\FilamentImport\Mapping;

/**
 * Common field names in the languages spreadsheets most often arrive in.
 *
 * A header matches a synonym only when the whole header equals it after
 * normalization, never by containment or similar spelling, so "Місто" fills a
 * `city` column but "Місто доставки" is left for the user to map.
 *
 * Each word belongs to exactly one field, and each field to a disjoint set of
 * English keys, so related fields (phone and mobile, address and street, notes
 * and comment) never trade values. A word that could still reach two columns
 * is suggested for neither; see HeuristicColumnMatcher. Words that usually
 * mean something else in real exports ("Estado" is often a state, "Пошта" is
 * often the delivery carrier) are deliberately absent.
 */
class HeaderSynonyms
{
    /** @var array<string, array{keys: list<string>, words: list<string>, given?: list<string>}> */
    public const GROUPS = [
        'name' => [
            'keys' => ['name', 'fullname'],
            'words' => ['ПІБ', 'Назва', 'ФИО', 'Название', 'Vollständiger Name', 'Nom complet', 'Nombre completo', 'Nome completo', 'Imię i nazwisko', 'Naam'],
            // First names only. They fill a full-name field only when the file
            // has no surname column, or the stored name would lose the surname.
            'given' => ["Ім'я", 'Імя', 'Имя', 'Nombre', 'Nome', 'Imię'],
        ],
        'first_name' => [
            'keys' => ['firstname', 'givenname'],
            'words' => ["Ім'я", 'Імя', 'Имя', 'Vorname', 'Prénom', 'Imię', 'Voornaam'],
        ],
        'last_name' => [
            'keys' => ['lastname', 'surname', 'familyname'],
            'words' => ['Прізвище', 'Фамилия', 'Nachname', 'Nom de famille', 'Apellido', 'Apellidos', 'Cognome', 'Sobrenome', 'Nazwisko', 'Achternaam'],
        ],
        'email' => [
            'keys' => ['email', 'emailaddress', 'mail'],
            'words' => ['Ел. пошта', 'Електронна пошта', 'Эл. почта', 'Электронная почта', 'E-Mail-Adresse', 'Courriel', 'Adresse e-mail', 'Correo electrónico', 'Posta elettronica', 'E-poczta', 'Adres e-mail'],
        ],
        'phone' => [
            'keys' => ['phone', 'phonenumber', 'telephone', 'tel'],
            'words' => ['Телефон', 'Тел', 'Номер телефону', 'Номер телефона', 'Telefon', 'Telefonnummer', 'Téléphone', 'Teléfono', 'Telefono', 'Telefone', 'Telefoon'],
        ],
        'mobile' => [
            'keys' => ['mobile', 'mobilephone', 'mobilenumber', 'cell', 'cellphone'],
            'words' => ['Мобільний', 'Мобильный', 'Handy', 'Móvil', 'Cellulare', 'Celular'],
        ],
        'city' => [
            'keys' => ['city', 'town'],
            'words' => ['Місто', 'Город', 'Stadt', 'Ort', 'Ville', 'Ciudad', 'Città', 'Cidade', 'Miasto', 'Stad'],
        ],
        'country' => [
            'keys' => ['country'],
            'words' => ['Країна', 'Страна', 'Land', 'Pays', 'País', 'Paese', 'Kraj'],
        ],
        'address' => [
            'keys' => ['address', 'streetaddress', 'address1', 'addressline1'],
            'words' => ['Адреса', 'Адрес', 'Adresse', 'Anschrift', 'Dirección', 'Direccion', 'Indirizzo', 'Endereço', 'Adres'],
        ],
        'street' => [
            'keys' => ['street'],
            'words' => ['Вулиця', 'Улица', 'Straße', 'Strasse', 'Calle', 'Rua', 'Ulica'],
        ],
        'postal_code' => [
            'keys' => ['zip', 'zipcode', 'postcode', 'postalcode'],
            'words' => ['Індекс', 'Поштовий індекс', 'Индекс', 'Почтовый индекс', 'PLZ', 'Postleitzahl', 'Code postal', 'Código postal', 'Codice postale', 'Kod pocztowy'],
        ],
        'company' => [
            'keys' => ['company', 'companyname', 'organization', 'organisation'],
            'words' => ['Компанія', 'Компания', 'Організація', 'Организация', 'Firma', 'Unternehmen', 'Entreprise', 'Société', 'Empresa', 'Azienda', 'Bedrijf'],
        ],
        'price' => [
            'keys' => ['price'],
            'words' => ['Ціна', 'Цена', 'Preis', 'Prix', 'Precio', 'Prezzo', 'Preço', 'Cena', 'Prijs'],
        ],
        'quantity' => [
            'keys' => ['quantity', 'qty'],
            'words' => ['Кількість', 'Количество', 'Menge', 'Anzahl', 'Quantité', 'Cantidad', 'Quantità', 'Quantidade', 'Ilość', 'Aantal'],
        ],
        'description' => [
            'keys' => ['description'],
            'words' => ['Опис', 'Описание', 'Beschreibung', 'Descripción', 'Descrizione', 'Descrição', 'Opis', 'Omschrijving'],
        ],
        'title' => [
            'keys' => ['title'],
            'words' => ['Заголовок', 'Titel', 'Titre', 'Título', 'Titolo', 'Tytuł'],
        ],
        'sku' => [
            'keys' => ['sku'],
            'words' => ['Артикул', 'Artikelnummer'],
        ],
        'category' => [
            'keys' => ['category'],
            'words' => ['Категорія', 'Категория', 'Kategorie', 'Catégorie', 'Categoría', 'Categoria', 'Kategoria', 'Categorie'],
        ],
        'notes' => [
            'keys' => ['notes', 'note'],
            'words' => ['Примітка', 'Примітки', 'Примечание', 'Notiz', 'Notizen', 'Bemerkung', 'Anmerkungen', 'Remarque', 'Uwagi', 'Opmerking'],
        ],
        'comment' => [
            'keys' => ['comment', 'comments'],
            'words' => ['Коментар', 'Комментарий', 'Kommentar', 'Commentaire', 'Comentario', 'Commento', 'Comentário', 'Komentarz'],
        ],
        'status' => [
            'keys' => ['status'],
            'words' => ['Статус', 'Statut'],
        ],
        'date_of_birth' => [
            'keys' => ['dateofbirth', 'birthdate', 'birthday', 'dob'],
            'words' => ['Дата народження', 'Дата рождения', 'Geburtsdatum', 'Date de naissance', 'Fecha de nacimiento', 'Data di nascita', 'Data de nascimento', 'Data urodzenia', 'Geboortedatum'],
        ],
    ];

    /**
     * Compact normalized synonyms for a column, given the compact forms of its
     * name and guesses, and of every header in the file.
     *
     * @param  list<string>  $compactTargets
     * @param  list<string>  $compactHeaders
     * @return list<string>
     */
    public static function for(array $compactTargets, array $compactHeaders = []): array
    {
        $words = [];

        foreach (static::GROUPS as $group) {
            if (array_intersect($compactTargets, $group['keys']) === []) {
                continue;
            }

            $groupWords = $group['words'];

            if (isset($group['given']) && ! static::hasSurnameHeader($compactHeaders)) {
                $groupWords = [...$groupWords, ...$group['given']];
            }

            foreach ($groupWords as $word) {
                $words[] = HeaderNormalizer::compact($word);
            }
        }

        return array_values(array_unique(array_filter($words)));
    }

    /**
     * @param  list<string>  $compactHeaders
     */
    protected static function hasSurnameHeader(array $compactHeaders): bool
    {
        $surname = [...static::GROUPS['last_name']['keys'], ...array_map(
            fn (string $word) => HeaderNormalizer::compact($word),
            static::GROUPS['last_name']['words'],
        )];

        return array_intersect($compactHeaders, $surname) !== [];
    }
}
