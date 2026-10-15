<?php
if (PHP_VERSION_ID < 70000 || PHP_VERSION_ID >= 90000) {
    http_response_code(500);
    exit("PHP 7.x or 8.x required.");
}
/** NyaaXport Cloud API exporter. Author: Yevhen Levchenko. */
const NYAAXPORT_CLOUD_KEY = "CHANGE-ME-NOW";
/* BUILD_COMMON */
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

