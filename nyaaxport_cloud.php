<?php
if (PHP_VERSION_ID < 70000 || PHP_VERSION_ID >= 90000) {
    http_response_code(500);
    exit("PHP 7.x or 8.x required.");
}
/** NyaaXport Cloud API exporter. Author: Yevhen Levchenko. */
const NYAAXPORT_CLOUD_KEY = "CHANGE-ME-NOW";
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
<style>/*! tailwindcss v4.3.0 | MIT License | https://tailwindcss.com */
@layer properties{@supports (((-webkit-hyphens:none)) and (not (margin-trim:inline))) or ((-moz-orient:inline) and (not (color:rgb(from red r g b)))){*,:before,:after,::backdrop{--tw-border-style:solid;--tw-blur:initial;--tw-brightness:initial;--tw-contrast:initial;--tw-grayscale:initial;--tw-hue-rotate:initial;--tw-invert:initial;--tw-opacity:initial;--tw-saturate:initial;--tw-sepia:initial;--tw-drop-shadow:initial;--tw-drop-shadow-color:initial;--tw-drop-shadow-alpha:100%;--tw-drop-shadow-size:initial}}}@layer theme{:root,:host{--font-sans:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";--font-mono:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;--default-font-family:var(--font-sans);--default-mono-font-family:var(--font-mono)}}@layer base{*,:after,:before,::backdrop{box-sizing:border-box;border:0 solid;margin:0;padding:0}::file-selector-button{box-sizing:border-box;border:0 solid;margin:0;padding:0}html,:host{-webkit-text-size-adjust:100%;tab-size:4;line-height:1.5;font-family:var(--default-font-family,-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji");font-feature-settings:var(--default-font-feature-settings,normal);font-variation-settings:var(--default-font-variation-settings,normal);-webkit-tap-highlight-color:transparent}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;-webkit-text-decoration:inherit;-webkit-text-decoration:inherit;-webkit-text-decoration:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,samp,pre{font-family:var(--default-mono-font-family,ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace);font-feature-settings:var(--default-mono-font-feature-settings,normal);font-variation-settings:var(--default-mono-font-variation-settings,normal);font-size:1em}small{font-size:80%}sub,sup{vertical-align:baseline;font-size:75%;line-height:0;position:relative}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}:-moz-focusring:where(:not(iframe)){outline:auto}progress{vertical-align:baseline}summary{display:list-item}ol,ul,menu{list-style:none}img,svg,video,canvas,audio,iframe,embed,object{vertical-align:middle;display:block}img,video{max-width:100%;height:auto}button,input,select,optgroup,textarea{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}::file-selector-button{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}:where(select:is([multiple],[size])) optgroup{font-weight:bolder}:where(select:is([multiple],[size])) optgroup option{padding-inline-start:20px}::file-selector-button{margin-inline-end:4px}::placeholder{opacity:1}@supports (not ((-webkit-appearance:-apple-pay-button))) or (contain-intrinsic-size:1px){::placeholder{color:currentColor}@supports (color:color-mix(in lab, red, red)){::placeholder{color:color-mix(in oklab, currentcolor 50%, transparent)}}}textarea{resize:vertical}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-date-and-time-value{min-height:1lh;text-align:inherit}::-webkit-datetime-edit{display:inline-flex}::-webkit-datetime-edit-fields-wrapper{padding:0}::-webkit-datetime-edit{padding-block:0}::-webkit-datetime-edit-year-field{padding-block:0}::-webkit-datetime-edit-month-field{padding-block:0}::-webkit-datetime-edit-day-field{padding-block:0}::-webkit-datetime-edit-hour-field{padding-block:0}::-webkit-datetime-edit-minute-field{padding-block:0}::-webkit-datetime-edit-second-field{padding-block:0}::-webkit-datetime-edit-millisecond-field{padding-block:0}::-webkit-datetime-edit-meridiem-field{padding-block:0}::-webkit-calendar-picker-indicator{line-height:1}:-moz-ui-invalid{box-shadow:none}button,input:where([type=button],[type=reset],[type=submit]){appearance:button}::file-selector-button{appearance:button}::-webkit-inner-spin-button{height:auto}::-webkit-outer-spin-button{height:auto}[hidden]:where(:not([hidden=until-found])){display:none!important}:where(:root),:root:has(input.theme-controller[value=light]:checked),[data-theme=light],:root:has(input.theme-controller[value=light]:checked),[data-theme=light]{color-scheme:light;--color-base-100:oklch(100% 0 0);--color-base-200:oklch(98% 0 0);--color-base-300:oklch(95% 0 0);--color-base-content:oklch(21% .006 285.885);--color-primary:oklch(45% .24 277.023);--color-primary-content:oklch(93% .034 272.788);--color-secondary:oklch(65% .241 354.308);--color-secondary-content:oklch(94% .028 342.258);--color-accent:oklch(77% .152 181.912);--color-accent-content:oklch(38% .063 188.416);--color-neutral:oklch(14% .005 285.823);--color-neutral-content:oklch(92% .004 286.32);--color-info:oklch(74% .16 232.661);--color-info-content:oklch(29% .066 243.157);--color-success:oklch(76% .177 163.223);--color-success-content:oklch(37% .077 168.94);--color-warning:oklch(82% .189 84.429);--color-warning-content:oklch(41% .112 45.904);--color-error:oklch(71% .194 13.428);--color-error-content:oklch(27% .105 12.094);--radius-selector:.5rem;--radius-field:.25rem;--radius-box:.5rem;--size-selector:.25rem;--size-field:.25rem;--border:1px;--depth:1;--noise:0}:root:has(input.theme-controller[value=dark]:checked),[data-theme=dark]{color-scheme:dark;--color-base-100:oklch(25.33% .016 252.42);--color-base-200:oklch(23.26% .014 253.1);--color-base-300:oklch(21.15% .012 254.09);--color-base-content:oklch(97.807% .029 256.847);--color-primary:oklch(58% .233 277.117);--color-primary-content:oklch(96% .018 272.314);--color-secondary:oklch(65% .241 354.308);--color-secondary-content:oklch(94% .028 342.258);--color-accent:oklch(77% .152 181.912);--color-accent-content:oklch(38% .063 188.416);--color-neutral:oklch(14% .005 285.823);--color-neutral-content:oklch(92% .004 286.32);--color-info:oklch(74% .16 232.661);--color-info-content:oklch(29% .066 243.157);--color-success:oklch(76% .177 163.223);--color-success-content:oklch(37% .077 168.94);--color-warning:oklch(82% .189 84.429);--color-warning-content:oklch(41% .112 45.904);--color-error:oklch(71% .194 13.428);--color-error-content:oklch(27% .105 12.094);--radius-selector:.5rem;--radius-field:.25rem;--radius-box:.5rem;--size-selector:.25rem;--size-field:.25rem;--border:1px;--depth:1;--noise:0}}@layer components;@layer utilities{@layer daisyui.l1.l2.l3{:where(.btn){width:unset}.btn{--size:calc(var(--size-field,.25rem) * 10);--btn-p:1rem;--btn-fg:var(--color-base-content);cursor:pointer;text-align:center;vertical-align:middle;outline-offset:2px;webkit-user-select:none;-webkit-user-select:none;user-select:none;border-width:var(--border);touch-action:manipulation;--btn-bg:var(--btn-color,var(--color-base-200));--btn-border:var(--btn-color,var(--color-base-200));border-start-start-radius:var(--join-ss,var(--radius-field));border-start-end-radius:var(--join-se,var(--radius-field));border-end-end-radius:var(--join-ee,var(--radius-field));border-end-start-radius:var(--join-es,var(--radius-field));flex-wrap:nowrap;flex-shrink:0;justify-content:center;align-items:center;gap:.375rem;font-weight:600;transition-property:color,background-color,border-color,box-shadow,transform;transition-duration:.2s;transition-timing-function:cubic-bezier(0,0,.2,1);display:inline-flex}@supports (color:color-mix(in lab, red, red)){.btn{--btn-border:color-mix(in oklab, var(--btn-color,var(--color-base-200)), #000 calc(var(--depth) * 5%))}}.btn{--btn-soft-bg:initial;--btn-shadow:0 3px 2px -2px var(--btn-bg), 0 4px 3px -2px var(--btn-bg)}@supports (color:color-mix(in lab, red, red)){.btn{--btn-shadow:0 3px 2px -2px color-mix(in oklab, var(--btn-bg) calc(var(--depth) * 30%), #0000), 0 4px 3px -2px color-mix(in oklab, var(--btn-bg) calc(var(--depth) * 30%), #0000)}}.btn{--btn-inset:0 .5px 0 .5px oklch(100% 0 0 / calc(var(--depth) * 6%));height:var(--size);padding-inline:var(--btn-p);font-size:var(--fontsize,.875rem);background-color:var(--btn-bg);color:var(--btn-fg);border-color:var(--btn-border);border-style:var(--btn-border-style,solid);outline-color:var(--btn-color,var(--color-base-content));--tw-prose-links:var(--btn-fg);background-image:none, var(--fx-noise);background-size:auto, calc(var(--noise,0) * 100%);text-shadow:0 .5px oklch(100% 0 0 / calc(var(--depth) * .15));box-shadow:var(--btn-inset) inset, var(--btn-shadow)}.btn:is([type=checkbox],[type=radio]){appearance:none}.btn:is([type=checkbox],[type=radio])[aria-label]:after{--tw-content:attr(aria-label);content:var(--tw-content)}.btn:where(:checked:not(.filter [type=radio].btn)){--btn-color:var(--color-primary);--btn-fg:var(--color-primary-content)}.alert{--alert-border-color:var(--color-base-200);border-radius:var(--radius-box);color:var(--color-base-content);background-color:var(--alert-color,var(--color-base-200));text-align:start;background-size:auto, calc(var(--noise) * 33%);background-image:none, var(--fx-noise);box-shadow:0 3px 0 -2px oklch(100% 0 0 / calc(var(--depth) * .08)) inset, 0 1px #000, 0 4px 3px -2px oklch(0% 0 0 / calc(var(--depth) * .08));border-style:solid;grid-template-columns:auto;grid-auto-flow:column;justify-content:start;place-items:center start;gap:1rem;padding-block:.75rem;padding-inline:1rem;font-size:.875rem;line-height:1.25rem;display:grid}@supports (color:color-mix(in lab, red, red)){.alert{box-shadow:0 3px 0 -2px oklch(100% 0 0 / calc(var(--depth) * .08)) inset, 0 1px color-mix(in oklab, color-mix(in oklab, #000 20%, var(--alert-color,var(--color-base-200))) calc(var(--depth) * 20%), #0000), 0 4px 3px -2px oklch(0% 0 0 / calc(var(--depth) * .08))}}.alert:has(>:nth-child(2)){grid-template-columns:auto minmax(auto,1fr)}}.prose :where(a.btn:not(.btn-link)):not(:where([class~=not-prose],[class~=not-prose] *)){text-decoration-line:none}@layer daisyui.l1{@media (hover:hover){.btn:hover{--btn-bg:var(--btn-color,var(--color-base-200))}@supports (color:color-mix(in lab, red, red)){.btn:hover{--btn-bg:color-mix(in oklab, var(--btn-color,var(--color-base-200)), #000 7%)}}.btn:hover{color:var(--btn-fg);--btn-border:var(--btn-bg)}@supports (color:color-mix(in lab, red, red)){.btn:hover{--btn-border:color-mix(in oklab, var(--btn-bg), #000 calc(var(--depth) * 5%))}}.btn:hover{--btn-border-style:solid;--btn-inset:0 .5px 0 .5px oklch(100% 0 0 / calc(var(--depth) * 6%));--btn-shadow:0 3px 2px -2px var(--btn-bg), 0 4px 3px -2px var(--btn-bg)}@supports (color:color-mix(in lab, red, red)){.btn:hover{--btn-shadow:0 3px 2px -2px color-mix(in oklab, var(--btn-bg) calc(var(--depth) * 30%), #0000), 0 4px 3px -2px color-mix(in oklab, var(--btn-bg) calc(var(--depth) * 30%), #0000)}}}.btn:active:not(.btn-active,[aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){--btn-bg:var(--btn-color,var(--color-base-200));translate:0 .5px}@supports (color:color-mix(in lab, red, red)){.btn:active:not(.btn-active,[aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){--btn-bg:color-mix(in oklab, var(--btn-color,var(--color-base-200)), #000 5%)}}.btn:active:not(.btn-active,[aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){color:var(--btn-fg,var(--color-base-content));--btn-border:var(--btn-color,var(--color-base-200))}@supports (color:color-mix(in lab, red, red)){.btn:active:not(.btn-active,[aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){--btn-border:color-mix(in oklab, var(--btn-color,var(--color-base-200)), #000 7%)}}.btn:active:not(.btn-active,[aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){--btn-border-style:solid;--btn-inset:0 0 0 0 oklch(0% 0 0/0);--btn-shadow:0 0 0 0 oklch(0% 0 0/0)}.btn:where(:checked:not(.filter [type=radio].btn),:not([type=radio],[type=checkbox]):focus-visible){--btn-bg:var(--btn-color,var(--color-base-200));color:var(--btn-fg,var(--color-base-content));--btn-border:var(--btn-bg)}@supports (color:color-mix(in lab, red, red)){.btn:where(:checked:not(.filter [type=radio].btn),:not([type=radio],[type=checkbox]):focus-visible){--btn-border:color-mix(in oklab, var(--btn-bg), #000 calc(var(--depth) * 5%))}}.btn:where(:checked:not(.filter [type=radio].btn),:not([type=radio],[type=checkbox]):focus-visible){--btn-border-style:solid;--btn-inset:0 .5px 0 .5px oklch(100% 0 0 / calc(var(--depth) * 6%));--btn-shadow:0 3px 2px -2px var(--btn-bg), 0 4px 3px -2px var(--btn-bg)}@supports (color:color-mix(in lab, red, red)){.btn:where(:checked:not(.filter [type=radio].btn),:not([type=radio],[type=checkbox]):focus-visible){--btn-shadow:0 3px 2px -2px color-mix(in oklab, var(--btn-bg) calc(var(--depth) * 30%), #0000), 0 4px 3px -2px color-mix(in oklab, var(--btn-bg) calc(var(--depth) * 30%), #0000)}}.btn:where(:checked:not(.filter [type=radio].btn),:not([type=radio],[type=checkbox]):focus-visible){isolation:isolate}.btn:focus-visible,.btn:has(:focus-visible){isolation:isolate;outline-width:2px;outline-style:solid}}@layer daisyui.l1.l2{.btn:is([aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){--btn-bg:var(--btn-color,var(--color-base-200))}@supports (color:color-mix(in lab, red, red)){.btn:is([aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){--btn-bg:color-mix(in oklab, var(--btn-color,var(--color-base-200)), #000 5%)}}.btn:is([aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){color:var(--btn-fg,var(--color-base-content));--btn-border:var(--btn-color,var(--color-base-200))}@supports (color:color-mix(in lab, red, red)){.btn:is([aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){--btn-border:color-mix(in oklab, var(--btn-color,var(--color-base-200)), #000 7%)}}.btn:is([aria-pressed=true],[aria-checked=true],[aria-current]:not([aria-current=false],[aria-current=""])){--btn-border-style:solid;--btn-inset:0 0 0 0 oklch(0% 0 0/0);--btn-shadow:0 0 0 0 oklch(0% 0 0/0);isolation:isolate}.btn-primary{--btn-color:var(--color-primary);--btn-fg:var(--color-primary-content);--btn-soft-bg:initial}}@layer daisyui{.btn:is(.btn-disabled,:disabled,[disabled],[aria-disabled=true]){pointer-events:none;color:var(--color-base-content)}@supports (color:color-mix(in lab, red, red)){.btn:is(.btn-disabled,:disabled,[disabled],[aria-disabled=true]){color:color-mix(in oklch, var(--color-base-content) 20%, #0000)}}.btn:is(.btn-disabled,:disabled,[disabled],[aria-disabled=true]){--btn-bg:#0000;--btn-border:#0000;--btn-inset:0 0 0 0 oklch(0% 0 0/0);--btn-shadow:0 0 0 0 oklch(0% 0 0/0);background-image:none}.btn:is(.btn-disabled,:disabled,[disabled],[aria-disabled=true]):not(.btn-link,.btn-ghost){background-color:var(--color-base-content)}@supports (color:color-mix(in lab, red, red)){.btn:is(.btn-disabled,:disabled,[disabled],[aria-disabled=true]):not(.btn-link,.btn-ghost){background-color:color-mix(in oklab, var(--color-base-content) 10%, transparent)}}}.visible{visibility:visible}.static{position:static}.alert{border-width:var(--border);border-color:var(--alert-border-color,var(--color-base-200))}.grid{display:grid}.hidden{display:none}.table{display:table}.border{border-style:var(--tw-border-style);border-width:1px}.filter{filter:var(--tw-blur,) var(--tw-brightness,) var(--tw-contrast,) var(--tw-grayscale,) var(--tw-hue-rotate,) var(--tw-invert,) var(--tw-saturate,) var(--tw-sepia,) var(--tw-drop-shadow,)}}@property --tw-border-style{syntax:"*";inherits:false;initial-value:solid}@property --tw-blur{syntax:"*";inherits:false}@property --tw-brightness{syntax:"*";inherits:false}@property --tw-contrast{syntax:"*";inherits:false}@property --tw-grayscale{syntax:"*";inherits:false}@property --tw-hue-rotate{syntax:"*";inherits:false}@property --tw-invert{syntax:"*";inherits:false}@property --tw-opacity{syntax:"*";inherits:false}@property --tw-saturate{syntax:"*";inherits:false}@property --tw-sepia{syntax:"*";inherits:false}@property --tw-drop-shadow{syntax:"*";inherits:false}@property --tw-drop-shadow-color{syntax:"*";inherits:false}@property --tw-drop-shadow-alpha{syntax:"<percentage>";inherits:false;initial-value:100%}@property --tw-drop-shadow-size{syntax:"*";inherits:false}* {
    box-sizing: border-box;
}
:root {
    color-scheme: light;
    --bg: #f5f3fa;
    --surface: #fff;
    --ink: #211b30;
    --muted: #625b70;
    --line: #d9d3e4;
    --accent: #5036a6;
}
html[data-theme="dark"] {
    color-scheme: dark;
    --bg: #19161f;
    --surface: #24202c;
    --ink: #f3eff8;
    --muted: #c0b7ce;
    --line: #52495f;
    --accent: #c6b3ff;
}
body {
    margin: 0;
    background: var(--bg);
    color: var(--ink);
    font:
        16px/1.55 system-ui,
        sans-serif;
}
.wrap {
    max-width: 1060px;
    margin: 32px auto;
    padding: 0 18px;
}
.card,
.support {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 24px;
    margin: 24px 0;
}
h1 {
    margin: 0;
    font-size: 32px;
}
h2,
h3 {
    line-height: 1.25;
}
.muted {
    color: var(--muted);
}
.langs,
.row,
.top {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}
.top {
    justify-content: space-between;
}
.langs {
    margin-bottom: 24px;
    gap: 6px;
}
a {
    color: var(--accent);
}
.btn,
button,
.langs a {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    min-height: 44px;
    padding: 9px 14px;
    border: 1px solid var(--line);
    border-radius: 6px;
    background: var(--surface);
    color: var(--ink);
    font: inherit;
    font-weight: 650;
    text-decoration: none;
    cursor: pointer;
}
button[type="submit"],
.paypal-btn {
    background: #5036a6;
    color: #fff;
}
.langs [aria-current="true"] {
    border: 2px solid var(--accent);
}
:focus-visible {
    outline: 3px solid var(--accent);
    outline-offset: 3px;
}
input,
select {
    font: inherit;
    max-width: 100%;
}
input:not([type="checkbox"]):not([type="hidden"]),
select {
    width: 100%;
    min-height: 44px;
    background: var(--surface);
    color: var(--ink);
    border: 1px solid var(--line);
    padding: 9px;
    border-radius: 5px;
}
.control {
    flex: 1;
    min-width: min(180px, 100%);
}
label {
    display: block;
    font-weight: 600;
}
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(175px, 100%), 1fr));
    gap: 10px;
    margin: 18px 0;
}
.field {
    display: flex;
    align-items: center;
    gap: 9px;
    min-height: 44px;
    border: 1px solid var(--line);
    padding: 9px;
    border-radius: 5px;
}
.filters {
    padding: 16px 0;
    border-block: 1px solid var(--line);
    margin: 24px 0;
}
.filters .row {
    margin: 12px 0;
}
.hint,
.warning,
.error,
.alert {
    padding: 12px 14px;
    border: 1px solid var(--line);
    border-radius: 6px;
    margin: 16px 0;
    overflow-wrap: anywhere;
}
.error {
    color: #9b2020;
    background: #fff0f0;
}
.hint,
.warning {
    color: var(--muted);
}
.status {
    min-height: 26px;
    margin-top: 12px;
}
.support table,
.diag table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.support td,
.support th,
.diag td {
    text-align: left;
    vertical-align: top;
    padding: 10px 6px;
    border-bottom: 1px solid var(--line);
    overflow-wrap: anywhere;
}
.support td:first-child,
.support th:first-child {
    width: 30%;
}
.support td:last-child {
    font-family: ui-monospace, monospace;
}
.author {
    text-align: center;
    margin-top: 20px;
    color: var(--muted);
}
.paypal-btn {
    font-family: system-ui, sans-serif;
}
.diag {
    margin-top: 20px;
}
summary {
    min-height: 44px;
    cursor: pointer;
}
button:disabled {
    opacity: 0.6;
    cursor: wait;
}
@media (max-width: 540px) {
    .wrap {
        padding: 0 12px;
        margin: 16px auto;
    }
    .card,
    .support {
        padding: 16px;
    }
    .support td,
    .support th {
        font-size: 12px;
        padding: 9px 4px;
    }
    .support .btn {
        white-space: normal;
        padding: 8px;
        font-size: 12px;
    }
    .row {
        align-items: stretch;
    }
    .control {
        flex-basis: 100%;
    }
}
</style></head><body><main class="wrap">
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

nxBoot("nyaaxport_cloud");
$error = nxLogin(NYAAXPORT_CLOUD_KEY, "cloud");
$ok = !empty($_SESSION["nyaaxport_ok"]);

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function xv($v)
{
    return htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function av($a, $k, $d = "")
{
    return is_array($a) && isset($a[$k]) ? $a[$k] : $d;
}
function pathv($a, $path, $d = "")
{
    foreach (explode(".", $path) as $k) {
        if (!is_array($a) || !isset($a[$k])) {
            return $d;
        }
        $a = $a[$k];
    }
    return $a;
}
function plain($v)
{
    return trim(
        preg_replace(
            "/\s+/u",
            " ",
            html_entity_decode(strip_tags((string) $v), ENT_QUOTES | ENT_HTML5, "UTF-8")
        )
    );
}
function publicApiIp($ip)
{
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return false;
    }
    if (strpos($ip, ":") !== false) {
        $packed = inet_pton($ip);
        return (ord($packed[0]) & 0xe0) === 0x20 &&
            substr($packed, 0, 2) !== hex2bin("2002") &&
            substr($packed, 0, 4) !== hex2bin("20010000");
    }
    $parts = array_map("intval", explode(".", $ip));
    if (
        $parts[0] >= 224 ||
        ($parts[0] === 100 && $parts[1] >= 64 && $parts[1] <= 127) ||
        ($parts[0] === 198 && ($parts[1] === 18 || $parts[1] === 19))
    ) {
        return false;
    }
    return true;
}
function publicApiTarget($url)
{
    $parts = parse_url($url);
    if (
        !$parts ||
        !isset($parts["scheme"], $parts["host"]) ||
        strtolower($parts["scheme"]) !== "https" ||
        isset($parts["user"]) ||
        isset($parts["pass"]) ||
        isset($parts["fragment"]) ||
        (isset($parts["port"]) && $parts["port"] !== 443)
    ) {
        throw new RuntimeException("API URL must use HTTPS on port 443 without credentials.");
    }
    $host = strtolower($parts["host"]);
    if (
        !preg_match('/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z]{2,}$/D', $host) ||
        substr($host, -6) === ".local" ||
        substr($host, -10) === ".localhost"
    ) {
        throw new RuntimeException("Use a public API hostname.");
    }
    $records = dns_get_record($host, DNS_A | DNS_AAAA);
    $ips = [];
    foreach ($records ?: [] as $record) {
        $ip = isset($record["ip"]) ? $record["ip"] : (isset($record["ipv6"]) ? $record["ipv6"] : "");
        if ($ip === "") {
            continue;
        }
        if (!publicApiIp($ip)) {
            throw new RuntimeException("Private and reserved API addresses are not allowed.");
        }
        $ips[] = $ip;
    }
    if (!$ips) {
        throw new RuntimeException("API hostname did not resolve.");
    }
    return [$host, $ips[0]];
}
function apiRequest($method, $url, $headers = [], $body = null)
{
    if (!extension_loaded("curl")) {
        throw new RuntimeException("PHP cURL extension is required.");
    }
    list($host, $ip) = publicApiTarget($url);
    foreach ($headers as $header) {
        if (strpbrk($header, "\r\n") !== false) {
            throw new RuntimeException("Invalid API header.");
        }
    }
    $ch = curl_init($url);
    $responseHeaders = [];
    $raw = "";
    $options = [
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_PROXY => "",
        CURLOPT_RESOLVE => [$host . ":443:" . (strpos($ip, ":") !== false ? "[" . $ip . "]" : $ip)],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 90,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$raw) {
            if (strlen($raw) + strlen($chunk) > 32 * 1024 * 1024) {
                return 0;
            }
            $raw .= $chunk;
            return strlen($chunk);
        },
        CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$responseHeaders) {
            $p = strpos($line, ":");
            if ($p !== false) {
                $responseHeaders[strtolower(trim(substr($line, 0, $p)))] = trim(substr($line, $p + 1));
            }
            return strlen($line);
        },
    ];
    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = is_string($body) ? $body : json_encode($body);
    }
    curl_setopt_array($ch, $options);
    $success = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($success === false) {
        throw new RuntimeException("API connection failed: " . $err);
    }
    if ($code < 200 || $code >= 300) {
        throw new RuntimeException("API HTTP " . $code . ". Check credentials, permissions and API limits.");
    }
    $json = json_decode($raw, true);
    if (!is_array($json)) {
        throw new RuntimeException("API returned invalid JSON.");
    }
    return [$json, $responseHeaders];
}
function row(
    $id,
    $name,
    $sku,
    $url,
    $price,
    $sale,
    $currency,
    $qty,
    $status,
    $brand,
    $category,
    $description,
    $image,
    $barcode = "",
    $modified = ""
) {
    if (is_array($category)) {
        $category = av($category, "caption", av($category, "name", av($category, "id", "")));
    }
    if (is_array($url)) {
        $url = rtrim(av($url, "base"), "/") . "/" . ltrim(av($url, "path"), "/");
    }
    return [
        "id" => $id,
        "name" => $name,
        "sku" => $sku,
        "url" => $url,
        "price" => $price,
        "special_price" => $sale,
        "currency" => $currency,
        "quantity" => $qty,
        "status" => $status,
        "manufacturer" => $brand,
        "category" => $category,
        "description" => plain($description),
        "image" => $image,
        "barcode" => $barcode,
        "date_modified" => $modified,
    ];
}

