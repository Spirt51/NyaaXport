<?php
function nxText($key)
{
    static $words = [
        "Export products" => ["Экспорт товаров", "Експорт товарів"],
        "Access key" => ["Ключ доступа", "Ключ доступу"],
        "Open" => ["Открыть", "Відкрити"],
        "Log out" => ["Выйти", "Вийти"],
        "Invalid access key." => ["Неверный ключ доступа.", "Неправильний ключ доступу."],
        "Too many attempts. Try again in 15 minutes." => [
            "Слишком много попыток. Повторите через 15 минут.",
            "Забагато спроб. Повторіть через 15 хвилин.",
        ],
        "Replace CHANGE-ME-NOW at the top of the file before use." => [
            "Перед использованием замените CHANGE-ME-NOW в начале файла.",
            "Перед використанням замініть CHANGE-ME-NOW на початку файлу.",
        ],
        "Export fields" => ["Поля выгрузки", "Поля експорту"],
        "Format" => ["Формат", "Формат"],
        "Download products" => ["Скачать товары", "Завантажити товари"],
        "Filters" => ["Фильтры", "Фільтри"],
        "Search" => ["Поиск", "Пошук"],
        "Category" => ["Категория", "Категорія"],
        "Manufacturer" => ["Производитель", "Виробник"],
        "Status" => ["Статус", "Статус"],
        "All" => ["Все", "Усі"],
        "Active" => ["Активные", "Активні"],
        "Disabled" => ["Отключённые", "Вимкнені"],
        "Min price" => ["Цена от", "Ціна від"],
        "Max price" => ["Цена до", "Ціна до"],
        "Modified from" => ["Изменён с", "Змінений від"],
        "In stock only" => ["Только в наличии", "Лише в наявності"],
        "Profile" => ["Набор полей", "Набір полів"],
        "Custom" => ["Свой набор", "Власний набір"],
        "Price list" => ["Прайс-лист", "Прайс-лист"],
        "Marketing feed" => ["Для рекламы", "Для реклами"],
        "Full export" => ["Все поля", "Усі поля"],
        "Variants" => ["Варианты", "Варіанти"],
        "Base products" => ["Основные товары", "Основні товари"],
        "Products + option values" => ["Товары и значения опций", "Товари та значення опцій"],
        "Option values only" => ["Только значения опций", "Лише значення опцій"],
        "Data language" => ["Язык товаров", "Мова товарів"],
        "Store" => ["Витрина", "Вітрина"],
        "Default store" => ["Основная витрина", "Основна вітрина"],
        "Auto" => ["Автоматически", "Автоматично"],
        "Tables" => ["Таблицы", "Таблиці"],
        "Feeds" => ["Фиды", "Фіди"],
        "Diagnostics" => ["Диагностика", "Діагностика"],
        "No products matched your filters." => [
            "По этим фильтрам товары не найдены.",
            "За цими фільтрами товарів не знайдено.",
        ],
        "Preparing file…" => ["Подготавливаем файл…", "Готуємо файл…"],
        "Download started." => ["Скачивание началось.", "Завантаження почалося."],
        "Export failed. Review the error and try again." => [
            "Выгрузка не удалась. Проверьте ошибку и повторите.",
            "Експорт не вдався. Перевірте помилку та повторіть.",
        ],
        "Support the project" => ["Поддержать проект", "Підтримати проєкт"],
        "If NyaaXport helps you, you can support its development." => [
            "Если NyaaXport вам полезен, вы можете поддержать его разработку.",
            "Якщо NyaaXport вам корисний, ви можете підтримати його розробку.",
        ],
        "Network" => ["Сеть", "Мережа"],
        "Address" => ["Адрес", "Адреса"],
        "Fund via PayPal" => ["Поддержать через PayPal", "Підтримати через PayPal"],
        "Check the network and address before sending cryptocurrency." => [
            "Перед переводом криптовалюты проверьте сеть и адрес.",
            "Перед переказом криптовалюти перевірте мережу та адресу.",
        ],
        "Delete the exporter from the server after use." => [
            "После работы удалите экспортёр с сервера.",
            "Після роботи видаліть експортер із сервера.",
        ],
        "Platform" => ["Платформа", "Платформа"],
        "API token" => ["API-токен", "API-токен"],
        "Store domain" => ["Домен магазина", "Домен магазину"],
        "JSON endpoint" => ["Адрес JSON API", "Адреса JSON API"],
        "Credentials are sent only to the selected HTTPS API for this request." => [
            "Токен используется только для HTTPS API выбранного магазина в этом запросе.",
            "Токен використовується лише для HTTPS API вибраного магазину в цьому запиті.",
        ],
        "Cloud APIs" => ["Облачные API", "Хмарні API"],
        "Option values are separate rows, not combinations. Other CMS export base products." => [
            "Значения опций выгружаются отдельными строками, без комбинаций. Другие CMS выгружают основные товары.",
            "Значення опцій експортуються окремими рядками, без комбінацій. Інші CMS експортують основні товари.",
        ],
        "Generic XML is not a marketplace-specific feed. Validate feeds before importing." => [
            "Универсальный XML не является фидом конкретного маркетплейса. Проверяйте фиды перед импортом.",
            "Універсальний XML не є фідом конкретного маркетплейсу. Перевіряйте фіди перед імпортом.",
        ],
        "Experimental adapters: verify prices, URLs and stock before use." => [
            "Экспериментальные адаптеры: проверьте цены, URL и остатки перед использованием.",
            "Експериментальні адаптери: перевірте ціни, URL та залишки перед використанням.",
        ],
        "Light / dark" => ["Светлая / тёмная тема", "Світла / темна тема"],
        "Database" => ["База данных", "База даних"],
        "Temporary directory" => ["Временная папка", "Тимчасова тека"],
        "missing" => ["отсутствует", "відсутній"],
        "not writable" => ["нет права записи", "немає права запису"],
        "missing (XLSX unavailable)" => ["отсутствует (XLSX недоступен)", "відсутній (XLSX недоступний)"],
        "Select at least one field." => ["Выберите хотя бы одно поле.", "Виберіть хоча б одне поле."],
        "Name" => ["Название", "Назва"],
        "SKU" => ["Артикул", "Артикул"],
        "Price" => ["Цена", "Ціна"],
        "Sale price" => ["Акционная цена", "Акційна ціна"],
        "Currency" => ["Валюта", "Валюта"],
        "Quantity" => ["Количество", "Кількість"],
        "Description" => ["Описание", "Опис"],
        "Image" => ["Изображение", "Зображення"],
        "Barcode" => ["Штрихкод", "Штрихкод"],
        "Modified" => ["Дата изменения", "Дата зміни"],
        "Invalid security token. Reload the page." => [
            "Неверный токен безопасности. Обновите страницу.",
            "Неправильний токен безпеки. Оновіть сторінку.",
        ],
    ];
    $lang = isset($_SESSION["nyaaxport_lang"]) ? $_SESSION["nyaaxport_lang"] : "en";
    return $lang === "en" || !isset($words[$key]) ? $key : $words[$key][$lang === "uk" ? 1 : 0];
}
function nxEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function nxExportError($message)
{
    return nxText("Export failed. Review the error and try again.") . " " . $message;
}
function nxBoot($name)
{
    date_default_timezone_set(ini_get("date.timezone") ?: "UTC");
    ini_set("session.use_strict_mode", "1");
    ini_set("session.cookie_httponly", "1");
    ini_set("session.cookie_secure", !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "1" : "0");
    if (PHP_VERSION_ID >= 70300) {
        ini_set("session.cookie_samesite", "Strict");
    }
    session_name($name);
    session_start();
    $ttl = defined("NYAAXPORT_SESSION_TTL") ? NYAAXPORT_SESSION_TTL : 1800;
    if (isset($_SESSION["nx_seen"]) && time() - $_SESSION["nx_seen"] > $ttl) {
        unset($_SESSION["nyaaxport_ok"]);
    }
    $_SESSION["nx_seen"] = time();
    $lang = isset($_GET["lang"])
        ? $_GET["lang"]
        : (isset($_SESSION["nyaaxport_lang"])
            ? $_SESSION["nyaaxport_lang"]
            : substr(
                isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : "en",
                0,
                2
            ));
    $_SESSION["nyaaxport_lang"] = in_array($lang, ["ru", "uk", "en"], true) ? $lang : "en";
    if (empty($_SESSION["nyaaxport_csrf"])) {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(24);
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $strong = false;
            $bytes = openssl_random_pseudo_bytes(24, $strong);
            if (!$strong || $bytes === false) {
                http_response_code(500);
                exit("OpenSSL secure random generator is unavailable.");
            }
        } else {
            http_response_code(500);
            exit("Enable the PHP OpenSSL extension for PHP 5.6.");
        }
        $_SESSION["nyaaxport_csrf"] = bin2hex($bytes);
    }
    header("Cache-Control: no-store");
    header("X-Robots-Tag: noindex, nofollow");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: no-referrer");
}
function nxCsrf()
{
    return isset($_POST["csrf"]) &&
        is_string($_POST["csrf"]) &&
        hash_equals($_SESSION["nyaaxport_csrf"], $_POST["csrf"]);
}
function nxCsrfField()
{
    return '<input type="hidden" name="csrf" value="' . nxEscape($_SESSION["nyaaxport_csrf"]) . '">';
}
function nxLogin($key, $scope)
{
    if (isset($_POST["logout"]) && nxCsrf()) {
        $_SESSION = [];
        session_regenerate_id(true);
        header("Location: " . strtok($_SERVER["REQUEST_URI"], "?"));
        exit();
    }
    if (!isset($_POST["access_key"])) {
        return null;
    }
    if (!nxCsrf()) {
        return nxText("Invalid security token. Reload the page.");
    }
    // A locked file keeps the IP limit independent of browser cookies and concurrent requests.
    $id = hash(
        "sha256",
        __FILE__ . "|" . $scope . "|" . (isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "cli")
    );
    $file = sys_get_temp_dir() . "/nyaaxport_auth_" . $id;
    $fh = fopen($file, "c+");
    if (!$fh || !flock($fh, LOCK_EX)) {
        throw new RuntimeException("Cannot access login throttle storage.");
    }
    @chmod($file, 0600);
    $state = json_decode(stream_get_contents($fh), true);
    if (!is_array($state) || empty($state["until"]) || $state["until"] <= time()) {
        $state = ["attempts" => 0, "until" => time() + 900];
    }
    $error = null;
    $maxAttempts = defined("NYAAXPORT_MAX_LOGIN_ATTEMPTS") ? NYAAXPORT_MAX_LOGIN_ATTEMPTS : 8;
    if ($state["attempts"] >= $maxAttempts) {
        $error = nxText("Too many attempts. Try again in 15 minutes.");
    } elseif (
        $key !== "CHANGE-ME-NOW" &&
        is_string($_POST["access_key"]) &&
        hash_equals($key, $_POST["access_key"])
    ) {
        session_regenerate_id(true);
        $_SESSION["nyaaxport_ok"] = true;
        $state["attempts"] = 0;
    } else {
        $state["attempts"]++;
        $error = nxText("Invalid access key.");
    }
    rewind($fh);
    ftruncate($fh, 0);
    fwrite($fh, json_encode($state));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return $error;
}
function nxDownload($format, $writer, $headers)
{
    $file = tmpfile();
    if (!$file) {
        throw new RuntimeException("Cannot create temporary export file.");
    }
    $level = ob_get_level();
    set_error_handler(function ($severity, $message, $fileName, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $fileName, $line);
    });
    try {
        ob_start(function ($chunk) use ($file) {
            if (fwrite($file, $chunk) !== strlen($chunk)) {
                throw new RuntimeException("Cannot write export file.");
            }
            return "";
        }, 65536);
        call_user_func($writer);
        ob_end_flush();
    } finally {
        while (ob_get_level() > $level) {
            ob_end_clean();
        }
        restore_error_handler();
    }
    rewind($file);
    call_user_func($headers, $format);
    header("Content-Length: " . fstat($file)["size"]);
    fpassthru($file);
    fclose($file);
}
function nxHead($title)
{
    $lang = $_SESSION["nyaaxport_lang"]; ?><!doctype html>
<html lang="<?= nxEscape(
    $lang
) ?>"><head><meta charset="utf-8"><meta name="robots" content="noindex, nofollow"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= nxEscape($title) ?></title>
<style>/* NX_UI_CSS */</style></head><body><main class="wrap">
<nav class="langs" aria-label="Language"><?php foreach (
    ["ru" => "RU", "uk" => "UA", "en" => "EN"]
    as $lc => $ll
): ?><a href="?lang=<?= $lc ?>" aria-current="<?= $lang === $lc
    ? "true"
    : "false" ?>"><?= $ll ?></a><?php endforeach; ?><button type="button" id="theme"><?= nxEscape(nxText("Light / dark")) ?></button></nav>
