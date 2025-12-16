// モーダル機能
$(document).ready(function() {
    console.log('app.js 読み込み完了');

    // ボタンの存在確認
    if ($('#fetch-address-btn').length === 0) {
        console.warn('住所取得ボタンが見つかりません');
    } else {
        console.log('住所取得ボタンが見つかりました');
    }

    // ログインリンクがクリックされたときにモーダルを開く
    $('#login-link').on('click', function(e) {
        e.preventDefault();
        $('#application-modal').addClass('is-active');
        $('body').css('overflow', 'hidden'); // ボディのスクロールを防止
    });

    // 閉じるボタンがクリックされたときにモーダルを閉じる
    $('#modal-close, .c-modal-overlay').on('click', function() {
        $('#application-modal').removeClass('is-active');
        $('body').css('overflow', ''); // ボディのスクロールを復元
    });

    // ESCキーでモーダルを閉じる
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#application-modal').hasClass('is-active')) {
            $('#application-modal').removeClass('is-active');
            $('body').css('overflow', '');
        }
    });

    // 日本郵便API - 郵便番号、事業所個別郵便番号、またはデジタルアドレスで住所を取得
    $(document).on('click', '#fetch-address-btn', function(e) {
        e.preventDefault();
        console.log('住所取得ボタンがクリックされました');

        let searchCode = $('#postal_code').val().trim();
        console.log('入力された検索コード:', searchCode);

        // 数値の郵便番号の場合のみハイフンを削除（デジタルアドレスはハイフンを保持）
        // デジタルアドレス形式: "A7E-2FK2" (文字と数字、ハイフンあり)
        // 郵便番号形式: "460-0001" または "4600001" (数字のみ)
        const isNumeric = /^\d+$/.test(searchCode.replace(/-/g, ''));
        if (isNumeric) {
            searchCode = searchCode.replace(/-/g, ''); // 数値コードのハイフンを削除
        }

        if (!searchCode || searchCode.length < 3) {
            Toastify({
                text: '郵便番号またはデジタルアドレスを3文字以上入力してください。',
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #ff6b6b, #ee5a6f)",
                }
            }).showToast();
            $('#postal_code').focus();
            return;
        }

        // ボタンを無効化してローディング表示
        const $btn = $(this);
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('取得中...');

        console.log('APIリクエストを送信します:', searchCode);

        // Laravelバックエンドプロキシを呼び出し（日本郵便APIを呼び出す）
        // Bladeテンプレートで設定されたwindowオブジェクトからルートURLを使用、またはフォールバック
        let apiUrl = window.japanPostApiUrl || '/api/japan-post/search';

        // 相対パスの場合は絶対パスに変換
        if (apiUrl.startsWith('/')) {
            apiUrl = window.location.origin + apiUrl;
        }

        console.log('API URL:', apiUrl);

        $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            data: {
                postal_code: searchCode,
                page: 1,
                limit: 10, // 選択用に結果を制限（最大1000件）
                choikitype: 1, // 1: 括弧無し町域フィールド, 2: 括弧有り町域フィールド
                searchtype: 1 // 1: 郵便番号、事業所個別郵便番号、デジタルアドレスを検索, 2: 郵便番号、デジタルアドレスのみ
            },
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            timeout: 15000,
            success: function(response, textStatus, xhr) {
                console.log('APIレスポンス取得成功:', response);

                // レスポンスが文字列（JWTトークンなど）の場合
                if (typeof response === 'string') {
                    console.error('予期しないレスポンス形式（文字列）:', response.substring(0, 50));
                    Toastify({
                        text: '住所の取得に失敗しました。APIレスポンスが無効な形式です。',
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff6b6b, #ee5a6f)",
                        }
                    }).showToast();
                    $btn.prop('disabled', false).text(originalText);
                    return;
                }

                // レスポンスがオブジェクトでない場合
                if (typeof response !== 'object' || response === null) {
                    console.error('予期しないレスポンス形式:', typeof response, response);
                    Toastify({
                        text: '住所の取得に失敗しました。APIレスポンスが無効な形式です。',
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff6b6b, #ee5a6f)",
                        }
                    }).showToast();
                    $btn.prop('disabled', false).text(originalText);
                    return;
                }

                fillAddressFields(response);
                $btn.prop('disabled', false).text(originalText);
            },
            error: function(xhr, status, error) {
                console.error('API Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText ? xhr.responseText.substring(0, 200) : 'なし',
                    error: error
                });

                let errorMessage = '住所の取得に失敗しました。';

                // JSONレスポンスを試みる
                let responseData = null;
                try {
                    if (xhr.responseText) {
                        responseData = JSON.parse(xhr.responseText);
                    }
                } catch (e) {
                    // JSONでない場合（JWTトークンなどが返された場合）
                    console.warn('レスポンスがJSONではありません:', xhr.responseText ? xhr.responseText.substring(0, 50) : 'なし');
                }

                if (responseData && responseData.error) {
                    errorMessage += '\n' + responseData.error;
                    if (responseData.message) {
                        errorMessage += '\n' + responseData.message;
                    }
                } else if (xhr.status === 0) {
                    errorMessage += '\nネットワークエラーが発生しました。';
                } else if (xhr.status === 404) {
                    errorMessage += '\n該当する郵便番号・住所が見つかりませんでした。';
                    errorMessage += '\n\nテスト環境では以下のサンプルが利用可能です：';
                    errorMessage += '\n・デジタルアドレス: A7E-2FK2, JN4-LKS2, QN6-GQX1';
                    errorMessage += '\n・東京都千代田区の郵便番号';
                } else if (xhr.status === 401) {
                    errorMessage += '\n認証に失敗しました。API認証情報を確認してください。';
                } else if (xhr.status === 500) {
                    errorMessage += '\nサーバーエラーが発生しました。';
                }

                Toastify({
                    text: errorMessage.replace(/\n/g, ' '),
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff6b6b, #ee5a6f)",
                    }
                }).showToast();
                $btn.prop('disabled', false).text(originalText);
            }
        });

        function fillAddressFields(response) {
            let addressData = null;
            let addressList = [];

            // 日本郵便APIのレスポンス形式を処理
            // レスポンス形式: { "addresses": [...], "searchtype": "...", "limit": 10, "count": 1, "page": 1 }

            if (response.addresses && Array.isArray(response.addresses) && response.addresses.length > 0) {
                addressList = response.addresses;
            } else if (response.data && Array.isArray(response.data) && response.data.length > 0) {
                addressList = response.data;
            } else if (response.results && Array.isArray(response.results) && response.results.length > 0) {
                addressList = response.results;
            } else if (Array.isArray(response) && response.length > 0) {
                addressList = response;
            }

            if (addressList.length === 0) {
                Toastify({
                    text: '該当する住所が見つかりませんでした。',
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff6b6b, #ee5a6f)",
                    }
                }).showToast();
                return;
            }

            // 複数の結果がある場合、最初の1つを使用（または選択ダイアログを表示可能）
            if (addressList.length > 1) {
                console.log('複数の住所が見つかりました:', addressList.length, '件');
                // ここでユーザーが選択できる選択ダイアログを表示可能
            }

            addressData = addressList[0];

            // 都道府県ドロップダウンを入力
            // 日本郵便APIフィールド: pref_name (都道府県名), pref_code (都道府県コード), pref_kana (都道府県名カナ)
            const prefectureName = addressData.pref_name || addressData.prefecture || addressData.pref || addressData.prefectureName;
            if (prefectureName) {
                let found = false;
                $('#prefecture_id option').each(function() {
                    const optionText = $(this).text().trim();
                    // 完全一致または部分一致（都/府/県の接尾辞を処理）
                    if (optionText === prefectureName ||
                        optionText.includes(prefectureName) ||
                        prefectureName.includes(optionText) ||
                        optionText.replace(/[都府県]$/, '') === prefectureName.replace(/[都府県]$/, '')) {
                        $(this).prop('selected', true);
                        found = true;
                        return false;
                    }
                });
                if (!found) {
                    console.warn('都道府県がドロップダウンに見つかりませんでした:', prefectureName);
                }
            }

            // 市区町村フィールドを入力
            // 日本郵便APIフィールド: city_name (市区町村名), city_code (市区町村コード), city_kana (市区町村名カナ)
            const cityName = addressData.city_name || addressData.city || addressData.cityName || addressData.municipality;
            if (cityName) {
                $('#city').val(cityName);
            }

            // 町名フィールドを入力
            // 日本郵便APIフィールド: town_name (町域), town_kana (町域カナ)
            const townName = addressData.town_name || addressData.town || addressData.townName || addressData.area;
            if (townName) {
                $('#town').val(townName);
            }

            // 住所の部分を解析して入力（丁目、番、号）
            // 日本郵便APIの実際のフィールド: block_name (街区名, 例: "４丁目８８"), address (住所), biz_name (事業所名)
            const blockName = addressData.block_name || ''; // 例: "４丁目８８"
            const address = addressData.address || ''; // 住所全文

            // block_nameから丁目、番、号を解析
            if (blockName) {
                console.log('block_nameを解析:', blockName);

                // 全角数字を半角に変換
                const normalizedBlock = blockName.replace(/[０-９]/g, function(s) {
                    return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                });

                // 丁目を解析 - 例: "4丁目", "４丁目"
                const chomeMatch = normalizedBlock.match(/(\d+)丁目/);
                if (chomeMatch) {
                    $('#chome').val(chomeMatch[1]);
                    console.log('丁目:', chomeMatch[1]);
                }

                // 番号を解析 - 例: "88", "８８"
                // block_nameの形式: "４丁目８８" または "８８" など
                // 丁目以降の部分を取得
                const afterChome = normalizedBlock.replace(/\d+丁目/, '').trim();
                if (afterChome) {
                    // 数字のみの場合（番のみ）
                    const numberMatch = afterChome.match(/(\d+)/);
                    if (numberMatch) {
                        $('#building_number').val(numberMatch[1]);
                        console.log('番:', numberMatch[1]);
                    }

                    // ハイフンで区切られている場合（番-号）
                    const hyphenMatch = afterChome.match(/(\d+)[-](\d+)/);
                    if (hyphenMatch) {
                        $('#building_number').val(hyphenMatch[1]);
                        $('#house_number').val(hyphenMatch[2]);
                        console.log('番-号:', hyphenMatch[1], '-', hyphenMatch[2]);
                    }
                }
            }

            // addressフィールドからも解析を試みる（block_nameがない場合）
            if (!blockName && address) {
                console.log('addressから解析:', address);
                // 丁目を解析
                const chomeMatch = address.match(/(\d+)丁目/);
                if (chomeMatch && !$('#chome').val()) {
                    $('#chome').val(chomeMatch[1]);
                }

                // 番号を解析
                const banGoPatterns = [
                    /(\d+)番(\d+)号/,            // "3番2号"
                    /(\d+)番(\d+)/,             // "3番2"
                    /(\d+)[-](\d+)[-](\d+)/,    // "2-3-1" (丁目-番-号)
                    /(\d+)[-](\d+)/,            // "7-2" (番-号)
                ];

                for (let pattern of banGoPatterns) {
                    const match = address.match(pattern);
                    if (match) {
                        if (match.length === 4) {
                            if (!$('#building_number').val()) {
                                $('#building_number').val(match[2]);
                                $('#house_number').val(match[3]);
                            }
                        } else {
                            if (!$('#building_number').val()) {
                                $('#building_number').val(match[1]);
                                $('#house_number').val(match[2]);
                            }
                        }
                        break;
                    }
                }
            }

            // 事業所名があれば建物名として入力（事業所個別郵便番号の場合）
            if (addressData.biz_name) {
                $('#apartment_name').val(addressData.biz_name);
            }

            // その他の建物情報があれば入力
            if (addressData.other_name) {
                if (!$('#apartment_name').val()) {
                    $('#apartment_name').val(addressData.other_name);
                }
            }

            // 成功メッセージを表示
            Toastify({
                text: '住所を取得しました',
                duration: 2000,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                }
            }).showToast();
        }
    });

    // 郵便番号入力の自動フォーマット（7桁の数値コードのみハイフンを追加）
    $('#postal_code').on('input', function() {
        let value = $(this).val();

        // 数値の郵便番号かどうかを確認（デジタルアドレスではない）
        const numericOnly = value.replace(/[^\d]/g, '');
        const hasLetters = /[A-Za-z]/.test(value);

        // 数値で文字がない場合のみ自動フォーマット（デジタルアドレスには文字が含まれる）
        if (!hasLetters && numericOnly.length === 7) {
            value = numericOnly.substring(0, 3) + '-' + numericOnly.substring(3, 7);
            $(this).val(value);
        }
    });
});

