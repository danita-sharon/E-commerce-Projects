<?php
// =========================================================
// Reye AI: the brain behind the chat bubble
//
// 1. Built-in answers using LIVE data from the database
//    (products, prices, stock, delivery fees, orders).
// 2. A short conversation to collect requests for things
//    Reye doesn't make yet -> saved to customer_requests.
// 3. Optional: if an Anthropic API key is set in config.php,
//    open-ended questions go to Claude. Without a key, the
//    built-in answers still cover everything.
// =========================================================

const AI_SIZE_TABLE = [40 => 25.4, 41 => 26.0, 42 => 26.7, 43 => 27.3, 44 => 28.0, 45 => 28.6];

// Each reply: ['text' => ..., 'chips' => [...], 'links' => [['label' => ..., 'url' => ...]]]
function ai_reply(string $text, array $chips = [], array $links = []): array
{
    return ['text' => $text, 'chips' => $chips, 'links' => $links];
}

function ai_products(PDO $pdo): array
{
    $rows = $pdo->query('SELECT p.id, p.name, p.slug, p.price, p.stock, p.sizes, p.badge, c.slug AS cat
                         FROM products p JOIN categories c ON c.id = p.category_id WHERE p.is_active = 1 ORDER BY p.id')->fetchAll();
    $colours = [];
    foreach ($pdo->query('SELECT product_id, colour_name FROM product_colours ORDER BY id') as $c) {
        $colours[(int) $c['product_id']][] = $c['colour_name'];
    }
    foreach ($rows as &$r) {
        $r['colours'] = $colours[(int) $r['id']] ?? [];
    }
    unset($r);
    return $rows;
}

// Does the message mention one of our products (by name or colour)?
function ai_find_product(array $products, string $msg): ?array
{
    $best = null;
    $bestScore = 0;
    foreach ($products as $p) {
        $score = 0;
        $words = array_diff(preg_split('/\W+/', strtolower($p['name'])), ['the', 'slide', 'pouch', '']);
        foreach ($words as $w) {
            if (strlen($w) > 2 && strpos($msg, $w) !== false) {
                $score += 3;
            }
        }
        foreach ($p['colours'] as $c) {
            if (strpos($msg, strtolower($c)) !== false) {
                $score += 1;
            }
        }
        if ($score > $bestScore) {
            $best = $p;
            $bestScore = $score;
        }
    }
    return $best;
}

function ai_product_line(array $p): string
{
    $stock = (int) $p['stock'] <= 0 ? 'sold out right now' : ((int) $p['stock'] <= 3 ? 'only ' . (int) $p['stock'] . ' left' : 'in stock');
    return $p['name'] . ': ' . money($p['price']) . ($p['colours'] ? ' in ' . implode(', ', $p['colours']) : '') . " ($stock)";
}

// Main entry point
function reye_ai(PDO $pdo, string $message): array
{
    $msg = strtolower(trim($message));
    $chat = &$_SESSION['reye_ai'];
    $chat = $chat ?? ['state' => 'idle', 'req' => [], 'history' => []];
    $user = current_user();

    // ---------- Collecting a request (multi-step) ----------
    if ($chat['state'] === 'offer') {
        if (preg_match('/^(yes|yeah|yh|sure|ok|okay|please|go ahead|yes please)\b/', $msg)) {
            $chat['state'] = 'ask_details';
            return ai_reply('Great. Tell me a bit more: style, colour, size, anything that matters to you.');
        }
        if (preg_match('/^(no|nah|not now|no thanks)\b/', $msg)) {
            $chat['state'] = 'idle';
            return ai_reply('No problem. Anything else I can help with?', ['Size help', 'Delivery', 'Bestsellers']);
        }
        $chat['state'] = 'idle';   // they moved on: answer the new question below
    }
    if ($chat['state'] === 'ask_details') {
        $chat['req']['message'] .= "\nDetails: " . mb_substr($message, 0, 500);
        if ($user) {
            $chat['req']['name'] = $user['full_name'];
            $chat['req']['contact'] = $user['phone'] ?: $user['email'];
            return ai_save_request($pdo, $chat);
        }
        $chat['state'] = 'ask_name';
        return ai_reply("Thanks. What's your name?");
    }
    if ($chat['state'] === 'ask_name') {
        $chat['req']['name'] = mb_substr(trim($message), 0, 100);
        $chat['state'] = 'ask_contact';
        return ai_reply('Thanks, ' . explode(' ', $chat['req']['name'])[0] . '. What WhatsApp number or email should the Reye team use to reach you?');
    }
    if ($chat['state'] === 'ask_contact') {
        $clean = preg_replace('/\s+/', '', $message);
        if (!filter_var($clean, FILTER_VALIDATE_EMAIL) && !preg_match('/^\+?\d{9,15}$/', $clean)) {
            return ai_reply("That doesn't look like a phone number or email. Could you try again? For example 024 123 4567.");
        }
        $chat['req']['contact'] = $clean;
        return ai_save_request($pdo, $chat);
    }

    $products = ai_products($pdo);

    // ---------- Greetings and thanks ----------
    if (preg_match('/^(hi|hello|hey|good (morning|afternoon|evening)|hiya|yo)\b/', $msg) && str_word_count($msg) <= 4) {
        return ai_reply("Hi! I'm Reye AI. I can help with sizes, delivery, payment, bespoke orders and finding the right pair.",
            ['Size help', 'Delivery', 'Bestsellers', 'Bespoke']);
    }
    if (preg_match('/\b(thank|thanks|thx|appreciate)\b/', $msg)) {
        return ai_reply("You're welcome! Anything else, just ask.");
    }

    // ---------- Track an order ----------
    if (preg_match('/reye-[a-z0-9]{6}/', $msg, $m) || preg_match('/\b(track|my order|where is|order status|delivered yet)\b/', $msg)) {
        if (!$user) {
            return ai_reply('Log in to see your orders and where they are.', [], [['label' => 'Log in', 'url' => url('login.php')]]);
        }
        $sql = "SELECT order_no, status, created_at FROM orders WHERE user_id = ? AND payment_status = 'paid'";
        $params = [$user['id']];
        if (!empty($m[0])) {
            $sql .= ' AND order_no = ?';
            $params[] = strtoupper($m[0]);
        }
        $stmt = $pdo->prepare($sql . ' ORDER BY id DESC LIMIT 1');
        $stmt->execute($params);
        $o = $stmt->fetch();
        if (!$o) {
            return ai_reply("I couldn't find a paid order on your account" . (!empty($m[0]) ? ' with that number' : '') . '.',
                [], [['label' => 'My account', 'url' => url('account.php')]]);
        }
        $words = ['processing' => 'confirmed and being prepared', 'shipped' => 'on its way to you', 'delivered' => 'delivered', 'cancelled' => 'cancelled'];
        return ai_reply('Your order ' . $o['order_no'] . ' is ' . ($words[$o['status']] ?? $o['status']) . '.', [],
            [['label' => 'View order', 'url' => url('order.php?no=' . urlencode($o['order_no']))]]);
    }

    // ---------- Sizes (including "my foot is 27cm") ----------
    if (preg_match('/(\d{2}(?:\.\d)?)\s*(cm|centimet)/', $msg, $m) || preg_match('/\b(size|fit|measure|foot|feet|big|small|tight)\b/', $msg)) {
        if (!empty($m[1])) {
            $cm = (float) $m[1];
            $size = null;
            foreach (AI_SIZE_TABLE as $eu => $len) {
                if ($cm <= $len + 0.2) { $size = $eu; break; }
            }
            if ($size) {
                return ai_reply("For a $cm cm foot, go for EU $size. If you're between sizes or have wide feet, go up one.", ['Bestsellers'],
                    [['label' => 'Shop slides', 'url' => url('shop.php?cat=slides')]]);
            }
            return ai_reply("For a $cm cm foot, our standard sizes may not fit perfectly, but a bespoke pair will. We make it to your exact measurement.", [],
                [['label' => 'Start a bespoke order', 'url' => url('bespoke.php')]]);
        }
        return ai_reply('Our slides come in EU 40 to 45. Measure from your heel to your longest toe: 26 cm is a 41, 26.7 cm a 42, 27.3 cm a 43. Tell me your foot length and I\'ll pick your size.',
            [], [['label' => 'Size guide', 'url' => url('shop.php#size-guide')]]);
    }

    // ---------- Delivery ----------
    if (preg_match('/\b(deliver|delivery|ship|shipping|how long|abroad|international|kumasi|accra|takoradi|tamale|courier|arrive)\b/', $msg)) {
        $regions = $pdo->query('SELECT name, fee, free_over FROM delivery_regions ORDER BY fee')->fetchAll();
        // Asked about one place? Answer just for that place.
        foreach ($regions as $r) {
            $first = strtolower(strtok($r['name'], ' '));
            if (strpos($msg, $first) !== false && !in_array($first, ['other'], true)) {
                $time = $first === 'international' ? 'about 7 to 10 working days' : '1 to 3 working days';
                return ai_reply('Delivery to ' . $r['name'] . ' is ' . money($r['fee'])
                    . ($r['free_over'] ? ', or free on orders over ' . money($r['free_over']) : '') . ". It takes $time.", ['Payment', 'Bestsellers']);
            }
        }
        if (preg_match('/\b(london|uk|usa|us|america|canada|nigeria|europe|abroad|overseas)\b/', $msg)) {
            foreach ($regions as $r) {
                if (stripos($r['name'], 'international') !== false) {
                    return ai_reply('We ship worldwide. International delivery is ' . money($r['fee']) . ' and takes about 7 to 10 working days.', ['Payment', 'Bestsellers']);
                }
            }
        }
        $lines = [];
        foreach ($regions as $r) {
            $lines[] = $r['name'] . ' ' . money($r['fee']) . ($r['free_over'] ? ' (free over ' . money($r['free_over']) . ')' : '');
        }
        return ai_reply('Delivery: ' . implode(' · ', $lines) . '. Orders in Ghana arrive in 1 to 3 working days, international in about 7 to 10.', [],
            [['label' => 'Delivery details', 'url' => url('about.php#delivery')]]);
    }

    // ---------- Payment ----------
    if (preg_match('/\b(pay|payment|momo|mobile money|card|visa|mastercard|paystack|telecel|vodafone|airteltigo|cash)\b/', $msg)) {
        return ai_reply('You can pay securely with Paystack using a card or mobile money (MTN MoMo, Telecel Cash and AT Money). We never see or store your card details.');
    }

    // ---------- Bespoke ----------
    if (preg_match('/\b(bespoke|custom|customi[sz]e|personal|initials|made to order|my own design)\b/', $msg)) {
        return ai_reply('Bespoke pairs are made just for you in 7 to 10 days. Choose your leather, colour and fit, and send your foot measurement. We confirm the price on WhatsApp first.', [],
            [['label' => 'Start a bespoke order', 'url' => url('bespoke.php')]]);
    }

    // ---------- Returns and exchanges (no invented policy: hand over to the team) ----------
    if (preg_match('/\b(return|refund|exchange|swap|wrong size|damaged|faulty)\b/', $msg)) {
        return ai_reply('Sorry to hear that. Message the Reye team on WhatsApp with your order number and they will sort it out with you.', [],
            [['label' => 'WhatsApp the team', 'url' => whatsapp_link('Hi Reye, I need help with my order.')]]);
    }

    // ---------- Talk to a person ----------
    if (preg_match('/\b(human|person|someone|call|whatsapp|contact|phone number|talk to)\b/', $msg)) {
        return ai_reply('You can chat with the Reye team directly on WhatsApp.', [],
            [['label' => 'WhatsApp the team', 'url' => whatsapp_link('Hi Reye, I have a question.')]]);
    }

    // ---------- A specific product ----------
    $found = ai_find_product($products, $msg);
    if ($found && !preg_match('/\b(recommend|suggest)\b/', $msg)) {
        return ai_reply(ai_product_line($found) . '. Sizes: ' . str_replace(',', ', ', $found['sizes']) . '.', ['Size help', 'Delivery'],
            [['label' => 'View ' . $found['name'], 'url' => url('product.php?slug=' . urlencode($found['slug']))]]);
    }

    // ---------- Recommendations ----------
    if (preg_match('/\b(recommend|suggest|best ?seller|popular|wedding|event|occasion|gift|what should|which one|bestsellers)\b/', $msg)) {
        $gift = (bool) preg_match('/\bgift\b/', $msg);
        $pool = array_values(array_filter($products, fn($p) => (int) $p['stock'] > 0));
        // Gifts: pouches first. Otherwise: bestsellers first.
        usort($pool, fn($a, $b) => $gift
            ? (($b['cat'] === 'pouches') <=> ($a['cat'] === 'pouches'))
            : ((stripos((string) $b['badge'], 'best') !== false) <=> (stripos((string) $a['badge'], 'best') !== false)));
        $picks = array_slice($pool, 0, 3);
        if (!$picks) {
            return ai_reply('Everything is sold out right now, but bespoke orders are open.', [], [['label' => 'Bespoke', 'url' => url('bespoke.php')]]);
        }
        $links = array_map(fn($p) => ['label' => $p['name'], 'url' => url('product.php?slug=' . urlencode($p['slug']))], $picks);
        return ai_reply('Customers love these: ' . implode(' · ', array_map('ai_product_line', $picks)) . '.', [], $links);
    }

    // ---------- Prices / what do you sell ----------
    if (preg_match('/\b(price|cost|how much|cheap|catalog|what do you (sell|have)|products|collection|slides?|sandals?|slippers?|pouch(es)?|bags?)\b/', $msg)
        && !preg_match('/\b(do you (have|sell|make)|got any)\b/', $msg)) {
        $lines = array_map('ai_product_line', $products);
        return ai_reply('Here is what we have: ' . implode(' · ', $lines) . '.', [], [['label' => 'Shop everything', 'url' => url('shop.php')]]);
    }

    // ---------- "Do you have X?" for things Reye doesn't make ----------
    if (preg_match('/\b(do you (have|sell|make|do)|got any|any chance|can you make|looking for|i want|i need)\b/', $msg)) {
        $known = '/\b(slides?|sandals?|slippers?|pouch(es)?|bags?|purses?|leather)\b/';
        if (!preg_match($known, $msg)) {
            return ai_offer_request($chat, $message, "Reye doesn't make that yet. Want me to send it to the Reye team as a request? They often make new pieces when customers ask.");
        }
        return ai_reply('Yes! Have a look at the collection, or tell me what you have in mind.', ['Bestsellers'], [['label' => 'Shop', 'url' => url('shop.php')]]);
    }

    if (preg_match('/\b(request|suggest something|can\'?t find)\b/', $msg)) {
        $chat['req'] = ['message' => 'Customer request', 'type' => 'request'];
        $chat['state'] = 'ask_details';
        return ai_reply('Happy to pass it on. What are you looking for?');
    }

    // ---------- Anything else: real AI if configured, otherwise offer to pass it on ----------
    $ai = ai_ask_claude($pdo, $message, $products, $chat);
    if ($ai) {
        return $ai;
    }
    return ai_offer_request($chat, $message, "I'm not sure about that one. Want me to pass your message to the Reye team?");
}

function ai_offer_request(array &$chat, string $message, string $text): array
{
    $chat['state'] = 'offer';
    $chat['req'] = ['message' => 'Asked Reye AI: ' . mb_substr($message, 0, 300), 'type' => 'request'];
    return ai_reply($text, ['Yes please', 'No thanks']);
}

function ai_save_request(PDO $pdo, array &$chat): array
{
    $user = current_user();
    $pdo->prepare('INSERT INTO customer_requests (user_id, name, contact, message, type) VALUES (?, ?, ?, ?, ?)')
        ->execute([$user['id'] ?? null, $chat['req']['name'], $chat['req']['contact'], $chat['req']['message'], $chat['req']['type'] ?? 'request']);
    $chat['state'] = 'idle';
    $chat['req'] = [];
    return ai_reply('Done! Your request is with the Reye team and they will reach out soon. Anything else?', ['Bestsellers', 'Delivery']);
}

// Optional: ask Claude for open-ended questions (only if a key is set in config.php)
function ai_ask_claude(PDO $pdo, string $message, array $products, array &$chat): ?array
{
    if (!defined('ANTHROPIC_API_KEY') || ANTHROPIC_API_KEY === '' || !function_exists('curl_init')) {
        return null;
    }
    $catalogue = implode("\n", array_map('ai_product_line', $products));
    $delivery = implode("\n", array_map(fn($r) => $r['name'] . ': ' . money($r['fee']) . ($r['free_over'] ? ', free over ' . money($r['free_over']) : ''),
        $pdo->query('SELECT name, fee, free_over FROM delivery_regions')->fetchAll()));

    $system = "You are Reye AI, the friendly shop assistant for Reye, a Ghanaian brand of handmade leather slides and pouches.\n"
        . "Answer in 1 to 3 short sentences, warm and clear. Only talk about Reye and shopping with Reye.\n"
        . "Never invent products, prices, discounts or policies. Use only the facts below.\n"
        . "If the customer wants something Reye does not sell, say so kindly and end your reply with [[OFFER_REQUEST]].\n"
        . "If you don't know, say so and end with [[OFFER_REQUEST]].\n\n"
        . "PRODUCTS:\n$catalogue\n\nDELIVERY:\n$delivery\nGhana 1-3 working days, international about 7-10.\n\n"
        . "PAYMENT: Paystack, card or mobile money (MTN MoMo, Telecel Cash, AT Money).\n"
        . "BESPOKE: custom pairs in 7 to 10 days; price confirmed on WhatsApp.\n"
        . "SIZES: EU 40-45. Foot length 25.4cm=40, 26=41, 26.7=42, 27.3=43, 28=44, 28.6=45.";

    $history = array_slice($chat['history'] ?? [], -6);
    $history[] = ['role' => 'user', 'content' => mb_substr($message, 0, 500)];

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['x-api-key: ' . ANTHROPIC_API_KEY, 'anthropic-version: 2023-06-01', 'content-type: application/json'],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => defined('ANTHROPIC_MODEL') ? ANTHROPIC_MODEL : 'claude-haiku-5-5',
            'max_tokens' => 300,
            'system' => $system,
            'messages' => $history,
        ]),
    ]);
    $res = json_decode((string) curl_exec($ch), true);
    curl_close($ch);
    $text = trim($res['content'][0]['text'] ?? '');
    if ($text === '') {
        return null;   // fall back to the built-in answer
    }

    $chat['history'] = array_slice(array_merge($history, [['role' => 'assistant', 'content' => $text]]), -8);
    if (strpos($text, '[[OFFER_REQUEST]]') !== false) {
        return ai_offer_request($chat, $message, trim(str_replace('[[OFFER_REQUEST]]', '', $text)) . ' Want me to pass this to the Reye team?');
    }
    return ai_reply($text);
}
