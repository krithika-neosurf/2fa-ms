<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'L\'attribut :attribute doit être accepté.',
    'active_url' => 'L\'attribut :attribute n\'est pas un URL valide.',
    'after' => 'L\'attribut :attribute doit être une date postérieure au :date.',
    'after_or_equal' => 'L\'attribut :attribute doit être une date postérieure ou égale au :date.',
    'alpha' => 'L\'attribut :attribute ne peut contenir que des lettres.',
    'alpha_dash' => 'L\'attribut :attribute ne peut contenir que des lettres, chiffres et les caractères - et _.',
    'alpha_num' => 'L\'attribut :attribute ne peut contenir que des lettres et des chiffres.',
    'array' => 'L\'attribut :attribute doit être un tableau.',
    'before' => 'L\'attribut :attribute doit être une date antérieure au :date.',
    'before_or_equal' => 'L\'attribut :attribute doit être une date antérieure ou égale au :date.',
    'between' => [
        'numeric' => 'L\'attribut :attribute doit être entre :min et :max.',
        'file' => 'L\'attribut :attribute doit avoir une taille entre :min et :max kilo octets.',
        'string' => 'L\'attribut :attribute doit être entre :min et :max caractères.',
        'array' => 'L\'attribut :attribute doit contenir entre :min et :max éléments.',
    ],
    'boolean' => 'L\'attribut :attribute n\'accepte que les valeurs Vrai et Faux.',
    'confirmed' => 'Les mots de passe doivent correspondre',
    'date' => 'L\'attribut :attribute n\'est pas une date valide.',
    'date_equals' => 'L\'attribut :attribute doit être une date égale au :date.',
    'date_format' => 'L\'attribut :attribute ne correspond pas au format :format.',
    'different' => 'L\'attribut :attribute et :other doivent être différents.',
    'digits' => 'L\'attribut :attribute doit être de :digits chiffres.',
    'digits_between' => 'Le nombre de chiffres de l\'attribut :attribute doit être entre :min et :max chiffres.',
    'dimensions' => 'L\'attribut :attribute has invalid image dimensions.',
    'distinct' => 'L\'attribut :attribute possède une valeur duploquée.',
    'email' => 'L\'attribut :attribute doit être une adresse e-mail valide.',
    'ends_with' => 'L\'attribut :attribute doit se terminer avec l\'une des valeures suivantes :values.',
    'exists' => 'L\'attribut selectionné :attribute est invalide.',
    'file' => 'L\'attribut :attribute doit être un fichier.',
    'filled' => 'L\'attribut :attribute doit être renseigné.',
    'gt' => [
        'numeric' => 'L\'attribut :attribute doit être supérieur à :value.',
        'file' => 'L\'attribut :attribute doit avoir une taille supérieure à :value kilo octets.',
        'string' => 'L\'attribut :attribute doit être de :value caractères.',
        'array' => 'L\'attribut :attribute doit contenir plus de :value éléments.',
    ],
    'gte' => [
        'numeric' => 'L\'attribut :attribute doit être supérieur ou égal à :value.',
        'file' => 'L\'attribut :attribute doit avoir une taille supérieure ou égale à :value kilo octets.',
        'string' => 'L\'attribut :attribute doit être de :value caractères ou plus.',
        'array' => 'L\'attribut :attribute doit contenir :value éléments ou plus.',
    ],
    'image' => 'L\'attribut :attribute doit être une image.',
    'in' => 'L\'attribut selectionné :attribute est invalide.',
    'in_array' => 'L\'attribut :attribute n\'existe pas dans :other.',
    'integer' => 'L\'attribut :attribute doit être un nombre entier.',
    'ip' => 'L\'attribut :attribute doit être une adresse IP valide.',
    'ipv4' => 'L\'attribut :attribute doit être une adresse IPv4 valide.',
    'ipv6' => 'L\'attribut :attribute doit être une adresse IPv6 valide.',
    'json' => 'L\'attribut :attribute doit être une chaine de caractère JSON valide.',
    'lt' => [
        'numeric' => 'L\'attribut :attribute doit être inférieur à :value.',
        'file' => 'L\'attribut :attribute doit avoir une taille inférieure ou égale à :value kilo octets.',
        'string' => 'L\'attribut :attribute doit avoir moins de :value characters.',
        'array' => 'L\'attribut :attribute doit contenir moins de :value éléments.',
    ],
    'lte' => [
        'numeric' => 'L\'attribut :attribute doit être inférieur ou égal à :value.',
        'file' => 'L\'attribut :attribute doit avoir une taille inférieure ou égale à :value kilo octets.',
        'string' => 'L\'attribut :attribute doit être de :value caractères ou moins.',
        'array' => 'L\'attribut :attribute doit contenir plus de :value éléments.',
    ],
    'max' => [
        'numeric' => 'L\'attribut :attribute de doit pas être supérieur à :max.',
        'file' => 'L\'attribut :attribute doit avoir une taille qui ne dépasse pas :max kilo octets.',
        'string' => 'L\'attribut :attribute ne doit pas être de plus de :max caractères.',
        'array' => 'L\'attribut :attribute ne doit pas avoir plus de :max éléments.',
    ],
    'mimes' => 'L\'attribut :attribute doit être un fichier de type: :values.',
    'mimetypes' => 'L\'attribut :attribute doit être un fichier de type: :values.',
    'min' => [
        'numeric' => 'L\'attribut :attribute doit être au minimum égal à :min.',
        'file' => 'L\'attribut :attribute doit avoir une taille minimale de :min kilo octets.',
        'string' => 'L\'attribut :attribute doit avoir au moins :min caractères.',
        'array' => 'L\'attribut :attribute doit contenir au moins :min éléments.',
    ],
    'not_in' => 'L\'attribut selectionné :attribute n\'est pas valide.',
    'not_regex' => 'Le format de l\'attribut :attribute n\'est pas valide.',
    'numeric' => 'L\'attribut :attribute must be a number.',
    'password' => 'Le mot de passe est incorrect.',
    'present' => 'L\'attribut :attribute doit être présent.',
    'regex' => 'Le format de l\'attribut :attribute n\'est pas valide..',
    'required' => 'L\'attribut :attribute est nécessaire.',
    'required_if' => 'L\'attribut :attribute est nécessaire quand :other est :value.',
    'required_unless' => 'L\'attribut :attribute est nécessaire sauf si :other est dans :values.',
    'required_with' => 'L\'attribut :attribute est nécessaire quand :values est présente.',
    'required_with_all' => 'L\'attribut :attribute est nécessaire quand :values sont présentes.',
    'required_without' => 'L\'attribut :attribute est nécessaire quand :values n\'est pas présente.',
    'required_without_all' => 'L\'attribut :attribute est nécessaire quand aucune des :values ne sont présentes.',
    'same' => 'L\'attribut :attribute et :other doivent correspondre.',
    'size' => [
        'numeric' => 'L\'attribut :attribute doit être de taille :size.',
        'file' => 'L\'attribut :attribute doit avoir une taille de :size kilo octets.',
        'string' => 'L\'attribut :attribute doit être de :size caractères.',
        'array' => 'L\'attribut :attribute doit contenir :size éléments.',
    ],
    'starts_with' => 'L\'attribut :attribute doit commencé par l\'une des valeurs suivantes: :values.',
    'string' => 'L\'attribut :attribute doit être une chaîne de caractères.',
    'timezone' => 'L\'attribut :attribute doit être une zone valide.',
    'unique' => 'L\'attribut :attribute a déjà été utilisé.',
    'uploaded' => 'Le téléchargement de :attribute a échoué.',
    'url' => 'Le format de :attribute est invalide.',
    'uuid' => 'L\'attribut :attribute doit être un UUID valide.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
