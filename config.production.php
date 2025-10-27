# =====================================================
# CONFINTER - Configuração de Produção
# Arquivo config.production.php
# Data: 06/09/2025
# =====================================================

<?php
// =====================================================
// CONFIGURAÇÕES DE PRODUÇÃO - CONFINTER
// Este arquivo contém todas as configurações do sistema
// =====================================================

// =====================================================
// CONFIGURAÇÕES DE BANCO DE DADOS
// =====================================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'confinter_prod');
define('DB_USER', getenv('DB_USER') ?: 'confinter_user');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

// Configurações avançadas do banco
define('DB_MAX_CONNECTIONS', 100);
define('DB_CONNECTION_TIMEOUT', 30);
define('DB_COMMAND_TIMEOUT', 300);

// =====================================================
// CONFIGURAÇÕES DE SEGURANÇA
// =====================================================
define('APP_SECRET_KEY', getenv('APP_SECRET_KEY') ?: 'sua-chave-secreta-muito-segura-aqui');
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'chave-de-criptografia-32-caracteres');
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'jwt-secret-key-production');

// Configurações de sessão
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_SECURE', true); // HTTPS obrigatório
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Strict');

// Configurações de senha
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SYMBOLS', true);

// =====================================================
// CONFIGURAÇÕES DE EMAIL
// =====================================================
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: 'noreply@confinter.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'noreply@confinter.com');
define('SMTP_FROM_NAME', 'CONFINTER');

// =====================================================
// CONFIGURAÇÕES DE UPLOAD
// =====================================================
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', '/var/www/uploads/');
define('UPLOAD_URL', 'https://seudominio.com/uploads/');

// =====================================================
// CONFIGURAÇÕES DE CACHE
// =====================================================
define('CACHE_ENABLED', true);
define('CACHE_DRIVER', 'file'); // file, redis, memcached
define('CACHE_TTL', 3600); // 1 hora
define('CACHE_PATH', '/var/www/cache/');

// Redis (se usado)
define('REDIS_HOST', getenv('REDIS_HOST') ?: 'localhost');
define('REDIS_PORT', getenv('REDIS_PORT') ?: 6379);
define('REDIS_PASSWORD', getenv('REDIS_PASSWORD') ?: '');

// =====================================================
// CONFIGURAÇÕES DE LOG
// =====================================================
define('LOG_LEVEL', 'WARNING'); // DEBUG, INFO, WARNING, ERROR
define('LOG_PATH', '/var/log/confinter/');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_ROTATION', 'daily'); // daily, weekly, monthly

// =====================================================
// CONFIGURAÇÕES DE API
// =====================================================
define('API_ENABLED', true);
define('API_RATE_LIMIT', 1000); // requests por hora
define('API_KEY_REQUIRED', true);
define('API_CORS_ORIGINS', ['https://seudominio.com', 'https://admin.seudominio.com']);

// =====================================================
// CONFIGURAÇÕES DE MONITORAMENTO
// =====================================================
define('MONITORING_ENABLED', true);
define('ERROR_REPORTING_EMAIL', 'admin@confinter.com');
define('PERFORMANCE_MONITORING', true);
define('SLOW_QUERY_THRESHOLD', 2.0); // segundos

// =====================================================
// CONFIGURAÇÕES DE BACKUP
// =====================================================
define('BACKUP_ENABLED', true);
define('BACKUP_FREQUENCY', 'daily'); // hourly, daily, weekly
define('BACKUP_RETENTION', 30); // dias
define('BACKUP_PATH', '/var/backups/confinter/');

// =====================================================
// CONFIGURAÇÕES DE CDN
// =====================================================
define('CDN_ENABLED', false);
define('CDN_URL', 'https://cdn.seudominio.com/');
define('CDN_KEY', getenv('CDN_KEY') ?: '');

// =====================================================
// CONFIGURAÇÕES DE ANALYTICS
// =====================================================
define('GOOGLE_ANALYTICS_ID', getenv('GA_ID') ?: 'GA-XXXXXXXXX');
define('HOTJAR_ID', getenv('HOTJAR_ID') ?: '');
define('MIXPANEL_TOKEN', getenv('MIXPANEL_TOKEN') ?: '');

