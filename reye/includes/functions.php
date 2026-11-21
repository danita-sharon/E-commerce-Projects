<?php
// =========================================================
// Shared helper functions: sessions, security, formatting
// =========================================================
require_once __DIR__ . '/db.php';

// ---- Secure session ----
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),   // only over HTTPS when available
        'httponly' => true,                        // JavaScript can't read the session cookie
        'samesite' => 'Lax',                       // helps block cross-site requests
    ]);
    session_start();
}

// ---- Output escaping: use e() whenever printing user data (stops XSS) ----
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// ---- Links ----
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

// ---- Money: 450 -> "GH₵ 450.00" ----
function money($amount): string
{
    return 'GH₵ ' . number_format((float) $amount, 2);
}

// ---- CSRF protection: every form includes csrf_field(), every POST calls csrf_check() ----
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('Your session expired. Please go back and try again.');
    }
}

// ---- Logged-in user ----
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function is_admin(): bool
{
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('admin/login.php');
    }
}

// ---- One-time messages ("Saved", "Wrong password", ...) ----
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// ---- WhatsApp link with a ready-made message ----
function whatsapp_link(string $message = ''): string
{
    return 'https://wa.me/' . WHATSAPP_NUMBER . ($message ? '?text=' . rawurlencode($message) : '');
}