function shopifyQuery($domain, $token, $query, $variables)
{
    list($j) = apiRequest(
        "POST",
        "https://" . $domain . "/admin/api/2026-07/graphql.json",
        ["Content-Type: application/json", "X-Shopify-Access-Token: " . $token],
        ["query" => $query, "variables" => $variables]
    );
    if (!empty($j["errors"]) || !isset($j["data"])) {
        throw new RuntimeException("Shopify GraphQL query failed. Check API permissions.");
    }
    return $j["data"];
}
function shopifyRows($domain, $token)
{
    $domain = strtolower(trim(preg_replace("~^https://~i", "", trim($domain)), "/"));
    if (!preg_match('/^[a-z0-9][a-z0-9-]*\\.myshopify\\.com$/D', $domain)) {
        throw new RuntimeException("Use the store.myshopify.com domain.");
    }
    $cursor = null;
    do {
        $data = shopifyQuery(
            $domain,
            $token,
            'query Products($after:String){shop{currencyCode} products(first:10,after:$after){pageInfo{hasNextPage endCursor}nodes{id title descriptionHtml handle onlineStoreUrl vendor productType updatedAt status featuredMedia{preview{image{url}}}variants(first:50){pageInfo{hasNextPage endCursor}nodes{id sku price compareAtPrice inventoryQuantity barcode image{url}}}}}}',
            ["after" => $cursor]
        );
        if (!isset($data["products"]["nodes"])) {
            throw new RuntimeException("Shopify products missing from response.");
        }
        foreach ($data["products"]["nodes"] as $p) {
            $connection = $p["variants"];
            do {
                foreach ($connection["nodes"] as $v) {
                    $gid = $v["id"];
                    $id = substr($gid, strrpos($gid, "/") + 1);
                    $price = av($v, "price");
                    $compare = av($v, "compareAtPrice");
                    $discounted = is_numeric($compare) && (float) $compare > (float) $price;
                    yield row(
                        $id,
                        av($p, "title"),
                        av($v, "sku"),
                        av($p, "onlineStoreUrl", ""),
                        $discounted ? $compare : $price,
                        $discounted ? $price : "",
                        pathv($data, "shop.currencyCode", ""),
                        av($v, "inventoryQuantity", 0),
                        av($p, "status") === "ACTIVE",
                        av($p, "vendor"),
                        av($p, "productType"),
                        av($p, "descriptionHtml"),
                        pathv($v, "image.url", pathv($p, "featuredMedia.preview.image.url", "")),
                        av($v, "barcode"),
                        av($p, "updatedAt")
                    );
                }
                $more = pathv($connection, "pageInfo.hasNextPage", false);
                if ($more) {
                    $next = pathv($connection, "pageInfo.endCursor");
                    if (!$next || (isset($previous) && $previous === $next)) {
                        throw new RuntimeException("Shopify variant cursor did not advance.");
                    }
                    $previous = $next;
                    $vdata = shopifyQuery(
                        $domain,
                        $token,
                        'query Variants($id:ID!,$after:String){product(id:$id){variants(first:100,after:$after){pageInfo{hasNextPage endCursor}nodes{id sku price compareAtPrice inventoryQuantity barcode image{url}}}}}',
                        ["id" => $p["id"], "after" => $next]
                    );
                    $connection = pathv($vdata, "product.variants", null);
                    if (!$connection) {
                        throw new RuntimeException("Shopify variants missing from response.");
                    }
                }
            } while ($more);
            unset($previous);
        }
        $more = pathv($data, "products.pageInfo.hasNextPage", false);
        $next = pathv($data, "products.pageInfo.endCursor", null);
        if ($more && (!$next || $next === $cursor)) {
            throw new RuntimeException("Shopify product cursor did not advance.");
        }
        $cursor = $next;
    } while ($more);
}
function bigCommerceRows($hash, $token, $domain = "")
{
    $page = 1;
    do {
        list($j) = apiRequest(
            "GET",
            "https://api.bigcommerce.com/stores/" .
                rawurlencode($hash) .
                "/v3/catalog/products?include=variants,images,primary_image&limit=250&page=" .
                $page,
            ["Accept: application/json", "X-Auth-Token: " . $token]
        );
        $items = av($j, "data", []);
        foreach ($items as $p) {
            $variants = av($p, "variants", []);
            if (!$variants) {
                $variants = [[]];
            }
            foreach ($variants as $v) {
                $id = av($v, "id", av($p, "id"));
                $custom = av($p, "custom_url", []);
                $url = $domain !== "" ? rtrim($domain, "/") . av($custom, "url", "") : "";
                $img = pathv($p, "primary_image.url_standard", "");
                yield row(
                    $id,
                    av($p, "name"),
                    av($v, "sku", av($p, "sku")),
                    $url,
                    av($v, "price", av($p, "price")),
                    av($v, "sale_price", av($p, "sale_price")),
                    av($p, "currency", ""),
                    av($v, "inventory_level", av($p, "inventory_level", 0)),
                    av($p, "is_visible", 1),
                    av($p, "brand_name", ""),
                    implode(", ", av($p, "categories", [])),
                    av($p, "description"),
                    $img,
                    av($v, "upc", av($p, "upc")),
                    av($p, "date_modified")
                );
            }
        }
        $pages = (int) pathv($j, "meta.pagination.total_pages", 1);
        $page++;
    } while ($page <= $pages);
}
function promRows($token)
{
    $last = 0;
    do {
        list($j) = apiRequest("GET", "https://my.prom.ua/api/v1/products/list?limit=100&last_id=" . $last, [
            "Authorization: Bearer " . $token,
        ]);
        $items = av($j, "products", []);
        foreach ($items as $p) {
            $last = max($last, (int) av($p, "id"));
            $images = av($p, "images", []);
            yield row(
                av($p, "id"),
                av($p, "name"),
                av($p, "sku"),
                av($p, "url"),
                av($p, "price"),
                "",
                av($p, "currency"),
                av($p, "quantity_in_stock", 0),
                av($p, "status") === "on_display",
                av($p, "manufacturer"),
                av($p, "category"),
                av($p, "description"),
                isset($images[0]) ? av($images[0], "url") : ""
            );
        }
    } while (count($items) === 100);
}
function wixRows($token, $siteId)
{
    $skip = 0;
    do {
        list($j) = apiRequest(
            "POST",
            "https://www.wixapis.com/stores/v1/products/query",
            ["Content-Type: application/json", "Authorization: " . $token, "wix-site-id: " . $siteId],
            ["query" => ["paging" => ["limit" => 100, "offset" => $skip]]]
        );
        $items = av($j, "products", []);
        foreach ($items as $p) {
            $variants = pathv($p, "variants", []);
            if (!$variants) {
                $variants = [[]];
            }
            foreach ($variants as $v) {
                yield row(
                    av($v, "id", av($p, "id")),
                    av($p, "name"),
                    av($v, "sku"),
                    av($p, "productPageUrl"),
                    pathv($v, "priceData.price", pathv($p, "priceData.price", "")),
                    pathv($v, "priceData.discountedPrice", ""),
                    pathv($p, "priceData.currency", ""),
                    av($v, "quantity", 0),
                    av($p, "visible", 1),
                    av($p, "brand"),
                    "",
                    av($p, "description"),
                    pathv($p, "media.mainMedia.image.url", ""),
                    av($v, "barcode"),
                    av($p, "lastUpdatedDate")
                );
            }
        }
        $skip += count($items);
    } while (count($items) === 100);
}
function genericRows($endpoint, $token)
{
    list($j) = apiRequest("GET", $endpoint, $token !== "" ? ["Authorization: Bearer " . $token] : []);
    $items = av($j, "products", av($j, "data", $j));
    foreach ($items as $p) {
        yield row(
            av($p, "id", av($p, "product_id")),
            av($p, "name", av($p, "title")),
            av($p, "sku", av($p, "article")),
            av($p, "url"),
            av($p, "price"),
            av($p, "special_price", av($p, "sale_price")),
            av($p, "currency"),
            av($p, "quantity", av($p, "stock", 0)),
            av($p, "status", 1),
            av($p, "brand", av($p, "manufacturer")),
            av($p, "category"),
            av($p, "description"),
            av($p, "image", av($p, "image_url")),
            av($p, "barcode"),
            av($p, "updated_at")
        );
    }
}