// =====================================================
// CONFIGURAÇÕES DE PAGAMENTO (se aplicável)
// =====================================================
define('PAYMENT_GATEWAY', 'stripe'); // stripe, paypal, pagseguro
define('STRIPE_PUBLIC_KEY', getenv('STRIPE_PUBLIC_KEY') ?: '');
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: '');
define('PAYMENT_CURRENCY', 'BRL');

// =====================================================
// CONFIGURAÇÕES DE NOTIFICAÇÕES
// =====================================================
define('PUSH_NOTIFICATIONS_ENABLED', false);
define('SMS_GATEWAY', 'twilio'); // twilio, aws-sns
define('TWILIO_SID', getenv('TWILIO_SID') ?: '');
define('TWILIO_TOKEN', getenv('TWILIO_TOKEN') ?: '');
define('TWILIO_FROM', getenv('TWILIO_FROM') ?: '');

// =====================================================
// CONFIGURAÇÕES DE MANUTENÇÃO
// =====================================================
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Sistema em manutenção. Volte em breve.');
define('MAINTENANCE_ALLOWED_IPS', ['127.0.0.1', '192.168.1.100']);

// =====================================================
// CONFIGURAÇÕES DE LOCALIZAÇÃO
// =====================================================
define('DEFAULT_TIMEZONE', 'America/Sao_Paulo');
define('DEFAULT_LANGUAGE', 'pt_BR');
define('DEFAULT_CURRENCY', 'BRL');
define('DATE_FORMAT', 'd/m/Y');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');

// =====================================================
// CONFIGURAÇÕES DE PERFORMANCE
// =====================================================
define('COMPRESSION_ENABLED', true);
define('MINIFY_HTML', true);
define('MINIFY_CSS', true);
define('MINIFY_JS', true);
define('LAZY_LOADING_IMAGES', true);

// =====================================================
// CONFIGURAÇÕES DE DEBUG (PRODUÇÃO = FALSE)
// =====================================================
define('DEBUG_MODE', false);
define('DISPLAY_ERRORS', false);
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', '/var/log/php/error.log');

// =====================================================
// CONFIGURAÇÕES DE SISTEMA
// =====================================================
define('APP_NAME', 'CONFINTER');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'https://seudominio.com');
define('ADMIN_URL', 'https://admin.seudominio.com');
define('API_URL', 'https://api.seudominio.com');

// Configurações de limite de recursos
define('MAX_EXECUTION_TIME', 300);
define('MEMORY_LIMIT', '128M');
define('POST_MAX_SIZE', '8M');
define('UPLOAD_MAX_FILESIZE', '5M');

// =====================================================
// CONFIGURAÇÕES DE FUNCIONALIDADES
// =====================================================
define('FEATURE_ANALYTICS', true);
define('FEATURE_ML_PREDICTIONS', true);
define('FEATURE_REAL_TIME_MONITORING', true);
define('FEATURE_USER_MANAGEMENT', true);
define('FEATURE_BACKUP_SYSTEM', true);
define('FEATURE_API_ACCESS', true);
define('FEATURE_MULTILANGUAGE', false);

// =====================================================
// CONSTANTES DE SISTEMA
// =====================================================
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', __DIR__);
define('LOGS_PATH', ROOT_PATH . '/logs');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Status codes
define('STATUS_ACTIVE', 1);
define('STATUS_INACTIVE', 0);
define('STATUS_DELETED', -1);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');
define('ROLE_MODERATOR', 'moderator');

// =====================================================
// CONFIGURAÇÕES DE TERCEIROS
// =====================================================

// Google reCAPTCHA
define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: '');
define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: '');

// Social Login
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('FACEBOOK_APP_ID', getenv('FACEBOOK_APP_ID') ?: '');
define('FACEBOOK_APP_SECRET', getenv('FACEBOOK_APP_SECRET') ?: '');

// =====================================================
// FIM DAS CONFIGURAÇÕES
// =====================================================

// Verificar configurações críticas
if (empty(DB_PASS)) {
    error_log("ATENÇÃO: Senha do banco de dados não configurada!");
}

if (empty(APP_SECRET_KEY) || APP_SECRET_KEY === 'sua-chave-secreta-muito-segura-aqui') {
    error_log("ATENÇÃO: APP_SECRET_KEY não configurada ou usando valor padrão!");
}

// =====================================================
// FIM DO ARQUIVO config.production.php
// =====================================================
?>