<?php
}
function nxFooter()
{
    ?><footer class="support"><h2><?= nxEscape(nxText("Support the project")) ?></h2><p><?= nxEscape(
    nxText("If NyaaXport helps you, you can support its development.")
) ?></p>
<table><thead><tr><th scope="col"><?= nxEscape(nxText("Network")) ?></th><th scope="col"><?= nxEscape(
    nxText("Address")
) ?></th></tr></thead><tbody>
<?php foreach (
    [
        "USDT (TRC20)" => "TR3fkALFRTDyRaHsDU8aUmANGPRDy2gU1s",
        "ETH (ERC20)" => "0x9d20248c779dcb742f7795a0c7461346c0c8934e",
        "BTC" => "1BQwypiKfhNGb1ryKzdp7TBPL4wR6ckU4J",
    ]
    as $network => $address
): ?><tr><td><?= nxEscape($network) ?></td><td><?= nxEscape($address) ?></td></tr><?php endforeach; ?>
<tr><td>PayPal</td><td><a class="btn paypal-btn" href="https://www.paypal.com/donate/?business=spirt51%40hotmail.com&amp;currency_code=USD" target="_blank" rel="noopener noreferrer"><?= nxEscape(
    nxText("Fund via PayPal")
) ?></a></td></tr></tbody></table>
<p class="hint"><?= nxEscape(
    nxText("Check the network and address before sending cryptocurrency.")
) ?></p><p class="author">NyaaXport 1.1.0 · Yevhen Levchenko</p></footer></main>
<script>
(function(){
var root=document.documentElement;try{root.dataset.theme=localStorage.getItem('nx_theme')||'light'}catch(e){}
document.getElementById('theme').onclick=function(){root.dataset.theme=root.dataset.theme==='dark'?'light':'dark';try{localStorage.setItem('nx_theme',root.dataset.theme)}catch(e){}};
var profile=document.getElementById('profile');if(profile)profile.onchange=function(){var sets={price:['name','sku','url','price','special_price','currency','quantity'],marketing:['id','name','sku','url','price','special_price','currency','quantity','manufacturer','category','description','image','barcode'],full:null};if(!Object.prototype.hasOwnProperty.call(sets,profile.value))return;var keys=sets[profile.value];document.querySelectorAll('.export-field').forEach(function(c){c.checked=keys===null||keys.indexOf(c.value)>=0})};
var form=document.querySelector('form[data-export]');if(!form||!window.fetch)return;
form.addEventListener('submit',function(e){e.preventDefault();var status=document.getElementById('export-status'),button=form.querySelector('[name=export]');var data=new FormData(form);data.set('export','1');data.set('ajax','1');button.disabled=true;status.textContent=<?= json_encode(
    nxText("Preparing file…"),
    JSON_HEX_TAG | JSON_HEX_AMP
) ?>;
fetch(location.href,{method:'POST',body:data,credentials:'same-origin'}).then(function(response){if(!response.ok)return response.text().then(function(message){throw new Error(message)});var disposition=response.headers.get('Content-Disposition')||'';if(disposition.indexOf('attachment')<0)throw new Error(<?= json_encode(
    nxText("Export failed. Review the error and try again."),
    JSON_HEX_TAG
) ?>);return response.blob().then(function(blob){var url=URL.createObjectURL(blob),link=document.createElement('a');link.href=url;var match=disposition.match(/filename="([^"]+)"/);link.download=match?match[1]:'products';link.click();setTimeout(function(){URL.revokeObjectURL(url)},60000);status.textContent=<?= json_encode(
    nxText("Download started."),
    JSON_HEX_TAG
) ?>})}).catch(function(error){status.textContent=error.message}).then(function(){button.disabled=false})});
})();
</script></body></html><?php
}