function fields()
{
    $f = [
        "id" => "ID",
        "name" => "Name",
        "sku" => "SKU",
        "url" => "URL",
        "price" => "Price",
        "special_price" => "Sale price",
        "currency" => "Currency",
        "quantity" => "Quantity",
        "status" => "Status",
        "manufacturer" => "Manufacturer",
        "category" => "Category",
        "description" => "Description",
        "image" => "Image",
        "barcode" => "Barcode",
        "date_modified" => "Modified",
    ];
    return array_map("nxText", $f);
}
function safe($v)
{
    $v = (string) $v;
    if ($v !== "" && preg_match('/^[\x00-\x20]*[=+\-@]/', $v)) {
        $v = "'" . $v;
    }
    return $v;
}
function headersOut($format)
{
    $ext = $format === "xls" ? "xls" : $format;
    header(
        "Content-Type: " .
            ($format === "csv"
                ? "text/csv; charset=UTF-8"
                : ($format === "xml"
                    ? "application/xml; charset=UTF-8"
                    : "application/vnd.ms-excel"))
    );
    header(
        'Content-Disposition: attachment; filename="cloud_products_' . date("Y-m-d_H-i-s") . "." . $ext . '"'
    );
    header("Cache-Control: no-store");
}
function exportRows($rows, $format)
{
    $f = fields();
    if ($format === "csv") {
        echo "\xEF\xBB\xBF";
        $o = fopen("php://output", "wb");
        fputcsv($o, array_values($f), ";", '"', "\\");
        foreach ($rows as $r) {
            $x = [];
            foreach ($f as $k => $l) {
                $x[] = safe(av($r, $k));
            }
            fputcsv($o, $x, ";", '"', "\\");
        }
        fclose($o);
    } elseif ($format === "xml") {
        echo '<?xml version="1.0" encoding="UTF-8"?><products>';
        foreach ($rows as $r) {
            echo "<product>";
            foreach ($f as $k => $l) {
                echo "<" . $k . ">" . xv(av($r, $k)) . "</" . $k . ">";
            }
            echo "</product>";
        }
        echo "</products>";
    } else {
        echo "\xEF\xBB\xBF" . '<meta charset="UTF-8"><table border="1"><tr>';
        foreach ($f as $l) {
            echo "<th>" . h($l) . "</th>";
        }
        echo "</tr>";
        foreach ($rows as $r) {
            echo "<tr>";
            foreach ($f as $k => $l) {
                echo "<td>" . h(safe(av($r, $k))) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

if ($ok && isset($_POST["export"])) {
    try {
        if (!nxCsrf()) {
            throw new RuntimeException(nxText("Invalid security token. Reload the page."));
        }
        @set_time_limit(0);
        $provider = av($_POST, "provider");
        $token = trim(av($_POST, "token"));
        if ($token === "") {
            throw new RuntimeException(nxText("API token") . ": required");
        }
        if ($provider === "shopify") {
            $rows = shopifyRows(av($_POST, "domain"), $token);
        } elseif ($provider === "bigcommerce") {
            $rows = bigCommerceRows(av($_POST, "store_hash"), $token, av($_POST, "domain"));
        } elseif ($provider === "prom") {
            $rows = promRows($token);
        } elseif ($provider === "wix") {
            $rows = wixRows($token, av($_POST, "site_id"));
        } elseif ($provider === "json") {
            $rows = genericRows(av($_POST, "endpoint"), $token);
        } else {
            throw new RuntimeException("Unknown provider.");
        }
        $format = av($_POST, "format", "csv");
        if (!in_array($format, ["csv", "xml", "xls"], true)) {
            throw new RuntimeException("Unknown format.");
        }
        session_write_close();
        nxDownload(
            $format,
            function () use ($rows, $format) {
                $rows->rewind();
                if (!$rows->valid()) {
                    throw new RuntimeException(nxText("No products matched your filters."));
                }
                exportRows($rows, $format);
            },
            "headersOut"
        );
        exit();
    } catch (Throwable $e) {
        $error = nxExportError($e->getMessage());
        if (isset($_POST["ajax"])) {
            http_response_code(422);
            header("Content-Type: text/plain; charset=UTF-8");
            exit($error);
        }
    }
}
nxHead("NyaaXport Cloud");
?>
<section class="card"><div class="top"><div><h1>NyaaXport Cloud</h1><p class="muted"><?= h(
    nxText("Cloud APIs")
) ?></p></div>
<?php if ($ok): ?><form method="post"><?= nxCsrfField() ?><button type="submit" name="logout" value="1"><?= h(
    nxText("Log out")
) ?></button></form><?php endif; ?></div>
<?php if ($error): ?><div role="alert" class="alert error"><?= h($error) ?></div><?php endif; ?>
<?php if (!$ok): ?>
<form method="post"><?= nxCsrfField() ?><label for="key"><?= h(
    nxText("Access key")
) ?></label><div class="row"><div class="control"><input id="key" type="password" name="access_key" required autocomplete="current-password"></div><button type="submit"><?= h(
    nxText("Open")
) ?></button></div></form>
<?php if (NYAAXPORT_CLOUD_KEY === "CHANGE-ME-NOW"): ?><p class="hint"><?= h(
    nxText("Replace CHANGE-ME-NOW at the top of the file before use.")
) ?></p><?php endif; ?>
<?php else: ?>
<p class="hint"><?= h(nxText("Experimental adapters: verify prices, URLs and stock before use.")) ?></p>
<form method="post" data-export><?= nxCsrfField() ?>
<div class="grid">
<div class="control"><label for="provider"><?= h(
    nxText("Platform")
) ?></label><select name="provider" id="provider"><option value="shopify">Shopify</option><option value="bigcommerce">BigCommerce</option><option value="wix">Wix</option><option value="prom">Prom.ua</option><option value="json">JSON API / Horoshop (beta)</option></select></div>
<div class="control"><label for="token"><?= h(
    nxText("API token")
) ?></label><input id="token" type="password" name="token" required autocomplete="off"></div>
<div class="control" data-provider="shopify bigcommerce"><label for="domain"><?= h(
    nxText("Store domain")
) ?></label><input id="domain" name="domain" placeholder="store.myshopify.com"></div>
<div class="control" data-provider="bigcommerce"><label for="store-hash">BigCommerce store hash</label><input id="store-hash" name="store_hash"></div>
<div class="control" data-provider="wix"><label for="site-id">Wix site ID</label><input id="site-id" name="site_id"></div>
<div class="control" data-provider="json"><label for="endpoint"><?= h(
    nxText("JSON endpoint")
) ?></label><input id="endpoint" type="url" name="endpoint" placeholder="https://example.com/api/products"></div>
<div class="control"><label for="format"><?= h(
    nxText("Format")
) ?></label><select id="format" name="format"><option value="csv">CSV</option><option value="xml">XML</option><option value="xls">XLS (Excel HTML)</option></select></div>
</div>
<p class="hint"><?= h(nxText("Credentials are sent only to the selected HTTPS API for this request.")) ?></p>
<button type="submit" name="export" value="1"><?= h(nxText("Download products")) ?></button>
<div id="export-status" class="status" role="status" aria-live="polite"></div></form>
<script>
(function(){var select=document.getElementById('provider');function update(){document.querySelectorAll('[data-provider]').forEach(function(el){var active=el.dataset.provider.split(' ').indexOf(select.value)>=0;el.hidden=!active;el.querySelectorAll('input').forEach(function(input){input.disabled=!active;input.required=active&&(select.value!=='bigcommerce'||input.name==='store_hash')})})}select.onchange=update;update()})();
</script>
<?php endif; ?></section>
<?php nxFooter(); ?>
