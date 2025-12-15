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

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeは有効なURLではありません。',
    'after' => ':attributeは:dateより後の日付にしてください。',
    'after_or_equal' => ':attributeは:date以降の日付にしてください。',
    'alpha' => ':attributeは英字のみで入力してください。',
    'alpha_dash' => ':attributeは英数字・ハイフン・アンダースコアのみで入力してください。',
    'alpha_num' => ':attributeは英数字のみで入力してください。',
    'array' => ':attributeは配列で入力してください。',
    'ascii' => ':attributeは半角英数字・記号のみで入力してください。',
    'before' => ':attributeは:dateより前の日付にしてください。',
    'before_or_equal' => ':attributeは:date以前の日付にしてください。',
    'between' => [
        'array' => ':attributeは:min個から:max個までにしてください。',
        'file' => ':attributeは:min KBから:max KBまでにしてください。',
        'numeric' => ':attributeは:minから:maxまでにしてください。',
        'string' => ':attributeは:min文字から:max文字までにしてください。',
    ],
    'boolean' => ':attributeは真偽値で入力してください。',
    'can' => ':attributeに権限がありません。',
    'confirmed' => ':attributeの確認が一致しません。',
    'contains' => ':attributeに有効な値が含まれていません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeは有効な日付ではありません。',
    'date_equals' => ':attributeは:dateと同じ日付にしてください。',
    'date_format' => ':attributeは:format形式で入力してください。',
    'decimal' => ':attributeは小数点以下:decimal桁で入力してください。',
    'declined' => ':attributeを拒否してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否してください。',
    'different' => ':attributeは:otherと異なる値にしてください。',
    'digits' => ':attributeは:digits桁で入力してください。',
    'digits_between' => ':attributeは:min桁から:max桁までにしてください。',
    'dimensions' => ':attributeの画像サイズが不正です。',
    'distinct' => ':attributeは重複した値は入力できません。',
    'doesnt_end_with' => ':attributeは:valuesで終わらない値にしてください。',
    'doesnt_start_with' => ':attributeは:valuesで始まらない値にしてください。',
    'email' => ':attributeは有効なメールアドレスで入力してください。',
    'ends_with' => ':attributeは:valuesのいずれかで終わる値にしてください。',
    'enum' => '選択された:attributeは無効です。',
    'exists' => '選択された:attributeは無効です。',
    'extensions' => ':attributeは以下の拡張子のいずれかである必要があります: :values。',
    'file' => ':attributeはファイルで入力してください。',
    'filled' => ':attributeは必須です。',
    'gt' => [
        'array' => ':attributeは:value個より多くしてください。',
        'file' => ':attributeは:value KBより大きくしてください。',
        'numeric' => ':attributeは:valueより大きくしてください。',
        'string' => ':attributeは:value文字より多くしてください。',
    ],
    'gte' => [
        'array' => ':attributeは:value個以上にしてください。',
        'file' => ':attributeは:value KB以上にしてください。',
        'numeric' => ':attributeは:value以上にしてください。',
        'string' => ':attributeは:value文字以上にしてください。',
    ],
    'hex_color' => ':attributeは有効な16進色コードで入力してください。',
    'image' => ':attributeは画像ファイルで入力してください。',
    'in' => '選択された:attributeは無効です。',
    'in_array' => ':attributeは:otherに含まれていません。',
    'integer' => ':attributeは整数で入力してください。',
    'ip' => ':attributeは有効なIPアドレスで入力してください。',
    'ipv4' => ':attributeは有効なIPv4アドレスで入力してください。',
    'ipv6' => ':attributeは有効なIPv6アドレスで入力してください。',
    'json' => ':attributeは有効なJSON文字列で入力してください。',
    'list' => ':attributeはリストで入力してください。',
    'lowercase' => ':attributeは小文字で入力してください。',
    'lt' => [
        'array' => ':attributeは:value個より少なくしてください。',
        'file' => ':attributeは:value KBより小さくしてください。',
        'numeric' => ':attributeは:valueより小さくしてください。',
        'string' => ':attributeは:value文字より少なくしてください。',
    ],
    'lte' => [
        'array' => ':attributeは:value個以下にしてください。',
        'file' => ':attributeは:value KB以下にしてください。',
        'numeric' => ':attributeは:value以下にしてください。',
        'string' => ':attributeは:value文字以下にしてください。',
    ],
    'mac_address' => ':attributeは有効なMACアドレスで入力してください。',
    'max' => [
        'array' => ':attributeは:max個以下にしてください。',
        'file' => ':attributeは:max KB以下にしてください。',
        'numeric' => ':attributeは:max以下にしてください。',
        'string' => ':attributeは:max文字以下にしてください。',
    ],
    'max_digits' => ':attributeは:max桁以下で入力してください。',
    'mimes' => ':attributeは:values形式のファイルで入力してください。',
    'mimetypes' => ':attributeは:values形式のファイルで入力してください。',
    'min' => [
        'array' => ':attributeは:min個以上にしてください。',
        'file' => ':attributeは:min KB以上にしてください。',
        'numeric' => ':attributeは:min以上にしてください。',
        'string' => ':attributeは:min文字以上にしてください。',
    ],
    'min_digits' => ':attributeは:min桁以上で入力してください。',
    'missing' => ':attributeは入力しないでください。',
    'missing_if' => ':otherが:valueの場合、:attributeは入力しないでください。',
    'missing_unless' => ':otherが:valuesでない場合、:attributeは入力しないでください。',
    'missing_with' => ':valuesが入力されている場合、:attributeは入力しないでください。',
    'missing_with_all' => ':valuesがすべて入力されている場合、:attributeは入力しないでください。',
    'multiple_of' => ':attributeは:valueの倍数で入力してください。',
    'not_in' => '選択された:attributeは無効です。',
    'not_regex' => ':attributeの形式が正しくありません。',
    'numeric' => ':attributeは数値で入力してください。',
    'password' => [
        'letters' => ':attributeには少なくとも1文字の英字を含めてください。',
        'mixed' => ':attributeには少なくとも1文字の大文字と1文字の小文字を含めてください。',
        'numbers' => ':attributeには少なくとも1文字の数字を含めてください。',
        'symbols' => ':attributeには少なくとも1文字の記号を含めてください。',
        'uncompromised' => '指定された:attributeはデータ漏洩で見つかりました。別の:attributeを選択してください。',
    ],
    'present' => ':attributeは必須です。',
    'present_if' => ':otherが:valueの場合、:attributeは必須です。',
    'present_unless' => ':otherが:valuesでない場合、:attributeは必須です。',
    'present_with' => ':valuesが入力されている場合、:attributeは必須です。',
    'present_with_all' => ':valuesがすべて入力されている場合、:attributeは必須です。',
    'prohibited' => ':attributeは入力できません。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは入力できません。',
    'prohibited_unless' => ':otherが:valuesでない場合、:attributeは入力できません。',
    'prohibits' => ':attributeが入力されている場合、:otherは入力できません。',
    'regex' => ':attributeの形式が正しくありません。',
    'required' => ':attributeは必須です。',
    'required_array_keys' => ':attributeには:valuesのキーが必要です。',
    'required_if' => ':otherが:valueの場合、:attributeは必須です。',
    'required_if_accepted' => ':otherが承認されている場合、:attributeは必須です。',
    'required_if_declined' => ':otherが拒否されている場合、:attributeは必須です。',
    'required_unless' => ':otherが:valuesでない場合、:attributeは必須です。',
    'required_with' => ':valuesが入力されている場合、:attributeは必須です。',
    'required_with_all' => ':valuesがすべて入力されている場合、:attributeは必須です。',
    'required_without' => ':valuesが入力されていない場合、:attributeは必須です。',
    'required_without_all' => ':valuesがすべて入力されていない場合、:attributeは必須です。',
    'same' => ':attributeと:otherが一致しません。',
    'size' => [
        'array' => ':attributeは:size個にしてください。',
        'file' => ':attributeは:size KBにしてください。',
        'numeric' => ':attributeは:sizeにしてください。',
        'string' => ':attributeは:size文字にしてください。',
    ],
    'starts_with' => ':attributeは:valuesのいずれかで始まる値にしてください。',
    'string' => ':attributeは文字列で入力してください。',
    'timezone' => ':attributeは有効なタイムゾーンで入力してください。',
    'unique' => 'この:attributeは既に使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは大文字で入力してください。',
    'url' => ':attributeは有効なURLで入力してください。',
    'ulid' => ':attributeは有効なULIDで入力してください。',
    'uuid' => ':attributeは有効なUUIDで入力してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
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

    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'name' => '名前',
        'privacy_policy' => '利用規約',
        'current_password' => '現在のパスワード',
        'password_confirmation' => 'パスワード確認',
    ],

];
