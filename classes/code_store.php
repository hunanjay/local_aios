<?php


/**
 * Authorization code store for SSO token exchange.
 *
 * Stores temporary authorization codes in Moodle cache for secure token exchange.
 * Codes are short-lived (5 minutes) and single-use only.
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */

namespace local_aios;

defined('MOODLE_INTERNAL') || die();

/**
 * Authorization code store class.
 *
 * Manages temporary authorization codes for OAuth2-style token exchange.
 */
class code_store {

    /** @var int Code expiration time in seconds (5 minutes) */
    const CODE_EXPIRY = 300;

    /** @var string Cache key prefix */
    const CACHE_PREFIX = 'local_aios_code_';

    /**
     * Generate a new authorization code.
     *
     * @param int $userid Moodle user ID
     * @param string $servicename Service name
     * @param int $tokenid Token ID
     * @return string Authorization code
     */
    public static function generate_code($userid, $servicename, $tokenid) {
        global $DB;

        // Generate a secure random code (32 bytes = 64 hex characters).
        $code = bin2hex(random_bytes(32));

        // Store code data in cache.
        $data = [
            'userid' => $userid,
            'servicename' => $servicename,
            'tokenid' => $tokenid,
            'created' => time(),
            'used' => false,
        ];

        // Use Moodle's cache API.
        $cache = \cache::make('local_aios', 'authcodes');
        $cache->set($code, $data);

        return $code;
    }

    /**
     * Validate and consume an authorization code.
     *
     * This method:
     * 1. Checks if the code exists
     * 2. Verifies it hasn't expired
     * 3. Ensures it hasn't been used before
     * 4. Marks it as used (single-use)
     *
     * @param string $code Authorization code
     * @return array|false Code data if valid, false otherwise
     */
    public static function validate_and_consume_code($code) {
        // Use Moodle's cache API.
        $cache = \cache::make('local_aios', 'authcodes');
        $data = $cache->get($code);

        // Check if code exists.
        if ($data === false) {
            return false;
        }

        // Check if code has expired.
        if (time() - $data['created'] > self::CODE_EXPIRY) {
            // Delete expired code.
            $cache->delete($code);
            return false;
        }

        // Check if code has already been used.
        if ($data['used']) {
            return false;
        }

        // Mark code as used and update cache.
        $data['used'] = true;
        $cache->set($code, $data);

        // Delete the code after a short delay to prevent race conditions.
        // We keep it briefly to prevent double-use.
        // The cache TTL will eventually clean it up.

        return $data;
    }

    /**
     * Clean up expired codes.
     *
     * This is called by scheduled task or manually for maintenance.
     * Moodle's cache system will handle automatic expiration.
     */
    public static function cleanup_expired_codes() {
        // Moodle's cache system handles this automatically.
        // This method exists for explicit cleanup if needed.
        $cache = \cache::make('local_aios', 'authcodes');
        $cache->purge();
    }
}
